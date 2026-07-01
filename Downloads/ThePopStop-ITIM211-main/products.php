<?php
require_once 'config/database.php';
require_once 'config/functions.php';

startSession();
$conn = getConnection();

$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
$brand = isset($_GET['brand']) ? sanitize($_GET['brand']) : '';
$type = isset($_GET['type']) ? sanitize($_GET['type']) : '';
$sort = isset($_GET['sort']) ? sanitize($_GET['sort']) : 'name';

$query = "SELECT * FROM products WHERE 1=1";
$params = [];
$types = '';

if (!empty($search)) {
    $query .= " AND (name REGEXP ? OR brand REGEXP ? OR series REGEXP ? OR description REGEXP ?)";
    $search_param = preg_quote($search, '/');
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= 'ssss';
}

if (!empty($brand)) {
    $query .= " AND brand = ?";
    $params[] = $brand;
    $types .= 's';
}

if (!empty($type)) {
    $query .= " AND type = ?";
    $params[] = $type;
    $types .= 's';
}

switch ($sort) {
    case 'price_low':
        $query .= " ORDER BY price ASC";
        break;
    case 'price_high':
        $query .= " ORDER BY price DESC";
        break;
    case 'newest':
        $query .= " ORDER BY created_at DESC";
        break;
    default:
        $query .= " ORDER BY name ASC";
}

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$brands = $conn->query("SELECT DISTINCT brand FROM products ORDER BY brand")->fetch_all(MYSQLI_ASSOC);
$types_list = $conn->query("SELECT DISTINCT type FROM products ORDER BY type")->fetch_all(MYSQLI_ASSOC);

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
    <title>Products - The Pop Stop</title>
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
        <h1 style="margin-bottom: 2rem; color: var(--dark-brown);">Our Products</h1>
        
        <!-- Search Bar -->
        <div class="search-container">
            <form method="GET" class="search-bar">
                <input type="text" name="search" placeholder="Search products..." value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" class="btn btn-primary">Search</button>
            </form>
        </div>

        <!-- Filters -->
        <div class="filter-section">
            <form method="GET" style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: center; width: 100%;">
                <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
                
                <select name="brand" onchange="this.form.submit()">
                    <option value="">All Brands</option>
                    <?php foreach ($brands as $b): ?>
                        <option value="<?php echo htmlspecialchars($b['brand']); ?>" <?php echo $brand === $b['brand'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($b['brand']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                
                <select name="type" onchange="this.form.submit()">
                    <option value="">All Types</option>
                    <?php foreach ($types_list as $t): ?>
                        <option value="<?php echo htmlspecialchars($t['type']); ?>" <?php echo $type === $t['type'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($t['type']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                
                <select name="sort" onchange="this.form.submit()">
                    <option value="name" <?php echo $sort === 'name' ? 'selected' : ''; ?>>Name (A-Z)</option>
                    <option value="price_low" <?php echo $sort === 'price_low' ? 'selected' : ''; ?>>Price (Low to High)</option>
                    <option value="price_high" <?php echo $sort === 'price_high' ? 'selected' : ''; ?>>Price (High to Low)</option>
                    <option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>Newest</option>
                </select>
                
                <?php if ($search || $brand || $type): ?>
                    <a href="products.php" class="btn btn-secondary btn-small">Clear Filters</a>
                <?php endif; ?>
            </form>
        </div>

        <?php if (count($products) > 0): ?>
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
                                <?php echo $product['status']; ?> (<?php echo $product['stock_quantity']; ?> available)
                            </div>
                            <a href="product-detail.php?id=<?php echo $product['product_id']; ?>" class="btn btn-secondary btn-small" style="width: 100%;">View Details</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-info">No products found matching your criteria.</div>
        <?php endif; ?>
    </div>

    <footer>
        <p>&copy; 2025 The Pop Stop. All rights reserved.</p>
    </footer>
</body>
</html>
