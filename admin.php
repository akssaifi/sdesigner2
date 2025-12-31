<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

require_once 'config.php';
// Include functions if not already loaded
if (!function_exists('getStockStatistics')) {
    require_once 'functions.php';
}
// Authentication check
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: login.php');
    exit;
}

// Get current section
$currentSection = $_GET['section'] ?? 'dashboard';
$action = $_GET['action'] ?? 'list';
$id = intval($_GET['id'] ?? 0);

// Initialize messages
$message = '';
$error = '';

// Get settings for dynamic owner name
$owner_name = getSetting($conn, 'designer_name') ?? 'Dinky Ahuja';
$boutique_name = getSetting($conn, 'boutique_name') ?? 'SDesigner';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Add/Edit Product - UPDATED: No description, separate image/video handling
    if (isset($_POST['save_product'])) {
        $product_type = $_POST['product_type'];
        $name = trim($_POST['name']);
        $price = floatval($_POST['price']);
        $category_id = intval($_POST['category_id']);
        $size_chart_id = !empty($_POST['size_chart_id']) ? intval($_POST['size_chart_id']) : NULL;
        $stock_quantity = intval($_POST['stock_quantity']);
        $reorder_level = intval($_POST['reorder_level']);
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        $colors = $_POST['colors'] ?? [];
        $meta_tags = $_POST['meta_tags'] ?? [];
        $product_id = intval($_POST['product_id'] ?? 0);

        // New: Handle description field
        $description = trim($_POST['description'] ?? '');

        // New: Handle unstitched specific fields - only for unstitched products
        $price_per_meter = NULL;
        $length_in_meters = NULL;

        // In form processing section, after getting other fields:
        $stitch_charges = NULL;

        if ($product_type === 'unstitched') {
            $price_per_meter = isset($_POST['price_per_meter']) ? floatval($_POST['price_per_meter']) : NULL;
            $length_in_meters = isset($_POST['length_in_meters']) ? floatval($_POST['length_in_meters']) : NULL;
            $stitch_charges = isset($_POST['stitch_charges']) ? floatval($_POST['stitch_charges']) : 0;  // ADD THIS
        }

        // Validate required fields
        if (empty($name) || empty($price) || $category_id <= 0) {
            $error = "Please fill in all required fields (Name, Price, Category)";
        } elseif (empty($colors)) {
            $error = "Please select at least one color for the product";
        } else {
            // Auto-deactivate when stock is zero
            if ($stock_quantity == 0) {
                $is_active = 0;
            }

            try {
                // Start transaction
                $conn->begin_transaction();

                if ($product_id > 0) {
                    $stmt = $conn->prepare("UPDATE products SET 
    product_type = ?, 
    name = ?, 
    description = ?,
    price = ?, 
    price_per_meter = ?,
    length_in_meters = ?,
    stitch_charges = ?,  
    category_id = ?, 
                    size_chart_id = ?, 
                    stock_quantity = ?, 
                    reorder_level = ?, 
                    is_active = ?,
                    stock_status = CASE 
                        WHEN stock_quantity = 0 THEN 'out_of_stock'
                        WHEN stock_quantity <= reorder_level THEN 'low_stock'
                        ELSE 'in_stock'
                    END
                    WHERE id = ?");

                    $stmt->bind_param(
                        "sssdddiiiisii",  // Changed from "sssddiiiisii" to include extra 'd' for stitch_charges
                        $product_type,
                        $name,
                        $description,
                        $price,
                        $price_per_meter,
                        $length_in_meters,
                        $stitch_charges,  // ADD THIS
                        $category_id,
                        $size_chart_id,
                        $stock_quantity,
                        $reorder_level,
                        $is_active,
                        $product_id
                    );
                } else {
                    // Insert new product
                    $stmt = $conn->prepare("INSERT INTO products (
                    product_type, name, description, price, 
                    price_per_meter, length_in_meters,
                    category_id, size_chart_id, stock_quantity, 
                    reorder_level, is_active, stock_status
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 
                    CASE 
                        WHEN ? = 0 THEN 'out_of_stock'
                        WHEN ? <= ? THEN 'low_stock'
                        ELSE 'in_stock'
                    END)");

                    $stmt->bind_param(
                        "sssddiiiiiiiii",
                        $product_type,
                        $name,
                        $description,
                        $price,
                        $price_per_meter,
                        $length_in_meters,
                        $category_id,
                        $size_chart_id,
                        $stock_quantity,
                        $reorder_level,
                        $is_active,
                        $stock_quantity, // For the CASE condition
                        $stock_quantity, // For the CASE condition
                        $reorder_level  // For the CASE condition
                    );
                }

                if ($stmt->execute()) {
                    $product_id = $product_id ?: $conn->insert_id;
                    $stmt->close();

                    // Update product colors
                    $conn->query("DELETE FROM product_colors WHERE product_id = $product_id");
                    if (!empty($colors)) {
                        $color_stmt = $conn->prepare("INSERT INTO product_colors (product_id, color_id) VALUES (?, ?)");
                        foreach ($colors as $color_id) {
                            $color_stmt->bind_param("ii", $product_id, $color_id);
                            $color_stmt->execute();
                        }
                        $color_stmt->close();
                    }

                    // Update meta tags
                    saveProductMetaTags($conn, $product_id, $meta_tags);

                    // Handle media uploads for each color
                    foreach ($colors as $color_id) {
                        // Handle image uploads
                        if (isset($_FILES['color_images_' . $color_id]) && !empty($_FILES['color_images_' . $color_id]['name'][0])) {
                            $upload_dir = 'uploads/products/';

                            $file_count = count($_FILES['color_images_' . $color_id]['name']);
                            for ($i = 0; $i < $file_count; $i++) {
                                if ($_FILES['color_images_' . $color_id]['error'][$i] === 0) {
                                    $file_name = $_FILES['color_images_' . $color_id]['name'][$i];
                                    $file_tmp = $_FILES['color_images_' . $color_id]['tmp_name'][$i];
                                    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                                    $allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

                                    if (in_array($file_ext, $allowed_ext)) {
                                        $unique_name = uniqid() . '_product_' . $product_id . '_color_' . $color_id . '.' . $file_ext;
                                        $target_file = $upload_dir . $unique_name;

                                        // Check if directory exists, create if not
                                        if (!is_dir($upload_dir)) {
                                            mkdir($upload_dir, 0777, true);
                                        }

                                        if (move_uploaded_file($file_tmp, $target_file)) {
                                            // Insert image record
                                            $image_stmt = $conn->prepare("
                            INSERT INTO product_color_images 
                            (product_id, color_id, image_url, is_video, display_order, is_main_image) 
                            VALUES (?, ?, ?, 0, ?, ?)
                        ");
                                            // Check if this is the first media item for this color
                                            $check_main = $conn->query("SELECT COUNT(*) as count FROM product_color_images WHERE product_id = $product_id AND color_id = $color_id");
                                            $count_result = $check_main->fetch_assoc();
                                            $is_main = ($count_result['count'] == 0) ? 1 : 0;

                                            $display_order = $i + 1;
                                            $image_stmt->bind_param("iisii", $product_id, $color_id, $target_file, $display_order, $is_main);
                                            $image_stmt->execute();
                                            $image_stmt->close();
                                        }
                                    }
                                }
                            }
                        }

                        // Handle video uploads
                        if (isset($_FILES['color_videos_' . $color_id]) && !empty($_FILES['color_videos_' . $color_id]['name'][0])) {
                            $upload_dir = 'uploads/products/';
                            $file_count = count($_FILES['color_videos_' . $color_id]['name']);

                            for ($i = 0; $i < $file_count; $i++) {
                                if ($_FILES['color_videos_' . $color_id]['error'][$i] === 0) {
                                    $file_name = $_FILES['color_videos_' . $color_id]['name'][$i];
                                    $file_tmp = $_FILES['color_videos_' . $color_id]['tmp_name'][$i];
                                    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                                    $allowed_ext = ['mp4', 'mov', 'avi', 'webm'];

                                    if (in_array($file_ext, $allowed_ext)) {
                                        $unique_name = uniqid() . '_product_' . $product_id . '_color_' . $color_id . '.' . $file_ext;
                                        $target_file = $upload_dir . $unique_name;

                                        // Check if directory exists, create if not
                                        if (!is_dir($upload_dir)) {
                                            mkdir($upload_dir, 0777, true);
                                        }

                                        if (move_uploaded_file($file_tmp, $target_file)) {
                                            // Insert video record
                                            $video_stmt = $conn->prepare("
                            INSERT INTO product_color_images 
                            (product_id, color_id, image_url, is_video, display_order, is_main_image) 
                            VALUES (?, ?, ?, 1, ?, ?)
                        ");
                                            // Check if this is the first media item for this color
                                            $check_main = $conn->query("SELECT COUNT(*) as count FROM product_color_images WHERE product_id = $product_id AND color_id = $color_id");
                                            $count_result = $check_main->fetch_assoc();
                                            $is_main = ($count_result['count'] == 0) ? 1 : 0;

                                            $display_order = $i + 1;
                                            $video_stmt->bind_param("iisii", $product_id, $color_id, $target_file, $display_order, $is_main);
                                            $video_stmt->execute();
                                            $video_stmt->close();
                                        }
                                    }
                                }
                            }
                        }
                    }

                    $conn->commit();
                    $message = "Product " . ($product_id > 0 ? "updated" : "added") . " successfully!";

                    // Redirect to prevent form resubmission
                    header("Location: ?section=$currentSection&action=edit&id=$product_id&msg=" . urlencode($message));
                    exit;

                } else {
                    $conn->rollback();
                    $error = "Error saving product: " . $conn->error;
                }
            } catch (Exception $e) {
                $conn->rollback();
                $error = "Database error: " . $e->getMessage();
            }
        }
    }

    // Add/Edit Category - UPDATED: Added icon field
    elseif (isset($_POST['save_category'])) {
        $category_id = intval($_POST['category_id'] ?? 0);
        $category_name = trim($_POST['category_name']);
        $category_slug = trim($_POST['category_slug']);
        $category_icon = trim($_POST['category_icon']);

        if (empty($category_slug)) {
            $category_slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $category_name));
        }

        if ($category_id > 0) {
            $stmt = $conn->prepare("UPDATE categories SET name = ?, slug = ?, icon = ? WHERE id = ?");
            $stmt->bind_param("sssi", $category_name, $category_slug, $category_icon, $category_id);
            if ($stmt->execute()) {
                $message = "Category updated successfully!";
            } else {
                $error = "Error updating category: " . $conn->error;
            }
            $stmt->close();
        } else {
            $stmt = $conn->prepare("INSERT INTO categories (name, slug, icon) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $category_name, $category_slug, $category_icon);
            if ($stmt->execute()) {
                $message = "Category added successfully!";
            } else {
                $error = "Error adding category: " . $conn->error;
            }
            $stmt->close();
        }
    }

    // Add/Edit Meta Tag
    elseif (isset($_POST['save_meta_tag'])) {
        $meta_tag_id = intval($_POST['meta_tag_id'] ?? 0);
        $meta_tag_name = trim($_POST['meta_tag_name']);
        $meta_tag_slug = trim($_POST['meta_tag_slug']);
        $meta_tag_type = $_POST['meta_tag_type'];

        if (empty($meta_tag_slug)) {
            $meta_tag_slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $meta_tag_name));
        }

        if ($meta_tag_id > 0) {
            $stmt = $conn->prepare("UPDATE meta_tags SET name = ?, slug = ?, type = ? WHERE id = ?");
            $stmt->bind_param("sssi", $meta_tag_name, $meta_tag_slug, $meta_tag_type, $meta_tag_id);
            if ($stmt->execute()) {
                $message = "Meta tag updated successfully!";
            } else {
                $error = "Error updating meta tag: " . $conn->error;
            }
            $stmt->close();
        } else {
            $stmt = $conn->prepare("INSERT INTO meta_tags (name, slug, type) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $meta_tag_name, $meta_tag_slug, $meta_tag_type);
            if ($stmt->execute()) {
                $message = "Meta tag added successfully!";
            } else {
                $error = "Error adding meta tag: " . $conn->error;
            }
            $stmt->close();
        }
    }

    // Add Color
    elseif (isset($_POST['add_color'])) {
        $color_name = trim($_POST['color_name']);
        $hex_code = trim($_POST['hex_code']);

        $stmt = $conn->prepare("INSERT INTO colors (name, hex_code) VALUES (?, ?)");
        $stmt->bind_param("ss", $color_name, $hex_code);

        if ($stmt->execute()) {
            $message = "Color added successfully!";
        } else {
            $error = "Error adding color: " . $conn->error;
        }
        $stmt->close();
    }

    // Add/Edit Size Chart
    elseif (isset($_POST['save_size_chart'])) {
        $chart_name = trim($_POST['chart_name']);
        $size_chart_id = intval($_POST['size_chart_id'] ?? 0);

        $measurements = [];

        if (isset($_POST['measurements']) && is_array($_POST['measurements'])) {
            foreach ($_POST['measurements'] as $entry) {
                if (!empty(trim($entry['size']))) {
                    $size = trim($entry['size']);
                    $measurements[$size] = [
                        'bust' => $entry['bust'] ?? '',
                        'waist' => $entry['waist'] ?? '',
                        'hips' => $entry['hips'] ?? '',
                        'length' => $entry['length'] ?? '',
                        'shoulder' => $entry['shoulder'] ?? ''
                    ];
                }
            }
        }

        $measurements_json = json_encode($measurements);

        if ($size_chart_id > 0) {
            $stmt = $conn->prepare("UPDATE size_charts SET chart_name = ?, measurements = ? WHERE id = ?");
            $stmt->bind_param("ssi", $chart_name, $measurements_json, $size_chart_id);
            $action_text = "updated";
        } else {
            $stmt = $conn->prepare("INSERT INTO size_charts (chart_name, measurements) VALUES (?, ?)");
            $stmt->bind_param("ss", $chart_name, $measurements_json);
            $action_text = "added";
        }

        if ($stmt->execute()) {
            $message = "Size chart $action_text successfully!";
            echo "<script>location.href='?section=sizes&msg=" . urlencode($message) . "';</script>";
            exit;
        } else {
            $error = "Error saving size chart: " . $conn->error;
        }
        $stmt->close();
    }

    // Stock Adjustment
    elseif (isset($_POST['adjust_stock'])) {
        $product_id = intval($_POST['product_id']);
        $adjustment_type = $_POST['adjustment_type'];
        $quantity = intval($_POST['quantity']);
        $notes = trim($_POST['notes']);

        $quantity_change = $adjustment_type === 'add' ? $quantity : -$quantity;

        if (updateStock($conn, $product_id, $quantity_change, 'adjustment', $notes)) {
            $message = "Stock adjusted successfully!";

            $check_stmt = $conn->prepare("SELECT stock_quantity, is_active FROM products WHERE id = ?");
            $check_stmt->bind_param("i", $product_id);
            $check_stmt->execute();
            $result = $check_stmt->get_result();
            $product = $result->fetch_assoc();
            $check_stmt->close();

            if ($product['stock_quantity'] == 0 && $product['is_active'] == 1) {
                $conn->query("UPDATE products SET is_active = 0 WHERE id = $product_id");
                $message .= " Product has been deactivated because stock is zero.";
            } elseif ($product['stock_quantity'] > 0 && $product['is_active'] == 0) {
                $conn->query("UPDATE products SET is_active = 1 WHERE id = $product_id");
                $message .= " Product has been reactivated because stock is now available.";
            }
        } else {
            $error = "Error adjusting stock";
        }
    }

    // Mark alert as read
    elseif (isset($_POST['mark_alert_read'])) {
        $alert_id = intval($_POST['alert_id']);
        if (markAlertAsRead($conn, $alert_id)) {
            $message = "Alert marked as read";
        }
    }

    // Save Settings
    elseif (isset($_POST['save_settings'])) {
        $settings_updated = 0;

        foreach ($_POST['settings'] as $key => $value) {
            if (updateSetting($conn, $key, trim($value))) {
                $settings_updated++;
            }
        }

        if ($settings_updated > 0) {
            $message = "Settings updated successfully!";

            if (isset($_POST['settings']['designer_name'])) {
                $_SESSION['owner_name'] = $_POST['settings']['designer_name'];
            }
        } else {
            $error = "No settings were updated.";
        }
    }
}

