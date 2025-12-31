<?php
// cart.php - Shopping cart page with AJAX API
require_once 'config.php';

// Check if this is an AJAX request for cart operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? '';
    
    // Handle different cart operations
    switch ($action) {
        case 'add':
            // Add item to server-side cart (we'll use session for this)
            session_start();
            $cart = $_SESSION['cart'] ?? [];
            
            $item = [
                'id' => $input['id'],
                'name' => $input['name'],
                'price' => $input['price'],
                'image' => $input['image'],
                'quantity' => $input['quantity'] ?? 1,
                'color' => $input['color'] ?? null,
                'productType' => $input['productType'] ?? null,
                'isUnstitched' => $input['isUnstitched'] ?? false,
                'selectedMeters' => $input['selectedMeters'] ?? null,
                'stitchingOption' => $input['stitchingOption'] ?? null,
                'stitchCharges' => $input['stitchCharges'] ?? 0
            ];
            
            // Check if item already exists in cart
            $existingIndex = null;
            foreach ($cart as $index => $cartItem) {
                if ($cartItem['id'] == $item['id'] &&
                    $cartItem['color'] == $item['color'] &&
                    $cartItem['stitchingOption'] == $item['stitchingOption']) {
                    $existingIndex = $index;
                    break;
                }
            }
            
            if ($existingIndex !== null) {
                $cart[$existingIndex]['quantity'] += $item['quantity'];
            } else {
                $cart[] = $item;
            }
            
            $_SESSION['cart'] = $cart;
            
            echo json_encode(['success' => true, 'message' => 'Item added to cart', 'cart' => $cart]);
            exit;
            
        case 'update':
            // Update item quantity
            session_start();
            $cart = $_SESSION['cart'] ?? [];
            $index = $input['index'] ?? -1;
            $quantity = $input['quantity'] ?? 1;
            
            if ($index >= 0 && $index < count($cart) && $quantity > 0) {
                $cart[$index]['quantity'] = $quantity;
                $_SESSION['cart'] = $cart;
            }
            
            echo json_encode(['success' => true, 'message' => 'Cart updated', 'cart' => $cart]);
            exit;
            
        case 'remove':
            // Remove item from cart
            session_start();
            $cart = $_SESSION['cart'] ?? [];
            $index = $input['index'] ?? -1;
            
            if ($index >= 0 && $index < count($cart)) {
                array_splice($cart, $index, 1);
                $_SESSION['cart'] = $cart;
            }
            
            echo json_encode(['success' => true, 'message' => 'Item removed from cart', 'cart' => $cart]);
            exit;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            exit;
    }
}

// Handle GET requests for cart data
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get_cart') {
    header('Content-Type: application/json');
    session_start();
    $cart = $_SESSION['cart'] ?? [];
    $item_count = array_sum(array_column($cart, 'quantity'));
    
    echo json_encode([
        'success' => true,
        'cart' => $cart,
        'item_count' => $item_count
    ]);
    exit;
}

