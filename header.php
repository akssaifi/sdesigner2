<!-- [file name]: header.php
[file content begin] -->
<?php
$boutique_name = getSetting($conn, 'boutique_name') ?? 'SDesigner Boutique';
$designer_name = getSetting($conn, 'designer_name') ?? 'Dinky Ahuja';
?>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo htmlspecialchars($boutique_name); ?> - Designer Boutique</title>
<meta name="description" content="Exclusive designer clothing and accessories by <?php echo htmlspecialchars($designer_name); ?>">
<meta name="keywords" content="designer boutique, ethnic wear, lehengas, sarees, bridal wear, <?php echo htmlspecialchars($designer_name); ?>">

<!-- Favicon -->
<link rel="icon" type="image/x-icon" href="assets/favicon.ico">

<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<!-- Google Fonts -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<!-- Compiled CSS from SCSS -->
<style>
/* CSS Variables */
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
/* Reset & Base Styles */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

html {
    scroll-behavior: smooth;
}

body {
    font-family: 'Poppins', sans-serif;
    color: var(--text-color);
    line-height: 1.6;
    overflow-x: hidden;
}

h1, h2, h3, h4, h5, h6 {
    font-family: 'Playfair Display', serif;
    font-weight: 600;
    line-height: 1.3;
}

a {
    text-decoration: none;
    color: inherit;
    transition: var(--transition);
}

button {
    border: none;
    background: none;
    cursor: pointer;
    font-family: inherit;
}

img {
    max-width: 100%;
    height: auto;
    display: block;
}

.container {
    width: 100%;
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
}

/* Utility Classes */
.section-padding {
    padding: 80px 0;
}

.bg-light {
    background-color: var(--bg-light);
}

.text-center {
    text-align: center;
}

/* Buttons */
.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 12px 28px;
    border-radius: 4px;
    font-weight: 500;
    font-size: 16px;
    transition: var(--transition);
    gap: 8px;
}

.btn-primary {
    background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
    color: white;
    border: 2px solid var(--primary-color);
}

.btn-primary:hover {
    background: linear-gradient(135deg, var(--primary-light), var(--primary-color));
    transform: translateY(-2px);
    box-shadow: var(--shadow);
}

.btn-secondary {
    background: transparent;
    color: var(--primary-color);
    border: 2px solid var(--primary-color);
}

.btn-secondary:hover {
    background: var(--primary-color);
    color: white;
    transform: translateY(-2px);
}

/* Header & Navigation */
.header {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    background: #fafcfb;
    backdrop-filter: blur(10px);
    z-index: 1000;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    transition: var(--transition);
}

.header.scrolled {
    position: sticky;
    padding: 10px 0;
    background: rgba(255, 255, 255, 0.98);
}

.header-container {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 20px 0;
    transition: var(--transition);
}

.header.scrolled .header-container {
    padding: 10px 0;
}

.logo {
    display: flex;
    align-items: center;
    gap: 10px;
}

.logo-icon {
    font-size: 24px;
    color: var(--primary-color);
}

.logo-text {
    font-family: 'Playfair Display', serif;
    font-size: 24px;
    font-weight: 700;
    color: var(--dark-color);
}

.logo-subtext {
    font-size: 12px;
    color: var(--primary-color);
    letter-spacing: 1px;
    text-transform: uppercase;
}

.nav-links {
    display: flex;
    gap: 40px;
    list-style: none;
}

.nav-link {
    font-weight: 500;
    color: var(--text-color);
    position: relative;
    padding: 5px 0;
}

.nav-link::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 0;
    height: 2px;
    background: var(--primary-color);
    transition: var(--transition);
}

.nav-link:hover::after,
.nav-link.active::after {
    width: 100%;
}

.nav-link:hover,
.nav-link.active {
    color: var(--primary-color);
}

.header-actions {
    display: flex;
    align-items: center;
    gap: 20px;
}

.search-box {
    position: relative;
}

