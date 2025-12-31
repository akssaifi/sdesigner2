
<?php
// terms.php - Terms & Conditions page
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

// Get filter parameters
$product_type = isset($_GET['type']) ? $_GET['type'] : '';
$category_id = isset($_GET['category']) ? intval($_GET['category']) : 0;
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
$occasion = isset($_GET['occasion']) ? intval($_GET['occasion']) : 0;
$fabric = isset($_GET['fabric']) ? intval($_GET['fabric']) : 0;
$style = isset($_GET['style']) ? intval($_GET['style']) : 0;
$work = isset($_GET['work']) ? intval($_GET['work']) : 0;
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include 'header.php'; ?>
    <title>Terms & Conditions | <?php echo htmlspecialchars($boutique_name); ?></title>
    <style>
        /* Terms Page Styles */
        body {
            padding-top: 70px;
        }

        @media (min-width: 768px) {
            body {
                padding-top: 80px;
            }
        }

        .terms-section {
            padding: 2rem 0;
            background: var(--gray-50);
            min-height: 70vh;
        }

        @media (min-width: 768px) {
            .terms-section {
                padding: 3rem 0;
            }
        }

        .terms-container {
            max-width: 900px;
            margin: 0 auto;
            padding: 0 1rem;
        }

        .page-title {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 2rem;
            font-family: 'Playfair Display', serif;
            text-align: center;
            position: relative;
            padding-bottom: 1rem;
        }

        @media (min-width: 768px) {
            .page-title {
                font-size: 2.5rem;
                margin-bottom: 3rem;
            }
        }

        .page-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 3px;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
        }

        .terms-content {
            background: white;
            border-radius: var(--radius);
            padding: 2rem;
            box-shadow: var(--shadow-sm);
        }

        @media (min-width: 768px) {
            .terms-content {
                padding: 3rem;
            }
        }

        .last-updated {
            text-align: center;
            color: var(--gray-600);
            font-style: italic;
            margin-bottom: 2rem;
            font-size: 0.9rem;
        }

        @media (min-width: 768px) {
            .last-updated {
                margin-bottom: 3rem;
                font-size: 1rem;
            }
        }

        .terms-section-heading {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--dark);
            margin: 2rem 0 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid var(--primary);
        }

        @media (min-width: 768px) {
            .terms-section-heading {
                font-size: 1.5rem;
                margin: 2.5rem 0 1.25rem;
            }
        }

        .terms-section-heading:first-child {
            margin-top: 0;
        }

        .terms-text {
            color: var(--gray-700);
            line-height: 1.8;
            margin-bottom: 1.5rem;
            font-size: 0.95rem;
        }

        @media (min-width: 768px) {
            .terms-text {
                font-size: 1rem;
            }
        }

        .terms-list {
            margin: 1.5rem 0;
            padding-left: 1.5rem;
        }

        .terms-list li {
            margin-bottom: 1rem;
            color: var(--gray-700);
            line-height: 1.6;
        }

        .highlight-box {
            background: var(--primary-light);
            border-left: 4px solid var(--primary);
            padding: 1.5rem;
            margin: 2rem 0;
            border-radius: 0 var(--radius) var(--radius) 0;
        }

        .highlight-box h4 {
            color: var(--dark);
            margin-bottom: 0.5rem;
            font-weight: 600;
        }

        .contact-info-box {
            background: var(--gray-100);
            border: 1px solid var(--gray-200);
            border-radius: var(--radius);
            padding: 1.5rem;
            margin: 2rem 0;
            text-align: center;
        }

        .contact-info-box h4 {
            color: var(--dark);
            margin-bottom: 1rem;
            font-weight: 600;
        }

        .contact-info-box p {
            margin-bottom: 0.5rem;
            color: var(--gray-700);
        }

        .contact-info-box a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
        }

        .contact-info-box a:hover {
            text-decoration: underline;
        }

        .note-box {
            background: #FFF9E6;
            border: 1px solid #FFE066;
            border-radius: var(--radius);
            padding: 1.5rem;
            margin: 2rem 0;
        }

        .note-box h4 {
            color: #E6B800;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .note-box h4 i {
            font-size: 1.2rem;
        }

        .back-to-home {
            text-align: center;
            margin-top: 3rem;
        }

        .back-to-home a {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            font-size: 1rem;
            padding: 0.75rem 1.5rem;
            border: 2px solid var(--primary);
            border-radius: var(--radius);
            transition: all 0.3s ease;
        }

        .back-to-home a:hover {
            background: var(--primary);
            color: white;
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        @media (max-width: 767px) {
            .terms-content {
                padding: 1.5rem;
            }
            
            .terms-section-heading {
                font-size: 1.125rem;
            }
            
            .highlight-box,
            .contact-info-box,
            .note-box {
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <!-- Header Section -->
    <?php include 'navigation.php'; ?>

    <!-- Terms & Conditions Section -->
    <section class="terms-section">
        <div class="terms-container">
            <h1 class="page-title">Terms & Conditions</h1>
            
           
            
            <div class="terms-content">
                <h2 class="terms-section-heading">Welcome to <?php echo htmlspecialchars($boutique_name); ?></h2>
                <p class="terms-text">
                    By accessing and using our website and services, you agree to comply with and be bound by the following terms and conditions. Please read them carefully before placing an order.
                </p>

                <h2 class="terms-section-heading">Order Placement & Acceptance</h2>
                <p class="terms-text">
                    All orders placed through our website are subject to acceptance and availability. We reserve the right to refuse or cancel any order for any reason at any time.
                </p>
                <ul class="terms-list">
                    <li>Orders are confirmed only after receiving full payment</li>
                    <li>Prices are subject to change without prior notice</li>
                    <li>All products are handmade and may have slight variations</li>
                    <li>Custom orders require special terms and timelines</li>
                </ul>

                <h2 class="terms-section-heading">Pricing & Payment</h2>
                <p class="terms-text">
                    All prices are in Indian Rupees (₹) and inclusive of applicable taxes unless otherwise stated.
                </p>
                <div class="highlight-box">
                    <h4>Accepted Payment Methods:</h4>
                    <p>• Credit/Debit Cards (Visa, MasterCard, American Express)</p>
                    <p>• Bank Transfers</p>
                    <p>• Cash on Delivery (Limited areas)</p>
                    <p>• UPI Payments</p>
                </div>

                <h2 class="terms-section-heading">Shipping & Delivery</h2>
                <p class="terms-text">
                    We strive to deliver your orders in the shortest possible time while maintaining quality standards.
                </p>
                <ul class="terms-list">
                    <li>Standard delivery: 7-10 business days</li>
                    <li>Express delivery: 3-5 business days (additional charges apply)</li>
                    <li>Custom orders: 15-30 business days depending on complexity</li>
                    <li>Shipping charges are calculated based on location and order value</li>
                    <li>Delivery timelines are estimates and not guaranteed</li>
                </ul>

                <h2 class="terms-section-heading" id="cancellation">Cancellation & Return Policy</h2>
                <p class="terms-text">
                    We want you to be completely satisfied with your purchase. Please review our cancellation and return policy carefully.
                </p>

                <div class="note-box">
                    <h4><i class="fas fa-exclamation-circle"></i> Important Notice</h4>
                    <p>Due to the customized and handmade nature of our products, certain restrictions apply to cancellations and returns.</p>
                </div>

                <h3>Cancellation Policy</h3>
                <ul class="terms-list">
                    <li>Cancellations are accepted within 5 Days of order placement</li>
                    <li>Customized orders cannot be cancelled once production has begun</li>
                    <li>Cancellation requests must be made in writing to sdesignerjal@gmail.com</li>
                    <li>Refunds for cancellations will be processed within 7-10 business days</li>
                    <li>A cancellation fee of 10% may apply for orders cancelled after 24 hours</li>
                </ul>

                <h3>Return & Exchange Policy</h3>
                <ul class="terms-list">
                    <li>Returns are accepted within 7 days of delivery</li>
                    <li>Products must be in original condition with all tags intact</li>
                    <li>Customized, altered, or made-to-order items cannot be returned</li>
                    <li>Sale items are final sale and cannot be returned</li>
                    <li>Return shipping costs are the responsibility of the customer</li>
                    <li>Exchanges are subject to availability</li>
                </ul>

                <h3>Defective or Damaged Items</h3>
                <p class="terms-text">
                    If you receive a defective or damaged item, please contact us within 48 hours of delivery. We will arrange for a replacement or refund, including shipping costs.
                </p>

                <h2 class="terms-section-heading">Refund Process</h2>
                <p class="terms-text">
                    Refunds are processed using the original payment method:
                </p>
                <ul class="terms-list">
                    <li>Credit/Debit Card: 7-10 business days</li>
                    <li>Bank Transfer: 3-5 business days</li>
                    <li>Cash on Delivery: Refund via bank transfer only</li>
                </ul>

                <h2 class="terms-section-heading">Custom Orders</h2>
                <p class="terms-text">
                    Custom orders require special attention and are subject to separate terms:
                </p>
                <ul class="terms-list">
                    <li>50% advance payment required to begin work</li>
                    <li>Balance payment due before shipping</li>
                    <li>No cancellations once production begins</li>
                    <li>Design changes during production may incur additional charges</li>
                    <li>Timelines for custom orders are estimates only</li>
                </ul>

                <h2 class="terms-section-heading">Intellectual Property</h2>
                <p class="terms-text">
                    All designs, images, and content on this website are the intellectual property of <?php echo htmlspecialchars($boutique_name); ?> and <?php echo htmlspecialchars($designer_name); ?>. Reproduction, distribution, or commercial use without written permission is strictly prohibited.
                </p>

                <h2 class="terms-section-heading">Privacy Policy</h2>
                <p class="terms-text">
                    We respect your privacy. Personal information collected during transactions is used solely for order processing and will not be shared with third parties without consent. Please refer to our Privacy Policy for detailed information.
                </p>

                <h2 class="terms-section-heading">Limitation of Liability</h2>
                <p class="terms-text">
                    <?php echo htmlspecialchars($boutique_name); ?> shall not be liable for any indirect, incidental, or consequential damages arising from the use of our products or services.
                </p>

                <div class="contact-info-box">
                    <h4>Need Help with Cancellation or Returns?</h4>
                    <p>Email: <a href="mailto:sdesignerjal@gmail.com">sdesignerjal@gmail.com</a></p>
                    <p>Phone: <a href="tel:<?php echo preg_replace('/[^0-9]/', '', $primary_phone); ?>"><?php echo htmlspecialchars($primary_phone); ?></a></p>
                    <p>Hours: Mon-Sat 10:00 AM - 8:00 PM</p>
                </div>

                <p class="terms-text">
                    These terms and conditions are subject to change without prior notice. Continued use of our services constitutes acceptance of any changes.
                </p>

                <div class="back-to-home">
                    <a href="index.php">
                        <i class="fas fa-home"></i>
                        Back to Home
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer Section -->
    <?php include 'footer.php'; ?>
</body>
</html>
<?php
$conn->close();
?>
