<?php
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

// Get marquee settings and messages
$marquee_enabled = getSetting($conn, 'marquee_enabled') ?? 1;
$marquee_speed = getSetting($conn, 'marquee_speed') ?? 30;
$marquee_messages = $marquee_enabled ? getMarqueeMessages($conn, true) : [];

// Get featured products for homepage
$featured_stitched = getProductsByType($conn, 'stitched', 6);
$featured_unstitched = getProductsByType($conn, 'unstitched', 4);
$featured_accessories = getProductsByType($conn, 'accessory', 4);

// Get only 10 categories
$categories = getCategories($conn);
$limited_categories = array_slice($categories, 0, 10);

// Get meta tags for filtering
$occasion_tags = getMetaTags($conn, 'occasion');
$fabric_tags = getMetaTags($conn, 'fabric');
$style_tags = getMetaTags($conn, 'style');
$work_tags = getMetaTags($conn, 'work');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($boutique_name); ?> - Home</title>
    <meta name="title" content="SDesigner Boutique Jalandhar | Designer Wear by Dinky Ahuja">
<meta name="description" content="SDesigner Boutique in Jalandhar by Dinky Ahuja offers premium designer wear, custom stitching, bridal outfits, lehengas, embroidery, and boutique services tailored to perfection.">
<meta name="keywords" content="SDesigner Boutique, SDesigner Jalandhar, Dinky Ahuja designer, boutique in Jalandhar, designer boutique Punjab, bridal wear Jalandhar, lehenga boutique, custom stitching Jalandhar, embroidery boutique, women's designer wear">
<meta name="author" content="Dinky Ahuja">
<meta name="robots" content="index, follow">
<meta name="language" content="English">
<meta name="revisit-after" content="7 days">
<meta name="geo.region" content="IN-PB">
<meta name="geo.placename" content="Jalandhar, Punjab, India">

<!-- Open Graph / Facebook -->
<meta property="og:type" content="website">
<meta property="og:url" content="https://sdesignerjal.in/">
<meta property="og:title" content="SDesigner Boutique Jalandhar | Designer Wear by Dinky Ahuja">
<meta property="og:description" content="Premium designer boutique in Jalandhar by Dinky Ahuja. Bridal wear, lehengas, custom stitching, and elegant designer outfits.">
<meta property="og:image" content="img/web.png">

