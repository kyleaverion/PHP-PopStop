<?php
require_once '../config/database.php';
require_once '../config/functions.php';

requireAdmin();

$conn = getConnection();

// CRUD operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_discount'])) {
        $code = strtoupper(sanitize($_POST['code']));
        $description = sanitize($_POST['description']);
        $discount_type = sanitize($_POST['discount_type']);
        $discount_value = floatval($_POST['discount_value']);
        $min_purchase = floatval($_POST['min_purchase']);
        $start_date = sanitize($_POST['start_date']);
        $end_date = sanitize($_POST['end_date']);
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        $stmt = $conn->prepare("INSERT INTO discounts (code, description, discount_type, discount_value, min_purchase, start_date, end_date, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssddssi", $code, $description, $discount_type, $discount_value, $min_purchase, $start_date, $end_date, $is_active);
        
        if ($stmt->execute()) {
            setAlert('success', 'Discount added successfully');
        } else {
            setAlert('error', 'Failed to add discount');
        }
        header("Location: discounts.php");
        exit();
    }
    
    if (isset($_POST['update_discount'])) {
        $discount_id = intval($_POST['discount_id']);
        $code = strtoupper(sanitize($_POST['code']));
        $description = sanitize($_POST['description']);
        $discount_type = sanitize($_POST['discount_type']);
        $discount_value = floatval($_POST['discount_value']);
        $min_purchase = floatval($_POST['min_purchase']);
        $start_date = sanitize($_POST['start_date']);
        $end_date = sanitize($_POST['end_date']);
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        $stmt = $conn->prepare("UPDATE discounts SET code = ?, description = ?, discount_type = ?, discount_value = ?, min_purchase = ?, start_date = ?, end_date = ?, is_active = ? WHERE discount_id = ?");
        $stmt->bind_param("sssddssii", $code, $description, $discount_type, $discount_value, $min_purchase, $start_date, $end_date, $is_active, $discount_id);
        
        if ($stmt->execute()) {
            setAlert('success', 'Discount updated successfully');
        } else {
            setAlert('error', 'Failed to update discount');
        }
        header("Location: discounts.php");
        exit();
    }
    
    if (isset($_POST['delete_discount'])) {
        $discount_id = intval($_POST['discount_id']);
        $stmt = $conn->prepare("DELETE FROM discounts WHERE discount_id = ?");
        $stmt->bind_param("i", $discount_id);
        
        if ($stmt->execute()) {
            setAlert('success', 'Discount deleted successfully');
        } else {
            setAlert('error', 'Failed to delete discount');
        }
        header("Location: discounts.php");
        exit();
    }
}

