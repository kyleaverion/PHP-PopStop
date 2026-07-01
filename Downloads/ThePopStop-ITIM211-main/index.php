<?php
require_once 'config/database.php';
require_once 'config/functions.php';

startSession();
$conn = getConnection();

$selected_brand = isset($_GET['brand']) ? sanitize($_GET['brand']) : '';
$search_query = isset($_GET['search']) ? sanitize($_GET['search']) : '';

if (!empty($search_query)) {
    $search_term = preg_quote($search_query, '/');
    if (!empty($selected_brand)) {
        $stmt = $conn->prepare("SELECT * FROM products WHERE (name REGEXP ? OR series REGEXP ? OR brand REGEXP ? OR description REGEXP ?) AND brand = ? ORDER BY created_at DESC LIMIT 20");
        $stmt->bind_param("sssss", $search_term, $search_term, $search_term, $search_term, $selected_brand);
    } else {
        $stmt = $conn->prepare("SELECT * FROM products WHERE (name REGEXP ? OR series REGEXP ? OR brand REGEXP ? OR description REGEXP ?) ORDER BY created_at DESC LIMIT 20");
        $stmt->bind_param("ssss", $search_term, $search_term, $search_term, $search_term);
    }
    $stmt->execute();
    $products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
} elseif (!empty($selected_brand)) {
    $stmt = $conn->prepare("SELECT * FROM products WHERE status = 'In Stock' AND brand = ? ORDER BY created_at DESC LIMIT 8");
    $stmt->bind_param("s", $selected_brand);
    $stmt->execute();
    $result = $stmt->get_result();
    $products = $result->fetch_all(MYSQLI_ASSOC);
} else {
    $query = "SELECT * FROM products WHERE status = 'In Stock' ORDER BY created_at DESC LIMIT 8";
    $result = $conn->query($query);
    $products = $result->fetch_all(MYSQLI_ASSOC);
}

$brands = $conn->query("SELECT DISTINCT brand FROM products ORDER BY brand")->fetch_all(MYSQLI_ASSOC);

$cart_count = 0;
$user_data = null;
if (isLoggedIn()) {
    $cart_count = getCartCount($conn, $_SESSION['user_id']);
    
    $user_stmt = $conn->prepare("SELECT profile_photo FROM users WHERE user_id = ?");
    $user_stmt->bind_param("i", $_SESSION['user_id']);
    $user_stmt->execute();
    $user_data = $user_stmt->get_result()->fetch_assoc();
}

closeConnection($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>The Pop Stop - Collectible Figurines</title>
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
                        <?php include 'includes/profile_dropdown.php'; ?>
                    <?php else: ?>
                        <li><a href="login.php">Login</a></li>
                        <li><a href="register.php">Register</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>

    <div class="container">
        <div class="hero">
            <h1>Welcome to The Pop Stop</h1>
            <p>Your destination for premium collectible figurines</p>
            <br>
            
            <!-- Search Bar -->
            <div class="card" style="max-width: 600px; margin: 2rem auto; padding: 1.5rem;">
                <form method="GET" action="index.php" style="display: flex; gap: 0.5rem;">
                    <input type="text" name="search" placeholder="Search products by name, series, or brand..." 
                           value="<?php echo htmlspecialchars($search_query); ?>" 
                           style="flex: 1; padding: 0.8rem; border: 2px solid var(--primary); border-radius: 8px; font-size: 1rem;">
                    <button type="submit" class="btn btn-primary" style="padding: 0.8rem 1.5rem;">🔍 Search</button>
                    <?php if ($search_query): ?>
                        <a href="index.php" class="btn btn-secondary" style="padding: 0.8rem 1rem;">Clear</a>
                    <?php endif; ?>
                </form>
            </div>
            
            <a href="products.php" class="btn btn-primary">Shop All Products</a>
        </div>

        <?php showAlert(); ?>

        <!-- Brand Selection -->
        <div class="brand-section">
            <h2 style="text-align: center; margin-bottom: 2rem; color: var(--dark-brown);">Shop by Brand</h2>
            <div class="brand-grid">
                <?php foreach ($brands as $brand): ?>
                    <a href="?brand=<?php echo urlencode($brand['brand']); ?>" class="brand-card <?php echo $selected_brand === $brand['brand'] ? 'active' : ''; ?>">
                        <?php
                        $brand_logo = '';
                        if (strtolower($brand['brand']) === 'pop mart') {
                            $brand_logo = 'products/popmart.jpg';
                        } elseif (strtolower($brand['brand']) === 'funko') {
                            $brand_logo = 'products/funko.jpg';
                        }
                        ?>
                        <?php if ($brand_logo && file_exists($brand_logo)): ?>
                            <img src="<?php echo htmlspecialchars($brand_logo); ?>" alt="<?php echo htmlspecialchars($brand['brand']); ?>">
                        <?php endif; ?>
                        <h3><?php echo htmlspecialchars($brand['brand']); ?></h3>
                    </a>
                <?php endforeach; ?>
                <?php if ($selected_brand): ?>
                    <a href="index.php" class="brand-card">
                        <h3 style="font-size: 0.9rem;">Clear Filter</h3>
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <h2 style="margin-bottom: 1rem; color: var(--dark-brown);">
            <?php echo $selected_brand ? htmlspecialchars($selected_brand) . ' Products' : 'Featured Products'; ?>
        </h2>
        
        <div class="product-grid">
            <?php foreach ($products as $product): ?>
                <div class="product-card">
                    <div class="product-image">
                        <?php if (!empty($product['image_url']) && file_exists($product['image_url'])): ?>
                            <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" style="width: 100%; height: 100%; object-fit: cover;">
                        <?php else: ?>
                            📦
                        <?php endif; ?>
                    </div>
                    <div class="product-info">
                        <div class="product-title"><?php echo htmlspecialchars($product['name']); ?></div>
                        <div class="product-series"><?php echo htmlspecialchars($product['series']); ?> - <?php echo htmlspecialchars($product['brand']); ?></div>
                        <div class="product-price"><?php echo formatCurrency($product['price']); ?></div>
                        <div class="product-stock stock-<?php echo $product['status'] === 'In Stock' ? 'in' : ($product['status'] === 'Low Stock' ? 'low' : 'out'); ?>">
                            <?php echo $product['status']; ?>
                        </div>
                        <a href="product-detail.php?id=<?php echo $product['product_id']; ?>" class="btn btn-secondary btn-small" style="width: 100%;">View Details</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div style="text-align: center; margin-top: 2rem;">
            <a href="products.php" class="btn btn-primary">View All Products</a>
        </div>
    </div>

    <footer>
        <p>&copy; 2025 The Pop Stop. All rights reserved.</p>
    </footer>
</body>
</html>
