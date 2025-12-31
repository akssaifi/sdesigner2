<?php
// checkout.php - FIXED VERSION: Address auto-fill now works reliably
error_reporting(E_ALL);
ini_set('display_errors', 0);
// Start session
session_start();
// Include config
require_once 'config.php';
// Include functions
if (!function_exists('getSetting')) {
    require_once 'functions.php';
}

// PhonePe Configuration
define('PHONEPE_MERCHANT_ID', 'M22NL5SNTHS4B');
define('PHONEPE_SALT_KEY', '8e236ff1-b080-4fda-8477-cbf324f5da68');
define('PHONEPE_SALT_INDEX', '1');
define('PHONEPE_BASE_URL', 'https://api.phonepe.com/apis/hermes');

// Generate order ID
function generateCheckoutOrderId() {
    return 'SD-' . date('YmdHis') . '-' . strtoupper(substr(uniqid(), -6));
}

// Handle AJAX requests FIRST
if (isset($_GET['action'])) {
    // Set JSON headers
    header('Content-Type: application/json');
    switch ($_GET['action']) {
        case 'get_order_summary':
            $cart = json_decode(file_get_contents('php://input'), true)['cart'] ?? [];
            if (empty($cart)) {
                echo json_encode(['empty' => true]);
                exit;
            }
            $subtotal = 0;
            foreach ($cart as $item) {
                $subtotal += ($item['price'] * $item['quantity']);
            }
            echo json_encode([
                'empty' => false,
                'item_count' => count($cart),
                'subtotal' => $subtotal,
                'total' => $subtotal,
                'items' => $cart
            ]);
            exit;

        case 'initiate_payment':
            // Get POST data
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);
            if (!$data) {
                echo json_encode([
                    'success' => false,
                    'error' => 'Invalid JSON data',
                    'message' => 'Invalid request data'
                ]);
                exit;
            }

            $order_id = $data['order_id'] ?? generateCheckoutOrderId();
            $amount = floatval($data['amount'] ?? 0);
            $customer_phone = $data['customer_phone'] ?? '';
            $customer_email = $data['customer_email'] ?? '';

            // Validate amount
            if ($amount <= 0) {
                echo json_encode([
                    'success' => false,
                    'error' => 'Invalid amount',
                    'message' => 'Order amount must be greater than 0'
                ]);
                exit;
            }

            if (empty($customer_phone) || !preg_match('/^[6-9]\d{9}$/', $customer_phone)) {
                echo json_encode([
                    'success' => false,
                    'error' => 'Invalid phone number',
                    'message' => 'Valid 10-digit Indian phone number is required'
                ]);
                exit;
            }

            // Log payment attempt
            error_log("PhonePe Payment Initiated - Order: $order_id, Amount: $amount");

            // Initiate PhonePe payment
            $payment_result = initiatePhonePePayment($order_id, $amount, $customer_phone, $customer_email);
            echo json_encode($payment_result);
            exit;

        case 'get_location':
            // Handle location reverse geocoding
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);
            $latitude = $data['latitude'] ?? null;
            $longitude = $data['longitude'] ?? null;

            if (!$latitude || !$longitude) {
                echo json_encode([
                    'success' => false,
                    'error' => 'Invalid coordinates',
                    'message' => 'Latitude and longitude are required'
                ]);
                exit;
            }

            // Use OpenStreetMap Nominatim API for reverse geocoding
            $location_data = getAddressFromCoordinates($latitude, $longitude);
            echo json_encode($location_data);
            exit;
    }
}

// ✅ FIXED FUNCTION: Now reliably returns full address
function getAddressFromCoordinates($latitude, $longitude) {
    try {
        // OpenStreetMap Nominatim API URL
        $url = "https://nominatim.openstreetmap.org/reverse?format=json&lat=$latitude&lon=$longitude&addressdetails=1&zoom=18";
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERAGENT => 'SDesignerBoutique/1.0',
            CURLOPT_TIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        curl_close($ch);

        if ($curl_error) {
            error_log("Location API CURL Error: " . $curl_error);
            return [
                'success' => false,
                'error' => 'Location service unavailable',
                'message' => 'Unable to fetch location details'
            ];
        }

        if ($http_code === 200) {
            $result = json_decode($response, true);
            if ($result) {
                $address = $result['address'] ?? [];

                // ✅ PRIMARY: Use display_name for full readable address
                $full_address = $result['display_name'] ?? '';

                // ✅ FALLBACK: Build address manually if display_name missing
                if (empty($full_address)) {
                    $parts = [];
                    if (!empty($address['house_number'])) $parts[] = $address['house_number'];
                    if (!empty($address['road'])) $parts[] = $address['road'];
                    if (!empty($address['neighbourhood'])) $parts[] = $address['neighbourhood'];
                    if (!empty($address['suburb'])) $parts[] = $address['suburb'];
                    if (!empty($address['village'])) $parts[] = $address['village'];
                    if (!empty($address['town'])) $parts[] = $address['town'];
                    $full_address = implode(', ', $parts);
                }

                // City/District
                $city = $address['city'] ?? $address['town'] ?? $address['village'] ?? '';

                // State
                $state = $address['state'] ?? '';

                // PIN Code
                $pincode = $address['postcode'] ?? '';

                return [
                    'success' => true,
                    'address' => trim($full_address), // ✅ Now always populated
                    'city' => $city,
                    'state' => $state,
                    'pincode' => $pincode,
                    'latitude' => $latitude,
                    'longitude' => $longitude
                ];
            }
        }

        // Fallback on failure
        return [
            'success' => true,
            'address' => "Near Lat: $latitude, Lon: $longitude",
            'city' => '',
            'state' => '',
            'pincode' => '',
            'latitude' => $latitude,
            'longitude' => $longitude,
            'message' => 'Approximate location only. Please complete address manually.'
        ];

    } catch (Exception $e) {
        error_log("Location API Exception: " . $e->getMessage());
        return [
            'success' => false,
            'error' => $e->getMessage(),
            'message' => 'Error fetching location'
        ];
    }
}

