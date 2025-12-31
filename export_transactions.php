<?php
require_once 'config.php';

// Authentication check
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Get stock transactions for export
$transactions = getStockTransactions($conn, 1000);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock Transactions Export</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header h1 {
            margin: 0;
            color: #333;
        }
        .header .date {
            color: #666;
            margin-top: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th {
            background-color: #f2f2f2;
            padding: 10px;
            text-align: left;
            border: 1px solid #ddd;
            font-weight: bold;
        }
        td {
            padding: 8px 10px;
            border: 1px solid #ddd;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .positive {
            color: green;
            font-weight: bold;
        }
        .negative {
            color: red;
            font-weight: bold;
        }
        .no-print {
            text-align: center;
            margin-top: 30px;
            padding: 20px;
        }
        .print-btn {
            display: inline-block;
            background-color: #8B4513;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            cursor: pointer;
            border: none;
            font-size: 16px;
        }
        .print-btn:hover {
            background-color: #a0522d;
        }
        @media print {
            .no-print {
                display: none;
            }
            .print-btn {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Stock Transactions Report</h1>
        <div class="date">Generated on: <?php echo date('F j, Y h:i A'); ?></div>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>Date & Time</th>
                <th>Product</th>
                <th>Transaction</th>
                <th>Quantity</th>
                <th>Previous</th>
                <th>New</th>
                <th>Performed By</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($transactions as $trans): ?>
            <tr>
                <td><?php echo date('M d, Y h:i A', strtotime($trans['created_at'])); ?></td>
                <td><?php echo htmlspecialchars($trans['product_name']); ?></td>
                <td><?php echo ucfirst(str_replace('_', ' ', $trans['transaction_type'])); ?></td>
                <td class="<?php echo $trans['quantity'] > 0 ? 'positive' : 'negative'; ?>">
                    <?php echo ($trans['quantity'] > 0 ? '+' : '') . $trans['quantity']; ?>
                </td>
                <td><?php echo $trans['previous_quantity']; ?></td>
                <td><?php echo $trans['new_quantity']; ?></td>
                <td><?php echo htmlspecialchars($trans['performed_by']); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <div class="no-print">
        <button onclick="window.print()" class="print-btn">
            <i class="fas fa-print"></i> Print Report
        </button>
        <p style="margin-top: 10px; color: #666;">Click the button above to print this report, or use Ctrl+P</p>
    </div>
    
    <script>
        // Auto-trigger print dialog when page loads
        window.onload = function() {
            // Uncomment the line below if you want auto-print
            // window.print();
        };
    </script>
</body>
</html>