// discounts
$discounts = $conn->query("SELECT * FROM discounts ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);

closeConnection($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Discounts - Admin</title>
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
                <li><a href="purchase-orders.php">📋 Purchase Orders</a></li>
                <li><a href="discounts.php" class="active">🎟️ Discounts</a></li>
                <li><a href="reports.php">📈 Reports</a></li>
            </ul>
        </div>

        <div class="admin-content">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <h1 style="color: var(--dark-brown);">Manage Discounts</h1>
                <button onclick="showAddModal()" class="btn btn-primary">+ Add New Discount</button>
            </div>
            
            <?php showAlert(); ?>
            
            <div class="card">
                <table>
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Description</th>
                            <th>Type</th>
                            <th>Value</th>
                            <th>Min Purchase</th>
                            <th>Valid Period</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($discounts as $discount): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($discount['code']); ?></strong></td>
                                <td><?php echo htmlspecialchars($discount['description']); ?></td>
                                <td><?php echo ucfirst($discount['discount_type']); ?></td>
                                <td>
                                    <?php 
                                    if ($discount['discount_type'] === 'percentage') {
                                        echo $discount['discount_value'] . '%';
                                    } else {
                                        echo formatCurrency($discount['discount_value']);
                                    }
                                    ?>
                                </td>
                                <td><?php echo formatCurrency($discount['min_purchase']); ?></td>
                                <td><?php echo formatDate($discount['start_date']) . ' - ' . formatDate($discount['end_date']); ?></td>
                                <td>
                                    <?php if ($discount['is_active']): ?>
                                        <span style="color: #27AE60; font-weight: 600;">Active</span>
                                    <?php else: ?>
                                        <span style="color: #E74C3C; font-weight: 600;">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button onclick='showEditModal(<?php echo json_encode($discount); ?>)' class="btn btn-secondary btn-small">Edit</button>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="discount_id" value="<?php echo $discount['discount_id']; ?>">
                                        <button type="submit" name="delete_discount" class="btn btn-danger btn-small" 
                                                onclick="return confirm('Delete this discount?')">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Add Discount -->
    <div id="addModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Add New Discount</h2>
                <button class="modal-close" onclick="closeModal('addModal')">&times;</button>
            </div>
            <form method="POST">
                <div class="form-group">
                    <label>Code *</label>
                    <input type="text" name="code" required style="text-transform: uppercase;">
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <input type="text" name="description">
                </div>
                <div class="form-group">
                    <label>Discount Type *</label>
                    <select name="discount_type" required>
                        <option value="percentage">Percentage</option>
                        <option value="fixed">Fixed Amount</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Discount Value *</label>
                    <input type="number" step="0.01" name="discount_value" required>
                </div>
                <div class="form-group">
                    <label>Minimum Purchase</label>
                    <input type="number" step="0.01" name="min_purchase" value="0">
                </div>
                <div class="form-group">
                    <label>Start Date</label>
                    <input type="date" name="start_date">
                </div>
                <div class="form-group">
                    <label>End Date</label>
                    <input type="date" name="end_date">
                </div>
                <div class="form-group">
                    <label style="display: flex; align-items: center; gap: 0.5rem;">
                        <input type="checkbox" name="is_active" checked style="width: auto;">
                        Active
                    </label>
                </div>
                <button type="submit" name="add_discount" class="btn btn-primary" style="width: 100%;">Add Discount</button>
            </form>
        </div>
    </div>

    <!-- Edit Discount -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Edit Discount</h2>
                <button class="modal-close" onclick="closeModal('editModal')">&times;</button>
            </div>
            <form method="POST">
                <input type="hidden" name="discount_id" id="edit_discount_id">
                <div class="form-group">
                    <label>Code *</label>
                    <input type="text" name="code" id="edit_code" required style="text-transform: uppercase;">
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <input type="text" name="description" id="edit_description">
                </div>
                <div class="form-group">
                    <label>Discount Type *</label>
                    <select name="discount_type" id="edit_discount_type" required>
                        <option value="percentage">Percentage</option>
                        <option value="fixed">Fixed Amount</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Discount Value *</label>
                    <input type="number" step="0.01" name="discount_value" id="edit_discount_value" required>
                </div>
                <div class="form-group">
                    <label>Minimum Purchase</label>
                    <input type="number" step="0.01" name="min_purchase" id="edit_min_purchase">
                </div>
                <div class="form-group">
                    <label>Start Date</label>
                    <input type="date" name="start_date" id="edit_start_date">
                </div>
                <div class="form-group">
                    <label>End Date</label>
                    <input type="date" name="end_date" id="edit_end_date">
                </div>
                <div class="form-group">
                    <label style="display: flex; align-items: center; gap: 0.5rem;">
                        <input type="checkbox" name="is_active" id="edit_is_active" style="width: auto;">
                        Active
                    </label>
                </div>
                <button type="submit" name="update_discount" class="btn btn-primary" style="width: 100%;">Update Discount</button>
            </form>
        </div>
    </div>

    <script>
        function showAddModal() {
            document.getElementById('addModal').classList.add('active');
        }
        
        function showEditModal(discount) {
            document.getElementById('edit_discount_id').value = discount.discount_id;
            document.getElementById('edit_code').value = discount.code;
            document.getElementById('edit_description').value = discount.description;
            document.getElementById('edit_discount_type').value = discount.discount_type;
            document.getElementById('edit_discount_value').value = discount.discount_value;
            document.getElementById('edit_min_purchase').value = discount.min_purchase;
            document.getElementById('edit_start_date').value = discount.start_date;
            document.getElementById('edit_end_date').value = discount.end_date;
            document.getElementById('edit_is_active').checked = discount.is_active == 1;
            document.getElementById('editModal').classList.add('active');
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