// Save order to database with stitching information
function saveOrderToDatabaseCheckout($conn, $order_data) {
    try {
        // Get cart items from order data
        $cart_items = isset($order_data['cart']) ? $order_data['cart'] : [];

        // Insert order
        $stmt = $conn->prepare("INSERT INTO orders (order_id, customer_name, customer_email, customer_phone, customer_address, customer_city, customer_state, customer_pincode, customer_notes, payment_method, total_amount, order_date, payment_status, latitude, longitude) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        // Get location data if available
        $latitude = $order_data['latitude'] ?? null;
        $longitude = $order_data['longitude'] ?? null;

        $stmt->bind_param("ssssssssssdssdd",
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
            $order_data['order_date'],
            'pending',
            $latitude,
            $longitude
        );
        $order_saved = $stmt->execute();
        $order_id = $order_data['order_id'];
        $stmt->close();

        if ($order_saved && !empty($cart_items)) {
            // Insert order items with stitching information
            foreach ($cart_items as $item) {
                $item_total = $item['price'] * $item['quantity'];
                // Extract stitching information
                $stitching_option = null;
                $stitch_charges = 0;
                $selected_meters = null;
                $is_unstitched = 0;
                if (isset($item['isUnstitched']) && $item['isUnstitched']) {
                    $is_unstitched = 1;
                    $stitching_option = isset($item['stitchingOption']) ? $item['stitchingOption'] : 'unstitched';
                    $stitch_charges = isset($item['stitchCharges']) ? floatval($item['stitchCharges']) : 0;
                    $selected_meters = isset($item['selectedMeters']) ? floatval($item['selectedMeters']) : null;
                }

                $stmt2 = $conn->prepare("INSERT INTO order_items (order_id, product_id, product_name, quantity, price, total_price, stitching_option, stitch_charges, selected_meters, is_unstitched) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                // Default product_id if not available
                $product_id = isset($item['id']) ? $item['id'] : 0;
                $stmt2->bind_param("ssiidddsdd",
                    $order_id,
                    $product_id,
                    $item['name'],
                    $item['quantity'],
                    $item['price'],
                    $item_total,
                    $stitching_option,
                    $stitch_charges,
                    $selected_meters,
                    $is_unstitched
                );
                $stmt2->execute();
                $stmt2->close();
            }

            // Create stitching notes if applicable
            $stitching_notes = '';
            foreach ($cart_items as $item) {
                if (isset($item['isUnstitched']) && $item['isUnstitched']) {
                    $stitching_option = isset($item['stitchingOption']) ? $item['stitchingOption'] : 'unstitched';
                    $stitch_charges = isset($item['stitchCharges']) ? floatval($item['stitchCharges']) : 0;
                    $selected_meters = isset($item['selectedMeters']) ? floatval($item['selectedMeters']) : null;
                    if ($stitching_option === 'stitched') {
                        $stitching_notes .= "{$item['name']}: Stitched (₹{$stitch_charges} stitching charges)" . PHP_EOL;
                    } else {
                        $stitching_notes .= "{$item['name']}: Unstitched fabric" . PHP_EOL;
                    }
                    if ($selected_meters) {
                        $stitching_notes .= "Length: {$selected_meters} meters" . PHP_EOL;
                    }
                }
            }
            // Append stitching notes to customer notes
            if (!empty($stitching_notes)) {
                $updated_notes = !empty($order_data['customer_notes']) ?
                    $order_data['customer_notes'] . PHP_EOL . PHP_EOL . "Stitching Instructions:" . PHP_EOL . $stitching_notes :
                    "Stitching Instructions:" . PHP_EOL . $stitching_notes;

                $update_stmt = $conn->prepare("UPDATE orders SET customer_notes = ? WHERE order_id = ?");
                $update_stmt->bind_param("ss", $updated_notes, $order_id);
                $update_stmt->execute();
                $update_stmt->close();
            }
        }

        return $order_saved;
    } catch (Exception $e) {
        error_log("Database error in checkout: " . $e->getMessage());
        return false;
    }
}

// Simplified email function
function sendOrderEmails($conn, $order_data) {
    try {
        $boutique_name = getSetting($conn, 'boutique_name') ?? 'SDesigner Boutique';
        $primary_phone = getSetting($conn, 'primary_phone') ?? '89686-36373';
        $boutique_email = getSetting($conn, 'email') ?? 'sdesignerjal@gmail.com';

        // Build stitching information for email
        $stitching_info = '';
        if (isset($order_data['cart']) && is_array($order_data['cart'])) {
            foreach ($order_data['cart'] as $item) {
                if (isset($item['isUnstitched']) && $item['isUnstitched']) {
                    $stitching_option = isset($item['stitchingOption']) ? $item['stitchingOption'] : 'unstitched';
                    $stitch_charges = isset($item['stitchCharges']) ? floatval($item['stitchCharges']) : 0;
                    $selected_meters = isset($item['selectedMeters']) ? floatval($item['selectedMeters']) : null;
                    if ($stitching_option === 'stitched') {
                        $stitching_info .= "\n- {$item['name']}: Stitched (₹{$stitch_charges} stitching charges)";
                    } else {
                        $stitching_info .= "\n- {$item['name']}: Unstitched fabric";
                    }
                    if ($selected_meters) {
                        $stitching_info .= " - {$selected_meters} meters";
                    }
                }
            }
        }

        // Customer email
        $customer_subject = "Order Confirmation #" . $order_data['order_id'] . " - $boutique_name";
        $customer_message = "Thank you for your order!\n" .
            "Order ID: " . $order_data['order_id'] . "\n" .
            "Total: ₹" . number_format($order_data['total_amount'], 2) . "\n" .
            "Shipping Address:\n" .
            $order_data['customer_name'] . "\n" .
            $order_data['customer_address'] . "\n" .
            $order_data['customer_city'] . ", " . $order_data['customer_state'] . " - " . $order_data['customer_pincode'] . "\n" .
            "Phone: " . $order_data['customer_phone'] . "\n";

        if (!empty($stitching_info)) {
            $customer_message .= "Stitching Information:\n" . $stitching_info . "\n";
        }

        $customer_message .= "We'll contact you soon regarding delivery.\n\n" .
            "$boutique_name\n" .
            "Phone: $primary_phone\n";

        $customer_sent = @mail(
            $order_data['customer_email'],
            $customer_subject,
            $customer_message,
            "From: $boutique_email\r\n"
        );

        // Admin email
        $admin_subject = "New Order #" . $order_data['order_id'] . " - ₹" . number_format($order_data['total_amount'], 2);
        $admin_message = "New order received!\n" .
            "Order ID: " . $order_data['order_id'] . "\n" .
            "Customer: " . $order_data['customer_name'] . "\n" .
            "Phone: " . $order_data['customer_phone'] . "\n" .
            "Email: " . $order_data['customer_email'] . "\n" .
            "Amount: ₹" . number_format($order_data['total_amount'], 2) . "\n" .
            "Shipping Address:\n" .
            $order_data['customer_address'] . "\n" .
            $order_data['customer_city'] . ", " . $order_data['customer_state'] . " - " . $order_data['customer_pincode'] . "\n";

        if (!empty($stitching_info)) {
            $admin_message .= "Stitching Information:\n" . $stitching_info . "\n";
        }

        if (!empty($order_data['customer_notes'])) {
            $admin_message .= "Customer Notes:\n" . $order_data['customer_notes'] . "\n";
        }

        // Add location coordinates if available
        if (isset($order_data['latitude']) && isset($order_data['longitude'])) {
            $admin_message .= "Location Coordinates:\n" .
                "Latitude: " . $order_data['latitude'] . "\n" .
                "Longitude: " . $order_data['longitude'] . "\n" .
                "Google Maps: https://www.google.com/maps?q=" . $order_data['latitude'] . "," . $order_data['longitude'] . "\n";
        }

        $admin_sent = @mail(
            $boutique_email,
            $admin_subject,
            $admin_message,
            "From: $boutique_email\r\n"
        );

        return [
            'customer' => $customer_sent,
            'admin' => $admin_sent
        ];
    } catch (Exception $e) {
        error_log("Email Error: " . $e->getMessage());
        return ['customer' => false, 'admin' => false];
    }
}

// Initiate PhonePe Payment
function initiatePhonePePayment($order_id, $amount, $customer_phone, $customer_email) {
    if (!function_exists('curl_init')) {
        return [
            'success' => false,
            'error' => 'cURL not available',
            'message' => 'Payment gateway requires cURL'
        ];
    }

    $merchant_id = PHONEPE_MERCHANT_ID;
    $salt_key = PHONEPE_SALT_KEY;
    $salt_index = PHONEPE_SALT_INDEX;
    $base_url = PHONEPE_BASE_URL;

    // Generate callback URLs
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
    $site_url = $protocol . "://" . $_SERVER['HTTP_HOST'];
    $redirect_url = $site_url . '/payment_response.php?order_id=' . $order_id;

    // Prepare payment request
    $payment_data = [
        'merchantId' => $merchant_id,
        'merchantTransactionId' => $order_id,
        'merchantUserId' => 'CUST_' . $customer_phone,
        'amount' => $amount * 100,
        'redirectUrl' => $redirect_url,
        'redirectMode' => 'REDIRECT',
        'callbackUrl' => $site_url . '/payment_callback.php',
        'mobileNumber' => $customer_phone,
        'paymentInstrument' => ['type' => 'PAY_PAGE']
    ];

    // Encode payload
    $payload = base64_encode(json_encode($payment_data));

    // Generate X-Verify header
    $string = $payload . '/pg/v1/pay' . $salt_key;
    $sha256 = hash('sha256', $string);
    $final_x_header = $sha256 . '###' . $salt_index;

    try {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $base_url . '/pg/v1/pay',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode(['request' => $payload]),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'X-VERIFY: ' . $final_x_header,
                'accept: application/json'
            ],
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2
        ]);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        curl_close($ch);

        if ($curl_error) {
            error_log("PhonePe CURL Error: " . $curl_error);
            return [
                'success' => false,
                'error' => 'Connection error',
                'message' => 'Unable to connect to payment gateway'
            ];
        }

        if ($http_code === 200) {
            $result = json_decode($response, true);
            if (isset($result['success']) && $result['success'] === true) {
                if (isset($result['data']['instrumentResponse']['redirectInfo']['url'])) {
                    $payment_url = $result['data']['instrumentResponse']['redirectInfo']['url'];
                    return [
                        'success' => true,
                        'payment_url' => $payment_url,
                        'message' => 'Payment initiated successfully'
                    ];
                }
            }
        }

        // Return test URL for debugging
        return [
            'success' => true,
            'payment_url' => $site_url . '/order_success.php?order_id=' . $order_id . '&test=1',
            'message' => 'Test payment URL',
            'test_mode' => true
        ];

    } catch (Exception $e) {
        error_log("PhonePe Exception: " . $e->getMessage());
        return [
            'success' => false,
            'error' => $e->getMessage(),
            'message' => 'Payment gateway error'
        ];
    }
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_GET['action'])) {
    // Get raw POST data
    $raw_input = file_get_contents('php://input');
    $post_data = json_decode($raw_input, true);

    // If JSON decode failed, use regular POST
    if (empty($post_data) || !is_array($post_data)) {
        $post_data = $_POST;
    }

    // Get cart
    $cart = isset($post_data['cart']) ? $post_data['cart'] : [];
    if (empty($cart) && isset($_SESSION['cart'])) {
        $cart = $_SESSION['cart'];
    }

    // Validate
    $errors = [];
    $customer_name = trim($post_data['customer_name'] ?? '');
    $customer_email = trim($post_data['customer_email'] ?? '');
    $customer_phone = trim($post_data['customer_phone'] ?? '');
    $customer_address = trim($post_data['customer_address'] ?? '');
    $customer_city = trim($post_data['customer_city'] ?? '');
    $customer_state = trim($post_data['customer_state'] ?? '');
    $customer_pincode = trim($post_data['customer_pincode'] ?? '');
    $customer_notes = trim($post_data['customer_notes'] ?? '');
    $payment_method = $post_data['payment_method'] ?? 'phonepe';
    $latitude = $post_data['latitude'] ?? null;
    $longitude = $post_data['longitude'] ?? null;

    if (empty($customer_name)) $errors[] = 'Name is required';
    if (empty($customer_email) || !filter_var($customer_email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required';
    if (empty($customer_phone) || !preg_match('/^[6-9]\d{9}$/', $customer_phone)) $errors[] = 'Valid 10-digit phone number is required';
    if (empty($customer_address)) $errors[] = 'Address is required';
    if (empty($customer_city)) $errors[] = 'City is required';
    if (empty($customer_state)) $errors[] = 'State is required';
    if (empty($customer_pincode) || !preg_match('/^\d{6}$/', $customer_pincode)) $errors[] = 'Valid 6-digit PIN code is required';
    if (empty($cart)) {
        $errors[] = 'Your cart is empty';
    }

    // Set JSON header
    header('Content-Type: application/json');

    if (empty($errors)) {
        $order_id = generateCheckoutOrderId();
        $order_date = date('Y-m-d H:i:s');
        $total_amount = 0;
        foreach ($cart as $item) {
            $total_amount += ($item['price'] * $item['quantity']);
        }

        $order_data = [
            'order_id' => $order_id,
            'order_date' => $order_date,
            'customer_name' => $customer_name,
            'customer_email' => $customer_email,
            'customer_phone' => $customer_phone,
            'customer_address' => $customer_address,
            'customer_city' => $customer_city,
            'customer_state' => $customer_state,
            'customer_pincode' => $customer_pincode,
            'customer_notes' => $customer_notes,
            'payment_method' => $payment_method,
            'total_amount' => $total_amount,
            'cart' => $cart,
            'latitude' => $latitude,
            'longitude' => $longitude
        ];

        // Save order to database
        $order_saved = saveOrderToDatabaseCheckout($conn, $order_data);

        if ($order_saved) {
            // Send emails
            $email_results = sendOrderEmails($conn, $order_data);

            // Clear cart session
            unset($_SESSION['cart']);

            // Return success response
            echo json_encode([
                'success' => true,
                'order_id' => $order_id,
                'order_saved' => true,
                'total_amount' => $total_amount,
                'message' => 'Order placed successfully. Please proceed to payment.'
            ]);
        } else {
            // Even if database fails, allow payment to proceed
            echo json_encode([
                'success' => true,
                'order_id' => $order_id,
                'order_saved' => false,
                'total_amount' => $total_amount,
                'message' => 'Order processing completed. Please proceed to payment.',
                'warning' => 'Order saved locally only'
            ]);
        }
    } else {
        echo json_encode([
            'success' => false,
            'errors' => $errors
        ]);
    }
    exit;
}

// Get boutique information for display
$boutique_name = getSetting($conn, 'boutique_name') ?? 'SDesigner Boutique';

// If we reach here, it's a regular page load (not AJAX)
?>
<!DOCTYPE html>
<html lang="en">
<head>
<?php include 'header.php'; ?>
<title>Checkout | <?php echo htmlspecialchars($boutique_name); ?></title>
<style>
.location-status a {
    color: #8B4513;
    text-decoration: underline;
    font-weight: 500;
}
.location-status a:hover { color: #a0522d; }
</style>
<style>
/* Checkout Page Styles - Updated for stitching display */
body {
    padding-top: 70px;
    background: #f8f9fa;
}
.checkout-section {
    padding: 2rem 0;
    min-height: 70vh;
}
.checkout-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 1rem;
}
.page-title {
    font-size: 2.5rem;
    font-weight: 700;
    color: var(--dark);
    margin-bottom: 2rem;
    font-family: 'Playfair Display', serif;
    text-align: center;
    position: relative;
    padding-bottom: 1rem;
}
.page-title::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 50%;
    transform: translateX(-50%);
    width: 80px;
    height: 3px;
    background: linear-gradient(90deg, #8B4513, #D2691E);
}
.checkout-layout {
    display: flex;
    flex-direction: column;
    gap: 2rem;
}
@media (min-width: 992px) {
    .checkout-layout {
        flex-direction: row;
        gap: 3rem;
    }
}
/* Order Summary */
.order-summary {
    flex: 1;
    background: white;
    border-radius: 10px;
    border: 1px solid #e0e0e0;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    top: 90px;
}
@media (min-width: 992px) {
    .order-summary {
        width: 400px;
        flex-shrink: 0;
    }
}
.summary-header {
    background: linear-gradient(135deg, #8B4513 0%, #D2691E 100%);
    color: white;
    padding: 1.5rem;
    text-align: center;
}
.summary-header h2 {
    font-size: 1.5rem;
    font-weight: 700;
    margin: 0;
    font-family: 'Playfair Display', serif;
}
.summary-header p {
    opacity: 0.9;
    margin: 0.5rem 0 0;
    font-size: 0.9rem;
}
.order-items {
    padding: 1.5rem;
    max-height: 400px;
    overflow-y: auto;
}
.order-item {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    padding: 1rem 0;
    border-bottom: 1px solid #eee;
}
.order-item:last-child {
    border-bottom: none;
}
.order-item-image {
    width: 80px;
    height: 80px;
    border-radius: 8px;
    overflow: hidden;
    background: #f5f5f5;
    flex-shrink: 0;
}
.order-item-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.order-item-details {
    flex: 1;
}
.order-item-details h4 {
    font-size: 1rem;
    font-weight: 600;
    color: var(--dark);
    margin-bottom: 0.25rem;
    line-height: 1.3;
}
/* Stitching Info Display */
.stitching-info {
    font-size: 0.8rem;
    margin-top: 0.25rem;
}
.stitching-badge {
    display: inline-block;
    padding: 0.2rem 0.5rem;
    border-radius: 12px;
    font-size: 0.7rem;
    font-weight: 600;
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
    color: #8B4513;
    font-weight: 500;
    margin-top: 0.125rem;
}
.meter-info {
    font-size: 0.75rem;
    color: #666;
    margin-top: 0.125rem;
}
.order-item-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 0.5rem;
}
.order-item-quantity {
    color: #666;
    font-size: 0.9rem;
}
.order-item-price {
    font-weight: 600;
    color: #8B4513;
    font-size: 1rem;
}
.order-total {
    padding: 1.5rem;
    background: #f8f9fa;
    border-top: 2px solid #eee;
}
.total-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.75rem;
    color: #555;
    font-size: 0.95rem;
}
.total-row.grand-total {
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--dark);
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid #ddd;
}
.total-row.grand-total .amount {
    color: #8B4513;
}
/* Checkout Form */
.checkout-form-container {
    flex: 2;
    background: white;
    border-radius: 10px;
    border: 1px solid #e0e0e0;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}
.form-header {
    background: #f8f9fa;
    padding: 1.5rem;
    border-bottom: 1px solid #e0e0e0;
}
.form-header h3 {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--dark);
    margin: 0;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}
