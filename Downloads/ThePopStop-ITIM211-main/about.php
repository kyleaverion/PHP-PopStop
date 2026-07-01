<?php
require_once 'config/database.php';
require_once 'config/functions.php';

startSession();
$conn = getConnection();

$cart_count = 0;
if (isLoggedIn()) {
    $cart_count = getCartCount($conn, $_SESSION['user_id']);
}

closeConnection($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - The Pop Stop</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header>
        <div class="header-container">
            <a href="index.php" class="logo">The Pop Stop</a>
            <nav>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="products.php">Products</a></li>
                    <li><a href="about.php">About Us</a></li>
                    <?php if (isLoggedIn()): ?>
                        <li><a href="orders.php">My Orders</a></li>
                        <li><a href="cart.php" class="cart-icon">
                            Cart
                            <?php if ($cart_count > 0): ?>
                                <span class="cart-count"><?php echo $cart_count; ?></span>
                            <?php endif; ?>
                        </a></li>
                        <?php if (isAdmin()): ?>
                            <li><a href="admin/dashboard.php">Admin Panel</a></li>
                        <?php endif; ?>
                        <li><a href="logout.php">Logout</a></li>
                    <?php else: ?>
                        <li><a href="login.php">Login</a></li>
                        <li><a href="register.php">Register</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>

    <div class="container">
        <div class="about-hero">
            <h1>About The Pop Stop</h1>
            <p>Your trusted destination for collectible figurines</p>
        </div>

        <div class="about-content">
            <div class="about-section">
                <div class="about-card">
                    <h2>Our Location</h2>
                    <p class="about-text">
                        <strong>Western Bicutan, Taguig City</strong><br>
                        Philippines
                    </p>
                    <p class="about-description">
                        Visit our store to explore our extensive collection of Pop Mart and Funko figures. 
                        We're conveniently located in the heart of Taguig City, making it easy for collectors 
                        to find their favorite pieces.
                    </p>
                </div>

                <div class="about-card">
                    <h2>Contact Information</h2>
                    <div class="contact-info">
                        <div class="contact-item">
                            <div>
                                <strong>Email</strong>
                                <p><a href="mailto:thepopstopmail@gmail.com">thepopstopmail@gmail.com</a></p>
                            </div>
                        </div>
                        <div class="contact-item">
                            <div>
                                <strong>Phone</strong>
                                <p><a href="tel:09125735465">0912-573-5465</a></p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="about-card">
                    <h2>What We Offer</h2>
                    <div class="offerings-grid">
                        <div class="offering-item">
                            <h3>Pop Mart Figures</h3>
                            <p>Exclusive collection of Hirono, Skullpanda, Crybaby, Labubu, and Pino Jelly figurines</p>
                        </div>
                        <div class="offering-item">
                            <h3>Funko Pop! Vinyl</h3>
                            <p>Wide selection of Marvel, DC Comics, Anime, and Gaming character figures</p>
                        </div>
                        <div class="offering-item">
                            <h3>Limited Editions</h3>
                            <p>Rare and exclusive collectibles for serious collectors</p>
                        </div>
                        <div class="offering-item">
                            <h3>Blind Boxes</h3>
                            <p>Experience the thrill of surprise with our blind box collections</p>
                        </div>
                    </div>
                </div>

                <div class="about-card">
                    <h2>Why Choose Us?</h2>
                    <ul class="benefits-list">
                        <li>Authentic and genuine products from official suppliers</li>
                        <li>Competitive pricing on all items</li>
                        <li>Regular stock updates with the latest releases</li>
                        <li>Secure packaging and careful handling</li>
                        <li>Excellent customer service</li>
                        <li>Easy online ordering and convenient payment options</li>
                    </ul>
                </div>

                <div class="about-card" style="text-align: center; background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%); color: var(--white);">
                    <h2 style="color: var(--white); font-family: 'Playfair Display', serif;">Ready to Start Collecting?</h2>
                    <p style="font-size: 1.1rem; margin: 1.5rem 0;">
                        Browse our catalog and find your next favorite figure!
                    </p>
                    <a href="products.php" class="btn btn-secondary" style="background: var(--white); color: var(--dark-brown); font-weight: 600;">
                        Shop Now
                    </a>
                </div>
            </div>
        </div>
    </div>

    <footer>
        <div class="footer-content">
            <div class="footer-section">
                <h3>The Pop Stop</h3>
                <p>Your one-stop shop for collectible figurines</p>
            </div>
            <div class="footer-section">
                <h3>Contact</h3>
                <p>Email: thepopstopmail@gmail.com</p>
                <p>Phone: 0912-573-5465</p>
            </div>
            <div class="footer-section">
                <h3>Location</h3>
                <p>Western Bicutan, Taguig City</p>
                <p>Philippines</p>
            </div>
        </div>
        <p style="text-align: center; margin-top: 2rem; padding-top: 2rem; border-top: 1px solid rgba(255,255,255,0.2);">
            &copy; 2025 The Pop Stop. All rights reserved.
        </p>
    </footer>
</body>
</html>