<!-- Twitter -->
<meta property="twitter:card" content="summary_large_image">
<meta property="twitter:url" content="https://sdesignerjal.in/">
<meta property="twitter:title" content="SDesigner Boutique Jalandhar | Designer Wear by Dinky Ahuja">
<meta property="twitter:description" content="Explore exclusive designer wear, bridal outfits, and custom stitching at SDesigner Boutique, Jalandhar.">
<meta property="twitter:image" content="img/web.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Playfair+Display:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        /* Global Styles */
        :root {
            --primary: #8B4513;
            --primary-light: #a0522d;
            --primary-dark: #6b340e;
            --secondary: #D4A76A;
            --secondary-light: #e6b87d;
            --accent: #E6B87D;
            --accent-light: #f4d4a8;
            --dark: #2C1810;
            --dark-light: #3d2417;
            --light: #FAF3E0;
            --light-dark: #f0e6cc;
            --gray-100: #F9FAFB;
            --gray-200: #E5E7EB;
            --gray-300: #D1D5DB;
            --gray-400: #9CA3AF;
            --gray-500: #6B7280;
            --gray-600: #4B5563;
            --gray-700: #374151;
            --gray-800: #1F2937;
            --transition: all 0.3s ease;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-md: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --shadow-lg: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            --radius: 0.5rem;
            --radius-lg: 1rem;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background-color: var(--gray-100);
            color: var(--gray-800);
            line-height: 1.6;
          
        }

        .container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
        }

        .section-padding {
            padding: 4rem 0;
        }

        .bg-light {
            background-color: var(--light);
        }

        .section-header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .section-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 0.5rem;
            font-family: 'Playfair Display', serif;
        }

        .section-subtitle {
            font-size: 1.1rem;
            color: var(--gray-600);
            max-width: 600px;
            margin: 0 auto;
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
            font-size: 1rem;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            color: white;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, var(--primary-light) 0%, var(--primary) 100%);
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .view-all-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
        }

        .view-all-btn:hover {
            gap: 1rem;
            color: var(--primary-light);
        }

        /* FIXED: Hero Carousel Section */
        .hero-carousel {
            height: 85vh;
            min-height: 600px;
            max-height: 800px;
            overflow: hidden;
            margin-top: -30px; /* Compensate for body padding */
        }

        .carousel-slides {
            position: relative;
            width: 100%;
            height: 100%;
        }

        .carousel-slide {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            transition: opacity 1s ease-in-out;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .carousel-slide.active {
            opacity: 1;
            z-index: 1;
        }

        .slide-bg {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            filter: brightness(0.8);
            animation: zoomInOut 30s ease-in-out infinite alternate;
        }

        .slide-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, 
                rgba(139, 69, 19, 0.4) 0%, 
                rgba(212, 167, 106, 0.3) 50%, 
                rgba(230, 184, 125, 0.2) 100%);
            z-index: 1;
        }

        .slide-content {
            position: relative;
            z-index: 2;
            width: 100%;
            text-align: center;
            padding: 2rem;
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.8s ease-out 0.3s;
        }

        .carousel-slide.active .slide-content {
            opacity: 1;
            transform: translateY(0);
        }

        .welcome-badge {
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

        .welcome-badge i {
            color: #FFD700;
            animation: pulse 2s infinite;
        }

        .welcome-badge span {
            color: white;
            font-weight: 600;
            letter-spacing: 1px;
            font-size: 0.9rem;
        }

        .hero-title {
            font-size: 3.5rem;
            font-weight: 800;
            color: white;
            margin-bottom: 0.5rem;
            line-height: 1.2;
        }

        @media (min-width: 768px) {
            .hero-title {
                font-size: 4.5rem;
            }
        }

        @media (min-width: 1024px) {
            .hero-title {
                font-size: 5.5rem;
            }
        }

        .hero-subtitle {
            font-size: 1.25rem;
            color: rgba(255, 255, 255, 0.9);
            max-width: 600px;
            margin: 0 auto 2.5rem;
            line-height: 1.6;
        }

        .hero-cta {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            justify-content: center;
            align-items: center;
            margin-bottom: 3rem;
        }

        @media (min-width: 640px) {
            .hero-cta {
                flex-direction: row;
            }
        }

        .hero-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            padding: 1rem 2rem;
            border-radius: 50px;
            font-weight: 600;
            text-decoration: none;
            transition: var(--transition);
            min-width: 200px;
            justify-content: center;
        }

        .hero-btn.primary {
            background: white;
            color: var(--primary);
            box-shadow: var(--shadow-lg);
        }

        .hero-btn.primary:hover {
            background: var(--primary);
            color: white;
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .hero-btn.secondary {
            background: transparent;
            color: white;
            border: 2px solid white;
        }

        .hero-btn.secondary:hover {
            background: white;
            color: var(--primary);
            transform: translateY(-2px);
        }

        .carousel-controls {
            position: absolute;
            bottom: 2rem;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 0.5rem;
            z-index: 3;
        }

        .carousel-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.5);
            border: none;
            cursor: pointer;
            transition: var(--transition);
        }

        .carousel-dot.active {
            background: white;
            transform: scale(1.2);
        }

        .carousel-dot:hover {
            background: white;
        }

        .scroll-indicator {
            position: absolute;
            bottom: 5rem;
            left: 50%;
            transform: translateX(-50%);
            animation: bounce 2s infinite;
            z-index: 2;
        }

        .scroll-indicator a {
            margin-bottom: -200px;
            display: flex;
            flex-direction: column;
            align-items: center;
            color: white;
            text-decoration: none;
            gap: 0.5rem;
        }

        .scroll-indicator span {
            font-size: 0.75rem;
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        /* Animations */
        @keyframes zoomInOut {
            0% {
                transform: scale(1);
            }
            100% {
                transform: scale(1.1);
            }
        }

        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {
                transform: translateX(-50%) translateY(0);
            }
            40% {
                transform: translateX(-50%) translateY(-10px);
            }
            60% {
                transform: translateX(-50%) translateY(-5px);
            }
        }

        @keyframes pulse {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: 0.5;
            }
        }

        /* Categories Section */
        .categories-grid {
            display: grid;
            grid-template-columns: repeat(1, 1fr);
            gap: 1.5rem;
            margin-top: 2rem;
        }

        @media (min-width: 640px) {
            .categories-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (min-width: 1024px) {
            .categories-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media (min-width: 1280px) {
            .categories-grid {
                grid-template-columns: repeat(5, 1fr);
            }
        }

        .category-card {
            background: white;
            border-radius: var(--radius);
            padding: 2rem 1.5rem;
            text-decoration: none;
            transition: var(--transition);
            border: 1px solid var(--gray-200);
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }

        .category-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
            border-color: var(--primary-light);
        }

        .category-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
        }

        .category-icon i {
            font-size: 1.5rem;
            color: white;
        }

        .category-info h3 {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 0.25rem;
        }

        .category-info p {
            font-size: 0.875rem;
            color: var(--gray-600);
        }

        .category-arrow {
            margin-top: 1rem;
            color: var(--primary);
            opacity: 0;
            transform: translateX(-5px);
            transition: var(--transition);
        }

        .category-card:hover .category-arrow {
            opacity: 1;
            transform: translateX(0);
        }

        /* Products Grid */
        .products-grid {
            display: grid;
            grid-template-columns: repeat(1, 1fr);
            gap: 2rem;
            margin-top: 2rem;
        }

        @media (min-width: 640px) {
            .products-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (min-width: 1024px) {
            .products-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        .product-card {
            background: white;
            border-radius: var(--radius);
            overflow: hidden;
            transition: var(--transition);
            border: 1px solid var(--gray-200);
            display: flex;
            flex-direction: column;
            position: relative;
        }

        .product-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-lg);
        }

        .product-image {
            position: relative;
            width: 100%;
            height: 300px;
            overflow: hidden;
            background-color: var(--gray-100);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            transition: transform 0.5s ease;
            padding: 10px;
        }

        .product-card:hover .product-image img {
            transform: scale(1.05);
        }

        .no-image {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--gray-100);
            color: var(--gray-400);
            font-size: 2rem;
        }

        .product-overlay {
            position: absolute;
            inset: 0;
            background: rgba(0, 0, 0, 0.7);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: var(--transition);
            z-index: 2;
        }

        .product-card:hover .product-overlay {
            opacity: 1;
        }

        .overlay-buttons {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            align-items: center;
            padding: 2rem;
            width: 100%;
        }

        .view-btn, .add-to-cart-btn {
            padding: 0.75rem 1.5rem;
            border-radius: var(--radius);
            border: none;
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            width: 80%;
            max-width: 200px;
            text-align: center;
        }

        .view-btn {
            background: white;
            color: var(--dark);
            text-decoration: none;
            font-weight: 600;
        }

        .view-btn:hover {
            background: var(--primary);
            color: white;
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .add-to-cart-btn {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            font-weight: 600;
        }

        .add-to-cart-btn:hover {
            background: linear-gradient(135deg, var(--secondary) 0%, var(--primary) 100%);
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .add-to-cart-btn.added {
            background: #10B981;
            cursor: default;
        }

        .add-to-cart-btn.added:hover {
            transform: none;
            box-shadow: none;
        }

        .stock-badge {
            position: absolute;
            top: 1rem;
            right: 1rem;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            z-index: 2;
        }

        .stock-badge.out-of-stock {
            background: #FEE2E2;
            color: #DC2626;
        }

        .stock-badge.low-stock {
            background: #FEF3C7;
            color: #92400E;
        }

        .product-info {
            padding: 1.5rem;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .product-title {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 0.5rem;
            line-height: 1.3;
        }

        .product-category {
            font-size: 0.875rem;
            color: var(--gray-600);
            margin-bottom: 0.75rem;
        }

        /* FIXED: White color visibility */
        .product-colors {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1rem;
            flex-wrap: wrap;
        }

        .color-dot {
            width: 16px;
            height: 16px;
            border-radius: 50%;
            border: 2px solid var(--gray-300);
            box-shadow: var(--shadow-sm);
        }

        /* Special handling for white color */
        .color-dot[style*="background-color: #FFFFFF"],
        .color-dot[style*="background-color: #fff"],
        .color-dot[style*="background-color: white"] {
            border: 2px solid var(--gray-400);
        }

        .more-colors {
            font-size: 0.75rem;
            color: var(--gray-500);
        }

        .product-price-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: auto;
        }

        .product-price {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--primary);
        }

        /* Collections Grid */
        .collections-grid {
            display: grid;
            grid-template-columns: repeat(1, 1fr);
            gap: 2rem;
            margin-top: 2rem;
        }

        @media (min-width: 768px) {
            .collections-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        .collection-card {
            border-radius: var(--radius);
            overflow: hidden;
            position: relative;
            height: 400px;
        }

        .collection-image {
            width: 100%;
            height: 100%;
            background-color: var(--gray-100);
        }

        .collection-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .collection-card:hover .collection-image img {
            transform: scale(1.1);
        }

        .collection-content {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(to top, rgba(0, 0, 0, 0.8), transparent);
            padding: 2rem;
            color: white;
        }

        .collection-content h3 {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .collection-content p {
            font-size: 0.875rem;
            opacity: 0.9;
            margin-bottom: 1rem;
        }

        .collection-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: white;
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
        }

        .collection-link:hover {
            gap: 1rem;
            color: var(--accent);
        }

        /* Mobile Responsive Adjustments */
        @media (max-width: 768px) {
           
            
            .hero-carousel {
                min-height: 500px;
            }

            .hero-title {
                font-size: 2.5rem;
            }

            .hero-subtitle {
                font-size: 1rem;
                padding: 0 1rem;
            }

            .section-title {
                font-size: 2rem;
            }
            
            .section-padding {
                padding: 3rem 0;
            }
            
            .product-image {
                height: 250px;
            }
            
            .overlay-buttons {
                flex-direction: row;
                flex-wrap: wrap;
                justify-content: center;
                gap: 0.5rem;
            }
            
            .view-btn, .add-to-cart-btn {
                width: auto;
                min-width: 140px;
                padding: 0.6rem 1rem;
                font-size: 0.9rem;
            }
        }

        @media (max-width: 480px) {
            .hero-carousel {
                min-height: 400px;
            }
            
            .hero-title {
                font-size: 2rem;
            }
            
            .hero-btn {
                width: 90%;
                max-width: none;
                padding: 0.8rem 1.5rem;
            }
            
            .hero-cta {
                flex-direction: column;
                width: 100%;
            }
            
            .categories-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 1rem;
            }
            
            .category-card {
                padding: 1.5rem 1rem;
            }
            
            .products-grid {
                gap: 1.5rem;
            }
            
            .product-image {
                height: 200px;
            }
        }
        
        /* FIXED: Mobile menu overflow prevention for index.php */
        @media (max-width: 768px) {
            .header ~ .hero-carousel,
            .header ~ .categories-section,
            .header ~ .featured-section,
            .header ~ .collections-section {
                position: relative;
                z-index: 1;
            }
            
            body.menu-open {
                overflow: hidden !important;
                position: fixed !important;
                width: 100% !important;
                height: 100% !important;
            }
            
            .nav.active {
                z-index: 1001 !important;
            }
            
            .mobile-menu-overlay.active {
                z-index: 1000 !important;
            }
        }

        /* DYNAMIC Promotional Marquee */
        .promo-marquee {
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--dark) 100%);
            color: white;
            padding: 0.75rem 0;
            overflow: hidden;
            position: relative;
            z-index: 10;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .marquee-content {
            display: flex;
            white-space: nowrap;
            padding: 0 1rem;
            align-items: center;
        }

        .marquee-item {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0 2rem;
            font-weight: 500;
            font-size: 0.95rem;
            white-space: nowrap;
        }

        .marquee-item i {
            font-size: 1rem;
            min-width: 20px;
        }

        .marquee-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            margin-right: 0.5rem;
        }

        @keyframes marquee {
            0% {
                transform: translateX(0);
            }
            100% {
                transform: translateX(-50%);
            }
        }

        /* Responsive marquee */
        @media (max-width: 768px) {
            .promo-marquee {
                padding: 0.5rem 0;
            }
            
            .marquee-item {
                font-size: 0.85rem;
                gap: 0.5rem;
                padding: 0 1.5rem;
            }
            
            .marquee-item i {
                font-size: 0.9rem;
            }
            
            .marquee-badge {
                font-size: 0.7rem;
                padding: 0.2rem 0.5rem;
            }
        }

        @media (max-width: 480px) {
            .marquee-item {
                font-size: 0.8rem;
                padding: 0 1rem;
            }
        }
        .search-btn {
        position: absolute;
        left: 1rem;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        color: var(--gray-500);
        cursor: pointer;
        z-index: 1;
    }
    </style>