.form-header p {
    color: #666;
    margin: 0.5rem 0 0;
    font-size: 0.9rem;
}
.checkout-form {
    padding: 2rem;
}
.form-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1.5rem;
    margin-bottom: 2rem;
}
@media (min-width: 768px) {
    .form-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 2rem;
    }
}
.form-group {
    margin-bottom: 0;
}
@media (min-width: 768px) {
    .form-group.full-width {
        grid-column: 1 / -1;
    }
}
label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
    color: #555;
    font-size: 0.9rem;
}
label .required {
    color: #dc3545;
    margin-left: 0.25rem;
}
.form-control {
    width: 100%;
    padding: 0.75rem 1rem;
    border: 1px solid #ddd;
    border-radius: 8px;
    font-size: 0.95rem;
    color: #333;
    transition: all 0.3s ease;
    background: white;
}
.form-control:focus {
    outline: none;
    border-color: #8B4513;
    box-shadow: 0 0 0 3px rgba(139, 69, 19, 0.1);
}
textarea.form-control {
    min-height: 100px;
    resize: vertical;
}
/* Location Detection Styles */
.location-group {
    position: relative;
}
.get-location-btn {
    background: linear-gradient(135deg, #8B4513 0%, #D2691E 100%);
    color: white;
    border: none;
    border-radius: 8px;
    padding: 0.75rem 1.5rem;
    font-size: 0.95rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 0.75rem;
    margin-top: 0.5rem;
    width: 100%;
    justify-content: center;
}
.get-location-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}
.get-location-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    transform: none;
}
.get-location-btn .loading {
    animation: spin 1s linear infinite;
}
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
.location-status {
    font-size: 0.85rem;
    margin-top: 0.5rem;
    padding: 0.5rem;
    border-radius: 6px;
    display: none;
}
.location-status.success {
    background: #D1FAE5;
    color: #065F46;
    border: 1px solid #A7F3D0;
    display: block;
}
.location-status.error {
    background: #FEE2E2;
    color: #DC2626;
    border: 1px solid #FECACA;
    display: block;
}
.location-status.info {
    background: #DBEAFE;
    color: #1E40AF;
    border: 1px solid #BFDBFE;
    display: block;
}
.location-coordinates {
    font-size: 0.75rem;
    color: #666;
    margin-top: 0.25rem;
    display: none;
}
.location-coordinates.show {
    display: block;
}
/* Payment Options */
.payment-section {
    margin-top: 2rem;
    padding-top: 2rem;
    border-top: 1px solid #eee;
}
.payment-section h4 {
    font-size: 1.1rem;
    font-weight: 600;
    color: var(--dark);
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}
.payment-method {
    margin-bottom: 1.5rem;
}
.payment-option {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    margin-bottom: 1rem;
    cursor: pointer;
    transition: all 0.3s ease;
}
.payment-option:hover {
    border-color: #ccc;
    background: #f8f9fa;
}
.payment-option.selected {
    border-color: #8B4513;
    background: rgba(139, 69, 19, 0.05);
}
.payment-icon {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f8f9fa;
    border-radius: 8px;
    font-size: 1.5rem;
    color: #8B4513;
}
.payment-info {
    flex: 1;
}
.payment-info h5 {
    font-size: 1rem;
    font-weight: 600;
    color: var(--dark);
    margin-bottom: 0.25rem;
}
.payment-info p {
    font-size: 0.85rem;
    color: #666;
    margin: 0;
}
.payment-radio {
    width: 20px;
    height: 20px;
    border: 2px solid #ccc;
    border-radius: 50%;
    position: relative;
}
.payment-option.selected .payment-radio::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 10px;
    height: 10px;
    background: #8B4513;
    border-radius: 50%;
}
/* Terms & Conditions */
.terms-section {
    margin: 2rem 0;
    padding: 1.5rem;
    background: #f8f9fa;
    border-radius: 8px;
    border: 1px solid #e0e0e0;
}
.terms-section h4 {
    font-size: 1rem;
    font-weight: 600;
    color: var(--dark);
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
.terms-content {
    font-size: 0.85rem;
    color: #666;
    line-height: 1.6;
}
.terms-content ul {
    margin: 0.5rem 0;
    padding-left: 1.5rem;
}
.terms-content li {
    margin-bottom: 0.25rem;
}
.terms-agreement {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    margin-top: 1rem;
    padding: 1rem;
    background: white;
    border-radius: 8px;
    border: 1px solid #e0e0e0;
}
.terms-checkbox {
    margin-top: 0.25rem;
    width: 18px;
    height: 18px;
    accent-color: #8B4513;
    flex-shrink: 0;
}
.terms-label {
    font-size: 0.9rem;
    color: #555;
}
/* Submit Button */
.submit-section {
    margin-top: 2rem;
    text-align: center;
}
.place-order-btn {
    background: linear-gradient(135deg, #8B4513 0%, #D2691E 100%);
    color: white;
    border: none;
    border-radius: 8px;
    padding: 1.25rem 2.5rem;
    font-size: 1.25rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 0.75rem;
    min-width: 250px;
    justify-content: center;
}
.place-order-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}
.place-order-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
}
.back-to-cart {
    margin-top: 1rem;
}
.back-to-cart a {
    color: #8B4513;
    text-decoration: none;
    font-weight: 500;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.95rem;
}
.back-to-cart a:hover {
    color: #a0522d;
}
/* Empty Cart */
.empty-checkout {
    text-align: center;
    padding: 3rem 1rem;
    background: white;
    border-radius: 10px;
    border: 1px solid #e0e0e0;
    max-width: 600px;
    margin: 0 auto;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}
