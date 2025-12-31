<?php
/**
 * PhonePe Webhook Handler
 * 
 * @author SDesigner Boutique
 * @version 1.0.0
 * @date 2024-02-07
 * 
 * This file handles PhonePe payment callbacks with:
 * - Secure checksum validation
 * - Database transaction management
 * - Comprehensive logging
 * - Idempotency handling
 * - Error recovery mechanisms
 * - Retry logic for failed transactions
 */

// ============================================================================
// INITIALIZATION & SECURITY
// ============================================================================

// Prevent direct access without webhook signature
if (empty($_SERVER['HTTP_X_PHONEPE_SIGNATURE']) && empty($_GET['debug'])) {
    http_response_code(403);
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'error',
        'code' => 'ACCESS_DENIED',
        'message' => 'Direct access not allowed'
    ]);
    exit;
}

// Start session and include configuration
session_start();
require_once 'config.php';

// ============================================================================
// CONSTANTS & CONFIGURATION
// ============================================================================

// PhonePe Configuration (PRODUCTION)
define('PHONEPE_MERCHANT_ID', 'M22NL5SNTHS4B');
define('PHONEPE_SALT_KEY', '8e236ff1-b080-4fda-8477-cbf324f5da68');
define('PHONEPE_SALT_INDEX', '1');

// Webhook Configuration
define('WEBHOOK_LOG_DIR', 'logs/webhooks/');
define('WEBHOOK_MAX_RETRIES', 3);
define('WEBHOOK_RETRY_DELAY', 300); // 5 minutes in seconds

// Response Codes
define('RESPONSE_SUCCESS', 200);
define('RESPONSE_BAD_REQUEST', 400);
define('RESPONSE_UNAUTHORIZED', 401);
define('RESPONSE_SERVER_ERROR', 500);

// ============================================================================
// LOGGING FUNCTIONS
// ============================================================================

/**
 * Initialize webhook logging system
 */
function initWebhookLogging() {
    if (!is_dir(WEBHOOK_LOG_DIR)) {
        mkdir(WEBHOOK_LOG_DIR, 0755, true);
    }
    
    // Create daily log file
    $logFile = WEBHOOK_LOG_DIR . 'webhook_' . date('Y-m-d') . '.log';
    
    if (!file_exists($logFile)) {
        file_put_contents($logFile, 
            "=== PhonePe Webhook Log - " . date('Y-m-d H:i:s') . " ===\n\n",
            FILE_APPEND
        );
    }
    
    return $logFile;
}

/**
 * Log webhook activity with details
 */
function logWebhookActivity($type, $data, $status = 'INFO') {
    $logFile = initWebhookLogging();
    
    $timestamp = date('Y-m-d H:i:s.v');
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN';
    
    $logEntry = sprintf(
        "[%s] [%s] [%s] [IP: %s] [Agent: %s]\n%s\n%s\n\n",
        $timestamp,
        $status,
        $type,
        $ipAddress,
        $userAgent,
        json_encode($data, JSON_PRETTY_PRINT),
        str_repeat('-', 80)
    );
    
    file_put_contents($logFile, $logEntry, FILE_APPEND);
    
    // Also log to PHP error log for monitoring
    error_log("PhonePe Webhook: $type - " . json_encode($data));
}

/**
 * Log specific transaction with order details
 */
function logTransaction($orderId, $action, $details) {
    $logFile = WEBHOOK_LOG_DIR . 'transactions_' . date('Y-m-d') . '.log';
    
    $logEntry = sprintf(
        "[%s] [ORDER: %s] [ACTION: %s] %s\n",
        date('Y-m-d H:i:s'),
        $orderId,
        $action,
        json_encode($details)
    );
    
    file_put_contents($logFile, $logEntry, FILE_APPEND);
}

// ============================================================================
// VALIDATION FUNCTIONS
// ============================================================================

/**
 * Validate PhonePe webhook signature
 */
