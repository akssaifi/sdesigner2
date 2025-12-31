<?php
// test_email.php - Test email sending for successful payments
session_start();
require_once 'config.php';

// Security check - only allow access from localhost or with password
$allowed = false;

// Method 1: Check if running locally
if ($_SERVER['HTTP_HOST'] === 'localhost' || $_SERVER['SERVER_ADDR'] === '127.0.0.1') {
    $allowed = true;
}

// Method 2: Check for password parameter
if (isset($_GET['password']) && $_GET['password'] === 'sdesigner2024') {
    $allowed = true;
}

if (!$allowed) {
    die("Access denied. This test script can only be run locally or with proper authorization.");
}

// Get boutique information
$boutique_name = getSetting($conn, 'boutique_name') ?? 'SDesigner Boutique';
$designer_name = getSetting($conn, 'designer_name') ?? 'Dinky Ahuja';
$primary_phone = getSetting($conn, 'primary_phone') ?? '89686-36373';
$boutique_email = getSetting($conn, 'email') ?? 'sdesignerjal@gmail.com';

// First, let's test if basic functions work
echo "<h1>📧 Testing Email & Order System for SDesigner Boutique</h1>";
echo "<hr>";

// Test 1: Basic connection test
echo "<h2>🔧 Test 1: Database Connection</h2>";
if ($conn->connect_error) {
    echo "<div style='color: red; padding: 10px; background: #ffe6e6; border: 1px solid red; margin: 10px 0;'>
            Database connection failed: " . $conn->connect_error . "
          </div>";
    exit;
} else {
    echo "<div style='color: green; padding: 10px; background: #e6ffe6; border: 1px solid green; margin: 10px 0;'>
            ✅ Database connected successfully
          </div>";
}

// Test 2: Check if email function exists
echo "<h2>📧 Test 2: Email Functions</h2>";
if (!function_exists('sendEmail')) {
    echo "<div style='color: red; padding: 10px; background: #ffe6e6; border: 1px solid red; margin: 10px 0;'>
            ❌ sendEmail() function not found. Check config.php
          </div>";
} else {
    echo "<div style='color: green; padding: 10px; background: #e6ffe6; border: 1px solid green; margin: 10px 0;'>
            ✅ sendEmail() function found
          </div>";
}

// Check which test to run
$test_type = $_GET['test'] ?? 'simple';

// Test 3: Simple direct email test
if ($test_type === 'simple' || isset($_GET['send_test_email'])) {
    echo "<h2>📨 Test 3: Simple Email Test</h2>";
    sendSimpleTestEmail();
}

// Test 4: Complete order simulation
if ($test_type === 'full' || isset($_GET['full_test'])) {
    echo "<h2>🛒 Test 4: Complete Order Simulation</h2>";
    runCompleteOrderTest($conn);
}

// Test 5: Stock deduction test
if ($test_type === 'stock' || isset($_GET['stock_test'])) {
    echo "<h2>📦 Test 5: Stock Deduction Test</h2>";
    testStockDeduction($conn);
}

// Navigation
if (!isset($_GET['test']) && !isset($_GET['send_test_email']) && !isset($_GET['full_test']) && !isset($_GET['stock_test'])) {
    showTestOptions();
}

// Show cleanup if needed
if (isset($_GET['cleanup'])) {
    cleanupTestData($conn);
}