</head>
<body>
    <!-- Navigation -->
    <?php 
    if (!file_exists('navigation.php')) {
        echo '
        <nav style="position: fixed; top: 0; left: 0; right: 0; background: white; z-index: 1000; box-shadow: var(--shadow); padding: 1rem 0;">
            <div class="container" style="display: flex; justify-content: space-between; align-items: center;">
                <div style="font-family: \'Playfair Display\', serif; font-size: 1.5rem; font-weight: 700; color: var(--primary);">
                    '.htmlspecialchars($boutique_name).'
                </div>
                <div style="display: flex; gap: 1.5rem; align-items: center;">
                    <a href="index.php" style="color: var(--gray-700); text-decoration: none; font-weight: 500;">Home</a>
                    <a href="products.php" style="color: var(--gray-700); text-decoration: none; font-weight: 500;">Products</a>
                    <a href="cart.php" style="color: var(--gray-700); text-decoration: none; font-weight: 500;">Cart</a>
                    <a href="marquee.php" style="color: var(--gray-700); text-decoration: none; font-weight: 500;">Manage Marquee</a>
                </div>
            </div>
        </nav>';
    } else {
        include 'navigation.php';
    }
    ?>

    <!-- Hero Carousel Section -->
    <section class="hero-carousel">
        <div class="carousel-slides">
            <!-- Slide 1 -->
            <div class="carousel-slide active">
                <img src="https://images.unsplash.com/photo-1445205170230-053b83016050?w=1920&auto=format&fit=crop&q=80" 
                     alt="Premium Fashion Collection" class="slide-bg">
                <div class="slide-overlay"></div>
                <div class="slide-content">
                    <div class="welcome-badge">
                        <i class="fas fa-crown"></i>
                        <span>WELCOME TO <?php echo strtoupper(htmlspecialchars($boutique_name)); ?></span>
                    </div>
                    
                    <h1 class="hero-title">
                        Elevate Your<br>Fashion Style
                    </h1>
                    
                    <p class="hero-subtitle">
                        Exclusive designer collection by <?php echo htmlspecialchars($designer_name); ?>. 
                        Where tradition meets contemporary elegance in every stitch.
                    </p>
                    
                    <div class="hero-cta">
                        <a href="#collections" class="hero-btn primary">
                            <i class="fas fa-compass"></i>
                            Explore Collection
                            <i class="fas fa-arrow-right"></i>
                        </a>
                        
                        <a href="products.php?type=stitched" class="hero-btn secondary">
                            <i class="fas fa-shopping-bag"></i>
                            Shop Now
                            <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Slide 2 -->
            <div class="carousel-slide">
                <img src="https://images.unsplash.com/photo-1515372039744-b8f02a3ae446?w=1920&auto=format&fit=crop&q=80" 
                     alt="Bridal Collection" class="slide-bg">
                <div class="slide-overlay"></div>
                <div class="slide-content">
                    <div class="welcome-badge">
                        <i class="fas fa-gem"></i>
                        <span>EXQUISITE BRIDAL WEAR</span>
                    </div>
                    
                    <h1 class="hero-title">
                        Dream Wedding<br>Collection
                    </h1>
                    
                    <p class="hero-subtitle">
                        Make your special day unforgettable with our handcrafted bridal lehengas 
                        and designer wedding outfits.
                    </p>
                    
                    <div class="hero-cta">
                        <a href="products.php?type=stitched&category=1" class="hero-btn primary">
                            <i class="fas fa-heart"></i>
                            Bridal Collection
                            <i class="fas fa-arrow-right"></i>
                        </a>
                        
                        <a href="products.php?type=stitched&category=2" class="hero-btn secondary">
                            <i class="fas fa-female"></i>
                            Saree Collection
                            <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Slide 3 -->
            <div class="carousel-slide">
                <img src="https://images.unsplash.com/photo-1595777457583-95e059d581b8?w=1920&auto=format&fit=crop&q=80" 
                     alt="Party Wear Collection" class="slide-bg">
                <div class="slide-overlay"></div>
                <div class="slide-content">
                    <div class="welcome-badge">
                        <i class="fas fa-star"></i>
                        <span>TRENDING NOW</span>
                    </div>
                    
                    <h1 class="hero-title">
                        Party Wear<br>Essentials
                    </h1>
                    
                    <p class="hero-subtitle">
                        Stand out at every occasion with our stunning party wear collection. 
                        Perfect for festivals, celebrations, and special events.
                    </p>
                    
                    <div class="hero-cta">
                        <a href="products.php?type=stitched&category=3" class="hero-btn primary">
                            <i class="fas fa-glass-cheers"></i>
                            Party Wear
                            <i class="fas fa-arrow-right"></i>
                        </a>
                        
                        <a href="products.php?type=stitched&category=4" class="hero-btn secondary">
                            <i class="fas fa-tshirt"></i>
                            Anarkalis
                            <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Slide 4 -->
            <div class="carousel-slide">
                <img src="https://images.unsplash.com/photo-1523359346063-d879354c0ea5?w=1920&auto=format&fit=crop&q=80" 
                     alt="Fabrics & Accessories" class="slide-bg">
                <div class="slide-overlay"></div>
                <div class="slide-content">
                    <div class="welcome-badge">
                        <i class="fas fa-cut"></i>
                        <span>CUSTOM TAILORING</span>
                    </div>
                    
                    <h1 class="hero-title">
                        Premium Fabrics<br>& Accessories
                    </h1>
                    
                    <p class="hero-subtitle">
                        Create your perfect outfit with our premium unstitched fabrics 
                        and complete your look with exquisite jewelry.
                    </p>
                    
                    <div class="hero-cta">
                        <a href="products.php?type=unstitched" class="hero-btn primary">
                            <i class="fas fa-cut"></i>
                            Fabrics
                            <i class="fas fa-arrow-right"></i>
                        </a>
                        
                        <a href="products.php?type=accessory" class="hero-btn secondary">
                            <i class="fas fa-gem"></i>
                            Accessories
                            <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Scroll Indicator -->
        <div class="scroll-indicator">
            <a href="#categories">
                <span>Scroll</span>
                <i class="fas fa-chevron-down"></i>
            </a>
        </div>

        <!-- Carousel Dots -->
        <div class="carousel-controls">
            <button class="carousel-dot active" data-slide="0"></button>
            <button class="carousel-dot" data-slide="1"></button>
            <button class="carousel-dot" data-slide="2"></button>
            <button class="carousel-dot" data-slide="3"></button>
        </div>
    </section>

    <!-- Promotional Marquee - DYNAMIC FROM DATABASE -->
    <?php if ($marquee_enabled && !empty($marquee_messages)): ?>
    <div class="promo-marquee">
        <div class="marquee-content" style="animation: marquee <?php echo $marquee_speed; ?>s linear infinite;">
            <?php foreach ($marquee_messages as $msg): ?>
            <div class="marquee-item">
                <?php if ($msg['badge_text']): ?>
                    <span class="marquee-badge" style="background: var(--<?php echo $msg['badge_color']; ?>);">
                        <?php echo htmlspecialchars($msg['badge_text']); ?>
                    </span>
                <?php endif; ?>
                <i class="<?php echo $msg['icon_class']; ?>" style="color: var(--<?php echo $msg['icon_color']; ?>);"></i>
                <span><?php echo htmlspecialchars($msg['message_text']); ?></span>
            </div>
            <?php endforeach; ?>
            <!-- Duplicate for seamless looping -->
            <?php foreach ($marquee_messages as $msg): ?>
            <div class="marquee-item">
                <?php if ($msg['badge_text']): ?>
                    <span class="marquee-badge" style="background: var(--<?php echo $msg['badge_color']; ?>);">
                        <?php echo htmlspecialchars($msg['badge_text']); ?>
                    </span>
                <?php endif; ?>
                <i class="<?php echo $msg['icon_class']; ?>" style="color: var(--<?php echo $msg['icon_color']; ?>);"></i>
                <span><?php echo htmlspecialchars($msg['message_text']); ?></span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php elseif ($marquee_enabled): ?>
    <!-- Default marquee if no messages in database -->
    <div class="promo-marquee">
        <div class="marquee-content" style="animation: marquee <?php echo $marquee_speed; ?>s linear infinite;">
            <div class="marquee-item">
                <span class="marquee-badge" style="background: var(--secondary);">WELCOME</span>
                <i class="fas fa-star" style="color: var(--secondary);"></i>
                <span>Welcome to <?php echo htmlspecialchars($boutique_name); ?> - Premium Designer Boutique</span>
            </div>
            <div class="marquee-item">
                <span class="marquee-badge" style="background: var(--secondary);">SALE</span>
                <i class="fas fa-gift" style="color: var(--secondary);"></i>
                <span>Exclusive offers available - Shop now!</span>
            </div>
            <div class="marquee-item">
                <span class="marquee-badge" style="background: var(--secondary);">PHONE</span>
                <i class="fas fa-phone" style="color: var(--secondary);"></i>
                <span>Call us: <?php echo htmlspecialchars($primary_phone); ?> | <?php echo htmlspecialchars($secondary_phone); ?></span>
            </div>
            <div class="marquee-item">
                <span class="marquee-badge" style="background: var(--secondary);">DESIGNER</span>
                <i class="fas fa-user" style="color: var(--secondary);"></i>
                <span>Designed by <?php echo htmlspecialchars($designer_name); ?></span>
            </div>
            <!-- Duplicate for seamless looping -->
            <div class="marquee-item">
                <span class="marquee-badge" style="background: var(--secondary);">WELCOME</span>
                <i class="fas fa-star" style="color: var(--secondary);"></i>
                <span>Welcome to <?php echo htmlspecialchars($boutique_name); ?> - Premium Designer Boutique</span>
            </div>
            <div class="marquee-item">
                <span class="marquee-badge" style="background: var(--secondary);">SALE</span>
                <i class="fas fa-gift" style="color: var(--secondary);"></i>
                <span>Exclusive offers available - Shop now!</span>
            </div>
            <div class="marquee-item">
                <span class="marquee-badge" style="background: var(--secondary);">PHONE</span>
                <i class="fas fa-phone" style="color: var(--secondary);"></i>
                <span>Call us: <?php echo htmlspecialchars($primary_phone); ?> | <?php echo htmlspecialchars($secondary_phone); ?></span>
            </div>
            <div class="marquee-item">
                <span class="marquee-badge" style="background: var(--secondary);">DESIGNER</span>
                <i class="fas fa-user" style="color: var(--secondary);"></i>
                <span>Designed by <?php echo htmlspecialchars($designer_name); ?></span>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Categories Section -->
    <section class="categories-section section-padding" id="categories">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Shop by Category</h2>
                <p class="section-subtitle">Discover our exquisite collections in 10 different categories</p>
            </div>
            
            <div class="categories-grid">
                <?php foreach ($limited_categories as $category): 
                    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM products WHERE category_id = ? AND is_active = 1");
                    $stmt->bind_param("i", $category['id']);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $count = $result->fetch_assoc()['count'];
                    $stmt->close();
                ?>
                <a href="products.php?category=<?php echo $category['id']; ?>" class="category-card">
                    <div class="category-icon">
                        <?php 
                        // Use the icon from database if available, otherwise fallback to default
                        $icon = !empty($category['icon']) ? htmlspecialchars($category['icon']) : 'fas fa-shopping-bag';
                        ?>
                        <i class="<?php echo $icon; ?>"></i>
                    </div>
                    <div class="category-info">
                        <h3><?php echo htmlspecialchars($category['name']); ?></h3>
                        <p><?php echo $count; ?> items</p>
                    </div>
                    <div class="category-arrow">
                        <i class="fas fa-chevron-right"></i>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Featured Stitched Collection -->
    <section class="featured-section section-padding bg-light" id="collections">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Featured Stitched Collection</h2>
                <p class="section-subtitle">Ready-to-wear designer outfits for every occasion</p>
                <a href="products.php?type=stitched" class="view-all-btn">
                    View All
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            
            <div class="products-grid">
                <?php foreach ($featured_stitched as $product): 
                    $main_image = getMainProductImage($conn, $product['id']);
                    $image_url = $main_image ? $main_image['image_url'] : null;
                    
                    $color_stmt = $conn->prepare("SELECT c.name, c.hex_code FROM product_colors pc 
                                                 JOIN colors c ON pc.color_id = c.id 
                                                 WHERE pc.product_id = ? LIMIT 3");
                    $color_stmt->bind_param("i", $product['id']);
                    $color_stmt->execute();
                    $color_result = $color_stmt->get_result();
                    $colors = [];
                    while ($color = $color_result->fetch_assoc()) {
                        $colors[] = $color;
                    }
                    $color_stmt->close();
                ?>
                <div class="product-card">
                    <div class="product-image">
                        <?php if ($image_url): ?>
                            <img src="<?php echo htmlspecialchars($image_url); ?>" 
                                 alt="<?php echo htmlspecialchars($product['name']); ?>"
                                 loading="lazy">
                        <?php else: ?>
                            <div class="no-image">
                                <i class="fas fa-image"></i>
                            </div>
                        <?php endif; ?>
                        <div class="product-overlay">
                            <div class="overlay-buttons">
                                <a href="product.php?id=<?php echo $product['id']; ?>" class="view-btn">
                                    <i class="fas fa-eye"></i>
                                    Quick View
                                </a>
                                <?php if ($product['stock_quantity'] > 0): ?>
                                <button class="add-to-cart-btn" data-id="<?php echo $product['id']; ?>">
                                    <i class="fas fa-shopping-bag"></i>
                                    Add to Cart
                                </button>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php if ($product['stock_quantity'] == 0): ?>
                            <div class="stock-badge out-of-stock">Out of Stock</div>
                        <?php elseif ($product['stock_quantity'] <= 5): ?>
                            <div class="stock-badge low-stock">Only <?php echo $product['stock_quantity']; ?> left</div>
                        <?php endif; ?>
                    </div>
                    <div class="product-info">
                        <h3 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h3>
                        <div class="product-category"><?php echo htmlspecialchars($product['category_name'] ?? 'Uncategorized'); ?></div>
                        <div class="product-colors">
                            <?php foreach ($colors as $color): ?>
                                <span class="color-dot" style="background-color: <?php echo $color['hex_code']; ?>" 
                                      title="<?php echo htmlspecialchars($color['name']); ?>"></span>
                            <?php endforeach; ?>
                            <?php if (count($colors) > 3): ?>
                                <span class="more-colors">+<?php echo count($colors) - 3; ?> more</span>
                            <?php endif; ?>
                        </div>
                        <div class="product-price-row">
                            <div class="product-price">₹<?php echo number_format($product['price'], 2); ?></div>
                            <?php if ($product['stock_quantity'] > 0): ?>
                                <button class="add-to-cart-btn mobile" data-id="<?php echo $product['id']; ?>">
                                    <i class="fas fa-shopping-bag"></i>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Collections Section -->
    <section class="collections-section section-padding">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Our Collections</h2>
                <p class="section-subtitle">Explore our complete range of designer wear</p>
            </div>
            
            <div class="collections-grid">
                <!-- Stitched Collection -->
                <div class="collection-card stitched">
                    <div class="collection-image">
                        <img src="https://images.unsplash.com/photo-1594633312681-425c7b97ccd1?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80" 
                             alt="Stitched Collection" loading="lazy">
                    </div>
                    <div class="collection-content">
                        <h3>Stitched Clothes</h3>
                        <p>Ready-to-wear designer outfits including lehengas, sarees, suits, and more.</p>
                        <a href="products.php?type=stitched" class="collection-link">
                            Shop Now
                            <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>

                <!-- Unstitched Collection -->
                <div class="collection-card unstitched">
                    <div class="collection-image">
                        <img src="https://images.unsplash.com/photo-1523359346063-d879354c0ea5?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80" 
                             alt="Unstitched Collection" loading="lazy">
                    </div>
                    <div class="collection-content">
                        <h3>Unstitched Fabrics</h3>
                        <p>Premium fabrics and materials for custom tailoring.</p>
                        <a href="products.php?type=unstitched" class="collection-link">
                            Shop Now
                            <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>

                <!-- Accessories Collection -->
                <div class="collection-card accessories">
                    <div class="collection-image">
                        <img src="https://images.unsplash.com/photo-1535632066927-ab7c9ab60908?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80" 
                             alt="Accessories Collection" loading="lazy">
                    </div>
                    <div class="collection-content">
                        <h3>Accessories</h3>
                        <p>Complete your look with our designer jewelry and accessories.</p>
                        <a href="products.php?type=accessory" class="collection-link">
                            Shop Now
                            <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer Section -->
    <?php 
    $footer_vars = [
        'boutique_name' => $boutique_name,
        'designer_name' => $designer_name,
        'location' => $location,
        'primary_phone' => $primary_phone,
        'secondary_phone' => $secondary_phone,
        'instagram_url' => $instagram_url,
        'facebook_url' => $facebook_url,
        'show_social_links' => $show_social_links
    ];
    
    if (file_exists('footer.php')) {
        extract($footer_vars);
        include 'footer.php';
    } else {
        echo '
        <footer style="background: var(--dark); color: white; padding: 3rem 0; margin-top: 3rem;">
            <div class="container">
                <div style="text-align: center;">
                    <div style="font-family: \'Playfair Display\', serif; font-size: 1.5rem; margin-bottom: 1rem;">
                        '.htmlspecialchars($boutique_name).'
                    </div>
                    <p style="color: rgba(255,255,255,0.8); margin-bottom: 1.5rem;">
                        Designed by '.htmlspecialchars($designer_name).'
                    </p>
                    <div style="color: rgba(255,255,255,0.6); font-size: 0.9rem;">
                        &copy; '.date('Y').' '.htmlspecialchars($boutique_name).'. All rights reserved.
                    </div>
                </div>
            </div>
        </footer>';
    }
    ?>

    <!-- Scripts -->
    <script>
        // Make PHP variables available to JavaScript
        const primaryPhone = '<?php echo htmlspecialchars($primary_phone); ?>';
        const secondaryPhone = '<?php echo htmlspecialchars($secondary_phone); ?>';
        
        // Carousel functionality
        let currentSlide = 0;
        const slides = document.querySelectorAll('.carousel-slide');
        const dots = document.querySelectorAll('.carousel-dot');
        let slideInterval;

        function showSlide(n) {
            slides.forEach(slide => slide.classList.remove('active'));
            dots.forEach(dot => dot.classList.remove('active'));
            
            slides[n].classList.add('active');
            dots[n].classList.add('active');
            currentSlide = n;
        }

        function nextSlide() {
            let next = currentSlide + 1;
            if (next >= slides.length) next = 0;
            showSlide(next);
        }

        function startCarousel() {
            slideInterval = setInterval(nextSlide, 5000);
        }

        function stopCarousel() {
            clearInterval(slideInterval);
        }

        // Initialize carousel
        document.addEventListener('DOMContentLoaded', function() {
            showSlide(0);
            startCarousel();
            
            dots.forEach((dot, index) => {
                dot.addEventListener('click', function() {
                    stopCarousel();
                    showSlide(index);
                    startCarousel();
                });
            });
            
            const carousel = document.querySelector('.hero-carousel');
            carousel.addEventListener('mouseenter', stopCarousel);
            carousel.addEventListener('mouseleave', startCarousel);
        });

        // Add to cart functionality
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('add-to-cart-btn') || e.target.closest('.add-to-cart-btn')) {
                const button = e.target.classList.contains('add-to-cart-btn') ? e.target : e.target.closest('.add-to-cart-btn');
                const productId = button.dataset.id;
                
                const card = button.closest('.product-card');
                const productName = card.querySelector('.product-title').textContent;
                
                const priceText = card.querySelector('.product-price').textContent;
                const productPrice = parsePrice(priceText);
                
                const productImage = card.querySelector('img')?.src || 'assets/images/no-image.jpg';
                
                addToCart({
                    id: productId,
                    name: productName,
                    price: productPrice,
                    image: productImage,
                    quantity: 1
                });
                
                showNotification(`${productName} added to cart!`);
                
                button.innerHTML = '<i class="fas fa-check"></i> Added';
                button.classList.add('added');
                button.disabled = true;
                setTimeout(() => {
                    button.innerHTML = '<i class="fas fa-shopping-bag"></i> Add to Cart';
                    button.classList.remove('added');
                    button.disabled = false;
                }, 2000);
            }
        });

        function parsePrice(priceText) {
            let cleaned = priceText.replace(/[₹$€£]/g, '');
            cleaned = cleaned.replace(/,/g, '');
            cleaned = cleaned.trim();
            const price = parseFloat(cleaned);
            return isNaN(price) ? 0 : price;
        }

        // --- REPLACE THE EXISTING addToCart FUNCTION ---
