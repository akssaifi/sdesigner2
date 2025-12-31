<?php
session_start();
require_once 'config.php';

// Initialize cart session if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'add':
        handleAddToCart();
        break;
    case 'get':
        handleGetCart();
        break;
    case 'update':
        handleUpdateQuantity();
        break;
    case 'remove':
        handleRemoveItem();
        break;
    case 'clear':
        handleClearCart();
        break;
    default:
        echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
        break;
}

function handleAddToCart() {
    global $conn;
    
    $product_id = (int)$_POST['product_id'];
    $quantity = (int)($_POST['quantity'] ?? 1);
    $color = $_POST['color'] ?? '';
    $size = $_POST['size'] ?? '';
    $isUnstitched = (bool)($_POST['isUnstitched'] ?? false);
    $selectedMeters = (float)($_POST['selectedMeters'] ?? 0);
    $stitchingOption = $_POST['stitchingOption'] ?? 'unstitched';
    $stitchCharges = (float)($_POST['stitchCharges'] ?? 0);
    
    if ($product_id <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid product ID']);
        return;
    }
    
    // Get product details from database
    $stmt = $conn->prepare("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $product = $stmt->get_result()->fetch_assoc();
    
    if (!$product) {
        echo json_encode(['status' => 'error', 'message' => 'Product not found']);
        return;
    }
    
    // Check stock availability
    if ($quantity > $product['stock_quantity']) {
        echo json_encode(['status' => 'error', 'message' => 'Insufficient stock. Only ' . $product['stock_quantity'] . ' items available.']);
        return;
    }
    
    // Create cart item
    $cartItem = [
        'id' => $product['id'],
        'name' => $product['name'],
        'price' => $product['price'],
        'image' => $product['image_url'] ?? 'uploads/products/default.jpg',
        'quantity' => $quantity,
        'color' => $color,
        'size' => $size,
        'isUnstitched' => $isUnstitched,
        'selectedMeters' => $selectedMeters,
        'stitchingOption' => $stitchingOption,
        'stitchCharges' => $stitchCharges
    ];
    
    // Check if item already exists in cart
    $itemExists = false;
    foreach ($_SESSION['cart'] as $index => $item) {
        if ($item['id'] == $product['id'] && $item['color'] == $color) {
            if ($isUnstitched) {
                // For unstitched items, also check stitching option
                if ($item['stitchingOption'] == $stitchingOption) {
                    $_SESSION['cart'][$index]['quantity'] += $quantity;
                    $itemExists = true;
                    break;
                }
            } else {
                // For stitched items, just check ID and color
                $_SESSION['cart'][$index]['quantity'] += $quantity;
                $itemExists = true;
                break;
            }
        }
    }
    
    // If item doesn't exist, add it to cart
    if (!$itemExists) {
        $_SESSION['cart'][] = $cartItem;
    }
    
    // Return updated cart
    echo json_encode([
        'status' => 'success', 
        'message' => 'Item added to cart successfully',
        'cart' => $_SESSION['cart'],
        'total_items' => array_sum(array_column($_SESSION['cart'], 'quantity'))
    ]);
}

function handleGetCart() {
    echo json_encode([
        'status' => 'success',
        'cart' => $_SESSION['cart'],
        'total_items' => array_sum(array_column($_SESSION['cart'], 'quantity'))
    ]);
}

function handleUpdateQuantity() {
    $index = (int)$_POST['index'];
    $quantity = (int)$_POST['quantity'];
    
    if ($index < 0 || $index >= count($_SESSION['cart'])) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid cart item index']);
        return;
    }
    
    if ($quantity <= 0) {
        // Remove item if quantity is 0 or less
        array_splice($_SESSION['cart'], $index, 1);
    } else {
        $_SESSION['cart'][$index]['quantity'] = $quantity;
    }
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Cart updated',
        'cart' => $_SESSION['cart'],
        'total_items' => array_sum(array_column($_SESSION['cart'], 'quantity'))
    ]);
}

function handleRemoveItem() {
    $index = (int)$_POST['index'];
    
    if ($index < 0 || $index >= count($_SESSION['cart'])) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid cart item index']);
        return;
    }
    
    array_splice($_SESSION['cart'], $index, 1);
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Item removed from cart',
        'cart' => $_SESSION['cart'],
        'total_items' => array_sum(array_column($_SESSION['cart'], 'quantity'))
    ]);
}

function handleClearCart() {
    $_SESSION['cart'] = [];
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Cart cleared',
        'cart' => [],
        'total_items' => 0
    ]);
}
?>