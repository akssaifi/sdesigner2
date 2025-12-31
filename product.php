<?php
// product.php - Single product page
require_once 'config.php';
require_once 'functions.php';

// Get product ID from URL
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($product_id <= 0) {
    header('Location: products.php');
    exit();
}

// Get product details with all related data
$product = getProductById($conn, $product_id);
if (!$product) {
    header('Location: products.php');
    exit();
}

// Debug: Uncomment to see product structure
// echo "<pre>"; print_r($product); echo "</pre>"; exit();

// Get product media for all colors at once
function getProductMediaByColor($conn, $product_id) {
    $sql = "SELECT pci.*, c.name as color_name, c.hex_code 
            FROM product_color_images pci
            JOIN colors c ON pci.color_id = c.id
            WHERE pci.product_id = ?
            ORDER BY pci.color_id, pci.display_order ASC, pci.is_main_image DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $media_by_color = [];
    while ($row = $result->fetch_assoc()) {
        $color_id = $row['color_id'];
        if (!isset($media_by_color[$color_id])) {
            $media_by_color[$color_id] = [
                'color_name' => $row['color_name'],
                'hex_code' => $row['hex_code'],
                'media' => []
            ];
        }
        
        $media_by_color[$color_id]['media'][] = [
            'id' => $row['id'],
            'url' => $row['image_url'],
            'is_video' => $row['is_video'],
            'is_main' => $row['is_main_image'],
            'display_order' => $row['display_order']
        ];
    }
    
    $stmt->close();
    return $media_by_color;
}

// Get media for all colors
$product_media = getProductMediaByColor($conn, $product_id);

// Get size chart for this product
$size_chart = null;
if ($product['size_chart_id']) {
    $size_chart_sql = "SELECT * FROM size_charts WHERE id = ?";
    $size_chart_stmt = $conn->prepare($size_chart_sql);
    $size_chart_stmt->bind_param("i", $product['size_chart_id']);
    $size_chart_stmt->execute();
    $size_chart_result = $size_chart_stmt->get_result();
    
    if ($size_chart_row = $size_chart_result->fetch_assoc()) {
        // Decode the JSON measurements
        $measurements = json_decode($size_chart_row['measurements'], true);
        
        if ($measurements && is_array($measurements)) {
            $size_chart = [
                'id' => $size_chart_row['id'],
                'chart_name' => $size_chart_row['chart_name'],
                'measurements' => $measurements,
                'created_at' => $size_chart_row['created_at']
            ];
        }
    }
    $size_chart_stmt->close();
}

// Get boutique information from settings
$boutique_name = getSetting($conn, 'boutique_name') ?? 'SDesigner Boutique';
$designer_name = getSetting($conn, 'designer_name') ?? 'Dinky Ahuja';
$location = getSetting($conn, 'location') ?? 'Jalandhar, Punjab';

// Get related products (same category, different product)
$related_sql = "SELECT p.*, c.name as category_name 
               FROM products p 
               LEFT JOIN categories c ON p.category_id = c.id
               WHERE p.category_id = ? 
               AND p.id != ? 
               AND p.is_active = 1 
               ORDER BY RAND() 
               LIMIT 4";
