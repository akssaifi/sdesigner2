<?php
// about.php - About Designer page
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include 'header.php'; ?>
    <title>About Designer | <?php echo htmlspecialchars($boutique_name); ?></title>
    <style>
   

        .about-hero {
            background: linear-gradient(135deg, 
                rgba(139, 69, 19, 0.9) 0%, 
                rgba(212, 167, 106, 0.8) 100%),
                url('https://images.unsplash.com/photo-1515372039744-b8f02a3ae446?ixlib=rb-4.0.3&auto=format&fit=crop&w=1600&q=80');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 4rem 0;
            text-align: center;
            margin-bottom: 3rem;
            position: relative;
            overflow: hidden;
        }

        .about-hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" preserveAspectRatio="none"><path d="M0,0 L100,0 L100,100 Z" fill="rgba(255,255,255,0.1)"/></svg>');
            background-size: cover;
            opacity: 0.3;
        }

        .about-hero-content {
            position: relative;
            z-index: 2;
            max-width: 800px;
            margin: 0 auto;
            padding: 0 1rem;
        }

        .designer-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            padding: 0.75rem 1.5rem;
            border-radius: 50px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            margin-bottom: 2rem;
        }

        .designer-badge i {
            color: #FFD700;
        }

        .about-title {
            font-size: 3rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
            font-family: 'Playfair Display', serif;
            line-height: 1.2;
        }

        @media (max-width: 768px) {
            .about-title {
                font-size: 2.25rem;
            }
        }

        .about-subtitle {
            font-size: 1.25rem;
            opacity: 0.9;
            max-width: 600px;
            margin: 0 auto;
        }

        .about-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
        }

        /* Main Content */
        .about-main {
            display: grid;
            grid-template-columns: 1fr;
            gap: 3rem;
            margin-bottom: 4rem;
        }

        @media (min-width: 992px) {
            .about-main {
                grid-template-columns: 1fr 1fr;
                align-items: start;
            }
        }

        .about-card {
            background: white;
            border-radius: var(--radius);
            padding: 2rem;
            box-shadow: var(--shadow);
            border: 1px solid var(--gray-200);
            height: 100%;
        }

        @media (min-width: 768px) {
            .about-card {
                padding: 2.5rem;
            }
        }

        .section-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.5rem;
        }

        .section-icon i {
            font-size: 1.5rem;
            color: white;
        }

        .section-heading {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 1.25rem;
            font-family: 'Playfair Display', serif;
            position: relative;
            padding-bottom: 0.75rem;
        }

        .section-heading::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 60px;
            height: 3px;
            background: var(--primary);
            border-radius: 2px;
        }

        .about-text {
            color: var(--gray-700);
            line-height: 1.8;
            margin-bottom: 1.5rem;
            font-size: 1.05rem;
        }

        .about-text:last-child {
            margin-bottom: 0;
        }

        /* Designer Philosophy */
        .philosophy-box {
            background: linear-gradient(135deg, var(--light) 0%, var(--accent-light) 100%);
            border-radius: var(--radius);
            padding: 2.5rem;
            margin: 2rem 0;
            position: relative;
            border-left: 4px solid var(--primary);
        }

        .philosophy-box::before {
            content: '"';
            position: absolute;
            top: 0.5rem;
            left: 1.5rem;
            font-size: 4rem;
            font-family: 'Playfair Display', serif;
            color: var(--primary);
            opacity: 0.2;
            line-height: 1;
        }

        .philosophy-text {
            font-size: 1.25rem;
            font-style: italic;
            color: var(--dark);
            line-height: 1.6;
            margin-bottom: 1rem;
            position: relative;
            z-index: 1;
        }

        .philosophy-author {
            font-size: 1rem;
            font-weight: 600;
            color: var(--primary);
            text-align: right;
            margin-top: 1rem;
        }

        /* Values Grid */
        .values-grid {
            display: grid;
            grid-template-columns: repeat(1, 1fr);
            gap: 1.5rem;
            margin-top: 2rem;
        }

        @media (min-width: 640px) {
            .values-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (min-width: 1024px) {
            .values-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        .value-item {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            padding: 1.5rem;
            background: white;
            border-radius: var(--radius);
            border: 1px solid var(--gray-200);
            transition: var(--transition);
        }

        .value-item:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
            border-color: var(--primary);
        }

        .value-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .value-icon i {
            font-size: 1.25rem;
            color: white;
        }

        .value-content h4 {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 0.5rem;
        }

        .value-content p {
            color: var(--gray-600);
            font-size: 0.95rem;
            line-height: 1.5;
        }

        /* Contact Information */
        .contact-section {
            background: white;
            border-radius: var(--radius);
            padding: 2.5rem;
            box-shadow: var(--shadow);
            margin-bottom: 4rem;
            border: 1px solid var(--gray-200);
        }

        .contact-grid {
            display: grid;
            grid-template-columns: repeat(1, 1fr);
            gap: 1.5rem;
            margin-top: 2rem;
        }

        @media (min-width: 768px) {
            .contact-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (min-width: 1024px) {
            .contact-grid {
                grid-template-columns: repeat(4, 1fr);
            }
        }

        .contact-card {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1.5rem;
            background: var(--gray-100);
            border-radius: var(--radius);
            border: 1px solid var(--gray-200);
            transition: var(--transition);
        }

        .contact-card:hover {
            background: var(--light);
            border-color: var(--primary);
            transform: translateY(-3px);
        }

        .contact-icon {
            width: 50px;
            height: 50px;
            background: var(--primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .contact-icon i {
            font-size: 1.25rem;
            color: white;
        }

        .contact-details h4 {
            font-size: 1rem;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 0.25rem;
        }

        .contact-detail p {
            color: var(--gray-600);
            font-size: 0.9rem;
            line-height: 1.4;
        }

.contact-detail p,
.contact-detail a {
    
    font-size: 0.85rem;
    line-height: 1.5;
    text-decoration: none;
}
        .contact-detail a {
            color: var(--primary);
            text-decoration: none;
            transition: var(--transition);
            display: block;
        }

        .contact-detail a:hover {
            color: var(--primary-light);
        }

        /* Working Hours */
        .hours-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .hours-list li {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            border-bottom: 1px solid var(--gray-200);
        }

        .hours-list li:last-child {
            border-bottom: none;
        }

        .hours-list span:first-child {
            color: var(--gray-600);
        }

        .hours-list span:last-child {
            font-weight: 500;
            color: var(--dark);
        }

        /* Final Inspiration */
        .inspiration-section {
            background: linear-gradient(135deg, var(--dark) 0%, var(--primary-dark) 100%);
            color: white;
            padding: 4rem 0;
            border-radius: var(--radius);
            text-align: center;
            margin-bottom: 3rem;
        }

        .inspiration-content {
            max-width: 800px;
            margin: 0 auto;
            padding: 0 1rem;
        }

        .inspiration-quote {
            font-size: 1.5rem;
            font-style: italic;
            line-height: 1.6;
            margin-bottom: 2rem;
            position: relative;
            padding: 0 2rem;
        }

        .inspiration-quote::before,
        .inspiration-quote::after {
            content: '"';
            font-size: 4rem;
            font-family: 'Playfair Display', serif;
            color: var(--secondary);
            opacity: 0.5;
            position: absolute;
            line-height: 1;
        }

        .inspiration-quote::before {
            top: -1rem;
            left: 0;
        }

        .inspiration-quote::after {
            bottom: -2rem;
            right: 0;
        }

        .inspiration-signature {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--secondary);
            margin-top: 2rem;
        }

        /* Back to Home */
        .back-home {
            text-align: center;
            padding: 2rem 0;
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            padding: 1rem 2rem;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 600;
            transition: var(--transition);
            box-shadow: var(--shadow);
        }

        .back-btn:hover {
            gap: 1.25rem;
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }
    </style>
</head>
<body>
    <!-- Header Section -->
    <?php include 'navigation.php'; ?>

    <!-- Hero Section -->
    <section class="about-hero">
        <div class="about-hero-content">
            <div class="designer-badge">
                <i class="fas fa-crown"></i>
                <span>MEET THE DESIGNER</span>
            </div>
            
            <h1 class="about-title"><?php echo htmlspecialchars($designer_name); ?></h1>
            <p class="about-subtitle">The Creative Vision Behind <?php echo htmlspecialchars($boutique_name); ?></p>
        </div>
    </section>

    <!-- Main Content -->
    <div class="about-container">
        <div class="about-main">
            <!-- Designer Story -->
            <div class="about-card">
                <div class="section-icon">
                    <i class="fas fa-user-circle"></i>
                </div>
                
                <h2 class="section-heading">My Journey</h2>
                
                <div class="about-text">
                    Welcome to <?php echo htmlspecialchars($boutique_name); ?>, where fashion meets artistry. 
                    I'm <?php echo htmlspecialchars($designer_name); ?>, the creative force and visionary behind this boutique. 
                    With a passion for traditional craftsmanship and contemporary design, I've dedicated my career 
                    to creating pieces that celebrate femininity, elegance, and individuality.
                </div>
                
                <div class="about-text">
                    Based in the culturally rich city of <?php echo htmlspecialchars($location); ?>, I draw inspiration from 
                    the vibrant colors, intricate patterns, and timeless traditions of Indian fashion. 
                    Each collection is thoughtfully curated to blend heritage techniques with modern aesthetics, 
                    ensuring that every piece tells a unique story.
                </div>
                
                <div class="philosophy-box">
                    <p class="philosophy-text">
                        Fashion is not just about clothing; it's about expressing who you are and celebrating 
                        your unique journey. At <?php echo htmlspecialchars($boutique_name); ?>, we don't just create 
                        outfits—we craft experiences and memories.
                    </p>
                    <div class="philosophy-author">— <?php echo htmlspecialchars($designer_name); ?></div>
                </div>
                
                <div class="about-text">
                    My journey in fashion began over a decade ago, with a simple mission: to create wearable art 
                    that makes women feel confident, beautiful, and empowered. From intricate bridal lehengas to 
                    elegant everyday wear, each piece is designed with meticulous attention to detail, quality 
                    fabrics, and exceptional craftsmanship.
                </div>
            </div>

            <!-- Core Values -->
            <div class="about-card">
                <div class="section-icon">
                    <i class="fas fa-star"></i>
                </div>
                
                <h2 class="section-heading">Our Core Values</h2>
                
                <div class="values-grid">
                    <div class="value-item">
                        <div class="value-icon">
                            <i class="fas fa-gem"></i>
                        </div>
                        <div class="value-content">
                            <h4>Quality Craftsmanship</h4>
                            <p>Every piece is meticulously crafted using premium materials and traditional techniques passed down through generations.</p>
                        </div>
                    </div>
                    
                    <div class="value-item">
                        <div class="value-icon">
                            <i class="fas fa-heart"></i>
                        </div>
                        <div class="value-content">
                            <h4>Personalized Service</h4>
                            <p>We believe in building relationships with our clients, offering customized solutions and attentive service for every occasion.</p>
                        </div>
                    </div>
                    
                    <div class="value-item">
                        <div class="value-icon">
                            <i class="fas fa-leaf"></i>
                        </div>
                        <div class="value-content">
                            <h4>Sustainable Fashion</h4>
                            <p>Committed to ethical practices and sustainable sourcing, ensuring our creations are beautiful inside and out.</p>
                        </div>
                    </div>
                    
                    <div class="value-item">
                        <div class="value-icon">
                            <i class="fas fa-bolt"></i>
                        </div>
                        <div class="value-content">
                            <h4>Innovation</h4>
                            <p>Constantly evolving our designs to blend traditional artistry with contemporary trends and modern sensibilities.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contact Information -->
        <div class="contact-section">
            <div class="section-icon" style="margin: 0 auto 2rem;">
                <i class="fas fa-address-book"></i>
            </div>
            
            <h2 class="section-heading" style="text-align: center;">Get in Touch</h2>
            
            <div class="contact-grid">
                <div class="contact-card">
                    <div class="contact-icon">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <div class="contact-detail">
                        <h4>Visit Our Boutique</h4>
                        <p><?php echo htmlspecialchars($location); ?></p>
                    </div>
                </div>
                
                <div class="contact-card">
                    <div class="contact-icon">
                        <i class="fas fa-phone"></i>
                    </div>
                    <div class="contact-detail">
                        <h4>Call Us</h4>
                        <p><a href="tel:<?php echo htmlspecialchars($primary_phone); ?>"><?php echo htmlspecialchars($primary_phone); ?></a></p>
                        <?php if (!empty($secondary_phone)): ?>
                        <p><a href="tel:<?php echo htmlspecialchars($secondary_phone); ?>"><?php echo htmlspecialchars($secondary_phone); ?></a></p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="contact-card">
                    <div class="contact-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div class="contact-detail">
                        <h4>Email Us</h4>
                        <p><a href="mailto:sdesignerjal@gmail.com">sdesignerjal@gmail.com</a></p>
                    </div>
                </div>
                
                <div class="contact-card">
                    <div class="contact-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="contact-details">
                        <h4>Working Hours</h4>
                        <ul class="hours-list">
                            <li><span>Mon - Sat</span><span>10:00 AM - 8:00 PM</span></li>
                            <li><span>Sunday</span><span>11:00 AM - 6:00 PM</span></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Final Inspiration -->
        <div class="inspiration-section">
            <div class="inspiration-content">
                <p class="inspiration-quote">
                    The most beautiful thing a woman can wear is confidence. My designs are created to 
                    enhance that confidence and celebrate the unique beauty in every woman. Each stitch, 
                    every pattern, and all the intricate details are woven together to create not just 
                    clothing, but a statement of self-expression and empowerment.
                </p>
                <div class="inspiration-signature">
                    — <?php echo htmlspecialchars($designer_name); ?>
                </div>
            </div>
        </div>

        <!-- Back to Home -->
        <div class="back-home">
            <a href="index.php" class="back-btn">
                <i class="fas fa-arrow-left"></i>
                Back to Home
            </a>
        </div>
    </div>

    <!-- Footer Section -->
    <?php include 'footer.php'; ?>
</body>
</html>
<?php
$conn->close();
?>