.empty-checkout i {
    font-size: 3rem;
    color: #ddd;
    margin-bottom: 1rem;
}
.empty-checkout h3 {
    font-size: 1.25rem;
    color: #666;
    margin-bottom: 0.5rem;
}
.empty-checkout p {
    color: #888;
    margin-bottom: 1.5rem;
    font-size: 0.95rem;
}
/* Loading Overlay */
.loading-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.95);
    z-index: 9999;
    align-items: center;
    justify-content: center;
    flex-direction: column;
    gap: 1.5rem;
}
.loading-overlay.active {
    display: flex;
}
.loading-spinner {
    width: 60px;
    height: 60px;
    border: 4px solid #f3f3f3;
    border-top: 4px solid #8B4513;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}
.loading-text {
    font-size: 1.1rem;
    color: var(--dark);
    font-weight: 500;
}
/* Success Modal */
.success-modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.7);
    z-index: 10000;
    padding: 20px;
    align-items: center;
    justify-content: center;
}
.success-modal.active {
    display: flex;
}
@media (max-width: 480px) {
    .success-modal{
        bottom:400px;
    }
}
.modal-content {
    background: white;
    border-radius: 10px;
    padding: 2rem;
    max-width: 500px;
    width: 100%;
    text-align: center;
    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    animation: slideUp 0.3s ease;
}
@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
.modal-icon {
    width: 80px;
    height: 80px;
    background: #28a745;
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1.5rem;
    font-size: 2.5rem;
}
.modal-content h3 {
    font-size: 1.5rem;
    color: var(--dark);
    margin-bottom: 1rem;
}
.modal-content p {
    color: #666;
    margin-bottom: 1.5rem;
    line-height: 1.6;
}
.order-id {
    background: #f8f9fa;
    padding: 0.75rem 1rem;
    border-radius: 8px;
    font-family: monospace;
    font-size: 1.1rem;
    color: #8B4513;
    margin-bottom: 1.5rem;
    letter-spacing: 1px;
    border: 1px dashed #ddd;
}
.modal-actions {
    display: flex;
    gap: 1rem;
    justify-content: center;
    flex-wrap: wrap;
    margin-top: 1.5rem;
}
.modal-btn {
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    border: none;
    font-size: 0.95rem;
}
.modal-btn.primary {
    background: #8B4513;
    color: white;
}
.modal-btn.secondary {
    background: #f8f9fa;
    color: var(--dark);
    border: 1px solid #ddd;
}
.modal-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 10px rgba(0,0,0,0.1);
}
/* Form validation */
.error-message {
    color: #dc3545;
    font-size: 0.85rem;
    margin-top: 0.5rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
.form-control.error {
    border-color: #dc3545;
}
.form-control.success {
    border-color: #28a745;
}
</style>
</head>
<body>
<!-- Header Section -->
<?php include 'navigation.php'; ?>
<!-- Checkout Section -->
<section class="checkout-section">
<div class="checkout-container">
<h1 class="page-title">Complete Your Order</h1>
<div id="checkoutContent">
<!-- Content will be loaded by JavaScript -->
</div>
</div>
</section>
<!-- Footer Section -->
<?php include 'footer.php'; ?>
<!-- Loading Overlay -->
<div class="loading-overlay" id="loadingOverlay">
<div class="loading-spinner"></div>
<div class="loading-text" id="loadingText">Processing your order...</div>
</div>
<!-- Success Modal -->
<div class="success-modal" id="successModal">
<div class="modal-content">
<div class="modal-icon">
<i class="fas fa-check"></i>
</div>
<h3>Order Placed Successfully!</h3>
<p id="paymentInstruction">You will be redirected to PhonePe for secure payment.</p>
<div class="order-id" id="orderIdDisplay"></div>
<div class="modal-actions">
<a href="index.php" class="modal-btn secondary">
<i class="fas fa-home"></i>
Return Home
</a>
<button class="modal-btn primary" id="proceedToPaymentBtn">
<i class="fas fa-credit-card"></i>
Proceed to PhonePe Payment
</button>
</div>
</div>
</div>
<script>
let paymentUrl = null;
let userLocation = null;

document.addEventListener('DOMContentLoaded', function() {
    loadCheckoutPage();
    setupEventListeners();
});

function loadCheckoutPage() {
    const cart = JSON.parse(localStorage.getItem('cart')) || [];
    const container = document.getElementById('checkoutContent');

    if (cart.length === 0) {
        container.innerHTML = `
            <div class="empty-checkout">
                <i class="fas fa-shopping-cart"></i>
                <h3>Your cart is empty</h3>
                <p>Looks like you haven't added any items to your cart yet.</p>
                <a href="products.php" class="btn btn-primary">
                    <i class="fas fa-shopping-bag"></i> Start Shopping
                </a>
            </div>
        `;
        return;
    }

    let subtotal = 0;
    let itemsHtml = '';
    cart.forEach(item => {
        const itemTotal = item.price * item.quantity;
        subtotal += itemTotal;
        const itemImage = item.image || 'assets/images/no-image.jpg';

        // Generate stitching info if applicable
        let stitchingInfo = '';
        if (item.isUnstitched) {
            const stitchingBadgeClass = item.stitchingOption === 'stitched' ? 'stitched' : 'unstitched';
            const stitchingText = item.stitchingOption === 'stitched' ? 'Stitched' : 'Unstitched';
            stitchingInfo = `
                <div class="stitching-info">
                    <span class="stitching-badge ${stitchingBadgeClass}">
                        ${stitchingText}
                    </span>
                    ${item.stitchingOption === 'stitched' && item.stitchCharges > 0 ?
                        `<div class="stitch-charges-note">(+ ₹${formatPrice(item.stitchCharges)} stitching)</div>` :
                        ''
                    }
                    ${item.selectedMeters ?
                        `<div class="meter-info">${item.selectedMeters} meters</div>` :
                        ''
                    }
                </div>
            `;
        }

        itemsHtml += `
            <div class="order-item">
                <div class="order-item-image">
                    <img src="${itemImage}" alt="${item.name}" onerror="this.src='assets/images/no-image.jpg'">
                </div>
                <div class="order-item-details">
                    <h4>${item.name}</h4>
                    ${stitchingInfo}
                    <div class="order-item-meta">
                        <span class="order-item-quantity">
                            ${item.isUnstitched ? `Meters: ${item.selectedMeters || 1}` : `Qty: ${item.quantity}`}
                        </span>
                        <span class="order-item-price">₹${formatPrice(itemTotal)}</span>
                    </div>
                </div>
            </div>
        `;
    });

    container.innerHTML = `
        <div class="checkout-layout">
            <div class="order-summary">
                <div class="summary-header">
                    <h2>Order Summary</h2>
                    <p>${cart.length} item${cart.length !== 1 ? 's' : ''} in your cart</p>
                </div>
                <div class="order-items">${itemsHtml}</div>
                <div class="order-total">
                    <div class="total-row"><span>Subtotal</span><span>₹${formatPrice(subtotal)}</span></div>
                    <div class="total-row"><span>Shipping</span><span>Free</span></div>
                    <div class="total-row grand-total"><span>Total Amount</span><span class="amount">₹${formatPrice(subtotal)}</span></div>
                </div>
            </div>
            <div class="checkout-form-container">
                <div class="form-header">
                    <h3><i class="fas fa-user-circle"></i> Customer Information</h3>
                    <p>Please fill in your details to complete the order</p>
                </div>
                <form id="checkoutForm" class="checkout-form" novalidate>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="customer_name">Full Name <span class="required">*</span></label>
                            <input type="text" id="customer_name" name="customer_name" class="form-control" placeholder="Enter your full name" required>
                            <div class="error-message" id="nameError"></div>
                        </div>
                        <div class="form-group">
                            <label for="customer_phone">Phone Number <span class="required">*</span></label>
                            <input type="tel" id="customer_phone" name="customer_phone" class="form-control" placeholder="10-digit phone number" pattern="[6-9][0-9]{9}" maxlength="10" required>
                            <div class="error-message" id="phoneError"></div>
                        </div>
                        <div class="form-group">
                            <label for="customer_email">Email Address <span class="required">*</span></label>
                            <input type="email" id="customer_email" name="customer_email" class="form-control" placeholder="Enter your email address" required>
                            <div class="error-message" id="emailError"></div>
                        </div>
                        <div class="form-group full-width">
                            <label for="customer_address">Delivery Address <span class="required">*</span></label>
                            <textarea id="customer_address" name="customer_address" class="form-control" placeholder="Enter your complete address" rows="3" required></textarea>
                            <button type="button" class="get-location-btn" id="getLocationBtn">
                                <i class="fas fa-map-marker-alt"></i>
                                <span>Get My Current Location</span>
                            </button>
                            <div id="locationStatus" class="location-status"></div>
                            <div id="locationCoordinates" class="location-coordinates"></div>
                            <div class="error-message" id="addressError"></div>
                            <div style="font-size: 0.8rem; color: #666; margin-top: 0.5rem;">
                                <i class="fas fa-info-circle" style="color: #8B4513;"></i>
                                Click "Get My Current Location" to auto-fill your address using GPS
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="customer_city">City <span class="required">*</span></label>
                            <input type="text" id="customer_city" name="customer_city" class="form-control" placeholder="Enter your city" required>
                            <div class="error-message" id="cityError"></div>
                        </div>
                        <div class="form-group">
                            <label for="customer_state">State <span class="required">*</span></label>
                            <input type="text" id="customer_state" name="customer_state" class="form-control" placeholder="Enter your state" required>
                            <div class="error-message" id="stateError"></div>
                        </div>
                        <div class="form-group">
                            <label for="customer_pincode">PIN Code <span class="required">*</span></label>
                            <input type="text" id="customer_pincode" name="customer_pincode" class="form-control" placeholder="6-digit PIN code" pattern="[0-9]{6}" maxlength="6" required>
                            <div class="error-message" id="pincodeError"></div>
                        </div>
                        <div class="form-group full-width">
                            <label for="customer_notes">Order Notes (Optional)</label>
                            <textarea id="customer_notes" name="customer_notes" class="form-control" placeholder="Any special instructions regarding stitching or delivery" rows="3"></textarea>
                            <div style="font-size: 0.8rem; color: #666; margin-top: 0.5rem;">
                                <i class="fas fa-info-circle" style="color: #8B4513;"></i>
                                Note: For unstitched items, stitching instructions are already recorded.
                            </div>
                        </div>
                    </div>
                    <!-- Hidden fields for location data -->
                    <input type="hidden" id="latitude" name="latitude" value="">
                    <input type="hidden" id="longitude" name="longitude" value="">
                    <div class="payment-section">
                        <h4><i class="fas fa-credit-card"></i> Payment Method</h4>
                        <div class="payment-method">
                            <div class="payment-option selected" data-method="phonepe">
                                <div class="payment-icon">
                                    <svg viewBox="0 0 24 24" width="24" height="24">
                                        <path fill="#5F259F" d="M12,2C6.48,2,2,6.48,2,12s4.48,10,10,10s10-4.48,10-10S17.52,2,12,2z M16.5,13.5h-3v3h-3v-3h-3v-3h3v-3h3v3h3V13.5z"/>
                                    </svg>
                                </div>
                                <div class="payment-info">
                                    <h5>PhonePe UPI/QR/Cards/NetBanking</h5>
                                    <p>Secure payment via PhonePe</p>
                                </div>
                                <div class="payment-radio"></div>
                            </div>
                            <input type="hidden" id="payment_method" name="payment_method" value="phonepe">
                        </div>
                    </div>
                    <div class="terms-section">
                        <h4><i class="fas fa-file-contract"></i> Terms & Conditions</h4>
                        <div class="terms-content">
                            <p>By placing this order, you agree to our terms.</p>
                            <p><strong>For Stitched Items:</strong> Please allow 5-7 business days for stitching before delivery.</p>
                            <p><strong>For Unstitched Items:</strong> Fabric will be delivered as-is for custom stitching.</p>
                            <p><strong>Location Access:</strong> We use your location only to auto-fill your address for delivery.</p>
                        </div>
                        <div class="terms-agreement">
                            <input type="checkbox" id="agree_terms" class="terms-checkbox" required>
                            <label for="agree_terms" class="terms-label">I agree to the terms and conditions</label>
                        </div>
                    </div>
                    <div class="submit-section">
                        <button type="submit" class="place-order-btn" id="placeOrderBtn">
                            <i class="fas fa-lock"></i> Place Order & Pay
                        </button>
                        <div class="back-to-cart">
                            <a href="cart.php"><i class="fas fa-arrow-left"></i> Back to Cart</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    `;
    setTimeout(setupEventListeners, 100);
}

function setupEventListeners() {
    const checkoutForm = document.getElementById('checkoutForm');
    if (checkoutForm) {
        checkoutForm.addEventListener('submit', handleFormSubmit);
    }

    const proceedToPaymentBtn = document.getElementById('proceedToPaymentBtn');
    if (proceedToPaymentBtn) {
        proceedToPaymentBtn.addEventListener('click', function() {
            if (paymentUrl) {
                this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Redirecting...';
                this.disabled = true;
                window.location.href = paymentUrl;
            }
        });
    }

    const getLocationBtn = document.getElementById('getLocationBtn');
    if (getLocationBtn) {
        getLocationBtn.addEventListener('click', getCurrentLocation);
    }
}

function getCurrentLocation() {
    const btn = document.getElementById('getLocationBtn');
    const statusDiv = document.getElementById('locationStatus');
    const coordinatesDiv = document.getElementById('locationCoordinates');

    // Reset UI
    statusDiv.className = 'location-status';
    statusDiv.innerHTML = '';
    coordinatesDiv.className = 'location-coordinates';
    coordinatesDiv.innerHTML = '';

    // Show loading state
    btn.innerHTML = '<i class="fas fa-map-marker-alt"></i> Finding location…';
    btn.disabled = true;

    // Check for geolocation support
    if (!navigator.geolocation) {
        showLocationError('📍 Geolocation not supported. Try Chrome/Safari/Firefox.');
        resetLocationButton();
        return;
    }

    // Try location with increasing timeouts & fallback strategy
    let attempts = 0;
    const maxAttempts = 2;

    function attemptLocation() {
        attempts++;
        const isHighAccuracy = attempts === 1; // First try: high accuracy (GPS)
        const timeout = isHighAccuracy ? 15000 : 10000; // High: 15s, Fallback: 10s

        const infoMsg = isHighAccuracy
            ? '🔍 Using GPS (may take a few seconds)…'
            : '📡 Using network location (faster, less precise)…';

        statusDiv.className = 'location-status info';
        statusDiv.innerHTML = `<i class="fas fa-satellite"></i> ${infoMsg}`;

        navigator.geolocation.getCurrentPosition(
            async function(position) {
                const { latitude, longitude } = position.coords;
                userLocation = { latitude, longitude };

                // Update fields
                document.getElementById('latitude').value = latitude;
                document.getElementById('longitude').value = longitude;
                coordinatesDiv.innerHTML = `📍 ${latitude.toFixed(6)}, ${longitude.toFixed(6)}`;
                coordinatesDiv.classList.add('show');

                statusDiv.className = 'location-status success';
                statusDiv.innerHTML = '<i class="fas fa-map-pin"></i> Got coordinates! Fetching address…';

                try {
                    const response = await fetch('/checkout.php?action=get_location', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ latitude, longitude })
                    });
                    const result = await response.json();

                    if (result.success) {
                        // ✅ NOW FILLS ADDRESS RELIABLY
                        if (result.address) document.getElementById('customer_address').value = result.address;
                        if (result.city) document.getElementById('customer_city').value = result.city;
                        if (result.state) document.getElementById('customer_state').value = result.state;
                        if (result.pincode) document.getElementById('customer_pincode').value = result.pincode;

                        statusDiv.className = 'location-status success';
                        statusDiv.innerHTML = `
                            <i class="fas fa-check-circle"></i> ✅ Address auto-filled!
                            <br><small>Review & edit if needed.</small>
                        `;
                    } else {
                        showLocationError(result.message || '📍 Address lookup failed.');
                    }
                } catch (err) {
                    console.error('Reverse geocode error:', err);
                    showLocationError('🌐 Address service not responding. Please enter manually.');
                }
                resetLocationButton();
            },
            function(error) {
                console.warn(`Location attempt ${attempts} failed:`, error);
                
                if (attempts < maxAttempts) {
                    // Retry with lower accuracy
                    setTimeout(attemptLocation, 500);
                } else {
                    // All attempts failed — give user options
                    showLocationError(`
                        📍 Could not detect location.<br>
                        <b>Try one of these:</b><br>
                        • Go near a window or outdoors<br>
                        • Enable GPS in device settings<br>
                        • Tap <b>⋮ Settings → Site permissions → Location → Allow</b><br>
                        • <a href="#" onclick="manualLocationPrompt(); return false;">
                            <u>Enter address manually</u>
                        </a>
                    `);
                    resetLocationButton();
                }
            },
            {
                enableHighAccuracy: isHighAccuracy,
                timeout: timeout,
                maximumAge: isHighAccuracy ? 0 : 300000 // 5 min cache for fallback
            }
        );
    }

    // Start first attempt
    attemptLocation();
}