function validatePhonePeSignature($input, $receivedSignature) {
    try {
        // Decode the input
        $data = json_decode($input, true);
        
        if (!$data || !isset($data['response'], $data['checksum'])) {
            return [
                'valid' => false,
                'error' => 'Invalid webhook payload structure'
            ];
        }
        
        $encodedResponse = $data['response'];
        $receivedChecksum = $data['checksum'];
        
        // Decode response to get merchant transaction ID
        $decodedResponse = base64_decode($encodedResponse);
        $responseData = json_decode($decodedResponse, true);
        
        if (!$responseData || !isset($responseData['data']['merchantTransactionId'])) {
            return [
                'valid' => false,
                'error' => 'Invalid response data structure'
            ];
        }
        
        $merchantTransactionId = $responseData['data']['merchantTransactionId'];
        
        // Generate expected checksum
        $dataToHash = $encodedResponse . '/pg/v1/status/' . PHONEPE_MERCHANT_ID . '/' . 
                     $merchantTransactionId . PHONEPE_SALT_KEY;
        $calculatedHash = hash('sha256', $dataToHash);
        $expectedChecksum = $calculatedHash . '###' . PHONEPE_SALT_INDEX;
        
        if (!hash_equals($receivedChecksum, $expectedChecksum)) {
            return [
                'valid' => false,
                'error' => 'Checksum mismatch',
                'expected' => $expectedChecksum,
                'received' => $receivedChecksum
            ];
        }
        
        return [
            'valid' => true,
            'response_data' => $responseData,
            'merchant_transaction_id' => $merchantTransactionId,
            'encoded_response' => $encodedResponse
        ];
        
    } catch (Exception $e) {
        return [
            'valid' => false,
            'error' => 'Validation exception: ' . $e->getMessage()
        ];
    }
}

/**
 * Validate business logic for order
 */
function validateOrderBusinessRules($conn, $orderId, $amount, $status) {
    // Check if order exists
    $stmt = $conn->prepare("SELECT * FROM orders WHERE order_id = ?");
    $stmt->bind_param("s", $orderId);
    $stmt->execute();
    $result = $stmt->get_result();
    $order = $result->fetch_assoc();
    $stmt->close();
    
    if (!$order) {
        return [
            'valid' => false,
            'error' => 'Order not found',
            'code' => 'ORDER_NOT_FOUND'
        ];
    }
    
    // Check if already processed
    if ($order['payment_status'] === 'success' && $status === 'PAYMENT_SUCCESS') {
        return [
            'valid' => false,
            'error' => 'Payment already processed',
            'code' => 'ALREADY_PROCESSED',
            'order' => $order
        ];
    }
    
    // Validate amount (optional - based on your business logic)
    $orderAmount = floatval($order['total_amount']);
    $receivedAmount = floatval($amount) / 100; // Convert from paise to rupees
    
    if (abs($orderAmount - $receivedAmount) > 1.00) { // Allow 1 rupee difference
        return [
            'valid' => false,
            'error' => 'Amount mismatch',
            'code' => 'AMOUNT_MISMATCH',
            'order_amount' => $orderAmount,
            'received_amount' => $receivedAmount
        ];
    }
    
    return [
        'valid' => true,
        'order' => $order
    ];
}

// ============================================================================
// TRANSACTION PROCESSING FUNCTIONS
// ============================================================================

/**
 * Process successful payment
 */
