<?php
require_once '../config/database.php';
require_once '../config/functions.php';

requireAdmin();

$conn = getConnection();

// Get statistics
$total_products = $conn->query("SELECT COUNT(*) as count FROM products")->fetch_assoc()['count'];
$total_users = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'customer'")->fetch_assoc()['count'];
$total_orders = $conn->query("SELECT COUNT(*) as count FROM orders")->fetch_assoc()['count'];

// Compute total revenue from order_items and discounts (no stored final_amount column)
$revenue_result = $conn->query("\n    SELECT COALESCE(SUM(order_total), 0) as total\n    FROM (\n        SELECT o.order_id,\n               COALESCE(SUM(oi.quantity * oi.unit_price), 0) - COALESCE(o.discount_amount, 0) AS order_total\n        FROM orders o\n        LEFT JOIN order_items oi ON o.order_id = oi.order_id\n        WHERE o.status != 'Cancelled'\n        GROUP BY o.order_id, o.discount_amount\n    ) t\n");
$revenue_row = $revenue_result->fetch_assoc();
$total_revenue = $revenue_row['total'] ?? 0;

// Recent orders
$recent_orders = $conn->query("\n    SELECT o.*, u.full_name \n    FROM orders o \n    JOIN users u ON o.user_id = u.user_id \n    ORDER BY o.order_date DESC \n    LIMIT 10\n")->fetch_all(MYSQLI_ASSOC);

// Compute final_amount for each recent order from its items
foreach ($recent_orders as $index => $order) {
    $items_stmt = $conn->prepare("\n        SELECT quantity, unit_price\n        FROM order_items\n        WHERE order_id = ?\n    ");
    $items_stmt->bind_param("i", $order['order_id']);
    $items_stmt->execute();
    $items = $items_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    $subtotal = 0;
    foreach ($items as $item) {
        $subtotal += $item['unit_price'] * $item['quantity'];
    }

    $discount = $order['discount_amount'] ?? 0;
    $recent_orders[$index]['final_amount'] = max(0, $subtotal - $discount);
}

// Low stock products
$low_stock = $conn->query("
    SELECT * FROM products 
    WHERE stock_quantity < 10 
    ORDER BY stock_quantity ASC 
    LIMIT 5
")->fetch_all(MYSQLI_ASSOC);

closeConnection($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - The Pop Stop</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header>
        <div class="header-container">
            <a href="../index.php" class="logo">The Pop Stop</a>
            <nav>
                <ul>
                    <li><a href="../index.php">View Site</a></li>
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="../logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="admin-layout">
        <div class="admin-sidebar">
            <h3>Admin Menu</h3>
            <ul>
                <li><a href="dashboard.php" class="active">📊 Dashboard</a></li>
                <li><a href="products.php">📦 Products</a></li>
                <li><a href="orders.php">🛍️ Orders</a></li>
                <li><a href="users.php">👥 Users</a></li>
                <li><a href="suppliers.php">🏭 Suppliers</a></li>
                <li><a href="purchase-orders.php">📋 Purchase Orders</a></li>
                <li><a href="discounts.php">🎟️ Discounts</a></li>
                <li><a href="reports.php">📈 Reports</a></li>
            </ul>
        </div>

        <div class="admin-content">
            <h1 style="margin-bottom: 2rem; color: var(--dark-brown);">Dashboard</h1>
            
            <?php showAlert(); ?>
            
            <!-- Statistics -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-label">Total Products</div>
                    <div class="stat-value"><?php echo $total_products; ?></div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-label">Total Customers</div>
                    <div class="stat-value"><?php echo $total_users; ?></div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-label">Total Orders</div>
                    <div class="stat-value"><?php echo $total_orders; ?></div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-label">Total Revenue</div>
                    <div class="stat-value"><?php echo formatCurrency($total_revenue); ?></div>
                </div>
            </div>
            
            <!-- Recent Orders -->
            <div class="card">
                <h3 style="color: var(--dark-brown); margin-bottom: 1rem;">Recent Orders</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Date</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_orders as $order): ?>
                            <tr>
                                <td>#<?php echo $order['order_id']; ?></td>
                                <td><?php echo htmlspecialchars($order['full_name']); ?></td>
                                <td><?php echo formatDateTime($order['order_date']); ?></td>
                                <td><?php echo formatCurrency($order['final_amount']); ?></td>
                                <td><?php echo $order['status']; ?></td>
                                <td>
                                    <a href="order-details.php?id=<?php echo $order['order_id']; ?>" class="btn btn-secondary btn-small">View</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Low Stock Alert -->
            <?php if (count($low_stock) > 0): ?>
                <div class="card">
                    <h3 style="color: var(--dark-brown); margin-bottom: 1rem;">⚠️ Low Stock Alert</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>SKU</th>
                                <th>Current Stock</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($low_stock as $product): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($product['name']); ?></td>
                                    <td><?php echo htmlspecialchars($product['sku']); ?></td>
                                    <td><?php echo $product['stock_quantity']; ?></td>
                                    <td class="stock-<?php echo $product['status'] === 'Out of Stock' ? 'out' : 'low'; ?>">
                                        <?php echo $product['status']; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <footer>
        <p>&copy; 2025 The Pop Stop. All rights reserved.</p>
    </footer>
</body>
</html>
