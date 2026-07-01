<?php
require_once 'config/database.php';
require_once 'config/functions.php';

requireLogin();

$conn = getConnection();
$user_id = $_SESSION['user_id'];

echo "<h1>Order Debug Information</h1>";
echo "<h2>User ID: $user_id</h2>";

// Check total orders for this user
$count_result = $conn->query("SELECT COUNT(*) as total FROM orders WHERE user_id = $user_id");
$count = $count_result->fetch_assoc()['total'];
echo "<p><strong>Total orders in database:</strong> $count</p>";

$all_orders = $conn->query("SELECT order_id, order_date, total_amount, status FROM orders WHERE user_id = $user_id ORDER BY order_date DESC");

echo "<h3>All Orders (Raw from Database):</h3>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Order ID</th><th>Date</th><th>Total</th><th>Status</th></tr>";
while ($order = $all_orders->fetch_assoc()) {
    echo "<tr>";
    echo "<td>#{$order['order_id']}</td>";
    echo "<td>{$order['order_date']}</td>";
    echo "<td>₱" . number_format($order['total_amount'], 2) . "</td>";
    echo "<td>{$order['status']}</td>";
    echo "</tr>";
}
echo "</table>";

// Check for duplicate order_ids
$duplicates = $conn->query("
    SELECT order_id, COUNT(*) as count 
    FROM orders 
    WHERE user_id = $user_id 
    GROUP BY order_id 
    HAVING COUNT(*) > 1
");

if ($duplicates->num_rows > 0) {
    echo "<h3 style='color: red;'>⚠️ DUPLICATE ORDER IDs FOUND:</h3>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Order ID</th><th>Count</th></tr>";
    while ($dup = $duplicates->fetch_assoc()) {
        echo "<tr><td>#{$dup['order_id']}</td><td>{$dup['count']}</td></tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: green;'>✓ No duplicate order IDs found</p>";
}

$stmt = $conn->prepare("
    SELECT o.order_id, o.user_id, o.order_date, o.total_amount, 
           o.discount_amount, o.final_amount, o.status, 
           o.shipping_address, o.payment_method,
           COUNT(oi.order_item_id) as item_count
    FROM orders o
    LEFT JOIN order_items oi ON o.order_id = oi.order_id
    WHERE o.user_id = ?
    GROUP BY o.order_id, o.user_id, o.order_date, o.total_amount, 
             o.discount_amount, o.final_amount, o.status, 
             o.shipping_address, o.payment_method
    ORDER BY o.order_date DESC
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$test_orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

echo "<h3>Orders from orders.php Query:</h3>";
echo "<p><strong>Count:</strong> " . count($test_orders) . "</p>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Order ID</th><th>Date</th><th>Total</th><th>Status</th><th>Item Count</th></tr>";
foreach ($test_orders as $order) {
    echo "<tr>";
    echo "<td>#{$order['order_id']}</td>";
    echo "<td>{$order['order_date']}</td>";
    echo "<td>₱" . number_format($order['total_amount'], 2) . "</td>";
    echo "<td>{$order['status']}</td>";
    echo "<td>{$order['item_count']}</td>";
    echo "</tr>";
}
echo "</table>";

echo "<hr>";
echo "<p><a href='orders.php'>← Back to My Orders</a></p>";

closeConnection($conn);
?>
