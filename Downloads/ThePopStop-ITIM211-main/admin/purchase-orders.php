<?php
require_once '../config/database.php';
require_once '../config/functions.php';

requireAdmin();

$conn = getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create_po'])) {
        $supplier_id = intval($_POST['supplier_id']);
        $order_date = sanitize($_POST['order_date']);
        $notes = sanitize($_POST['notes']);
        
        $conn->begin_transaction();
        
        try {
            // purchase order (no stored total_cost column)
            $stmt = $conn->prepare("INSERT INTO purchase_orders (supplier_id, order_date, notes) VALUES (?, ?, ?)");
            $stmt->bind_param("iss", $supplier_id, $order_date, $notes);
            $stmt->execute();
            $po_id = $conn->insert_id;
            
            // Add items
            $product_ids = $_POST['product_ids'];
            $quantities = $_POST['quantities'];
            $unit_costs = $_POST['unit_costs'];
            
            for ($i = 0; $i < count($product_ids); $i++) {
                if (!empty($product_ids[$i]) && !empty($quantities[$i]) && !empty($unit_costs[$i])) {
                    $product_id = intval($product_ids[$i]);
                    $quantity = intval($quantities[$i]);
                    $unit_cost = floatval($unit_costs[$i]);
                    
                    // purchase_order_items no longer stores total_cost; compute from quantity * unit_cost when needed
                    $item_stmt = $conn->prepare("INSERT INTO purchase_order_items (po_id, product_id, quantity, unit_cost) VALUES (?, ?, ?, ?)");
                    $item_stmt->bind_param("iiid", $po_id, $product_id, $quantity, $unit_cost);
                    $item_stmt->execute();
                }
            }
            
            $conn->commit();
            setAlert('success', 'Purchase order created successfully');
        } catch (Exception $e) {
            $conn->rollback();
            setAlert('error', 'Failed to create purchase order: ' . $e->getMessage());
        }
        
        header("Location: purchase-orders.php");
        exit();
    }
    
    if (isset($_POST['update_po_status'])) {
        $po_id = intval($_POST['po_id']);
        $status = sanitize($_POST['status']);
        
        $stmt = $conn->prepare("UPDATE purchase_orders SET status = ? WHERE po_id = ?");
        $stmt->bind_param("si", $status, $po_id);
        
        if ($stmt->execute()) {
            // update stock
            if ($status === 'Received') {
                $items = $conn->query("SELECT product_id, quantity FROM purchase_order_items WHERE po_id = $po_id")->fetch_all(MYSQLI_ASSOC);
                foreach ($items as $item) {
                    $update = $conn->prepare("UPDATE products SET stock_quantity = stock_quantity + ? WHERE product_id = ?");
                    $update->bind_param("ii", $item['quantity'], $item['product_id']);
                    $update->execute();
                    updateProductStatus($conn, $item['product_id']);
                }
            }
            setAlert('success', 'Purchase order status updated');
        } else {
            setAlert('error', 'Failed to update status');
        }
        header("Location: purchase-orders.php");
        exit();
    }
}