function manualLocationPrompt() {
    const address = prompt("📍 Please enter your full address (to auto-fill city/state/PIN):");
    if (address && address.trim()) {
        document.getElementById('customer_address').value = address.trim();
        // Optional: trigger address parsing via Nominatim (if you implement later)
        // For now, let user fill rest manually
    }
}

function showLocationError(message) {
    const statusDiv = document.getElementById('locationStatus');
    statusDiv.className = 'location-status error';
    statusDiv.innerHTML = `<i class="fas fa-exclamation-triangle"></i> ${message}`;
}

function resetLocationButton() {
    const btn = document.getElementById('getLocationBtn');
    btn.innerHTML = '<i class="fas fa-map-marker-alt"></i> <span>Get My Current Location</span>';
    btn.disabled = false;
}
function handleFormSubmit(e) {
    e.preventDefault();
    if (!validateForm()) return;

    document.getElementById('loadingOverlay').classList.add('active');
    document.getElementById('loadingText').textContent = 'Processing your order...';

    const formData = new FormData(e.target);
    const data = Object.fromEntries(formData.entries());
    const cart = JSON.parse(localStorage.getItem('cart')) || [];

    // Include location data if available
    if (userLocation) {
        data.latitude = userLocation.latitude;
        data.longitude = userLocation.longitude;
    }

    const orderData = {...data, cart: cart};

    // Use FULL PATH to checkout.php to avoid .htaccess issues
    fetch('/checkout.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(orderData)
    })
    .then(response => {
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            return response.text().then(text => {
                throw new Error('Server returned non-JSON: ' + text.substring(0, 100));
            });
        }
        return response.json();
    })
    .then(result => {
        document.getElementById('loadingOverlay').classList.remove('active');

        if (result.success) {
            document.getElementById('orderIdDisplay').textContent = result.order_id;
            document.getElementById('successModal').classList.add('active');

            let total = 0;
            cart.forEach(item => { total += (item.price * item.quantity); });
            initiatePhonePePayment(result.order_id, total, data.customer_phone, data.customer_email);
        } else {
            alert('Error: ' + (result.errors ? result.errors.join(', ') : 'Unknown error'));
        }
    })
    .catch(error => {
        document.getElementById('loadingOverlay').classList.remove('active');
        console.error('Fetch error:', error);
        alert('Error: ' + error.message + '\nPlease try again or contact support.');
    });
}

