
<?php
// Check if variables are defined, if not get from config
if (!isset($boutique_name) || !isset($designer_name)) {
    require_once 'config.php';
    $boutique_name = getSetting($conn, 'boutique_name') ?? 'SDesigner Boutique';
    $designer_name = getSetting($conn, 'designer_name') ?? 'Dinky Ahuja';
    $location = getSetting($conn, 'location') ?? 'Jalandhar, Punjab';
    $primary_phone = getSetting($conn, 'primary_phone') ?? '89686-36373';
    $secondary_phone = getSetting($conn, 'secondary_phone') ?? '9814927250';
    $instagram_url = getSetting($conn, 'instagram_url') ?? '#';
    $facebook_url = getSetting($conn, 'facebook_url') ?? '#';
    $show_social_links = getSetting($conn, 'show_social_links') ?? '1';
}
?>
<footer class="footer">
    <div class="container">
        <div class="footer-grid">
            <!-- Brand Info -->
            <div class="footer-brand">
                <div class="footer-logo"><?php echo htmlspecialchars($boutique_name); ?></div>
                <p class="footer-tagline">Exclusive designer wear by <?php echo htmlspecialchars($designer_name); ?>. Where tradition meets contemporary elegance.</p>
                <?php if ($show_social_links == '1'): ?>
                <div class="footer-social">
                    <a href="<?php echo htmlspecialchars($instagram_url); ?>" class="social-link" target="_blank" rel="noopener noreferrer">
                        <i class="fab fa-instagram"></i>
                        <span class="social-text">Instagram</span>
                    </a>
                    <a href="<?php echo htmlspecialchars($facebook_url); ?>" class="social-link" target="_blank" rel="noopener noreferrer">
                        <i class="fab fa-facebook-f"></i>
                        <span class="social-text">Facebook</span>
                    </a>
                    <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $primary_phone); ?>" class="social-link whatsapp" target="_blank" rel="noopener noreferrer">
                        <i class="fab fa-whatsapp"></i>
                        <span class="social-text">WhatsApp</span>
                    </a>
                </div>
                <?php endif; ?>
            </div>

            <!-- Quick Links -->
            <div class="footer-links-section">
                <h3 class="footer-heading">Quick Links</h3>
                <ul class="footer-links">
                    <li><a href="index.php">Home</a></li>
                    <li><a href="products.php?type=stitched">Stitched Clothes</a></li>
                    <li><a href="products.php?type=unstitched">Unstitched Fabrics</a></li>
                    <li><a href="products.php?type=accessory">Accessories</a></li>
                    <li><a href="about.php">About Designer</a></li>
                    <li><a href="contact.php">Contact Us</a></li>
                     <li><a href="terms.php">Terms & Conditions</a></li>
                </ul>
            </div>

            <!-- Collections -->
            <div class="footer-links-section">
                <h3 class="footer-heading">Collections</h3>
                <ul class="footer-links">
                    <li><a href="products.php?type=stitched&category=1">Lehengas</a></li>
                    <li><a href="products.php?type=stitched&category=2">Sarees</a></li>
                    <li><a href="products.php?type=stitched&category=3">Salwar Suits</a></li>
                    <li><a href="products.php?type=stitched&category=4">Anarkalis</a></li>
                    <li><a href="products.php?type=unstitched&category=8">Fabrics</a></li>
                    <li><a href="products.php?type=accessory&category=9">Jewelry</a></li>
                   
                </ul>
            </div>

            <!-- Contact Info -->
            <div class="footer-contact">
                <h3 class="footer-heading">Get In Touch</h3>
                <div class="contact-info">
                    <div class="contact-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <div class="contact-details">
                            <strong>Boutique Address</strong>
                            <p><?php echo htmlspecialchars($location); ?></p>
                        </div>
                    </div>
                    <div class="contact-item">
                        <i class="fas fa-phone"></i>
                        <div class="contact-details">
                            <strong>Call Us</strong>
                            <a href="tel:<?php echo preg_replace('/[^0-9]/', '', $primary_phone); ?>" class="phone-link">
                                <?php echo htmlspecialchars($primary_phone); ?>
                            </a>
                            <a href="tel:<?php echo preg_replace('/[^0-9]/', '', $secondary_phone); ?>" class="phone-link">
                                <?php echo htmlspecialchars($secondary_phone); ?>
                            </a>
                        </div>
                    </div>
                    <div class="contact-item">
                        <i class="fas fa-envelope"></i>
                        <div class="contact-details">
                            <strong>Email Us</strong>
                            <a href="mailto:sdesignerjal@gmail.com" class="email-link">
                               sdesignerjal@gmail.com
                            </a>
                        </div>
                    </div>
                    <div class="contact-item">
                        <i class="fas fa-clock"></i>
                        <div class="contact-details">
                            <strong>Business Hours</strong>
                            <p>Mon - Sat: 10:00 AM - 8:00 PM</p>
                            <p>Sunday: 11:00 AM - 6:00 PM</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="footer-bottom">
            <div class="copyright">
                <p>&copy; <//?php echo date('Y'); ?> <//?php echo htmlspecialchars($boutique_name); ?>. All rights reserved.</p>
                <p>Designed with <i class="fas fa-heart"></i> by Aks Saifi</p>
                
            </div>
            <div class="payment-methods">
                <span>We Accept:</span>
                <div class="payment-icons">
                    <i class="fab fa-cc-visa" title="Visa"></i>
                    <i class="fab fa-cc-mastercard" title="Mastercard"></i>
                    <i class="fab fa-cc-amex" title="American Express"></i>
                    <i class="fas fa-university" title="Bank Transfer"></i>
                    <i class="fas fa-money-bill-wave" title="Cash"></i>
                </div>
            </div>
        </div>
    </div>