.search-box input {
    padding: 10px 40px 10px 15px;
    border: 1px solid var(--border-color);
    border-radius: 4px;
    width: 200px;
    transition: var(--transition);
}

.search-box input:focus {
    outline: none;
    border-color: var(--primary-color);
    width: 250px;
}

.search-btn {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    color: var(--text-light);
}

.search-results {
    position: absolute;
    top: 100%;
    left: 0;
    width: 300px;
    background: white;
    border: 1px solid var(--border-color);
    border-radius: 4px;
    box-shadow: var(--shadow);
    display: none;
    z-index: 1000;
}

.search-result-item {
    display: flex;
    align-items: center;
    padding: 10px;
    gap: 10px;
    border-bottom: 1px solid var(--border-color);
}

.search-result-item:hover {
    background: var(--bg-light);
}

.search-result-item img {
    width: 50px;
    height: 50px;
    object-fit: cover;
    border-radius: 4px;
}

.cart-toggle {
    position: relative;
    font-size: 20px;
    color: var(--dark-color);
}

.cart-count {
    position: absolute;
    top: -8px;
    right: -8px;
    background: var(--primary-color);
    color: white;
    font-size: 12px;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.cart-dropdown {
    position: absolute;
    top: 100%;
    right: 0;
    width: 320px;
    background: white;
    border: 1px solid var(--border-color);
    border-radius: 4px;
    box-shadow: var(--shadow);
    padding: 20px;
    display: none;
    z-index: 1000;
}

.cart-dropdown.active {
    display: block;
}

.cart-items {
    max-height: 300px;
    overflow-y: auto;
    margin-bottom: 20px;
}

.cart-item {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 10px 0;
    border-bottom: 1px solid var(--border-color);
}

.cart-item img {

    object-fit: cover;
    border-radius: 4px;
}

.cart-item-info h4 {
    font-size: 14px;
    margin-bottom: 5px;
    font-family: 'Poppins', sans-serif;
}

.cart-item-price {
    display: flex;
    justify-content: space-between;
    font-size: 14px;
    color: var(--primary-color);
}

.remove-item {
    margin-left: auto;
    color: var(--danger-color);
    font-size: 18px;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
}

.remove-item:hover {
    background: var(--danger-color);
    color: white;
}

.cart-total {
    display: flex;
    justify-content: space-between;
    font-weight: 600;
    font-size: 18px;
    padding-top: 15px;
    border-top: 2px solid var(--border-color);
}

.cart-actions {
    display: flex;
    gap: 10px;
    margin-top: 15px;
}

.cart-actions .btn {
    flex: 1;
    padding: 10px;
    font-size: 14px;
}

.menu-toggle {
    display: none;
    font-size: 24px;
    color: var(--dark-color);
}

/* Hero Section */
.hero-section {
    position: relative;
    height: 90vh;
    min-height: 600px;
    background: linear-gradient(rgba(44, 24, 16, 0.7), rgba(44, 24, 16, 0.7)),
                url('assets/images/hero-bg.jpg');
    background-size: cover;
    background-position: center;
    display: flex;
    align-items: center;
    color: white;
    margin-top: 80px;
}

.hero-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(45deg, rgba(139, 69, 19, 0.3), rgba(212, 167, 106, 0.2));
}

.hero-content {
    position: relative;
    z-index: 1;
    text-align: center;
}

.hero-title {
    font-size: 4rem;
    font-weight: 700;
    margin-bottom: 20px;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
}

.hero-subtitle {
    font-size: 1.5rem;
    margin-bottom: 30px;
    opacity: 0.9;
}

.hero-location {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    font-size: 1.1rem;
    margin-bottom: 40px;
    background: rgba(255, 255, 255, 0.1);
    padding: 10px 20px;
    border-radius: 30px;
    backdrop-filter: blur(5px);
}