function addToCart(product) {
    // First, send an AJAX request to update the server-side cart
    fetch('cart_handler.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            action: 'add',
            id: product.id,
            quantity: product.quantity,
            name: product.name,
            price: product.price,
            image: product.image,
            color: product.color, // Include color if relevant
            productType: product.productType, // Include type if relevant
            isUnstitched: product.isUnstitched,
            selectedMeters: product.selectedMeters,
            stitchingOption: product.stitchingOption,
            stitchCharges: product.stitchCharges
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update localStorage with the server's cart state (optional, but good practice for consistency)
            // localStorage.setItem('cart', JSON.stringify(data.cart)); // Server response might not always include full cart
            // Or, just update local storage optimistically and rely on sync via event
            let cart = JSON.parse(localStorage.getItem('cart')) || [];
            const existingItem = cart.find(item => item.id === product.id);
            if (existingItem) {
                existingItem.quantity += product.quantity;
            } else {
                cart.push(product);
            }
            localStorage.setItem('cart', JSON.stringify(cart));

            // Dispatch the event to notify navigation.php to update its display
            window.dispatchEvent(new CustomEvent('cartUpdated'));
            console.log("Cart updated via AJAX and event dispatched.");
        } else {
            console.error("Server error:", data.message);
            // Fallback: update localStorage only if server fails
            let cart = JSON.parse(localStorage.getItem('cart')) || [];
            const existingItem = cart.find(item => item.id === product.id);
            if (existingItem) {
                existingItem.quantity += product.quantity;
            } else {
                cart.push(product);
            }
            localStorage.setItem('cart', JSON.stringify(cart));
            updateCartCount(); // Update local count
        }
    })
    .catch(error => {
        console.error("AJAX error:", error);
        // Fallback: update localStorage only if request fails
        let cart = JSON.parse(localStorage.getItem('cart')) || [];
        const existingItem = cart.find(item => item.id === product.id);
        if (existingItem) {
            existingItem.quantity += product.quantity;
        } else {
            cart.push(product);
        }
        localStorage.setItem('cart', JSON.stringify(cart));
        updateCartCount(); // Update local count
    });
}
// --- END REPLACE ---
// Shared cart sync for all pages
function updateCartCount() {
    fetch('/cart_handler.php?action=get_cart')
        .then(async r => {
            const text = await r.text();
            try {
                const data = JSON.parse(text);
                if (data.success) {
                    const count = data.item_count || 0;
                    const el = document.querySelector('.cart-count');
                    if (el) {
                        el.textContent = count;
                        el.style.display = count > 0 ? 'flex' : 'none';
                    }
                    // Sync localStorage for offline fallback
                    localStorage.setItem('cart', JSON.stringify(data.cart || []));
                }
            } catch (e) {
                console.warn('Cart sync failed, falling back to localStorage');
                const cart = JSON.parse(localStorage.getItem('cart')) || [];
                const count = cart.reduce((s, i) => s + i.quantity, 0);
                const el = document.querySelector('.cart-count');
                if (el) {
                    el.textContent = count;
                    el.style.display = count > 0 ? 'flex' : 'none';
                }
            }
        })
        .catch(err => {
            console.error('Cart sync error:', err);
        });
}

