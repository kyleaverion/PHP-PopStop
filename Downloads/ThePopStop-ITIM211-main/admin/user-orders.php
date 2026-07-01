<?php
require_once '../config/database.php';
require_once '../config/functions.php';

requireAdmin();

$conn = getConnection();

$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    header("Location: users.php");
    exit();
}

// user order & history
$orders = $conn->query("
    SELECT o.*, 
           (SELECT COUNT(*) FROM order_items WHERE order_id = o.order_id) as item_count
    FROM orders o
    WHERE o.user_id = $user_id
    ORDER BY o.order_date DESC
")->fetch_all(MYSQLI_ASSOC);

closeConnection($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order History - <?php echo htmlspecialchars($user['full_name']); ?></title>
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
                <li><a href="dashboard.php">📊 Dashboard</a></li>
                <li><a href="products.php">📦 Products</a></li>
                <li><a href="orders.php">🛍️ Orders</a></li>
                <li><a href="users.php" class="active">👥 Users</a></li>
                <li><a href="suppliers.php">🏭 Suppliers</a></li>
                <li><a href="purchase-orders.php">📋 Purchase Orders</a></li>
                <li><a href="discounts.php">🎟️ Discounts</a></li>
                <li><a href="reports.php">📈 Reports</a></li>
            </ul>
        </div>

        <div class="admin-content">
            <div style="margin-bottom: 2rem;">
                <a href="users.php" class="btn btn-secondary btn-small">← Back to Users</a>
            </div>
            
            <div class="card" style="margin-bottom: 2rem;">
                <h2 style="color: var(--dark-brown); margin-bottom: 1rem;">Customer Information</h2>
                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem;">
                    <div>
                        <strong>Name:</strong> <?php echo htmlspecialchars($user['full_name']); ?>
                    </div>
                    <div>
                        <strong>Username:</strong> <?php echo htmlspecialchars($user['username']); ?>
                    </div>
                    <div>
                        <strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?>
                    </div>
                    <div>
                        <strong>Phone:</strong> <?php echo htmlspecialchars($user['phone'] ?? 'N/A'); ?>
                    </div>
                </div>
            </div>

            <div class="card">
                <h2 style="color: var(--dark-brown); margin-bottom: 1.5rem;">Order History (<?php echo count($orders); ?> orders)</h2>
                
                <?php if (count($orders) > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Date</th>
                                <th>Items</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td>#<?php echo $order['order_id']; ?></td>
                                    <td><?php echo formatDateTime($order['order_date']); ?></td>
                                    <td><?php echo $order['item_count']; ?> item(s)</td>
                                    <td><?php echo formatCurrency($order['final_amount']); ?></td>
                                    <td>
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
                                    </td>
                                    <td>
                                        <a href="order-details.php?id=<?php echo $order['order_id']; ?>" class="btn btn-secondary btn-small">View Details</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="alert alert-info">This customer has no orders yet.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <footer>
        <p>&copy; 2025 The Pop Stop. All rights reserved.</p>
    </footer>
</body>
</html>
