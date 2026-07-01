<?php
require_once 'config/database.php';
require_once 'config/functions.php';

requireLogin();

$conn = getConnection();
$user_id = $_SESSION['user_id'];

$user_stmt = $conn->prepare("SELECT profile_photo FROM users WHERE user_id = ?");
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_data = $user_stmt->get_result()->fetch_assoc();

$stmt = $conn->prepare("
    SELECT o.order_id, o.user_id, o.order_date,
           o.discount_amount, o.status,
           o.shipping_address, o.payment_method,
           COUNT(oi.order_item_id) as item_count
    FROM orders o
    LEFT JOIN order_items oi ON o.order_id = oi.order_id
    WHERE o.user_id = ?
    GROUP BY o.order_id, o.user_id, o.order_date,
             o.discount_amount, o.status,
             o.shipping_address, o.payment_method
    ORDER BY o.order_date DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

error_log("Orders fetched for user $user_id: " . count($orders) . " orders");
foreach ($orders as $order) {
    error_log("Order ID: " . $order['order_id'] . ", Date: " . $order['order_date']);
}

foreach ($orders as $key => $order) {
    $items_stmt = $conn->prepare("
        SELECT oi.*, p.name, p.image_url,
               (SELECT COUNT(*) FROM reviews WHERE user_id = ? AND product_id = oi.product_id AND order_id = oi.order_id) as has_review
        FROM order_items oi
        JOIN products p ON oi.product_id = p.product_id
        WHERE oi.order_id = ?
    ");
    $items_stmt->bind_param("ii", $user_id, $order['order_id']);
    $items_stmt->execute();
    $orders[$key]['items'] = $items_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // Compute per-order totals from items instead of relying solely on stored totals
    $subtotal = 0;
    foreach ($orders[$key]['items'] as $item) {
        $subtotal += $item['unit_price'] * $item['quantity'];
    }

    if (!isset($orders[$key]['discount_amount'])) {
        $orders[$key]['discount_amount'] = 0;
    }

    $orders[$key]['total_amount'] = $subtotal;
    $orders[$key]['final_amount'] = max(0, $subtotal - $orders[$key]['discount_amount']);
}

$cart_count = getCartCount($conn, $user_id);
closeConnection($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - The Pop Stop</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        /* Profile Dropdown Styles */
        .profile-dropdown {
            position: relative;
        }
        
        .profile-trigger {
            display: flex !important;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
        }
        
        .profile-icon {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--primary);
        }
        
        .profile-icon-placeholder {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background: var(--primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 1.1rem;
        }
        
        .dropdown-menu {
            position: absolute;
            top: 100%;
            right: 0;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            min-width: 200px;
            margin-top: 0.5rem;
            display: none;
            z-index: 1000;
        }
        
        .dropdown-menu.show {
            display: block;
        }
        
        .dropdown-item {
            display: flex !important;
            align-items: center;
            gap: 0.8rem;
            padding: 0.8rem 1.2rem !important;
            color: var(--dark-brown) !important;
            text-decoration: none;
            transition: background 0.3s;
            border-bottom: 1px solid var(--light-beige);
        }
        
        .dropdown-item:last-child {
            border-bottom: none;
        }
        
        .dropdown-item:hover {
            background: var(--light-beige);
        }
        
        .dropdown-item span {
            font-size: 1.2rem;
        }
    </style>
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
                    
                    <!-- Profile Dropdown -->
                    <li class="profile-dropdown">
                        <a href="#" class="profile-trigger" onclick="toggleProfileMenu(event)">
                            <?php if (!empty($user_data['profile_photo']) && file_exists($user_data['profile_photo'])): ?>
                                <img src="<?php echo htmlspecialchars($user_data['profile_photo']); ?>" alt="Profile" class="profile-icon">
                            <?php else: ?>
                                <div class="profile-icon-placeholder"><?php echo strtoupper(substr($_SESSION['full_name'], 0, 1)); ?></div>
                            <?php endif; ?>
                            <span>My Account</span>
                        </a>
                        <div class="dropdown-menu" id="profileDropdown">
                            <a href="profile.php" class="dropdown-item">
                                <span>👤</span> My Profile
                            </a>
                            <a href="logout.php" class="dropdown-item">
                                <span>🚪</span> Logout
                            </a>
                        </div>
                    </li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="container">
        <h1 style="margin-bottom: 2rem; color: var(--dark-brown);">My Orders</h1>
        
        <?php showAlert(); ?>
        
        <!-- Debug: Total orders = <?php echo count($orders); ?> -->
        
        <?php if (count($orders) > 0): ?>
            <?php foreach ($orders as $index => $order): ?>
                <!-- Debug: Displaying order <?php echo $index + 1; ?> - Order ID: <?php echo $order['order_id']; ?> -->
                <div class="card" style="margin-bottom: 2rem;">
                    <div style="padding: 1.5rem; background: #f9f9f9;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                            <div>
                                <strong style="font-size: 1.1rem; color: var(--dark-brown);">Order #<?php echo $order['order_id']; ?></strong>
                                <span style="margin-left: 1rem; color: var(--secondary);"><?php echo formatDateTime($order['order_date']); ?></span>
                            </div>
                            <div>
                                <span style="padding: 0.5rem 1rem; border-radius: 20px; font-size: 0.9rem; font-weight: 500;
                                    background: <?php 
                                        echo $order['status'] === 'Delivered' ? '#D5F4E6' : 
                                            ($order['status'] === 'Cancelled' ? '#FADBD8' : '#FFF3CD'); 
                                    ?>;
                                    color: <?php 
                                        echo $order['status'] === 'Delivered' ? '#27AE60' : 
                                            ($order['status'] === 'Cancelled' ? '#E74C3C' : '#F39C12'); 
                                    ?>;">
                                    <?php echo $order['status']; ?>
                                </span>
                                <strong style="margin-left: 1rem; font-size: 1.2rem; color: var(--accent);"><?php echo formatCurrency($order['final_amount']); ?></strong>
                            </div>
                        </div>
                        
                        <!-- Order Items -->
                        <div style="background: white; border-radius: 10px; padding: 1rem; margin-bottom: 1rem;">
                            <?php foreach ($order['items'] as $item): ?>
                                <div style="display: flex; justify-content: space-between; align-items: center; padding: 0.8rem; border-bottom: 1px solid var(--light-beige);">
                                    <div style="display: flex; align-items: center; gap: 1rem; flex: 1;">
                                        <?php if (!empty($item['image_url']) && file_exists($item['image_url'])): ?>
                                            <img src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px;">
                                        <?php else: ?>
                                            <div style="width: 60px; height: 60px; background: var(--light-beige); border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">📦</div>
                                        <?php endif; ?>
                                        <div>
                                            <strong><?php echo htmlspecialchars($item['name']); ?></strong>
                                            <div style="color: var(--secondary); font-size: 0.9rem;">Qty: <?php echo $item['quantity']; ?> × <?php echo formatCurrency($item['unit_price']); ?></div>
                                        </div>
                                    </div>
                                    <div style="text-align: right;">
                                        <div style="font-weight: bold; margin-bottom: 0.5rem;"><?php echo formatCurrency($item['unit_price'] * $item['quantity']); ?></div>
                                        <?php if ($order['status'] === 'Delivered'): ?>
                                            <?php if ($item['has_review'] > 0): ?>
                                                <a href="product-detail.php?id=<?php echo $item['product_id']; ?>" class="btn btn-secondary btn-small" style="background: #e0e0e0;">✓ Reviewed</a>
                                            <?php else: ?>
                                                <a href="product-detail.php?id=<?php echo $item['product_id']; ?>" class="btn btn-primary btn-small">⭐ Write Review</a>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div style="text-align: right;">
                            <a href="order-receipt.php?id=<?php echo $order['order_id']; ?>" class="btn btn-secondary btn-small">📋 View Receipt</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="card" style="text-align: center; padding: 3rem;">
                <h2 style="color: var(--dark-brown); margin-bottom: 1rem;">No orders yet</h2>
                <p style="margin-bottom: 2rem;">Start shopping to place your first order!</p>
                <a href="products.php" class="btn btn-primary">Browse Products</a>
            </div>
        <?php endif; ?>
    </div>

    <footer>
        <p>&copy; 2025 The Pop Stop. All rights reserved.</p>
    </footer>
    
    <script>
        function toggleProfileMenu(event) {
            event.preventDefault();
            const dropdown = document.getElementById('profileDropdown');
            dropdown.classList.toggle('show');
        }
        
        document.addEventListener('click', function(event) {
            const dropdown = document.getElementById('profileDropdown');
            const trigger = document.querySelector('.profile-trigger');
            
            if (dropdown && trigger && !trigger.contains(event.target) && !dropdown.contains(event.target)) {
                dropdown.classList.remove('show');
            }
        });
    </script>
</body>
</html>
