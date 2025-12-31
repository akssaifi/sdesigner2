<?php
// order_success.php
session_start();
require_once 'config.php';

$order_id = $_GET['order_id'] ?? '';
$status = $_GET['status'] ?? 'pending';
$test = $_GET['test'] ?? '0';

$boutique_name = getSetting($conn, 'boutique_name') ?? 'SDesigner Boutique';
?>

<!DOCTYPE html>
<html>
<head>
    <?php include 'header.php'; ?>
    <title>Order Confirmation | <?php echo htmlspecialchars($boutique_name); ?></title>
</head>
<body>
    <?php include 'navigation.php'; ?>
    
    <div style="padding: 100px 20px; text-align: center;">
        <?php if ($test === '1'): ?>
            <h1 style="color: orange;">⚠️ TEST PAYMENT</h1>
            <p>This was a test payment. No real money was charged.</p>
        <?php elseif ($status === 'completed' || $status === 'success'): ?>
            <h1 style="color: green;">🎉 Order Received Successfully!</h1>
            <p>Thank you for your order. Your payment has been confirmed.</p>
        <?php elseif ($status === 'failed'): ?>
            <h1 style="color: red;">❌ Payment Failed</h1>
            <p>We're sorry, but your payment could not be processed. Please try again.</p>
        <?php else: ?>
            <h1>📦 Order Received</h1>
            <p>Thank you for your order. We're processing it now.</p>
        <?php endif; ?>
        
        <?php if ($order_id): ?>
            <p style="font-size: 1.2rem; margin: 20px 0;">
                Order ID: <strong style="color: #8B4513;"><?php echo htmlspecialchars($order_id); ?></strong>
            </p>
        <?php endif; ?>
        
        <div style="margin-top: 30px;">
            <?php if ($status === 'failed'): ?>
                <a href="checkout.php?order_id=<?php echo urlencode($order_id); ?>" style="padding: 12px 24px; background: #dc3545; color: white; text-decoration: none; border-radius: 5px; display: inline-block; margin-right: 10px;">
                    <i class="fas fa-redo"></i> Try Again
                </a>
            <?php endif; ?>
            <a href="index.php" style="padding: 12px 24px; background: #8B4513; color: white; text-decoration: none; border-radius: 5px; display: inline-block;">
                <i class="fas fa-home"></i> Return to Home
            </a>
        </div>
        
        <?php if ($status === 'failed'): ?>
            <div style="margin-top: 20px; padding: 15px; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 5px; max-width: 600px; margin-left: auto; margin-right: auto;">
                <h3 style="color: #721c24; margin-top: 0;">Need Help?</h3>
                <p style="color: #721c24; margin-bottom: 10px;">If you're experiencing payment issues:</p>
                <ul style="text-align: left; color: #721c24;">
                    <li>Check your card details and balance</li>
                    <li>Try a different payment method</li>
                    <li>Contact your bank for authorization issues</li>
                    <li>Contact support at: <strong>support@sdesignerjal.in</strong></li>
                </ul>
            </div>
        <?php endif; ?>
    </div>
    
    <?php include 'footer.php'; ?>
</body>
</html>
<?php if (isset($conn)) $conn->close(); ?>