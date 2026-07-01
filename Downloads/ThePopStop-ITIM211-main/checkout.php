<?php
require_once 'config/database.php';
require_once 'config/functions.php';

requireLogin();

$conn = getConnection();
$user_id = $_SESSION['user_id'];

// cart items
$stmt = $conn->prepare("
    SELECT c.*, p.name, p.price, p.stock_quantity
    FROM cart c
    JOIN products p ON c.product_id = p.product_id
    WHERE c.user_id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$cart_items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

if (count($cart_items) === 0) {
    header("Location: cart.php");
    exit();
}

// user info
$user_stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user = $user_stmt->get_result()->fetch_assoc();

$discounts = $conn->query("SELECT * FROM discounts WHERE is_active = 1 AND (end_date IS NULL OR end_date >= CURDATE())")->fetch_all(MYSQLI_ASSOC);

$error = '';
$subtotal = 0;
foreach ($cart_items as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}

// checkout
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $shipping_address = sanitize($_POST['shipping_address']);
    $payment_method = sanitize($_POST['payment_method']);
    $discount_code = sanitize($_POST['discount_code'] ?? '');
    
    $discount_amount = 0;
    $discount_valid = false;
    
    if (!empty($discount_code)) {
        $disc_stmt = $conn->prepare("SELECT * FROM discounts WHERE code = ? AND is_active = 1 AND (end_date IS NULL OR end_date >= CURDATE())");
        $disc_stmt->bind_param("s", $discount_code);
        $disc_stmt->execute();
        $discount = $disc_stmt->get_result()->fetch_assoc();
        
        if ($discount && $subtotal >= $discount['min_purchase']) {
            $discount_valid = true;
            if ($discount['discount_type'] === 'percentage') {
                $discount_amount = $subtotal * ($discount['discount_value'] / 100);
            } else {
                $discount_amount = $discount['discount_value'];
            }
        }
    }
    
    $final_amount = $subtotal - $discount_amount;
    
    $conn->begin_transaction();
    
    try {
        // Orders table no longer stores total_amount/final_amount; only keep discount_amount
        $order_stmt = $conn->prepare("INSERT INTO orders (user_id, discount_amount, status, shipping_address, payment_method) VALUES (?, ?, 'Pending', ?, ?)");
        $order_stmt->bind_param("idss", $user_id, $discount_amount, $shipping_address, $payment_method);
        $order_stmt->execute();
        $order_id = $conn->insert_id;
        
        foreach ($cart_items as $item) {
            if ($item['stock_quantity'] < $item['quantity']) {
                throw new Exception("Insufficient stock for " . $item['name']);
            }
            
            // order_items table no longer stores total_price; compute it later from unit_price * quantity
            $item_stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, unit_price) VALUES (?, ?, ?, ?)");
            $item_stmt->bind_param("iiid", $order_id, $item['product_id'], $item['quantity'], $item['price']);
            $item_stmt->execute();
            
            $stock_stmt = $conn->prepare("UPDATE products SET stock_quantity = stock_quantity - ? WHERE product_id = ?");
            $stock_stmt->bind_param("ii", $item['quantity'], $item['product_id']);
            $stock_stmt->execute();
            
            updateProductStatus($conn, $item['product_id']);
        }
        
        $clear_stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
        $clear_stmt->bind_param("i", $user_id);
        $clear_stmt->execute();
        
        $conn->commit();
        
        setAlert('success', 'Order placed successfully! Order ID: #' . $order_id);
        header("Location: order-receipt.php?id=" . $order_id);
        exit();
        
    } catch (Exception $e) {
        $conn->rollback();
        $error = $e->getMessage();
    }
}

$cart_count = getCartCount($conn, $user_id);
closeConnection($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - The Pop Stop</title>
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
                    <li><a href="orders.php">My Orders</a></li>
                    <li><a href="cart.php" class="cart-icon">
                        Cart
                        <?php if ($cart_count > 0): ?>
                            <span class="cart-count"><?php echo $cart_count; ?></span>
                        <?php endif; ?>
                    </a></li>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="container">
        <h1 style="margin-bottom: 2rem; color: var(--dark-brown);">Checkout</h1>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">
            <div>
                <div class="card">
                    <h3 style="color: var(--dark-brown); margin-bottom: 1.5rem;">Shipping Information</h3>
                    
                    <form method="POST">
                        <div class="form-group">
                            <label>Full Name</label>
                            <input type="text" value="<?php echo htmlspecialchars($user['full_name']); ?>" readonly>
                        </div>
                        
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
                        </div>
                        
                        <div class="form-group">
                            <label>Phone</label>
                            <input type="text" value="<?php echo htmlspecialchars($user['phone']); ?>" readonly>
                        </div>
                        
                        <div class="form-group">
                            <label for="shipping_address">Shipping Address *</label>
                            <textarea id="shipping_address" name="shipping_address" required><?php echo htmlspecialchars($user['address']); ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="payment_method">Payment Method *</label>
                            <select id="payment_method" name="payment_method" required>
                                <option value="">Select payment method</option>
                                <option value="Cash on Delivery">Cash on Delivery</option>
                                <option value="GCash">GCash</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="discount_code">Discount Code (Optional)</label>
                            <input type="text" id="discount_code" name="discount_code" placeholder="Enter discount code">
                            <small style="display: block; margin-top: 0.5rem; color: var(--secondary);">
                                Available codes: 
                                <?php foreach ($discounts as $disc): ?>
                                    <strong><?php echo htmlspecialchars($disc['code']); ?></strong> (<?php echo htmlspecialchars($disc['description']); ?>)<?php echo $disc !== end($discounts) ? ', ' : ''; ?>
                                <?php endforeach; ?>
                            </small>
                        </div>
                        
                        <button type="submit" class="btn btn-primary" style="width: 100%;">Place Order</button>
                    </form>
                </div>
            </div>
            
            <div>
                <div class="order-summary">
                    <h3 style="color: var(--dark-brown); margin-bottom: 1.5rem;">Order Summary</h3>
                    
                    <?php foreach ($cart_items as $item): ?>
                        <div class="summary-row">
                            <span><?php echo htmlspecialchars($item['name']); ?> (x<?php echo $item['quantity']; ?>)</span>
                            <span><?php echo formatCurrency($item['price'] * $item['quantity']); ?></span>
                        </div>
                    <?php endforeach; ?>
                    
                    <div class="summary-row summary-total">
                        <span>Total:</span>
                        <span><?php echo formatCurrency($subtotal); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer>
        <p>&copy; 2025 The Pop Stop. All rights reserved.</p>
    </footer>
</body>
</html>