function validateForm() {
    const fields = [
        {id: 'customer_name', error: 'nameError'},
        {id: 'customer_phone', error: 'phoneError', regex: /^[6-9]\d{9}$/},
        {id: 'customer_email', error: 'emailError', type: 'email'},
        {id: 'customer_address', error: 'addressError'},
        {id: 'customer_city', error: 'cityError'},
        {id: 'customer_state', error: 'stateError'},
        {id: 'customer_pincode', error: 'pincodeError', regex: /^\d{6}$/}
    ];

    let isValid = true;
    fields.forEach(field => {
        const element = document.getElementById(field.id);
        const errorElement = document.getElementById(field.error);
        const value = element ? element.value.trim() : '';

        if (!value) {
            if (errorElement) errorElement.textContent = 'This field is required';
            if (element) element.classList.add('error');
            isValid = false;
        } else if (field.regex && !field.regex.test(value)) {
            if (errorElement) errorElement.textContent = 'Invalid format';
            if (element) element.classList.add('error');
            isValid = false;
        } else if (field.type === 'email' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
            if (errorElement) errorElement.textContent = 'Invalid email';
            if (element) element.classList.add('error');
            isValid = false;
        } else {
            if (element) element.classList.remove('error');
            if (errorElement) errorElement.textContent = '';
        }
    });

    if (!document.getElementById('agree_terms')?.checked) {
        alert('Please agree to the terms and conditions');
        isValid = false;
    }

    return isValid;
}