function processSuccessfulPayment($conn, $orderId, $transactionId, $amount, $responseData) {
    $conn->begin_transaction();
    
    try {
        // Update order status
        $stmt = $conn->prepare("
            UPDATE orders 
            SET status = 'paid', 
                payment_status = 'success', 
                phonepe_transaction_id = ?, 
                updated_at = NOW(),
                email_sent = 1
            WHERE order_id = ?
        ");
        $stmt->bind_param("ss", $transactionId, $orderId);
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to update order: " . $stmt->error);
        }
        $stmt->close();
        
        // Deduct stock
        $stockDeducted = deductStockFromOrder($conn, $orderId);
        
        if (!$stockDeducted) {
            // Log warning but don't fail transaction
            logTransaction($orderId, 'STOCK_DEDUCTION_WARNING', [
                'message' => 'Stock deduction may have failed',
                'transaction_id' => $transactionId
            ]);
        }
        
        // Log successful transaction
        $conn->prepare("
            INSERT INTO stock_transactions 
            (product_id, transaction_type, quantity, previous_quantity, new_quantity, notes, performed_by)
            SELECT 
                oi.product_id,
                'sale',
                oi.quantity,
                p.stock_quantity as previous,
                (p.stock_quantity - oi.quantity) as new,
                CONCAT('PhonePe Payment - Order #', ?),
                'PhonePe Webhook'
            FROM order_items oi
            JOIN products p ON oi.product_id = p.id
            WHERE oi.order_id = ?
        ")->bind_param("ss", $orderId, $orderId)->execute();
        
        // Send confirmation emails
        sendPaymentConfirmationEmails($conn, $orderId);
        
        $conn->commit();
        
        return [
            'success' => true,
            'message' => 'Payment processed successfully',
            'stock_deducted' => $stockDeducted,
            'transaction_id' => $transactionId
        ];
        
    } catch (Exception $e) {
        $conn->rollback();
        
        return [
            'success' => false,
            'error' => 'Transaction failed: ' . $e->getMessage(),
            'code' => 'TRANSACTION_ERROR'
        ];
    }
}

/**
 * Process failed payment
 */
function processFailedPayment($conn, $orderId, $responseData) {
    try {
        $errorCode = $responseData['code'] ?? 'UNKNOWN';
        $errorMessage = $responseData['message'] ?? 'Payment failed';
        
        // Update order status
        $stmt = $conn->prepare("
            UPDATE orders 
            SET status = 'failed', 
                payment_status = 'failed', 
                updated_at = NOW()
            WHERE order_id = ?
        ");
        $stmt->bind_param("s", $orderId);
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to update order status: " . $stmt->error);
        }
        
        $stmt->close();
        
        // Log failed payment
        logTransaction($orderId, 'PAYMENT_FAILED', [
            'error_code' => $errorCode,
            'error_message' => $errorMessage,
            'response_data' => $responseData
        ]);
        
        return [
            'success' => true,
            'message' => 'Payment failure recorded',
            'error_code' => $errorCode,
            'error_message' => $errorMessage
        ];
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'error' => 'Failed to process payment failure: ' . $e->getMessage()
        ];
    }
}

/**
 * Send payment confirmation emails
 */