// Handle delete operations
if (isset($_GET['delete']) && $id > 0) {
    $delete_type = $_GET['delete'];

    if ($delete_type === 'product') {
        // First delete all media files
        $media_stmt = $conn->prepare("SELECT image_url FROM product_color_images WHERE product_id = ?");
        $media_stmt->bind_param("i", $id);
        $media_stmt->execute();
        $media_result = $media_stmt->get_result();
        while ($media = $media_result->fetch_assoc()) {
            if ($media['image_url'] && file_exists($media['image_url'])) {
                unlink($media['image_url']);
            }
        }
        $media_stmt->close();

        // Now delete the product
        $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $message = "Product deleted successfully!";
        }
        $stmt->close();
    } elseif ($delete_type === 'color') {
        // First check if color is used in any product
        $check_stmt = $conn->prepare("SELECT COUNT(*) as count FROM product_colors WHERE color_id = ?");
        $check_stmt->bind_param("i", $id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        $color_usage = $check_result->fetch_assoc()['count'];
        $check_stmt->close();

        if ($color_usage == 0) {
            $stmt = $conn->prepare("DELETE FROM colors WHERE id = ?");
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                $message = "Color deleted successfully!";
            }
            $stmt->close();
        } else {
            $error = "Cannot delete color - it is used in $color_usage product(s)";
        }
    } elseif ($delete_type === 'category') {
        // First check if category is used
        $check_stmt = $conn->prepare("SELECT COUNT(*) as count FROM products WHERE category_id = ?");
        $check_stmt->bind_param("i", $id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        $category_usage = $check_result->fetch_assoc()['count'];
        $check_stmt->close();

        if ($category_usage == 0) {
            $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                $message = "Category deleted successfully!";
            }
            $stmt->close();
        } else {
            $error = "Cannot delete category - it is used in $category_usage product(s)";
        }
    } elseif ($delete_type === 'meta_tag') {
        // First check if meta tag is used
        $check_stmt = $conn->prepare("SELECT COUNT(*) as count FROM product_meta_tags WHERE meta_tag_id = ?");
        $check_stmt->bind_param("i", $id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        $tag_usage = $check_result->fetch_assoc()['count'];
        $check_stmt->close();

        if ($tag_usage == 0) {
            $stmt = $conn->prepare("DELETE FROM meta_tags WHERE id = ?");
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                $message = "Meta tag deleted successfully!";
            }
            $stmt->close();
        } else {
            $error = "Cannot delete meta tag - it is used in $tag_usage product(s)";
        }
    } elseif ($delete_type === 'size_chart') {
        $conn->query("UPDATE products SET size_chart_id = NULL WHERE size_chart_id = $id");

        $stmt = $conn->prepare("DELETE FROM size_charts WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $message = "Size chart deleted successfully!";
        }
        $stmt->close();
    }
}