$pos = $conn->query("
    SELECT po.*, s.brand as supplier_brand,
           (SELECT COUNT(*) FROM purchase_order_items WHERE po_id = po.po_id) as item_count,
           COALESCE(
               (SELECT SUM(oi.quantity * oi.unit_cost)
                FROM purchase_order_items oi
                WHERE oi.po_id = po.po_id
               ), 0) AS total_cost
    FROM purchase_orders po
    LEFT JOIN suppliers s ON po.supplier_id = s.supplier_id
    ORDER BY po.order_date DESC
")->fetch_all(MYSQLI_ASSOC);

$suppliers = $conn->query("SELECT * FROM suppliers ORDER BY brand")->fetch_all(MYSQLI_ASSOC);
$products = $conn->query("SELECT product_id, name, sku FROM products ORDER BY name")->fetch_all(MYSQLI_ASSOC);

closeConnection($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Orders - Admin</title>
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
                <li><a href="users.php">👥 Users</a></li>
                <li><a href="suppliers.php">🏭 Suppliers</a></li>
                <li><a href="purchase-orders.php" class="active">📋 Purchase Orders</a></li>
                <li><a href="discounts.php">🎟️ Discounts</a></li>
                <li><a href="reports.php">📈 Reports</a></li>
            </ul>
        </div>

        <div class="admin-content">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <h1 style="color: var(--dark-brown);">Purchase Orders</h1>
                <button onclick="showCreateModal()" class="btn btn-primary">+ Create Purchase Order</button>
            </div>
            
            <?php showAlert(); ?>
            
            <div class="card">
                <table>
                    <thead>
                        <tr>
                            <th>PO ID</th>
                            <th>Supplier</th>
                            <th>Order Date</th>
                            <th>Items</th>
                            <th>Total Cost</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pos as $po): ?>
                            <tr>
                                <td>#<?php echo $po['po_id']; ?></td>
                                <td><?php echo htmlspecialchars($po['supplier_brand']); ?></td>
                                <td><?php echo formatDate($po['order_date']); ?></td>
                                <td><?php echo $po['item_count']; ?> item(s)</td>
                                <td><?php echo formatCurrency($po['total_cost']); ?></td>
                                <td><?php echo $po['status']; ?></td>
                                <td>
                                    <button onclick='showStatusModal(<?php echo $po["po_id"]; ?>, "<?php echo $po["status"]; ?>")' class="btn btn-secondary btn-small">Update Status</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Create PO -->
    <div id="createModal" class="modal">
        <div class="modal-content" style="max-width: 800px;">
            <div class="modal-header">
                <h2>Create Purchase Order</h2>
                <button class="modal-close" onclick="closeModal('createModal')">&times;</button>
            </div>
            <form method="POST">
                <div class="form-group">
                    <label>Supplier *</label>
                    <select name="supplier_id" required>
                        <option value="">Select supplier</option>
                        <?php foreach ($suppliers as $supplier): ?>
                            <option value="<?php echo $supplier['supplier_id']; ?>"><?php echo htmlspecialchars($supplier['brand']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Order Date *</label>
                    <input type="date" name="order_date" value="<?php echo date('Y-m-d'); ?>" required>
                </div>
                <div class="form-group">
                    <label>Notes</label>
                    <textarea name="notes"></textarea>
                </div>
                
                <h3 style="margin: 1.5rem 0;">Order Items</h3>
                <div id="itemsContainer">
                    <div class="po-item" style="display: grid; grid-template-columns: 2fr 1fr 1fr auto; gap: 1rem; margin-bottom: 1rem; align-items: end;">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label>Product</label>
                            <select name="product_ids[]">
                                <option value="">Select product</option>
                                <?php foreach ($products as $product): ?>
                                    <option value="<?php echo $product['product_id']; ?>"><?php echo htmlspecialchars($product['name']); ?> (<?php echo htmlspecialchars($product['sku']); ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label>Quantity</label>
                            <input type="number" name="quantities[]" min="1">
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label>Unit Cost</label>
                            <input type="number" step="0.01" name="unit_costs[]">
                        </div>
                        <button type="button" onclick="this.parentElement.remove()" class="btn btn-danger btn-small">Remove</button>
                    </div>
                </div>
                <button type="button" onclick="addItem()" class="btn btn-secondary btn-small">+ Add Item</button>
                
                <button type="submit" name="create_po" class="btn btn-primary" style="width: 100%; margin-top: 1.5rem;">Create Purchase Order</button>
            </form>
        </div>
    </div>

    <!-- Update Status -->
    <div id="statusModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Update PO Status</h2>
                <button class="modal-close" onclick="closeModal('statusModal')">&times;</button>
            </div>
            <form method="POST">
                <input type="hidden" name="po_id" id="status_po_id">
                <div class="form-group">
                    <label>Status *</label>
                    <select name="status" id="status_value" required>
                        <option value="Ordered">Ordered</option>
                        <option value="Shipped">Shipped</option>
                        <option value="Received">Received</option>
                        <option value="Cancelled">Cancelled</option>
                    </select>
                </div>
                <button type="submit" name="update_po_status" class="btn btn-primary" style="width: 100%;">Update Status</button>
            </form>
        </div>
    </div>

    <script>
        function showCreateModal() {
            document.getElementById('createModal').classList.add('active');
        }
        
        function showStatusModal(poId, currentStatus) {
            document.getElementById('status_po_id').value = poId;
            document.getElementById('status_value').value = currentStatus;
            document.getElementById('statusModal').classList.add('active');
        }
        
        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('active');
        }
        
        function addItem() {
            const container = document.getElementById('itemsContainer');
            const newItem = container.children[0].cloneNode(true);
            newItem.querySelectorAll('select, input').forEach(el => el.value = '');
            container.appendChild(newItem);
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
