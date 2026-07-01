<?php
require_once 'config/database.php';
require_once 'config/functions.php';

requireLogin();

if (!isset($_GET['id'])) {
    header("Location: orders.php");
    exit();
}

$conn = getConnection();
$order_id = intval($_GET['id']);
$user_id = $_SESSION['user_id'];

// order details
$receipt = generateReceipt($conn, $order_id);

if (!$receipt || $receipt['order']['user_id'] != $user_id) {
    header("Location: orders.php");
    exit();
}

$order = $receipt['order'];
$items = $receipt['items'];

$cart_count = getCartCount($conn, $user_id);
closeConnection($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Receipt #<?php echo $order_id; ?> - The Pop Stop</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <style>
        @media print {
            header, footer, .no-print { display: none; }
        }
    </style>
</head>
<body>
    <header class="no-print">
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
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="container">
        <div class="card">
            <div style="text-align: center; margin-bottom: 2rem;">
                <h1 style="color: var(--dark-brown); font-family: 'Playfair Display', serif;">The Pop Stop</h1>
                <p style="color: var(--secondary);">Order Receipt</p>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 2rem;">
                <div>
                    <h3 style="color: var(--dark-brown); margin-bottom: 1rem;">Order Information</h3>
                    <p><strong>Order ID:</strong> #<?php echo $order['order_id']; ?></p>
                    <p><strong>Order Date:</strong> <?php echo formatDateTime($order['order_date']); ?></p>
                    <p><strong>Status:</strong> 
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
                    </p>
                    <p><strong>Payment Method:</strong> <?php echo htmlspecialchars($order['payment_method']); ?></p>
                </div>
                
                <div>
                    <h3 style="color: var(--dark-brown); margin-bottom: 1rem;">Customer Information</h3>
                    <p><strong>Name:</strong> <?php echo htmlspecialchars($order['full_name']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($order['email']); ?></p>
                    <p><strong>Phone:</strong> <?php echo htmlspecialchars($order['phone']); ?></p>
                    <p><strong>Shipping Address:</strong><br><?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?></p>
                </div>
            </div>
            
            <h3 style="color: var(--dark-brown); margin-bottom: 1rem;">Order Items</h3>
            <table style="margin-bottom: 2rem;">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>SKU</th>
                        <th>Quantity</th>
                        <th>Unit Price</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['name']); ?></td>
                            <td><?php echo htmlspecialchars($item['sku']); ?></td>
                            <td><?php echo $item['quantity']; ?></td>
                            <td><?php echo formatCurrency($item['unit_price']); ?></td>
                            <td><?php echo formatCurrency($item['unit_price'] * $item['quantity']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <div style="text-align: right; margin-top: 2rem;">
                <div style="display: inline-block; text-align: left; min-width: 300px;">
                    <div style="display: flex; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid var(--border);">
                        <span>Subtotal:</span>
                        <span><?php echo formatCurrency($order['total_amount']); ?></span>
                    </div>
                    
                    <?php if ($order['discount_amount'] > 0): ?>
                        <div style="display: flex; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid var(--border); color: #27AE60;">
                            <span>Discount:</span>
                            <span>-<?php echo formatCurrency($order['discount_amount']); ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <div style="display: flex; justify-content: space-between; padding: 1rem 0; font-size: 1.3rem; font-weight: bold; color: var(--dark-brown);">
                        <span>Total:</span>
                        <span><?php echo formatCurrency($order['final_amount']); ?></span>
                    </div>
                </div>
            </div>
            
            <?php if ($order['notes']): ?>
                <div style="margin-top: 2rem; padding: 1rem; background: var(--light-beige); border-radius: 10px;">
                    <strong>Notes:</strong> <?php echo nl2br(htmlspecialchars($order['notes'])); ?>
                </div>
            <?php endif; ?>
            
            <div class="no-print" style="text-align: center; margin-top: 2rem; display: flex; gap: 1rem; justify-content: center;">
                <button onclick="window.print()" class="btn btn-primary">Print Receipt</button>
                <button onclick="downloadPDF()" class="btn btn-success">Download PDF</button>
                <a href="orders.php" class="btn btn-secondary">Back to Orders</a>
            </div>
            
            <script>
            function downloadPDF() {
                const element = document.querySelector('.card');
                
                const opt = {
                    margin: 10,
                    filename: 'Receipt_Order_<?php echo $order['order_id']; ?>.pdf',
                    image: { type: 'jpeg', quality: 0.98 },
                    html2canvas: { scale: 2, logging: false },
                    jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
                };
                
                html2pdf().set(opt).from(element).save();
            }
            </script>
        </div>
    </div>

    <footer class="no-print">
        <p>&copy; 2025 The Pop Stop. All rights reserved.</p>
    </footer>
</body>
</html>
