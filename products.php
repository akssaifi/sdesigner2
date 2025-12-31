<?php
// products.php - Product listing page with filtering

require_once 'config.php';

// Get boutique information
$boutique_name = getSetting($conn, 'boutique_name') ?? 'SDesigner Boutique';
$designer_name = getSetting($conn, 'designer_name') ?? 'Dinky Ahuja';
$location = getSetting($conn, 'location') ?? 'Jalandhar, Punjab';
$primary_phone = getSetting($conn, 'primary_phone') ?? '89686-36373';
$secondary_phone = getSetting($conn, 'secondary_phone') ?? '9814927250';
$instagram_url = getSetting($conn, 'instagram_url') ?? '#';
$facebook_url = getSetting($conn, 'facebook_url') ?? '#';
$show_social_links = getSetting($conn, 'show_social_links') ?? '1';

// Get filter parameters
$product_type = isset($_GET['type']) ? $_GET['type'] : '';
$category_id = isset($_GET['category']) ? intval($_GET['category']) : 0;
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
$occasion = isset($_GET['occasion']) ? intval($_GET['occasion']) : 0;
$fabric = isset($_GET['fabric']) ? intval($_GET['fabric']) : 0;
$style = isset($_GET['style']) ? intval($_GET['style']) : 0;
$work = isset($_GET['work']) ? intval($_GET['work']) : 0;
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';

// Get page number
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 12;
$offset = ($page - 1) * $limit;

// Build query
$sql = "SELECT p.*, c.name as category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE p.is_active = 1";

$params = [];
$types = '';

if (!empty($product_type) && in_array($product_type, ['stitched', 'unstitched', 'accessory'])) {
    $sql .= " AND p.product_type = ?";
    $params[] = $product_type;
    $types .= 's';
}

if ($category_id > 0) {
    $sql .= " AND p.category_id = ?";
    $params[] = $category_id;
    $types .= 'i';
}

if (!empty($search_query)) {
    $sql .= " AND (p.name LIKE ? OR c.name LIKE ?)";
    $params[] = "%$search_query%";
    $params[] = "%$search_query%";
    $types .= 'ss';
}

// Meta tag filters
$meta_filters = ['occasion' => $occasion, 'fabric' => $fabric, 'style' => $style, 'work' => $work];
foreach ($meta_filters as $key => $value) {
    if ($value > 0) {
        $sql .= " AND EXISTS (SELECT 1 FROM product_meta_tags pmt 
                  JOIN meta_tags mt ON pmt.meta_tag_id = mt.id 
                  WHERE pmt.product_id = p.id AND mt.id = ?)";
        $params[] = $value;
        $types .= 'i';
    }
}

// Get total count for pagination
$count_sql = str_replace("SELECT p.*, c.name as category_name", "SELECT COUNT(*) as total", $sql);
$count_stmt = $conn->prepare($count_sql);
if (!empty($params)) {
    $count_stmt->bind_param($types, ...$params);
}
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$total_rows = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $limit);
$count_stmt->close();

// Add sorting
switch ($sort) {
    case 'price_low':
        $sql .= " ORDER BY p.price ASC";
        break;
    case 'price_high':
        $sql .= " ORDER BY p.price DESC";
        break;
    case 'name':
        $sql .= " ORDER BY p.name ASC";
        break;
    case 'newest':
    default:
        $sql .= " ORDER BY p.created_at DESC";
        break;
}

// Add pagination
$sql .= " LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$types .= 'ii';