$related_stmt = $conn->prepare($related_sql);
$related_stmt->bind_param("ii", $product['category_id'], $product_id);
$related_stmt->execute();
$related_result = $related_stmt->get_result();
$related_products = [];
while ($row = $related_result->fetch_assoc()) {
    $main_image = getMainProductImage($conn, $row['id']);
    $row['main_image_url'] = $main_image ? $main_image['image_url'] : null;
    $row['is_video'] = $main_image ? $main_image['is_video'] : false;
    $related_products[] = $row;
}
$related_stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include 'header.php'; ?>
    <title><?php echo htmlspecialchars($product['name']); ?> | <?php echo htmlspecialchars($boutique_name); ?></title>
    <style>
    

        .product-detail-section {
            padding: 2rem 0;
        }

        @media (min-width: 768px) {
            .product-detail-section {
                padding: 3rem 0;
            }
        }

        .product-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
        }

        .product-layout {
            display: flex;
            flex-direction: column;
            gap: 2rem;
            margin-bottom: 3rem;
        }

        @media (min-width: 992px) {
            .product-layout {
                flex-direction: row;
                gap: 3rem;
            }
        }

        /* Product Gallery */
        .product-gallery {
            flex: 1;
            position: relative;
        }

        .main-image-container {
            position: relative;
            width: 100%;
            height: 300px;
            border-radius: var(--radius);
            overflow: hidden;
            background: var(--gray-100);
            margin-bottom: 1rem;
        }

        @media (min-width: 768px) {
            .main-image-container {
                height: 400px;
            }
        }

        @media (min-width: 992px) {
            .main-image-container {
                height: 500px;
            }
        }

        .main-media {
            width: 100%;
            height: 100%;
            object-fit: contain;
            background: white;
            display: block;
        }

        /* Video Styling */
        video.main-media {
            display: block;
            background: #000;
        }

        video.thumbnail-video {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* Image Navigation */
        .image-nav {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            z-index: 10;
            transition: var(--transition);
            border: 1px solid var(--gray-200);
        }

        .image-nav:hover {
            background: white;
            box-shadow: var(--shadow-md);
        }

        .image-nav.prev {
            left: 1rem;
        }

        .image-nav.next {
            right: 1rem;
        }

        .image-nav i {
            color: var(--dark);
            font-size: 1.25rem;
        }

        /* Thumbnail Gallery */
        .thumbnail-gallery {
            display: flex;
            gap: 0.5rem;
            overflow-x: auto;
            padding: 0.5rem 0;
            scrollbar-width: thin;
        }

        .thumbnail-item {
            flex: 0 0 auto;
            width: 70px;
            height: 70px;
            border-radius: var(--radius);
            overflow: hidden;
            cursor: pointer;
            border: 2px solid transparent;
            transition: var(--transition);
            background: var(--gray-100);
            position: relative;
        }

        @media (min-width: 768px) {
            .thumbnail-item {
                width: 80px;
                height: 80px;
            }
        }

        .thumbnail-item.active {
            border-color: var(--primary);
        }

        .thumbnail-item img,
        .thumbnail-item video {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .video-indicator {
            position: absolute;
            top: 5px;
            right: 5px;
            background: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 2px 5px;
            border-radius: 3px;
            font-size: 0.7rem;
            z-index: 2;
        }

        /* Product Info */
        .product-info {
            flex: 1;
            padding: 0;
        }

        @media (min-width: 992px) {
            .product-info {
                padding: 1rem;
            }
        }

        .product-title {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 0.5rem;
            font-family: 'Playfair Display', serif;
        }

        @media (min-width: 768px) {
            .product-title {
                font-size: 2.5rem;
            }
        }

        .product-category {
            color: var(--gray-600);
            font-size: 1rem;
            margin-bottom: 1rem;
        }

        .product-price {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 1.5rem;
        }

        @media (min-width: 768px) {
            .product-price {
                font-size: 2rem;
            }
        }

        .price-unit {
            font-size: 1rem;
            color: var(--gray-600);
            margin-left: 0.5rem;
        }

        /* Color Selection */
        .product-colors-section {
            margin-bottom: 1.5rem;
        }

        .section-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 1rem;
        }

        .color-options {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
        }

        .color-option {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: 2px solid var(--gray-300);
            cursor: pointer;
            transition: var(--transition);
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .color-option.active {
            border-color: var(--primary);
            transform: scale(1.1);
        }

        .color-option:hover {
            transform: scale(1.05);
        }

        .color-preview {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            border: 1px solid var(--gray-200);
        }

        /* Fix for white color preview */
        .color-preview[style*="#ffffff"],
        .color-preview[style*="#FFFFFF"],
        .color-preview[style*="#fff"],
        .color-preview[style*="#FFF"] {
            border: 2px solid var(--gray-400);
        }

        /* Stock Info */
        .stock-info {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
            padding: 1rem;
            background: var(--gray-100);
            border-radius: var(--radius);
        }

        .stock-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 600;
        }

        .in-stock {
            background: #D1FAE5;
            color: #065F46;
        }

        .low-stock {
            background: #FEF3C7;
            color: #92400E;
        }

        .out-of-stock {
            background: #FEE2E2;
            color: #DC2626;
        }

        /* Quantity/Meter Selector */
        .quantity-selector {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
        }

        .quantity-controls {
            display: flex;
            align-items: center;
            border: 1px solid var(--gray-300);
            border-radius: var(--radius);
            overflow: hidden;
        }

        .quantity-btn {
            width: 40px;
            height: 40px;
            background: var(--gray-100);
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            color: var(--gray-700);
            transition: var(--transition);
        }

        .quantity-btn:hover {
            background: var(--gray-200);
        }

        .quantity-input {
            width: 60px;
            height: 40px;
            border: none;
            text-align: center;
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--dark);
            -moz-appearance: textfield;
        }

        .quantity-input::-webkit-outer-spin-button,
        .quantity-input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        .meter-input {
            width: 80px;
        }

        .available-length {
            color: var(--gray-600);
            font-size: 0.875rem;
        }

        /* Stitching Option Styles */
        .stitching-option-section {
            margin-top: 1rem;
            padding: 1rem;
            background: white;
            border-radius: var(--radius);
            border: 1px solid var(--gray-200);
        }

        .stitching-options {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        @media (min-width: 640px) {
            .stitching-options {
                flex-direction: row;
            }
        }

        .stitching-option-label {
            flex: 1;
            padding: 1rem;
            background: var(--gray-100);
            border: 2px solid transparent;
            border-radius: var(--radius);
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
        }

        .stitching-option-label:hover {
            background: var(--gray-200);
            transform: translateY(-2px);
        }

        .stitching-option-label.active {
            background: rgba(139, 69, 19, 0.1);
            border-color: var(--primary);
            box-shadow: var(--shadow-sm);
        }

        .stitching-option-label input[type="radio"] {
            margin-top: 0.25rem;
            accent-color: var(--primary);
        }

        .stitching-option-content {
            flex: 1;
        }

        .stitching-option-title {
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 0.25rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .stitching-option-desc {
            font-size: 0.875rem;
            color: var(--gray-600);
        }

        .stitch-charges-badge {
            background: var(--primary);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .stitching-info {
            font-size: 0.875rem;
            color: var(--gray-600);
            margin-top: 0.5rem;
            padding-left: 0.25rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            margin-bottom: 2rem;
        }

        @media (min-width: 640px) {
            .action-buttons {
                flex-direction: row;
            }
        }

        .add-to-cart-btn-large {
            flex: 1;
            padding: 1rem 1.5rem;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: var(--radius);
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
        }

        @media (min-width: 768px) {
            .add-to-cart-btn-large {
                padding: 1rem 2rem;
                font-size: 1.125rem;
            }
        }

        .add-to-cart-btn-large:hover {
            background: var(--primary-light);
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .add-to-cart-btn-large:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
        }

        .wishlist-btn {
            padding: 1rem 1.5rem;
            background: white;
            color: var(--dark);
            border: 2px solid var(--gray-300);
            border-radius: var(--radius);
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .wishlist-btn:hover {
            border-color: var(--primary);
            color: var(--primary);
        }

        /* Product Details Tabs */
        .product-tabs {
            margin: 2rem 0;
        }

        @media (min-width: 768px) {
            .product-tabs {
                margin: 3rem 0;
            }
        }

        .tab-headers {
            display: flex;
            border-bottom: 1px solid var(--gray-200);
            margin-bottom: 1.5rem;
            overflow-x: auto;
        }

        .tab-header {
            padding: 1rem 1.5rem;
            background: none;
            border: none;
            font-size: 1rem;
            font-weight: 600;
            color: var(--gray-600);
            cursor: pointer;
            transition: var(--transition);
            position: relative;
            white-space: nowrap;
        }

        @media (min-width: 768px) {
            .tab-header {
                padding: 1rem 2rem;
                font-size: 1.125rem;
            }
        }

        .tab-header.active {
            color: var(--primary);
        }

        .tab-header.active::after {
            content: '';
            position: absolute;
            bottom: -1px;
            left: 0;
            right: 0;
            height: 3px;
            background: var(--primary);
        }

        .tab-content {
            padding: 1.5rem;
            background: white;
            border-radius: var(--radius);
            border: 1px solid var(--gray-200);
        }

        @media (min-width: 768px) {
            .tab-content {
                padding: 2rem;
            }
        }

        .tab-pane {
            display: none;
        }

        .tab-pane.active {
            display: block;
        }

        .description-content p {
            margin-bottom: 1rem;
            line-height: 1.7;
            color: var(--gray-700);
        }

        /* Size Chart Styles */
        .size-chart-container {
            overflow-x: auto;
            margin-bottom: 1.5rem;
        }

        .size-chart-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 400px;
        }

        .size-chart-table th,
        .size-chart-table td {
            padding: 0.75rem 1rem;
            text-align: center;
            border: 1px solid var(--gray-200);
        }

        .size-chart-table th {
            background: var(--primary);
            color: white;
            font-weight: 600;
            font-size: 0.875rem;
        }

        .size-chart-table td {
            font-size: 0.875rem;
            color: var(--gray-700);
        }

        .size-chart-table tr:nth-child(even) {
            background: var(--gray-50);
        }

        .size-chart-table tr:hover {
            background: var(--gray-100);
        }

        .size-unit {
            font-size: 0.75rem;
            color: var(--gray-500);
            margin-left: 0.25rem;
        }

        /* Related Products */
        .related-products {
            margin-top: 3rem;
            padding-top: 2rem;
            border-top: 1px solid var(--gray-200);
        }

        .related-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 1.5rem;
            text-align: center;
            font-family: 'Playfair Display', serif;
        }

        @media (min-width: 768px) {
            .related-title {
                font-size: 1.875rem;
                margin-bottom: 2rem;
            }
        }

        .related-grid {
            display: grid;
            grid-template-columns: repeat(1, 1fr);
            gap: 1.5rem;
        }

        @media (min-width: 640px) {
            .related-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (min-width: 1024px) {
            .related-grid {
                grid-template-columns: repeat(4, 1fr);
                gap: 2rem;
            }
        }

        .product-card {
            border: 1px solid var(--gray-200);
            border-radius: var(--radius);
            overflow: hidden;
            transition: var(--transition);
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
        }

        .product-image {
            position: relative;
            height: 200px;
            overflow: hidden;
        }

        .product-image img,
        .product-image video {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .product-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: var(--transition);
        }

        .product-card:hover .product-overlay {
            opacity: 1;
        }

        .view-btn {
            background: white;
            color: var(--dark);
            padding: 0.75rem 1.5rem;
            border-radius: var(--radius);
            text-decoration: none;
            font-weight: 600;
            transition: var(--transition);
        }

        .view-btn:hover {
            background: var(--primary);
            color: white;
        }

        .product-card-info {
            padding: 1rem;
        }

        .product-card-title {
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 0.5rem;
            font-size: 1rem;
        }

        .product-card-price {
            color: var(--primary);
            font-weight: 700;
            font-size: 1.125rem;
        }

        /* Mobile Notification */
        .mobile-notification {
            position: fixed;
            bottom: 1rem;
            left: 50%;
            transform: translateX(-50%) translateY(100px);
            background: var(--primary);
            color: white;
            padding: 1rem 1.5rem;
            border-radius: var(--radius);
            box-shadow: var(--shadow-lg);
            z-index: 9999;
            transition: transform 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            max-width: 90%;
        }

        .mobile-notification.show {
            transform: translateX(-50%) translateY(0);
        }

        /* Video Control Overlay */
        .video-control-overlay {
            position: absolute;
            bottom: 10px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 10px;
            z-index: 5;
        }

        .video-control-btn {
            background: rgba(0, 0, 0, 0.7);
            color: white;
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: var(--transition);
        }

        .video-control-btn:hover {
            background: rgba(0, 0, 0, 0.9);
            transform: scale(1.1);
        }

        /* Error Message */
        .error-message {
            color: var(--danger);
            font-size: 0.875rem;
            margin-top: 0.5rem;
            display: none;
        }

        .error-message.show {
            display: block;
        }
    </style>
</head>
<body>
    <!-- Header Section -->
    <?php include 'navigation.php'; ?>

    <!-- Product Detail Section -->
    <section class="product-detail-section">
        <div class="product-container">
            <div class="product-layout">
                <!-- Product Gallery -->
                <div class="product-gallery">
                    <div class="main-image-container" id="mainImageContainer">
                        <!-- Media will be loaded here by JavaScript -->
                        <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; background: var(--gray-100);">
                            <i class="fas fa-spinner fa-spin fa-2x" style="color: var(--gray-400);"></i>
                        </div>
                    </div>
                    
                    <div class="thumbnail-gallery" id="thumbnailGallery">
                        <!-- Thumbnails will be loaded here by JavaScript -->
                    </div>
                </div>

                <!-- Product Info -->
                <div class="product-info">
                    <h1 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h1>
                    <div class="product-category">
                        <?php echo htmlspecialchars($product['category_name'] ?? 'Uncategorized'); ?>
                    </div>
                    
                    <!-- Price Display -->
                    <div class="product-price" id="priceDisplay">
                        <?php if ($product['product_type'] === 'unstitched' && isset($product['price_per_meter'])): ?>
                            ₹<?php echo number_format($product['price_per_meter'], 2); ?>
                            <span class="price-unit">/ meter</span>
                            
                            <?php if ($product['stitch_charges'] > 0): ?>
                                <div class="stitching-info" style="margin-top: 0.25rem;">
                                    <i class="fas fa-scissors" style="color: var(--primary);"></i>
                                    Stitching available: + ₹<?php echo number_format($product['stitch_charges'], 2); ?>
                                </div>
                            <?php endif; ?>
                            
                            <div id="totalPriceContainer" style="font-size: 1.2rem; margin-top: 0.5rem;">
                                Total: ₹<span id="calculatedPrice"><?php echo number_format($product['price_per_meter'], 2); ?></span>
                            </div>
                        <?php else: ?>
                            ₹<?php echo number_format($product['price'], 2); ?>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Color Selection -->
                    <?php if (isset($product['colors']) && count($product['colors']) > 0): ?>
                    <div class="product-colors-section">
                        <h3 class="section-title">Select Color</h3>
                        <div class="color-options" id="colorOptions">
                            <?php 
                            $first_color_id = null;
                            foreach ($product['colors'] as $index => $color): 
                                if ($index === 0) $first_color_id = $color['id'];
                            ?>
                            <div class="color-option <?php echo $index == 0 ? 'active' : ''; ?>" 
                                 data-color-id="<?php echo $color['id']; ?>"
                                 data-color-index="<?php echo $index; ?>"
                                 onclick="selectColor(<?php echo $color['id']; ?>, <?php echo $index; ?>)"
                                 title="<?php echo htmlspecialchars($color['name']); ?>">
                                <div class="color-preview" style="background-color: <?php echo $color['hex_code']; ?>"></div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Stock Information -->
                    <div class="stock-info">
                        <?php if ($product['stock_quantity'] > 5): ?>
                            <div class="stock-badge in-stock">
                                <i class="fas fa-check-circle"></i> In Stock
                            </div>
                            <span>Available: <?php echo $product['stock_quantity']; ?> units</span>
                        <?php elseif ($product['stock_quantity'] > 0): ?>
                            <div class="stock-badge low-stock">
                                <i class="fas fa-exclamation-triangle"></i> Low Stock
                            </div>
                            <span>Only <?php echo $product['stock_quantity']; ?> left</span>
                        <?php else: ?>
                            <div class="stock-badge out-of-stock">
                                <i class="fas fa-times-circle"></i> Out of Stock
                            </div>
                            <span>Currently unavailable</span>
                        <?php endif; ?>
                    </div>

                    <!-- Quantity/Meter Selection with Stitching Option -->
                    <div class="quantity-selector">
                        <?php if ($product['product_type'] === 'unstitched'): ?>
                            <!-- Unstitched Product Section -->
                            <div style="display: flex; flex-direction: column; gap: 1.5rem; width: 100%;">
                                <!-- Length Selection -->
                                <div>
                                    <h3 class="section-title" style="margin-bottom: 0.75rem;">Select Length (meters)</h3>
                                    <div class="quantity-controls">
                                        <button class="quantity-btn" onclick="decreaseMeters()" type="button">-</button>
                                        <input type="number" class="quantity-input meter-input" id="meterInput" 
                                               value="1" min="0.1" step="0.1" 
                                               max="<?php echo $product['length_in_meters'] ?? 10; ?>"
                                               data-price-per-meter="<?php echo $product['price_per_meter'] ?? 0; ?>"
                                               data-max-length="<?php echo $product['length_in_meters'] ?? 10; ?>"
                                               onchange="calculateTotalPrice()">
                                        <button class="quantity-btn" onclick="increaseMeters()" type="button">+</button>
                                    </div>
                                    <div class="available-length">
                                        Available: <span id="availableMeters"><?php echo $product['length_in_meters'] ?? 10; ?></span> meters
                                    </div>
                                </div>

                                <!-- Stitching Option -->
                                <div class="stitching-option-section">
                                    <h3 class="section-title" style="margin-bottom: 0.75rem;">Stitching Option</h3>
                                    <div class="stitching-options">
                                        <!-- Unstitched Option -->
                                        <label class="stitching-option-label <?php echo 'active'; ?>" id="unstitchedOption">
                                            <input type="radio" name="stitching_option" value="unstitched" checked 
                                                   onchange="updateStitchingOption(this)">
                                            <div class="stitching-option-content">
                                                <div class="stitching-option-title">
                                                    Unstitched (Fabric Only)
                                                </div>
                                                <div class="stitching-option-desc">
                                                    Get fabric for custom stitching
                                                </div>
                                            </div>
                                        </label>

                                        <!-- Stitched Option -->
                                        <label class="stitching-option-label" id="stitchedOption">
                                            <input type="radio" name="stitching_option" value="stitched" 
                                                   onchange="updateStitchingOption(this)">
                                            <div class="stitching-option-content">
                                                <div class="stitching-option-title">
                                                    Stitched (Ready to Wear)
                                                    <?php if ($product['stitch_charges'] > 0): ?>
                                                        <span class="stitch-charges-badge">
                                                            + ₹<?php echo number_format($product['stitch_charges'], 2); ?>
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="stitching-option-desc">
                                                    Professionally stitched & ready to wear
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                    
                                    <!-- Stitch Charges Info -->
                                    <div class="stitching-info">
                                        <?php if ($product['stitch_charges'] > 0): ?>
                                            <i class="fas fa-info-circle" style="color: var(--primary);"></i>
                                            Stitching charges will be added to total price when "Stitched" option is selected
                                        <?php else: ?>
                                            <i class="fas fa-check-circle" style="color: var(--success);"></i>
                                            Free stitching service included
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                        <?php else: ?>
                            <!-- Stitched or Accessory Product -->
                            <div style="width: 100%;">
                                <h3 class="section-title" style="margin-bottom: 0.75rem;">Quantity</h3>
                                <div class="quantity-controls">
                                    <button class="quantity-btn" onclick="decreaseQuantity()" type="button">-</button>
                                    <input type="number" class="quantity-input" id="quantityInput" 
                                           value="1" min="1" 
                                           max="<?php echo min($product['stock_quantity'], 10); ?>"
                                           <?php echo $product['stock_quantity'] == 0 ? 'disabled' : ''; ?>>
                                    <button class="quantity-btn" onclick="increaseQuantity()" type="button">+</button>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Error Message for Meters -->
                    <div class="error-message" id="meterError"></div>

                    <!-- Action Buttons -->
                    <div class="action-buttons">
                        <button class="add-to-cart-btn-large" id="addToCartBtn"
                                data-product-id="<?php echo $product['id']; ?>"
                                data-product-type="<?php echo $product['product_type']; ?>"
                                <?php echo $product['stock_quantity'] == 0 ? 'disabled' : ''; ?>>
                            <i class="fas fa-shopping-bag"></i>
                            <?php echo $product['stock_quantity'] > 0 ? 'Add to Cart' : 'Out of Stock'; ?>
                        </button>
                        <button class="wishlist-btn" id="wishlistBtn">
                            <i class="far fa-heart"></i>
                            Add to Wishlist
                        </button>
                    </div>
                </div>
            </div>

            <!-- Product Details Tabs -->
            <div class="product-tabs">
                <div class="tab-headers">
                    <button class="tab-header active" onclick="switchTab('description')">Description</button>
                    <button class="tab-header" onclick="switchTab('specifications')">Specifications</button>
                    <?php if ($size_chart): ?>
                    <button class="tab-header" onclick="switchTab('size-chart')">Size Chart</button>
                    <?php endif; ?>
                    <button class="tab-header" onclick="switchTab('shipping')">Shipping & Returns</button>
                </div>
                
                <div class="tab-content">
                    <div class="tab-pane active" id="description-tab">
                        <div class="description-content">
                            <?php if (!empty($product['description'])): ?>
                                <?php echo nl2br(htmlspecialchars($product['description'])); ?>
                            <?php else: ?>
                                <p>This exquisite <?php echo htmlspecialchars($product['category_name'] ?? 'product'); ?> from <?php echo htmlspecialchars($boutique_name); ?> is crafted with premium materials and exceptional attention to detail.</p>
                                <p>Designed by <?php echo htmlspecialchars($designer_name); ?>, this piece showcases traditional craftsmanship blended with contemporary elegance.</p>
                                <p>Perfect for special occasions and everyday wear alike.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="tab-pane" id="specifications-tab">
                        <table class="specs-table">
                            <tr>
                                <td style="padding: 1rem; font-weight: 600; width: 150px;">Product Type</td>
                                <td style="padding: 1rem;"><?php echo ucfirst($product['product_type']); ?></td>
                            </tr>
                            <tr>
                                <td style="padding: 1rem; font-weight: 600;">Category</td>
                                <td style="padding: 1rem;"><?php echo htmlspecialchars($product['category_name'] ?? 'Not specified'); ?></td>
                            </tr>
                            <?php if ($product['product_type'] === 'unstitched'): ?>
                            <tr>
                                <td style="padding: 1rem; font-weight: 600;">Price per Meter</td>
                                <td style="padding: 1rem;">₹<?php echo number_format($product['price_per_meter'] ?? 0, 2); ?></td>
                            </tr>
                            <tr>
                                <td style="padding: 1rem; font-weight: 600;">Available Length</td>
                                <td style="padding: 1rem;"><?php echo $product['length_in_meters'] ?? 0; ?> meters</td>
                            </tr>
                            <?php if ($product['stitch_charges'] > 0): ?>
                            <tr>
                                <td style="padding: 1rem; font-weight: 600;">Stitch Charges</td>
                                <td style="padding: 1rem;">₹<?php echo number_format($product['stitch_charges'], 2); ?> (Optional)</td>
                            </tr>
                            <?php endif; ?>
                            <?php endif; ?>
                            <tr>
                                <td style="padding: 1rem; font-weight: 600;">Colors Available</td>
                                <td style="padding: 1rem;">
                                    <?php 
                                    $color_names = [];
                                    if (isset($product['colors'])) {
                                        foreach ($product['colors'] as $color) {
                                            $color_names[] = htmlspecialchars($color['name']);
                                        }
                                    }
                                    echo implode(', ', $color_names);
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding: 1rem; font-weight: 600;">Stock Status</td>
                                <td style="padding: 1rem;">
                                    <?php if ($product['stock_quantity'] > 5): ?>
                                        <span style="color: #065F46;">In Stock</span>
                                    <?php elseif ($product['stock_quantity'] > 0): ?>
                                        <span style="color: #92400E;">Low Stock (<?php echo $product['stock_quantity']; ?> left)</span>
                                    <?php else: ?>
                                        <span style="color: #DC2626;">Out of Stock</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        </table>
                    </div>
                    
                    <?php if ($size_chart): ?>
                    <div class="tab-pane" id="size-chart-tab">
                        <h3><?php echo htmlspecialchars($size_chart['chart_name']); ?></h3>
                        
                        <?php if (!empty($size_chart['measurements'])): ?>
                        <div class="size-chart-container">
                            <table class="size-chart-table">
                                <thead>
                                    <tr>
                                        <th>Size</th>
                                        <?php 
                                        // Get measurement types from first size
                                        $first_size = reset($size_chart['measurements']);
                                        if ($first_size && is_array($first_size)) {
                                            foreach (array_keys($first_size) as $measurement): ?>
                                            <th><?php echo ucfirst($measurement); ?> <span class="size-unit">(in)</span></th>
                                            <?php endforeach;
                                        }
                                        ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($size_chart['measurements'] as $size => $measurements): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($size); ?></strong></td>
                                        <?php 
                                        if (is_array($measurements)) {
                                            foreach ($measurements as $value): ?>
                                            <td><?php echo htmlspecialchars($value); ?></td>
                                            <?php endforeach;
                                        }
                                        ?>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php else: ?>
                        <div class="no-size-chart">
                            <i class="fas fa-ruler-combined"></i>
                            <h3>Size Chart Not Available</h3>
                            <p>No size measurements available for this chart.</p>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                    
                    <div class="tab-pane" id="shipping-tab">
                        <h3>Shipping Information</h3>
                        <p>We offer worldwide shipping. Most orders are processed within 1-2 business days and delivered within 5-7 business days.</p>
                        <p>Free shipping on orders above ₹5000.</p>
                        
                        <h3>Returns & Exchanges</h3>
                        <p>We accept returns within 14 days of delivery for unworn, unwashed items with original tags attached.</p>
                        <p>Customized items cannot be returned unless damaged or defective.</p>
                    </div>
                </div>
            </div>

            <!-- Related Products -->
            <?php if (!empty($related_products)): ?>
            <div class="related-products">
                <h2 class="related-title">You May Also Like</h2>
                <div class="related-grid">
                    <?php foreach ($related_products as $related): ?>
                    <div class="product-card">
                        <div class="product-image">
                            <?php if ($related['main_image_url']): ?>
                                <?php if ($related['is_video']): ?>
                                <video src="<?php echo htmlspecialchars($related['main_image_url']); ?>" 
                                       muted loop playsinline>
                                </video>
                                <?php else: ?>
                                <img src="<?php echo htmlspecialchars($related['main_image_url']); ?>" 
                                     alt="<?php echo htmlspecialchars($related['name']); ?>">
                                <?php endif; ?>
                            <?php else: ?>
                                <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; background: var(--gray-100);">
                                    <i class="fas fa-image" style="color: var(--gray-300);"></i>
                                </div>
                            <?php endif; ?>
                            <div class="product-overlay">
                                <div class="overlay-buttons">
                                    <a href="product.php?id=<?php echo $related['id']; ?>" class="view-btn">
                                        <i class="fas fa-eye"></i>
                                        View
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="product-card-info">
                            <h3 class="product-card-title"><?php echo htmlspecialchars($related['name']); ?></h3>
                            <div class="product-card-price">₹<?php echo number_format($related['price'], 2); ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Footer Section -->
    <?php include 'footer.php'; ?>

    <!-- Mobile Notification -->
    <div class="mobile-notification" id="mobileNotification">
        <i class="fas fa-check-circle"></i>
        <span id="notificationMessage">Item added to cart!</span>
    </div>

    <script>
        // Product data from PHP
        const productData = <?php echo json_encode($product); ?>;
        const productColors = productData.colors || [];
        
        // Media data from PHP - organized by color
        const productMedia = <?php echo json_encode($product_media); ?>;
        
        // Stitching configuration
        const stitchCharges = <?php echo ($product['product_type'] === 'unstitched' && isset($product['stitch_charges'])) ? $product['stitch_charges'] : 0; ?>;
        
        let currentColorIndex = 0;
        let currentMediaIndex = 0;
        let currentColorMedia = [];
        let currentColorId = null;
        let selectedStitchingOption = 'unstitched';
        
        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            updateCartCount();
            
            // Load first color's media
            if (productColors.length > 0) {
                currentColorId = productColors[0].id;
                loadColorMedia(currentColorId);
            }
            
            // Calculate initial price for unstitched products
            if (productData.product_type === 'unstitched') {
                calculateTotalPrice();
            }
            
            // Initialize stitching option styling
            const unstitchedOption = document.getElementById('unstitchedOption');
            if (unstitchedOption) {
                unstitchedOption.classList.add('active');
            }
            
            // Add event listeners
            document.getElementById('addToCartBtn').addEventListener('click', addToCartHandler);
            document.getElementById('wishlistBtn').addEventListener('click', addToWishlistHandler);
            
            // Add change event for meter input
            const meterInput = document.getElementById('meterInput');
            if (meterInput) {
                meterInput.addEventListener('input', calculateTotalPrice);
            }
        });
        
        // Color selection
        function selectColor(colorId, colorIndex) {
            currentColorIndex = colorIndex;
            currentColorId = colorId;
            loadColorMedia(colorId);
            
            // Update active color in UI
            document.querySelectorAll('.color-option').forEach(option => {
                option.classList.toggle('active', option.dataset.colorId == colorId);
            });
        }
        
        // Load media for selected color
        function loadColorMedia(colorId) {
            if (productMedia[colorId]) {
                currentColorMedia = productMedia[colorId].media || [];
            } else {
                currentColorMedia = [];
            }
            
            currentMediaIndex = 0;
            updateMainMedia();
            updateThumbnails();
        }
        
        // Update main media display
        function updateMainMedia() {
            const container = document.getElementById('mainImageContainer');
            if (!container) return;
            
            container.innerHTML = '';
            
            if (currentColorMedia.length === 0) {
                // No media available
                container.innerHTML = `
                    <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; background: var(--gray-100);">
                        <i class="fas fa-image fa-4x" style="color: var(--gray-300);"></i>
                    </div>
                `;
                return;
            }
            
            const media = currentColorMedia[currentMediaIndex];
            const isVideo = media.is_video == 1;
            
            if (isVideo) {
                container.innerHTML = `
                    <video class="main-media" id="mainMedia" 
                           src="${media.url}" 
                           controls autoplay muted loop playsinline>
                    </video>
                    <div class="video-control-overlay">
                        <button class="video-control-btn" onclick="togglePlay()">
                            <i class="fas fa-play" id="playIcon"></i>
                        </button>
                        <button class="video-control-btn" onclick="toggleMute()">
                            <i class="fas fa-volume-up" id="muteIcon"></i>
                        </button>
                        <button class="video-control-btn" onclick="toggleFullscreen()">
                            <i class="fas fa-expand"></i>
                        </button>
                    </div>
                    ${currentColorMedia.length > 1 ? `
                    <div class="image-nav prev" onclick="prevMedia()">
                        <i class="fas fa-chevron-left"></i>
                    </div>
                    <div class="image-nav next" onclick="nextMedia()">
                        <i class="fas fa-chevron-right"></i>
                    </div>
                    ` : ''}
                `;
            } else {
                container.innerHTML = `
                    <img class="main-media" id="mainMedia" 
                         src="${media.url}" 
                         alt="${productData.name}">
                    ${currentColorMedia.length > 1 ? `
                    <div class="image-nav prev" onclick="prevMedia()">
                        <i class="fas fa-chevron-left"></i>
                    </div>
                    <div class="image-nav next" onclick="nextMedia()">
                        <i class="fas fa-chevron-right"></i>
                    </div>
                    ` : ''}
                `;
            }
        }
        
        // Update thumbnail gallery
        function updateThumbnails() {
            const gallery = document.getElementById('thumbnailGallery');
            if (!gallery) return;
            
            gallery.innerHTML = '';
            
            if (currentColorMedia.length === 0) {
                return;
            }
            
            currentColorMedia.forEach((media, index) => {
                const thumb = document.createElement('div');
                thumb.className = `thumbnail-item ${index === currentMediaIndex ? 'active' : ''}`;
                thumb.setAttribute('data-index', index);
                thumb.onclick = () => showMedia(index);
                
                if (media.is_video == 1) {
                    thumb.innerHTML = `
                        <video class="thumbnail-video" src="${media.url}" muted playsinline></video>
                        <div class="video-indicator">
                            <i class="fas fa-play"></i>
                        </div>
                    `;
                } else {
                    thumb.innerHTML = `<img src="${media.url}" alt="Thumbnail ${index + 1}">`;
                }
                
                gallery.appendChild(thumb);
            });
        }
        
        // Media navigation
        function showMedia(index) {
            currentMediaIndex = index;
            updateMainMedia();
            updateThumbnails();
        }
        
        function prevMedia() {
            if (currentMediaIndex > 0) {
                currentMediaIndex--;
            } else {
                currentMediaIndex = currentColorMedia.length - 1;
            }
            updateMainMedia();
            updateThumbnails();
        }
        
        function nextMedia() {
            if (currentMediaIndex < currentColorMedia.length - 1) {
                currentMediaIndex++;
            } else {
                currentMediaIndex = 0;
            }
            updateMainMedia();
            updateThumbnails();
        }
        
        // Video controls
        function togglePlay() {
            const video = document.getElementById('mainMedia');
            const playIcon = document.getElementById('playIcon');
            
            if (video && video.tagName === 'VIDEO') {
                if (video.paused) {
                    video.play();
                    if (playIcon) playIcon.className = 'fas fa-pause';
                } else {
                    video.pause();
                    if (playIcon) playIcon.className = 'fas fa-play';
                }
            }
        }
        
        function toggleMute() {
            const video = document.getElementById('mainMedia');
            const muteIcon = document.getElementById('muteIcon');
            
            if (video && video.tagName === 'VIDEO') {
                video.muted = !video.muted;
                if (muteIcon) {
                    muteIcon.className = video.muted ? 'fas fa-volume-mute' : 'fas fa-volume-up';
                }
            }
        }
        
        function toggleFullscreen() {
            const video = document.getElementById('mainMedia');
            if (video) {
                if (video.requestFullscreen) {
                    video.requestFullscreen();
                } else if (video.webkitRequestFullscreen) {
                    video.webkitRequestFullscreen();
                } else if (video.msRequestFullscreen) {
                    video.msRequestFullscreen();
                }
            }
        }
        
        // Tab switching
        function switchTab(tabName) {
            // Update tab headers
            document.querySelectorAll('.tab-header').forEach(header => {
                header.classList.remove('active');
            });
            event.currentTarget.classList.add('active');
            
            // Update tab content
            document.querySelectorAll('.tab-pane').forEach(pane => {
                pane.classList.remove('active');
            });
            document.getElementById(tabName + '-tab').classList.add('active');
        }
        
        // Quantity controls
        function decreaseQuantity() {
            const input = document.getElementById('quantityInput');
            if (parseInt(input.value) > 1) {
                input.value = parseInt(input.value) - 1;
            }
        }
        
        function increaseQuantity() {
            const input = document.getElementById('quantityInput');
            const max = parseInt(input.max);
            if (parseInt(input.value) < max) {
                input.value = parseInt(input.value) + 1;
            }
        }
        
        // Meter controls for unstitched products
        function decreaseMeters() {
            const input = document.getElementById('meterInput');
            const min = parseFloat(input.min) || 0.1;
            const step = parseFloat(input.step) || 0.1;
            const current = parseFloat(input.value) || 0;
            
            if (current - step >= min) {
                input.value = (current - step).toFixed(1);
                calculateTotalPrice();
            }
        }
        
        function increaseMeters() {
            const input = document.getElementById('meterInput');
            const max = parseFloat(input.dataset.maxLength) || 10;
            const step = parseFloat(input.step) || 0.1;
            const current = parseFloat(input.value) || 0;
            
            if (current + step <= max) {
                input.value = (current + step).toFixed(1);
                calculateTotalPrice();
            }
        }
        
        // Stitching option update
        function updateStitchingOption(radio) {
            selectedStitchingOption = radio.value;
            
            // Update visual styling
            document.querySelectorAll('.stitching-option-label').forEach(label => {
                label.classList.remove('active');
            });
            radio.parentElement.classList.add('active');
            
            calculateTotalPrice();
        }
        
        // Calculate total price for unstitched products
        function calculateTotalPrice() {
            const input = document.getElementById('meterInput');
            if (!input) return;
            
            const meters = parseFloat(input.value) || 0;
            const pricePerMeter = parseFloat(input.dataset.pricePerMeter) || 0;
            const maxLength = parseFloat(input.dataset.maxLength) || 10;
            
            // Validate meter length
            const errorDiv = document.getElementById('meterError');
            
            if (meters > maxLength) {
                if (errorDiv) {
                    errorDiv.textContent = `Cannot select more than ${maxLength} meters. Available: ${maxLength} meters`;
                    errorDiv.classList.add('show');
                }
                input.value = maxLength.toFixed(1);
                calculateTotalPrice(); // Recalculate with corrected value
                return;
            }
            
            // Clear error if valid
            if (errorDiv) {
                errorDiv.classList.remove('show');
            }
            
            // Calculate base price
            let totalPrice = meters * pricePerMeter;
            
            // Add stitch charges if selected
            if (selectedStitchingOption === 'stitched') {
                totalPrice += stitchCharges;
            }
            
            // Update display
            const priceDisplay = document.getElementById('calculatedPrice');
            if (priceDisplay) {
                priceDisplay.textContent = totalPrice.toFixed(2);
            }
        }
        
        // Add to cart handler
        function addToCartHandler() {
            const btn = document.getElementById('addToCartBtn');
            const productId = btn.dataset.productId;
            const productType = btn.dataset.productType;
            const productName = document.querySelector('.product-title').textContent;
            
            // Get selected color
            const selectedColor = productColors[currentColorIndex]?.name || '';
            
            // Get media URL for cart
            const mainMedia = document.getElementById('mainMedia');
            const mediaUrl = mainMedia ? mainMedia.src : '';
            
            let quantity = 1;
            let price = 0;
            
            // Get quantity/meters based on product type
            if (productType === 'unstitched') {
                const meterInput = document.getElementById('meterInput');
                quantity = parseFloat(meterInput.value) || 1;
                const pricePerMeter = parseFloat(meterInput.dataset.pricePerMeter) || 0;
                price = quantity * pricePerMeter;
                
                // Add stitch charges if selected
                if (selectedStitchingOption === 'stitched') {
                    price += stitchCharges;
                }
                
                // Validate meter length
                const maxLength = parseFloat(meterInput.dataset.maxLength) || 0;
                if (quantity > maxLength) {
                    showNotification(`Cannot add more than ${maxLength} meters to cart`, 'error');
                    return;
                }
            } else {
                quantity = parseInt(document.getElementById('quantityInput').value) || 1;
                price = parseFloat(productData.price) * quantity;
            }
            
            // Validate stock
            if (productData.stock_quantity < quantity) {
                showNotification(`Only ${productData.stock_quantity} units available`, 'error');
                return;
            }
            
            // Prepare cart item
            const cartItem = {
                id: productId,
                name: productName,
                price: price,
                image: mediaUrl,
                quantity: quantity,
                color: selectedColor,
                productType: productType,
                isUnstitched: productType === 'unstitched',
                selectedMeters: productType === 'unstitched' ? quantity : null,
                stitchingOption: productType === 'unstitched' ? selectedStitchingOption : null,
                stitchCharges: productType === 'unstitched' && selectedStitchingOption === 'stitched' ? stitchCharges : 0
            };
            
            // Add to cart
            addToCart(cartItem);
            
            // Show success notification
            const unitText = productType === 'unstitched' ? 'meters' : 'units';
            let notificationMsg = `${productName} (${quantity} ${unitText}) added to cart!`;
            
            if (productType === 'unstitched' && selectedStitchingOption === 'stitched') {
                notificationMsg += ' (Stitched option selected)';
            }
            
            showNotification(notificationMsg, 'success');
            
            // Update cart count
            updateCartCount();
            
            // Button feedback
            const originalHTML = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-check"></i> Added to Cart';
            btn.disabled = true;
            
            setTimeout(() => {
                btn.innerHTML = originalHTML;
                btn.disabled = productData.stock_quantity == 0;
            }, 2000);
        }
        
        // Add to wishlist handler
        function addToWishlistHandler() {
            const productName = document.querySelector('.product-title').textContent;
            showNotification(`${productName} added to wishlist!`, 'success');
            
            const btn = document.getElementById('wishlistBtn');
            const originalHTML = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-heart"></i> Added to Wishlist';
            
            setTimeout(() => {
                btn.innerHTML = originalHTML;
            }, 2000);
        }
        // --- REPLACE THE EXISTING addToCart FUNCTION ---
function addToCart(item) {
    // First, send an AJAX request to update the server-side cart
    fetch('cart_handler.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'add',
            id: item.id,
            quantity: item.quantity,
            name: item.name,
            price: item.price,
            image: item.image,
            color: item.color, // Include color if relevant
            productType: item.productType, // Include type if relevant
            isUnstitched: item.isUnstitched,
            selectedMeters: item.selectedMeters,
            stitchingOption: item.stitchingOption,
            stitchCharges: item.stitchCharges
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update localStorage with the server's cart state (optional)
            // localStorage.setItem('cart', JSON.stringify(data.cart));
            // Or, just update local storage optimistically and rely on sync via event
            let cart = JSON.parse(localStorage.getItem('cart')) || [];

            // Create unique key for cart item (considering stitching option for unstitched items)
            const itemKey = item.isUnstitched ?
                `${item.id}-${item.color}-${item.stitchingOption}` :
                `${item.id}-${item.color}`;

            // Check if item already exists in cart
            const existingIndex = cart.findIndex(cartItem => {
                if (cartItem.isUnstitched) {
                    return cartItem.id === item.id &&
                           cartItem.color === item.color &&
                           cartItem.stitchingOption === item.stitchingOption;
                } else {
                    return cartItem.id === item.id && cartItem.color === item.color;
                }
            });

            if (existingIndex > -1) {
                // Update quantity
                cart[existingIndex].quantity += item.quantity;
            } else {
                // Add new item
                cart.push(item);
            }
            localStorage.setItem('cart', JSON.stringify(cart));

            // Dispatch the event to notify navigation.php to update its display
            window.dispatchEvent(new CustomEvent('cartUpdated'));
            console.log("Cart updated via AJAX and event dispatched.");
            
            // Show notification
            showNotification(item.name + " added to cart!");
        } else {
            console.error("Server error:", data.message);
            // Fallback: update localStorage only if server fails
            let cart = JSON.parse(localStorage.getItem('cart')) || [];

            const itemKey = item.isUnstitched ?
                `${item.id}-${item.color}-${item.stitchingOption}` :
                `${item.id}-${item.color}`;

            const existingIndex = cart.findIndex(cartItem => {
                if (cartItem.isUnstitched) {
                    return cartItem.id === item.id &&
                           cartItem.color === item.color &&
                           cartItem.stitchingOption === item.stitchingOption;
                } else {
                    return cartItem.id === item.id && cartItem.color === item.color;
                }
            });

            if (existingIndex > -1) {
                cart[existingIndex].quantity += item.quantity;
            } else {
                cart.push(item);
            }
            localStorage.setItem('cart', JSON.stringify(cart));
            updateCartCount(); // Update local count
            showNotification(item.name + " added to cart!");
        }
    })
    .catch(error => {
        console.error("AJAX error:", error);
        // Fallback: update localStorage only if request fails
        let cart = JSON.parse(localStorage.getItem('cart')) || [];

        const itemKey = item.isUnstitched ?
            `${item.id}-${item.color}-${item.stitchingOption}` :
            `${item.id}-${item.color}`;

        const existingIndex = cart.findIndex(cartItem => {
            if (cartItem.isUnstitched) {
                return cartItem.id === item.id &&
                       cartItem.color === item.color &&
                       cartItem.stitchingOption === item.stitchingOption;
            } else {
                return cartItem.id === item.id && cartItem.color === item.color;
            }
        });

        if (existingIndex > -1) {
            cart[existingIndex].quantity += item.quantity;
        } else {
            cart.push(item);
        }
        localStorage.setItem('cart', JSON.stringify(cart));
        updateCartCount(); // Update local count
        showNotification(item.name + " added to cart!");
    });
}
// --- END REPLACE ---
        
       // Shared cart sync for all pages
function updateCartCount() {
    fetch('/cart_handler.php?action=get_cart')
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
        // Show notification
        function showNotification(message, type = 'success') {
            const notification = document.getElementById('mobileNotification');
            const messageSpan = document.getElementById('notificationMessage');
            
            // Update message and style
            messageSpan.textContent = message;
            notification.style.background = type === 'success' ? 'var(--primary)' : 'var(--danger)';
            
            // Show notification
            notification.classList.add('show');
            
            // Hide after 3 seconds
            setTimeout(() => {
                notification.classList.remove('show');
            }, 3000);
        }
    </script>
</body>
</html>
<?php
$conn->close();
?>