.hero-btn {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    background: var(--secondary-color);
    color: var(--dark-color);
    padding: 15px 40px;
    border-radius: 30px;
    font-weight: 600;
    font-size: 1.1rem;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.hero-btn:hover {
    background: var(--accent-color);
    transform: translateY(-3px);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
}

/* Section Styles */
.section-header {
    text-align: center;
    margin-bottom: 60px;
}

.section-title {
    font-size: 2.5rem;
    color: var(--dark-color);
    margin-bottom: 15px;
}

.section-subtitle {
    font-size: 1.1rem;
    color: var(--text-light);
    max-width: 600px;
    margin: 0 auto;
}

.view-all-btn {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    color: var(--primary-color);
    font-weight: 500;
    margin-top: 20px;
}

.view-all-btn:hover {
    gap: 10px;
}

/* Categories Grid */
.categories-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 30px;
}

.category-card {
    text-align: center;
    padding: 40px 20px;
    background: white;
    border-radius: 10px;
    box-shadow: var(--shadow);
    transition: var(--transition);
}

.category-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
}

.category-icon {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, var(--primary-light), var(--primary-color));
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
    color: white;
    font-size: 30px;
}

.category-card h3 {
    font-size: 1.3rem;
    margin-bottom: 10px;
    color: var(--dark-color);
}

.category-card p {
    color: var(--text-light);
    font-size: 0.9rem;
}

/* Products Grid */
.products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 30px;
}

.product-card {
    background: white;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: var(--shadow);
    transition: var(--transition);
}

.product-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
}

.product-image {
    position: relative;
    height: 300px;
    overflow: hidden;
}

.product-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}

.product-card:hover .product-image img {
    transform: scale(1.05);
}

.no-image {
    width: 100%;
    height: 100%;
    background: var(--bg-light);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--text-light);
    font-size: 3rem;
}

.product-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.3);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: var(--transition);
}

.product-card:hover .product-overlay {
    opacity: 1;
}

.view-btn {
    background: white;
    color: var(--dark-color);
    padding: 12px 25px;
    border-radius: 30px;
    font-weight: 500;
    transform: translateY(20px);
    transition: var(--transition);
}

.product-card:hover .view-btn {
    transform: translateY(0);
}

.stock-badge {
    position: absolute;
    top: 15px;
    right: 15px;
    padding: 5px 10px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 500;
    color: white;
}

.stock-badge.out-of-stock {
    background: var(--danger-color);
}

.stock-badge.low-stock {
    background: var(--warning-color);
}

.product-info {
    padding: 20px;
}

.product-title {
    font-size: 1.1rem;
    margin-bottom: 5px;
    color: var(--dark-color);
    font-family: 'Poppins', sans-serif;
    font-weight: 500;
}

.product-category {
    font-size: 0.9rem;
    color: var(--text-light);
    margin-bottom: 10px;
}

.product-price {
    font-size: 1.3rem;
    font-weight: 600;
    color: var(--primary-color);
}

/* Banner Section */
.banner-section {
    background: linear-gradient(rgba(44, 24, 16, 0.8), rgba(44, 24, 16, 0.8)),
                url('assets/images/banner-bg.jpg');
    background-size: cover;
    background-position: center;
    padding: 100px 0;
    color: white;
    text-align: center;
}

.banner-content h2 {
    font-size: 3rem;
    margin-bottom: 20px;
}

.banner-content p {
    font-size: 1.2rem;
    margin-bottom: 40px;
    opacity: 0.9;
}

.banner-btn {
    display: inline-block;
    background: var(--secondary-color);
    color: var(--dark-color);
    padding: 15px 40px;
    border-radius: 30px;
    font-weight: 600;
    font-size: 1.1rem;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.banner-btn:hover {
    background: var(--accent-color);
    transform: translateY(-3px);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
}

/* Collections Section */
.collections-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
    gap: 40px;
}

.collection-card {
    background: white;
    border-radius: 10px;
    padding: 30px;
    box-shadow: var(--shadow);
}

.collection-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    padding-bottom: 15px;
    border-bottom: 2px solid var(--border-color);
}

.collection-header h3 {
    font-size: 1.8rem;
    color: var(--dark-color);
}

