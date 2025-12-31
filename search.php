
<?php
require_once 'config.php';

// Set JSON header
header('Content-Type: application/json');

// Get search query
$query = $_GET['q'] ?? '';
$query = trim($query);

// Debug logging
error_log("Search query received: " . $query);

if (empty($query) || strlen($query) < 2) {
    echo json_encode([]);
    exit;
}

// Prepare search query
$searchTerm = '%' . $conn->real_escape_string($query) . '%';

// Debug: Check if products exist
$checkSql = "SELECT COUNT(*) as total FROM products WHERE is_active = 1";
$checkResult = $conn->query($checkSql);
$row = $checkResult->fetch_assoc();
error_log("Total active products in database: " . $row['total']);

// Search in products and meta tags
$sql = "SELECT DISTINCT p.*, c.name as category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        LEFT JOIN product_meta_tags pmt ON p.id = pmt.product_id 
        LEFT JOIN meta_tags mt ON pmt.meta_tag_id = mt.id 
        WHERE p.is_active = 1 
        AND (
            p.name LIKE ? 
            OR p.product_type LIKE ?
            OR c.name LIKE ? 
            OR mt.name LIKE ? 
            OR mt.type LIKE ?
        )
        ORDER BY p.created_at DESC 
        LIMIT 10";

error_log("SQL Query: " . $sql);
error_log("Search term: " . $searchTerm);

$stmt = $conn->prepare($sql);
if (!$stmt) {
    error_log("Prepare failed: " . $conn->error);
    echo json_encode(['error' => 'Database error']);
    exit;
}

$stmt->bind_param("sssss", $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm);
$stmt->execute();
$result = $stmt->get_result();

error_log("Number of results found: " . $result->num_rows);

$products = [];
while ($row = $result->fetch_assoc()) {
    error_log("Found product: " . $row['name'] . " (ID: " . $row['id'] . ")");
    
    // Get main image
    $main_image = getMainProductImage($conn, $row['id']);
    
    $products[] = [
        'id' => $row['id'],
        'name' => $row['name'],
        'category' => $row['category_name'] ?: 'Uncategorized',
        'price' => number_format($row['price'], 2),
        'image' => $main_image ? $main_image['image_url'] : 'assets/images/no-image.jpg',
        'product_type' => $row['product_type']
    ];
}

// If no results found, try a broader search
if (empty($products)) {
    error_log("No products found with meta tags, trying broader search...");
    
    $sql2 = "SELECT p.*, c.name as category_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.is_active = 1 
            AND (
                p.name LIKE ? 
                OR p.product_type LIKE ?
                OR c.name LIKE ?
            )
            LIMIT 10";
    
    $stmt2 = $conn->prepare($sql2);
    $stmt2->bind_param("sss", $searchTerm, $searchTerm, $searchTerm);
    $stmt2->execute();
    $result2 = $stmt2->get_result();
    
    error_log("Broad search results: " . $result2->num_rows);
    
    while ($row = $result2->fetch_assoc()) {
        error_log("Found product in broad search: " . $row['name']);
        
        $main_image = getMainProductImage($conn, $row['id']);
        
        $products[] = [
            'id' => $row['id'],
            'name' => $row['name'],
            'category' => $row['category_name'] ?: 'Uncategorized',
            'price' => number_format($row['price'], 2),
            'image' => $main_image ? $main_image['image_url'] : 'assets/images/no-image.jpg',
            'product_type' => $row['product_type']
        ];
    }
    
    $stmt2->close();
}

// Log final results
error_log("Total products to return: " . count($products));

echo json_encode($products);

$stmt->close();
$conn->close();
?>
