<?php
// payment_callback.php - Handle PhonePe payment callback
session_start();
require_once 'config.php';

$merchant_id = 'M22NL5SNTHS4B';  // Your merchant ID
$salt_key = '8e236ff1-b080-4fda-8477-cbf324f5da68';  // Your salt key
$salt_index = '1';

// Get the response from PhonePe
$response = json_decode(file_get_contents('php://input'), true);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($response)) {
    // Validate the response
    $encoded_data = $response['response'];
    $decoded_data = base64_decode($encoded_data);
    $payment_response = json_decode($decoded_data, true);
    
    // Verify the checksum
    $checksum = $response['checksum'];
    $data_to_hash = $encoded_data . '/pg/v1/status/' . $merchant_id . '/' . $payment_response['data']['merchantTransactionId'] . $salt_key;
    $calculated_checksum = hash('sha256', $data_to_hash) . '###' . $salt_index;
    
    if ($calculated_checksum === $checksum) {
        $order_id = $payment_response['data']['merchantTransactionId'];
        $payment_status = $payment_response['code'];
        
        // Update order status in database
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        if ($payment_status === 'PAYMENT_SUCCESS') {
            $status = 'paid';
            $message = 'Payment successful!';
        } else {
            $status = 'failed';
            $message = 'Payment failed. Please try again.';
        }
        
        $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE order_id = ?");
        $stmt->bind_param("ss", $status, $order_id);
        $stmt->execute();
        $stmt->close();
        $conn->close();
        
        // Redirect to appropriate page
        if ($payment_status === 'PAYMENT_SUCCESS') {
            header('Location: order_success.php?order_id=' . $order_id);
        } else {
            header('Location: order_failed.php?order_id=' . $order_id);
        }
        exit;
    }
}

// If GET request (redirect from PhonePe)
if (isset($_GET['order_id'])) {
    // Show processing page
    $order_id = $_GET['order_id'];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Processing Payment</title>
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: #f5f5f5;
            font-family: Arial, sans-serif;
        }
        .processing {
            text-align: center;
            padding: 2rem;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #3498db;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto 1rem;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
    <script>
        // Check payment status
        setTimeout(function() {
            window.location.href = 'check_payment.php?order_id=<?php echo $order_id; ?>';
        }, 3000);
    </script>
</head>
<body>
    <div class="processing">
        <div class="spinner"></div>
        <h2>Processing Payment...</h2>
        <p>Please wait while we confirm your payment.</p>
    </div>
</body>
</html>
<?php
    exit;
}
?>