.collection-link {
    color: var(--primary-color);
    font-weight: 500;
}

.collection-link:hover {
    text-decoration: underline;
}

.collection-products {
    display: grid;
    gap: 20px;
}

.collection-product {
    display: flex;
    align-items: center;
    gap: 20px;
    padding: 15px;
    border-radius: 8px;
    transition: var(--transition);
}

.collection-product:hover {
    background: var(--bg-light);
}

.collection-product img {
    width: 80px;
    height: 80px;
    object-fit: cover;
    border-radius: 8px;
}

.no-image-small {
    width: 80px;
    height: 80px;
    background: var(--bg-light);
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--text-light);
    font-size: 1.5rem;
}

.collection-product-info h4 {
    font-size: 1rem;
    margin-bottom: 5px;
    font-family: 'Poppins', sans-serif;
    font-weight: 500;
}

.collection-product-info .price {
    font-size: 1.1rem;
    font-weight: 600;
    color: var(--primary-color);
}

/* About Section */
.about-content {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 60px;
    align-items: center;
}

.about-image {
    border-radius: 10px;
    overflow: hidden;
    box-shadow: var(--shadow);
}

.about-image img {
    width: 100%;
    height: 500px;
    object-fit: cover;
}

.about-text h2 {
    margin-bottom: 10px;
}

.about-text h3 {
    font-size: 2rem;
    color: var(--primary-color);
    margin-bottom: 20px;
}

.about-text p {
    margin-bottom: 20px;
    color: var(--text-color);
}

.about-contact {
    margin-top: 30px;
    display: grid;
    gap: 20px;
}

.contact-item {
    display: flex;
    align-items: flex-start;
    gap: 15px;
}

.contact-item i {
    font-size: 20px;
    color: var(--primary-color);
    margin-top: 5px;
}

.contact-item strong {
    display: block;
    margin-bottom: 5px;
    color: var(--dark-color);
}

/* Footer */
.footer {
    background: var(--dark-color);
    color: white;
    padding: 80px 0 30px;
}

.footer-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 40px;
    margin-bottom: 60px;
}

.footer-logo {
    font-family: 'Playfair Display', serif;
    font-size: 28px;
    font-weight: 700;
    margin-bottom: 15px;
}

.footer-tagline {
    color: var(--accent-color);
    margin-bottom: 20px;
    line-height: 1.8;
}

.footer-social {
    display: flex;
    gap: 15px;
}

.social-link {
    width: 40px;
    height: 40px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: var(--transition);
}

.social-link:hover {
    background: var(--primary-color);
    transform: translateY(-3px);
}

.footer-heading {
    font-size: 1.3rem;
    margin-bottom: 25px;
    color: var(--secondary-color);
}

.footer-links {
    list-style: none;
}

.footer-links li {
    margin-bottom: 12px;
}

.footer-links a {
    color: rgba(255, 255, 255, 0.8);
    transition: var(--transition);
}

.footer-links a:hover {
    color: var(--secondary-color);
    padding-left: 5px;
}

.contact-info {
    display: grid;
    gap: 20px;
}

.contact-info div {
    display: flex;
    align-items: flex-start;
    gap: 15px;
}

.contact-info i {
    color: var(--secondary-color);
    margin-top: 5px;
}

.copyright {
    text-align: center;
    padding-top: 30px;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    color: rgba(255, 255, 255, 0.6);
    font-size: 0.9rem;
}

/* Notification */
.notification {
    position: fixed;
    bottom: 30px;
    right: 30px;
    background: var(--success-color);
    color: white;
    padding: 15px 25px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    gap: 10px;
    box-shadow: var(--shadow);
    transform: translateY(100px);
    opacity: 0;
    transition: var(--transition);
    z-index: 2000;
}

.notification.show {
    transform: translateY(0);
    opacity: 1;
}

/* Responsive Design */
@media (max-width: 992px) {
    .hero-title {
        font-size: 3rem;
    }
    
    .section-title {
        font-size: 2rem;
    }
    
    .collections-grid {
        grid-template-columns: 1fr;
    }
    
    .collections-grid {
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    }
}

