<?php
// config.php - Complete Database Configuration with All Functions
session_start();

// DATABASE CONFIGURATION
define('DB_HOST', 'localhost');
define('DB_USER', 'u211483608_s');
define('DB_PASS', '0mUO##a4Y'); 
define('DB_NAME', 'u211483608_sdesigner');

// SMTP Configuration for Hostinger - USING PHP mail() function
define('SMTP_FROM_EMAIL', 'sdesigner@sdesignerjal.in');
define('SMTP_FROM_NAME', 'SDesigner Boutique');

// // PhonePe Configuration
// define('PHONEPE_MERCHANT_ID', 'M22NL5SNTHS4B');
// define('PHONEPE_SALT_KEY', '8e236ff1-b080-4fda-8477-cbf324f5da68');
// define('PHONEPE_SALT_INDEX', '1');
// define('PHONEPE_BASE_URL', 'https://api.phonepe.com/apis/hermes');

try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS);
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    // Create database if not exists
    $conn->query("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` 
                  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $conn->select_db(DB_NAME);
    $conn->set_charset("utf8mb4");
    
    // Initialize database tables with new structure
    initializeDatabase($conn);
} catch (Exception $e) {
    die("Database connection error: " . $e->getMessage());
}

// DATABASE INITIALIZATION FUNCTION - UPDATED STRUCTURE
function initializeDatabase($conn) {
    // Colors table
    $conn->query("CREATE TABLE IF NOT EXISTS `colors` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `name` VARCHAR(50) NOT NULL,
        `hex_code` VARCHAR(7) DEFAULT '#000000',
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Size charts table
    $conn->query("CREATE TABLE IF NOT EXISTS `size_charts` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `chart_name` VARCHAR(100) NOT NULL,
        `measurements` JSON,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Categories table
    $conn->query("CREATE TABLE IF NOT EXISTS `categories` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `name` VARCHAR(50) NOT NULL,
        `slug` VARCHAR(50) UNIQUE NOT NULL
    )");
    
// Products table
$conn->query("CREATE TABLE IF NOT EXISTS `products` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `product_type` ENUM('stitched', 'unstitched', 'accessory') NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `description` TEXT NULL,
    `price` DECIMAL(10, 2) NOT NULL,
    `price_per_meter` DECIMAL(10, 2) NULL,
    `length_in_meters` DECIMAL(10, 2) NULL,
    `category_id` INT,
    `size_chart_id` INT NULL,
    `stock_quantity` INT DEFAULT 0,
    `reorder_level` INT DEFAULT 5,
    `stock_status` ENUM('in_stock', 'low_stock', 'out_of_stock') DEFAULT 'in_stock',
    `last_restocked` TIMESTAMP NULL,
    `is_active` BOOLEAN DEFAULT TRUE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`),
    FOREIGN KEY (`size_chart_id`) REFERENCES `size_charts`(`id`) ON DELETE SET NULL
)");
    
    // Product Colors table
    $conn->query("CREATE TABLE IF NOT EXISTS `product_colors` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `product_id` INT NOT NULL,
        `color_id` INT NOT NULL,
        FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`color_id`) REFERENCES `colors`(`id`) ON DELETE CASCADE,
        UNIQUE KEY `unique_product_color` (`product_id`, `color_id`)
    )");
    
    // Product Color Images table
    $conn->query("CREATE TABLE IF NOT EXISTS `product_color_images` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `product_id` INT NOT NULL,
        `color_id` INT NOT NULL,
        `image_url` VARCHAR(500) NOT NULL,
        `description` TEXT DEFAULT NULL,
        `is_video` BOOLEAN DEFAULT FALSE,
        `display_order` INT DEFAULT 0,
        `is_main_image` BOOLEAN DEFAULT FALSE,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`color_id`) REFERENCES `colors`(`id`) ON DELETE CASCADE,
        UNIQUE KEY `unique_product_color_image` (`product_id`, `color_id`, `image_url`)
    )");
    
    // Orders table
    $conn->query("CREATE TABLE IF NOT EXISTS `orders` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `order_id` VARCHAR(50) UNIQUE,
        `customer_name` VARCHAR(100),
        `customer_email` VARCHAR(100),
        `customer_phone` VARCHAR(20),
        `customer_address` TEXT,
        `customer_city` VARCHAR(50),
        `customer_state` VARCHAR(50),
        `customer_pincode` VARCHAR(10),
        `customer_notes` TEXT,
        `payment_method` VARCHAR(50),
        `total_amount` DECIMAL(10,2),
        `order_date` DATETIME,
        `status` VARCHAR(20) DEFAULT 'pending',
        `payment_status` VARCHAR(20) DEFAULT 'pending',
        `phonepe_transaction_id` VARCHAR(100),
        `email_sent` BOOLEAN DEFAULT FALSE,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Order items table
    $conn->query("CREATE TABLE IF NOT EXISTS `order_items` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `order_id` VARCHAR(50) NOT NULL,
        `product_id` INT NOT NULL,
        `color_id` INT DEFAULT NULL,
        `quantity` INT NOT NULL,
        `price` DECIMAL(10,2) NOT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (`order_id`) REFERENCES `orders`(`order_id`) ON DELETE CASCADE,
        FOREIGN KEY (`product_id`) REFERENCES `products`(`id`),
        FOREIGN KEY (`color_id`) REFERENCES `colors`(`id`)
    )");
    
    // Meta Tags table
    $conn->query("CREATE TABLE IF NOT EXISTS `meta_tags` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `name` VARCHAR(100) NOT NULL UNIQUE,
        `slug` VARCHAR(100) NOT NULL UNIQUE,
        `type` ENUM('occasion', 'fabric', 'style', 'work') NOT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Product Meta Tags table
    $conn->query("CREATE TABLE IF NOT EXISTS `product_meta_tags` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `product_id` INT NOT NULL,
        `meta_tag_id` INT NOT NULL,
        FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
        FOREIGN KEY (`meta_tag_id`) REFERENCES `meta_tags`(`id`) ON DELETE CASCADE,
        UNIQUE KEY `unique_product_meta` (`product_id`, `meta_tag_id`)
    )");
    
    // Stock Transactions table
    $conn->query("CREATE TABLE IF NOT EXISTS `stock_transactions` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `product_id` INT NOT NULL,
        `transaction_type` ENUM('purchase','sale','return','adjustment','damage','transfer') NOT NULL,
        `quantity` INT NOT NULL,
        `previous_quantity` INT NOT NULL,
        `new_quantity` INT NOT NULL,
        `notes` TEXT,
        `performed_by` VARCHAR(100) DEFAULT 'Admin',
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
    )");
    
    // Stock Alerts table
    $conn->query("CREATE TABLE IF NOT EXISTS `stock_alerts` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `product_id` INT NOT NULL,
        `alert_type` ENUM('low_stock', 'out_of_stock', 'expiring') NOT NULL,
        `message` TEXT,
        `is_read` BOOLEAN DEFAULT FALSE,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
    )");
    
    // Marquee Messages table
    $conn->query("CREATE TABLE IF NOT EXISTS `marquee_messages` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `message_text` TEXT NOT NULL,
        `badge_text` VARCHAR(50) DEFAULT NULL,
        `badge_color` VARCHAR(50) DEFAULT 'secondary',
        `icon_class` VARCHAR(100) DEFAULT 'fas fa-bullhorn',
        `icon_color` VARCHAR(50) DEFAULT 'secondary',
        `priority` INT DEFAULT 1 COMMENT '1=Low, 2=Medium, 3=High',
        `display_order` INT DEFAULT 0,
        `is_active` BOOLEAN DEFAULT TRUE,
        `start_date` DATE DEFAULT NULL,
        `end_date` DATE DEFAULT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");
    
    // Settings table
    $conn->query("CREATE TABLE IF NOT EXISTS `settings` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `key` VARCHAR(100) NOT NULL UNIQUE,
        `value` TEXT,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");
    
    // Insert default data
    insertDefaultData($conn);
}
// ============================================================
// ADDITIONAL FUNCTIONS NEEDED
// ============================================================

/**
 * Get product color media (images and videos)
 */

/**
 * Get main product image
 */
function getMainProductImage($conn, $product_id) {
    $stmt = $conn->prepare("
        SELECT pci.image_url 
        FROM product_color_images pci
        WHERE pci.product_id = ? AND pci.is_main_image = 1 
        LIMIT 1
    ");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $image = $result->fetch_assoc();
    $stmt->close();
    
    return $image;
}

/**
 * Save product meta tags
 */
function saveProductMetaTags($conn, $product_id, $meta_tags) {
    // Delete existing tags
    $stmt = $conn->prepare("DELETE FROM product_meta_tags WHERE product_id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $stmt->close();
    
    // Insert new tags
    if (!empty($meta_tags)) {
        $stmt = $conn->prepare("INSERT INTO product_meta_tags (product_id, meta_tag_id) VALUES (?, ?)");
        foreach ($meta_tags as $tag_id) {
            $stmt->bind_param("ii", $product_id, $tag_id);
            $stmt->execute();
        }
        $stmt->close();
    }
    
    return true;
}

/**
 * Get product meta tags
 */
function getProductMetaTags($conn, $product_id) {
    $tags = [];
    $stmt = $conn->prepare("
        SELECT mt.* FROM meta_tags mt 
        JOIN product_meta_tags pmt ON mt.id = pmt.meta_tag_id 
        WHERE pmt.product_id = ?
    ");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while($row = $result->fetch_assoc()) {
        $tags[] = $row;
    }
    $stmt->close();
    
    return $tags;
}

/**
 * Update stock function
 */
function updateStock($conn, $product_id, $quantity_change, $type, $notes = '', $performed_by = 'Admin') {
    try {
        // Get current stock
        $stmt = $conn->prepare("SELECT stock_quantity, reorder_level, name FROM products WHERE id = ?");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            $stmt->close();
            return false;
        }
        
        $product = $result->fetch_assoc();
        $stmt->close();
        
        $previous_qty = $product['stock_quantity'];
        $reorder_level = $product['reorder_level'] ?? 5;
        $product_name = $product['name'];
        
        // Calculate new quantity
        $new_qty = $previous_qty + $quantity_change;
        if ($new_qty < 0) $new_qty = 0;
        
        // Determine stock status
        $status = 'in_stock';
        if ($new_qty == 0) {
            $status = 'out_of_stock';
        } elseif ($new_qty <= $reorder_level) {
            $status = 'low_stock';
        }
        
        // Update product stock and status
        $update_stmt = $conn->prepare("UPDATE products 
                                      SET stock_quantity = ?, 
                                          stock_status = ?, 
                                          last_restocked = NOW() 
                                      WHERE id = ?");
        $update_stmt->bind_param("isi", $new_qty, $status, $product_id);
        $update_success = $update_stmt->execute();
        $update_stmt->close();
        
        if (!$update_success) {
            return false;
        }
        
        // Record transaction
        $trans_stmt = $conn->prepare("INSERT INTO stock_transactions 
                                     (product_id, transaction_type, quantity, previous_quantity, new_quantity, notes, performed_by) 
                                     VALUES (?, ?, ?, ?, ?, ?, ?)");
        $trans_stmt->bind_param("isiiiss", $product_id, $type, $quantity_change, $previous_qty, $new_qty, $notes, $performed_by);
        $trans_success = $trans_stmt->execute();
        $trans_stmt->close();
        
        if (!$trans_success) {
            return false;
        }
        
        return true;
    } catch (Exception $e) {
        error_log("Stock update error: " . $e->getMessage());
        return false;
    }
}
// DEFAULT DATA INSERTION
function insertDefaultData($conn) {
    // Insert default settings if not exists
    $result = $conn->query("SELECT COUNT(*) as count FROM settings");
    $row = $result->fetch_assoc();
    if ($row['count'] == 0) {
        $settings = [
            ['boutique_name', 'SDesigner Boutique'],
            ['location', 'Jalandhar, Punjab'],
            ['primary_phone', '89686-36373'],
            ['secondary_phone', '9814927250'],
            ['designer_name', 'Dinky Ahuja'],
            ['instagram_url', 'https://instagram.com/sdesigner_boutique'],
            ['facebook_url', 'https://facebook.com/sdesignerdinky'],
            ['reorder_level_default', '5'],
            ['show_social_links', '1'],
            ['default_growth_rate', '5'],
            ['marquee_speed', '30'],
            ['marquee_enabled', '1'],
            ['email', 'sdesignerjal@gmail.com']
        ];
        
        $stmt = $conn->prepare("INSERT INTO settings (`key`, `value`) VALUES (?, ?)");
        foreach ($settings as $setting) {
            $stmt->bind_param("ss", $setting[0], $setting[1]);
            $stmt->execute();
        }
        $stmt->close();
    }
    
    // Insert default categories
    $result = $conn->query("SELECT COUNT(*) as count FROM categories");
    $row = $result->fetch_assoc();
    if ($row['count'] == 0) {
        $categories = [
            ['Lehengas', 'lehengas'],
            ['Sarees', 'sarees'],
            ['Salwar Suits', 'salwar-suits'],
            ['Anarkalis', 'anarkalis'],
            ['Gowns', 'gowns'],
            ['Kurtas', 'kurtas'],
            ['Dupattas', 'dupattas'],
            ['Fabrics', 'fabrics'],
            ['Jewelry Sets', 'jewelry-sets'],
            ['Earrings', 'earrings'],
            ['Rings', 'rings'],
            ['Bangles', 'bangles']
        ];
        $stmt = $conn->prepare("INSERT INTO categories (name, slug) VALUES (?, ?)");
        foreach ($categories as $category) {
            $stmt->bind_param("ss", $category[0], $category[1]);
            $stmt->execute();
        }
        $stmt->close();
    }
    
    // Insert default colors
    $result = $conn->query("SELECT COUNT(*) as count FROM colors");
    $row = $result->fetch_assoc();
    if ($row['count'] == 0) {
        $colors = [
            ['Red', '#FF0000'],
            ['Royal Blue', '#4169E1'],
            ['Emerald Green', '#50C878'],
            ['Black', '#000000'],
            ['White', '#FFFFFF'],
            ['Gold', '#FFD700'],
            ['Silver', '#C0C0C0'],
            ['Hot Pink', '#FF69B4'],
            ['Purple', '#800080'],
            ['Maroon', '#800000'],
            ['Peach', '#FFDAB9'],
            ['Sky Blue', '#87CEEB'],
            ['Lavender', '#E6E6FA'],
            ['Cream', '#FFFDD0'],
            ['Brown', '#964B00']
        ];
        $stmt = $conn->prepare("INSERT INTO colors (name, hex_code) VALUES (?, ?)");
        foreach ($colors as $color) {
            $stmt->bind_param("ss", $color[0], $color[1]);
            $stmt->execute();
        }
        $stmt->close();
    }
    
    // Insert default size charts
    $result = $conn->query("SELECT COUNT(*) as count FROM size_charts");
    $row = $result->fetch_assoc();
    if ($row['count'] == 0) {
        $size_charts = [
            ['Standard Indian', '{"S": {"bust": "34", "waist": "30", "hip": "36"}, "M": {"bust": "36", "waist": "32", "hip": "38"}, "L": {"bust": "38", "waist": "34", "hip": "40"}, "XL": {"bust": "40", "waist": "36", "hip": "42"}}'],
            ['Lehenga & Blouse', '{"Small": {"blouse": "S", "lehenga": "S"}, "Medium": {"blouse": "M", "lehenga": "M"}, "Large": {"blouse": "L", "lehenga": "L"}, "X-Large": {"blouse": "XL", "lehenga": "XL"}}'],
            ['Saree Blouse', '{"28": {"size": "28"}, "30": {"size": "30"}, "32": {"size": "32"}, "34": {"size": "34"}, "36": {"size": "36"}}'],
            ['Kurta Size', '{"S": {"length": "52", "chest": "40"}, "M": {"length": "54", "chest": "42"}, "L": {"length": "56", "chest": "44"}, "XL": {"length": "58", "chest": "46"}}']
        ];
        $stmt = $conn->prepare("INSERT INTO size_charts (chart_name, measurements) VALUES (?, ?)");
        foreach ($size_charts as $chart) {
            $stmt->bind_param("ss", $chart[0], $chart[1]);
            $stmt->execute();
        }
        $stmt->close();
    }
    
    // Insert default meta tags
    $result = $conn->query("SELECT COUNT(*) as count FROM meta_tags");
    $row = $result->fetch_assoc();
    if ($row['count'] == 0) {
        $meta_tags = [
            ['Wedding', 'wedding', 'occasion'],
            ['Party', 'party', 'occasion'],
            ['Casual', 'casual', 'occasion'],
            ['Formal', 'formal', 'occasion'],
            ['Silk', 'silk', 'fabric'],
            ['Cotton', 'cotton', 'fabric'],
            ['Georgette', 'georgette', 'fabric'],
            ['Chiffon', 'chiffon', 'fabric'],
            ['Embroidery', 'embroidery', 'work'],
            ['Zari Work', 'zari-work', 'work'],
            ['Stone Work', 'stone-work', 'work'],
            ['Traditional', 'traditional', 'style'],
            ['Contemporary', 'contemporary', 'style']
        ];
        $stmt = $conn->prepare("INSERT INTO meta_tags (name, slug, type) VALUES (?, ?, ?)");
        foreach ($meta_tags as $tag) {
            $stmt->bind_param("sss", $tag[0], $tag[1], $tag[2]);
            $stmt->execute();
        }
        $stmt->close();
    }
    
    // Insert default marquee messages if not exists
    $result = $conn->query("SELECT COUNT(*) as count FROM marquee_messages");
    $row = $result->fetch_assoc();
    if ($row['count'] == 0) {
        $marquee_messages = [
            ['NEW COLLECTION LAUNCHED! Get 20% off on all bridal lehengas', 'NEW', 'secondary', 'fas fa-fire', 'secondary', 3, 1],
            ['Exclusive Sale! Buy 2 sarees, get 1 dupatta free', 'SALE', 'secondary', 'fas fa-gift', 'secondary', 3, 2],
            ['Free shipping on orders above ₹5000', 'FREE SHIPPING', 'secondary', 'fas fa-truck', 'secondary', 2, 3],
            ['Custom tailoring available on all unstitched fabrics', 'CUSTOM', 'secondary', 'fas fa-star', 'secondary', 1, 4],
            ['Festive Season Special: Extra 10% off with code FESTIVE10', 'FESTIVE', 'secondary', 'fas fa-bolt', 'secondary', 3, 5]
        ];
        $stmt = $conn->prepare("INSERT INTO marquee_messages (message_text, badge_text, badge_color, icon_class, icon_color, priority, display_order) VALUES (?, ?, ?, ?, ?, ?, ?)");
        foreach ($marquee_messages as $msg) {
            $stmt->bind_param("sssssii", $msg[0], $msg[1], $msg[2], $msg[3], $msg[4], $msg[5], $msg[6]);
            $stmt->execute();
        }
        $stmt->close();
    }
}

// ============================================================
// EMAIL FUNCTIONS - SIMPLIFIED VERSION
// ============================================================

/**
 * Main email sending function
 */
function sendEmail($to, $subject, $message, $from_email = null, $from_name = null) {
    // Include PHPMailer helper
    $phpmailer_helper = __DIR__ . '/phpmailer_helper.php';
    if (file_exists($phpmailer_helper)) {
        require_once $phpmailer_helper;
        
        // Use universal sender
        if (function_exists('sendEmailUniversal')) {
            return sendEmailUniversal($to, $subject, $message, $from_email, $from_name);
        }
    }
    
    // Fallback to basic mail() if helper fails
    if (!$from_email) $from_email = SMTP_FROM_EMAIL;
    if (!$from_name) $from_name = SMTP_FROM_NAME;
    
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "From: " . $from_name . " <" . $from_email . ">\r\n";
    $headers .= "Reply-To: " . $from_email . "\r\n";
    
    return mail($to, $subject, $message, $headers);
}
// ============================================================
// DATABASE FUNCTIONS
// ============================================================

/**
 * Stock deduction function
 */
function deductStockFromOrder($conn, $order_id) {
    try {
        // Get all items in the order
        $stmt = $conn->prepare("SELECT product_id, quantity FROM order_items WHERE order_id = ?");
        $stmt->bind_param("s", $order_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($item = $result->fetch_assoc()) {
            $product_id = $item['product_id'];
            $quantity = $item['quantity'];
            
            // Get current stock
            $product_stmt = $conn->prepare("SELECT stock_quantity, name FROM products WHERE id = ?");
            $product_stmt->bind_param("i", $product_id);
            $product_stmt->execute();
            $product_result = $product_stmt->get_result();
            
            if ($product_row = $product_result->fetch_assoc()) {
                $current_stock = $product_row['stock_quantity'];
                $new_stock = $current_stock - $quantity;
                if ($new_stock < 0) $new_stock = 0;
                
                // Update stock
                $update_stmt = $conn->prepare("UPDATE products SET stock_quantity = ? WHERE id = ?");
                $update_stmt->bind_param("ii", $new_stock, $product_id);
                $update_stmt->execute();
                $update_stmt->close();
                
                // Log transaction
                $log_stmt = $conn->prepare("INSERT INTO stock_transactions (product_id, transaction_type, quantity, previous_quantity, new_quantity, notes, performed_by) VALUES (?, 'sale', ?, ?, ?, ?, ?)");
                $notes = "Order #$order_id - Stock deducted";
                $performed_by = "System";
                $log_stmt->bind_param("iiiiss", $product_id, $quantity, $current_stock, $new_stock, $notes, $performed_by);
                $log_stmt->execute();
                $log_stmt->close();
            }
            
            $product_stmt->close();
        }
        
        $stmt->close();
        return true;
    } catch (Exception $e) {
        error_log("Stock deduction error for order $order_id: " . $e->getMessage());
        return false;
    }
}

/**
 * Save order to database
 */
function saveOrderToDatabase($conn, $order_data) {
    try {
        // Insert order
        $stmt = $conn->prepare("INSERT INTO orders (order_id, customer_name, customer_email, customer_phone, customer_address, customer_city, customer_state, customer_pincode, customer_notes, payment_method, total_amount, order_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssssssds", 
            $order_data['order_id'],
            $order_data['customer_name'],
            $order_data['customer_email'],
            $order_data['customer_phone'],
            $order_data['customer_address'],
            $order_data['customer_city'],
            $order_data['customer_state'],
            $order_data['customer_pincode'],
            $order_data['customer_notes'],
            $order_data['payment_method'],
            $order_data['total_amount'],
            $order_data['order_date']
        );
        
        $order_saved = $stmt->execute();
        $stmt->close();
        
        if ($order_saved) {
            // Insert order items
            foreach ($order_data['items'] as $item) {
                $stmt2 = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
                $stmt2->bind_param("siid", 
                    $order_data['order_id'],
                    $item['id'] ?? 0,
                    $item['quantity'],
                    $item['price']
                );
                $stmt2->execute();
                $stmt2->close();
            }
        }
        
        return $order_saved;
    } catch (Exception $e) {
        error_log("Database error: " . $e->getMessage());
        return false;
    }
}

// ============================================================
// SETTINGS MANAGEMENT FUNCTIONS
// ============================================================

function getSetting($conn, $key) {
    $stmt = $conn->prepare("SELECT value FROM settings WHERE `key` = ?");
    $stmt->bind_param("s", $key);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    return $row ? $row['value'] : null;
}

function updateSetting($conn, $key, $value) {
    $stmt = $conn->prepare("UPDATE settings SET value = ? WHERE `key` = ?");
    $stmt->bind_param("ss", $value, $key);
    return $stmt->execute();
}

function getAllSettings($conn) {
    $result = $conn->query("SELECT * FROM settings");
    $settings = [];
    while($row = $result->fetch_assoc()) {
        $settings[$row['key']] = $row['value'];
    }
    return $settings;
}

// ============================================================
// PRODUCT FUNCTIONS
// ============================================================

function getCategories($conn) {
    $sql = "SELECT * FROM categories ORDER BY name";
    $result = $conn->query($sql);
    $categories = [];
    while($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
    return $categories;
}

function getColors($conn) {
    $sql = "SELECT * FROM colors ORDER BY name";
    $result = $conn->query($sql);
    $colors = [];
    while($row = $result->fetch_assoc()) {
        $colors[] = $row;
    }
    return $colors;
}

function getProductsByType($conn, $type, $limit = 50) {
    $stmt = $conn->prepare("SELECT p.*, c.name as category_name 
                           FROM products p 
                           LEFT JOIN categories c ON p.category_id = c.id 
                           WHERE p.product_type = ? 
                           AND p.is_active = 1 
                           ORDER BY p.created_at DESC 
                           LIMIT ?");
    $stmt->bind_param("si", $type, $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    $products = [];
    while($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    $stmt->close();
    return $products;
}

function getProductById($conn, $product_id) {
    $stmt = $conn->prepare("SELECT p.*, c.name as category_name, sc.chart_name 
                           FROM products p 
                           LEFT JOIN categories c ON p.category_id = c.id 
                           LEFT JOIN size_charts sc ON p.size_chart_id = sc.id 
                           WHERE p.id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    $stmt->close();
    
    if ($product) {
        // Get colors
        $color_stmt = $conn->prepare("SELECT c.id, c.name, c.hex_code 
                                     FROM product_colors pc 
                                     JOIN colors c ON pc.color_id = c.id 
                                     WHERE pc.product_id = ?");
        $color_stmt->bind_param("i", $product_id);
        $color_stmt->execute();
        $color_result = $color_stmt->get_result();
        $colors = [];
        while($color = $color_result->fetch_assoc()) {
            $colors[] = $color;
        }
        $product['colors'] = $colors;
        $color_stmt->close();
        
        // Get meta tags
        $product['meta_tags'] = getProductMetaTags($conn, $product_id);
    }
    return $product;
}

// ============================================================
// MARQUEE FUNCTIONS
// ============================================================

function getMarqueeMessages($conn, $active_only = true) {
    $sql = "SELECT * FROM marquee_messages";
    
    if ($active_only) {
        $sql .= " WHERE is_active = 1";
        $sql .= " AND (start_date IS NULL OR start_date <= CURDATE())";
        $sql .= " AND (end_date IS NULL OR end_date >= CURDATE())";
    }
    
    $sql .= " ORDER BY display_order, priority DESC, created_at DESC";
    
    $result = $conn->query($sql);
    $messages = [];
    while($row = $result->fetch_assoc()) {
        $messages[] = $row;
    }
    return $messages;
}

// ============================================================
// META TAGS FUNCTIONS
// ============================================================

function getMetaTags($conn, $type = null) {
    $sql = "SELECT * FROM meta_tags";
    if ($type) {
        $sql .= " WHERE type = ?";
    }
    $sql .= " ORDER BY type, name";
    if ($type) {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $type);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
    } else {
        $result = $conn->query($sql);
    }
    $tags = [];
    while($row = $result->fetch_assoc()) {
        $tags[] = $row;
    }
    return $tags;
}

// ============================================================
// STOCK FUNCTIONS
// ============================================================

// function updateStock($conn, $product_id, $quantity_change, $type, $notes = '', $performed_by = 'Admin') {
//     try {
//         // Get current stock
//         $stmt = $conn->prepare("SELECT stock_quantity, reorder_level, name FROM products WHERE id = ?");
//         $stmt->bind_param("i", $product_id);
//         $stmt->execute();
//         $result = $stmt->get_result();
        
//         if ($result->num_rows === 0) {
//             $stmt->close();
//             return false;
//         }
        
//         $product = $result->fetch_assoc();
//         $stmt->close();
        
//         $previous_qty = $product['stock_quantity'];
//         $reorder_level = $product['reorder_level'] ?? 5;
//         $product_name = $product['name'];
        
//         // Calculate new quantity
//         $new_qty = $previous_qty + $quantity_change;
//         if ($new_qty < 0) $new_qty = 0;
        
//         // Determine stock status
//         $status = 'in_stock';
//         if ($new_qty == 0) {
//             $status = 'out_of_stock';
//         } elseif ($new_qty <= $reorder_level) {
//             $status = 'low_stock';
//         }
        
//         // Update product stock and status
//         $update_stmt = $conn->prepare("UPDATE products 
//                                       SET stock_quantity = ?, 
//                                           stock_status = ?, 
//                                           last_restocked = NOW() 
//                                       WHERE id = ?");
//         $update_stmt->bind_param("isi", $new_qty, $status, $product_id);
//         $update_success = $update_stmt->execute();
//         $update_stmt->close();
        
//         if (!$update_success) {
//             return false;
//         }
        
//         // Record transaction
//         $trans_stmt = $conn->prepare("INSERT INTO stock_transactions 
//                                      (product_id, transaction_type, quantity, previous_quantity, new_quantity, notes, performed_by) 
//                                      VALUES (?, ?, ?, ?, ?, ?, ?)");
//         $trans_stmt->bind_param("isiiiss", $product_id, $type, $quantity_change, $previous_qty, $new_qty, $notes, $performed_by);
//         $trans_success = $trans_stmt->execute();
//         $trans_stmt->close();
        
//         if (!$trans_success) {
//             return false;
//         }
        
//         return true;
//     } catch (Exception $e) {
//         return false;
//     }
// }

// ============================================================
// HELPER FUNCTIONS
// ============================================================

/**
 * Generate order ID
 */


/**
 * Get location from coordinates
 */
function getLocationFromCoordinates($lat, $lng) {
    try {
        $services = [
            [
                'url' => "https://nominatim.openstreetmap.org/reverse?format=json&lat=$lat&lon=$lng&addressdetails=1&zoom=18",
                'type' => 'nominatim'
            ]
        ];
        
        $address_data = null;
        
        foreach ($services as $service) {
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $service['url'],
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_USERAGENT => 'SDesigner Boutique Contact Form',
                CURLOPT_TIMEOUT => 5,
                CURLOPT_SSL_VERIFYPEER => false
            ]);
            
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($http_code === 200 && $response) {
                $data = json_decode($response, true);
                
                if ($service['type'] === 'nominatim' && isset($data['display_name'])) {
                    $address_data = [
                        'full_address' => $data['display_name'],
                        'address' => $data['address'] ?? []
                    ];
                    break;
                }
            }
        }
        
        return $address_data;
        
    } catch (Exception $e) {
        error_log("GPS Location Error: " . $e->getMessage());
        return null;
    }
}
// Include functions file
$functions_file = __DIR__ . '/functions.php';
if (file_exists($functions_file)) {
    require_once $functions_file;
} else {
    error_log("Functions file not found: $functions_file");
}
?>