// Call on every page load
document.addEventListener('DOMContentLoaded', updateCartCount);

// Listen for cart changes (e.g., add/remove on other pages)
window.addEventListener('cartUpdated', updateCartCount);

        function showNotification(message) {
            const notification = document.createElement('div');
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: var(--primary);
                color: white;
                padding: 1rem 1.5rem;
                border-radius: var(--radius);
                box-shadow: var(--shadow-lg);
                z-index: 9999;
                display: flex;
                align-items: center;
                gap: 0.75rem;
                transform: translateX(150%);
                transition: transform 0.3s ease;
            `;
            notification.innerHTML = `
                <i class="fas fa-check-circle"></i>
                <span>${message}</span>
            `;
            document.body.appendChild(notification);
            
            setTimeout(() => notification.style.transform = 'translateX(0)', 10);
            setTimeout(() => {
                notification.style.transform = 'translateX(150%)';
                setTimeout(() => notification.remove(), 300);
            }, 3000);
        }

        // Initialize cart on page load
        document.addEventListener('DOMContentLoaded', function() {
            updateCartCount();
        });

        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                const href = this.getAttribute('href');
                if (href !== '#') {
                    e.preventDefault();
                    const target = document.querySelector(href);
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                }
            });
        });
    </script>
</body>
</html>
<?php
$conn->close();
?>