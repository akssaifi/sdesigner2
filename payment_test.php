<?php
// payment_test.php - For manual testing without PhonePe
session_start();

$order_id = $_GET['order_id'] ?? 'TEST-' . time();
$test_mode = isset($_GET['test']);

if ($_GET['payment'] ?? '' == 'simulated') {
    // Simulate payment success
    header('Location: order_success.php?order_id=' . $order_id . '&status=success');
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Test Payment</title>
    <style>
        body { font-family: Arial; padding: 50px; text-align: center; }
        .test-panel { max-width: 500px; margin: 0 auto; padding: 30px; border: 1px solid #ddd; }
        .btn { padding: 10px 20px; margin: 10px; }
    </style>
</head>
<body>
    <div class="test-panel">
        <h2>Test Payment Simulation</h2>
        <p>Order ID: <strong><?php echo $order_id; ?></strong></p>
        
        <div style="margin: 30px 0;">
            <a href="order_success.php?order_id=<?php echo $order_id; ?>&status=success" 
               class="btn" style="background: #4CAF50; color: white;">Simulate Success</a>
            <a href="order_failed.php?order_id=<?php echo $order_id; ?>" 
               class="btn" style="background: #f44336; color: white;">Simulate Failure</a>
        </div>
        
        <p><small>This is for testing only. Real payments go through PhonePe.</small></p>
    </div>
</body>
</html>