@media (max-width: 768px) {
    /* .menu-toggle {
        display: block;
    }
    
    .nav-links {
        position: fixed;
        top: 80px;
        left: 0;
        width: 100%;
        background: white;
        flex-direction: column;
        padding: 20px;
        box-shadow: var(--shadow);
        transform: translateY(-100%);
        opacity: 0;
        visibility: hidden;
        transition: var(--transition);
        gap: 0;
    }
    
    .nav-links.active {
        transform: translateY(0);
        opacity: 1;
        visibility: visible;
    }
     */
    .nav-link {
        padding: 15px 0;
        border-bottom: 1px solid var(--border-color);
        width: 100%;
    }
    
    .nav-link:last-child {
        border-bottom: none;
    }
    
    .search-box input:focus {
        width: 200px;
    }
    
    .cart-dropdown {
        width: 280px;
        right: -50px;
    }
    
    .hero-section {
        height: 50vh;
        min-height: 500px;
    }
    
    .hero-title {
        font-size: 2.5rem;
    }
    
    .hero-subtitle {
        font-size: 1.2rem;
    }
    
    .section-padding {
        padding: 60px 0;
    }
}

@media (max-width: 576px) {
    .container {
        padding: 0 15px;
    }
    
    .header-container {
        padding: 15px 0;
    }
    
    .hero-title {
        font-size: 2rem;
    }
    
    .hero-subtitle {
        font-size: 1rem;
    }
    
    .hero-btn {
        padding: 12px 30px;
        font-size: 1rem;
    }
    
    .section-title {
        font-size: 1.8rem;
    }
    
    .products-grid {
        grid-template-columns: 1fr;
    }
    
    .categories-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 15px;
    }
    
    .category-card {
        padding: 25px 15px;
    }
    
    .banner-content h2 {
        font-size: 2rem;
    }
    
    .footer-grid {
        grid-template-columns: 1fr;
        gap: 30px;
    }
}
/* Add to header.php CSS */
/* Hero Section Styles */
.hero-section {
    position: relative;
    height: 100vh;
    min-height: 700px;
    background: linear-gradient(135deg, #2C1810 0%, #5D2906 50%, #8B4513 100%);
    display: flex;
    align-items: center;
    color: white;
    margin-top: 80px;
    overflow: hidden;
}

.hero-pattern {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-image: url("data:image/svg+xml,%3Csvg width='100' height='100' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M11 18c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm48 25c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm-43-7c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm63 31c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM34 90c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm56-76c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM12 86c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm28-65c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm23-11c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-6 60c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm29 22c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zM32 63c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm57-13c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-9-21c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM60 91c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM35 41c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM12 60c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2z' fill='%238B4513' fill-opacity='0.1' fill-rule='evenodd'/%3E%3C/svg%3E");
    opacity: 0.3;
}

.hero-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(45deg, rgba(139, 69, 19, 0.3), rgba(212, 167, 106, 0.2));
}

.hero-content {
    position: relative;
    z-index: 1;
    width: 100%;
}

.hero-text-wrapper {
    max-width: 800px;
    padding: 40px;
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    border-radius: 20px;
    border: 1px solid rgba(255, 255, 255, 0.2);
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
}

.hero-subtitle {
    display: block;
    font-size: 1.2rem;
    color: var(--secondary-color);
    margin-bottom: 10px;
    letter-spacing: 2px;
    text-transform: uppercase;
}

