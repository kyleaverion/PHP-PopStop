<?php
require_once '../config/database.php';
require_once '../config/functions.php';

requireAdmin();

$conn = getConnection();

// status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $order_id = intval($_POST['order_id']);
    $status = sanitize($_POST['status']);

    $current_stmt = $conn->prepare("SELECT status FROM orders WHERE order_id = ?");
    $current_stmt->bind_param("i", $order_id);
    $current_stmt->execute();
    $current_row = $current_stmt->get_result()->fetch_assoc();
    $previous_status = $current_row['status'] ?? null;
    
    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE order_id = ?");
    $stmt->bind_param("si", $status, $order_id);
    
    if ($stmt->execute()) {
        if ($status === 'Cancelled' && $previous_status !== 'Cancelled') {
            $items_stmt = $conn->prepare("
                SELECT product_id, quantity
                FROM order_items
                WHERE order_id = ?
            ");
            $items_stmt->bind_param("i", $order_id);
            $items_stmt->execute();
            $items = $items_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

            foreach ($items as $item) {
                if (empty($item['product_id'])) {
                    continue;
                }

                $restock_stmt = $conn->prepare("UPDATE products SET stock_quantity = stock_quantity + ? WHERE product_id = ?");
                $restock_stmt->bind_param("ii", $item['quantity'], $item['product_id']);
                $restock_stmt->execute();


                updateProductStatus($conn, $item['product_id']);
            }
        }

        $customer_stmt = $conn->prepare("
            SELECT u.email, u.full_name 
            FROM orders o 
            JOIN users u ON o.user_id = u.user_id 
            WHERE o.order_id = ?
        ");
        $customer_stmt->bind_param("i", $order_id);
        $customer_stmt->execute();
        $customer = $customer_stmt->get_result()->fetch_assoc();
        
        if ($customer) {
            $email_sent = sendOrderEmail($conn, $order_id, $customer['email'], $customer['full_name']);
            
            if ($email_sent) {
                setAlert('success', 'Order status updated and email notification sent successfully');
            } else {
                setAlert('success', 'Order status updated (email notification failed)');
            }
        } else {
            setAlert('success', 'Order status updated successfully');
        }
    } else {
        setAlert('error', 'Failed to update order status');
    }
    header("Location: orders.php");
    exit();
}

// status filter
$status_filter = isset($_GET['status']) ? sanitize($_GET['status']) : 'all';

if ($status_filter !== 'all') {
    $stmt = $conn->prepare("
        SELECT o.*, u.full_name, u.email,
               (SELECT COUNT(*) FROM order_items WHERE order_id = o.order_id) as item_count,
               COALESCE(
                   (SELECT SUM(oi.quantity * oi.unit_price)
                   FROM order_items oi
                   WHERE oi.order_id = o.order_id
                   ), 0) AS subtotal
        FROM orders o
        LEFT JOIN users u ON o.user_id = u.user_id
        WHERE o.status = ?
        ORDER BY o.order_date DESC
    ");
    $stmt->bind_param("s", $status_filter);
    $stmt->execute();
    $orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
} else {
    $orders = $conn->query("
        SELECT o.*, u.full_name, u.email,
               (SELECT COUNT(*) FROM order_items WHERE order_id = o.order_id) as item_count,
               COALESCE(
                   (SELECT SUM(oi.quantity * oi.unit_price)
                   FROM order_items oi
                   WHERE oi.order_id = o.order_id
                   ), 0) AS subtotal
        FROM orders o
        LEFT JOIN users u ON o.user_id = u.user_id
        ORDER BY o.order_date DESC
    ")->fetch_all(MYSQLI_ASSOC);
}

// status counts
$status_counts = $conn->query("
    SELECT status, COUNT(*) as count
    FROM orders
    GROUP BY status
")->fetch_all(MYSQLI_ASSOC);

// Compute final_amount for admin list using subtotal and discount_amount
foreach ($orders as $index => $order) {
    $subtotal = $order['subtotal'] ?? 0;
    $discount = $order['discount_amount'] ?? 0;
    $orders[$index]['final_amount'] = max(0, $subtotal - $discount);
}

$counts = [
    'all' => array_sum(array_column($status_counts, 'count')),
    'Pending' => 0,
    'Processing' => 0,
    'Shipped' => 0,
    'Delivered' => 0,
    'Cancelled' => 0
];

foreach ($status_counts as $sc) {
    $counts[$sc['status']] = $sc['count'];
}

closeConnection($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders - Admin</title>
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
                <li><a href="orders.php" class="active">🛍️ Orders</a></li>
                <li><a href="users.php">👥 Users</a></li>
                <li><a href="suppliers.php">🏭 Suppliers</a></li>
                <li><a href="purchase-orders.php">📋 Purchase Orders</a></li>
                <li><a href="discounts.php">🎟️ Discounts</a></li>
                <li><a href="reports.php">📈 Reports</a></li>
            </ul>
        </div>

        <div class="admin-content">
            <h1 style="margin-bottom: 2rem; color: var(--dark-brown);">Manage Orders</h1>
            
            <?php showAlert(); ?>
            
            <div class="card" style="margin-bottom: 1.5rem;">
                <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                    <a href="orders.php?status=all" class="btn <?php echo $status_filter === 'all' ? 'btn-primary' : 'btn-secondary'; ?>" style="font-size: 0.9rem;">
                        All Orders (<?php echo $counts['all']; ?>)
                    </a>
                    <a href="orders.php?status=Pending" class="btn <?php echo $status_filter === 'Pending' ? 'btn-primary' : 'btn-secondary'; ?>" style="font-size: 0.9rem;">
                        ⏳ Pending (<?php echo $counts['Pending']; ?>)
                    </a>
                    <a href="orders.php?status=Processing" class="btn <?php echo $status_filter === 'Processing' ? 'btn-primary' : 'btn-secondary'; ?>" style="font-size: 0.9rem;">
                        🔄 Processing (<?php echo $counts['Processing']; ?>)
                    </a>
                    <a href="orders.php?status=Shipped" class="btn <?php echo $status_filter === 'Shipped' ? 'btn-primary' : 'btn-secondary'; ?>" style="font-size: 0.9rem;">
                        🚚 Shipped (<?php echo $counts['Shipped']; ?>)
                    </a>
                    <a href="orders.php?status=Delivered" class="btn <?php echo $status_filter === 'Delivered' ? 'btn-primary' : 'btn-secondary'; ?>" style="font-size: 0.9rem;">
                        ✅ Delivered (<?php echo $counts['Delivered']; ?>)
                    </a>
                    <a href="orders.php?status=Cancelled" class="btn <?php echo $status_filter === 'Cancelled' ? 'btn-primary' : 'btn-secondary'; ?>" style="font-size: 0.9rem;">
                        ❌ Cancelled (<?php echo $counts['Cancelled']; ?>)
                    </a>
                </div>
            </div>
            
            <div class="card">
                <table>
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Email</th>
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
                                <td><?php echo $order['full_name'] ? htmlspecialchars($order['full_name']) : '<em style="color: #999;">User Deleted</em>'; ?></td>
                                <td><?php echo $order['email'] ? htmlspecialchars($order['email']) : '<em style="color: #999;">-</em>'; ?></td>
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
                                    <a href="order-details.php?id=<?php echo $order['order_id']; ?>" class="btn btn-secondary btn-small">View</a>
                                    <button onclick='showStatusModal(<?php echo $order["order_id"]; ?>, "<?php echo $order["status"]; ?>")' class="btn btn-primary btn-small">Update</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="statusModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Update Order Status</h2>
                <button class="modal-close" onclick="closeModal('statusModal')">&times;</button>
            </div>
            <form method="POST">
                <input type="hidden" name="order_id" id="status_order_id">
                <div class="form-group">
                    <label>Order Status *</label>
                    <select name="status" id="status_value" required>
                        <option value="Pending">Pending</option>
                        <option value="Processing">Processing</option>
                        <option value="Shipped">Shipped</option>
                        <option value="Delivered">Delivered</option>
                        <option value="Cancelled">Cancelled</option>
                    </select>
                </div>
                <button type="submit" name="update_status" class="btn btn-primary" style="width: 100%;">Update Status</button>
            </form>
        </div>
    </div>

    <script>
        function showStatusModal(orderId, currentStatus) {
            document.getElementById('status_order_id').value = orderId;
            document.getElementById('status_value').value = currentStatus;
            document.getElementById('statusModal').classList.add('active');
        }
        
        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('active');
        }
        
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.classList.remove('active');
            }
        }
    </script>

    <footer>
        <p>&copy; 2025 The Pop Stop. All rights reserved.</p>
    </footer>
</body>
</html>
