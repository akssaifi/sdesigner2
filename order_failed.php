<?php
// order_failed.php
require_once 'config.php';

$order_id = $_GET['order_id'] ?? '';
?>
<!DOCTYPE html>
<html>
<head>
    <?php include 'header.php'; ?>
    <title>Payment Failed</title>
    <style>
        .failed-page { padding: 100px 20px; text-align: center; }
        .failed-icon { color: #dc3545; font-size: 80px; margin-bottom: 20px; }
        h1 { color: #dc3545; }
        .btn { margin: 10px; }
    </style>
</head>
<body>
    <?php include 'navigation.php'; ?>
    <div class="failed-page">
        <div class="failed-icon"><i class="fas fa-times-circle"></i></div>
        <h1>Payment Failed</h1>
        <p>Order ID: <?php echo htmlspecialchars($order_id); ?></p>
        <p>Please try again or contact us.</p>
        <a href="checkout.php?order_id=<?php echo $order_id; ?>" class="btn btn-primary">
            Retry Payment
        </a>
        <a href="index.php" class="btn btn-secondary">Return Home</a>
    </div>
    <?php include 'footer.php'; ?>
</body>
</html>