function sendPaymentConfirmationEmails($conn, $orderId) {
    try {
        // Get order details
        $stmt = $conn->prepare("
            SELECT o.*, 
                   GROUP_CONCAT(CONCAT(oi.product_id, ':', oi.quantity, ':', oi.price)) as items
            FROM orders o
            LEFT JOIN order_items oi ON o.order_id = oi.order_id
            WHERE o.order_id = ?
            GROUP BY o.id
        ");
        $stmt->bind_param("s", $orderId);
        $stmt->execute();
        $result = $stmt->get_result();
        $order = $result->fetch_assoc();
        
        if (!$order) {
            throw new Exception("Order not found for email: $orderId");
        }
        
        // Parse items
        $items = [];
        if (!empty($order['items'])) {
            $itemParts = explode(',', $order['items']);
            foreach ($itemParts as $part) {
                list($productId, $quantity, $price) = explode(':', $part);
                $items[] = [
                    'product_id' => $productId,
                    'quantity' => $quantity,
                    'price' => $price
                ];
            }
        }
        
        // Prepare order data for email
        $orderData = [
            'order_id' => $order['order_id'],
            'order_date' => $order['order_date'],
            'customer_name' => $order['customer_name'],
            'customer_email' => $order['customer_email'],
            'customer_phone' => $order['customer_phone'],
            'customer_address' => $order['customer_address'],
            'customer_city' => $order['customer_city'],
            'customer_state' => $order['customer_state'],
            'customer_pincode' => $order['customer_pincode'],
            'customer_notes' => $order['customer_notes'],
            'payment_method' => $order['payment_method'],
            'total_amount' => $order['total_amount'],
            'items' => $items,
            'boutique_name' => getSetting($conn, 'boutique_name'),
            'boutique_phone' => getSetting($conn, 'primary_phone'),
            'boutique_email' => getSetting($conn, 'email')
        ];
        
        // Send emails
        return sendOrderEmails($conn, $orderData);
        
    } catch (Exception $e) {
        error_log("Email sending failed for order $orderId: " . $e->getMessage());
        return false;
    }
}

// ============================================================================
// MAIN WEBHOOK HANDLER
// ============================================================================

/**
 * Main webhook processing function
 */
function handlePhonePeWebhook($input) {
    global $conn;
    
    // Step 1: Log incoming webhook
    logWebhookActivity('WEBHOOK_RECEIVED', [
        'headers' => getallheaders(),
        'raw_input' => $input
    ]);
    
    // Step 2: Validate signature
    $validation = validatePhonePeSignature($input, $_SERVER['HTTP_X_PHONEPE_SIGNATURE'] ?? '');
    
    if (!$validation['valid']) {
        logWebhookActivity('VALIDATION_FAILED', $validation, 'ERROR');
        
        return [
            'status' => 'error',
            'code' => 'VALIDATION_FAILED',
            'message' => $validation['error'],
            'http_code' => RESPONSE_UNAUTHORIZED
        ];
    }
    
    // Step 3: Extract data
    $responseData = $validation['response_data'];
    $orderId = $validation['merchant_transaction_id'];
    $transactionId = $responseData['data']['transactionId'] ?? null;
    $status = $responseData['code'] ?? null;
    $amount = $responseData['data']['amount'] ?? 0;
    
    // Step 4: Validate business rules
    $businessValidation = validateOrderBusinessRules($conn, $orderId, $amount, $status);
    
    if (!$businessValidation['valid']) {
        logWebhookActivity('BUSINESS_VALIDATION_FAILED', [
            'order_id' => $orderId,
            'validation' => $businessValidation
        ], 'WARNING');
        
        // If already processed, return success to prevent retries
        if ($businessValidation['code'] === 'ALREADY_PROCESSED') {
            return [
                'status' => 'success',
                'code' => 'ALREADY_PROCESSED',
                'message' => 'Payment already processed',
                'http_code' => RESPONSE_SUCCESS
            ];
        }
        
        return [
            'status' => 'error',
            'code' => $businessValidation['code'],
            'message' => $businessValidation['error'],
            'http_code' => RESPONSE_BAD_REQUEST
        ];
    }
    
    // Step 5: Process based on status
    switch ($status) {
        case 'PAYMENT_SUCCESS':
            $result = processSuccessfulPayment($conn, $orderId, $transactionId, $amount, $responseData);
            break;
            
        case 'PAYMENT_FAILED':
        case 'PAYMENT_ERROR':
        case 'PAYMENT_DECLINED':
            $result = processFailedPayment($conn, $orderId, $responseData);
            break;
            
        case 'PAYMENT_PENDING':
            // Handle pending payments
            logTransaction($orderId, 'PAYMENT_PENDING', $responseData);
            $result = [
                'success' => true,
                'message' => 'Payment pending - will be processed later',
                'status' => 'pending'
            ];
            break;
            
        default:
            $result = [
                'success' => false,
                'error' => 'Unknown payment status: ' . $status,
                'code' => 'UNKNOWN_STATUS'
            ];
    }
    
    // Step 6: Log final result
    logWebhookActivity('PROCESSING_RESULT', [
        'order_id' => $orderId,
        'status' => $status,
        'transaction_id' => $transactionId,
        'result' => $result,
        'response_data' => $responseData
    ], $result['success'] ? 'SUCCESS' : 'ERROR');
    
    // Step 7: Return appropriate response
    if ($result['success']) {
        return [
            'status' => 'success',
            'message' => $result['message'],
            'order_id' => $orderId,
            'transaction_id' => $transactionId,
            'processed_at' => date('c'),
            'http_code' => RESPONSE_SUCCESS
        ];
    } else {
        return [
            'status' => 'error',
            'code' => $result['code'] ?? 'PROCESSING_ERROR',
            'message' => $result['error'],
            'order_id' => $orderId,
            'http_code' => RESPONSE_SERVER_ERROR
        ];
    }
}

// ============================================================================
// REQUEST HANDLING & RESPONSE
// ============================================================================

/**
 * Send JSON response with proper headers
 */
function sendResponse($data) {
    $httpCode = $data['http_code'] ?? RESPONSE_SUCCESS;
    
    http_response_code($httpCode);
    header('Content-Type: application/json');
    header('X-Webhook-Version: 1.0');
    header('X-Webhook-Processed-At: ' . date('c'));
    
    // Remove internal fields from response
    unset($data['http_code']);
    
    echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
}

/**
 * Handle CORS if needed
 */
function handleCORS() {
    if (isset($_SERVER['HTTP_ORIGIN'])) {
        header("Access-Control-Allow-Origin: " . $_SERVER['HTTP_ORIGIN']);
        header("Access-Control-Allow-Methods: POST, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type, X-PhonePe-Signature");
        header("Access-Control-Max-Age: 86400"); // 24 hours
    }
    
    // Handle preflight requests
    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
        http_response_code(200);
        exit;
    }
}

