<?php
// phpmailer_helper.php - PHPMailer for Hostinger when mail() is disabled

// Check for PHPMailer in different possible locations
$phpmailer_paths = [
    __DIR__ . '/PHPMailer/src/',
    __DIR__ . '/PHPMailer/',
    __DIR__ . '/vendor/phpmailer/phpmailer/src/'
];

$phpmailer_loaded = false;

foreach ($phpmailer_paths as $path) {
    $exception_file = $path . 'Exception.php';
    $phpmailer_file = $path . 'PHPMailer.php';
    $smtp_file = $path . 'SMTP.php';
    
    if (file_exists($exception_file) && file_exists($phpmailer_file) && file_exists($smtp_file)) {
        require_once $exception_file;
        require_once $phpmailer_file;
        require_once $smtp_file;
        $phpmailer_loaded = true;
        error_log("PHPMailer loaded from: $path");
        break;
    }
}

if (!$phpmailer_loaded) {
    error_log("PHPMailer files not found. Checked paths: " . implode(', ', $phpmailer_paths));
    // Continue without PHPMailer - will fall back to mail()
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Send email using PHPMailer with Hostinger SMTP
 */
function sendEmailPHPMailer($to, $subject, $message, $from_email = null, $from_name = null) {
    // Check if PHPMailer classes are available
    if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        error_log("PHPMailer classes not available");
        return false;
    }
    
    try {
        // Default from address
        if (!$from_email) $from_email = 'sdesigner@sdesignerjal.in';
        if (!$from_name) $from_name = 'SDesigner Boutique';
        
        // Create PHPMailer instance
        $mail = new PHPMailer(true);
        
        // Server settings for Hostinger
        $mail->isSMTP();
        $mail->Host       = 'smtp.hostinger.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'sdesigner@sdesignerjal.in'; // Your Hostinger email
        $mail->Password   = '0mUO##a4Y'; // ⚠️ REPLACE WITH YOUR ACTUAL EMAIL PASSWORD
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Use STARTTLS
        $mail->Port       = 587;
        
        // Debug mode (only for testing)
        // $mail->SMTPDebug = 2; // Uncomment for debugging
        // $mail->Debugoutput = function($str, $level) {
        //     error_log("PHPMailer Debug level $level: $str");
        // };
        
        // Recipients
        $mail->setFrom($from_email, $from_name);
        $mail->addAddress($to);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $message;
        $mail->AltBody = strip_tags($message); // Plain text version
        
        // Add headers for better deliverability
        $mail->addCustomHeader('X-Priority', '1');
        $mail->addCustomHeader('X-MSMail-Priority', 'High');
        $mail->addCustomHeader('X-Mailer', 'PHPMailer ' . $mail::VERSION . ' (https://github.com/PHPMailer/PHPMailer)');
        
        // Send email
        if ($mail->send()) {
            error_log("SUCCESS: Email sent to $to using PHPMailer SMTP");
            return true;
        } else {
            error_log("FAILED: Email not sent to $to. PHPMailer Error: " . $mail->ErrorInfo);
            return false;
        }
        
    } catch (Exception $e) {
        error_log("PHPMailer Exception: " . $e->getMessage());
        return false;
    }
}

/**
 * Simple email function using PHP mail() as fallback
 */
function sendEmailSimple($to, $subject, $message, $from_email = null, $from_name = null) {
    try {
        if (!$from_email) $from_email = 'sdesigner@sdesignerjal.in';
        if (!$from_name) $from_name = 'SDesigner Boutique';
        
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $headers .= "From: " . $from_name . " <" . $from_email . ">\r\n";
        $headers .= "Reply-To: " . $from_email . "\r\n";
        $headers .= "Return-Path: " . $from_email . "\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
        $headers .= "X-Priority: 1\r\n";
        
        $result = mail($to, $subject, $message, $headers);
        
        if ($result) {
            error_log("SUCCESS: Email sent to $to using mail() function");
        } else {
            error_log("FAILED: Email not sent to $to using mail() function");
        }
        
        return $result;
        
    } catch (Exception $e) {
        error_log("Simple Email Exception: " . $e->getMessage());
        return false;
    }
}

/**
 * Universal email sender - tries PHPMailer first, then falls back to mail()
 */
function sendEmailUniversal($to, $subject, $message, $from_email = null, $from_name = null) {
    // Try PHPMailer first
    $phpmailer_result = sendEmailPHPMailer($to, $subject, $message, $from_email, $from_name);
    
    if ($phpmailer_result) {
        return true;
    }
    
    // Fallback to simple mail() function
    error_log("PHPMailer failed, trying mail() fallback...");
    return sendEmailSimple($to, $subject, $message, $from_email, $from_name);
}
?>