<?php
require_once 'config/database.php';
require_once 'config/functions.php';

requireLogin();

$conn = getConnection();
$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_cart'])) {
        $cart_id = intval($_POST['cart_id']);
        $quantity = intval($_POST['quantity']);
        
        if ($quantity > 0) {
            $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE cart_id = ? AND user_id = ?");
            $stmt->bind_param("iii", $quantity, $cart_id, $user_id);
            $stmt->execute();
            setAlert('success', 'Cart updated');
        }
    } elseif (isset($_POST['remove_item'])) {
        $cart_id = intval($_POST['cart_id']);
        $stmt = $conn->prepare("DELETE FROM cart WHERE cart_id = ? AND user_id = ?");
        $stmt->bind_param("ii", $cart_id, $user_id);
        $stmt->execute();
        setAlert('success', 'Item removed from cart');
    }
    
    header("Location: cart.php");
    exit();
}

$stmt = $conn->prepare("
    SELECT c.*, p.name, p.price, p.stock_quantity, p.series, p.brand, p.image_url
    FROM cart c
    JOIN products p ON c.product_id = p.product_id
    WHERE c.user_id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$cart_items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$cart_count = getCartCount($conn, $user_id);
$subtotal = 0;

$user_stmt = $conn->prepare("SELECT profile_photo FROM users WHERE user_id = ?");
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_data = $user_stmt->get_result()->fetch_assoc();

closeConnection($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - The Pop Stop</title>
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
                    <?php if (isAdmin()): ?>
                        <li><a href="admin/dashboard.php">Admin Panel</a></li>
                    <?php endif; ?>
                    <?php include 'includes/profile_dropdown.php'; ?>
                </ul>
            </nav>
        </div>
    </header>

    <div class="container">
        <h1 style="margin-bottom: 2rem; color: var(--dark-brown);">Shopping Cart</h1>
        
        <?php showAlert(); ?>
        
        <?php if (count($cart_items) > 0): ?>
            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">
                <div>
                    <?php foreach ($cart_items as $item): ?>
                        <?php
                        $item_total = $item['price'] * $item['quantity'];
                        $subtotal += $item_total;
                        ?>
                        <div class="cart-item">
                            <div class="cart-item-image">
                                <?php if (!empty($item['image_url']) && file_exists($item['image_url'])): ?>
                                    <img src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" style="width: 100%; height: 100%; object-fit: cover; border-radius: 8px;">
                                <?php else: ?>
                                    <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; background: var(--light-beige); border-radius: 8px; font-size: 2rem;">📦</div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="cart-item-details">
                                <div class="cart-item-title"><?php echo htmlspecialchars($item['name']); ?></div>
                                <div style="color: var(--secondary); font-size: 0.9rem;">
                                    <?php echo htmlspecialchars($item['series']); ?> - <?php echo htmlspecialchars($item['brand']); ?>
                                </div>
                                <div class="cart-item-price"><?php echo formatCurrency($item['price']); ?> each</div>
                                <div style="font-size: 0.9rem; color: var(--text-dark);">
                                    Subtotal: <?php echo formatCurrency($item_total); ?>
                                </div>
                            </div>
                            
                            <div class="cart-item-actions">
                                <form method="POST" style="display: flex; gap: 0.5rem; align-items: center;">
                                    <input type="hidden" name="cart_id" value="<?php echo $item['cart_id']; ?>">
                                    <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" 
                                           min="1" max="<?php echo $item['stock_quantity']; ?>" class="quantity-input">
                                    <button type="submit" name="update_cart" class="btn btn-secondary btn-small">Update</button>
                                </form>
                                
                                <form method="POST">
                                    <input type="hidden" name="cart_id" value="<?php echo $item['cart_id']; ?>">
                                    <button type="submit" name="remove_item" class="btn btn-danger btn-small" 
                                            onclick="return confirm('Remove this item from cart?')">Remove</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div>
                    <div class="order-summary">
                        <h3 style="color: var(--dark-brown); margin-bottom: 1.5rem;">Order Summary</h3>
                        
                        <div class="summary-row">
                            <span>Subtotal:</span>
                            <span><?php echo formatCurrency($subtotal); ?></span>
                        </div>
                        
                        <div class="summary-row summary-total">
                            <span>Total:</span>
                            <span><?php echo formatCurrency($subtotal); ?></span>
                        </div>
                        
                        <a href="checkout.php" class="btn btn-primary" style="width: 100%; margin-top: 1.5rem; display: block; text-align: center;">
                            Proceed to Checkout
                        </a>
                        
                        <a href="products.php" class="btn btn-secondary" style="width: 100%; margin-top: 1rem; display: block; text-align: center;">
                            Continue Shopping
                        </a>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="card" style="text-align: center; padding: 3rem;">
                <h2 style="color: var(--dark-brown); margin-bottom: 1rem;">Your cart is empty</h2>
                <p style="margin-bottom: 2rem;">Start shopping and add some items to your cart!</p>
                <a href="products.php" class="btn btn-primary">Browse Products</a>
            </div>
        <?php endif; ?>
    </div>

    <footer>
        <p>&copy; 2025 The Pop Stop. All rights reserved.</p>
    </footer>
</body>
</html>