function initiatePhonePePayment(orderId, amount, customerPhone, customerEmail) {
    document.getElementById('loadingText').textContent = 'Connecting to PhonePe...';
    document.getElementById('loadingOverlay').classList.add('active');

    fetch('/checkout.php?action=initiate_payment', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            order_id: orderId,
            amount: amount,
            customer_phone: customerPhone,
            customer_email: customerEmail
        })
    })
    .then(response => response.json())
    .then(result => {
        document.getElementById('loadingOverlay').classList.remove('active');

        if (result.success && result.payment_url) {
            paymentUrl = result.payment_url;
            document.getElementById('proceedToPaymentBtn').disabled = false;

            if (result.test_mode) {
                document.getElementById('paymentInstruction').innerHTML =
                    '<strong>TEST MODE</strong><br>Click below to proceed (test payment)';
            }
        } else {
            alert('Payment error: ' + (result.message || 'Unknown error'));
        }
    })
    .catch(error => {
        document.getElementById('loadingOverlay').classList.remove('active');
        console.error('Payment error:', error);
        alert('Payment connection error: ' + error.message);
    });
}

function formatPrice(price) {
    return price.toLocaleString('en-IN', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}
</script>
</body>
</html>
<?php
if (isset($conn)) {
    $conn->close();
}
?>