.hero-title {
    font-size: 4.5rem;
    font-weight: 700;
    margin-bottom: 25px;
    line-height: 1.1;
    text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
    background: linear-gradient(45deg, #FFFFFF, var(--secondary-color));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.hero-description {
    font-size: 1.3rem;
    margin-bottom: 30px;
    color: rgba(255, 255, 255, 0.9);
    line-height: 1.8;
    max-width: 600px;
}

.hero-location {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    font-size: 1.1rem;
    margin-bottom: 40px;
    background: rgba(255, 255, 255, 0.15);
    padding: 12px 25px;
    border-radius: 50px;
    backdrop-filter: blur(5px);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.hero-location i {
    color: var(--secondary-color);
}

.hero-buttons {
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
}

.hero-btn {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 18px 35px;
    border-radius: 50px;
    font-weight: 600;
    font-size: 1.1rem;
    text-transform: uppercase;
    letter-spacing: 1px;
    transition: all 0.3s ease;
    border: 2px solid transparent;
}

.hero-btn.primary {
    background: linear-gradient(135deg, var(--secondary-color), var(--accent-color));
    color: var(--dark-color);
}

.hero-btn.primary:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 30px rgba(212, 167, 106, 0.3);
    border-color: var(--secondary-color);
}

.hero-btn.secondary {
    background: transparent;
    color: white;
    border: 2px solid rgba(255, 255, 255, 0.3);
}

.hero-btn.secondary:hover {
    background: rgba(255, 255, 255, 0.1);
    transform: translateY(-5px);
    border-color: var(--secondary-color);
}

/* Collections Section */
.collections-section {
    padding: 100px 0;
}

.collections-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 30px;
}

.collection-card {
    position: relative;
    border-radius: 15px;
    overflow: hidden;
    height: 400px;
    transition: transform 0.3s ease;
}

.collection-card:hover {
    transform: translateY(-10px);
}

.collection-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(to bottom, transparent 0%, rgba(0, 0, 0, 0.7) 100%);
    z-index: 1;
}

.collection-card.stitched::before {
    background: linear-gradient(to bottom, transparent 0%, rgba(139, 69, 19, 0.7) 100%);
}

.collection-card.unstitched::before {
    background: linear-gradient(to bottom, transparent 0%, rgba(84, 139, 19, 0.7) 100%);
}

.collection-card.accessories::before {
    background: linear-gradient(to bottom, transparent 0%, rgba(139, 19, 84, 0.7) 100%);
}

.collection-image {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
}

.collection-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}

.collection-card:hover .collection-image img {
    transform: scale(1.05);
}

.collection-content {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    padding: 40px;
    z-index: 2;
    color: white;
}

.collection-content h3 {
    font-size: 2rem;
    margin-bottom: 15px;
    color: white;
}

.collection-content p {
    font-size: 1.1rem;
    margin-bottom: 25px;
    opacity: 0.9;
    max-width: 300px;
}

.collection-link {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    color: white;
    font-weight: 600;
    font-size: 1.1rem;
    padding: 12px 25px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 30px;
    backdrop-filter: blur(10px);
    transition: all 0.3s ease;
}

.collection-link:hover {
    background: var(--secondary-color);
    color: var(--dark-color);
    gap: 15px;
}

/* Product card enhancements */
.product-colors {
    display: flex;
    align-items: center;
    gap: 8px;
    margin: 10px 0;
}

.color-dot {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    border: 2px solid white;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
}

.more-colors {
    font-size: 0.8rem;
    color: var(--text-light);
}

.product-price-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.add-to-cart-btn.mobile {
    display: none;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: var(--primary-color);
    color: white;
    align-items: center;
    justify-content: center;
}

.add-to-cart-btn.added {
    background: var(--success-color);
}

/* Responsive design for hero */
@media (max-width: 768px) {
    .hero-section {
        height: 80vh;
        min-height: 600px;
    }
    
    .hero-title {
        font-size: 2.8rem;
    }
    
    .hero-description {
        font-size: 1.1rem;
    }
    
    .hero-text-wrapper {
        padding: 30px 20px;
        margin: 0 15px;
    }
    
    .hero-buttons {
        flex-direction: column;
    }
    
    .hero-btn {
        width: 100%;
        justify-content: center;
    }
    
    .collections-grid {
        grid-template-columns: 1fr;
    }
    
    .add-to-cart-btn.mobile {
        display: flex;
    }
}
</style>
