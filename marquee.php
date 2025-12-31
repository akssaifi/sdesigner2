<?php
// marquee.php - Marquee Message Management
require_once 'config.php';

// Check if functions.php exists and include it
if (file_exists('functions.php')) {
    require_once 'functions.php';
}

// Define the missing marquee functions if they don't exist
if (!function_exists('addMarqueeMessage')) {
    function addMarqueeMessage($conn, $data) {
        // Handle date fields - set to NULL if empty
        $start_date = (!empty($data['start_date']) && $data['start_date'] != '0000-00-00') ? $data['start_date'] : null;
        $end_date = (!empty($data['end_date']) && $data['end_date'] != '0000-00-00') ? $data['end_date'] : null;
        
        $sql = "INSERT INTO marquee_messages 
                (message_text, badge_text, badge_color, icon_class, icon_color, 
                 priority, display_order, is_active, start_date, end_date) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            error_log("SQL Error: " . $conn->error);
            return false;
        }
        
        $stmt->bind_param("sssssiiiiss", 
            $data['message_text'],
            $data['badge_text'],
            $data['badge_color'],
            $data['icon_class'],
            $data['icon_color'],
            $data['priority'],
            $data['display_order'],
            $data['is_active'],
            $start_date,
            $end_date
        );
        
        $result = $stmt->execute();
        if (!$result) {
            error_log("Execute Error: " . $stmt->error);
        }
        $stmt->close();
        return $result;
    }
}

if (!function_exists('updateMarqueeMessage')) {
    function updateMarqueeMessage($conn, $id, $data) {
        // Handle date fields - set to NULL if empty
        $start_date = (!empty($data['start_date']) && $data['start_date'] != '0000-00-00') ? $data['start_date'] : null;
        $end_date = (!empty($data['end_date']) && $data['end_date'] != '0000-00-00') ? $data['end_date'] : null;
        
        $sql = "UPDATE marquee_messages SET 
                message_text = ?,
                badge_text = ?,
                badge_color = ?,
                icon_class = ?,
                icon_color = ?,
                priority = ?,
                display_order = ?,
                is_active = ?,
                start_date = ?,
                end_date = ?
                WHERE id = ?";
        
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            error_log("SQL Error: " . $conn->error);
            return false;
        }
        
        $stmt->bind_param("sssssiiiissi", 
            $data['message_text'],
            $data['badge_text'],
            $data['badge_color'],
            $data['icon_class'],
            $data['icon_color'],
            $data['priority'],
            $data['display_order'],
            $data['is_active'],
            $start_date,
            $end_date,
            $id
        );
        
        $result = $stmt->execute();
        if (!$result) {
            error_log("Execute Error: " . $stmt->error);
        }
        $stmt->close();
        return $result;
    }
}

if (!function_exists('deleteMarqueeMessage')) {
    function deleteMarqueeMessage($conn, $id) {
        $sql = "DELETE FROM marquee_messages WHERE id = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            error_log("SQL Error: " . $conn->error);
            return false;
        }
        
        $stmt->bind_param("i", $id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }
}

if (!function_exists('toggleMarqueeStatus')) {
    function toggleMarqueeStatus($conn, $id) {
        // Get current status
        $sql = "SELECT is_active FROM marquee_messages WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        
        $new_status = $row['is_active'] ? 0 : 1;
        
        $sql = "UPDATE marquee_messages SET is_active = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $new_status, $id);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }
}

if (!function_exists('getActiveMarqueeCount')) {
    function getActiveMarqueeCount($conn) {
        $sql = "SELECT COUNT(*) as count FROM marquee_messages WHERE is_active = 1 
                AND (start_date IS NULL OR start_date <= CURDATE()) 
                AND (end_date IS NULL OR end_date >= CURDATE() OR end_date = '0000-00-00')";
        $result = $conn->query($sql);
        if (!$result) {
            error_log("SQL Error: " . $conn->error);
            return 0;
        }
        $row = $result->fetch_assoc();
        return $row['count'];
    }
}

if (!function_exists('getMarqueeMessageById')) {
    function getMarqueeMessageById($conn, $id) {
        $sql = "SELECT * FROM marquee_messages WHERE id = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            error_log("SQL Error: " . $conn->error);
            return null;
        }
        
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row;
    }
}

