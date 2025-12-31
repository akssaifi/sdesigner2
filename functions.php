<?php
// functions.php - All missing functions for admin.php

// Get stock statistics
function getStockStatistics($conn) {
    $stats = [];
    
    // Total stock value
    $result = $conn->query("SELECT SUM(stock_quantity * price) as total_value FROM products");
    $stats['total_stock_value'] = $result->fetch_assoc()['total_value'] ?? 0;
    
    // Low stock items (below reorder level)
    $result = $conn->query("SELECT COUNT(*) as count FROM products WHERE stock_quantity > 0 AND stock_quantity <= reorder_level");
    $stats['low_stock'] = $result->fetch_assoc()['count'] ?? 0;
    
    // Out of stock items
    $result = $conn->query("SELECT COUNT(*) as count FROM products WHERE stock_quantity = 0");
    $stats['out_of_stock'] = $result->fetch_assoc()['count'] ?? 0;
    
    return $stats;
}

// Get recent products
function getRecentProducts($conn, $limit = 5) {
    $products = [];
    $stmt = $conn->prepare("SELECT p.*, c.name as category_name FROM products p 
                           LEFT JOIN categories c ON p.category_id = c.id 
                           ORDER BY p.created_at DESC LIMIT ?");
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    $stmt->close();
    
    return $products;
}

// Get active alerts
function getActiveAlerts($conn, $limit = 5) {
    $alerts = [];
    $stmt = $conn->prepare("SELECT sa.*, p.name as product_name FROM stock_alerts sa 
                           JOIN products p ON sa.product_id = p.id 
                           WHERE sa.is_read = 0 
                           ORDER BY sa.created_at DESC LIMIT ?");
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while($row = $result->fetch_assoc()) {
        $alerts[] = $row;
    }
    $stmt->close();
    
    return $alerts;
}

// Get low stock products
function getLowStockProducts($conn, $limit = 5) {
    $products = [];
    $stmt = $conn->prepare("SELECT p.*, c.name as category_name FROM products p 
                           LEFT JOIN categories c ON p.category_id = c.id 
                           WHERE p.stock_quantity > 0 AND p.stock_quantity <= p.reorder_level 
                           ORDER BY p.stock_quantity ASC LIMIT ?");
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    $stmt->close();
    
    return $products;
}

// Get out of stock products
function getOutOfStockProducts($conn, $limit = 5) {
    $products = [];
    $stmt = $conn->prepare("SELECT p.*, c.name as category_name FROM products p 
                           LEFT JOIN categories c ON p.category_id = c.id 
                           WHERE p.stock_quantity = 0 
                           ORDER BY p.last_restocked DESC LIMIT ?");
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    $stmt->close();
    
    return $products;
}

// Get stock transactions
function getStockTransactions($conn, $limit = 10) {
    $transactions = [];
    $stmt = $conn->prepare("SELECT st.*, p.name as product_name FROM stock_transactions st 
                           JOIN products p ON st.product_id = p.id 
                           ORDER BY st.created_at DESC LIMIT ?");
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while($row = $result->fetch_assoc()) {
        $transactions[] = $row;
    }
    $stmt->close();
    
    return $transactions;
}

// Get all products with stock (including inactive)
function getAllProductsWithStock($conn, $include_inactive = false) {
    $products = [];
    $sql = "SELECT p.*, c.name as category_name FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id ";
    
    if (!$include_inactive) {
        $sql .= "WHERE p.is_active = 1 ";
    }
    
    $sql .= "ORDER BY p.name";
    
    $result = $conn->query($sql);
    
    while($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    
    return $products;
}

// Get size charts
function getSizeCharts($conn) {
    $charts = [];
    $result = $conn->query("SELECT * FROM size_charts ORDER BY chart_name");
    
    while($row = $result->fetch_assoc()) {
        $charts[] = $row;
    }
    
    return $charts;
}

// // Get product meta tags
// function getProductMetaTags($conn, $product_id) {
//     $tags = [];
//     $stmt = $conn->prepare("SELECT mt.* FROM meta_tags mt 
//                            JOIN product_meta_tags pmt ON mt.id = pmt.meta_tag_id 
//                            WHERE pmt.product_id = ?");
//     $stmt->bind_param("i", $product_id);
//     $stmt->execute();
//     $result = $stmt->get_result();
    
//     while($row = $result->fetch_assoc()) {
//         $tags[] = $row;
//     }
//     $stmt->close();
    
//     return $tags;
// }

// // Save product meta tags
// function saveProductMetaTags($conn, $product_id, $meta_tags) {
//     // Delete existing tags
//     $stmt = $conn->prepare("DELETE FROM product_meta_tags WHERE product_id = ?");
//     $stmt->bind_param("i", $product_id);
//     $stmt->execute();
//     $stmt->close();
    
//     // Insert new tags
//     if (!empty($meta_tags)) {
//         $stmt = $conn->prepare("INSERT INTO product_meta_tags (product_id, meta_tag_id) VALUES (?, ?)");
//         foreach ($meta_tags as $tag_id) {
//             $stmt->bind_param("ii", $product_id, $tag_id);
//             $stmt->execute();
//         }
//         $stmt->close();
//     }
    
//     return true;
// }

// Get main product image


// Get product color images only (no videos)


// Get product color videos only


// Get total products count
function getTotalProducts($conn) {
    $result = $conn->query("SELECT COUNT(*) as total FROM products");
    return $result->fetch_assoc()['total'] ?? 0;
}

// Get product count by type
function getProductCountByType($conn, $type) {
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM products WHERE product_type = ? AND is_active = 1");
    $stmt->bind_param("s", $type);
    $stmt->execute();
    $result = $stmt->get_result();
    $count = $result->fetch_assoc()['count'] ?? 0;
    $stmt->close();
    
    return $count;
}

// Calculate growth rates
function calculateGrowthRates($conn) {
    $rates = [
        'stitched' => 0,
        'unstitched' => 0,
        'accessories' => 0
    ];
    
    // Get default growth rate from settings
    $default_rate = getSetting($conn, 'default_growth_rate') ?? 5;
    
    // For simplicity, we'll use default rates
    // In a real application, you'd calculate actual growth from historical data
    foreach ($rates as $type => &$rate) {
        $rate = $default_rate;
    }
    
    return $rates;
}

// Category management functions
function addCategory($conn, $name, $slug) {
    $stmt = $conn->prepare("INSERT INTO categories (name, slug) VALUES (?, ?)");
    $stmt->bind_param("ss", $name, $slug);
    return $stmt->execute();
}

function updateCategory($conn, $id, $name, $slug) {
    $stmt = $conn->prepare("UPDATE categories SET name = ?, slug = ? WHERE id = ?");
    $stmt->bind_param("ssi", $name, $slug, $id);
    return $stmt->execute();
}

function deleteCategory($conn, $id) {
    // Check if category is used in products
    $check_stmt = $conn->prepare("SELECT COUNT(*) as count FROM products WHERE category_id = ?");
    $check_stmt->bind_param("i", $id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    $product_count = $result->fetch_assoc()['count'];
    $check_stmt->close();
    
    if ($product_count > 0) {
        return false; // Cannot delete category with products
    }
    
    $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->bind_param("i", $id);
    return $stmt->execute();
}

// Meta tag management functions
function addMetaTag($conn, $name, $slug, $type) {
    $stmt = $conn->prepare("INSERT INTO meta_tags (name, slug, type) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $slug, $type);
    return $stmt->execute();
}

function updateMetaTag($conn, $id, $name, $slug, $type) {
    $stmt = $conn->prepare("UPDATE meta_tags SET name = ?, slug = ?, type = ? WHERE id = ?");
    $stmt->bind_param("sssi", $name, $slug, $type, $id);
    return $stmt->execute();
}

function deleteMetaTag($conn, $id) {
    // Check if meta tag is used in products
    $check_stmt = $conn->prepare("SELECT COUNT(*) as count FROM product_meta_tags WHERE meta_tag_id = ?");
    $check_stmt->bind_param("i", $id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    $product_count = $result->fetch_assoc()['count'];
    $check_stmt->close();
    
    if ($product_count > 0) {
        return false; // Cannot delete meta tag used in products
    }
    
    $stmt = $conn->prepare("DELETE FROM meta_tags WHERE id = ?");
    $stmt->bind_param("i", $id);
    return $stmt->execute();
}

// Mark alert as read
function markAlertAsRead($conn, $alert_id) {
    $stmt = $conn->prepare("UPDATE stock_alerts SET is_read = 1 WHERE id = ?");
    $stmt->bind_param("i", $alert_id);
    return $stmt->execute();
}

// Send contact email function (missing from config.php)
function sendContactEmail($conn, $form_data) {
    try {
        $boutique_name = getSetting($conn, 'boutique_name') ?? 'SDesigner Boutique';
        $boutique_email = getSetting($conn, 'email') ?? 'sdesignerjal@gmail.com';
        $primary_phone = getSetting($conn, 'primary_phone') ?? '89686-36373';
        $designer_name = getSetting($conn, 'designer_name') ?? 'Dinky Ahuja';
        
        // Generate customer email
        $customer_subject = "Thank you for contacting $boutique_name";
        $customer_message = generateContactCustomerEmail($form_data, $boutique_name, $designer_name, $primary_phone, $boutique_email);
        
        // Send to customer
        $customer_sent = sendEmail(
            $form_data['email'],
            $customer_subject,
            $customer_message,
            'sdesignerjal@sdesignerjal.in',
            $boutique_name
        );
        
        // Generate admin email
        $admin_subject = "📧 New Contact Form Submission: " . $form_data['subject'];
        $admin_message = generateContactAdminEmail($form_data, $boutique_name);
        
        // Send to admin
        $admin_sent = sendEmail(
            $boutique_email,
            $admin_subject,
            $admin_message,
            'sdesignerjal@sdesignerjal.in',
            $boutique_name
        );
        
        return [
            'customer' => $customer_sent,
            'admin' => $admin_sent
        ];
        
    } catch (Exception $e) {
        error_log("Contact Email Error: " . $e->getMessage());
        return [
            'customer' => false,
            'admin' => false
        ];
    }
}

function generateContactCustomerEmail($form_data, $boutique_name, $designer_name, $phone, $email) {
    return "
    <!DOCTYPE html>
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #8B4513; color: white; padding: 20px; text-align: center; }
            .content { padding: 30px; background: #f9f9f9; }
            .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>$boutique_name</h1>
                <p>Message Received</p>
            </div>
            
            <div class='content'>
                <h2>Thank you for contacting us, " . htmlspecialchars($form_data['name']) . "!</h2>
                <p>We have received your message and will get back to you within 24 hours.</p>
                
                <h3>Your Message Details:</h3>
                <p><strong>Subject:</strong> " . htmlspecialchars($form_data['subject']) . "</p>
                <p><strong>Message:</strong> " . nl2br(htmlspecialchars($form_data['message'])) . "</p>
                
                <h3>Our Contact Information:</h3>
                <p><strong>Designer:</strong> $designer_name</p>
                <p><strong>Phone:</strong> $phone</p>
                <p><strong>Email:</strong> $email</p>
                
                <p>Best regards,<br>The $boutique_name Team</p>
            </div>
            
            <div class='footer'>
                <p>$boutique_name | Jalandhar, Punjab</p>
                <p>© " . date('Y') . " $boutique_name. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>";
}

function generateContactAdminEmail($form_data, $boutique_name) {
    return "
    <!DOCTYPE html>
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #dc3545; color: white; padding: 20px; text-align: center; }
            .content { padding: 30px; background: #f9f9f9; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>📧 NEW CONTACT FORM SUBMISSION</h1>
                <p>$boutique_name</p>
            </div>
            
            <div class='content'>
                <h2>Contact Form Details:</h2>
                
                <p><strong>Name:</strong> " . htmlspecialchars($form_data['name']) . "</p>
                <p><strong>Email:</strong> " . htmlspecialchars($form_data['email']) . "</p>
                <p><strong>Phone:</strong> " . htmlspecialchars($form_data['phone']) . "</p>
                <p><strong>Subject:</strong> " . htmlspecialchars($form_data['subject']) . "</p>
                <p><strong>Message:</strong></p>
                <div style='background: white; padding: 15px; border-radius: 5px; border: 1px solid #ddd;'>
                    " . nl2br(htmlspecialchars($form_data['message'])) . "
                </div>
                
                <p><strong>Time:</strong> " . date('Y-m-d H:i:s') . "</p>
                
                <div style='margin-top: 30px; padding: 15px; background: #e8f4f8; border-radius: 5px;'>
                    <p><strong>Quick Actions:</strong></p>
                    <p><a href='mailto:" . htmlspecialchars($form_data['email']) . "'>Reply via Email</a></p>
                </div>
            </div>
        </div>
    </body>
    </html>";
}
/**
 * Get all media (images and videos) for a specific product and color
 * 
 * @param mysqli $conn Database connection
 * @param int $product_id Product ID
 * @param int $color_id Color ID
 * @return array Array of media items
 */
// function getProductColorMedia($conn, $product_id, $color_id) {
//     $media = [];
    
//     // Get all media for this product and color (both images and videos)
//     $stmt = $conn->prepare("
//         SELECT id, product_id, color_id, image_url, is_video, display_order, is_main_image 
//         FROM product_color_images 
//         WHERE product_id = ? AND color_id = ? 
//         ORDER BY is_video, display_order
//     ");
//     $stmt->bind_param("ii", $product_id, $color_id);
//     $stmt->execute();
//     $result = $stmt->get_result();
    
//     while ($row = $result->fetch_assoc()) {
//         $media[] = $row;
//     }
    
//     $stmt->close();
//     return $media;
// }

/**
 * Get only images for a specific product and color
 * 
 * @param mysqli $conn Database connection
 * @param int $product_id Product ID
 * @param int $color_id Color ID
 * @return array Array of image items
 */
function getProductColorImagesOnly($conn, $product_id, $color_id) {
    $images = [];
    
    $stmt = $conn->prepare("
        SELECT id, product_id, color_id, image_url, is_video, display_order, is_main_image 
        FROM product_color_images 
        WHERE product_id = ? AND color_id = ? AND is_video = 0
        ORDER BY display_order
    ");
    $stmt->bind_param("ii", $product_id, $color_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $images[] = $row;
    }
    
    $stmt->close();
    return $images;
}

/**
 * Get only videos for a specific product and color
 * 
 * @param mysqli $conn Database connection
 * @param int $product_id Product ID
 * @param int $color_id Color ID
 * @return array Array of video items
 */
function getProductColorVideosOnly($conn, $product_id, $color_id) {
    $videos = [];
    
    $stmt = $conn->prepare("
        SELECT id, product_id, color_id, image_url, is_video, display_order, is_main_image 
        FROM product_color_images 
        WHERE product_id = ? AND color_id = ? AND is_video = 1
        ORDER BY display_order
    ");
    $stmt->bind_param("ii", $product_id, $color_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $videos[] = $row;
    }
    
    $stmt->close();
    return $videos;
}

// functions.php - Add these functions if not already there

/**
 * Get product images for a specific product and color
 */
// function getProductColorImagesOnly($conn, $product_id, $color_id) {
//     $images = [];
//     $stmt = $conn->prepare("
//         SELECT id, product_id, color_id, image_url, is_video, display_order, is_main_image 
//         FROM product_color_images 
//         WHERE product_id = ? AND color_id = ? AND is_video = 0
//         ORDER BY display_order
//     ");
//     $stmt->bind_param("ii", $product_id, $color_id);
//     $stmt->execute();
//     $result = $stmt->get_result();
    
//     while ($row = $result->fetch_assoc()) {
//         $images[] = $row;
//     }
    
//     $stmt->close();
//     return $images;
// }

/**
 * Get product videos for a specific product and color
 */
// function getProductColorVideosOnly($conn, $product_id, $color_id) {
//     $videos = [];
//     $stmt = $conn->prepare("
//         SELECT id, product_id, color_id, image_url, is_video, display_order, is_main_image 
//         FROM product_color_images 
//         WHERE product_id = ? AND color_id = ? AND is_video = 1
//         ORDER BY display_order
//     ");
//     $stmt->bind_param("ii", $product_id, $color_id);
//     $stmt->execute();
//     $result = $stmt->get_result();
    
//     while ($row = $result->fetch_assoc()) {
//         $videos[] = $row;
//     }
    
//     $stmt->close();
//     return $videos;
// }

/**
 * Get all media for a specific product and color
 */
function getProductColorMedia($conn, $product_id, $color_id) {
    $media = [];
    $stmt = $conn->prepare("
        SELECT id, product_id, color_id, image_url, is_video, display_order, is_main_image 
        FROM product_color_images 
        WHERE product_id = ? AND color_id = ? 
        ORDER BY is_video, display_order
    ");
    $stmt->bind_param("ii", $product_id, $color_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $media[] = $row;
    }
    
    $stmt->close();
    return $media;
}
function getProductMedia($conn, $product_id, $color_id = null) {
    $sql = "SELECT * FROM product_color_images WHERE product_id = ?";
    $params = [$product_id];
    $types = "i";
    
    if ($color_id) {
        $sql .= " AND color_id = ?";
        $params[] = $color_id;
        $types .= "i";
    }
    
    $sql .= " ORDER BY display_order ASC, is_main_image DESC";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) return [];
    
    if ($color_id) {
        $stmt->bind_param($types, $product_id, $color_id);
    } else {
        $stmt->bind_param($types, $product_id);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $media = [];
    while ($row = $result->fetch_assoc()) {
        $media[] = $row;
    }
    
    $stmt->close();
    return $media;
}

function getProductImagesAndVideos($conn, $product_id, $color_id) {
    $all_media = getProductMedia($conn, $product_id, $color_id);
    
    $images = [];
    $videos = [];
    
    foreach ($all_media as $media) {
        if ($media['is_video']) {
            $videos[] = $media;
        } else {
            $images[] = $media;
        }
    }
    
    return [
        'images' => $images,
        'videos' => $videos
    ];
}
?>