// Handle media deletion
if (isset($_GET['delete_media'])) {
    $media_id = intval($_GET['delete_media']);
    $product_id = intval($_GET['product_id'] ?? 0);

    // Get the media info first
    $stmt = $conn->prepare("SELECT image_url, is_video FROM product_color_images WHERE id = ?");
    $stmt->bind_param("i", $media_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    if ($row) {
        // Delete the file from server
        if (file_exists($row['image_url'])) {
            unlink($row['image_url']);
        }

        // Delete from database
        $delete_stmt = $conn->prepare("DELETE FROM product_color_images WHERE id = ?");
        $delete_stmt->bind_param("i", $media_id);
        if ($delete_stmt->execute()) {
            $message = ($row['is_video'] ? "Video" : "Image") . " deleted successfully!";
        }
        $delete_stmt->close();
    }

    // Redirect back to edit product page
    if ($product_id > 0) {
        $section = $_GET['section'] ?? 'stitched';
        header("Location: ?section=$section&action=edit&id=$product_id");
        exit;
    }
}

// Get data for current section
$stats = getStockStatistics($conn);
$recent_products = getRecentProducts($conn, 5);
$active_alerts = getActiveAlerts($conn, 5);
$low_stock_products = getLowStockProducts($conn, 5);
$out_of_stock_products = getOutOfStockProducts($conn, 5);
$stock_transactions = getStockTransactions($conn, 10);
$all_products = getAllProductsWithStock($conn);
$colors = getColors($conn);
$size_charts = getSizeCharts($conn);
$categories = getCategories($conn);
$meta_tags = getMetaTags($conn);
$meta_tag_types = ['occasion', 'fabric', 'style', 'work'];

// Get products by type with main images
$stitched_products = getProductsByType($conn, 'stitched', 50);
$unstitched_products = getProductsByType($conn, 'unstitched', 50);
$accessories_products = getProductsByType($conn, 'accessory', 50);

// Get item for editing
$edit_product = null;
$edit_category = null;
$edit_meta_tag = null;
$edit_size_chart = null;

if ($action === 'edit' && $id > 0) {
    if ($currentSection === 'categories') {
        $stmt = $conn->prepare("SELECT * FROM categories WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $edit_category = $result->fetch_assoc();
        $stmt->close();
    } elseif ($currentSection === 'meta-tags') {
        $stmt = $conn->prepare("SELECT * FROM meta_tags WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $edit_meta_tag = $result->fetch_assoc();
        $stmt->close();
    } elseif ($currentSection === 'sizes') {
        $stmt = $conn->prepare("SELECT * FROM size_charts WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $edit_size_chart = $result->fetch_assoc();
        $stmt->close();
    } else {
        $edit_product = getProductById($conn, $id);
    }
}

// Get totals
$total_products = getTotalProducts($conn);
$stitched_count = getProductCountByType($conn, 'stitched');
$unstitched_count = getProductCountByType($conn, 'unstitched');
$accessories_count = getProductCountByType($conn, 'accessory');
$total_colors = count($colors);

// Check for products with zero stock that are still active and auto-deactivate them
$zero_stock_active = $conn->query("SELECT COUNT(*) as count FROM products WHERE stock_quantity = 0 AND is_active = 1");
$zero_count = $zero_stock_active->fetch_assoc()['count'];
if ($zero_count > 0) {
    $conn->query("UPDATE products SET is_active = 0 WHERE stock_quantity = 0 AND is_active = 1");
}

// Get all settings for settings section
$all_settings = getAllSettings($conn);

// Calculate growth rates for dashboard
$growth_rates = calculateGrowthRates($conn);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SDesigner - Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #8B4513;
            --primary-light: #a0522d;
            --primary-dark: #5d2906;
            --secondary: #D4A76A;
            --secondary-light: #e6b87d;
            --accent: #E6B87D;
            --accent-light: #f4d4a8;
            --dark: #2C1810;
            --dark-light: #3d2417;
            --light: #FAF3E0;
            --light-dark: #f0e6cc;
            --success: #10B981;
            --warning: #F59E0B;
            --danger: #EF4444;
            --info: #3B82F6;
            --gray-100: #F9FAFB;
            --gray-200: #E5E7EB;
            --gray-300: #D1D5DB;
            --gray-400: #9CA3AF;
            --gray-500: #6B7280;
            --gray-600: #4B5563;
            --gray-700: #374151;
            --gray-800: #1F2937;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-md: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --shadow-lg: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            --radius-sm: 0.375rem;
            --radius: 0.5rem;
            --radius-md: 0.75rem;
            --radius-lg: 1rem;
            --transition: all 0.3s ease;
            --transition-fast: all 0.15s ease;
        }

        @media (max-width: 768px) {
            .admin-layout {
                flex-direction: column;
            }

            .admin-sidebar {
                width: 100%;
                position: fixed;
                left: 0;
                top: 0;
                bottom: 0;
                transform: translateX(-100%);
                transition: transform 0.3s ease;
                z-index: 1000;
                height: 100vh;
                overflow-y: auto;
            }

            .admin-sidebar.active {
                transform: translateX(0);
            }

            .admin-main {
                margin-left: 0;
                padding: 1rem;
                width: 100%;
                margin-top: 60px;
            }

            .color-media-section {
                background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
                border: 2px solid #e2e8f0;
                transition: all 0.3s ease;
            }

            .color-media-section:hover {
                border-color: #cbd5e1;
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            }

            #noColorsSelected {
                background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
            }

            .image-upload-fields .flex,
            .video-upload-fields .flex {
                align-items: center;
            }

            .mobile-menu-toggle {
                display: flex !important;
                position: fixed;
                top: 1rem;
                left: 1rem;
                z-index: 1100;
                background: var(--primary);
                color: white;
                border: none;
                border-radius: var(--radius);
                width: 48px;
                height: 48px;
                align-items: center;
                justify-content: center;
                font-size: 1.5rem;
                cursor: pointer;
                box-shadow: var(--shadow-md);
            }

            .stats-grid {
                grid-template-columns: 1fr !important;
            }

            .form-row {
                grid-template-columns: 1fr !important;
                gap: 1rem !important;
            }

            .data-table {
                font-size: 0.75rem;
            }

            .data-table th,
            .data-table td {
                padding: 0.5rem;
            }

            .page-title {
                font-size: 1.75rem !important;
            }

            .page-subtitle {
                font-size: 0.9rem !important;
            }

            .btn {
                padding: 0.5rem 1rem !important;
                font-size: 0.8rem !important;
            }

            .content-section {
                padding: 1rem !important;
            }

            .section-header {
                flex-direction: column !important;
                align-items: flex-start !important;
                gap: 0.5rem !important;
            }

            .quick-actions {
                grid-template-columns: 1fr !important;
            }

            .nav-item {
                padding: 0.75rem 1rem !important;
            }
        }

        @media (min-width: 769px) and (max-width: 1024px) {
            .admin-sidebar {
                width: 240px;
            }

            .admin-main {
                margin-left: 240px;
                width: calc(100% - 240px);
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr) !important;
            }

            .form-row {
                grid-template-columns: repeat(2, 1fr) !important;
            }
        }

        @media (min-width: 1025px) {
            .admin-sidebar {
                width: 280px;
                transform: translateX(0) !important;
                position: fixed;
                left: 0;
                top: 0;
                bottom: 0;
            }

            .admin-main {
                margin-left: 280px;
                width: calc(100% - 280px);
            }

            .mobile-menu-toggle {
                display: none !important;
            }

            .stats-grid {
                grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            }

            .form-row {
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            }
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            background-color: var(--gray-100);
            color: var(--gray-800);
            line-height: 1.6;
            min-height: 100vh;
        }

        .admin-layout {
            display: flex;
            min-height: 100vh;
        }

        .admin-sidebar {
            background: var(--dark);
            color: white;
            overflow-y: auto;
            transition: var(--transition);
            box-shadow: 4px 0 20px rgba(0, 0, 0, 0.15);
        }

        .sidebar-header {
            padding: 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            background: var(--dark-light);
        }

        .brand-title {
            font-family: 'Playfair Display', serif;
            font-size: 1.5rem;
            font-weight: 700;
            color: white;
            margin-bottom: 0.25rem;
            letter-spacing: 0.5px;
        }

        .brand-subtitle {
            color: var(--accent);
            font-size: 0.8rem;
            letter-spacing: 2px;
            text-transform: uppercase;
            opacity: 0.9;
        }

        .admin-profile {
            padding: 1.25rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            background: rgba(255, 255, 255, 0.05);
        }

        .profile-avatar {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, var(--secondary) 0%, var(--accent) 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 1rem;
            color: var(--dark);
            box-shadow: 0 4px 10px rgba(212, 167, 106, 0.3);
        }

        .profile-info h3 {
            font-weight: 600;
            margin-bottom: 0.25rem;
            font-size: 0.9rem;
        }

        .profile-info p {
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.75rem;
        }

        .nav-section {
            padding: 1rem 0;
        }

        .nav-title {
            padding: 0 1.25rem 0.5rem;
            color: rgba(255, 255, 255, 0.6);
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 600;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1.25rem;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: var(--transition);
            border-left: 3px solid transparent;
            margin: 0.125rem 0;
            position: relative;
        }

        .nav-item:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            border-left-color: var(--accent-light);
            padding-left: 1.5rem;
        }

        .nav-item.active {
            background: rgba(255, 255, 255, 0.15);
            color: white;
            border-left-color: var(--secondary);
            font-weight: 500;
        }

        .nav-icon {
            width: 20px;
            text-align: center;
            font-size: 1rem;
            opacity: 0.9;
        }

        .logout-item {
            margin-top: auto;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding-top: 1rem;
        }

        .logout-btn {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1.25rem;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: var(--transition);
            background: rgba(239, 68, 68, 0.1);
            margin: 1rem;
            border-radius: var(--radius);
            border: 1px solid rgba(239, 68, 68, 0.2);
        }

        .logout-btn:hover {
            background: rgba(239, 68, 68, 0.2);
            color: white;
            transform: translateY(-1px);
        }

        .mobile-menu-toggle {
            display: none;
        }

        .admin-main {
            flex: 1;
            padding: 1.5rem;
            min-height: 100vh;
        }

        .page-header {
            margin-bottom: 1.5rem;
        }

        .page-title {
            font-family: 'Playfair Display', serif;
            font-size: 2rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 0.5rem;
            line-height: 1.2;
        }

        .page-subtitle {
            color: var(--gray-600);
            font-size: 1rem;
            max-width: 800px;
        }

        .alert-message {
            padding: 1rem 1.25rem;
            border-radius: var(--radius);
            margin-bottom: 1.25rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            animation: slideIn 0.3s ease;
            box-shadow: var(--shadow);
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .alert-success {
            background-color: #D1FAE5;
            border: 1px solid #A7F3D0;
            color: #065F46;
        }

        .alert-error {
            background-color: #FEE2E2;
            border: 1px solid #FECACA;
            color: #991B1B;
        }

        .stats-grid {
            display: grid;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .stat-card {
            background: white;
            border-radius: var(--radius);
            padding: 1.25rem;
            box-shadow: var(--shadow);
            transition: var(--transition);
            border: 1px solid var(--gray-200);
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--primary);
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-md);
            border-color: var(--primary-light);
        }

        .stat-card.stitched::before {
            background: var(--info);
        }

        .stat-card.unstitched::before {
            background: var(--success);
        }

        .stat-card.accessories::before {
            background: var(--warning);
        }

        .stat-card.colors::before {
            background: var(--primary);
        }

        .stat-content {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .stat-info h3 {
            font-size: 0.75rem;
            color: var(--gray-600);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }

        .stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--dark);
            line-height: 1;
        }

        .stat-change {
            font-size: 0.75rem;
            display: flex;
            align-items: center;
            gap: 0.25rem;
            margin-top: 0.5rem;
        }

        .stat-change.positive {
            color: var(--success);
        }

        .stat-change.negative {
            color: var(--danger);
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: var(--radius);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            color: white;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .stat-icon.stitched {
            background: linear-gradient(135deg, var(--info) 0%, #60A5FA 100%);
        }

        .stat-icon.unstitched {
            background: linear-gradient(135deg, var(--success) 0%, #34D399 100%);
        }

        .stat-icon.accessories {
            background: linear-gradient(135deg, var(--warning) 0%, #FBBF24 100%);
        }

        .stat-icon.colors {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
        }

        .stat-icon.stock {
            background: linear-gradient(135deg, #8B5CF6 0%, #A78BFA 100%);
        }

        .content-section {
            background: white;
            border-radius: var(--radius);
            padding: 1.25rem;
            box-shadow: var(--shadow);
            margin-bottom: 1.25rem;
            border: 1px solid var(--gray-200);
            transition: var(--transition);
        }

        .content-section:hover {
            box-shadow: var(--shadow-md);
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.25rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--gray-100);
        }

        .section-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: var(--radius);
            font-weight: 500;
            text-decoration: none;
            transition: var(--transition);
            border: none;
            cursor: pointer;
            font-size: 0.875rem;
            letter-spacing: 0.25px;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, var(--primary-light) 0%, var(--primary) 100%);
        }

        .btn-secondary {
            background: linear-gradient(135deg, var(--secondary) 0%, var(--accent) 100%);
            color: var(--dark);
        }

        .btn-success {
            background: linear-gradient(135deg, var(--success) 0%, #059669 100%);
            color: white;
        }

        .btn-warning {
            background: linear-gradient(135deg, var(--warning) 0%, #D97706 100%);
            color: white;
        }

        .btn-danger {
            background: linear-gradient(135deg, var(--danger) 0%, #DC2626 100%);
            color: white;
        }

        .btn-sm {
            padding: 0.375rem 0.75rem;
            font-size: 0.75rem;
        }

        .btn-icon {
            padding: 0.5rem;
            width: 32px;
            height: 32px;
            justify-content: center;
        }

        .data-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        .data-table th {
            background: var(--gray-100);
            padding: 0.75rem;
            text-align: left;
            font-weight: 600;
            color: var(--gray-700);
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid var(--gray-200);
        }

        .data-table td {
            padding: 0.75rem;
            border-bottom: 1px solid var(--gray-200);
            color: var(--gray-700);
            font-size: 0.875rem;
        }

        .data-table tbody tr {
            transition: var(--transition-fast);
        }

        .data-table tbody tr:hover {
            background: var(--gray-100);
        }

        .product-image-cell img {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: var(--radius-sm);
            border: 2px solid var(--gray-200);
        }

        .status-badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            letter-spacing: 0.25px;
        }

        .status-active {
            background: #D1FAE5;
            color: #065F46;
        }

        .status-inactive {
            background: #FEE2E2;
            color: #991B1B;
        }

        .status-low-stock {
            background: #FEF3C7;
            color: #92400E;
        }

        .status-out-stock {
            background: #FEE2E2;
            color: #DC2626;
        }

        .stock-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            padding: 0.25rem 0.5rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            background: var(--gray-100);
            color: var(--gray-700);
        }

        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--gray-700);
            font-size: 0.875rem;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid var(--gray-300);
            border-radius: var(--radius);
            font-size: 0.875rem;
            transition: var(--transition);
            background: white;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(139, 69, 19, 0.1);
        }

        .form-row {
            display: grid;
            gap: 1rem;
        }

        .form-actions {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
            padding-top: 1.25rem;
            border-top: 2px solid var(--gray-100);
        }

        .color-preview {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 0.5rem;
            vertical-align: middle;
            border: 2px solid white;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        /* SIMPLIFIED: Color media display area */
        .color-media-display-area {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border: 2px solid #dee2e6;
            border-radius: var(--radius);
            padding: 1.25rem;
            margin-bottom: 1.5rem;
        }

        .color-media-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            padding-bottom: 0.75rem;
            border-bottom: 1px solid #ced4da;
        }

        .color-media-header h4 {
            font-weight: 600;
            color: #495057;
            font-size: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .media-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
            gap: 1rem;
        }

        .media-display-item {
            position: relative;
            border-radius: var(--radius-sm);
            border: 2px solid #ced4da;
            overflow: hidden;
            aspect-ratio: 1;
            background: #fff;
        }

        .media-display-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .video-display-item {
            background: linear-gradient(135deg, #000428 0%, #004e92 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }

        .media-type-badge {
            position: absolute;
            top: 5px;
            left: 5px;
            background: rgba(0, 0, 0, 0.75);
            color: white;
            padding: 2px 6px;
            border-radius: 10px;
            font-size: 0.65rem;
            font-weight: 600;
            z-index: 2;
        }

        .media-main-badge {
            position: absolute;
            top: 5px;
            right: 5px;
            background: rgba(245, 158, 11, 0.9);
            color: white;
            padding: 2px 6px;
            border-radius: 10px;
            font-size: 0.65rem;
            font-weight: 600;
            z-index: 2;
        }

        /* SIMPLIFIED: Media upload sections */
        .media-upload-section {
            margin: 1rem 0;
            padding: 1rem;
            background: white;
            border-radius: var(--radius);
            border: 2px dashed #adb5bd;
        }

        .file-field-group {
            display: flex;
            gap: 0.75rem;
            align-items: center;
            margin-bottom: 0.5rem;
        }

        /* Meta Tags */
        .meta-tags-container {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            padding: 1rem;
            border: 2px solid #e9ecef;
            border-radius: var(--radius);
            max-height: 200px;
            overflow-y: auto;
            background: #fff;
        }

        .meta-tag-option {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 0.75rem;
            background: #f8f9fa;
            border-radius: 20px;
            cursor: pointer;
            transition: var(--transition);
            font-size: 0.875rem;
        }

        .meta-tag-option:hover {
            background: #e9ecef;
        }

        .meta-tag-option input[type="checkbox"] {
            accent-color: var(--primary);
            width: 16px;
            height: 16px;
        }

        .meta-tag-type {
            display: inline-block;
            padding: 0.2rem 0.5rem;
            border-radius: 12px;
            font-size: 0.65rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .meta-tag-occasion {
            background: #DBEAFE;
            color: #1E40AF;
        }

        .meta-tag-fabric {
            background: #D1FAE5;
            color: #065F46;
        }

        .meta-tag-style {
            background: #FEF3C7;
            color: #92400E;
        }

        .meta-tag-work {
            background: #FCE7F3;
            color: #9D174D;
        }

        .alert-card {
            background: white;
            border-left: 4px solid;
            border-radius: var(--radius);
            padding: 1rem;
            margin-bottom: 0.75rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: var(--shadow-sm);
            transition: var(--transition);
        }

        .alert-card:hover {
            transform: translateX(5px);
            box-shadow: var(--shadow);
        }

        .alert-card.low-stock {
            border-left-color: var(--warning);
            background: linear-gradient(to right, rgba(245, 158, 11, 0.05), white);
        }

        .alert-card.out-stock {
            border-left-color: var(--danger);
            background: linear-gradient(to right, rgba(239, 68, 68, 0.05), white);
        }

        .alert-content h4 {
            font-weight: 600;
            margin-bottom: 0.25rem;
            color: var(--gray-800);
            font-size: 0.875rem;
        }

        .alert-content p {
            color: var(--gray-600);
            font-size: 0.75rem;
        }

        .alert-time {
            font-size: 0.7rem;
            color: var(--gray-500);
        }

        .quick-actions {
            display: grid;
            gap: 1rem;
        }

        .action-card {
            background: white;
            border: 2px solid #e9ecef;
            border-radius: var(--radius);
            padding: 1.25rem;
            text-align: center;
            transition: var(--transition);
            text-decoration: none;
            color: inherit;
        }

        .action-card:hover {
            transform: translateY(-5px);
            border-color: var(--primary);
            box-shadow: var(--shadow-lg);
        }

        .action-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            margin: 0 auto 1rem;
        }

        .action-title {
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--dark);
            font-size: 0.875rem;
        }

        .action-desc {
            color: var(--gray-600);
            font-size: 0.75rem;
        }

        .empty-state {
            text-align: center;
            padding: 2rem;
            color: var(--gray-500);
        }

        .empty-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 2000;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background: white;
            border-radius: var(--radius);
            max-width: 600px;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: var(--shadow-lg);
        }

        .modal-header {
            padding: 1.25rem;
            border-bottom: 1px solid var(--gray-200);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-body {
            padding: 1.25rem;
        }

        .modal-footer {
            padding: 1.25rem;
            border-top: 1px solid var(--gray-200);
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
        }

        .color-validation-error {
            color: #dc3545;
            font-size: 0.875rem;
            margin-top: 0.5rem;
            display: none;
        }

        /* NEW: Category icon preview */
        .icon-preview {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            background: #f8f9fa;
            border-radius: var(--radius);
            border: 2px solid #dee2e6;
        }

        /* NEW: Size chart dynamic fields */
        .size-entry {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: var(--radius);
            padding: 1rem;
            margin-bottom: 1rem;
        }

        .measurement-field {
            display: flex;
            gap: 0.5rem;
            align-items: center;
            margin-bottom: 0.5rem;
        }

        .measurement-field input {
            flex: 1;
        }
    </style>
</head>

<body>
    <!-- Mobile Menu Toggle -->
    <button class="mobile-menu-toggle" id="mobileMenuToggle">
        <i class="fas fa-bars"></i>
    </button>

    <div class="admin-layout">
        <!-- Sidebar -->
        <aside class="admin-sidebar" id="adminSidebar">
            <div class="sidebar-header">
                <div class="brand-title"><?php echo htmlspecialchars($boutique_name); ?></div>
                <div class="brand-subtitle">Admin Panel</div>
            </div>

            <div class="admin-profile">
                <div class="profile-avatar"><?php echo substr($owner_name, 0, 2); ?></div>
                <div class="profile-info">
                    <h3><?php echo htmlspecialchars($owner_name); ?></h3>
                    <p>Head Designer & Admin</p>
                </div>
            </div>

            <div class="nav-section">
                <div class="nav-title">Main Navigation</div>
                <a href="?section=dashboard"
                    class="nav-item <?php echo $currentSection === 'dashboard' ? 'active' : ''; ?>">
                    <i class="fas fa-chart-line nav-icon"></i>
                    <span>Dashboard</span>
                </a>
                <a href="?section=stitched"
                    class="nav-item <?php echo $currentSection === 'stitched' ? 'active' : ''; ?>">
                    <i class="fas fa-tshirt nav-icon"></i>
                    <span>Stitched Clothes</span>
                    <span
                        class="ml-auto text-xs bg-white/20 px-2 py-1 rounded-full"><?php echo $stitched_count; ?></span>
                </a>
                <a href="?section=unstitched"
                    class="nav-item <?php echo $currentSection === 'unstitched' ? 'active' : ''; ?>">
                    <i class="fas fa-cut nav-icon"></i>
                    <span>Unstitched Cloth</span>
                    <span
                        class="ml-auto text-xs bg-white/20 px-2 py-1 rounded-full"><?php echo $unstitched_count; ?></span>
                </a>
                <a href="?section=accessories"
                    class="nav-item <?php echo $currentSection === 'accessories' ? 'active' : ''; ?>">
                    <i class="fas fa-gem nav-icon"></i>
                    <span>Accessories</span>
                    <span
                        class="ml-auto text-xs bg-white/20 px-2 py-1 rounded-full"><?php echo $accessories_count; ?></span>
                </a>
            </div>

            <div class="nav-section">
                <div class="nav-title">Inventory Management</div>
                <a href="?section=stock" class="nav-item <?php echo $currentSection === 'stock' ? 'active' : ''; ?>">
                    <i class="fas fa-boxes nav-icon"></i>
                    <span>Stock Management</span>
                    <?php if (($stats['low_stock'] ?? 0) + ($stats['out_of_stock'] ?? 0) > 0): ?>
                        <span class="ml-auto text-xs bg-red-500 px-2 py-1 rounded-full">
                            <?php echo ($stats['low_stock'] ?? 0) + ($stats['out_of_stock'] ?? 0); ?>
                        </span>
                    <?php endif; ?>
                </a>
                <a href="?section=colors" class="nav-item <?php echo $currentSection === 'colors' ? 'active' : ''; ?>">
                    <i class="fas fa-palette nav-icon"></i>
                    <span>Colors</span>
                    <span class="ml-auto text-xs bg-white/20 px-2 py-1 rounded-full"><?php echo $total_colors; ?></span>
                </a>
                <a href="?section=sizes" class="nav-item <?php echo $currentSection === 'sizes' ? 'active' : ''; ?>">
                    <i class="fas fa-ruler-combined nav-icon"></i>
                    <span>Size Charts</span>
                </a>
                <a href="?section=transactions"
                    class="nav-item <?php echo $currentSection === 'transactions' ? 'active' : ''; ?>">
                    <i class="fas fa-exchange-alt nav-icon"></i>
                    <span>Stock Transactions</span>
                </a>
            </div>

            <div class="nav-section">
                <div class="nav-title">Content Management</div>
                <a href="?section=categories"
                    class="nav-item <?php echo $currentSection === 'categories' ? 'active' : ''; ?>">
                    <i class="fas fa-folder nav-icon"></i>
                    <span>Categories</span>
                </a>
                <a href="?section=meta-tags"
                    class="nav-item <?php echo $currentSection === 'meta-tags' ? 'active' : ''; ?>">
                    <i class="fas fa-tags nav-icon"></i>
                    <span>Meta Tags</span>
                </a>
            </div>

            <div class="nav-section">
                <div class="nav-title">Settings</div>
                <a href="?section=settings"
                    class="nav-item <?php echo $currentSection === 'settings' ? 'active' : ''; ?>">
                    <i class="fas fa-cog nav-icon"></i>
                    <span>Settings</span>
                </a>
            </div>

            <div class="logout-item">
                <a href="?logout" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="admin-main">
            <?php if ($message): ?>
                <div class="alert-message alert-success">
                    <i class="fas fa-check-circle"></i>
                    <span><?php echo htmlspecialchars($message); ?></span>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert-message alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <span><?php echo htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>

            <?php if ($currentSection === 'dashboard'): ?>
                <!-- Dashboard Section -->
                <div class="page-header">
                    <h1 class="page-title">Dashboard</h1>
                    <p class="page-subtitle">Welcome back, <?php echo htmlspecialchars($owner_name); ?>! Here's what's
                        happening with your boutique today.</p>
                </div>

                <!-- Stats Grid -->
                <div class="stats-grid">
                    <div class="stat-card stitched">
                        <div class="stat-content">
                            <div class="stat-info">
                                <h3>Stitched Items</h3>
                                <div class="stat-value"><?php echo $stitched_count; ?></div>
                                <div class="stat-change positive">
                                    <i class="fas fa-arrow-up"></i>
                                    <span><?php echo $growth_rates['stitched'] ?? 0; ?>% from last month</span>
                                </div>
                            </div>
                            <div class="stat-icon stitched">
                                <i class="fas fa-tshirt"></i>
                            </div>
                        </div>
                    </div>

                    <div class="stat-card unstitched">
                        <div class="stat-content">
                            <div class="stat-info">
                                <h3>Unstitched Fabrics</h3>
                                <div class="stat-value"><?php echo $unstitched_count; ?></div>
                                <div class="stat-change positive">
                                    <i class="fas fa-arrow-up"></i>
                                    <span><?php echo $growth_rates['unstitched'] ?? 0; ?>% from last month</span>
                                </div>
                            </div>
                            <div class="stat-icon unstitched">
                                <i class="fas fa-cut"></i>
                            </div>
                        </div>
                    </div>

                    <div class="stat-card accessories">
                        <div class="stat-content">
                            <div class="stat-info">
                                <h3>Accessories</h3>
                                <div class="stat-value"><?php echo $accessories_count; ?></div>
                                <div class="stat-change positive">
                                    <i class="fas fa-arrow-up"></i>
                                    <span><?php echo $growth_rates['accessories'] ?? 0; ?>% from last month</span>
                                </div>
                            </div>
                            <div class="stat-icon accessories">
                                <i class="fas fa-gem"></i>
                            </div>
                        </div>
                    </div>

                    <div class="stat-card colors">
                        <div class="stat-content">
                            <div class="stat-info">
                                <h3>Available Colors</h3>
                                <div class="stat-value"><?php echo $total_colors; ?></div>
                                <div class="stat-change positive">
                                    <i class="fas fa-plus"></i>
                                    <span>3 new colors</span>
                                </div>
                            </div>
                            <div class="stat-icon colors">
                                <i class="fas fa-palette"></i>
                            </div>
                        </div>
                    </div>

                    <div class="stat-card stock-value">
                        <div class="stat-content">
                            <div class="stat-info">
                                <h3>Stock Value</h3>
                                <div class="stat-value">₹<?php echo number_format($stats['total_stock_value'] ?? 0); ?>
                                </div>
                                <div class="stat-change positive">
                                    <i class="fas fa-arrow-up"></i>
                                    <span>Updated today</span>
                                </div>
                            </div>
                            <div class="stat-icon stock">
                                <i class="fas fa-rupee-sign"></i>
                            </div>
                        </div>
                    </div>

                    <div class="stat-card low-stock">
                        <div class="stat-content">
                            <div class="stat-info">
                                <h3>Low Stock Items</h3>
                                <div class="stat-value"><?php echo $stats['low_stock'] ?? 0; ?></div>
                                <div class="stat-change negative">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    <span>Needs attention</span>
                                </div>
                            </div>
                            <div class="stat-icon accessories">
                                <i class="fas fa-exclamation"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Alerts & Quick Actions -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                    <!-- Stock Alerts -->
                    <div class="content-section">
                        <div class="section-header">
                            <h2 class="section-title">
                                <i class="fas fa-bell"></i>
                                Stock Alerts
                            </h2>
                            <a href="?section=stock" class="text-primary hover:underline text-sm">View All</a>
                        </div>

                        <?php if (!empty($active_alerts)): ?>
                            <?php foreach ($active_alerts as $alert): ?>
                                <div class="alert-card <?php echo $alert['alert_type']; ?>">
                                    <div class="alert-content">
                                        <h4><?php echo htmlspecialchars($alert['product_name']); ?></h4>
                                        <p><?php echo htmlspecialchars($alert['message']); ?></p>
                                        <div class="alert-time">
                                            <?php echo date('M d, h:i A', strtotime($alert['created_at'])); ?>
                                        </div>
                                    </div>
                                    <form method="POST" class="inline">
                                        <input type="hidden" name="alert_id" value="<?php echo $alert['id']; ?>">
                                        <button type="submit" name="mark_alert_read" class="btn btn-icon btn-sm">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    </form>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="empty-state py-4">
                                <div class="empty-icon">
                                    <i class="far fa-check-circle"></i>
                                </div>
                                <h3 class="text-lg font-semibold text-gray-600 mb-2">All Good!</h3>
                                <p class="text-gray-500">No stock alerts at the moment.</p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Quick Actions -->
                    <div class="content-section">
                        <div class="section-header">
                            <h2 class="section-title">
                                <i class="fas fa-bolt"></i>
                                Quick Actions
                            </h2>
                        </div>

                        <div class="quick-actions">
                            <a href="?section=stitched&action=add" class="action-card">
                                <div class="action-icon">
                                    <i class="fas fa-plus"></i>
                                </div>
                                <h3 class="action-title">Add Product</h3>
                                <p class="action-desc">Add new stitched item</p>
                            </a>

                            <a href="?section=colors" class="action-card">
                                <div class="action-icon">
                                    <i class="fas fa-palette"></i>
                                </div>
                                <h3 class="action-title">Add Color</h3>
                                <p class="action-desc">Expand color palette</p>
                            </a>

                            <a href="?section=stock" class="action-card">
                                <div class="action-icon">
                                    <i class="fas fa-boxes"></i>
                                </div>
                                <h3 class="action-title">Check Stock</h3>
                                <p class="action-desc">Review inventory</p>
                            </a>

                            <a href="?section=categories&action=add" class="action-card">
                                <div class="action-icon">
                                    <i class="fas fa-folder-plus"></i>
                                </div>
                                <h3 class="action-title">Add Category</h3>
                                <p class="action-desc">Create new category</p>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Recent Products -->
                <div class="content-section">
                    <div class="section-header">
                        <h2 class="section-title">
                            <i class="fas fa-clock"></i>
                            Recently Added Products
                        </h2>
                        <a href="?section=stitched" class="btn btn-sm btn-primary">View All</a>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Type</th>
                                    <th>Price</th>
                                    <th>Stock</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_products as $product): ?>
                                    <tr>
                                        <td class="flex items-center space-x-3">
                                            <?php
                                            // Get main image for the product
                                            $main_image = getMainProductImage($conn, $product['id']);
                                            $image_url = $main_image ? $main_image['image_url'] : null;
                                            ?>
                                            <?php if ($image_url): ?>
                                                <img src="<?php echo htmlspecialchars($image_url); ?>"
                                                    alt="<?php echo htmlspecialchars($product['name']); ?>"
                                                    class="w-10 h-10 rounded object-cover">
                                            <?php else: ?>
                                                <div class="w-10 h-10 bg-gray-200 rounded flex items-center justify-center">
                                                    <i class="fas fa-image text-gray-400"></i>
                                                </div>
                                            <?php endif; ?>
                                            <div>
                                                <div class="font-medium"><?php echo htmlspecialchars($product['name']); ?></div>
                                                <div class="text-sm text-gray-500">
                                                    <?php echo htmlspecialchars($product['category_name']); ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span
                                                class="status-badge <?php echo $product['is_active'] ? 'status-active' : 'status-inactive'; ?>">
                                                <?php echo ucfirst($product['product_type']); ?>
                                            </span>
                                        </td>
                                        <td class="font-semibold">₹<?php echo number_format($product['price'], 2); ?></td>
                                        <td>
                                            <span
                                                class="stock-badge <?php echo $product['stock_quantity'] == 0 ? 'status-out-stock' : ($product['stock_quantity'] <= 5 ? 'status-low-stock' : ''); ?>">
                                                <i class="fas fa-box"></i>
                                                <?php echo $product['stock_quantity']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span
                                                class="status-badge <?php echo $product['is_active'] ? 'status-active' : 'status-inactive'; ?>">
                                                <?php echo $product['is_active'] ? 'Active' : 'Inactive'; ?>
                                                <?php if ($product['stock_quantity'] == 0 && $product['is_active'] == 0): ?>
                                                    <i class="fas fa-exclamation-circle ml-1"
                                                        title="Auto-deactivated due to zero stock"></i>
                                                <?php endif; ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            <?php elseif (in_array($currentSection, ['stitched', 'unstitched', 'accessories'])): ?>
                <!-- Product Management -->
                <div class="page-header">
                    <h1 class="page-title">
                        <?php echo ucfirst($currentSection); ?> Collection
                    </h1>
                    <p class="page-subtitle">
                        Manage your <?php echo $currentSection; ?> items. Add new products, update existing ones, and
                        monitor stock levels.
                    </p>
                    <?php if ($currentSection === 'stitched'): ?>
                        <div class="mt-2 text-sm text-gray-600">
                            <i class="fas fa-info-circle mr-1"></i>
                            Products with zero stock are automatically deactivated
                        </div>
                    <?php endif; ?>
                </div>

                <?php if ($action === 'add' || $action === 'edit'): ?>
                    <!-- Add/Edit Product Form - FIXED: Color sections and media display -->
                    <div class="content-section">
                        <div class="section-header">
                            <h2 class="section-title">
                                <i
                                    class="fas fa-<?php echo $currentSection === 'stitched' ? 'tshirt' : ($currentSection === 'unstitched' ? 'cut' : 'gem'); ?>"></i>
                                <?php echo $action === 'edit' ? 'Edit' : 'Add'; ?>         <?php echo ucfirst($currentSection); ?> Item
                            </h2>
                            <a href="?section=<?php echo $currentSection; ?>" class="btn btn-secondary">Back to List</a>
                        </div>

                        <form method="POST" enctype="multipart/form-data" class="space-y-6" id="productForm">
                            <input type="hidden" name="product_id" value="<?php echo $edit_product['id'] ?? ''; ?>">
                            <input type="hidden" name="product_type"
                                value="<?php echo $currentSection === 'accessories' ? 'accessory' : $currentSection; ?>">

                            <!-- Basic Information -->
                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">Product Name *</label>
                                    <input type="text" name="name" class="form-control"
                                        value="<?php echo isset($edit_product['name']) ? htmlspecialchars($edit_product['name']) : ''; ?>"
                                        required placeholder="e.g., Royal Blue Lehenga">
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Description</label>
                                    <textarea name="description" class="form-control" rows="3"
                                        placeholder="Product description..."><?php echo isset($edit_product['description']) ? htmlspecialchars($edit_product['description']) : ''; ?></textarea>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">Category *</label>
                                    <select name="category_id" class="form-control" required>
                                        <option value="">Select Category</option>
                                        <?php foreach ($categories as $category): ?>
                                            <option value="<?php echo $category['id']; ?>" <?php echo (isset($edit_product['category_id']) && $edit_product['category_id'] == $category['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($category['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <!-- Pricing & Stock -->
                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">Price (₹) *</label>
                                    <input type="number" name="price" class="form-control" step="0.01" min="0"
                                        value="<?php echo isset($edit_product['price']) ? $edit_product['price'] : ''; ?>"
                                        required placeholder="2999.00">
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Stock Quantity *</label>
                                    <input type="number" name="stock_quantity" class="form-control" min="0"
                                        value="<?php echo isset($edit_product['stock_quantity']) ? $edit_product['stock_quantity'] : '0'; ?>"
                                        required>
                                    <small
                                        class="text-gray-500 <?php echo (isset($edit_product['stock_quantity']) && $edit_product['stock_quantity'] == 0) ? 'text-red-600 font-semibold' : ''; ?>">
                                        <?php if (isset($edit_product['stock_quantity']) && $edit_product['stock_quantity'] == 0): ?>
                                            <i class="fas fa-exclamation-triangle"></i> Product will be deactivated when stock is
                                            zero
                                        <?php endif; ?>
                                    </small>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Reorder Level</label>
                                    <input type="number" name="reorder_level" class="form-control" min="1"
                                        value="<?php echo isset($edit_product['reorder_level']) ? $edit_product['reorder_level'] : '5'; ?>">
                                    <small class="text-gray-500">Alert when stock goes below this level</small>
                                </div>
                            </div>

                            <?php if ($currentSection === 'unstitched'): ?>
                                <div class="form-row">
                                    <div class="form-group">
                                        <label class="form-label">Price per Meter (₹)</label>
                                        <input type="number" name="price_per_meter" class="form-control" step="0.01" min="0"
                                            value="<?php echo isset($edit_product['price_per_meter']) ? $edit_product['price_per_meter'] : ''; ?>"
                                            placeholder="299.00">
                                        <small class="text-gray-500">Price per meter of fabric (for unstitched only)</small>
                                    </div>

                                    <div class="form-group">
                                        <label class="form-label">Length (meters)</label>
                                        <input type="number" name="length_in_meters" class="form-control" step="0.01" min="0"
                                            value="<?php echo isset($edit_product['length_in_meters']) ? $edit_product['length_in_meters'] : ''; ?>"
                                            placeholder="2.5">
                                        <small class="text-gray-500">Length of fabric in meters (for unstitched only)</small>
                                    </div>

                                    <!-- ADD THIS NEW FIELD -->
                                    <div class="form-group">
                                        <label class="form-label">Stitch Charges (₹)</label>
                                        <input type="number" name="stitch_charges" class="form-control" step="0.01" min="0"
                                            value="<?php echo isset($edit_product['stitch_charges']) ? $edit_product['stitch_charges'] : '0'; ?>"
                                            placeholder="500.00">
                                        <small class="text-gray-500">Additional charges for stitching (for unstitched only)</small>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- Colors Selection - COMPULSORY -->
                            <div class="form-group">
                                <label class="form-label">Available Colors * <span class="text-red-500">(Select at least
                                        one)</span></label>
                                <div class="meta-tags-container" id="colorsContainer">
                                    <?php
                                    $selected_colors = [];
                                    if (isset($edit_product['colors'])) {
                                        foreach ($edit_product['colors'] as $color) {
                                            $selected_colors[] = $color['id'];
                                        }
                                    }
                                    ?>
                                    <?php foreach ($colors as $color): ?>
                                        <label class="meta-tag-option">
                                            <input type="checkbox" name="colors[]" value="<?php echo $color['id']; ?>" <?php echo in_array($color['id'], $selected_colors) ? 'checked' : ''; ?>
                                                data-color-id="<?php echo $color['id']; ?>"
                                                data-color-name="<?php echo htmlspecialchars($color['name']); ?>"
                                                data-hex-code="<?php echo $color['hex_code']; ?>" class="color-checkbox">
                                            <span class="color-preview"
                                                style="background: <?php echo $color['hex_code']; ?>"></span>
                                            <span><?php echo htmlspecialchars($color['name']); ?></span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                                <div id="colorError" class="color-validation-error">Please select at least one color</div>
                                <small class="text-gray-500">Select all colors available for this product.</small>
                            </div>

                            <!-- Media Upload Area - Only appears when colors are selected -->
                            <div id="colorMediaContainer" class="mt-6">
                                <div id="noColorsSelected"
                                    class="text-center p-8 border-2 border-dashed border-gray-300 rounded-lg <?php echo (empty($selected_colors) && $action !== 'edit') ? '' : 'hidden'; ?>">
                                    <i class="fas fa-palette text-4xl text-gray-400 mb-4"></i>
                                    <h4 class="text-lg font-semibold text-gray-600 mb-2">No Colors Selected</h4>
                                    <p class="text-gray-500">Select colors above to add images and videos for each color.</p>
                                </div>

                                <?php if ($action === 'edit' && isset($edit_product['colors']) && !empty($edit_product['colors'])): ?>
                                    <!-- Display existing media for each selected color -->
                                    <?php foreach ($edit_product['colors'] as $color): ?>
                                        <?php
                                        // Get existing images for this color
                                        $existing_images = getProductColorImagesOnly($conn, $edit_product['id'], $color['id']);
                                        // Get existing videos for this color
                                        $existing_videos = getProductColorVideosOnly($conn, $edit_product['id'], $color['id']);
                                        ?>
                                        <div class="color-media-section border rounded-lg p-4 mb-4"
                                            id="media-section-<?php echo $color['id']; ?>">
                                            <div class="flex justify-between items-center mb-4">
                                                <h4 class="font-semibold text-gray-800 flex items-center gap-2">
                                                    <span class="color-preview"
                                                        style="background: <?php echo $color['hex_code']; ?>; width: 24px; height: 24px;"></span>
                                                    <?php echo htmlspecialchars($color['name']); ?> - Media
                                                </h4>
                                                <button type="button" onclick="removeColorMediaSection(<?php echo $color['id']; ?>)"
                                                    class="text-red-600 hover:text-red-800 text-sm flex items-center gap-1">
                                                    <i class="fas fa-times"></i> Remove Color
                                                </button>
                                            </div>

                                            <?php if (!empty($existing_images) || !empty($existing_videos)): ?>
                                                <div class="mb-6">
                                                    <h5 class="text-sm font-semibold text-gray-700 mb-3">Existing Media:</h5>
                                                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3">
                                                        <?php foreach ($existing_images as $image): ?>
                                                            <div class="relative border rounded-lg overflow-hidden bg-gray-50">
                                                                <div class="aspect-square">
                                                                    <img src="<?php echo htmlspecialchars($image['image_url']); ?>"
                                                                        alt="Product Image" class="w-full h-full object-cover">
                                                                    <div
                                                                        class="absolute top-1 left-1 bg-black bg-opacity-70 text-white text-xs px-2 py-1 rounded">
                                                                        Image
                                                                    </div>
                                                                    <?php if ($image['is_main_image']): ?>
                                                                        <div
                                                                            class="absolute top-1 right-1 bg-yellow-500 text-white text-xs px-2 py-1 rounded">
                                                                            Main
                                                                        </div>
                                                                    <?php endif; ?>
                                                                    <a href="?section=<?php echo $currentSection; ?>&action=edit&id=<?php echo $edit_product['id']; ?>&delete_media=<?php echo $image['id']; ?>&product_id=<?php echo $edit_product['id']; ?>"
                                                                        class="absolute bottom-1 right-1 bg-red-600 text-white p-1 rounded text-xs hover:bg-red-700"
                                                                        onclick="return confirm('Delete this image?')">
                                                                        <i class="fas fa-trash"></i>
                                                                    </a>
                                                                </div>
                                                            </div>
                                                        <?php endforeach; ?>

                                                        <?php foreach ($existing_videos as $video): ?>
                                                            <div class="relative border rounded-lg overflow-hidden bg-gray-50">
                                                                <div class="aspect-square">
                                                                    <div
                                                                        class="w-full h-full bg-gradient-to-br from-blue-900 to-purple-900 flex items-center justify-center">
                                                                        <i class="fas fa-video text-white text-2xl"></i>
                                                                    </div>
                                                                    <div
                                                                        class="absolute top-1 left-1 bg-black bg-opacity-70 text-white text-xs px-2 py-1 rounded">
                                                                        Video
                                                                    </div>
                                                                    <?php if ($video['is_main_image']): ?>
                                                                        <div
                                                                            class="absolute top-1 right-1 bg-yellow-500 text-white text-xs px-2 py-1 rounded">
                                                                            Main
                                                                        </div>
                                                                    <?php endif; ?>
                                                                    <a href="?section=<?php echo $currentSection; ?>&action=edit&id=<?php echo $edit_product['id']; ?>&delete_media=<?php echo $video['id']; ?>&product_id=<?php echo $edit_product['id']; ?>"
                                                                        class="absolute bottom-1 right-1 bg-red-600 text-white p-1 rounded text-xs hover:bg-red-700"
                                                                        onclick="return confirm('Delete this video?')">
                                                                        <i class="fas fa-trash"></i>
                                                                    </a>
                                                                </div>
                                                            </div>
                                                        <?php endforeach; ?>
                                                    </div>
                                                </div>
                                            <?php endif; ?>

                                            <!-- Upload new images -->
                                            <div class="mb-4">
                                                <label class="form-label">Upload New Images for
                                                    <?php echo htmlspecialchars($color['name']); ?></label>
                                                <div class="image-upload-fields space-y-2"
                                                    id="image-fields-<?php echo $color['id']; ?>">
                                                    <div class="flex gap-2">
                                                        <input type="file" name="color_images_<?php echo $color['id']; ?>[]"
                                                            class="form-control flex-1" accept="image/*">
                                                        <button type="button" class="btn btn-sm btn-secondary"
                                                            onclick="addImageField(<?php echo $color['id']; ?>)">
                                                            <i class="fas fa-plus"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                                <small class="text-gray-500">JPEG, PNG, GIF, WebP formats supported</small>
                                            </div>

                                            <!-- Upload new videos -->
                                            <div class="mb-4">
                                                <label class="form-label">Upload New Videos for
                                                    <?php echo htmlspecialchars($color['name']); ?></label>
                                                <div class="video-upload-fields space-y-2"
                                                    id="video-fields-<?php echo $color['id']; ?>">
                                                    <div class="flex gap-2">
                                                        <input type="file" name="color_videos_<?php echo $color['id']; ?>[]"
                                                            class="form-control flex-1" accept="video/*">
                                                        <button type="button" class="btn btn-sm btn-secondary"
                                                            onclick="addVideoField(<?php echo $color['id']; ?>)">
                                                            <i class="fas fa-plus"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                                <small class="text-gray-500">MP4, MOV, AVI, WebM formats supported</small>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>

                                <!-- Template for new color media section (hidden, used by JavaScript) -->
                                <template id="colorMediaTemplate">
                                    <div class="color-media-section border rounded-lg p-4 mb-4" id="media-section-{colorId}">
                                        <div class="flex justify-between items-center mb-4">
                                            <h4 class="font-semibold text-gray-800 flex items-center gap-2">
                                                <span class="color-preview"
                                                    style="background: {hexCode}; width: 24px; height: 24px;"></span>
                                                {colorName} - Media
                                            </h4>
                                            <button type="button" onclick="removeColorMediaSection('{colorId}')"
                                                class="text-red-600 hover:text-red-800 text-sm flex items-center gap-1">
                                                <i class="fas fa-times"></i> Remove Color
                                            </button>
                                        </div>

                                        <!-- Upload new images -->
                                        <div class="mb-4">
                                            <label class="form-label">Upload New Images for {colorName}</label>
                                            <div class="image-upload-fields space-y-2" id="image-fields-{colorId}">
                                                <div class="flex gap-2">
                                                    <input type="file" name="color_images_{colorId}[]"
                                                        class="form-control flex-1" accept="image/*">
                                                    <button type="button" class="btn btn-sm btn-secondary"
                                                        onclick="addImageField('{colorId}')">
                                                        <i class="fas fa-plus"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            <small class="text-gray-500">JPEG, PNG, GIF, WebP formats supported</small>
                                        </div>

                                        <!-- Upload new videos -->
                                        <div class="mb-4">
                                            <label class="form-label">Upload New Videos for {colorName}</label>
                                            <div class="video-upload-fields space-y-2" id="video-fields-{colorId}">
                                                <div class="flex gap-2">
                                                    <input type="file" name="color_videos_{colorId}[]"
                                                        class="form-control flex-1" accept="video/*">
                                                    <button type="button" class="btn btn-sm btn-secondary"
                                                        onclick="addVideoField('{colorId}')">
                                                        <i class="fas fa-plus"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            <small class="text-gray-500">MP4, MOV, AVI, WebM formats supported</small>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            <!-- Size Chart -->
                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">Size Chart (Optional)</label>
                                    <select name="size_chart_id" class="form-control">
                                        <option value="">No Size Chart</option>
                                        <?php foreach ($size_charts as $chart): ?>
                                            <option value="<?php echo $chart['id']; ?>" <?php echo (isset($edit_product['size_chart_id']) && $edit_product['size_chart_id'] == $chart['id']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($chart['chart_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <!-- Meta Tags for Filtering -->
                            <div class="form-group">
                                <label class="form-label">Meta Tags for Filtering</label>
                                <div class="meta-tags-container">
                                    <?php
                                    $selected_meta_tags = [];
                                    if (isset($edit_product['id'])) {
                                        $selected_meta_tags = getProductMetaTags($conn, $edit_product['id']);
                                        $selected_tag_ids = array_column($selected_meta_tags, 'id');
                                    } else {
                                        $selected_tag_ids = [];
                                    }

                                    $meta_tags_by_type = [];
                                    foreach ($meta_tags as $tag) {
                                        $meta_tags_by_type[$tag['type']][] = $tag;
                                    }

                                    foreach ($meta_tags_by_type as $type => $tags):
                                        ?>
                                        <div class="w-full mb-3">
                                            <h4 class="font-semibold text-gray-700 mb-2 capitalize"><?php echo $type; ?></h4>
                                            <div class="flex flex-wrap gap-2">
                                                <?php foreach ($tags as $tag): ?>
                                                    <label class="meta-tag-option">
                                                        <input type="checkbox" name="meta_tags[]" value="<?php echo $tag['id']; ?>"
                                                            <?php echo in_array($tag['id'], $selected_tag_ids) ? 'checked' : ''; ?>>
                                                        <span class="meta-tag-type meta-tag-<?php echo $tag['type']; ?>">
                                                            <?php echo substr($tag['type'], 0, 1); ?>
                                                        </span>
                                                        <span><?php echo htmlspecialchars($tag['name']); ?></span>
                                                    </label>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <small class="text-gray-500">Select meta tags for filtering on the main site.</small>
                            </div>

                            <!-- Status -->
                            <div class="form-group">
                                <label class="flex items-center space-x-2">
                                    <input type="checkbox" name="is_active" class="rounded" <?php echo (isset($edit_product['stock_quantity']) && $edit_product['stock_quantity'] > 0 && isset($edit_product['is_active']) && $edit_product['is_active']) ? 'checked' : ''; ?>
                                        <?php echo (isset($edit_product['stock_quantity']) && $edit_product['stock_quantity'] == 0) ? 'disabled' : ''; ?>>
                                    <span>
                                        Active (Visible in store)
                                        <?php if (isset($edit_product['stock_quantity']) && $edit_product['stock_quantity'] == 0): ?>
                                            <span class="text-red-600 text-sm ml-2">
                                                <i class="fas fa-exclamation-circle"></i> Cannot activate - stock is zero
                                            </span>
                                        <?php endif; ?>
                                    </span>
                                </label>
                            </div>

                            <div class="form-actions">
                                <button type="submit" name="save_product" class="btn btn-primary"
                                    onclick="return validateProductForm()">
                                    <i class="fas fa-save"></i>
                                    <?php echo $action === 'edit' ? 'Update' : 'Save'; ?> Product
                                </button>
                                <a href="?section=<?php echo $currentSection; ?>" class="btn btn-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>

                <?php else: ?>
                    <!-- Product List -->
                    <div class="content-section">
                        <div class="section-header">
                            <h2 class="section-title">
                                <i
                                    class="fas fa-<?php echo $currentSection === 'stitched' ? 'tshirt' : ($currentSection === 'unstitched' ? 'cut' : 'gem'); ?>"></i>
                                <?php echo ucfirst($currentSection); ?> Items
                                <span class="text-gray-500 font-normal text-lg ml-2">
                                    (<?php echo ${$currentSection . '_count'}; ?> items)
                                </span>
                            </h2>
                            <div class="flex space-x-3">
                                <a href="?section=<?php echo $currentSection; ?>&action=add" class="btn btn-primary">
                                    <i class="fas fa-plus"></i>
                                    Add New
                                </a>
                            </div>
                        </div>

                        <?php
                        $products_list = ${$currentSection . '_products'};

                        if (!empty($products_list)):
                            ?>
                            <div class="overflow-x-auto">
                                <table class="data-table">
                                    <thead>
                                        <tr>
                                            <th>Product</th>
                                            <th>Category</th>
                                            <th>Price</th>
                                            <th>Stock</th>
                                            <th>Colors</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($products_list as $product):

                                            $product_tags = getProductMetaTags($conn, $product['id']);
                                            ?>
                                            <tr>
                                                <td class="flex items-center space-x-3">
                                                    <?php
                                                    // Get main image for display
                                                    $main_image = getMainProductImage($conn, $product['id']);
                                                    $image_url = $main_image ? $main_image['image_url'] : null;
                                                    ?>
                                                    <?php if ($image_url): ?>
                                                        <img src="<?php echo htmlspecialchars($image_url); ?>"
                                                            alt="<?php echo htmlspecialchars($product['name']); ?>"
                                                            class="w-12 h-12 rounded object-cover">
                                                    <?php else: ?>
                                                        <div class="w-12 h-12 bg-gray-200 rounded flex items-center justify-center">
                                                            <i class="fas fa-image text-gray-400"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                    <div>
                                                        <div class="font-medium"><?php echo htmlspecialchars($product['name']); ?></div>
                                                    </div>
                                                </td>
                                                <td><?php echo htmlspecialchars($product['category_name'] ?? 'Uncategorized'); ?></td>
                                                <td class="font-semibold">₹<?php echo number_format($product['price'], 2); ?></td>
                                                <td>
                                                    <div class="flex items-center space-x-2">
                                                        <span
                                                            class="stock-badge <?php echo $product['stock_quantity'] == 0 ? 'status-out-stock' : ($product['stock_quantity'] <= ($product['reorder_level'] ?? 5) ? 'status-low-stock' : ''); ?>">
                                                            <?php echo $product['stock_quantity']; ?>
                                                        </span>
                                                        <?php if ($product['stock_quantity'] <= ($product['reorder_level'] ?? 5)): ?>
                                                            <i class="fas fa-exclamation-triangle text-yellow-500" title="Low stock"></i>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <?php
                                                    $product_colors = $conn->query("SELECT c.name FROM product_colors pc JOIN colors c ON pc.color_id = c.id WHERE pc.product_id = " . $product['id']);
                                                    $color_count = 0;
                                                    while ($color = $product_colors->fetch_assoc()):
                                                        if ($color_count < 3):
                                                            ?>
                                                            <span
                                                                class="inline-block px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-700 mr-1 mb-1">
                                                                <?php echo htmlspecialchars($color['name']); ?>
                                                            </span>
                                                            <?php
                                                        endif;
                                                        $color_count++;
                                                    endwhile;
                                                    if ($color_count > 3): ?>
                                                        <span class="text-xs text-gray-500">+<?php echo $color_count - 3; ?> more</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <span
                                                        class="status-badge <?php echo $product['is_active'] ? 'status-active' : 'status-inactive'; ?>">
                                                        <?php echo $product['is_active'] ? 'Active' : 'Inactive'; ?>
                                                        <?php if ($product['stock_quantity'] == 0 && $product['is_active'] == 0): ?>
                                                            <i class="fas fa-exclamation-circle ml-1"
                                                                title="Auto-deactivated due to zero stock"></i>
                                                        <?php endif; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="flex space-x-2">
                                                        <a href="?section=<?php echo $currentSection; ?>&action=edit&id=<?php echo $product['id']; ?>"
                                                            class="btn btn-sm btn-secondary" title="Edit">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <a href="?section=stock&product_id=<?php echo $product['id']; ?>"
                                                            class="btn btn-sm btn-warning" title="Stock">
                                                            <i class="fas fa-box"></i>
                                                        </a>
                                                        <a href="?section=<?php echo $currentSection; ?>&delete=product&id=<?php echo $product['id']; ?>"
                                                            class="btn btn-sm btn-danger"
                                                            onclick="return confirm('Delete this product?')" title="Delete">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <div class="empty-icon">
                                    <i
                                        class="fas fa-<?php echo $currentSection === 'stitched' ? 'tshirt' : ($currentSection === 'unstitched' ? 'cut' : 'gem'); ?>"></i>
                                </div>
                                <h3 class="text-lg font-semibold text-gray-600 mb-2">No <?php echo $currentSection; ?> items yet
                                </h3>
                                <p class="text-gray-500 mb-4">Get started by adding your first <?php echo $currentSection; ?> item.
                                </p>
                                <a href="?section=<?php echo $currentSection; ?>&action=add" class="btn btn-primary">
                                    <i class="fas fa-plus"></i>
                                    Add First Item
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

            <?php elseif ($currentSection === 'categories'): ?>
                <!-- Category Management - UPDATED: Added icon field -->
                <div class="page-header">
                    <h1 class="page-title">Category Management</h1>
                    <p class="page-subtitle">Manage product categories. Add new categories or edit existing ones.</p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Add/Edit Category Form -->
                    <div class="content-section">
                        <div class="section-header">
                            <h2 class="section-title">
                                <i class="fas fa-folder-plus"></i>
                                <?php echo ($action === 'edit' && isset($edit_category)) ? 'Edit' : 'Add'; ?> Category
                            </h2>
                            <?php if ($action === 'edit'): ?>
                                <a href="?section=categories" class="btn btn-secondary">Add New</a>
                            <?php endif; ?>
                        </div>

                        <form method="POST" class="space-y-4">
                            <input type="hidden" name="category_id" value="<?php echo $edit_category['id'] ?? ''; ?>">

                            <div class="form-group">
                                <label class="form-label">Category Name *</label>
                                <input type="text" name="category_name" class="form-control"
                                    value="<?php echo isset($edit_category['name']) ? htmlspecialchars($edit_category['name']) : ''; ?>"
                                    required placeholder="e.g., Lehengas">
                            </div>

                            <div class="form-group">
                                <label class="form-label">Slug (URL-friendly)</label>
                                <input type="text" name="category_slug" class="form-control"
                                    value="<?php echo isset($edit_category['slug']) ? htmlspecialchars($edit_category['slug']) : ''; ?>"
                                    placeholder="lehengas">
                                <small class="text-gray-500">Leave empty to auto-generate from name</small>
                            </div>

                            <!-- NEW: Icon field -->
                            <div class="form-group">
                                <label class="form-label">Icon (Font Awesome)</label>
                                <div class="flex items-center space-x-3">
                                    <div class="icon-preview" id="iconPreview">
                                        <i
                                            class="<?php echo isset($edit_category['icon']) && !empty($edit_category['icon']) ? htmlspecialchars($edit_category['icon']) : 'fas fa-folder'; ?>"></i>
                                    </div>
                                    <input type="text" name="category_icon" id="categoryIcon" class="form-control"
                                        value="<?php echo isset($edit_category['icon']) ? htmlspecialchars($edit_category['icon']) : 'fas fa-folder'; ?>"
                                        placeholder="fas fa-tshirt" oninput="updateIconPreview()">
                                </div>
                                <small class="text-gray-500">Enter Font Awesome icon class (e.g., fas fa-tshirt, fas
                                    fa-gem)</small>
                            </div>

                            <div class="form-actions">
                                <button type="submit" name="save_category" class="btn btn-primary">
                                    <i class="fas fa-save"></i>
                                    <?php echo ($action === 'edit' && isset($edit_category)) ? 'Update' : 'Save'; ?>
                                    Category
                                </button>
                                <?php if ($action === 'edit'): ?>
                                    <a href="?section=categories" class="btn btn-secondary">Cancel</a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>

                    <!-- Categories List - UPDATED: Added icon column -->
                    <div class="content-section">
                        <div class="section-header">
                            <h2 class="section-title">
                                <i class="fas fa-list"></i>
                                All Categories
                                <span class="text-gray-500 font-normal text-lg ml-2">
                                    (<?php echo count($categories); ?> categories)
                                </span>
                            </h2>
                        </div>

                        <?php if (!empty($categories)): ?>
                            <div class="overflow-x-auto">
                                <table class="data-table">
                                    <thead>
                                        <tr>
                                            <th>Icon</th>
                                            <th>Name</th>
                                            <th>Slug</th>
                                            <th>Products</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        foreach ($categories as $category):
                                            $stmt = $conn->prepare("SELECT COUNT(*) as count FROM products WHERE category_id = ?");
                                            $stmt->bind_param("i", $category['id']);
                                            $stmt->execute();
                                            $result = $stmt->get_result();
                                            $product_count = $result->fetch_assoc()['count'];
                                            $stmt->close();
                                            ?>
                                            <tr>
                                                <td>
                                                    <div class="icon-preview">
                                                        <i
                                                            class="<?php echo htmlspecialchars($category['icon'] ?? 'fas fa-folder'); ?>"></i>
                                                    </div>
                                                </td>
                                                <td class="font-medium"><?php echo htmlspecialchars($category['name']); ?></td>
                                                <td class="text-gray-600 font-mono text-sm">
                                                    <?php echo htmlspecialchars($category['slug']); ?>
                                                </td>
                                                <td>
                                                    <span class="stock-badge"><?php echo $product_count; ?></span>
                                                </td>
                                                <td>
                                                    <div class="flex space-x-2">
                                                        <a href="?section=categories&action=edit&id=<?php echo $category['id']; ?>"
                                                            class="btn btn-sm btn-secondary" title="Edit">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <?php if ($product_count == 0): ?>
                                                            <a href="?section=categories&delete=category&id=<?php echo $category['id']; ?>"
                                                                class="btn btn-sm btn-danger"
                                                                onclick="return confirm('Delete this category?')" title="Delete">
                                                                <i class="fas fa-trash"></i>
                                                            </a>
                                                        <?php else: ?>
                                                            <button class="btn btn-sm btn-danger opacity-50 cursor-not-allowed"
                                                                title="Cannot delete - has <?php echo $product_count; ?> product(s)">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="empty-state py-8">
                                <div class="empty-icon">
                                    <i class="fas fa-folder"></i>
                                </div>
                                <h3 class="text-lg font-semibold text-gray-600 mb-2">No Categories Yet</h3>
                                <p class="text-gray-500">Add your first category to get started.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

            <?php elseif ($currentSection === 'meta-tags'): ?>
                <!-- Meta Tags Management -->
                <div class="page-header">
                    <h1 class="page-title">Meta Tags Management</h1>
                    <p class="page-subtitle">Manage meta tags for filtering products on the main site.</p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Add/Edit Meta Tag Form -->
                    <div class="content-section">
                        <div class="section-header">
                            <h2 class="section-title">
                                <i class="fas fa-tag"></i>
                                <?php echo ($action === 'edit' && isset($edit_meta_tag)) ? 'Edit' : 'Add'; ?> Meta Tag
                            </h2>
                            <?php if ($action === 'edit'): ?>
                                <a href="?section=meta-tags" class="btn btn-secondary">Add New</a>
                            <?php endif; ?>
                        </div>

                        <form method="POST" class="space-y-4">
                            <input type="hidden" name="meta_tag_id" value="<?php echo $edit_meta_tag['id'] ?? ''; ?>">

                            <div class="form-group">
                                <label class="form-label">Tag Name *</label>
                                <input type="text" name="meta_tag_name" class="form-control"
                                    value="<?php echo isset($edit_meta_tag['name']) ? htmlspecialchars($edit_meta_tag['name']) : ''; ?>"
                                    required placeholder="e.g., Wedding, Silk, Embroidery">
                            </div>

                            <div class="form-group">
                                <label class="form-label">Slug (URL-friendly)</label>
                                <input type="text" name="meta_tag_slug" class="form-control"
                                    value="<?php echo isset($edit_meta_tag['slug']) ? htmlspecialchars($edit_meta_tag['slug']) : ''; ?>"
                                    placeholder="wedding">
                                <small class="text-gray-500">Leave empty to auto-generate from name</small>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Tag Type *</label>
                                <select name="meta_tag_type" class="form-control" required>
                                    <option value="">Select Type</option>
                                    <?php foreach ($meta_tag_types as $type): ?>
                                        <option value="<?php echo $type; ?>" <?php echo (isset($edit_meta_tag['type']) && $edit_meta_tag['type'] == $type) ? 'selected' : ''; ?>>
                                            <?php echo ucfirst($type); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="text-gray-500">Used for grouping filters on the site</small>
                            </div>

                            <div class="form-actions">
                                <button type="submit" name="save_meta_tag" class="btn btn-primary">
                                    <i class="fas fa-save"></i>
                                    <?php echo ($action === 'edit' && isset($edit_meta_tag)) ? 'Update' : 'Save'; ?> Meta
                                    Tag
                                </button>
                                <?php if ($action === 'edit'): ?>
                                    <a href="?section=meta-tags" class="btn btn-secondary">Cancel</a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>

                    <!-- Meta Tags List -->
                    <div class="content-section">
                        <div class="section-header">
                            <h2 class="section-title">
                                <i class="fas fa-tags"></i>
                                All Meta Tags
                                <span class="text-gray-500 font-normal text-lg ml-2">
                                    (<?php echo count($meta_tags); ?> tags)
                                </span>
                            </h2>
                        </div>

                        <?php if (!empty($meta_tags)): ?>
                            <div class="overflow-x-auto">
                                <table class="data-table">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Slug</th>
                                            <th>Type</th>
                                            <th>Usage</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $meta_tags_by_type = [];
                                        foreach ($meta_tags as $tag) {
                                            $meta_tags_by_type[$tag['type']][] = $tag;
                                        }

                                        foreach ($meta_tags_by_type as $type => $tags):
                                            foreach ($tags as $tag):
                                                $stmt = $conn->prepare("SELECT COUNT(*) as count FROM product_meta_tags WHERE meta_tag_id = ?");
                                                $stmt->bind_param("i", $tag['id']);
                                                $stmt->execute();
                                                $result = $stmt->get_result();
                                                $usage_count = $result->fetch_assoc()['count'];
                                                $stmt->close();
                                                ?>
                                                <tr>
                                                    <td class="font-medium"><?php echo htmlspecialchars($tag['name']); ?></td>
                                                    <td class="text-gray-600 font-mono text-sm">
                                                        <?php echo htmlspecialchars($tag['slug']); ?>
                                                    </td>
                                                    <td>
                                                        <span class="meta-tag-type meta-tag-<?php echo $tag['type']; ?>">
                                                            <?php echo ucfirst($tag['type']); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="stock-badge"><?php echo $usage_count; ?></span>
                                                    </td>
                                                    <td>
                                                        <div class="flex space-x-2">
                                                            <a href="?section=meta-tags&action=edit&id=<?php echo $tag['id']; ?>"
                                                                class="btn btn-sm btn-secondary" title="Edit">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                            <?php if ($usage_count == 0): ?>
                                                                <a href="?section=meta-tags&delete=meta_tag&id=<?php echo $tag['id']; ?>"
                                                                    class="btn btn-sm btn-danger"
                                                                    onclick="return confirm('Delete this meta tag?')" title="Delete">
                                                                    <i class="fas fa-trash"></i>
                                                                </a>
                                                            <?php else: ?>
                                                                <button class="btn btn-sm btn-danger opacity-50 cursor-not-allowed"
                                                                    title="Cannot delete - used by <?php echo $usage_count; ?> product(s)">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            <?php endif; ?>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="empty-state py-8">
                                <div class="empty-icon">
                                    <i class="fas fa-tags"></i>
                                </div>
                                <h3 class="text-lg font-semibold text-gray-600 mb-2">No Meta Tags Yet</h3>
                                <p class="text-gray-500">Add your first meta tag to get started.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

            <?php elseif ($currentSection === 'stock'): ?>
                <!-- Stock Management -->
                <div class="page-header">
                    <h1 class="page-title">Stock Management</h1>
                    <p class="page-subtitle">Monitor inventory levels, adjust stock quantities, and handle low stock alerts.
                    </p>
                    <div class="mt-2 text-sm text-gray-600">
                        <i class="fas fa-info-circle mr-1"></i>
                        Products with zero stock are automatically deactivated. They will be reactivated when stock is
                        added.
                    </div>
                </div>

                <!-- Stock Overview -->
                <div class="stats-grid mb-6">
                    <div class="stat-card">
                        <div class="stat-content">
                            <div class="stat-info">
                                <h3>Total Stock Value</h3>
                                <div class="stat-value">₹<?php echo number_format($stats['total_stock_value'] ?? 0, 2); ?>
                                </div>
                            </div>
                            <div class="stat-icon stitched">
                                <i class="fas fa-rupee-sign"></i>
                            </div>
                        </div>
                    </div>

                    <div class="stat-card low-stock">
                        <div class="stat-content">
                            <div class="stat-info">
                                <h3>Low Stock Items</h3>
                                <div class="stat-value"><?php echo $stats['low_stock'] ?? 0; ?></div>
                                <div class="stat-change negative">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    <span>Needs attention</span>
                                </div>
                            </div>
                            <div class="stat-icon accessories">
                                <i class="fas fa-exclamation"></i>
                            </div>
                        </div>
                    </div>

                    <div class="stat-card out-stock">
                        <div class="stat-content">
                            <div class="stat-info">
                                <h3>Out of Stock</h3>
                                <div class="stat-value"><?php echo $stats['out_of_stock'] ?? 0; ?></div>
                                <div class="stat-change negative">
                                    <i class="fas fa-times-circle"></i>
                                    <span>Restock needed</span>
                                </div>
                            </div>
                            <div class="stat-icon accessories">
                                <i class="fas fa-ban"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Stock Adjustment Form -->
                <?php
                $adjust_product_id = $_GET['product_id'] ?? 0;
                $adjust_product = $adjust_product_id ? getProductById($conn, $adjust_product_id) : null;
                $all_products_including_inactive = getAllProductsWithStock($conn, true);
                ?>
                <div class="content-section mb-6">
                    <div class="section-header">
                        <h2 class="section-title">
                            <i class="fas fa-edit"></i>
                            Adjust Stock
                        </h2>
                    </div>

                    <form method="GET" class="space-y-4">
                        <input type="hidden" name="section" value="stock">
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Select Product *</label>
                                <select name="product_id" class="form-control" required onchange="this.form.submit()">
                                    <option value="">Choose a product</option>
                                    <?php foreach ($all_products_including_inactive as $product): ?>
                                        <option value="<?php echo $product['id']; ?>" <?php echo $adjust_product_id == $product['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($product['name']); ?>
                                            (Stock: <?php echo $product['stock_quantity']; ?>)
                                            <?php if (!$product['is_active']): ?>
                                                - [INACTIVE]
                                            <?php endif; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <?php if ($adjust_product): ?>
                                <div class="form-group">
                                    <label class="form-label">Current Status</label>
                                    <div class="p-3 bg-gray-50 rounded border">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <div class="text-lg font-bold text-gray-800">
                                                    <?php echo $adjust_product['stock_quantity']; ?> units
                                                </div>
                                                <div class="text-sm text-gray-600 mt-1">
                                                    <?php echo htmlspecialchars($adjust_product['name']); ?>
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                <span
                                                    class="status-badge <?php echo $adjust_product['is_active'] ? 'status-active' : 'status-inactive'; ?>">
                                                    <?php echo $adjust_product['is_active'] ? 'Active' : 'Inactive'; ?>
                                                </span>
                                            </div>
                                        </div>
                                        <?php if ($adjust_product['stock_quantity'] == 0): ?>
                                            <div class="text-sm text-red-600 mt-2">
                                                <i class="fas fa-exclamation-circle"></i> Product is deactivated due to zero stock
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </form>

                    <?php if ($adjust_product): ?>
                        <form method="POST" class="space-y-4 mt-6 border-t pt-6">
                            <input type="hidden" name="product_id" value="<?php echo $adjust_product_id; ?>">

                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">Adjustment Type *</label>
                                    <select name="adjustment_type" class="form-control" required>
                                        <option value="add">Add Stock</option>
                                        <option value="remove">Remove Stock</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Quantity *</label>
                                    <input type="number" name="quantity" class="form-control" min="1" required>
                                    <small class="text-gray-500">
                                        <?php if ($adjust_product['stock_quantity'] == 0): ?>
                                            <span class="text-green-600 font-semibold">
                                                <i class="fas fa-info-circle"></i> Adding stock will automatically reactivate this
                                                product
                                            </span>
                                        <?php else: ?>
                                            Enter the quantity to add or remove
                                        <?php endif; ?>
                                    </small>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label">Notes (Optional)</label>
                                <textarea name="notes" class="form-control" rows="2"
                                    placeholder="Reason for adjustment..."></textarea>
                            </div>

                            <div class="form-actions">
                                <button type="submit" name="adjust_stock" class="btn btn-primary">
                                    <i class="fas fa-check"></i>
                                    Apply Adjustment
                                </button>
                                <a href="?section=stock" class="btn btn-secondary">Cancel</a>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>

                <!-- Low Stock & Out of Stock -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Low Stock Items -->
                    <div class="content-section">
                        <div class="section-header">
                            <h2 class="section-title">
                                <i class="fas fa-exclamation-triangle"></i>
                                Low Stock Items
                            </h2>
                        </div>

                        <?php if (!empty($low_stock_products)): ?>
                            <div class="overflow-x-auto">
                                <table class="data-table">
                                    <thead>
                                        <tr>
                                            <th>Product</th>
                                            <th>Current Stock</th>
                                            <th>Reorder Level</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($low_stock_products as $product): ?>
                                            <tr>
                                                <td class="flex items-center space-x-3">
                                                    <?php
                                                    $main_image = getMainProductImage($conn, $product['id']);
                                                    $image_url = $main_image ? $main_image['image_url'] : null;
                                                    ?>
                                                    <?php if ($image_url): ?>
                                                        <img src="<?php echo htmlspecialchars($image_url); ?>"
                                                            class="w-10 h-10 rounded object-cover">
                                                    <?php endif; ?>
                                                    <div>
                                                        <div class="font-medium"><?php echo htmlspecialchars($product['name']); ?>
                                                        </div>
                                                        <div class="text-sm text-gray-500">
                                                            <?php echo htmlspecialchars($product['category_name']); ?>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span
                                                        class="stock-badge status-low-stock"><?php echo $product['stock_quantity']; ?></span>
                                                </td>
                                                <td><?php echo $product['reorder_level']; ?></td>
                                                <td>
                                                    <span
                                                        class="status-badge <?php echo $product['is_active'] ? 'status-active' : 'status-inactive'; ?>">
                                                        <?php echo $product['is_active'] ? 'Active' : 'Inactive'; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="?section=stock&product_id=<?php echo $product['id']; ?>"
                                                        class="btn btn-sm btn-warning">
                                                        <i class="fas fa-edit"></i> Adjust
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="empty-state py-8">
                                <div class="empty-icon">
                                    <i class="far fa-check-circle"></i>
                                </div>
                                <h3 class="text-lg font-semibold text-gray-600 mb-2">No Low Stock Items</h3>
                                <p class="text-gray-500">All products have sufficient stock levels.</p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Out of Stock Items -->
                    <div class="content-section">
                        <div class="section-header">
                            <h2 class="section-title">
                                <i class="fas fa-ban"></i>
                                Out of Stock
                            </h2>
                        </div>

                        <?php if (!empty($out_of_stock_products)): ?>
                            <div class="overflow-x-auto">
                                <table class="data-table">
                                    <thead>
                                        <tr>
                                            <th>Product</th>
                                            <th>Last Restocked</th>
                                            <th>Price</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($out_of_stock_products as $product): ?>
                                            <tr>
                                                <td class="flex items-center space-x-3">
                                                    <?php
                                                    $main_image = getMainProductImage($conn, $product['id']);
                                                    $image_url = $main_image ? $main_image['image_url'] : null;
                                                    ?>
                                                    <?php if ($image_url): ?>
                                                        <img src="<?php echo htmlspecialchars($image_url); ?>"
                                                            class="w-10 h-10 rounded object-cover">
                                                    <?php endif; ?>
                                                    <div>
                                                        <div class="font-medium"><?php echo htmlspecialchars($product['name']); ?>
                                                        </div>
                                                        <div class="text-sm text-gray-500">
                                                            <?php echo htmlspecialchars($product['category_name']); ?>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <?php echo $product['last_restocked'] ? date('M d, Y', strtotime($product['last_restocked'])) : 'Never'; ?>
                                                </td>
                                                <td class="font-semibold">₹<?php echo number_format($product['price'], 2); ?></td>
                                                <td>
                                                    <span
                                                        class="status-badge <?php echo $product['is_active'] ? 'status-active' : 'status-inactive'; ?>">
                                                        <?php echo $product['is_active'] ? 'Active' : 'Inactive'; ?>
                                                        <?php if ($product['stock_quantity'] == 0 && $product['is_active'] == 0): ?>
                                                            <i class="fas fa-exclamation-circle ml-1" title="Auto-deactivated"></i>
                                                        <?php endif; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="?section=stock&product_id=<?php echo $product['id']; ?>"
                                                        class="btn btn-sm btn-danger">
                                                        <i class="fas fa-plus"></i> Restock
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="empty-state py-8">
                                <div class="empty-icon">
                                    <i class="far fa-check-circle"></i>
                                </div>
                                <h3 class="text-lg font-semibold text-gray-600 mb-2">All Products In Stock</h3>
                                <p class="text-gray-500">Great! No out-of-stock items.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

            <?php elseif ($currentSection === 'colors'): ?>
                <!-- Color Management -->
                <div class="page-header">
                    <h1 class="page-title">Color Management</h1>
                    <p class="page-subtitle">Manage color palette for your products. Add new colors or edit existing ones.
                    </p>
                </div>

                <!-- Add Color Form -->
                <div class="content-section mb-6">
                    <div class="section-header">
                        <h2 class="section-title">
                            <i class="fas fa-plus-circle"></i>
                            Add New Color
                        </h2>
                    </div>

                    <form method="POST" class="space-y-4">
                        <div class="form-row">
                            <div class="form-group">
                                <label class="form-label">Color Name *</label>
                                <input type="text" name="color_name" class="form-control" required
                                    placeholder="e.g., Royal Blue">
                            </div>

                            <div class="form-group">
                                <label class="form-label">Hex Color Code *</label>
                                <div class="flex items-center space-x-3">
                                    <input type="color" name="hex_code" class="w-12 h-12 rounded cursor-pointer"
                                        value="#4169E1" title="Choose color">
                                    <input type="text" name="hex_code_text" class="form-control flex-1"
                                        pattern="^#[0-9A-Fa-f]{6}$" placeholder="#4169E1"
                                        oninput="document.getElementsByName('hex_code')[0].value = this.value">
                                </div>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="submit" name="add_color" class="btn btn-primary">
                                <i class="fas fa-plus"></i>
                                Add Color
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Colors Grid -->
                <div class="content-section">
                    <div class="section-header">
                        <h2 class="section-title">
                            <i class="fas fa-palette"></i>
                            Available Colors
                            <span class="text-gray-500 font-normal text-lg ml-2">
                                (<?php echo count($colors); ?> colors)
                            </span>
                        </h2>
                    </div>

                    <?php if (!empty($colors)): ?>
                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
                            <?php foreach ($colors as $color): ?>
                                <div class="border rounded-lg p-4 hover:shadow-md transition-shadow">
                                    <div class="flex items-center justify-between mb-3">
                                        <div class="color-preview" style="background: <?php echo $color['hex_code']; ?>"></div>
                                        <div class="text-right">
                                            <span class="text-xs text-gray-500">
                                                <?php echo date('M d', strtotime($color['created_at'])); ?>
                                            </span>
                                        </div>
                                    </div>
                                    <h3 class="font-semibold text-gray-800 mb-1"><?php echo htmlspecialchars($color['name']); ?>
                                    </h3>
                                    <div class="text-sm text-gray-600 font-mono mb-3"><?php echo $color['hex_code']; ?></div>
                                    <div class="flex justify-between">
                                        <button class="text-sm text-primary hover:underline"
                                            onclick="copyToClipboard('<?php echo $color['hex_code']; ?>')">
                                            Copy Code
                                        </button>
                                        <a href="?section=colors&delete=color&id=<?php echo $color['id']; ?>"
                                            class="text-sm text-red-600 hover:underline"
                                            onclick="return confirm('Delete this color?')">
                                            Delete
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <div class="empty-icon">
                                <i class="fas fa-palette"></i>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-600 mb-2">No Colors Yet</h3>
                            <p class="text-gray-500">Add your first color to get started.</p>
                        </div>
                    <?php endif; ?>
                </div>

            <?php elseif ($currentSection === 'sizes'): ?>
                <!-- Size Charts Management - UPDATED: Dynamic fields -->
                <div class="page-header">
                    <h1 class="page-title">Size Charts</h1>
                    <p class="page-subtitle">Manage size charts for different product types. Create templates with custom
                        measurement fields.</p>
                </div>

                <!-- Size Charts List -->
                <div class="content-section">
                    <div class="section-header">
                        <h2 class="section-title">
                            <i class="fas fa-ruler-combined"></i>
                            Available Size Charts
                        </h2>
                        <button onclick="openSizeChartModal()" class="btn btn-primary">
                            <i class="fas fa-plus"></i>
                            Add New Chart
                        </button>
                    </div>

                    <?php if (!empty($size_charts)): ?>
                        <div class="space-y-4">
                            <?php foreach ($size_charts as $chart):
                                $measurements = json_decode($chart['measurements'], true);
                                ?>
                                <div class="border rounded-lg p-4 hover:shadow-md transition-shadow">
                                    <div class="flex justify-between items-start mb-3">
                                        <h3 class="text-lg font-semibold text-gray-800">
                                            <?php echo htmlspecialchars($chart['chart_name']); ?>
                                        </h3>
                                        <span class="text-sm text-gray-500">
                                            Created: <?php echo date('M d, Y', strtotime($chart['created_at'])); ?>
                                        </span>
                                    </div>

                                    <?php if ($measurements && is_array($measurements)): ?>
                                        <div class="overflow-x-auto">
                                            <table class="min-w-full bg-gray-50 rounded">
                                                <thead>
                                                    <tr class="bg-gray-100">
                                                        <th class="px-3 py-2 text-left text-sm font-medium text-gray-700">Size</th>
                                                        <?php
                                                        // Get all measurement keys from first size
                                                        $first_size = reset($measurements);
                                                        if ($first_size && is_array($first_size)):
                                                            foreach (array_keys($first_size) as $measurement_key): ?>
                                                                <th class="px-3 py-2 text-left text-sm font-medium text-gray-700 capitalize">
                                                                    <?php echo htmlspecialchars($measurement_key); ?>
                                                                </th>
                                                            <?php endforeach;
                                                        endif; ?>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($measurements as $size => $dims): ?>
                                                        <tr class="border-t">
                                                            <td class="px-3 py-2 text-sm font-medium text-gray-900">
                                                                <?php echo htmlspecialchars($size); ?>
                                                            </td>
                                                            <?php if (is_array($dims)):
                                                                foreach ($dims as $value): ?>
                                                                    <td class="px-3 py-2 text-sm text-gray-700">
                                                                        <?php echo htmlspecialchars($value); ?>
                                                                    </td>
                                                                <?php endforeach;
                                                            endif; ?>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php endif; ?>

                                    <div class="mt-4 flex justify-end space-x-2">
                                        <button
                                            onclick="editSizeChart(<?php echo $chart['id']; ?>, '<?php echo htmlspecialchars(addslashes($chart['chart_name'])); ?>', '<?php echo htmlspecialchars(addslashes($chart['measurements'])); ?>')"
                                            class="btn btn-sm btn-secondary">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <a href="?section=sizes&delete=size_chart&id=<?php echo $chart['id']; ?>"
                                            class="btn btn-sm btn-danger" onclick="return confirm('Delete this size chart?')">
                                            <i class="fas fa-trash"></i> Delete
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <div class="empty-icon">
                                <i class="fas fa-ruler-combined"></i>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-600 mb-2">No Size Charts Yet</h3>
                            <p class="text-gray-500">Create your first size chart template.</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Size Chart Modal - UPDATED: Dynamic fields -->
                <div id="sizeChartModal" class="modal">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h3 class="text-xl font-semibold text-gray-800" id="modalTitle">Add New Size Chart</h3>
                            <button onclick="closeSizeChartModal()" class="text-gray-500 hover:text-gray-700">
                                <i class="fas fa-times text-xl"></i>
                            </button>
                        </div>
                        <div class="modal-body">
                            <form method="POST" id="sizeChartForm">
                                <input type="hidden" name="size_chart_id" id="sizeChartId" value="">
                                <div class="space-y-4">
                                    <div class="form-group">
                                        <label class="form-label">Chart Name *</label>
                                        <input type="text" name="chart_name" id="chartName" class="form-control" required
                                            placeholder="e.g., Standard Indian Sizes">
                                    </div>

                                    <div class="form-group">
                                        <label class="form-label">Size Entries</label>
                                        <div id="sizeEntriesContainer" class="space-y-4">
                                            <!-- Size entries will be added here -->
                                        </div>
                                        <button type="button" onclick="addSizeEntry()"
                                            class="btn btn-sm btn-secondary mt-2">
                                            <i class="fas fa-plus"></i> Add Size Entry
                                        </button>
                                    </div>
                                </div>

                                <div class="form-actions mt-6">
                                    <button type="submit" name="save_size_chart" class="btn btn-primary">
                                        <i class="fas fa-save"></i>
                                        Save Size Chart
                                    </button>
                                    <button type="button" onclick="closeSizeChartModal()" class="btn btn-secondary">
                                        Cancel
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

            <?php elseif ($currentSection === 'transactions'): ?>
                <!-- Stock Transactions -->
                <div class="page-header">
                    <h1 class="page-title">Stock Transactions</h1>
                    <p class="page-subtitle">Track all stock movements, adjustments, and inventory changes.</p>
                </div>

                <!-- Export Button -->
                <div class="content-section mb-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800">Export Transactions</h3>
                            <p class="text-gray-600 text-sm">Download transaction history in PDF format</p>
                        </div>
                        <a href="export_transactions.php" target="_blank" class="btn btn-primary">
                            <i class="fas fa-file-export"></i> Export Report
                        </a>
                    </div>
                </div>

                <!-- Transactions List -->
                <div class="content-section">
                    <div class="section-header">
                        <h2 class="section-title">
                            <i class="fas fa-exchange-alt"></i>
                            Recent Stock Transactions
                        </h2>
                    </div>

                    <?php if (!empty($stock_transactions)): ?>
                        <div class="overflow-x-auto">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Date & Time</th>
                                        <th>Product</th>
                                        <th>Transaction</th>
                                        <th>Quantity</th>
                                        <th>Previous</th>
                                        <th>New</th>
                                        <th>Performed By</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($stock_transactions as $trans): ?>
                                        <tr>
                                            <td>
                                                <div class="text-sm font-medium text-gray-900">
                                                    <?php echo date('M d, Y', strtotime($trans['created_at'])); ?>
                                                </div>
                                                <div class="text-sm text-gray-500">
                                                    <?php echo date('h:i A', strtotime($trans['created_at'])); ?>
                                                </div>
                                            </td>
                                            <td class="font-medium"><?php echo htmlspecialchars($trans['product_name']); ?></td>
                                            <td>
                                                <span
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $trans['quantity'] > 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                                    <i
                                                        class="fas fa-<?php echo $trans['quantity'] > 0 ? 'arrow-up' : 'arrow-down'; ?> mr-1"></i>
                                                    <?php echo ucfirst(str_replace('_', ' ', $trans['transaction_type'])); ?>
                                                </span>
                                                <?php if ($trans['notes']): ?>
                                                    <div class="text-xs text-gray-500 mt-1">
                                                        <?php echo htmlspecialchars($trans['notes']); ?>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td
                                                class="font-bold <?php echo $trans['quantity'] > 0 ? 'text-green-600' : 'text-red-600'; ?>">
                                                <?php echo $trans['quantity'] > 0 ? '+' : ''; ?>             <?php echo $trans['quantity']; ?>
                                            </td>
                                            <td class="text-gray-600"><?php echo $trans['previous_quantity']; ?></td>
                                            <td class="font-semibold"><?php echo $trans['new_quantity']; ?></td>
                                            <td class="text-gray-600"><?php echo htmlspecialchars($trans['performed_by']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <div class="empty-icon">
                                <i class="fas fa-exchange-alt"></i>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-600 mb-2">No Transactions Yet</h3>
                            <p class="text-gray-500">Stock transactions will appear here when you adjust inventory.</p>
                        </div>
                    <?php endif; ?>
                </div>

            <?php elseif ($currentSection === 'settings'): ?>
                <!-- Settings -->
                <div class="page-header">
                    <h1 class="page-title">Settings</h1>
                    <p class="page-subtitle">Configure your boutique settings and preferences.</p>
                </div>

                <!-- Settings Form -->
                <div class="content-section">
                    <div class="section-header">
                        <h2 class="section-title">
                            <i class="fas fa-cog"></i>
                            System Settings
                        </h2>
                    </div>

                    <form method="POST" action="?section=settings">
                        <div class="space-y-6">
                            <?php foreach ($all_settings as $key => $value):
                                $label = ucwords(str_replace('_', ' ', $key));
                                $field_type = 'text';

                                if (strpos($key, 'url') !== false) {
                                    $field_type = 'url';
                                } elseif (strpos($key, 'phone') !== false) {
                                    $field_type = 'tel';
                                } elseif (strpos($key, 'level') !== false) {
                                    $field_type = 'number';
                                } elseif (strpos($key, 'rate') !== false) {
                                    $field_type = 'number';
                                    $step = '0.1';
                                }
                                ?>
                                <div class="form-group">
                                    <label class="form-label"><?php echo htmlspecialchars($label); ?></label>
                                    <?php if ($key === 'show_social_links'): ?>
                                        <select name="settings[<?php echo $key; ?>]" class="form-control">
                                            <option value="1" <?php echo $value == '1' ? 'selected' : ''; ?>>Yes - Show on website
                                            </option>
                                            <option value="0" <?php echo $value == '0' ? 'selected' : ''; ?>>No - Hide from website
                                            </option>
                                        </select>
                                    <?php else: ?>
                                        <input type="<?php echo $field_type; ?>" name="settings[<?php echo $key; ?>]"
                                            class="form-control" value="<?php echo htmlspecialchars($value); ?>" <?php echo isset($step) ? 'step="' . $step . '"' : ''; ?>             <?php echo $field_type === 'number' ? 'min="0"' : ''; ?>             <?php echo $field_type === 'number' && strpos($key, 'level') !== false ? 'min="1"' : ''; ?>>
                                    <?php endif; ?>

                                    <?php if ($key === 'reorder_level_default'): ?>
                                        <small class="text-gray-500">Default reorder level for new products</small>
                                    <?php elseif ($key === 'default_growth_rate'): ?>
                                        <small class="text-gray-500">Default growth percentage for dashboard statistics</small>
                                    <?php elseif (strpos($key, 'url') !== false): ?>
                                        <small class="text-gray-500">Full URL including https://</small>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="form-actions mt-6">
                            <button type="submit" name="save_settings" class="btn btn-primary">
                                <i class="fas fa-save"></i>
                                Save Settings
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Auto-deactivation Info Card -->
                <div class="content-section mt-6">
                    <div class="section-header">
                        <h2 class="section-title">
                            <i class="fas fa-robot"></i>
                            Auto-Deactivation System
                        </h2>
                    </div>
                    <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <i class="fas fa-info-circle text-blue-500 text-xl mt-1"></i>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-lg font-semibold text-blue-800">Automatic Product Deactivation</h3>
                                <div class="mt-2 text-blue-700">
                                    <p>The system automatically deactivates products when their stock reaches zero.</p>
                                    <p class="mt-1">This prevents customers from ordering out-of-stock items.</p>
                                    <p class="mt-1">Products are automatically reactivated when stock is added.</p>
                                </div>
                                <div class="mt-3 text-sm text-blue-600">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    <strong>Note:</strong> Manual reactivation is not allowed while stock is zero.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            <?php endif; ?>
    </div>
    </main>
    </div>

    <script>
        // Track which colors have media sections
        const colorMediaSections = new Set();
        const isEditMode = <?php echo ($action === 'edit' && isset($edit_product['id'])) ? 'true' : 'false'; ?>;
        const isAddMode = <?php echo ($action === 'add') ? 'true' : 'false'; ?>;

        // Initialize: For edit mode, track existing colors that already have media sections
        <?php if ($action === 'edit' && isset($edit_product['colors'])): ?>
            <?php foreach ($edit_product['colors'] as $color): ?>
                colorMediaSections.add('<?php echo $color['id']; ?>');
            <?php endforeach; ?>
        <?php endif; ?>

        // Handle color selection
        function handleColorSelection(checkbox) {
            const colorId = checkbox.value;
            const colorName = checkbox.getAttribute('data-color-name');
            const hexCode = checkbox.getAttribute('data-hex-code');

            if (checkbox.checked) {
                // Hide the "no colors selected" message
                const noColorsMsg = document.getElementById('noColorsSelected');
                if (noColorsMsg) {
                    noColorsMsg.classList.add('hidden');
                }

                // Add media section if it doesn't exist
                if (!colorMediaSections.has(colorId)) {
                    createColorMediaSection(colorId, colorName, hexCode);
                    colorMediaSections.add(colorId);
                }
            } else {
                // Remove media section
                if (colorMediaSections.has(colorId)) {
                    removeColorMediaSection(colorId);
                    colorMediaSections.delete(colorId);
                }

                // Show "no colors selected" if no colors are selected
                if (colorMediaSections.size === 0) {
                    const noColorsMsg = document.getElementById('noColorsSelected');
                    if (noColorsMsg) {
                        noColorsMsg.classList.remove('hidden');
                    }
                }
            }

            // Validate color selection
            validateColorSelection();
        }

        // Create a new color media section
        function createColorMediaSection(colorId, colorName, hexCode) {
            const container = document.getElementById('colorMediaContainer');
            const template = document.getElementById('colorMediaTemplate');

            if (!container || !template) {
                console.error('Container or template not found');
                return;
            }

            // Check if section already exists
            if (document.getElementById(`media-section-${colorId}`)) {
                return;
            }

            // Create the section
            const html = template.innerHTML
                .replace(/{colorId}/g, colorId)
                .replace(/{colorName}/g, colorName)
                .replace(/{hexCode}/g, hexCode);

            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = html;

            // Insert before the "no colors selected" message
            const noColorsMsg = document.getElementById('noColorsSelected');
            if (noColorsMsg) {
                container.insertBefore(tempDiv.firstElementChild, noColorsMsg);
            } else {
                // If no message exists, append to container
                container.appendChild(tempDiv.firstElementChild);
            }
        }

        // Remove color media section
        function removeColorMediaSection(colorId) {
            const section = document.getElementById(`media-section-${colorId}`);
            if (section) {
                section.remove();
            }

            // Also uncheck the corresponding checkbox
            const checkbox = document.querySelector(`.color-checkbox[value="${colorId}"]`);
            if (checkbox) {
                checkbox.checked = false;
            }
        }

        // Add image field
        function addImageField(colorId) {
            const container = document.getElementById(`image-fields-${colorId}`);
            if (!container) return;

            const newField = document.createElement('div');
            newField.className = 'flex gap-2';
            newField.innerHTML = `
            <input type="file" name="color_images_${colorId}[]" class="form-control flex-1" accept="image/*">
            <button type="button" class="btn btn-sm btn-danger" onclick="this.parentElement.remove()">
                <i class="fas fa-minus"></i>
            </button>
        `;
            container.appendChild(newField);
        }

        // Add video field
        function addVideoField(colorId) {
            const container = document.getElementById(`video-fields-${colorId}`);
            if (!container) return;

            const newField = document.createElement('div');
            newField.className = 'flex gap-2';
            newField.innerHTML = `
            <input type="file" name="color_videos_${colorId}[]" class="form-control flex-1" accept="video/*">
            <button type="button" class="btn btn-sm btn-danger" onclick="this.parentElement.remove()">
                <i class="fas fa-minus"></i>
            </button>
        `;
            container.appendChild(newField);
        }

        // Validate at least one color is selected
        function validateColorSelection() {
            const colorCheckboxes = document.querySelectorAll('.color-checkbox:checked');
            const colorError = document.getElementById('colorError');

            if (!colorError) return true;

            if (colorCheckboxes.length === 0) {
                colorError.style.display = 'block';
                return false;
            }

            colorError.style.display = 'none';
            return true;
        }

        // Validate product form
        function validateProductForm() {
            if (!validateColorSelection()) {
                alert('Please select at least one color for the product.');
                return false;
            }
            return true;
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function () {
            // Hide the no colors message if we already have sections
            if (colorMediaSections.size > 0) {
                const noColorsMsg = document.getElementById('noColorsSelected');
                if (noColorsMsg) {
                    noColorsMsg.classList.add('hidden');
                }
            }

            // Add event listeners to all color checkboxes
            document.querySelectorAll('.color-checkbox').forEach(checkbox => {
                // Add click event listener
                checkbox.addEventListener('click', function () {
                    handleColorSelection(this);
                });

                // For ADD mode: If checkbox is checked on page load (shouldn't happen but just in case)
                if (isAddMode && checkbox.checked && !colorMediaSections.has(checkbox.value)) {
                    const colorId = checkbox.value;
                    const colorName = checkbox.getAttribute('data-color-name');
                    const hexCode = checkbox.getAttribute('data-hex-code');
                    createColorMediaSection(colorId, colorName, hexCode);
                    colorMediaSections.add(colorId);

                    // Hide the "no colors selected" message
                    const noColorsMsg = document.getElementById('noColorsSelected');
                    if (noColorsMsg) {
                        noColorsMsg.classList.add('hidden');
                    }
                }
            });
        });
        // Size Chart Management
        let sizeEntryCounter = 0;

        function openSizeChartModal(chartId = null, chartName = '', measurements = null) {
            const modal = document.getElementById('sizeChartModal');
            const modalTitle = document.getElementById('modalTitle');
            const sizeChartId = document.getElementById('sizeChartId');
            const chartNameInput = document.getElementById('chartName');

            if (chartId) {
                modalTitle.textContent = 'Edit Size Chart';
                sizeChartId.value = chartId;
                chartNameInput.value = chartName;

                // Clear existing entries
                const container = document.getElementById('sizeEntriesContainer');
                container.innerHTML = '';

                // Add size entries from measurements
                if (measurements) {
                    const parsedMeasurements = JSON.parse(measurements);
                    Object.keys(parsedMeasurements).forEach((size, index) => {
                        addSizeEntry();
                        const entry = container.lastElementChild;
                        const sizeInput = entry.querySelector('.size-input');
                        sizeInput.value = size;

                        // Fill measurement fields
                        const measurementsData = parsedMeasurements[size];
                        Object.keys(measurementsData).forEach(measurement => {
                            const input = entry.querySelector(`[name^="measurements[${index}][${measurement}]"]`);
                            if (input) {
                                input.value = measurementsData[measurement];
                            }
                        });
                    });
                }
            } else {
                modalTitle.textContent = 'Add New Size Chart';
                sizeChartId.value = '';
                chartNameInput.value = '';
                document.getElementById('sizeEntriesContainer').innerHTML = '';
                addSizeEntry(); // Add one empty entry
            }

            modal.classList.add('active');
        }

        function closeSizeChartModal() {
            document.getElementById('sizeChartModal').classList.remove('active');
        }

        function addSizeEntry() {
            const container = document.getElementById('sizeEntriesContainer');
            const entryId = sizeEntryCounter++;

            const entryDiv = document.createElement('div');
            entryDiv.className = 'size-entry';
            entryDiv.innerHTML = `
        <div class="flex items-center justify-between mb-2">
            <h4 class="font-medium text-gray-700">Size Entry</h4>
            <button type="button" onclick="this.parentElement.parentElement.remove()" 
                    class="text-red-600 hover:text-red-800 text-sm">
                <i class="fas fa-trash"></i> Remove
            </button>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-3 mb-3">
            <div>
                <label class="form-label">Size Label *</label>
                <input type="text" name="measurements[${entryId}][size]" 
                       class="form-control size-input" placeholder="e.g., S, M, L" required>
            </div>
            <div>
                <label class="form-label">Bust (in)</label>
                <input type="text" name="measurements[${entryId}][bust]" 
                       class="form-control" placeholder="Bust measurement">
            </div>
            <div>
                <label class="form-label">Waist (in)</label>
                <input type="text" name="measurements[${entryId}][waist]" 
                       class="form-control" placeholder="Waist measurement">
            </div>
            <div>
                <label class="form-label">Hips (in)</label>
                <input type="text" name="measurements[${entryId}][hips]" 
                       class="form-control" placeholder="Hips measurement">
            </div>
            <div>
                <label class="form-label">Length (in)</label>
                <input type="text" name="measurements[${entryId}][length]" 
                       class="form-control" placeholder="Length measurement">
            </div>
            <div>
                <label class="form-label">Shoulder (in)</label>
                <input type="text" name="measurements[${entryId}][shoulder]" 
                       class="form-control" placeholder="Shoulder measurement">
            </div>
        </div>
    `;
            container.appendChild(entryDiv);
        }

        function editSizeChart(id, chartName, measurements) {
            openSizeChartModal(id, chartName, measurements);
        }
    </script>
</body>

</html>