// Get boutique information for HTML page
$boutique_name = getSetting($conn, 'boutique_name') ?? 'SDesigner Boutique';
$designer_name = getSetting($conn, 'designer_name') ?? 'Dinky Ahuja';
$location = getSetting($conn, 'location') ?? 'Jalandhar, Punjab';
$primary_phone = getSetting($conn, 'primary_phone') ?? '89686-36373';
$secondary_phone = getSetting($conn, 'secondary_phone') ?? '9814927250';
$instagram_url = getSetting($conn, 'instagram_url') ?? '#';
$facebook_url = getSetting($conn, 'facebook_url') ?? '#';
$show_social_links = getSetting($conn, 'show_social_links') ?? '1';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include 'header.php'; ?>
    <title>Shopping Cart | <?php echo htmlspecialchars($boutique_name); ?></title>
    <style>
       

        .cart-section {
            padding: 2rem 0;
            min-height: 60vh;
        }

        @media (min-width: 768px) {
            .cart-section {
                padding: 3rem 0;
            }
        }

        .cart-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
        }

        .page-title {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 1.5rem;
            font-family: 'Playfair Display', serif;
            text-align: center;
        }

        @media (min-width: 768px) {
            .page-title {
                font-size: 2.5rem;
                margin-bottom: 2rem;
            }
        }

        .cart-layout {
            display: flex;
            flex-direction: column;
            gap: 2rem;
        }

        @media (min-width: 992px) {
            .cart-layout {
                flex-direction: row;
                gap: 3rem;
            }
        }

        /* Cart Items */
        .cart-items-container {
            flex: 1;
            background: white;
            border-radius: var(--radius);
            border: 1px solid var(--gray-200);
            overflow: hidden;
        }

        .cart-header {
            display: none;
            grid-template-columns: 2fr 1fr 1fr 1fr 0.5fr;
            padding: 1.5rem;
            background: var(--gray-100);
            border-bottom: 1px solid var(--gray-200);
            font-weight: 600;
            color: var(--gray-700);
        }

        @media (min-width: 768px) {
            .cart-header {
                display: grid;
            }
        }

        .cart-item {
            flex-direction: column;
            padding: 1.25rem;
            border-bottom: 1px solid var(--gray-200);
            transition: var(--transition);
            position: relative;
        }

        @media (min-width: 768px) {
            .cart-item {
                display: grid;
                grid-template-columns: 2fr 1fr 1fr 1fr 0.5fr;
                align-items: center;
                padding: 1.5rem;
            }
        }

        .cart-item:hover {
            background: var(--gray-50);
        }

        .cart-item:last-child {
            border-bottom: none;
        }

        .cart-item-info {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        @media (min-width: 768px) {
            .cart-item-info {
                align-items: center;
                margin-bottom: 0;
                gap: 1.5rem;
            }
        }

        .cart-item-image {
            width: 80px;
            height: 80px;
            border-radius: var(--radius);
            overflow: hidden;
            background: var(--gray-100);
            flex-shrink: 0;
        }

        @media (min-width: 768px) {
            .cart-item-image {
                width: 100px;
                height: 100px;
            }
        }

        .cart-item-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .cart-item-details h3 {
            font-size: 1rem;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 0.25rem;
            line-height: 1.3;
        }

        @media (min-width: 768px) {
            .cart-item-details h3 {
                font-size: 1.125rem;
            }
        }

        .cart-item-details p {
            font-size: 0.8rem;
            color: var(--gray-600);
        }

        @media (min-width: 768px) {
            .cart-item-details p {
                font-size: 0.875rem;
            }
        }

        /* Stitching Info Badge */
        .stitching-badge {
            display: inline-block;
            padding: 0.2rem 0.5rem;
            border-radius: 12px;
            font-size: 0.7rem;
            font-weight: 600;
            margin-top: 0.25rem;
        }

        .stitching-badge.unstitched {
            background: #D1FAE5;
            color: #065F46;
        }

        .stitching-badge.stitched {
            background: #DBEAFE;
            color: #1E40AF;
        }

        .stitch-charges-note {
            font-size: 0.7rem;
            color: var(--primary);
            font-weight: 500;
            margin-top: 0.125rem;
        }

        .cart-item-price {
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 1rem;
        }

        @media (min-width: 768px) {
            .cart-item-price {
                margin-bottom: 0;
                text-align: center;
            }
        }

        .cart-item-quantity {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        @media (min-width: 768px) {
            .cart-item-quantity {
                margin-bottom: 0;
                justify-content: center;
            }
        }

        .quantity-control {
            display: flex;
            align-items: center;
            border: 1px solid var(--gray-300);
            border-radius: var(--radius);
            overflow: hidden;
        }

        .quantity-btn {
            width: 32px;
            height: 32px;
            background: var(--gray-100);
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            color: var(--gray-700);
            transition: var(--transition);
        }

        .quantity-btn:hover {
            background: var(--gray-200);
        }

        .quantity-input {
            width: 40px;
            height: 32px;
            border: none;
            text-align: center;
            font-size: 1rem;
            font-weight: 600;
            color: var(--dark);
            -moz-appearance: textfield;
        }

        .quantity-input::-webkit-outer-spin-button,
        .quantity-input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        .cart-item-total {
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 1rem;
        }

        @media (min-width: 768px) {
            .cart-item-total {
                margin-bottom: 0;
                text-align: center;
            }
        }

        .cart-item-remove {
            position: absolute;
            top: 1.25rem;
            right: 1.25rem;
            background: none;
            border: none;
            color: var(--gray-400);
            cursor: pointer;
            font-size: 1.25rem;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            z-index: 1;
        }

        @media (min-width: 768px) {
            .cart-item-remove {
                position: static;
            }
        }

        .cart-item-remove:hover {
            color: #DC2626;
            background: #FEE2E2;
        }

        /* Cart Summary */
        .cart-summary {
            background: white;
            border-radius: var(--radius);
            border: 1px solid var(--gray-200);
            padding: 1.5rem;
            position: sticky;
            top: 90px;
        }

        @media (min-width: 768px) {
            .cart-summary {
                width: 350px;
                padding: 2rem;
            }
        }

        .summary-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 1.25rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--primary);
        }

        @media (min-width: 768px) {
            .summary-title {
                font-size: 1.5rem;
                margin-bottom: 1.5rem;
            }
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
            color: var(--gray-700);
            font-size: 0.95rem;
        }

        @media (min-width: 768px) {
            .summary-row {
                font-size: 1rem;
            }
        }

        .summary-row.total {
            font-size: 1.125rem;
            font-weight: 700;
            color: var(--dark);
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--gray-200);
        }

        @media (min-width: 768px) {
            .summary-row.total {
                font-size: 1.25rem;
            }
        }

        .summary-row.total span:last-child {
            color: var(--primary);
        }

        .checkout-btn {
            width: 100%;
            padding: 1rem;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: var(--radius);
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            margin-top: 1.25rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
        }

        @media (min-width: 768px) {
            .checkout-btn {
                font-size: 1.125rem;
                margin-top: 1.5rem;
            }
        }

        .checkout-btn:hover {
            background: var(--primary-light);
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .checkout-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
        }

        .continue-shopping {
            text-align: center;
            margin-top: 1.25rem;
        }

        .continue-shopping a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.95rem;
        }

        .continue-shopping a:hover {
            color: var(--primary-light);
        }

        /* Empty Cart */
        .empty-cart {
            text-align: center;
            padding: 3rem 1rem;
        }

        @media (min-width: 768px) {
            .empty-cart {
                padding: 4rem 2rem;
            }
        }

        .empty-cart i {
            font-size: 3rem;
            color: var(--gray-300);
            margin-bottom: 1rem;
        }

        @media (min-width: 768px) {
            .empty-cart i {
                font-size: 4rem;
                margin-bottom: 1.5rem;
            }
        }

        .empty-cart h3 {
            font-size: 1.25rem;
            color: var(--gray-700);
            margin-bottom: 0.5rem;
        }

        @media (min-width: 768px) {
            .empty-cart h3 {
                font-size: 1.5rem;
            }
        }

        .empty-cart p {
            color: var(--gray-600);
            margin-bottom: 1.5rem;
            font-size: 0.95rem;
        }

        @media (min-width: 768px) {
            .empty-cart p {
                font-size: 1rem;
                margin-bottom: 2rem;
            }
        }

        /* Mobile labels for cart items */
        .mobile-label {
            display: inline-block;
            font-weight: 600;
            color: var(--gray-600);
            min-width: 80px;
        }

        @media (min-width: 768px) {
            .mobile-label {
                display: none;
            }
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
    </style>
</head>
<body>
    <!-- Header Section -->
    <?php include 'navigation.php'; ?>

    <!-- Cart Section -->
    <section class="cart-section">
        <div class="cart-container">
            <h1 class="page-title">Your Shopping Cart</h1>
            
            <div class="cart-layout">
                <!-- Cart Items -->
                <div class="cart-items-container" id="cart-items-container">
                    <!-- Cart items will be loaded dynamically -->
                    <div class="empty-cart">
                        <i class="fas fa-shopping-cart"></i>
                        <h3>Your cart is empty</h3>
                        <p>Looks like you haven't added any items to your cart yet.</p>
                        <a href="products.php" class="btn btn-primary">
                            <i class="fas fa-shopping-bag"></i>
                            Start Shopping
                        </a>
                    </div>
                </div>

                <!-- Cart Summary -->
                <div class="cart-summary">
                    <h2 class="summary-title">Order Summary</h2>
                    
                    <div class="summary-row">
                        <span>Subtotal</span>
                        <span id="subtotal">₹0.00</span>
                    </div>
                    
                    <div class="summary-row total">
                        <span>Total Amount</span>
                        <span id="total">₹0.00</span>
                    </div>
                    
                    <button class="checkout-btn" id="checkout-btn">
                        <i class="fas fa-lock"></i>
                        Proceed to Checkout
                    </button>
                    
                    <div class="continue-shopping">
                        <a href="products.php">
                            <i class="fas fa-arrow-left"></i>
                            Continue Shopping
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer Section -->
    <?php include 'footer.php'; ?>

    <!-- Mobile Notification -->
    <div class="mobile-notification" id="mobileNotification">
        <i class="fas fa-check-circle"></i>
        <span>Cart updated!</span>
    </div>

    <script>
        // Load cart on page load
        document.addEventListener('DOMContentLoaded', function() {
            updateCartDisplay();
        });
        
        // Function to normalize image path (convert absolute URLs to relative paths)
        function normalizeImagePath(imagePath) {
            if (!imagePath) return null;
            
            try {
                // Check if it's an absolute URL
                if (imagePath.startsWith('http')) {
                    const url = new URL(imagePath, window.location.origin);
                    return url.pathname; // Return just the path part
                }
                // If it's already a relative path, return as is
                return imagePath;
            } catch(e) {
                // If URL parsing fails, return the original path
                return imagePath;
            }
        }

        function updateCartDisplay() {
            const cart = JSON.parse(localStorage.getItem('cart')) || [];
            const container = document.getElementById('cart-items-container');
            const subtotalEl = document.getElementById('subtotal');
            const totalEl = document.getElementById('total');
            const checkoutBtn = document.getElementById('checkout-btn');
            
            if (cart.length === 0) {
                container.innerHTML = `
                    <div class="empty-cart">
                        <i class="fas fa-shopping-cart"></i>
                        <h3>Your cart is empty</h3>
                        <p>Looks like you haven't added any items to your cart yet.</p>
                        <a href="products.php" class="btn btn-primary">
                            <i class="fas fa-shopping-bag"></i>
                            Start Shopping
                        </a>
                    </div>
                `;
                subtotalEl.textContent = '₹0.00';
                totalEl.textContent = '₹0.00';
                checkoutBtn.disabled = true;
                updateCartCount();
                return;
            }
            
            // Calculate totals
            let subtotal = 0;
            let html = `
                <div class="cart-header">
                    <div>Product</div>
                    <div>Price</div>
                    <div>Quantity</div>
                    <div>Total</div>
                    <div></div>
                </div>
            `;
            
            cart.forEach((item, index) => {
                const itemTotal = item.price * item.quantity;
                subtotal += itemTotal;
                
                // Generate stitching info if applicable
                let stitchingInfo = '';
                if (item.isUnstitched) {
                    const stitchingBadgeClass = item.stitchingOption === 'stitched' ? 'stitched' : 'unstitched';
                    const stitchingText = item.stitchingOption === 'stitched' ? 'Stitched' : 'Unstitched';
                    
                    stitchingInfo = `
                        <div>
                            <span class="stitching-badge ${stitchingBadgeClass}">
                                ${stitchingText}
                            </span>
                            ${item.stitchingOption === 'stitched' && item.stitchCharges > 0 ? 
                                `<div class="stitch-charges-note">(+ ₹${formatPrice(item.stitchCharges)} stitching)</div>` : 
                                ''
                            }
                        </div>
                    `;
                }
                
                html += `
                    <div class="cart-item" data-index="${index}">
                        <div class="cart-item-info">
                            <div class="cart-item-image">
                                <img src="${normalizeImagePath(item.image) || 'assets/images/no-image.jpg'}" alt="${item.name}" 
                                     onerror="this.src='assets/images/no-image.jpg'">
                            </div>
                            <div class="cart-item-details">
                                <h3>${item.name}</h3>
                                <p>${item.isUnstitched ? \`\${item.selectedMeters} meters\` : ''}</p>
                                ${stitchingInfo}
                            </div>
                        </div>
                        <div class="cart-item-price">
                            <span class="mobile-label">Price:</span>
                            ₹${formatPrice(item.price / item.quantity)}
                        </div>
                        <div class="cart-item-quantity">
                            <span class="mobile-label">Qty:</span>
                            <div class="quantity-control">
                                <button class="quantity-btn" onclick="updateQuantity(${index}, -1)">-</button>
                                <input type="number" class="quantity-input" 
                                       value="${item.quantity}" min="1" max="10"
                                       onchange="updateQuantity(${index}, 0, this.value)">
                                <button class="quantity-btn" onclick="updateQuantity(${index}, 1)">+</button>
                            </div>
                        </div>
                        <div class="cart-item-total">
                            <span class="mobile-label">Total:</span>
                            ₹${formatPrice(itemTotal)}
                        </div>
                        <button class="cart-item-remove" onclick="removeFromCart(${index})">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                `;
            });
            
            container.innerHTML = html;
            
            const total = subtotal;
            
            // Update summary
            subtotalEl.textContent = '₹' + formatPrice(subtotal);
            totalEl.textContent = '₹' + formatPrice(total);
            
            checkoutBtn.disabled = false;
            
            // Update cart count in header
            updateCartCount();
        }

        function updateQuantity(index, change, customValue = null) {
            let newQuantity;
            
            if (customValue !== null) {
                newQuantity = parseInt(customValue);
                if (newQuantity < 1 || newQuantity > 10) return;
            } else {
                const cart = JSON.parse(localStorage.getItem('cart')) || [];
                newQuantity = cart[index].quantity + change;
                if (newQuantity < 1 || newQuantity > 10) return;
            }
            
            // Send AJAX request to update server-side cart
            fetch('cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'update',
                    index: index,
                    quantity: newQuantity
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update localStorage with server response
                    localStorage.setItem('cart', JSON.stringify(data.cart));
                    updateCartDisplay();
                    showNotification('Cart updated');
                } else {
                    console.error('Error updating cart:', data.message);
                }
            })
            .catch(error => {
                console.error('Error updating cart:', error);
                // Fallback: update localStorage only
                const cart = JSON.parse(localStorage.getItem('cart')) || [];
                if (customValue !== null) {
                    cart[index].quantity = parseInt(customValue);
                } else {
                    cart[index].quantity = cart[index].quantity + change;
                }
                localStorage.setItem('cart', JSON.stringify(cart));
                updateCartDisplay();
            });
        }

        function removeFromCart(index) {
            // Get item name before removal for notification
            const cart = JSON.parse(localStorage.getItem('cart')) || [];
            const itemName = cart[index].name;
            
            // Send AJAX request to update server-side cart
            fetch('cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'remove',
                    index: index
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update localStorage with server response
                    localStorage.setItem('cart', JSON.stringify(data.cart));
                    updateCartDisplay();
                    showNotification(`${itemName} removed from cart`);
                } else {
                    console.error('Error removing item:', data.message);
                }
            })
            .catch(error => {
                console.error('Error removing item:', error);
                // Fallback: update localStorage only
                const cart = JSON.parse(localStorage.getItem('cart')) || [];
                cart.splice(index, 1);
                localStorage.setItem('cart', JSON.stringify(cart));
                updateCartDisplay();
                showNotification(`${itemName} removed from cart`);
            });
        }

        // Checkout function
        document.getElementById('checkout-btn').addEventListener('click', function() {
            const cart = JSON.parse(localStorage.getItem('cart')) || [];
            if (cart.length === 0) return;
            
            // Here you would typically redirect to checkout page
            // For now, we'll show a confirmation
            if (confirm('Proceed to checkout?')) {
                showNotification('Redirecting to checkout...');
                window.location.href = 'checkout.php';
                
                // For demo purposes, clear cart
                setTimeout(() => {
                    localStorage.removeItem('cart');
                    updateCartDisplay();
                    showNotification('Order placed successfully! Thank you for your purchase.');
                }, 2000);
            }
        });

        // Helper function to format price
        function formatPrice(price) {
            return price.toLocaleString('en-IN', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        // Update cart count in header
        function updateCartCount() {
            // First try to sync with server
            fetch('cart.php?action=get_cart')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update localStorage with server data
                        localStorage.setItem('cart', JSON.stringify(data.cart));
                        const count = data.item_count || 0;
                        const cartCount = document.querySelector('.cart-count');
                        if (cartCount) {
                            cartCount.textContent = count;
                            cartCount.style.display = count > 0 ? 'flex' : 'none';
                        }
                    } else {
                        // Fallback to localStorage
                        const cart = JSON.parse(localStorage.getItem('cart')) || [];
                        const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
                        const cartCount = document.querySelector('.cart-count');
                        if (cartCount) {
                            cartCount.textContent = totalItems;
                            cartCount.style.display = totalItems > 0 ? 'flex' : 'none';
                        }
                    }
                })
                .catch(error => {
                    // Fallback to localStorage on error
                    const cart = JSON.parse(localStorage.getItem('cart')) || [];
                    const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
                    const cartCount = document.querySelector('.cart-count');
                    if (cartCount) {
                        cartCount.textContent = totalItems;
                        cartCount.style.display = totalItems > 0 ? 'flex' : 'none';
                    }
                });
        }

        // Mobile notification function
        function showNotification(message) {
            const notification = document.getElementById('mobileNotification');
            const messageSpan = notification.querySelector('span');
            messageSpan.textContent = message;
            
            notification.classList.add('show');
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