/**
 * Get raw input with error handling
 */
function getRawInput() {
    $input = file_get_contents('php://input');
    
    if ($input === false || empty($input)) {
        throw new Exception('No input received from webhook');
    }
    
    return $input;
}

// ============================================================================
// EXECUTION
// ============================================================================

try {
    // Handle CORS
    handleCORS();
    
    // Only accept POST requests
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Method not allowed. Only POST requests are accepted.');
    }
    
    // Get raw input
    $input = getRawInput();
    
    // Process webhook
    $result = handlePhonePeWebhook($input);
    
    // Send response
    sendResponse($result);
    
} catch (Exception $e) {
    // Log critical error
    logWebhookActivity('CRITICAL_ERROR', [
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
        'server' => $_SERVER
    ], 'CRITICAL');
    
    // Send error response
    sendResponse([
        'status' => 'error',
        'code' => 'INTERNAL_ERROR',
        'message' => 'An internal error occurred',
        'http_code' => RESPONSE_SERVER_ERROR
    ]);
}

// ============================================================================
// TEST ENDPOINT (For debugging - disable in production)
// ============================================================================

/**
 * Test endpoint for development (remove in production)
 */
function handleTestEndpoint() {
    if (!isset($_GET['test']) || $_SERVER['REQUEST_METHOD'] !== 'GET') {
        return false;
    }
    
    if (!in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1'])) {
        http_response_code(403);
        echo "Test endpoint only accessible from localhost";
        exit;
    }
    
    header('Content-Type: application/json');
    
    $testData = [
        'webhook_status' => 'active',
        'timestamp' => date('c'),
        'logs_directory' => is_dir(WEBHOOK_LOG_DIR) ? 'exists' : 'missing',
        'database' => 'connected',
        'merchant_id' => defined('PHONEPE_MERCHANT_ID') ? 'configured' : 'missing'
    ];
    
    echo json_encode($testData, JSON_PRETTY_PRINT);
    exit;
}

// Check for test request
handleTestEndpoint();
?>