// Also ensure getMarqueeMessages exists (should be in functions.php)
if (!function_exists('getMarqueeMessages')) {
    function getMarqueeMessages($conn, $active_only = false) {
        $sql = "SELECT * FROM marquee_messages";
        
        if ($active_only) {
            $sql .= " WHERE is_active = 1 
                     AND (start_date IS NULL OR start_date <= CURDATE()) 
                     AND (end_date IS NULL OR end_date >= CURDATE() OR end_date = '0000-00-00')";
        }
        
        $sql .= " ORDER BY display_order, priority DESC, created_at DESC";
        
        $result = $conn->query($sql);
        if (!$result) {
            error_log("SQL Error: " . $conn->error);
            return [];
        }
        
        $messages = [];
        while ($row = $result->fetch_assoc()) {
            $messages[] = $row;
        }
        return $messages;
    }
}

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Handle form submissions
$message = '';
$success = false;

// Debug: Check if form is being submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log("POST Data: " . print_r($_POST, true));
    
    // Add new message
    if (isset($_POST['action']) && $_POST['action'] === 'add') {
        error_log("Processing ADD action");
        
        // Handle dates - convert empty strings to null
        $start_date = !empty($_POST['start_date']) ? $_POST['start_date'] : null;
        $end_date = !empty($_POST['end_date']) ? $_POST['end_date'] : null;
        
        $data = [
            'message_text' => trim($_POST['message_text']),
            'badge_text' => !empty($_POST['badge_text']) ? trim($_POST['badge_text']) : '',
            'badge_color' => $_POST['badge_color'],
            'icon_class' => !empty($_POST['icon_class']) ? trim($_POST['icon_class']) : 'fas fa-bullhorn',
            'icon_color' => $_POST['icon_color'],
            'priority' => intval($_POST['priority']),
            'display_order' => intval($_POST['display_order']),
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
            'start_date' => $start_date,
            'end_date' => $end_date
        ];
        
        error_log("Data to insert: " . print_r($data, true));
        
        if (addMarqueeMessage($conn, $data)) {
            $message = "Marquee message added successfully!";
            $success = true;
        } else {
            $message = "Error adding marquee message. Check error logs.";
        }
    }
    
    // Update message
    if (isset($_POST['action']) && $_POST['action'] === 'update') {
        error_log("Processing UPDATE action");
        
        $id = intval($_POST['id']);
        // Handle dates - convert empty strings to null
        $start_date = !empty($_POST['start_date']) ? $_POST['start_date'] : null;
        $end_date = !empty($_POST['end_date']) ? $_POST['end_date'] : null;
        
        $data = [
            'message_text' => trim($_POST['message_text']),
            'badge_text' => !empty($_POST['badge_text']) ? trim($_POST['badge_text']) : '',
            'badge_color' => $_POST['badge_color'],
            'icon_class' => !empty($_POST['icon_class']) ? trim($_POST['icon_class']) : 'fas fa-bullhorn',
            'icon_color' => $_POST['icon_color'],
            'priority' => intval($_POST['priority']),
            'display_order' => intval($_POST['display_order']),
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
            'start_date' => $start_date,
            'end_date' => $end_date
        ];
        
        if (updateMarqueeMessage($conn, $id, $data)) {
            $message = "Marquee message updated successfully!";
            $success = true;
        } else {
            $message = "Error updating marquee message. Check error logs.";
        }
    }
    
    // Delete message
    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        $id = intval($_POST['id']);
        if (deleteMarqueeMessage($conn, $id)) {
            $message = "Marquee message deleted successfully!";
            $success = true;
        } else {
            $message = "Error deleting marquee message.";
        }
    }
    
    // Toggle status
    if (isset($_POST['action']) && $_POST['action'] === 'toggle') {
        $id = intval($_POST['id']);
        if (toggleMarqueeStatus($conn, $id)) {
            $message = "Marquee message status updated!";
            $success = true;
        } else {
            $message = "Error updating marquee message status.";
        }
    }
}

// Get all marquee messages
$marquee_messages = getMarqueeMessages($conn, false);

// Get settings
$marquee_speed = getSetting($conn, 'marquee_speed') ?? 30;
$marquee_enabled = getSetting($conn, 'marquee_enabled') ?? 1;

// Handle settings update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_settings'])) {
    updateSetting($conn, 'marquee_speed', $_POST['marquee_speed']);
    updateSetting($conn, 'marquee_enabled', isset($_POST['marquee_enabled']) ? 1 : 0);
    $marquee_speed = $_POST['marquee_speed'];
    $marquee_enabled = isset($_POST['marquee_enabled']) ? 1 : 0;
    $message = "Settings updated successfully!";
    $success = true;
}

