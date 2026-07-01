<?php
require_once '../config/database.php';
require_once '../config/functions.php';

requireAdmin();

$conn = getConnection();

// CRUD
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_supplier'])) {
        $brand = sanitize($_POST['brand']);
        $contact_person = sanitize($_POST['contact_person']);
        $email = sanitize($_POST['email']);
        $phone = sanitize($_POST['phone']);
        $address = sanitize($_POST['address']);
        
        $stmt = $conn->prepare("INSERT INTO suppliers (brand, contact_person, email, phone, address) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $brand, $contact_person, $email, $phone, $address);
        
        if ($stmt->execute()) {
            setAlert('success', 'Supplier added successfully');
        } else {
            setAlert('error', 'Failed to add supplier');
        }
        header("Location: suppliers.php");
        exit();
    }
    
    if (isset($_POST['update_supplier'])) {
        $supplier_id = intval($_POST['supplier_id']);
        $brand = sanitize($_POST['brand']);
        $contact_person = sanitize($_POST['contact_person']);
        $email = sanitize($_POST['email']);
        $phone = sanitize($_POST['phone']);
        $address = sanitize($_POST['address']);
        
        $stmt = $conn->prepare("UPDATE suppliers SET brand = ?, contact_person = ?, email = ?, phone = ?, address = ? WHERE supplier_id = ?");
        $stmt->bind_param("sssssi", $brand, $contact_person, $email, $phone, $address, $supplier_id);
        
        if ($stmt->execute()) {
            setAlert('success', 'Supplier updated successfully');
        } else {
            setAlert('error', 'Failed to update supplier');
        }
        header("Location: suppliers.php");
        exit();
    }
    
    if (isset($_POST['delete_supplier'])) {
        $supplier_id = intval($_POST['supplier_id']);
        $stmt = $conn->prepare("DELETE FROM suppliers WHERE supplier_id = ?");
        $stmt->bind_param("i", $supplier_id);
        
        if ($stmt->execute()) {
            setAlert('success', 'Supplier deleted successfully');
        } else {
            setAlert('error', 'Failed to delete supplier');
        }
        header("Location: suppliers.php");
        exit();
    }
}

$suppliers = $conn->query("SELECT * FROM suppliers ORDER BY brand")->fetch_all(MYSQLI_ASSOC);

closeConnection($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Suppliers - Admin</title>
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
                <li><a href="suppliers.php" class="active">🏭 Suppliers</a></li>
                <li><a href="purchase-orders.php">📋 Purchase Orders</a></li>
                <li><a href="discounts.php">🎟️ Discounts</a></li>
                <li><a href="reports.php">📈 Reports</a></li>
            </ul>
        </div>

        <div class="admin-content">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <h1 style="color: var(--dark-brown);">Manage Suppliers</h1>
                <button onclick="showAddModal()" class="btn btn-primary">+ Add New Supplier</button>
            </div>
            
            <?php showAlert(); ?>
            
            <div class="card">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Brand</th>
                            <th>Contact Person</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($suppliers as $supplier): ?>
                            <tr>
                                <td><?php echo $supplier['supplier_id']; ?></td>
                                <td><?php echo htmlspecialchars($supplier['brand']); ?></td>
                                <td><?php echo htmlspecialchars($supplier['contact_person']); ?></td>
                                <td><?php echo htmlspecialchars($supplier['email']); ?></td>
                                <td><?php echo htmlspecialchars($supplier['phone']); ?></td>
                                <td>
                                    <button onclick='showEditModal(<?php echo json_encode($supplier); ?>)' class="btn btn-secondary btn-small">Edit</button>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="supplier_id" value="<?php echo $supplier['supplier_id']; ?>">
                                        <button type="submit" name="delete_supplier" class="btn btn-danger btn-small" 
                                                onclick="return confirm('Are you sure you want to delete this supplier?')">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Add Supplier -->
    <div id="addModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Add New Supplier</h2>
                <button class="modal-close" onclick="closeModal('addModal')">&times;</button>
            </div>
            <form method="POST">
                <div class="form-group">
                    <label>Brand *</label>
                    <input type="text" name="brand" required>
                </div>
                <div class="form-group">
                    <label>Contact Person</label>
                    <input type="text" name="contact_person">
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email">
                </div>
                <div class="form-group">
                    <label>Phone</label>
                    <input type="text" name="phone">
                </div>
                <div class="form-group">
                    <label>Address</label>
                    <textarea name="address"></textarea>
                </div>
                <button type="submit" name="add_supplier" class="btn btn-primary" style="width: 100%;">Add Supplier</button>
            </form>
        </div>
    </div>

    <!-- Edit Supplier -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Edit Supplier</h2>
                <button class="modal-close" onclick="closeModal('editModal')">&times;</button>
            </div>
            <form method="POST">
                <input type="hidden" name="supplier_id" id="edit_supplier_id">
                <div class="form-group">
                    <label>Brand *</label>
                    <input type="text" name="brand" id="edit_brand" required>
                </div>
                <div class="form-group">
                    <label>Contact Person</label>
                    <input type="text" name="contact_person" id="edit_contact_person">
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" id="edit_email">
                </div>
                <div class="form-group">
                    <label>Phone</label>
                    <input type="text" name="phone" id="edit_phone">
                </div>
                <div class="form-group">
                    <label>Address</label>
                    <textarea name="address" id="edit_address"></textarea>
                </div>
                <button type="submit" name="update_supplier" class="btn btn-primary" style="width: 100%;">Update Supplier</button>
            </form>
        </div>
    </div>

    <script>
        function showAddModal() {
            document.getElementById('addModal').classList.add('active');
        }
        
        function showEditModal(supplier) {
            document.getElementById('edit_supplier_id').value = supplier.supplier_id;
            document.getElementById('edit_brand').value = supplier.brand;
            document.getElementById('edit_contact_person').value = supplier.contact_person;
            document.getElementById('edit_email').value = supplier.email;
            document.getElementById('edit_phone').value = supplier.phone;
            document.getElementById('edit_address').value = supplier.address;
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