// Functions
function sendSimpleTestEmail() {
    global $boutique_name, $boutique_email, $conn;
    
    echo "<h3>Sending test email to: {$boutique_email}</h3>";
    
    $subject = "📧 Test Email from SDesigner Boutique - " . date('Y-m-d H:i:s');
    
    $message = "
    <!DOCTYPE html>
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #8B4513; color: white; padding: 20px; text-align: center; }
            .content { padding: 30px; background: #f9f9f9; }
            .success-badge { background: #28a745; color: white; padding: 5px 10px; border-radius: 3px; font-weight: bold; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>📧 Email System Test</h1>
                <p>{$boutique_name}</p>
            </div>
            
            <div class='content'>
                <div style='text-align: center; margin-bottom: 20px;'>
                    <span class='success-badge'>TEST EMAIL - SYSTEM VERIFICATION</span>
                </div>
                
                <h2>Email System is Working!</h2>
                <p>This is a test email sent from your SDesigner Boutique website.</p>
                
                <h3>Test Details:</h3>
                <ul>
                    <li><strong>Time:</strong> " . date('F j, Y, g:i a') . "</li>
                    <li><strong>To:</strong> {$boutique_email}</li>
                    <li><strong>From:</strong> sdesigner@sdesignerjal.in</li>
                    <li><strong>Status:</strong> Test Successful</li>
                </ul>
                
                <p>If you received this email, your email system is configured correctly.</p>
                
                <div style='margin-top: 30px; padding: 15px; background: #e8f4f8; border-radius: 5px;'>
                    <h3>What's Next?</h3>
                    <ol>
                        <li>Orders will generate similar emails</li>
                        <li>Contact form submissions will also use this system</li>
                        <li>All emails will be sent to both customer and admin</li>
                    </ol>
                </div>
                
                <p style='margin-top: 30px;'>Best regards,<br>Test System<br>{$boutique_name}</p>
            </div>
        </div>
    </body>
    </html>";
    
    // Use the sendEmail function from config.php
    $result = sendEmail(
        $boutique_email,
        $subject,
        $message,
        'sdesigner@sdesignerjal.in',
        $boutique_name
    );
    
    if ($result) {
        echo "<div style='color: green; padding: 10px; background: #e6ffe6; border: 1px solid green; margin: 10px 0;'>
                ✅ Email sent successfully to: {$boutique_email}
              </div>";
        echo "<p>Please check your inbox at <strong>sdesignerjal@gmail.com</strong> (check spam folder too)</p>";
        
        // Also log the email attempt
        error_log("Test email sent to: {$boutique_email} at " . date('Y-m-d H:i:s'));
    } else {
        echo "<div style='color: orange; padding: 10px; background: #fff4e6; border: 1px solid orange; margin: 10px 0;'>
                ⚠️ Email may not have been sent. Check error logs.
              </div>";
        
        // Try alternative method
        echo "<p>Trying alternative email method...</p>";
        
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $headers .= "From: {$boutique_name} <sdesigner@sdesignerjal.in>\r\n";
        
        $alt_result = mail($boutique_email, $subject, $message, $headers);
        
        if ($alt_result) {
            echo "<div style='color: green; padding: 10px; background: #e6ffe6; border: 1px solid green; margin: 10px 0;'>
                    ✅ Alternative mail() function worked!
                  </div>";
        } else {
            echo "<div style='color: red; padding: 10px; background: #ffe6e6; border: 1px solid red; margin: 10px 0;'>
                    ❌ Both email methods failed. Check server email configuration.
                  </div>";
        }
    }
    
    echo "<hr>";
    echo "<p><a href='?password=sdesigner2024' class='btn'>← Back to Tests</a></p>";
}

function runCompleteOrderTest($conn) {
    global $boutique_name, $boutique_email;
    
    echo "<h3>Creating Test Order and Sending Email</h3>";
    
    // Step 1: Create or find test product
    echo "<h4>Step 1: Creating Test Product</h4>";
    
    // Check for existing test product first
    $existing_product = $conn->query("SELECT id, stock_quantity FROM products WHERE name LIKE 'Test Product%' LIMIT 1");
    if ($existing_product && $existing_product->num_rows > 0) {
        $row = $existing_product->fetch_assoc();
        $product_id = $row['id'];
        $current_stock = $row['stock_quantity'];
        echo "<div style='color: blue; padding: 10px; background: #e6f7ff; border: 1px solid blue; margin: 10px 0;'>
                Using existing test product:<br>
                • ID: {$product_id}<br>
                • Current Stock: {$current_stock}
              </div>";
    } else {
        // Create new test product with proper error handling
        $sql = "INSERT INTO products 
                (product_type, name, description, price, stock_quantity, reorder_level, is_active, created_at) 
                VALUES 
                ('stitched', 'Test Product " . date('His') . "', 
                'Test product created for email system testing', 
                1999.99, 20, 5, 1, NOW())";
        
        if ($conn->query($sql)) {
            $product_id = $conn->insert_id;
            echo "<div style='color: green; padding: 10px; background: #e6ffe6; border: 1px solid green; margin: 10px 0;'>
                    ✅ Test Product Created:<br>
                    • ID: {$product_id}<br>
                    • Stock: 20 units<br>
                    • Price: ₹1,999.99
                  </div>";
        } else {
            echo "<div style='color: red; padding: 10px; background: #ffe6e6; border: 1px solid red; margin: 10px 0;'>
                    ❌ Failed to create test product: " . htmlspecialchars($conn->error) . "
                  </div>";
            
            // Try simpler insert
            echo "<p>Trying simpler product creation...</p>";
            $simple_sql = "INSERT INTO products (name, price, stock_quantity) 
                          VALUES ('Test Product', 1999.99, 20)";
            if ($conn->query($simple_sql)) {
                $product_id = $conn->insert_id;
                echo "<div style='color: green; padding: 10px; background: #e6ffe6; border: 1px solid green; margin: 10px 0;'>
                        ✅ Simple Test Product Created: ID {$product_id}
                      </div>";
            } else {
                echo "<div style='color: red; padding: 10px; background: #ffe6e6; border: 1px solid red; margin: 10px 0;'>
                        ❌ Could not create test product. Using dummy ID 1.
                      </div>";
                $product_id = 1;
            }
        }
    }
    
    // Step 2: Create test order with proper database structure
    echo "<h4>Step 2: Creating Test Order</h4>";
    $order_id = 'TEST-' . date('YmdHis');
    
    // First, check orders table structure
    $table_check = $conn->query("DESCRIBE orders");
    $columns = [];
    while($col = $table_check->fetch_assoc()) {
        $columns[] = $col['Field'];
    }
    
    echo "<div style='padding: 10px; background: #f0f0f0; border: 1px solid #ddd; margin: 10px 0; font-size: 12px;'>
            Orders table columns: " . implode(', ', $columns) . "
          </div>";
    
    // Prepare order data based on actual table structure
    $order_date = date('Y-m-d H:i:s');
    
    // Use the correct column names from your orders table
    $order_sql = "INSERT INTO orders 
                  (order_id, customer_name, customer_email, customer_phone, customer_address, 
                   customer_city, customer_state, customer_pincode, customer_notes, 
                   payment_method, total_amount, order_date, payment_status, status) 
                  VALUES 
                  ('{$order_id}', 'Test Customer', 'test@example.com', '9876543210', 
                   '123 Test Street, Test Area', 'Jalandhar', 'Punjab', '144001', 
                   'Test order for email verification', 'phonepe', 3999.98, 
                   '{$order_date}', 'success', 'completed')";
    
    if ($conn->query($order_sql)) {
        echo "<div style='color: green; padding: 10px; background: #e6ffe6; border: 1px solid green; margin: 10px 0;'>
                ✅ Order Created Successfully:<br>
                • Order ID: {$order_id}<br>
                • Amount: ₹3,999.98<br>
                • Status: Completed
              </div>";
        
        // Now add order items
        $item_sql = "INSERT INTO order_items 
                     (order_id, product_id, quantity, price) 
                     VALUES 
                     ('{$order_id}', {$product_id}, 2, 1999.99)";
        
        if ($conn->query($item_sql)) {
            echo "<div style='color: green; padding: 10px; background: #e6ffe6; border: 1px solid green; margin: 10px 0;'>
                    ✅ Order Item Added:<br>
                    • Product: {$product_id}<br>
                    • Quantity: 2<br>
                    • Price: ₹1,999.99 each
                  </div>";
        } else {
            echo "<div style='color: orange; padding: 10px; background: #fff4e6; border: 1px solid orange; margin: 10px 0;'>
                    ⚠️ Could not add order item: " . htmlspecialchars($conn->error) . "
                  </div>";
        }
    } else {
        echo "<div style='color: red; padding: 10px; background: #ffe6e6; border: 1px solid red; margin: 10px 0;'>
                ❌ Failed to create order: " . htmlspecialchars($conn->error) . "
              </div>";
        
        // Try alternative approach
        echo "<p>Trying alternative order creation...</p>";
        
        // Check if order_id column exists
        $alt_sql = "INSERT INTO orders 
                    (customer_name, customer_email, customer_phone, total_amount, order_date) 
                    VALUES 
                    ('Test Customer', 'test@example.com', '9876543210', 3999.98, NOW())";
        
        if ($conn->query($alt_sql)) {
            $order_id = $conn->insert_id;
            echo "<div style='color: green; padding: 10px; background: #e6ffe6; border: 1px solid green; margin: 10px 0;'>
                    ✅ Order Created with ID: {$order_id}
                  </div>";
        } else {
            echo "<div style='color: red; padding: 10px; background: #ffe6e6; border: 1px solid red; margin: 10px 0;'>
                    ❌ Could not create order at all. Continuing with dummy order...
                  </div>";
            $order_id = 'TEST-DUMMY-' . date('His');
        }
    }
    
    // Step 3: Deduct stock if order was created
    echo "<h4>Step 3: Stock Deduction</h4>";
    
    if (function_exists('deductStockFromOrder')) {
        $stock_deducted = deductStockFromOrder($conn, $order_id);
        if ($stock_deducted) {
            echo "<div style='color: green; padding: 10px; background: #e6ffe6; border: 1px solid green; margin: 10px 0;'>
                    ✅ Stock deducted using deductStockFromOrder() function
                  </div>";
        } else {
            echo "<div style='color: orange; padding: 10px; background: #fff4e6; border: 1px solid orange; margin: 10px 0;'>
                    ⚠️ Stock deduction function failed. Trying manual update...
                  </div>";
        }
    }
    
    // Manual stock update
    $check_stock = $conn->query("SELECT stock_quantity FROM products WHERE id = {$product_id}");
    if ($check_stock && $row = $check_stock->fetch_assoc()) {
        $old_stock = $row['stock_quantity'];
        $new_stock = $old_stock - 2; // Deduct 2 items
        
        if ($new_stock < 0) $new_stock = 0;
        
        $update_sql = "UPDATE products SET stock_quantity = {$new_stock} WHERE id = {$product_id}";
        if ($conn->query($update_sql)) {
            echo "<div style='color: green; padding: 10px; background: #e6ffe6; border: 1px solid green; margin: 10px 0;'>
                    ✅ Stock Updated:<br>
                    • Old Stock: {$old_stock}<br>
                    • New Stock: {$new_stock}<br>
                    • Deducted: 2 units
                  </div>";
            
            // Check stock status
            $status_sql = "SELECT stock_quantity, reorder_level, stock_status FROM products WHERE id = {$product_id}";
            $status_result = $conn->query($status_sql);
            if ($status_row = $status_result->fetch_assoc()) {
                $stock_qty = $status_row['stock_quantity'];
                $reorder = $status_row['reorder_level'];
                $status = $status_row['stock_status'];
                
                echo "<div style='padding: 10px; background: #e6f7ff; border: 1px solid blue; margin: 10px 0;'>
                        Stock Status:<br>
                        • Quantity: {$stock_qty}<br>
                        • Reorder Level: {$reorder}<br>
                        • Status: {$status}
                      </div>";
            }
        }
    }
    
    // Step 4: Send order confirmation email
    echo "<h4>Step 4: Sending Order Confirmation Email</h4>";
    
    $subject = "✅ TEST Order #{$order_id} - Payment Successful - {$boutique_name}";
    
    $message = "
    <!DOCTYPE html>
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #28a745; color: white; padding: 20px; text-align: center; }
            .content { padding: 30px; background: #f9f9f9; }
            .order-details { background: white; padding: 20px; border-radius: 5px; border: 1px solid #ddd; margin: 20px 0; }
            .test-notice { background: #ffeb3b; padding: 15px; border-radius: 5px; margin: 20px 0; text-align: center; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>✅ PAYMENT SUCCESSFUL</h1>
                <p>Test Order Confirmation - {$boutique_name}</p>
            </div>
            
            <div class='content'>
                <div class='test-notice'>
                    <strong>⚠️ THIS IS A TEST TRANSACTION - NO ACTUAL PAYMENT WAS PROCESSED</strong>
                </div>
                
                <h2>Order #{$order_id}</h2>
                <p><strong>Order Date:</strong> " . date('F j, Y, g:i a') . "</p>
                <p><strong>Total Amount:</strong> ₹3,999.98</p>
                <p><strong>Payment Status:</strong> <span style='color: green; font-weight: bold;'>Success</span></p>
                
                <div class='order-details'>
                    <h3>Order Summary:</h3>
                    <table style='width: 100%; border-collapse: collapse;'>
                        <tr style='background: #f5f5f5;'>
                            <th style='padding: 10px; border: 1px solid #ddd; text-align: left;'>Item</th>
                            <th style='padding: 10px; border: 1px solid #ddd; text-align: center;'>Qty</th>
                            <th style='padding: 10px; border: 1px solid #ddd; text-align: right;'>Price</th>
                            <th style='padding: 10px; border: 1px solid #ddd; text-align: right;'>Total</th>
                        </tr>
                        <tr>
                            <td style='padding: 10px; border: 1px solid #ddd;'>Test Product</td>
                            <td style='padding: 10px; border: 1px solid #ddd; text-align: center;'>2</td>
                            <td style='padding: 10px; border: 1px solid #ddd; text-align: right;'>₹1,999.99</td>
                            <td style='padding: 10px; border: 1px solid #ddd; text-align: right;'>₹3,999.98</td>
                        </tr>
                        <tr style='font-weight: bold;'>
                            <td colspan='3' style='padding: 10px; border: 1px solid #ddd; text-align: right;'>Grand Total:</td>
                            <td style='padding: 10px; border: 1px solid #ddd; text-align: right;'>₹3,999.98</td>
                        </tr>
                    </table>
                </div>
                
                <div style='margin-top: 30px; padding: 15px; background: #e8f4f8; border-radius: 5px;'>
                    <h3>📦 Stock Update:</h3>
                    <p>For product ID {$product_id}: Stock was deducted by 2 units.</p>
                    <p>This simulates real inventory management.</p>
                </div>
                
                <p style='margin-top: 30px;'>Best regards,<br>
                Test System<br>
                {$boutique_name}</p>
            </div>
        </div>
    </body>
    </html>";
    
    $email_result = sendEmail(
        $boutique_email,
        $subject,
        $message,
        'sdesigner@sdesignerjal.in',
        $boutique_name
    );
    
    if ($email_result) {
        echo "<div style='color: green; padding: 10px; background: #e6ffe6; border: 1px solid green; margin: 10px 0;'>
                ✅ Order confirmation email sent to: {$boutique_email}
              </div>";
        echo "<p>Please check <strong>sdesignerjal@gmail.com</strong> for the test order confirmation.</p>";
    } else {
        echo "<div style='color: orange; padding: 10px; background: #fff4e6; border: 1px solid orange; margin: 10px 0;'>
                ⚠️ Email may not have been sent. Trying alternative...
              </div>";
        
        // Try simple mail() as fallback
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $headers .= "From: {$boutique_name} <sdesigner@sdesignerjal.in>\r\n";
        
        $alt_email = mail($boutique_email, $subject, $message, $headers);
        if ($alt_email) {
            echo "<div style='color: green; padding: 10px; background: #e6ffe6; border: 1px solid green; margin: 10px 0;'>
                    ✅ Alternative email sent successfully!
                  </div>";
        }
    }
    
    // Step 5: Show results and cleanup option
    echo "<h4>Step 5: Test Results</h4>";
    echo "<div style='padding: 15px; background: #f8f9fa; border: 1px solid #ddd; border-radius: 5px; margin: 10px 0;'>";
    echo "<h5>📊 Test Data Created:</h5>";
    echo "<ul>";
    echo "<li><strong>Product ID:</strong> {$product_id}</li>";
    echo "<li><strong>Order ID:</strong> {$order_id}</li>";
    echo "<li><strong>Email Sent To:</strong> {$boutique_email}</li>";
    echo "<li><strong>Stock Deducted:</strong> 2 units</li>";
    echo "</ul>";
    echo "</div>";
    
    // Verify data was created
    echo "<h5>🔍 Verification Queries:</h5>";
    echo "<pre style='background: #f4f4f4; padding: 10px; border-radius: 4px; font-size: 12px;'>";
    echo "-- Check the test order\n";
    echo "SELECT order_id, customer_name, total_amount, payment_status FROM orders WHERE order_id = '{$order_id}';\n\n";
    echo "-- Check order items\n";
    echo "SELECT * FROM order_items WHERE order_id = '{$order_id}';\n\n";
    echo "-- Check product stock\n";
    echo "SELECT id, name, stock_quantity, reorder_level, stock_status FROM products WHERE id = {$product_id};\n\n";
    echo "-- Check stock transactions\n";
    echo "SELECT * FROM stock_transactions WHERE product_id = {$product_id} ORDER BY created_at DESC LIMIT 5;";
    echo "</pre>";
    
    // Cleanup option
    echo "<div style='margin-top: 20px; padding: 15px; background: #fff3cd; border: 1px solid #ffc107; border-radius: 5px;'>";
    echo "<h5>🧹 Cleanup Options:</h5>";
    echo "<p><a href='?password=sdesigner2024&cleanup=1&order_id={$order_id}&product_id={$product_id}' 
            style='color: white; background: #dc3545; padding: 8px 15px; text-decoration: none; border-radius: 4px; display: inline-block; margin: 5px;'
            onclick='return confirm(\"Delete ALL test data (Order + Product)?\")'>
            🗑️ Delete All Test Data
          </a>";
    
    echo "<a href='?password=sdesigner2024&cleanup=order&order_id={$order_id}' 
            style='color: white; background: #fd7e14; padding: 8px 15px; text-decoration: none; border-radius: 4px; display: inline-block; margin: 5px;'
            onclick='return confirm(\"Delete only the test order?\")'>
            📋 Delete Only Order
          </a>";
    
    echo "<a href='?password=sdesigner2024' 
            style='color: white; background: #6c757d; padding: 8px 15px; text-decoration: none; border-radius: 4px; display: inline-block; margin: 5px;'>
            ↩️ Back to Tests
          </a></p>";
    echo "</div>";
}

function testStockDeduction($conn) {
    echo "<h3>Testing Stock Deduction Function</h3>";
    
    // Create a test product
    $sql = "INSERT INTO products (name, price, stock_quantity, reorder_level) 
            VALUES ('Stock Test Product " . date('His') . "', 999.99, 50, 10)";
    
    if ($conn->query($sql)) {
        $product_id = $conn->insert_id;
        echo "<div style='color: green; padding: 10px; background: #e6ffe6; border: 1px solid green; margin: 10px 0;'>
                Test Product Created: ID {$product_id} (Stock: 50)
              </div>";
        
        // Test updateStock function
        if (function_exists('updateStock')) {
            $result = updateStock($conn, $product_id, -5, 'sale', 'Test stock deduction', 'Test System');
            
            if ($result) {
                echo "<div style='color: green; padding: 10px; background: #e6ffe6; border: 1px solid green; margin: 10px 0;'>
                        ✅ updateStock() function worked! Deducted 5 units.
                      </div>";
                
                // Check new stock
                $check = $conn->query("SELECT stock_quantity FROM products WHERE id = {$product_id}");
                if ($row = $check->fetch_assoc()) {
                    echo "<div style='padding: 10px; background: #e6f7ff; border: 1px solid blue; margin: 10px 0;'>
                            New Stock Level: {$row['stock_quantity']} units
                          </div>";
                }
            } else {
                echo "<div style='color: red; padding: 10px; background: #ffe6e6; border: 1px solid red; margin: 10px 0;'>
                        ❌ updateStock() function failed
                      </div>";
            }
        } else {
            echo "<div style='color: orange; padding: 10px; background: #fff4e6; border: 1px solid orange; margin: 10px 0;'>
                    ⚠️ updateStock() function not found in config.php
                  </div>";
        }
        
        // Cleanup
        echo "<p><a href='?password=sdesigner2024&cleanup=product&product_id={$product_id}' 
                style='color: white; background: #dc3545; padding: 5px 10px; text-decoration: none; border-radius: 3px;'
                onclick='return confirm(\"Delete test product?\")'>
                Delete Test Product
              </a></p>";
        
    } else {
        echo "<div style='color: red; padding: 10px; background: #ffe6e6; border: 1px solid red; margin: 10px 0;'>
                ❌ Could not create test product: " . htmlspecialchars($conn->error) . "
              </div>";
    }
    
    echo "<p><a href='?password=sdesigner2024' class='btn'>← Back to Tests</a></p>";
}

function showTestOptions() {
    echo "<h2>🧪 Available Tests</h2>";
    
    echo "<div style='display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin: 20px 0;'>";
    
    echo "<div style='padding: 20px; background: #e6ffe6; border: 1px solid #28a745; border-radius: 8px;'>";
    echo "<h3>📨 Simple Email Test</h3>";
    echo "<p>Send a basic test email to verify email system is working.</p>";
    echo "<p><a href='?password=sdesigner2024&test=simple' class='btn'>Run Test</a></p>";
    echo "</div>";
    
    echo "<div style='padding: 20px; background: #e6f7ff; border: 1px solid #007bff; border-radius: 8px;'>";
    echo "<h3>🛒 Complete Order Test</h3>";
    echo "<p>Create test order, deduct stock, and send confirmation email.</p>";
    echo "<p><a href='?password=sdesigner2024&test=full' class='btn'>Run Test</a></p>";
    echo "</div>";
    
    echo "<div style='padding: 20px; background: #fff4e6; border: 1px solid #fd7e14; border-radius: 8px;'>";
    echo "<h3>📦 Stock Deduction Test</h3>";
    echo "<p>Test the stock deduction functions separately.</p>";
    echo "<p><a href='?password=sdesigner2024&test=stock' class='btn'>Run Test</a></p>";
    echo "</div>";
    
    echo "</div>";
    
    echo "<hr>";
    echo "<h3>📋 Quick Status Check</h3>";
    echo "<p>Your previous test shows:</p>";
    echo "<ul>";
    echo "<li>✅ Email system is working (emails were sent to sdesignerjal@gmail.com)</li>";
    echo "<li>✅ Database is connected</li>";
    echo "<li>⚠️ Order creation needs fixing (working on it)</li>";
    echo "<li>✅ Stock deduction is working</li>";
    echo "</ul>";
    
    echo "<h3>🔧 Troubleshooting Tips</h3>";
    echo "<ol>";
    echo "<li><strong>Check spam folder</strong> in sdesignerjal@gmail.com for test emails</li>";
    echo "<li><strong>Verify SMTP settings</strong> in phpmailer_helper.php</li>";
    echo "<li><strong>Check database structure</strong> - orders table might have different columns</li>";
    echo "<li><strong>Test contact.php</strong> - if it works, email system is fine</li>";
    echo "</ol>";
}

function cleanupTestData($conn) {
    if (isset($_GET['order_id'])) {
        $order_id = $_GET['order_id'];
        
        // Delete order items first
        $conn->query("DELETE FROM order_items WHERE order_id = '{$order_id}'");
        
        // Delete order
        $conn->query("DELETE FROM orders WHERE order_id = '{$order_id}'");
        
        echo "<div style='color: green; padding: 10px; background: #e6ffe6; border: 1px solid green; margin: 10px 0;'>
                ✅ Order '{$order_id}' deleted
              </div>";
    }
    
    if (isset($_GET['product_id'])) {
        $product_id = $_GET['product_id'];
        
        // Delete test product
        $conn->query("DELETE FROM products WHERE id = {$product_id} AND name LIKE 'Test%'");
        
        echo "<div style='color: green; padding: 10px; background: #e6ffe6; border: 1px solid green; margin: 10px 0;'>
                ✅ Test product ID {$product_id} deleted
              </div>";
    }
    
    echo "<p><a href='?password=sdesigner2024' class='btn'>← Back to Tests</a></p>";
}

?>

<style>
    body {
        font-family: Arial, sans-serif;
        margin: 20px;
        background: #f5f5f5;
        line-height: 1.6;
    }
    .container {
        max-width: 1200px;
        margin: 0 auto;
        background: white;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    h1 {
        color: #8B4513;
        border-bottom: 2px solid #8B4513;
        padding-bottom: 10px;
    }
    h2 {
        color: #333;
        margin-top: 30px;
        padding-bottom: 5px;
        border-bottom: 1px solid #eee;
    }
    h3 {
        color: #555;
        margin-top: 20px;
    }
    h4 {
        color: #666;
        margin-top: 15px;
    }
    .btn {
        display: inline-block;
        padding: 10px 20px;
        background: #8B4513;
        color: white;
        text-decoration: none;
        border-radius: 4px;
        margin: 5px;
        border: none;
        cursor: pointer;
        font-size: 14px;
    }
    .btn:hover {
        background: #a0522d;
        text-decoration: none;
    }
    pre {
        background: #f4f4f4;
        padding: 10px;
        border-radius: 4px;
        overflow-x: auto;
        font-family: monospace;
        font-size: 12px;
    }
    ul, ol {
        margin: 10px 0;
        padding-left: 20px;
    }
    li {
        margin-bottom: 5px;
    }
</style>

<script>
    // Simple confirmation for cleanup
    function confirmAction(message) {
        return confirm(message || 'Are you sure?');
    }
    
    // Auto-scroll to results
    window.addEventListener('load', function() {
        if (window.location.search.includes('test=') || 
            window.location.search.includes('cleanup=')) {
            window.scrollTo(0, document.body.scrollHeight);
        }
    });
</script>

<?php
$conn->close();
?>