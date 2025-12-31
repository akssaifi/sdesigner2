<?php
// Get boutique name from settings if available
$boutique_name = isset($boutique_name) ? $boutique_name : 'SDesigner Boutique';
?>
<style>
    /* Header Styles - COMPATIBLE WITH ALL PAGES */
    .header {
        /* --- MODIFICATION: Made sticky and always visible --- */
        position: sticky;
        top: 0;
        z-index: 1000;
        /* --- END MODIFICATION --- */
        background: white;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        /* Removed: fixed positioning and scroll hiding logic */
        transition: var(--transition);
    }

    /* --- REMOVED: .header.scrolled and scroll hiding logic --- */
    .header-container {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1rem 0;
        height: 80px;
        position: relative;
    }

    /* Logo Styles */
    .logo {
        display: flex;
        align-items: center;
        text-decoration: none;
        gap: 1rem;
        z-index: 1002;
    }

    .logo-img {
        height: 70px;
        width: auto;
        object-fit: contain;
        transition: var(--transition);
    }

    .logo:hover .logo-img {
        transform: scale(1.05);
    }

    .logo-text {
        display: flex;
        flex-direction: column;
    }

    .logo-name {
        font-family: 'Playfair Display', serif;
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--primary);
        line-height: 1.2;
    }

    .logo-tagline {
        font-size: 0.8rem;
        color: var(--gray-600);
        font-weight: 500;
        letter-spacing: 1px;
    }

    /* Navigation Menu - DESKTOP */
    .nav {
        flex: 1;
        display: flex;
        justify-content: center;
    }

    .nav-links {
        display: flex;
        list-style: none;
        gap: 2rem;
        margin: 0;
        padding: 0;
    }

    .nav-link {
        text-decoration: none;
        color: var(--gray-700);
        font-weight: 500;
        font-size: 1rem;
        padding: 0.5rem 0;
        position: relative;
        transition: var(--transition);
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }

    .nav-link:hover,
    .nav-link.active {
        color: var(--primary);
    }

    .nav-link::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 0;
        height: 2px;
        background: var(--primary);
        transition: var(--transition);
    }

    .nav-link:hover::after,
    .nav-link.active::after {
        width: 100%;
    }

    /* FIXED: Dropdown Menu - NOW WORKING ON HOVER */
    .nav-dropdown {
        position: relative;
    }

    .dropdown-menu {
        position: absolute;
        top: 100%;
        left: 50%;
        transform: translateX(-50%) translateY(10px);
        width: 800px;
        background: white;
        border: 1px solid var(--gray-200);
        border-radius: var(--radius);
        box-shadow: var(--shadow-lg);
        padding: 2rem;
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 2rem;
        z-index: 1001;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
        pointer-events: none;
    }

    /* FIXED: Show dropdown on hover */
    .nav-dropdown:hover .dropdown-menu {
        opacity: 1;
        visibility: visible;
        pointer-events: auto;
    }

    /* FIXED: Keep dropdown open when hovering over it */
    .dropdown-menu:hover {
        opacity: 1;
        visibility: visible;
        transform: translateX(-50%) translateY(0);
    }

    .dropdown-column h4 {
        font-size: 1.1rem;
        color: var(--dark);
        margin-bottom: 1rem;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid var(--primary);
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .dropdown-column a {
        display: block;
        padding: 0.5rem 0;
        color: var(--gray-600);
        text-decoration: none;
        transition: var(--transition);
        font-size: 0.95rem;
    }

    .dropdown-column a:hover {
        color: var(--primary);
        padding-left: 0.5rem;
    }

    .dropdown-divider {
        height: 1px;
        background: var(--gray-200);
        margin: 1rem 0;
    }

    /* Header Actions */
    .header-actions {
        display: flex;
        align-items: center;
        gap: 1.5rem;
        z-index: 1002;
    }

    /* Search Box - UPDATED */
    .search-box {
        position: relative;
    }

    .search-box input {
        padding: 0.5rem 1rem 0.5rem 2.5rem;
        border: 1px solid var(--gray-300);
        border-radius: 50px;
        font-size: 0.9rem;
        width: 200px;
        transition: var(--transition);
        background: var(--gray-100);
    }

    .search-box input:focus {
        outline: none;
        border-color: var(--primary);
        width: 250px;
        background: white;
        box-shadow: 0 0 0 3px rgba(139, 69, 19, 0.1);
    }

    .search-btn {
        position: absolute;
        left: 0.75rem;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        color: var(--gray-500);
        cursor: pointer;
        z-index: 1;
    }

    .search-results {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border: 1px solid var(--gray-200);
        border-radius: var(--radius);
        box-shadow: var(--shadow-lg);
        max-height: 400px;
        overflow-y: auto;
        display: none;
        z-index: 1003;
        margin-top: 0.5rem;
    }

    .search-result-item {
        display: flex;
        align-items: center;
        padding: 1rem;
        gap: 1rem;
        border-bottom: 1px solid var(--gray-200);
        transition: var(--transition);
        text-decoration: none;
        color: inherit;
    }

    .search-result-item:hover {
        background: var(--gray-100);
    }

    .search-result-item img {
        width: 50px;
        height: 50px;
        object-fit: cover;
        border-radius: var(--radius);
    }

    .search-result-info {
        flex: 1;
    }

    .search-result-info h4 {
        font-size: 0.95rem;
        color: var(--dark);
        margin-bottom: 0.25rem;
    }

    .search-result-category {
        font-size: 0.8rem;
        color: var(--gray-500);
        display: block;
        margin-bottom: 0.25rem;
    }

    .search-result-price {
        font-size: 0.9rem;
        font-weight: 600;
        color: var(--primary);
        display: block;
    }

    .no-results {
        padding: 2rem;
        text-align: center;
        color: var(--gray-500);
    }

    /* Cart */
    .cart {
        position: relative;
    }

    .cart-toggle {
        background: none;
        border: none;
        color: var(--gray-700);
        font-size: 1.25rem;
        cursor: pointer;
        position: relative;
        padding: 0.5rem;
        transition: var(--transition);
    }

    .cart-toggle:hover {
        color: var(--primary);
    }

    .cart-count {
        position: absolute;
        top: -5px;
        right: -5px;
        background: var(--primary);
        color: white;
        font-size: 0.7rem;
        font-weight: 600;
        width: 18px;
        height: 18px;
        border-radius: 50%;
        display: none;
        align-items: center;
        justify-content: center;
    }

    .cart-count.show {
        display: flex;
    }

    .cart-dropdown {
        position: absolute;
        top: 100%;
        right: 0;
        width: 350px;
        background: white;
        border: 1px solid var(--gray-200);
        border-radius: var(--radius);
        box-shadow: var(--shadow-lg);
        padding: 0;
        display: none;
        z-index: 1003;
    }

    .cart-dropdown.active {
        display: block;
        animation: slideDown 0.3s ease;
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .cart-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem;
        border-bottom: 1px solid var(--gray-200);
    }

    .cart-header h4 {
        font-size: 1.1rem;
        color: var(--dark);
        margin: 0;
    }

    .close-cart {
        background: none;
        border: none;
        color: var(--gray-500);
        cursor: pointer;
        font-size: 1.1rem;
    }

    .cart-items {
        max-height: 300px;
        overflow-y: auto;
        padding: 1rem;
    }

    .cart-item {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 0.75rem;
        border-bottom: 1px solid var(--gray-200);
    }

    .cart-item:last-child {
        border-bottom: none;
    }

    .cart-item img {
       height: 60px;
        object-fit: cover;
        border-radius: var(--radius);
    }

    .cart-item-info {
        flex: 1;
    }

    .cart-item-info h4 {
        font-size: 0.9rem;
        color: var(--dark);
        margin-bottom: 0.25rem;
    }

    .cart-item-price {
        display: flex;
        justify-content: space-between;
        font-size: 0.85rem;
        color: var(--gray-600);
    }

    .remove-item {
        background: none;
        border: none;
        color: var(--gray-400);
        cursor: pointer;
        font-size: 1rem;
        transition: var(--transition);
    }

    .remove-item:hover {
        color: #DC2626;
    }

    .cart-total {
        display: flex;
        justify-content: space-between;
        font-weight: 600;
        padding: 1rem;
        border-top: 2px solid var(--gray-200);
        background: var(--gray-100);
    }

    .cart-actions {
        display: flex;
        gap: 0.5rem;
        padding: 1rem;
        background: white;
    }

    .cart-actions a {
        flex: 1;
        text-align: center;
        padding: 0.75rem;
        text-decoration: none;
        border-radius: var(--radius);
        font-weight: 500;
        font-size: 0.9rem;
    }

    .cart-actions .btn-secondary {
        background: var(--gray-100);
        color: var(--dark);
        border: 1px solid var(--gray-300);
    }

    .cart-actions .btn-primary {
        background: var(--primary);
        color: white;
        border: 1px solid var(--primary);
    }

    .empty-cart {
        text-align: center;
        padding: 2rem;
        color: var(--gray-500);
    }

    .empty-cart i {
        font-size: 2rem;
        margin-bottom: 0.5rem;
    }

    /* FIXED: Mobile Menu Toggle - Hamburger Menu */
    .menu-toggle {
        display: none;
        flex-direction: column;
        justify-content: space-between;
        width: 30px;
        height: 21px;
        background: none;
        border: none;
        cursor: pointer;
        padding: 0;
        z-index: 1002;
    }

    .menu-toggle span {
        display: block;
        width: 100%;
        height: 3px;
        background: var(--dark);
        border-radius: 3px;
        transition: var(--transition);
    }

    .menu-toggle.active span:nth-child(1) {
        transform: translateY(9px) rotate(45deg);
    }

    .menu-toggle.active span:nth-child(2) {
        opacity: 0;
    }

    .menu-toggle.active span:nth-child(3) {
        transform: translateY(-9px) rotate(-45deg);
    }

    /* FIXED: Mobile Navigation - WORKS ON ALL PAGES */
    @media (max-width: 768px) {
        .header-container {
            padding: 0.75rem 0;
            height: 70px;
        }

        .logo-img {
            height: 60px;
        }

        .logo-text {
            display: none;
        }

        .menu-toggle {
            display: flex;
        }

        /* FIXED: Navigation for mobile */
        .nav {
            position: fixed;
            top: 70px;
            left: 0;
            width: 100%;
            height: calc(100vh - 70px);
            background: white;
            padding: 1.5rem;
            display: block;
            overflow-x: hidden;
            overflow-y: auto;
            transform: translateX(-100%);
            transition: transform 0.3s ease;
            z-index: 1001;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
        }

        .nav.active {
            transform: translateX(0);
        }

        .nav-links {
            flex-direction: column;
            gap: 0;
            width: 100%;
        }

        .nav-link {
            padding: 1rem 0;
            border-bottom: 1px solid var(--gray-200);
            font-size: 1.1rem;
            justify-content: space-between;
            width: 100%;
            color: var(--dark);
        }

        .nav-link i {
            transition: transform 0.3s ease;
        }

        .nav-dropdown.active .nav-link i {
            transform: rotate(180deg);
        }

        .nav-dropdown .dropdown-menu {
            position: static;
            width: 100%;
            transform: none;
            display: none;
            box-shadow: none;
            border: none;
            padding: 1rem;
            background: var(--gray-100);
            border-radius: 0;
            margin-top: 0.5rem;
            grid-template-columns: 1fr;
            opacity: 1;
            visibility: visible;
            height: auto;
            max-height: none;
            overflow: visible;
            pointer-events: auto;
        }

        .nav-dropdown.active .dropdown-menu {
            display: grid;
        }

        .dropdown-column {
            padding: 0;
        }

        .dropdown-column h4 {
            font-size: 1rem;
            margin-bottom: 0.75rem;
        }

        .dropdown-column a {
            padding: 0.75rem 0;
            font-size: 0.95rem;
        }

        .search-box {
            display: none;
        }

        .header-actions {
            gap: 1rem;
        }

        .cart-dropdown {
            position: fixed;
            top: 70px;
            right: 0;
            left: 0;
            width: 100%;
            max-width: 100%;
            border-radius: 0;
            max-height: calc(100vh - 70px);
            overflow-y: auto;
            z-index: 1001;
        }
    }

    @media (max-width: 640px) {
        .logo-img {
            height: 50px;
        }

        .header-container {
            height: 60px;
        }

        .nav {
            top: 60px;
            height: calc(100vh - 60px);
            padding: 1rem;
        }

        .cart-dropdown {
            top: 60px;
            max-height: calc(100vh - 60px);
        }
    }

    @media (max-width: 480px) {
        .dropdown-menu {
            grid-template-columns: 1fr;
            gap: 0.5rem;
        }

        .cart-dropdown {
            width: 100vw;
        }
    }

    /* FIXED: Mobile Menu Overlay */
    .mobile-menu-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        z-index: 1000;
        backdrop-filter: blur(2px);
    }

    .mobile-menu-overlay.active {
        display: block;
    }

    /* FIXED: Body scroll lock when menu is open */
    body.menu-open {
        overflow: hidden;
        position: fixed;
        width: 100%;
        height: 100%;
    }

    /* Search loading spinner */
    .search-loading {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1rem;
        color: var(--gray-500);
    }

    .search-loading i {
        margin-right: 0.5rem;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
</style>

<header class="header" id="header">
    <div class="container">
        <div class="header-container">
            <!-- Logo -->
            <a href="index.php" class="logo">
                <img src="img/lo.png" alt="<?php echo htmlspecialchars($boutique_name); ?>" class="logo-img" onerror="this.onerror=null; this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNzAiIGhlaWdodD0iNzAiIHZpZXdCb3g9IjAgMCA3MCA3MCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48Y2lyY2xlIGN4PSIzNSIgY3k9IjM1IiByPSIzMCIgZmlsbD0iIzg0NDUxMyIvPjx0ZXh0IHg9IjM1IiB5PSI0MCIgZm9udC1mYW1pbHk9IkFyaWEiIGZvbnQtc2l6ZT0iMTIiIGZpbGw9IndoaXRlIiB0ZXh0LWFuY2hvcj0ibWlkZGxlIj5TRDwvdGV4dD48L3N2Zz4=';">
                <div class="logo-text">
                    <div class="logo-name"><?php echo htmlspecialchars($boutique_name); ?></div>
                    <div class="logo-tagline">Designer Boutique</div>
                </div>
            </a>

            <!-- Navigation Menu -->
            <nav class="nav" id="mainNav">
                <ul class="nav-links">
                    <li><a href="index.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">Home</a></li>
                    <li class="nav-dropdown">
                        <a href="products.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'products.php' ? 'active' : ''; ?>">Collections <i class="fas fa-chevron-down"></i></a>
                        <div class="dropdown-menu">
                            <div class="dropdown-column">
                                <h4><i class="fas fa-tshirt"></i> Stitched Clothes</h4>
                                <a href="products.php?type=stitched">View All Stitched</a>
                                <div class="dropdown-divider"></div>
                                <?php 
                                $stitched_categories = $conn->query("SELECT c.* FROM categories c 
                                                                   JOIN products p ON c.id = p.category_id 
                                                                   WHERE p.product_type = 'stitched' 
                                                                   AND p.is_active = 1
                                                                   GROUP BY c.id LIMIT 4");
                                if ($stitched_categories && $stitched_categories->num_rows > 0):
                                    while($cat = $stitched_categories->fetch_assoc()): ?>
                                    <a href="products.php?type=stitched&category=<?php echo $cat['id']; ?>">
                                        <?php echo htmlspecialchars($cat['name']); ?>
                                    </a>
                                    <?php endwhile;
                                else: ?>
                                    <a href="products.php?type=stitched">Lehengas</a>
                                    <a href="products.php?type=stitched&category=2">Sarees</a>
                                    <a href="products.php?type=stitched&category=3">Salwar Suits</a>
                                    <a href="products.php?type=stitched&category=4">Anarkalis</a>
                                <?php endif; ?>
                            </div>
                            <div class="dropdown-column">
                                <h4><i class="fas fa-cut"></i> Unstitched Cloth</h4>
                                <a href="products.php?type=unstitched">View All Unstitched</a>
                                <div class="dropdown-divider"></div>
                                <?php 
                                $unstitched_categories = $conn->query("SELECT c.* FROM categories c 
                                                                     JOIN products p ON c.id = p.category_id 
                                                                     WHERE p.product_type = 'unstitched' 
                                                                     AND p.is_active = 1
                                                                     GROUP BY c.id LIMIT 4");
                                if ($unstitched_categories && $unstitched_categories->num_rows > 0):
                                    while($cat = $unstitched_categories->fetch_assoc()): ?>
                                    <a href="products.php?type=unstitched&category=<?php echo $cat['id']; ?>">
                                        <?php echo htmlspecialchars($cat['name']); ?>
                                    </a>
                                    <?php endwhile;
                                else: ?>
                                    <a href="products.php?type=unstitched">Fabrics</a>
                                    <a href="products.php?type=unstitched">Materials</a>
                                <?php endif; ?>
                            </div>
                            <div class="dropdown-column">
                                <h4><i class="fas fa-gem"></i> Accessories</h4>
                                <a href="products.php?type=accessory">View All Accessories</a>
                                <div class="dropdown-divider"></div>
                                <?php 
                                $accessory_categories = $conn->query("SELECT c.* FROM categories c 
                                                                    JOIN products p ON c.id = p.category_id 
                                                                    WHERE p.product_type = 'accessory' 
                                                                    AND p.is_active = 1
                                                                    GROUP BY c.id LIMIT 4");
                                if ($accessory_categories && $accessory_categories->num_rows > 0):
                                    while($cat = $accessory_categories->fetch_assoc()): ?>
                                    <a href="products.php?type=accessory&category=<?php echo $cat['id']; ?>">
                                        <?php echo htmlspecialchars($cat['name']); ?>
                                    </a>
                                    <?php endwhile;
                                else: ?>
                                    <a href="products.php?type=accessory">Jewelry Sets</a>
                                    <a href="products.php?type=accessory">Earrings</a>
                                    <a href="products.php?type=accessory">Rings</a>
                                    <a href="products.php?type=accessory">Bangles</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </li>
                    <li><a href="about.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'about.php' ? 'active' : ''; ?>">About Designer</a></li>
                    <li><a href="contact.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'contact.php' ? 'active' : ''; ?>">Contact Us</a></li>
                </ul>
            </nav>

            <!-- Header Actions -->
            <div class="header-actions">
                <!-- Search Box -->
                <div class="search-box">
                    <button class="search-btn"><i class="fas fa-search"></i></button>
                    <input type="text" placeholder="Search products..." id="searchInput">
                    <div class="search-results" id="searchResults"></div>
                </div>

                <!-- Cart -->
                <div class="cart">
                    <button class="cart-toggle">
                        <i class="fas fa-shopping-bag"></i>
                        <span class="cart-count"></span>
                    </button>
                    <div class="cart-dropdown">
                        <div class="cart-header">
                            <h4>Shopping Cart</h4>
                            <button class="close-cart"><i class="fas fa-times"></i></button>
                        </div>
                        <div class="cart-items" id="cartDropdownItems">
                            <!-- Cart items will be dynamically added here -->
                        </div>
                        <div class="cart-total">
                            <span>Total:</span>
                            <span id="cartDropdownTotal">₹0.00</span>
                        </div>
                        <div class="cart-actions">
                            <a href="cart.php" class="btn-secondary">View Cart</a>
                            <a href="checkout.php" class="btn-primary">Checkout</a>
                        </div>
                    </div>
                </div>

                <!-- Mobile Menu Toggle - Hamburger Menu -->
                <button class="menu-toggle" id="menuToggle">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
            </div>
        </div>
    </div>
    
    <!-- Mobile Menu Overlay -->
    <div class="mobile-menu-overlay" id="mobileMenuOverlay"></div>
</header>

<script>
    // FIXED: Mobile menu functionality for ALL PAGES
    document.addEventListener('DOMContentLoaded', function() {
        const menuToggle = document.getElementById('menuToggle');
        const nav = document.getElementById('mainNav');
        const mobileOverlay = document.getElementById('mobileMenuOverlay');
        const navDropdowns = document.querySelectorAll('.nav-dropdown');
        const body = document.body;
        
        // Function to close mobile menu
        function closeMobileMenu() {
            if (menuToggle) menuToggle.classList.remove('active');
            if (nav) nav.classList.remove('active');
            if (mobileOverlay) mobileOverlay.classList.remove('active');
            body.classList.remove('menu-open');
            
            // Close all dropdowns
            navDropdowns.forEach(dropdown => {
                dropdown.classList.remove('active');
            });
        }
        
        // Function to open mobile menu
        function openMobileMenu() {
            if (menuToggle) menuToggle.classList.add('active');
            if (nav) nav.classList.add('active');
            if (mobileOverlay) mobileOverlay.classList.add('active');
            body.classList.add('menu-open');
        }
        
        // Toggle mobile menu
        if (menuToggle) {
            menuToggle.addEventListener('click', function(e) {
                e.stopPropagation();
                
                if (nav.classList.contains('active')) {
                    closeMobileMenu();
                } else {
                    openMobileMenu();
                }
            });
        }
        
        // Close menu when clicking overlay
        if (mobileOverlay) {
            mobileOverlay.addEventListener('click', closeMobileMenu);
        }
        
        // Mobile dropdown handling
        navDropdowns.forEach(dropdown => {
            const link = dropdown.querySelector('.nav-link');
            if (link) {
                link.addEventListener('click', function(e) {
                    if (window.innerWidth <= 768) {
                        e.preventDefault();
                        e.stopPropagation();
                        
                        // Toggle current dropdown
                        dropdown.classList.toggle('active');
                        
                        // Close other dropdowns
                        navDropdowns.forEach(other => {
                            if (other !== dropdown) {
                                other.classList.remove('active');
                            }
                        });
                    }
                });
            }
        });
        
        // Close menu when clicking regular nav links (not dropdowns)
        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', function(e) {
                if (window.innerWidth <= 768) {
                    // Don't close if it's a dropdown link with chevron
                    if (!this.querySelector('.fa-chevron-down')) {
                        setTimeout(closeMobileMenu, 300);
                    }
                }
            });
        });
        
        // Close menu on window resize
        let resizeTimer;
        window.addEventListener('resize', function() {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(function() {
                if (window.innerWidth > 768) {
                    closeMobileMenu();
                }
            }, 250);
        });
        
        // Close menu when clicking outside
        document.addEventListener('click', function(e) {
            if (window.innerWidth <= 768 && nav && nav.classList.contains('active')) {
                if (!nav.contains(e.target) && !menuToggle.contains(e.target)) {
                    closeMobileMenu();
                }
            }
        });
        
        // Close menu with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && nav && nav.classList.contains('active')) {
                closeMobileMenu();
            }
        });
        
        // Cart functionality
        const cartToggle = document.querySelector('.cart-toggle');
        const cartDropdown = document.querySelector('.cart-dropdown');
        const closeCart = document.querySelector('.close-cart');
        
        if (cartToggle && cartDropdown) {
            cartToggle.addEventListener(047click047, (e) => {
                e.preventDefault();
                // Redirect directly to cart.php instead of showing dropdown
                window.location.href = 047cart.php047;
            });
                e.stopPropagation();
                cartDropdown.classList.toggle('active');
                
                // Close mobile menu if open
                if (window.innerWidth <= 768 && nav && nav.classList.contains('active')) {
                    closeMobileMenu();
                }
            });
            
            if (closeCart) {
                closeCart.addEventListener('click', () => {
                    cartDropdown.classList.remove('active');
                });
            }
            
            // Close cart when clicking outside
            document.addEventListener('click', (e) => {
                if (!cartToggle.contains(e.target) && !cartDropdown.contains(e.target)) {
                    cartDropdown.classList.remove('active');
                }
            });
        }
        
        // Initialize cart on page load
        updateCartCount();
        updateCartDropdown();

        // Listen for custom events to update cart from other parts of the site
        window.addEventListener('cartUpdated', function() {
            updateCartCount();
            updateCartDropdown();
        });
        
        // SIMPLIFIED SEARCH FUNCTIONALITY - WORKING VERSION
        const searchInput = document.getElementById('searchInput');
        const searchResults = document.getElementById('searchResults');
        
        if (searchInput && searchResults) {
            searchInput.addEventListener('input', function() {
                const query = this.value.trim();
                if (query.length < 2) {
                    searchResults.style.display = 'none';
                    searchResults.innerHTML = '';
                    return;
                }
                
                // Show loading state
                searchResults.innerHTML = `
                    <div class="search-loading">
                        <i class="fas fa-spinner"></i>
                        <span>Searching...</span>
                    </div>
                `;
                searchResults.style.display = 'block';
                
                // Simple AJAX call to search.php
                fetch(`search.php?q=${encodeURIComponent(query)}`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        displaySearchResults(data, query);
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        searchResults.innerHTML = `
                            <div class="no-results">
                                <p>Error loading search results</p>
                            </div>
                        `;
                    });
            });
            
            // Close search results when clicking outside
            document.addEventListener('click', (e) => {
                if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
                    searchResults.style.display = 'none';
                }
            });
            
            searchInput.addEventListener('focus', function() {
                if (this.value.trim().length >= 2) {
                    searchResults.style.display = 'block';
                }
            });
        }
        
        function displaySearchResults(products, query) {
            if (!products || products.length === 0 || (products.error)) {
                searchResults.innerHTML = `
                    <div class="no-results">
                        <p>No products found for "${query}"</p>
                        <p style="font-size: 0.9rem; margin-top: 0.5rem;">Try searching for: Blazer, Saree, Lehanga, etc.</p>
                    </div>
                `;
                return;
            }
            
            let html = '';
            products.forEach(product => {
                // Create product URL based on product type
                const productUrl = `products.php?product_id=${product.id}&category=${product.category_id}`;
                
                html += `
                    <a href="${productUrl}" class="search-result-item">
                        <img src="${product.image}" 
                             alt="${product.name}"
                             onerror="this.src='assets/images/no-image.jpg'">
                        <div class="search-result-info">
                            <h4>${product.name}</h4>
                            <span class="search-result-category">${product.category}</span>
                            <span class="search-result-price">₹${product.price}</span>
                        </div>
                    </a>
                `;
            });
            
            // Add a view all results link if we have results
            if (products.length > 0) {
                html += `
                    <a href="products.php?search=${encodeURIComponent(query)}" class="search-result-item" style="text-align: center; justify-content: center; font-weight: 500;">
                        <span>View all results for "${query}"</span>
                    </a>
                `;
            }
            
            searchResults.innerHTML = html;
        }
        
        // --- REMOVED: Header scroll effect logic ---
    });
    
    // Cart functions
    function updateCartCount() {
        // Fetch cart from server via AJAX
        fetch('ajax_cart_handler.php?action=get')
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    const totalItems = data.total_items;
                    const cartCount = document.querySelector('.cart-count');
                    if (cartCount) {
                        cartCount.textContent = totalItems;
                        cartCount.style.display = totalItems > 0 ? 'flex' : 'none';
                    }
                }
            })
            .catch(error => {
                console.error('Error fetching cart count:', error);
                // Fallback to localStorage if server request fails
                const cart = JSON.parse(localStorage.getItem('cart')) || [];
                const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
                const cartCount = document.querySelector('.cart-count');
                if (cartCount) {
                    cartCount.textContent = totalItems;
                    cartCount.style.display = totalItems > 0 ? 'flex' : 'none';
                }
            });
    }
    
    function updateCartDropdown() {
        // Fetch cart from server via AJAX
        fetch('ajax_cart_handler.php?action=get')
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    renderCartDropdown(data.cart);
                } else {
                    // Fallback to localStorage
                    const cart = JSON.parse(localStorage.getItem('cart')) || [];
                    renderCartDropdown(cart);
                }
            })
            .catch(() => {
                // Fallback to localStorage
                const cart = JSON.parse(localStorage.getItem('cart')) || [];
                renderCartDropdown(cart);
            });
    }

    // --- NEW FUNCTION: Fetch cart from server ---
    function fetchCartFromServer() {
        return fetch('cart.php?action=get_cart', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update localStorage with server data to keep it in sync
                localStorage.setItem('cart', JSON.stringify(data.cart));
                return data.cart;
            } else {
                return null; // Indicate failure
            }
        })
        .catch(error => {
            console.warn('Could not fetch cart from server:', error);
            return null; // Indicate failure
        });
    }
    // --- END NEW FUNCTION ---

    // --- NEW FUNCTION: Render cart dropdown HTML ---
    function renderCartDropdown(cart) {
        const cartItems = document.getElementById('cartDropdownItems');
        const cartTotal = document.getElementById('cartDropdownTotal');

        if (cartItems && cartTotal) {
            if (cart.length === 0) {
                cartItems.innerHTML = '<div class="empty-cart"><i class="fas fa-shopping-cart"></i><p>Your cart is empty</p></div>';
                cartTotal.textContent = '₹0.00';
                return;
            }

            cartItems.innerHTML = cart.map(item => `
                <div class="cart-item">
                    <img src="${item.image || 'assets/images/no-image.jpg'}" alt="${item.name}" 
                         onerror="this.src='assets/images/no-image.jpg'">
                    <div class="cart-item-info">
                        <h4>${item.name}</h4>
                        <div class="cart-item-price">
                            <span>₹${formatPrice(item.price)} × ${item.quantity}</span>
                            <span>₹${formatPrice(item.price * item.quantity)}</span>
                        </div>
                    </div>
                    <button class="remove-item" onclick="removeFromCart('${item.id}')">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `).join('');

            const total = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
            cartTotal.textContent = '₹' + formatPrice(total);
        }
    }
    // --- END NEW FUNCTION ---
    
    function formatPrice(price) {
        return price.toLocaleString('en-IN', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }
    
    function removeFromCart(productId) {
    // Find index in cart (not ID — cart.php uses index-based removal)
    const cart = JSON.parse(localStorage.getItem('cart')) || [];
    const index = cart.findIndex(item => item.id == productId);
    
    if (index === -1) {
        showNotification('Item not found in cart');
        return;
    }

    fetch('ajax_cart_handler.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=remove&index=${index}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            // Update localStorage to match server
            localStorage.setItem('cart', JSON.stringify(data.cart));
            // Dispatch event to update cart displays
            window.dispatchEvent(new CustomEvent('cartUpdated'));
            showNotification('Item removed');
        } else {
            throw new Error(data.message || 'Failed to remove item');
        }
    })
    .catch(error => {
        console.error('Remove from cart error:', error);
        showNotification('❌ ' + (error.message || 'Could not remove item'));
    });
}
    
    function showNotification(message) {
        // Remove any existing notifications
        document.querySelectorAll('.custom-notification').forEach(el => el.remove());
        
        const notification = document.createElement('div');
        notification.className = 'custom-notification';
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
        
        setTimeout(() => {
            notification.style.transform = 'translateX(0)';
        }, 10);
        
        setTimeout(() => {
            notification.style.transform = 'translateX(150%)';
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }
</script>