<?php
// cart_handler.php - Handle AJAX cart operations
header('Content-Type: application/json');

require_once 'config.php';

// Enable CORS for AJAX requests
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

try {
    // Check if the request is for getting cart data
    if (isset($_GET['action']) && $_GET['action'] === 'get_cart') {
        // Get cart from session or create empty cart
        session_start();
        $cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
        
        // Calculate total items
        $totalItems = 0;
        foreach ($cart as $item) {
            $totalItems += $item['quantity'];
        }
        
        echo json_encode([
            'success' => true,
            'item_count' => $totalItems,
            'cart' => $cart
        ]);
        exit;
    }
    
    // Handle POST requests for cart operations
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            throw new Exception('Invalid JSON data');
        }
        
        $action = $input['action'] ?? '';
        
        session_start();
        
        // Initialize cart session if not exists
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        
        switch ($action) {
            case 'add':
                $id = $input['id'] ?? 0;
                $quantity = $input['quantity'] ?? 1;
                $name = $input['name'] ?? '';
                $price = $input['price'] ?? 0;
                
                if (!$id || !$name || $price <= 0) {
                    throw new Exception('Invalid product data');
                }
                
                // Check if item already exists in cart
                $itemExists = false;
                foreach ($_SESSION['cart'] as &$item) {
                    if ($item['id'] == $id) {
                        $item['quantity'] += $quantity;
                        $itemExists = true;
                        break;
                    }
                }
                
                // If item doesn't exist, add it
                if (!$itemExists) {
                    $_SESSION['cart'][] = [
                        'id' => $id,
                        'name' => $name,
                        'price' => $price,
                        'quantity' => $quantity
                    ];
                }
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Item added to cart',
                    'cart_count' => array_sum(array_column($_SESSION['cart'], 'quantity'))
                ]);
                break;
                
            case 'update':
                $id = $input['id'] ?? 0;
                $quantity = $input['quantity'] ?? 1;
                
                if ($quantity <= 0) {
                    // Remove item if quantity is 0 or less
                    $_SESSION['cart'] = array_filter($_SESSION['cart'], function($item) use ($id) {
                        return $item['id'] != $id;
                    });
                } else {
                    // Update quantity
                    foreach ($_SESSION['cart'] as &$item) {
                        if ($item['id'] == $id) {
                            $item['quantity'] = $quantity;
                            break;
                        }
                    }
                }
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Cart updated',
                    'cart_count' => array_sum(array_column($_SESSION['cart'], 'quantity'))
                ]);
                break;
                
            case 'remove':
                $id = $input['id'] ?? 0;
                
                $_SESSION['cart'] = array_filter($_SESSION['cart'], function($item) use ($id) {
                    return $item['id'] != $id;
                });
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Item removed from cart',
                    'cart_count' => array_sum(array_column($_SESSION['cart'], 'quantity'))
                ]);
                break;
                
            case 'clear':
                $_SESSION['cart'] = [];
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Cart cleared',
                    'cart_count' => 0
                ]);
                break;
                
            default:
                throw new Exception('Invalid action');
        }
        
        exit;
    }
    
    // If no action matched
    throw new Exception('Invalid request');
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
    exit;
}
?>