// Get edit message if requested
$edit_message = null;
if (isset($_GET['edit'])) {
    $edit_message = getMarqueeMessageById($conn, intval($_GET['edit']));
    
    // Format dates for display in form
    if ($edit_message) {
        // Check for invalid dates (0000-00-00) and convert to empty string
        $edit_message['start_date'] = ($edit_message['start_date'] && $edit_message['start_date'] != '0000-00-00') 
            ? $edit_message['start_date'] 
            : '';
        $edit_message['end_date'] = ($edit_message['end_date'] && $edit_message['end_date'] != '0000-00-00') 
            ? $edit_message['end_date'] 
            : '';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Marquee Messages - <?php echo getSetting($conn, 'boutique_name'); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #8B4513;
            --primary-light: #a0522d;
            --secondary: #D4A76A;
            --dark: #2C1810;
            --light: #FAF3E0;
            --gray-200: #E5E7EB;
            --gray-300: #D1D5DB;
            --gray-600: #4B5563;
            --gray-700: #374151;
            --transition: all 0.3s ease;
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            --shadow-md: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            --radius: 0.5rem;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--light);
            color: var(--gray-700);
            line-height: 1.6;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .header {
            background: white;
            padding: 1.5rem 2rem;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h1 {
            color: var(--dark);
            font-size: 1.8rem;
            font-weight: 700;
        }

        .header-buttons {
            display: flex;
            gap: 1rem;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border-radius: var(--radius);
            font-weight: 500;
            text-decoration: none;
            transition: var(--transition);
            border: none;
            cursor: pointer;
            font-size: 0.95rem;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background: var(--primary-light);
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .btn-secondary {
            background: var(--secondary);
            color: var(--dark);
        }

        .btn-secondary:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }

        .btn-danger {
            background: #DC2626;
            color: white;
        }

        .btn-danger:hover {
            background: #B91C1C;
            transform: translateY(-2px);
        }

        .btn-sm {
            padding: 0.4rem 0.8rem;
            font-size: 0.85rem;
        }

        .alert {
            padding: 1rem 1.5rem;
            border-radius: var(--radius);
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .alert-success {
            background: #D1FAE5;
            color: #065F46;
            border: 1px solid #A7F3D0;
        }

        .alert-error {
            background: #FEE2E2;
            color: #991B1B;
            border: 1px solid #FECACA;
        }

        .card {
            background: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            margin-bottom: 2rem;
            overflow: hidden;
        }

        .card-header {
            padding: 1.5rem 2rem;
            background: var(--dark);
            color: white;
            font-weight: 600;
            font-size: 1.2rem;
        }

        .card-body {
            padding: 2rem;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--gray-700);
        }

        .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid var(--gray-300);
            border-radius: var(--radius);
            font-family: 'Inter', sans-serif;
            font-size: 0.95rem;
            transition: var(--transition);
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(139, 69, 19, 0.1);
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .checkbox-group input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: var(--primary);
        }

        .select-wrapper {
            position: relative;
        }

        .select-wrapper select {
            appearance: none;
            padding-right: 2.5rem;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3E%3Cpath stroke='%236B7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3E%3C/svg%3E");
            background-position: right 0.75rem center;
            background-repeat: no-repeat;
            background-size: 1.5em 1.5em;
        }

        .table-container {
            overflow-x: auto;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th {
            background: var(--gray-200);
            padding: 1rem 1.5rem;
            text-align: left;
            font-weight: 600;
            color: var(--gray-700);
            white-space: nowrap;
        }

        .table td {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid var(--gray-200);
            vertical-align: middle;
        }

        .table tr:hover {
            background: #f9fafb;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
        }

        .status-active {
            background: #D1FAE5;
            color: #065F46;
        }

        .status-inactive {
            background: #FEE2E2;
            color: #991B1B;
        }

        .priority-badge {
            display: inline-block;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            text-align: center;
            line-height: 24px;
            font-weight: 600;
            font-size: 0.85rem;
        }

        .priority-high {
            background: #FEE2E2;
            color: #DC2626;
        }

        .priority-medium {
            background: #FEF3C7;
            color: #92400E;
        }

        .priority-low {
            background: #D1FAE5;
            color: #065F46;
        }

        .icon-preview {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 1.2rem;
        }

        .action-buttons {
            display: flex;
            gap: 0.5rem;
            flex-wrap: nowrap;
        }

        .preview-section {
            background: var(--dark);
            color: white;
            padding: 1rem;
            border-radius: var(--radius);
            margin-top: 1rem;
            overflow: hidden;
            position: relative;
        }

        .preview-title {
            font-size: 0.9rem;
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 0.5rem;
        }

        .marquee-preview {
            display: flex;
            animation: marquee-preview calc(<?php echo $marquee_speed; ?>s) linear infinite;
            white-space: nowrap;
        }

        @keyframes marquee-preview {
            0% { transform: translateX(0); }
            100% { transform: translateX(-50%); }
        }

        .preview-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0 2rem;
            font-size: 0.9rem;
        }

        .preview-badge {
            padding: 0.2rem 0.6rem;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .back-link {
            color: var(--primary);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
        }

        .back-link:hover {
            color: var(--primary-light);
        }

        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }
            
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .action-buttons {
                flex-direction: column;
            }
            
            .table th, .table td {
                padding: 0.75rem 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="index.php" class="back-link">
            <i class="fas fa-arrow-left"></i>
            Back to Home
        </a>
        
        <div class="header">
            <h1>Manage Marquee Messages</h1>
            <div class="header-buttons">
                <a href="?preview" class="btn btn-secondary">
                    <i class="fas fa-eye"></i> Preview
                </a>
                <a href="marquee.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add New
                </a>
            </div>
        </div>

        <?php if ($message): ?>
            <div class="alert <?php echo $success ? 'alert-success' : 'alert-error'; ?>">
                <span><?php echo $message; ?></span>
                <button onclick="this.parentElement.style.display='none'">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['preview'])): ?>
            <!-- Preview Section -->
            <div class="card">
                <div class="card-header">
                    Marquee Preview
                </div>
                <div class="card-body">
                    <div class="preview-section">
                        <div class="preview-title">Live Preview:</div>
                        <div class="marquee-preview">
                            <?php 
                            $active_messages = getMarqueeMessages($conn, true);
                            foreach ($active_messages as $msg): 
                            ?>
                            <div class="preview-item">
                                <?php if ($msg['badge_text']): ?>
                                    <span class="preview-badge" style="background: var(--<?php echo $msg['badge_color']; ?>); color: var(--dark);">
                                        <?php echo htmlspecialchars($msg['badge_text']); ?>
                                    </span>
                                <?php endif; ?>
                                <i class="<?php echo $msg['icon_class']; ?>" style="color: var(--<?php echo $msg['icon_color']; ?>);"></i>
                                <span><?php echo htmlspecialchars($msg['message_text']); ?></span>
                            </div>
                            <?php endforeach; ?>
                            <!-- Duplicate for seamless loop -->
                            <?php foreach ($active_messages as $msg): ?>
                            <div class="preview-item">
                                <?php if ($msg['badge_text']): ?>
                                    <span class="preview-badge" style="background: var(--<?php echo $msg['badge_color']; ?>); color: var(--dark);">
                                        <?php echo htmlspecialchars($msg['badge_text']); ?>
                                    </span>
                                <?php endif; ?>
                                <i class="<?php echo $msg['icon_class']; ?>" style="color: var(--<?php echo $msg['icon_color']; ?>);"></i>
                                <span><?php echo htmlspecialchars($msg['message_text']); ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div style="margin-top: 2rem;">
                        <a href="marquee.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Management
                        </a>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <!-- Add/Edit Form -->
            <div class="card">
                <div class="card-header">
                    <?php echo $edit_message ? 'Edit Marquee Message' : 'Add New Marquee Message'; ?>
                </div>
                <div class="card-body">
                    <form method="POST" action="marquee.php" id="marqueeForm">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="message_text">Message Text *</label>
                                <input type="text" id="message_text" name="message_text" 
                                       class="form-control" required
                                       value="<?php echo $edit_message ? htmlspecialchars($edit_message['message_text']) : ''; ?>"
                                       placeholder="Enter marquee message">
                            </div>

                            <div class="form-group">
                                <label for="badge_text">Badge Text (Optional)</label>
                                <input type="text" id="badge_text" name="badge_text" 
                                       class="form-control"
                                       value="<?php echo $edit_message ? htmlspecialchars($edit_message['badge_text']) : ''; ?>"
                                       placeholder="e.g., NEW, SALE, etc.">
                            </div>

                            <div class="form-group">
                                <label for="badge_color">Badge Color</label>
                                <div class="select-wrapper">
                                    <select id="badge_color" name="badge_color" class="form-control">
                                        <option value="secondary" <?php echo ($edit_message && $edit_message['badge_color'] == 'secondary') ? 'selected' : ''; ?>>Secondary (Gold)</option>
                                        <option value="primary" <?php echo ($edit_message && $edit_message['badge_color'] == 'primary') ? 'selected' : ''; ?>>Primary (Brown)</option>
                                        <option value="accent" <?php echo ($edit_message && $edit_message['badge_color'] == 'accent') ? 'selected' : ''; ?>>Accent (Light Brown)</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="icon_class">Icon Class</label>
                                <input type="text" id="icon_class" name="icon_class" 
                                       class="form-control"
                                       value="<?php echo $edit_message ? htmlspecialchars($edit_message['icon_class']) : 'fas fa-bullhorn'; ?>"
                                       placeholder="e.g., fas fa-fire, fas fa-gift">
                                <small>Use Font Awesome icon classes</small>
                            </div>

                            <div class="form-group">
                                <label for="icon_color">Icon Color</label>
                                <div class="select-wrapper">
                                    <select id="icon_color" name="icon_color" class="form-control">
                                        <option value="secondary" <?php echo ($edit_message && $edit_message['icon_color'] == 'secondary') ? 'selected' : ''; ?>>Secondary (Gold)</option>
                                        <option value="primary" <?php echo ($edit_message && $edit_message['icon_color'] == 'primary') ? 'selected' : ''; ?>>Primary (Brown)</option>
                                        <option value="accent" <?php echo ($edit_message && $edit_message['icon_color'] == 'accent') ? 'selected' : ''; ?>>Accent (Light Brown)</option>
                                        <option value="white" <?php echo ($edit_message && $edit_message['icon_color'] == 'white') ? 'selected' : ''; ?>>White</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="priority">Priority</label>
                                <div class="select-wrapper">
                                    <select id="priority" name="priority" class="form-control">
                                        <option value="1" <?php echo ($edit_message && $edit_message['priority'] == 1) ? 'selected' : ''; ?>>Low</option>
                                        <option value="2" <?php echo ($edit_message && $edit_message['priority'] == 2) ? 'selected' : ''; ?>>Medium</option>
                                        <option value="3" <?php echo ($edit_message && $edit_message['priority'] == 3) ? 'selected' : ''; ?>>High</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="display_order">Display Order</label>
                                <input type="number" id="display_order" name="display_order" 
                                       class="form-control" min="0"
                                       value="<?php echo $edit_message ? $edit_message['display_order'] : 0; ?>">
                            </div>

                            <div class="form-group">
                                <label for="start_date">Start Date (Optional)</label>
                                <input type="date" id="start_date" name="start_date" 
                                       class="form-control"
                                       value="<?php echo $edit_message ? $edit_message['start_date'] : ''; ?>">
                            </div>

                            <div class="form-group">
                                <label for="end_date">End Date (Optional)</label>
                                <input type="date" id="end_date" name="end_date" 
                                       class="form-control"
                                       value="<?php echo $edit_message ? $edit_message['end_date'] : ''; ?>">
                            </div>
                        </div>

                        <div class="form-group checkbox-group">
                            <input type="checkbox" id="is_active" name="is_active" 
                                   value="1" <?php echo ($edit_message && $edit_message['is_active']) ? 'checked' : (!isset($edit_message) ? 'checked' : ''); ?>>
                            <label for="is_active">Active</label>
                        </div>

                        <div class="form-group" style="margin-top: 2rem;">
                            <?php if ($edit_message): ?>
                                <input type="hidden" name="action" value="update">
                                <input type="hidden" name="id" value="<?php echo $edit_message['id']; ?>">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Update Message
                                </button>
                                <a href="marquee.php" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Cancel
                                </a>
                            <?php else: ?>
                                <input type="hidden" name="action" value="add">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Add Message
                                </button>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Settings Form -->
            <div class="card">
                <div class="card-header">
                    Marquee Settings
                </div>
                <div class="card-body">
                    <form method="POST" action="marquee.php">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="marquee_speed">Marquee Speed (seconds)</label>
                                <input type="number" id="marquee_speed" name="marquee_speed" 
                                       class="form-control" min="10" max="60" step="1"
                                       value="<?php echo $marquee_speed; ?>">
                                <small>Lower = faster, Higher = slower</small>
                            </div>
                        </div>

                        <div class="form-group checkbox-group">
                            <input type="checkbox" id="marquee_enabled" name="marquee_enabled" 
                                   value="1" <?php echo $marquee_enabled ? 'checked' : ''; ?>>
                            <label for="marquee_enabled">Enable Marquee</label>
                        </div>

                        <div class="form-group" style="margin-top: 2rem;">
                            <input type="hidden" name="update_settings" value="1">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Messages List -->
            <div class="card">
                <div class="card-header">
                    All Marquee Messages
                    <span style="float: right; font-size: 0.9rem;">
                        Active: <?php echo getActiveMarqueeCount($conn); ?> messages
                    </span>
                </div>
                <div class="card-body">
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Message</th>
                                    <th>Badge</th>
                                    <th>Icon</th>
                                    <th>Priority</th>
                                    <th>Order</th>
                                    <th>Status</th>
                                    <th>Dates</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($marquee_messages)): ?>
                                    <tr>
                                        <td colspan="9" style="text-align: center; padding: 3rem;">
                                            No marquee messages found. Add your first message above!
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($marquee_messages as $msg): 
                                        // Format dates for display
                                        $start_date_display = ($msg['start_date'] && $msg['start_date'] != '0000-00-00') 
                                            ? date('Y-m-d', strtotime($msg['start_date'])) 
                                            : 'No start';
                                        $end_date_display = ($msg['end_date'] && $msg['end_date'] != '0000-00-00') 
                                            ? date('Y-m-d', strtotime($msg['end_date'])) 
                                            : 'No end';
                                    ?>
                                    <tr>
                                        <td><?php echo $msg['id']; ?></td>
                                        <td style="max-width: 300px; word-wrap: break-word;">
                                            <?php echo htmlspecialchars($msg['message_text']); ?>
                                        </td>
                                        <td>
                                            <?php if ($msg['badge_text']): ?>
                                                <span class="status-badge" style="background: var(--<?php echo $msg['badge_color']; ?>); color: var(--dark);">
                                                    <?php echo htmlspecialchars($msg['badge_text']); ?>
                                                </span>
                                            <?php else: ?>
                                                <span style="color: var(--gray-600); font-size: 0.85rem;">None</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="icon-preview">
                                                <i class="<?php echo $msg['icon_class']; ?>" style="color: var(--<?php echo $msg['icon_color']; ?>);"></i>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="priority-badge priority-<?php 
                                                echo $msg['priority'] == 3 ? 'high' : ($msg['priority'] == 2 ? 'medium' : 'low'); 
                                            ?>">
                                                <?php echo $msg['priority']; ?>
                                            </span>
                                        </td>
                                        <td><?php echo $msg['display_order']; ?></td>
                                        <td>
                                            <span class="status-badge <?php echo $msg['is_active'] ? 'status-active' : 'status-inactive'; ?>">
                                                <?php echo $msg['is_active'] ? 'Active' : 'Inactive'; ?>
                                            </span>
                                        </td>
                                        <td style="font-size: 0.85rem;">
                                            <?php if ($start_date_display != 'No start' || $end_date_display != 'No end'): ?>
                                                <?php echo $start_date_display; ?> 
                                                <br>to<br>
                                                <?php echo $end_date_display; ?>
                                            <?php else: ?>
                                                Always
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <a href="?edit=<?php echo $msg['id']; ?>" class="btn btn-secondary btn-sm">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form method="POST" action="marquee.php" style="display: inline;">
                                                    <input type="hidden" name="action" value="toggle">
                                                    <input type="hidden" name="id" value="<?php echo $msg['id']; ?>">
                                                    <button type="submit" class="btn btn-secondary btn-sm">
                                                        <i class="fas fa-power-off"></i>
                                                    </button>
                                                </form>
                                                <form method="POST" action="marquee.php" style="display: inline;" 
                                                      onsubmit="return confirm('Delete this marquee message?');">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="id" value="<?php echo $msg['id']; ?>">
                                                    <button type="submit" class="btn btn-danger btn-sm">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Form validation
        document.getElementById('marqueeForm').addEventListener('submit', function(e) {
            const messageText = document.getElementById('message_text').value.trim();
            if (!messageText) {
                alert('Please enter a message text.');
                e.preventDefault();
                return false;
            }
            return true;
        });
    </script>
</body>
</html>
<?php
$conn->close();
?>