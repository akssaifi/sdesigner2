<?php
// payment_response.php
session_start();
require_once 'config.php';

$order_id = $_GET['order_id'] ?? $_POST['order_id'] ?? '';

if ($order_id) {
    // Update order status based on PhonePe response
    $stmt = $conn->prepare("UPDATE orders SET payment_status = ? WHERE order_id = ?");
    
    // Check if payment was successful (PhonePe returns 'SUCCESS' or 'FAILED')
    $payment_status = ($_POST['code'] ?? '') === 'PAYMENT_SUCCESS' ? 'completed' : 'failed';
    
    $stmt->bind_param("ss", $payment_status, $order_id);
    $stmt->execute();
    $stmt->close();
    
    // Redirect to thank you page
    header("Location: order_success.php?order_id=" . $order_id . "&status=" . $payment_status);
    exit;
}

header("Location: index.php");
?>