</footer>

<style>
/* Footer Styles */
.footer {
    background: var(--dark);
    color: white;
    padding: 3rem 0 1.5rem;
    position: relative;
    margin-top: 3rem;
}

.footer::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, var(--primary), var(--secondary));
}

.footer-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 2rem;
    margin-bottom: 2rem;
}

@media (min-width: 768px) {
    .footer-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 3rem;
    }
}

@media (min-width: 1024px) {
    .footer-grid {
        grid-template-columns: repeat(4, 1fr);
        gap: 2rem;
        margin-bottom: 3rem;
    }
}

.footer-brand {
    display: flex;
    flex-direction: column;
}

.footer-logo {
    font-family: 'Playfair Display', serif;
    font-size: 1.5rem;
    font-weight: 700;
    margin-bottom: 1rem;
    color: white;
}

@media (min-width: 768px) {
    .footer-logo {
        font-size: 1.75rem;
    }
}

.footer-tagline {
    color: rgba(255, 255, 255, 0.8);
    margin-bottom: 1.5rem;
    line-height: 1.6;
    font-size: 0.9rem;
}

@media (min-width: 768px) {
    .footer-tagline {
        font-size: 0.95rem;
    }
}

.footer-social {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    margin-top: 0.5rem;
}

@media (min-width: 768px) {
    .footer-social {
        gap: 0.75rem;
    }
}

.social-link {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1rem;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 8px;
    transition: all 0.3s ease;
    color: white;
    text-decoration: none;
}

.social-link:hover {
    background: var(--primary);
    transform: translateX(5px);
    color: white;
}

.social-link.whatsapp:hover {
    background: #25D366;
}

.social-link i {
    font-size: 1rem;
    width: 20px;
    text-align: center;
}

.social-text {
    font-size: 0.85rem;
}

.footer-heading {
    font-size: 1.1rem;
    margin-bottom: 1.25rem;
    color: var(--secondary);
    position: relative;
    padding-bottom: 0.5rem;
}

@media (min-width: 768px) {
    .footer-heading {
        font-size: 1.2rem;
        margin-bottom: 1.5rem;
        padding-bottom: 0.75rem;
    }
}

.footer-heading::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 40px;
    height: 2px;
    background: var(--primary);
}

.footer-links {
    list-style: none;
}

.footer-links li {
    margin-bottom: 0.5rem;
}

@media (min-width: 768px) {
    .footer-links li {
        margin-bottom: 0.75rem;
    }
}

.footer-links a {
    color: rgba(255, 255, 255, 0.8);
    transition: all 0.3s ease;
    display: block;
    padding: 0.25rem 0;
    position: relative;
    padding-left: 0;
    text-decoration: none;
    font-size: 0.9rem;
}

.footer-links a::before {
    content: '→';
    position: absolute;
    left: 0;
    opacity: 0;
    transition: all 0.3s ease;
}

.footer-links a:hover {
    color: var(--secondary);
    padding-left: 1.25rem;
}

.footer-links a:hover::before {
    opacity: 1;
    left: 0.25rem;
}

.contact-info {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}

.contact-item {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
}

.contact-item i {
    color: var(--secondary);
    margin-top: 0.25rem;
    font-size: 1rem;
    min-width: 20px;
}

.contact-details strong {
    display: block;
    margin-bottom: 0.25rem;
    color: white;
    font-size: 0.9rem;
}

.contact-details p,
.contact-details a {
    color: rgba(255, 255, 255, 0.8);
    font-size: 0.85rem;
    line-height: 1.5;
    text-decoration: none;
}

.phone-link,
.email-link {
    display: block;
    margin-bottom: 0.25rem;
    transition: color 0.3s ease;
}

.phone-link:hover,
.email-link:hover {
    color: var(--secondary);
}

.footer-bottom {
    padding-top: 1.5rem;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1.5rem;
    text-align: center;
}

@media (min-width: 768px) {
    .footer-bottom {
        flex-direction: row;
        justify-content: space-between;
        align-items: center;
        text-align: left;
        padding-top: 2rem;
    }
}

.copyright {
    color: rgba(255, 255, 255, 0.6);
    font-size: 0.85rem;
    line-height: 1.6;
}

.copyright p {
    margin-bottom: 0.5rem;
}

.copyright .fa-heart {
    color: #ef4444;
    margin: 0 0.25rem;
}

.legal-links {
    margin-top: 1rem;
    padding-top: 1rem;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.legal-links a {
    color: rgba(255, 255, 255, 0.6);
    text-decoration: none;
    transition: color 0.3s ease;
}

.legal-links a:hover {
    color: var(--secondary);
    text-decoration: underline;
}

.payment-methods {
    display: flex;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
    justify-content: center;
}

.payment-methods span {
    color: rgba(255, 255, 255, 0.8);
    font-size: 0.85rem;
}

.payment-icons {
    display: flex;
    gap: 1rem;
}

.payment-icons i {
    font-size: 1.25rem;
    color: rgba(255, 255, 255, 0.7);
    transition: color 0.3s ease;
}

.payment-icons i:hover {
    color: var(--secondary);
}

/* Mobile specific adjustments */
@media (max-width: 767px) {
    .footer {
        padding: 2rem 0 1rem;
    }
    
    .footer-links a:hover {
        padding-left: 1rem;
    }
    
    .payment-methods {
        flex-direction: column;
        gap: 0.75rem;
    }
    
    .legal-links {
        text-align: center;
        margin-top: 0.5rem;
        padding-top: 0.5rem;
    }
}
</style>