// Execute query
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$products = [];
while ($row = $result->fetch_assoc()) {
    $main_image = getMainProductImage($conn, $row['id']);
    $row['main_image_url'] = $main_image ? $main_image['image_url'] : null;
    $row['is_video'] = $main_image ? $main_image['is_video'] : false;
    
    // Get colors
    $color_stmt = $conn->prepare("SELECT c.name, c.hex_code FROM product_colors pc 
                                 JOIN colors c ON pc.color_id = c.id 
                                 WHERE pc.product_id = ? LIMIT 3");
    $color_stmt->bind_param("i", $row['id']);
    $color_stmt->execute();
    $color_result = $color_stmt->get_result();
    $colors = [];
    while ($color = $color_result->fetch_assoc()) {
        $colors[] = $color;
    }
    $row['colors'] = $colors;
    $color_stmt->close();
    
    $products[] = $row;
}
$stmt->close();

// Get filter options
$occasion_tags = getMetaTags($conn, 'occasion');
$fabric_tags = getMetaTags($conn, 'fabric');
$style_tags = getMetaTags($conn, 'style');
$work_tags = getMetaTags($conn, 'work');
$categories = getCategories($conn);

// Get current category name
$current_category = '';
if ($category_id > 0) {
    $cat_stmt = $conn->prepare("SELECT name FROM categories WHERE id = ?");
    $cat_stmt->bind_param("i", $category_id);
    $cat_stmt->execute();
    $cat_result = $cat_stmt->get_result();
    $cat_row = $cat_result->fetch_assoc();
    $current_category = $cat_row ? $cat_row['name'] : '';
    $cat_stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include 'header.php'; ?>
    <title><?php 
        if (!empty($current_category)) echo htmlspecialchars($current_category) . ' | ';
        if (!empty($product_type)) echo ucfirst($product_type) . ' Collection | ';
        echo htmlspecialchars($boutique_name); 
    ?></title>
    
    <style>
        /* Products Page Specific Styles */
     
        .page-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            padding: 4rem 0 2rem;
            margin-top: -70px;
            text-align: center;
            margin-bottom: 2rem;
            position: relative;
            overflow: hidden;
        }

        @media (min-width: 768px) {
            .page-header {
                padding: 5rem 0 3rem;
                margin-top: -80px;
                margin-bottom: 3rem;
            }
        }

        .page-header .container {
            position: relative;
            z-index: 2;
        }

        .page-title {
            font-size: 2rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
            font-family: 'Playfair Display', serif;
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.3);
        }

        @media (min-width: 768px) {
            .page-title {
                font-size: 3rem;
            }
        }

        .page-subtitle {
            font-size: 1rem;
            opacity: 0.95;
            max-width: 600px;
            margin: 0 auto 1.5rem;
            line-height: 1.5;
        }

        @media (min-width: 768px) {
            .page-subtitle {
                font-size: 1.1rem;
                margin-bottom: 2rem;
            }
        }

        .breadcrumb {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.9rem;
            opacity: 0.9;
            flex-wrap: wrap;
            margin-bottom: 1rem;
        }

        .breadcrumb a {
            color: white;
            text-decoration: none;
            transition: var(--transition);
        }

        .breadcrumb a:hover {
            color: var(--accent);
        }

        .breadcrumb .separator {
            color: var(--accent);
        }
        
        .products-container {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 1rem;
        }

        @media (min-width: 992px) {
            .products-container {
                flex-direction: row;
                gap: 2rem;
            }
        }

        /* Mobile Filters Button */
        .mobile-filters-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.75rem 1rem;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: var(--radius);
            font-weight: 600;
            cursor: pointer;
            margin-bottom: 1rem;
            width: 100%;
        }

        @media (min-width: 992px) {
            .mobile-filters-btn {
                display: none;
            }
        }

        /* Filters Sidebar */
        .filters-sidebar {
            width: 100%;
            flex-shrink: 0;
            position: static;
            max-height: none;
            overflow: visible;
            margin-bottom: 2rem;
            display: none;
        }

        @media (min-width: 992px) {
            .filters-sidebar {
                display: block !important;
                width: 280px;
                position: sticky;
                top: 100px;
                align-self: flex-start;
                max-height: calc(100vh - 120px);
                overflow-y: auto;
            }
        }

        .filters-sidebar.active {
            display: block;
        }

        .filter-section {
            background: white;
            border-radius: var(--radius);
            padding: 1.25rem;
            margin-bottom: 1rem;
            border: 1px solid var(--gray-200);
            box-shadow: var(--shadow-sm);
        }

        .filter-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid var(--primary);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .clear-filter {
            font-size: 0.8rem;
            color: var(--primary);
            background: none;
            border: none;
            cursor: pointer;
            font-weight: 500;
            padding: 0.25rem 0.5rem;
        }

        .filter-group {
            margin-bottom: 0.5rem;
            max-height: 200px;
            overflow-y: auto;
        }

        .filter-checkbox {
            display: flex;
            align-items: center;
            margin-bottom: 0.5rem;
            cursor: pointer;
            padding: 0.25rem 0;
        }

        .filter-checkbox input {
            margin-right: 0.75rem;
            width: 18px;
            height: 18px;
            accent-color: var(--primary);
        }

        .filter-label {
            color: var(--gray-700);
            font-size: 0.9rem;
            transition: var(--transition);
        }

        .filter-checkbox:hover .filter-label {
            color: var(--primary);
            padding-left: 0.25rem;
        }

        .filter-checkbox input:checked + .filter-label {
            color: var(--primary);
            font-weight: 600;
        }

        /* Products Content */
        .products-content {
            flex-grow: 1;
            min-width: 0;
        }

        .products-header {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            margin-bottom: 1.5rem;
            background: white;
            padding: 1.25rem;
            border-radius: var(--radius);
            border: 1px solid var(--gray-200);
            box-shadow: var(--shadow-sm);
        }

        @media (min-width: 768px) {
            .products-header {
                flex-direction: row;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 2rem;
                padding: 1.5rem;
            }
        }

        .products-count {
            color: var(--gray-600);
            font-size: 0.9rem;
        }

        .sort-options {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            flex-wrap: wrap;
        }

        .sort-select {
            padding: 0.5rem 0.75rem;
            border: 1px solid var(--gray-300);
            border-radius: var(--radius);
            background: white;
            color: var(--gray-700);
            font-size: 0.9rem;
            cursor: pointer;
            transition: var(--transition);
            min-width: 150px;
        }

        .sort-select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(139, 69, 19, 0.1);
        }

        /* Products Grid */
        .products-grid {
            display: grid;
            grid-template-columns: repeat(1, 1fr);
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        @media (min-width: 640px) {
            .products-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (min-width: 1024px) {
            .products-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media (min-width: 1280px) {
            .products-grid {
                grid-template-columns: repeat(4, 1fr);
                gap: 2rem;
            }
        }

        /* Enhanced Product Card */
        .product-card {
            background: white;
            border-radius: var(--radius);
            overflow: hidden;
            transition: var(--transition);
            border: 1px solid var(--gray-200);
            display: flex;
            flex-direction: column;
            position: relative;
            height: 100%;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
            border-color: var(--primary-light);
        }

        .product-image {
            position: relative;
            width: 100%;
            height: 250px;
            overflow: hidden;
            background: linear-gradient(135deg, var(--gray-100) 0%, var(--gray-200) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        @media (min-width: 768px) {
            .product-image {
                height: 280px;
            }
        }

        .product-image img,
        .product-image video {
            width: 100%;
            height: 100%;
            object-fit: contain;
            transition: transform 0.5s ease;
            padding: 1rem;
        }

        .product-card:hover .product-image img,
        .product-card:hover .product-image video {
            transform: scale(1.05);
        }

        .product-overlay {
            position: absolute;
            inset: 0;
            background: rgba(0, 0, 0, 0.7);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: var(--transition);
            z-index: 2;
        }

        @media (max-width: 768px) {
            .product-overlay {
                opacity: 1;
                background: rgba(0, 0, 0, 0.5);
            }
            
            .overlay-buttons {
                flex-direction: row;
                padding: 1rem;
                gap: 0.5rem;
            }
            
            .view-btn, .add-to-cart-btn {
                padding: 0.5rem 0.75rem;
                font-size: 0.8rem;
                width: auto;
                min-width: 100px;
            }
        }

        .product-card:hover .product-overlay {
            opacity: 1;
        }

        .overlay-buttons {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            align-items: center;
            padding: 1.5rem;
            width: 100%;
        }

        .view-btn, .add-to-cart-btn {
            padding: 0.6rem 1.2rem;
            border-radius: var(--radius);
            border: none;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            width: 80%;
            max-width: 180px;
            text-align: center;
            font-size: 0.9rem;
        }

        .view-btn {
            background: white;
            color: var(--dark);
            text-decoration: none;
        }

        .view-btn:hover {
            background: var(--primary);
            color: white;
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .add-to-cart-btn {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
        }

        .add-to-cart-btn:hover {
            background: linear-gradient(135deg, var(--secondary) 0%, var(--primary) 100%);
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .add-to-cart-btn.added {
            background: #10B981;
            cursor: default;
        }

        .add-to-cart-btn.added:hover {
            transform: none;
            box-shadow: none;
        }

        .stock-badge {
            position: absolute;
            top: 0.75rem;
            right: 0.75rem;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
            z-index: 3;
        }

        @media (min-width: 768px) {
            .stock-badge {
                top: 1rem;
                right: 1rem;
                font-size: 0.75rem;
            }
        }

        .stock-badge.out-of-stock {
            background: #FEE2E2;
            color: #DC2626;
        }

        .stock-badge.low-stock {
            background: #FEF3C7;
            color: #92400E;
        }

        .product-info {
            padding: 1.25rem;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .product-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 0.5rem;
            line-height: 1.3;
        }

        .product-category {
            font-size: 0.8rem;
            color: var(--gray-600);
            margin-bottom: 0.75rem;
        }

        .product-colors {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1rem;
            flex-wrap: wrap;
        }

        .color-dot {
            width: 16px;
            height: 16px;
            border-radius: 50%;
            border: 2px solid white;
            box-shadow: var(--shadow-sm);
        }

        /* Fix for white color dots */
        .color-dot[style*="#ffffff"],
        .color-dot[style*="#FFFFFF"],
        .color-dot[style*="#fff"],
        .color-dot[style*="#FFF"] {
            border: 2px solid var(--gray-300);
        }

        .more-colors {
            font-size: 0.7rem;
            color: var(--gray-500);
        }

        .product-price-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: auto;
            padding-top: 1rem;
            border-top: 1px solid var(--gray-200);
        }

        .product-price {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--primary);
        }

        @media (min-width: 768px) {
            .product-price {
                font-size: 1.25rem;
            }
        }

        .add-to-cart-btn.mobile {
            padding: 0.5rem;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--primary);
            color: white;
            border: none;
            cursor: pointer;
        }

        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 0.5rem;
            margin: 2rem 0;
            flex-wrap: wrap;
        }

        .pagination-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 36px;
            height: 36px;
            padding: 0 0.5rem;
            border-radius: var(--radius);
            border: 1px solid var(--gray-300);
            color: var(--gray-700);
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
            font-size: 0.9rem;
        }

        .pagination-link:hover:not(.disabled) {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        .pagination-link.active {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        .pagination-link.disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        /* No Products */
        .no-products {
            text-align: center;
            padding: 3rem 1rem;
            background: white;
            border-radius: var(--radius);
            border: 1px solid var(--gray-200);
            grid-column: 1 / -1;
        }

        .no-products i {
            font-size: 3rem;
            color: var(--gray-300);
            margin-bottom: 1rem;
        }

        .no-products h3 {
            font-size: 1.25rem;
            color: var(--gray-700);
            margin-bottom: 0.5rem;
        }

        .no-products p {
            color: var(--gray-600);
            margin-bottom: 1.5rem;
            max-width: 400px;
            margin-left: auto;
            margin-right: auto;
        }

        /* Filter Overlay */
        .filter-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }

        .filter-overlay.active {
            display: block;
        }

        /* Close Filters Button */
        .close-filters {
            display: none;
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: var(--primary);
            color: white;
            border: none;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            z-index: 1000;
        }

        @media (max-width: 991px) {
            .close-filters {
                display: flex;
            }
            
            .filters-sidebar {
                position: fixed;
                top: 0;
                left: -100%;
                width: 100%;
                max-width: 320px;
                height: 100vh;
                background: white;
                z-index: 1000;
                padding: 1rem;
                transition: left 0.3s ease;
                overflow-y: auto;
                box-shadow: var(--shadow-lg);
            }
            
            .filters-sidebar.active {
                left: 0;
            }
            
            .filter-overlay.active {
                display: block;
            }
        }

        /* Clear Filters Button */
        .clear-filters-btn {
            width: 100%;
            padding: 0.75rem;
            background: var(--gray-100);
            color: var(--gray-700);
            border: 1px solid var(--gray-300);
            border-radius: var(--radius);
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
            margin-top: 1rem;
        }

        .clear-filters-btn:hover {
            background: var(--gray-200);
        }
    </style>
</head>
<body>
    <!-- Header Section -->
    <?php include 'navigation.php'; ?>

    <!-- Page Header -->
    <section class="page-header">
        <div class="container">
            <div class="breadcrumb">
                <a href="index.php">Home</a>
                <span class="separator">/</span>
                <span>
                    <?php
                    if (!empty($current_category)) {
                        echo htmlspecialchars($current_category);
                    } elseif (!empty($product_type)) {
                        echo ucfirst($product_type) . ' Collection';
                    } elseif (!empty($search_query)) {
                        echo 'Search Results';
                    } else {
                        echo 'All Products';
                    }
                    ?>
                </span>
            </div>
            <h1 class="page-title">
                <?php
                if (!empty($current_category)) {
                    echo htmlspecialchars($current_category);
                } elseif (!empty($product_type)) {
                    echo ucfirst($product_type) . ' Collection';
                } elseif (!empty($search_query)) {
                    echo 'Search Results for "' . htmlspecialchars($search_query) . '"';
                } else {
                    echo 'All Products';
                }
                ?>
            </h1>
            <p class="page-subtitle">
                <?php
                if (!empty($current_category)) {
                    echo 'Browse our exclusive collection of ' . htmlspecialchars($current_category);
                } elseif (!empty($product_type)) {
                    echo 'Discover our premium ' . $product_type . ' collection';
                } elseif (!empty($search_query)) {
                    echo "Found $total_rows matching products";
                } else {
                    echo 'Explore our complete collection of designer wear';
                }
                ?>
            </p>
        </div>
    </section>

    <!-- Products Section -->
    <section class="products-section">
        <div class="products-container">
            <!-- Mobile Filters Button -->
            <button class="mobile-filters-btn" id="mobileFiltersBtn">
                <i class="fas fa-filter"></i>
                Filter Products
            </button>

            <!-- Filter Overlay -->
            <div class="filter-overlay" id="filterOverlay"></div>

            <!-- Filters Sidebar -->
            <aside class="filters-sidebar" id="filtersSidebar">
                <button class="close-filters" id="closeFilters">
                    <i class="fas fa-times"></i>
                </button>

                <div class="filter-section">
                    <h3 class="filter-title">
                        Categories
                        <button class="clear-filter" data-filter="category">Clear</button>
                    </h3>
                    <div class="filter-group">
                        <?php foreach ($categories as $category): ?>
                        <label class="filter-checkbox">
                            <input type="radio" name="category" value="<?php echo $category['id']; ?>"
                                   <?php echo $category_id == $category['id'] ? 'checked' : ''; ?>
                                   onchange="updateFilter()">
                            <span class="filter-label"><?php echo htmlspecialchars($category['name']); ?></span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <?php if (!empty($occasion_tags)): ?>
                <div class="filter-section">
                    <h3 class="filter-title">
                        Occasion
                        <button class="clear-filter" data-filter="occasion">Clear</button>
                    </h3>
                    <div class="filter-group">
                        <?php foreach ($occasion_tags as $tag): ?>
                        <label class="filter-checkbox">
                            <input type="radio" name="occasion" value="<?php echo $tag['id']; ?>"
                                   <?php echo $occasion == $tag['id'] ? 'checked' : ''; ?>
                                   onchange="updateFilter()">
                            <span class="filter-label"><?php echo htmlspecialchars($tag['name']); ?></span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <?php if (!empty($fabric_tags)): ?>
                <div class="filter-section">
                    <h3 class="filter-title">
                        Fabric
                        <button class="clear-filter" data-filter="fabric">Clear</button>
                    </h3>
                    <div class="filter-group">
                        <?php foreach ($fabric_tags as $tag): ?>
                        <label class="filter-checkbox">
                            <input type="radio" name="fabric" value="<?php echo $tag['id']; ?>"
                                   <?php echo $fabric == $tag['id'] ? 'checked' : ''; ?>
                                   onchange="updateFilter()">
                            <span class="filter-label"><?php echo htmlspecialchars($tag['name']); ?></span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <?php if (!empty($style_tags)): ?>
                <div class="filter-section">
                    <h3 class="filter-title">
                        Style
                        <button class="clear-filter" data-filter="style">Clear</button>
                    </h3>
                    <div class="filter-group">
                        <?php foreach ($style_tags as $tag): ?>
                        <label class="filter-checkbox">
                            <input type="radio" name="style" value="<?php echo $tag['id']; ?>"
                                   <?php echo $style == $tag['id'] ? 'checked' : ''; ?>
                                   onchange="updateFilter()">
                            <span class="filter-label"><?php echo htmlspecialchars($tag['name']); ?></span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <?php if (!empty($work_tags)): ?>
                <div class="filter-section">
                    <h3 class="filter-title">
                        Work
                        <button class="clear-filter" data-filter="work">Clear</button>
                    </h3>
                    <div class="filter-group">
                        <?php foreach ($work_tags as $tag): ?>
                        <label class="filter-checkbox">
                            <input type="radio" name="work" value="<?php echo $tag['id']; ?>"
                                   <?php echo $work == $tag['id'] ? 'checked' : ''; ?>
                                   onchange="updateFilter()">
                            <span class="filter-label"><?php echo htmlspecialchars($tag['name']); ?></span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <button class="clear-filters-btn" onclick="clearAllFilters()">
                    <i class="fas fa-times"></i>
                    Clear All Filters
                </button>
            </aside>

            <!-- Products Content -->
            <div class="products-content">
                <div class="products-header">
                    <div class="products-count">
                        Showing <?php echo count($products); ?> of <?php echo $total_rows; ?> products
                    </div>
                    <div class="sort-options">
                        <span>Sort by:</span>
                        <select class="sort-select" onchange="sortProducts(this.value)">
                            <option value="newest" <?php echo $sort == 'newest' ? 'selected' : ''; ?>>Newest First</option>
                            <option value="price_low" <?php echo $sort == 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                            <option value="price_high" <?php echo $sort == 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
                            <option value="name" <?php echo $sort == 'name' ? 'selected' : ''; ?>>Name: A to Z</option>
                        </select>
                    </div>
                </div>

                <?php if (empty($products)): ?>
                <div class="no-products">
                    <i class="fas fa-search"></i>
                    <h3>No Products Found</h3>
                    <p>Try adjusting your filters or search criteria</p>
                    <a href="products.php" class="btn btn-primary">Browse All Products</a>
                </div>
                <?php else: ?>
                <div class="products-grid">
                    <?php foreach ($products as $product): ?>
                    <div class="product-card">
                        <div class="product-image">
                            <?php if ($product['main_image_url']): ?>
                                <?php if ($product['is_video']): ?>
                                <video src="<?php echo htmlspecialchars($product['main_image_url']); ?>" 
                                       muted loop playsinline>
                                </video>
                                <?php else: ?>
                                <img src="<?php echo htmlspecialchars($product['main_image_url']); ?>" 
                                     alt="<?php echo htmlspecialchars($product['name']); ?>"
                                     loading="lazy">
                                <?php endif; ?>
                            <?php else: ?>
                                <div class="no-image">
                                    <i class="fas fa-image"></i>
                                </div>
                            <?php endif; ?>
                            <div class="product-overlay">
                                <div class="overlay-buttons">
                                    <a href="product.php?id=<?php echo $product['id']; ?>" class="view-btn">
                                        <i class="fas fa-eye"></i>
                                        Quick View
                                    </a>
                                    <?php if ($product['stock_quantity'] > 0): ?>
                                    <button class="add-to-cart-btn" data-id="<?php echo $product['id']; ?>">
                                        <i class="fas fa-shopping-bag"></i>
                                        Add to Cart
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php if ($product['stock_quantity'] == 0): ?>
                                <div class="stock-badge out-of-stock">Out of Stock</div>
                            <?php elseif ($product['stock_quantity'] <= 5): ?>
                                <div class="stock-badge low-stock">Only <?php echo $product['stock_quantity']; ?> left</div>
                            <?php endif; ?>
                        </div>
                        <div class="product-info">
                            <h3 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h3>
                            <div class="product-category"><?php echo htmlspecialchars($product['category_name'] ?? 'Uncategorized'); ?></div>
                            <div class="product-colors">
                                <?php foreach ($product['colors'] as $color): ?>
                                    <span class="color-dot" style="background-color: <?php echo $color['hex_code']; ?>" 
                                          title="<?php echo htmlspecialchars($color['name']); ?>"></span>
                                <?php endforeach; ?>
                                <?php if (count($product['colors']) > 3): ?>
                                    <span class="more-colors">+<?php echo count($product['colors']) - 3; ?> more</span>
                                <?php endif; ?>
                            </div>
                            <div class="product-price-row">
                                <div class="product-price">₹<?php echo number_format($product['price'], 2); ?></div>
                                <?php if ($product['stock_quantity'] > 0): ?>
                                    <button class="add-to-cart-btn mobile" data-id="<?php echo $product['id']; ?>">
                                        <i class="fas fa-shopping-bag"></i>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                    <a href="?<?php echo buildQueryString(['page' => $page - 1]); ?>" class="pagination-link">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <?php if ($i == 1 || $i == $total_pages || ($i >= $page - 2 && $i <= $page + 2)): ?>
                        <a href="?<?php echo buildQueryString(['page' => $i]); ?>" 
                           class="pagination-link <?php echo $i == $page ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                        <?php elseif ($i == $page - 3 || $i == $page + 3): ?>
                        <span class="pagination-link disabled">...</span>
                        <?php endif; ?>
                    <?php endfor; ?>

                    <?php if ($page < $total_pages): ?>
                    <a href="?<?php echo buildQueryString(['page' => $page + 1]); ?>" class="pagination-link">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Footer Section -->
    <?php include 'footer.php'; ?>

    <!-- Mobile Notification -->
    

    <script>
        // Mobile filters toggle
        const mobileFiltersBtn = document.getElementById('mobileFiltersBtn');
        const filtersSidebar = document.getElementById('filtersSidebar');
        const filterOverlay = document.getElementById('filterOverlay');
        const closeFilters = document.getElementById('closeFilters');

        if (mobileFiltersBtn && filtersSidebar) {
            mobileFiltersBtn.addEventListener('click', () => {
                filtersSidebar.classList.add('active');
                filterOverlay.classList.add('active');
                document.body.style.overflow = 'hidden';
            });

            closeFilters.addEventListener('click', () => {
                filtersSidebar.classList.remove('active');
                filterOverlay.classList.remove('active');
                document.body.style.overflow = '';
            });

            filterOverlay.addEventListener('click', () => {
                filtersSidebar.classList.remove('active');
                filterOverlay.classList.remove('active');
                document.body.style.overflow = '';
            });
        }

        // Clear filter buttons
        document.querySelectorAll('.clear-filter').forEach(button => {
            button.addEventListener('click', function() {
                const filter = this.dataset.filter;
                const input = document.querySelector(`input[name="${filter}"]:checked`);
                if (input) {
                    input.checked = false;
                    updateFilter();
                }
            });
        });

        function updateFilter() {
            const params = new URLSearchParams(window.location.search);
            
            // Clear existing filter params
            ['category', 'occasion', 'fabric', 'style', 'work', 'type'].forEach(param => {
                params.delete(param);
            });
            
            // Add selected filters
            document.querySelectorAll('input[type="radio"]:checked').forEach(input => {
                params.set(input.name, input.value);
            });
            
            // Keep search query if present
            const search = new URLSearchParams(window.location.search).get('search');
            if (search) {
                params.set('search', search);
            }
            
            // Reset to page 1
            params.set('page', '1');
            
            window.location.href = 'products.php?' + params.toString();
        }

        function clearAllFilters() {
            window.location.href = 'products.php';
        }

        function sortProducts(sortBy) {
            const params = new URLSearchParams(window.location.search);
            params.set('sort', sortBy);
            params.set('page', '1');
            window.location.href = 'products.php?' + params.toString();
        }

        // Add to cart functionality
        document.addEventListener('click', function(e) {
            const addToCartBtn = e.target.closest('.add-to-cart-btn');
            if (addToCartBtn && !addToCartBtn.classList.contains('added')) {
                const productId = addToCartBtn.dataset.id;
                
                // Get product details from card
                const card = addToCartBtn.closest('.product-card');
                const productName = card.querySelector('.product-title').textContent;
                
                // Parse price
                const priceText = card.querySelector('.product-price').textContent;
                const productPrice = parsePrice(priceText);
                
                const productImage = card.querySelector('img')?.src || 
                                   card.querySelector('video')?.src ||
                                   'assets/images/no-image.jpg';
                
                // Add to cart
                const cartItem = {
                    id: productId,
                    name: productName,
                    price: productPrice,
                    image: productImage,
                    quantity: 1
                };
                
                addToCart(cartItem);
                
                // Show notification
                showMobileNotification(`${productName} added to cart!`);
                
                // Button feedback
                const originalHTML = addToCartBtn.innerHTML;
                addToCartBtn.innerHTML = '<i class="fas fa-check"></i> Added';
                addToCartBtn.classList.add('added');
                addToCartBtn.disabled = true;
                
                setTimeout(() => {
                    addToCartBtn.innerHTML = originalHTML;
                    addToCartBtn.classList.remove('added');
                    addToCartBtn.disabled = false;
                }, 2000);
            }
        });

        function parsePrice(priceText) {
            let cleaned = priceText.replace(/[₹$€£]/g, '');
            cleaned = cleaned.replace(/,/g, '');
            cleaned = cleaned.trim();
            const price = parseFloat(cleaned);
            return isNaN(price) ? 0 : price;
        }

      // --- REPLACE THE EXISTING addToCart FUNCTION ---
function addToCart(product) {
    // First, send an AJAX request to update the server-side cart
    fetch('cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'add',
            id: product.id,
            quantity: product.quantity,
            name: product.name,
            price: product.price,
            image: product.image,
            color: product.color, // Include color if relevant
            productType: product.productType, // Include type if relevant
            isUnstitched: product.isUnstitched,
            selectedMeters: product.selectedMeters,
            stitchingOption: product.stitchingOption,
            stitchCharges: product.stitchCharges
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update localStorage with the server's cart state (optional)
            // localStorage.setItem('cart', JSON.stringify(data.cart));
            // Or, just update local storage optimistically and rely on sync via event
            let cart = JSON.parse(localStorage.getItem('cart')) || [];
            const existingItem = cart.find(item => item.id === product.id);
            if (existingItem) {
                existingItem.quantity += product.quantity;
            } else {
                cart.push(product);
            }
            localStorage.setItem('cart', JSON.stringify(cart));

            // Dispatch the event to notify navigation.php to update its display
            window.dispatchEvent(new CustomEvent('cartUpdated'));
            console.log("Cart updated via AJAX and event dispatched.");
        } else {
            console.error("Server error:", data.message);
            // Fallback: update localStorage only if server fails
            let cart = JSON.parse(localStorage.getItem('cart')) || [];
            const existingItem = cart.find(item => item.id === product.id);
            if (existingItem) {
                existingItem.quantity += product.quantity;
            } else {
                cart.push(product);
            }
            localStorage.setItem('cart', JSON.stringify(cart));
            updateCartCount(); // Update local count
        }
    })
    .catch(error => {
        console.error("AJAX error:", error);
        // Fallback: update localStorage only if request fails
        let cart = JSON.parse(localStorage.getItem('cart')) || [];
        const existingItem = cart.find(item => item.id === product.id);
        if (existingItem) {
            existingItem.quantity += product.quantity;
        } else {
            cart.push(product);
        }
        localStorage.setItem('cart', JSON.stringify(cart));
        updateCartCount(); // Update local count
    });
}
// --- END REPLACE ---

        // Shared cart sync for all pages
function updateCartCount() {
    fetch('/cart.php?action=get_cart')
        .then(async r => {
            const text = await r.text();
            try {
                const data = JSON.parse(text);
                if (data.success) {
                    const count = data.item_count || 0;
                    const el = document.querySelector('.cart-count');
                    if (el) {
                        el.textContent = count;
                        el.style.display = count > 0 ? 'flex' : 'none';
                    }
                    // Sync localStorage for offline fallback
                    localStorage.setItem('cart', JSON.stringify(data.cart || []));
                }
            } catch (e) {
                console.warn('Cart sync failed, falling back to localStorage');
                const cart = JSON.parse(localStorage.getItem('cart')) || [];
                const count = cart.reduce((s, i) => s + i.quantity, 0);
                const el = document.querySelector('.cart-count');
                if (el) {
                    el.textContent = count;
                    el.style.display = count > 0 ? 'flex' : 'none';
                }
            }
        })
        .catch(err => {
            console.error('Cart sync error:', err);
        });
}

// Call on every page load
document.addEventListener('DOMContentLoaded', updateCartCount);

// Listen for cart changes (e.g., add/remove on other pages)
window.addEventListener('cartUpdated', updateCartCount);

        // Mobile notification function
        function showMobileNotification(message) {
            const notification = document.getElementById('mobileNotification');
            const messageSpan = notification.querySelector('span');
            messageSpan.textContent = message;
            
            notification.classList.add('show');
            setTimeout(() => {
                notification.classList.remove('show');
            }, 3000);
        }

        // Initialize cart on page load
        document.addEventListener('DOMContentLoaded', function() {
            updateCartCount();
        });
    </script>
</body>
</html>
<?php
// Helper function to build query string
function buildQueryString($updates = []) {
    $params = $_GET;
    foreach ($updates as $key => $value) {
        $params[$key] = $value;
    }
    return http_build_query($params);
}
$conn->close();
?>