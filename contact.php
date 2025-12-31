<?php
// contact.php - Contact page with working email
session_start();
require_once 'config.php';

// Get boutique information
$boutique_name = getSetting($conn, 'boutique_name') ?? 'SDesigner Boutique';
$designer_name = getSetting($conn, 'designer_name') ?? 'Dinky Ahuja';
$location = getSetting($conn, 'location') ?? 'Jalandhar, Punjab';
$primary_phone = getSetting($conn, 'primary_phone') ?? '89686-36373';
$secondary_phone = getSetting($conn, 'secondary_phone') ?? '9814927250';
$instagram_url = getSetting($conn, 'instagram_url') ?? '#';
$facebook_url = getSetting($conn, 'facebook_url') ?? '#';
$show_social_links = getSetting($conn, 'show_social_links') ?? '1';
$boutique_email = getSetting($conn, 'email') ?? 'sdesignerjal@gmail.com';

// Get marquee settings and messages
$marquee_enabled = getSetting($conn, 'marquee_enabled') ?? 1;
$marquee_speed = getSetting($conn, 'marquee_speed') ?? 30;
$marquee_messages = $marquee_enabled ? getMarqueeMessages($conn, true) : [];

// Handle form submission
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    
    // Validate
    $errors = [];
    if (empty($name)) $errors[] = 'Name is required';
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required';
    if (empty($subject)) $errors[] = 'Subject is required';
    if (empty($message)) $errors[] = 'Message is required';
    
    if (empty($errors)) {
        try {
            // Prepare form data
            $form_data = [
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'subject' => $subject,
                'message' => $message
            ];
            
            // Generate customer email
            $customer_subject = "Thank you for contacting $boutique_name";
            $customer_message = "
            <!DOCTYPE html>
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background: #8B4513; color: white; padding: 20px; text-align: center; }
                    .content { padding: 30px; background: #f9f9f9; }
                    .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h1>$boutique_name</h1>
                        <p>Message Received</p>
                    </div>
                    
                    <div class='content'>
                        <h2>Thank you for contacting us, " . htmlspecialchars($name) . "!</h2>
                        <p>We have received your message and will get back to you within 24 hours.</p>
                        
                        <h3>Your Message Details:</h3>
                        <p><strong>Subject:</strong> " . htmlspecialchars($subject) . "</p>
                        <p><strong>Message:</strong> " . nl2br(htmlspecialchars($message)) . "</p>
                        
                        <h3>Our Contact Information:</h3>
                        <p><strong>Designer:</strong> $designer_name</p>
                        <p><strong>Phone:</strong> $primary_phone</p>
                        <p><strong>Email:</strong> $boutique_email</p>
                        
                        <p>Best regards,<br>The $boutique_name Team</p>
                    </div>
                    
                    <div class='footer'>
                        <p>$boutique_name | $location</p>
                        <p>© " . date('Y') . " $boutique_name. All rights reserved.</p>
                    </div>
                </div>
            </body>
            </html>";
            
            // Send to customer
            $customer_sent = sendEmail($email, $customer_subject, $customer_message);
            
            // Generate admin email
            $admin_subject = "📧 New Contact Form: " . htmlspecialchars($subject);
            $admin_message = "
            <!DOCTYPE html>
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background: #dc3545; color: white; padding: 20px; text-align: center; }
                    .content { padding: 30px; background: #f9f9f9; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h1>📧 NEW CONTACT FORM SUBMISSION</h1>
                        <p>$boutique_name</p>
                    </div>
                    
                    <div class='content'>
                        <h2>Contact Form Details:</h2>
                        
                        <p><strong>Name:</strong> " . htmlspecialchars($name) . "</p>
                        <p><strong>Email:</strong> " . htmlspecialchars($email) . "</p>
                        <p><strong>Phone:</strong> " . htmlspecialchars($phone) . "</p>
                        <p><strong>Subject:</strong> " . htmlspecialchars($subject) . "</p>
                        <p><strong>Message:</strong></p>
                        <div style='background: white; padding: 15px; border-radius: 5px; border: 1px solid #ddd;'>
                            " . nl2br(htmlspecialchars($message)) . "
                        </div>
                        
                        <p><strong>Time:</strong> " . date('Y-m-d H:i:s') . "</p>
                        
                        <div style='margin-top: 30px; padding: 15px; background: #e8f4f8; border-radius: 5px;'>
                            <p><strong>Quick Actions:</strong></p>
                            <p><a href='mailto:" . htmlspecialchars($email) . "'>Reply via Email</a> | 
                            <a href='tel:$primary_phone'>Call Customer</a></p>
                        </div>
                    </div>
                </div>
            </body>
            </html>";
            
            // Send to admin
            $admin_sent = sendEmail($boutique_email, $admin_subject, $admin_message);
            
            if ($admin_sent) {
                // Log the submission
                error_log("Contact form submitted: " . $name . " (" . $email . ") - " . $subject);
                
                $success_message = "Thank you! Your message has been sent successfully. We'll get back to you within 24 hours.";
                
                // Clear form data
                $_POST = [];
            } else {
                $error_message = "There was an error sending your message. Please try again or call us directly at " . htmlspecialchars($primary_phone);
                error_log("Email sending failed for: " . $email);
            }
        } catch (Exception $e) {
            $error_message = "There was an error processing your request. Please try again later.";
            error_log("Contact form error: " . $e->getMessage());
        }
    } else {
        $error_message = "Please fix the following errors:<br>" . implode('<br>', $errors);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include 'header.php'; ?>
    <title>Contact Us | <?php echo htmlspecialchars($boutique_name); ?></title>
    <style>
        /* Contact Section Styles */
        .contact-section {
            padding: 4rem 0;
            background: linear-gradient(135deg, #fdfcfb 0%, #f5f3f0 100%);
            min-height: 100vh;
        }

        .contact-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
        }

        .page-title {
            font-size: 2.8rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 1rem;
            font-family: 'Playfair Display', serif;
            text-align: center;
            position: relative;
            padding-bottom: 1rem;
        }

        .page-title:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 3px;
            background: linear-gradient(135deg, #8B4513 0%, #D2691E 100%);
            border-radius: 2px;
        }

        .page-subtitle {
            font-size: 1.3rem;
            color: var(--gray-600);
            text-align: center;
            max-width: 700px;
            margin: 0 auto 4rem;
            line-height: 1.6;
        }

        .contact-layout {
            display: grid;
            grid-template-columns: 1fr;
            gap: 3rem;
            margin-bottom: 4rem;
        }

        @media (min-width: 992px) {
            .contact-layout {
                grid-template-columns: 1fr 1fr;
            }
        }

        /* Contact Info */
        .contact-info {
            padding: 2.5rem;
           
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(139, 69, 19, 0.08);
         
            position: relative;
            overflow: hidden;
        }

        .contact-info:before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 5px;
            height: 100%;
            background: linear-gradient(to bottom, #8B4513 0%, #D2691E 100%);
        }

        .info-title {
            font-size: 1.6rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid rgba(139, 69, 19, 0.2);
            position: relative;
        }

        .info-title:after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 60px;
            height: 2px;
            background: linear-gradient(135deg, #8B4513 0%, #D2691E 100%);
        }

        .info-item {
            display: flex;
            align-items: flex-start;
            gap: 1.2rem;
            margin-bottom: 1.8rem;
            padding-bottom: 1.8rem;
            border-bottom: 1px solid rgba(139, 69, 19, 0.1);
            transition: transform 0.3s ease;
        }

        .info-item:hover {
            transform: translateX(5px);
        }

        .info-item:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .info-icon {
            width: 56px;
            height: 56px;
            background: linear-gradient(135deg, #8B4513 0%, #D2691E 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(139, 69, 19, 0.2);
        }

        .info-item:hover .info-icon {
            transform: scale(1.1) rotate(5deg);
            box-shadow: 0 6px 20px rgba(139, 69, 19, 0.3);
        }

        .info-icon i {
            font-size: 1.4rem;
            color: white;
        }

        .info-content h3 {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 0.5rem;
        }

        .info-content p {
            color: #666;
            line-height: 1.6;
            margin: 0.2rem 0;
        }

        .info-content a {
            color: #8B4513;
            text-decoration: none;
            transition: color 0.3s;
        }

        .info-content a:hover {
            color: #D2691E;
            text-decoration: underline;
        }

        /* Contact Form */
        .contact-form {
            padding: 2.5rem;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(139, 69, 19, 0.08);
            border: 1px solid rgba(139, 69, 19, 0.1);
            position: relative;
            overflow: hidden;
        }

        .contact-form:before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 5px;
            height: 100%;
            background: linear-gradient(to bottom, #8B4513 0%, #D2691E 100%);
        }

        .form-title {
            font-size: 1.6rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid rgba(139, 69, 19, 0.2);
            position: relative;
        }

        .form-title:after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 60px;
            height: 2px;
            background: linear-gradient(135deg, #8B4513 0%, #D2691E 100%);
        }

        .alert {
            padding: 1.2rem;
            border-radius: 8px;
            margin-bottom: 1.8rem;
            font-weight: 500;
            border-left: 4px solid;
            animation: slideIn 0.5s ease;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .alert-success {
            background-color: #f0fff4;
            color: #22543d;
            border-left-color: #48bb78;
            border: 1px solid #c6f6d5;
        }

        .alert-success i {
            color: #48bb78;
            margin-right: 10px;
        }

        .alert-error {
            background-color: #fff5f5;
            color: #742a2a;
            border-left-color: #f56565;
            border: 1px solid #fed7d7;
        }

        .alert-error i {
            color: #f56565;
            margin-right: 10px;
        }

        .form-group {
            margin-bottom: 1.8rem;
            position: relative;
        }

        .form-label {
            display: block;
            margin-bottom: 0.6rem;
            font-weight: 600;
            color: #555;
            font-size: 0.95rem;
        }

        .form-control {
            width: 100%;
            padding: 0.9rem 1.2rem;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s;
            background: white;
            color: #333;
            font-family: inherit;
        }

        .form-control:focus {
            outline: none;
            border-color: #8B4513;
            box-shadow: 0 0 0 3px rgba(139, 69, 19, 0.15);
            background-color: #fffaf0;
        }

        textarea.form-control {
            min-height: 160px;
            resize: vertical;
            line-height: 1.5;
        }

        select.form-control {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%238B4513' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 1rem center;
            background-size: 16px 12px;
            padding-right: 3rem;
        }

        .submit-btn {
            width: 100%;
            padding: 1.2rem;
            background: linear-gradient(135deg, #8B4513 0%, #D2691E 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            position: relative;
            overflow: hidden;
        }

        .submit-btn:hover {
            background: linear-gradient(135deg, #A0522D 0%, #8B4513 100%);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(139, 69, 19, 0.3);
        }

        .submit-btn:active {
            transform: translateY(0);
        }

        .submit-btn:before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: 0.5s;
        }

        .submit-btn:hover:before {
            left: 100%;
        }

        .submit-btn i {
            font-size: 1.2rem;
            transition: transform 0.3s;
        }

        .submit-btn:hover i {
            transform: translateX(5px);
        }

        /* Success animation */
        @keyframes successCheck {
            0% {
                transform: scale(0);
                opacity: 0;
            }
            50% {
                transform: scale(1.2);
            }
            100% {
                transform: scale(1);
                opacity: 1;
            }
        }

        .success-check {
            animation: successCheck 0.5s ease;
            display: inline-block;
        }
    </style>
</head>
<body>
    <!-- Header Section -->
    <?php include 'navigation.php'; ?>

    <!-- Contact Section -->
    <section class="contact-section">
        <div class="contact-container">
            <h1 class="page-title">Get in Touch</h1>
            <p class="page-subtitle">
                Have questions, need assistance with custom designs, or want to schedule a visit? 
                We're here to help you find the perfect outfit for every occasion.
            </p>
            
            <div class="contact-layout">
                <!-- Contact Information -->
                <div class="contact-info">
                    <h2 class="info-title">Contact Information</h2>
                    
                    <div class="info-item">
                        <div class="info-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div class="info-content">
                            <h3>Visit Our Boutique</h3>
                            <p><?php echo htmlspecialchars($location); ?></p>
                            <p style="color: #888; font-size: 0.9rem; margin-top: 5px;">
                                <i class="fas fa-info-circle"></i> By appointment recommended
                            </p>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-icon">
                            <i class="fas fa-phone"></i>
                        </div>
                        <div class="info-content">
                            <h3>Call Us</h3>
                            <p><strong>Primary:</strong> <a href="tel:<?php echo htmlspecialchars(str_replace('-', '', $primary_phone)); ?>">
                                <?php echo htmlspecialchars($primary_phone); ?></a></p>
                            <p><strong>Secondary:</strong> <a href="tel:<?php echo htmlspecialchars(str_replace('-', '', $secondary_phone)); ?>">
                                <?php echo htmlspecialchars($secondary_phone); ?></a></p>
                            <p style="color: #888; font-size: 0.9rem; margin-top: 5px;">
                                <i class="fas fa-clock"></i> Available during business hours
                            </p>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <div class="info-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div class="info-content">
                            <h3>Email Us</h3>
                            <p><a href="mailto:<?php echo htmlspecialchars($boutique_email); ?>">
                                <?php echo htmlspecialchars($boutique_email); ?></a></p>
                            <p style="color: #888; font-size: 0.9rem; margin-top: 5px;">
                                <i class="fas fa-reply"></i> Response within 24 hours
                            </p>
                        </div>
                    </div>
                </div>
                
                <!-- Contact Form -->
                <div class="contact-form">
                    <h2 class="form-title">Send Us a Message</h2>
                    
                    <?php if ($success_message): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle success-check"></i> 
                            <span><?php echo $success_message; ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($error_message): ?>
                        <div class="alert alert-error">
                            <i class="fas fa-exclamation-circle"></i> 
                            <span><?php echo $error_message; ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="" id="contactForm">
                        <div class="form-group">
                            <label class="form-label" for="name">
                                Full Name <span class="required">*</span>
                            </label>
                            <input type="text" class="form-control" id="name" name="name" 
                                   value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" 
                                   placeholder="Enter your full name" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="email">
                                Email Address <span class="required">*</span>
                            </label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" 
                                   placeholder="your.email@example.com" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="subject">
                                Subject <span class="required">*</span>
                            </label>
                            <select class="form-control" id="subject" name="subject" required>
                                <option value="">Select a subject</option>
                                <option value="General Inquiry" <?php echo (isset($_POST['subject']) && $_POST['subject'] == 'General Inquiry') ? 'selected' : ''; ?>>General Inquiry</option>
                                <option value="Custom Design" <?php echo (isset($_POST['subject']) && $_POST['subject'] == 'Custom Design') ? 'selected' : ''; ?>>Custom Design Consultation</option>
                                <option value="Appointment" <?php echo (isset($_POST['subject']) && $_POST['subject'] == 'Appointment') ? 'selected' : ''; ?>>Schedule Appointment</option>
                                <option value="Other" <?php echo (isset($_POST['subject']) && $_POST['subject'] == 'Other') ? 'selected' : ''; ?>>Other</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="message">
                                Message <span class="required">*</span>
                            </label>
                            <textarea class="form-control" id="message" name="message" 
                                      placeholder="Please provide details about your inquiry..." 
                                      required><?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; ?></textarea>
                        </div>
                        
                        <button type="submit" class="submit-btn" id="submitBtn">
                            <i class="fas fa-paper-plane"></i>
                            Send Message
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer Section -->
    <?php include 'footer.php'; ?>

    <script>
        // Simple form validation
        document.getElementById('contactForm').addEventListener('submit', function(e) {
            const name = document.getElementById('name').value.trim();
            const email = document.getElementById('email').value.trim();
            const subject = document.getElementById('subject').value;
            const message = document.getElementById('message').value.trim();
            
            if (!name || !email || !subject || !message) {
                e.preventDefault();
                alert('Please fill in all required fields');
                return false;
            }
            
            // Show loading
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
            submitBtn.disabled = true;
        });
    </script>
</body>
</html>
<?php
$conn->close();
?>