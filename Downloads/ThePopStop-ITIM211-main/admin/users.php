<?php
require_once '../config/database.php';
require_once '../config/functions.php';

requireAdmin();

$conn = getConnection();

// CRUD
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_user'])) {
        $username = sanitize($_POST['username']);
        $email = sanitize($_POST['email']);
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $full_name = sanitize($_POST['full_name']);
        $phone = sanitize($_POST['phone']);
        $address = sanitize($_POST['address']);
        $role = sanitize($_POST['role']);
        
        $stmt = $conn->prepare("INSERT INTO users (username, email, password, full_name, phone, address, role) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $username, $email, $password, $full_name, $phone, $address, $role);
        
        if ($stmt->execute()) {
            setAlert('success', 'User added successfully');
        } else {
            setAlert('error', 'Failed to add user');
        }
        header("Location: users.php");
        exit();
    }
    
    if (isset($_POST['update_user'])) {
        $user_id = intval($_POST['user_id']);
        $username = sanitize($_POST['username']);
        $email = sanitize($_POST['email']);
        $full_name = sanitize($_POST['full_name']);
        $phone = sanitize($_POST['phone']);
        $address = sanitize($_POST['address']);
        $role = sanitize($_POST['role']);
        
        if (!empty($_POST['password'])) {
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, password = ?, full_name = ?, phone = ?, address = ?, role = ? WHERE user_id = ?");
            $stmt->bind_param("sssssssi", $username, $email, $password, $full_name, $phone, $address, $role, $user_id);
        } else {
            $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, full_name = ?, phone = ?, address = ?, role = ? WHERE user_id = ?");
            $stmt->bind_param("ssssssi", $username, $email, $full_name, $phone, $address, $role, $user_id);
        }
        
        if ($stmt->execute()) {
            setAlert('success', 'User updated successfully');
        } else {
            setAlert('error', 'Failed to update user');
        }
        header("Location: users.php");
        exit();
    }
    
    if (isset($_POST['delete_user'])) {
        $user_id = intval($_POST['user_id']);
        
        $check_stmt = $conn->prepare("SELECT COUNT(*) as order_count FROM orders WHERE user_id = ?");
        $check_stmt->bind_param("i", $user_id);
        $check_stmt->execute();
        $result = $check_stmt->get_result()->fetch_assoc();
        
        $review_check = $conn->prepare("SELECT COUNT(*) as review_count FROM reviews WHERE user_id = ?");
        $review_check->bind_param("i", $user_id);
        $review_check->execute();
        $review_result = $review_check->get_result()->fetch_assoc();
        
        if ($result['order_count'] > 0) {
            $order_stmt = $conn->prepare("DELETE FROM orders WHERE user_id = ?");
            $order_stmt->bind_param("i", $user_id);
            $order_stmt->execute();
        }
        
        $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        
        if ($stmt->execute()) {
            setAlert('success', 'User and related orders deleted successfully');
        } else {
            setAlert('error', 'Failed to delete user');
        }
        
        header("Location: users.php");
        exit();
    }
    
    if (isset($_POST['toggle_active'])) {
        $user_id = intval($_POST['user_id']);
        $current_status = intval($_POST['current_status']);
        $new_status = $current_status == 1 ? 0 : 1;
        
        $stmt = $conn->prepare("UPDATE users SET is_active = ? WHERE user_id = ?");
        $stmt->bind_param("ii", $new_status, $user_id);
        
        if ($stmt->execute()) {
            $message = $new_status == 1 ? 'User activated successfully' : 'User deactivated successfully';
            setAlert('success', $message);
        } else {
            setAlert('error', 'Failed to update user status');
        }
        header("Location: users.php");
        exit();
    }
}

// Handle search
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';

if ($search) {
    $stmt = $conn->prepare("
        SELECT u.*, 
               (SELECT COUNT(*) FROM orders WHERE user_id = u.user_id) as order_count,
               (SELECT COUNT(*) FROM reviews WHERE user_id = u.user_id) as review_count
        FROM users u 
        WHERE (u.username REGEXP ? OR u.email REGEXP ? OR u.full_name REGEXP ?) 
        ORDER BY u.created_at DESC
    ");
    $search_term = preg_quote($search, '/');
    $stmt->bind_param("sss", $search_term, $search_term, $search_term);
    $stmt->execute();
    $users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
} else {
    $users = $conn->query("
        SELECT u.*, 
               (SELECT COUNT(*) FROM orders WHERE user_id = u.user_id) as order_count,
               (SELECT COUNT(*) FROM reviews WHERE user_id = u.user_id) as review_count
        FROM users u 
        ORDER BY u.created_at DESC
    ")->fetch_all(MYSQLI_ASSOC);
}

closeConnection($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Admin</title>
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
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <h1 style="color: var(--dark-brown);">Manage Users</h1>
                <button onclick="showAddModal()" class="btn btn-primary">+ Add New User</button>
            </div>
            
            <?php showAlert(); ?>
            
            <!-- Search Bar -->
            <div class="card" style="margin-bottom: 1.5rem;">
                <form method="GET" style="display: flex; gap: 1rem; align-items: end;">
                    <div class="form-group" style="flex: 1; margin-bottom: 0;">
                        <label>Search Users</label>
                        <input type="text" name="search" placeholder="Search by username, email, or name..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    <button type="submit" class="btn btn-primary">🔍 Search</button>
                    <?php if ($search): ?>
                        <a href="users.php" class="btn btn-secondary">Clear</a>
                    <?php endif; ?>
                </form>
            </div>
            
            <div class="card">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Full Name</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Activity</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo $user['user_id']; ?></td>
                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                <td>
                                    <span style="padding: 0.5rem 1rem; border-radius: 20px; background: <?php echo $user['role'] === 'admin' ? 'var(--secondary)' : 'var(--primary)'; ?>; color: white; font-size: 0.85rem;">
                                        <?php echo ucfirst($user['role']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span style="padding: 0.5rem 1rem; border-radius: 20px; background: <?php echo $user['is_active'] == 1 ? '#D5F4E6' : '#FADBD8'; ?>; color: <?php echo $user['is_active'] == 1 ? '#27AE60' : '#E74C3C'; ?>; font-size: 0.85rem; font-weight: 500;">
                                        <?php echo $user['is_active'] == 1 ? 'Active' : 'Inactive'; ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($user['order_count'] > 0 || $user['review_count'] > 0): ?>
                                        <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                                            <?php if ($user['order_count'] > 0): ?>
                                                <span style="padding: 0.3rem 0.6rem; border-radius: 15px; background: #E3F2FD; color: #1976D2; font-size: 0.75rem; font-weight: 500;">
                                                    📦 <?php echo $user['order_count']; ?> order<?php echo $user['order_count'] > 1 ? 's' : ''; ?>
                                                </span>
                                            <?php endif; ?>
                                            <?php if ($user['review_count'] > 0): ?>
                                                <span style="padding: 0.3rem 0.6rem; border-radius: 15px; background: #FFF3E0; color: #F57C00; font-size: 0.75rem; font-weight: 500;">
                                                    ⭐ <?php echo $user['review_count']; ?> review<?php echo $user['review_count'] > 1 ? 's' : ''; ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        <span style="color: #999; font-size: 0.85rem;">—</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo formatDate($user['created_at']); ?></td>
                                <td>
                                    <?php if ($user['role'] === 'customer'): ?>
                                        <a href="user-orders.php?user_id=<?php echo $user['user_id']; ?>" class="btn btn-primary btn-small">📋 Orders</a>
                                    <?php endif; ?>
                                    <button onclick='showEditModal(<?php echo json_encode($user); ?>)' class="btn btn-secondary btn-small">Edit</button>
                                    <?php if ($user['user_id'] != $_SESSION['user_id']): ?>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                            <input type="hidden" name="current_status" value="<?php echo $user['is_active']; ?>">
                                            <button type="submit" name="toggle_active" class="btn <?php echo $user['is_active'] == 1 ? 'btn-danger' : 'btn-primary'; ?> btn-small">
                                                <?php echo $user['is_active'] == 1 ? 'Deactivate' : 'Activate'; ?>
                                            </button>
                                        </form>
                                        <?php 
                                        $has_orders = $user['order_count'] > 0;
                                        $has_reviews = $user['review_count'] > 0;
                                        $confirm_message = 'Are you sure you want to delete this user?';
                                        if ($has_orders && $has_reviews) {
                                            $confirm_message = 'Are you sure you want to delete this user? This will also delete their orders and reviews.';
                                        } elseif ($has_orders) {
                                            $confirm_message = 'Are you sure you want to delete this user? This will also delete their orders.';
                                        } elseif ($has_reviews) {
                                            $confirm_message = 'Are you sure you want to delete this user? This will also delete their reviews.';
                                        }
                                        ?>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                            <button type="submit" name="delete_user" class="btn btn-danger btn-small" 
                                                    onclick="return confirm('<?php echo $confirm_message; ?>')">Delete</button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Add User Modal -->
    <div id="addModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Add New User</h2>
                <button class="modal-close" onclick="closeModal('addModal')">&times;</button>
            </div>
            <form method="POST">
                <div class="form-group">
                    <label>Username *</label>
                    <input type="text" name="username" required>
                </div>
                <div class="form-group">
                    <label>Email *</label>
                    <input type="email" name="email" required>
                </div>
                <div class="form-group">
                    <label>Password *</label>
                    <input type="password" name="password" required>
                </div>
                <div class="form-group">
                    <label>Full Name *</label>
                    <input type="text" name="full_name" required>
                </div>
                <div class="form-group">
                    <label>Phone</label>
                    <input type="text" name="phone">
                </div>
                <div class="form-group">
                    <label>Address</label>
                    <textarea name="address"></textarea>
                </div>
                <div class="form-group">
                    <label>Role *</label>
                    <select name="role" required>
                        <option value="customer">Customer</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <button type="submit" name="add_user" class="btn btn-primary" style="width: 100%;">Add User</button>
            </form>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Edit User</h2>
                <button class="modal-close" onclick="closeModal('editModal')">&times;</button>
            </div>
            <form method="POST">
                <input type="hidden" name="user_id" id="edit_user_id">
                <div class="form-group">
                    <label>Username *</label>
                    <input type="text" name="username" id="edit_username" required>
                </div>
                <div class="form-group">
                    <label>Email *</label>
                    <input type="email" name="email" id="edit_email" required>
                </div>
                <div class="form-group">
                    <label>Password (leave blank to keep current)</label>
                    <input type="password" name="password" id="edit_password">
                </div>
                <div class="form-group">
                    <label>Full Name *</label>
                    <input type="text" name="full_name" id="edit_full_name" required>
                </div>
                <div class="form-group">
                    <label>Phone</label>
                    <input type="text" name="phone" id="edit_phone">
                </div>
                <div class="form-group">
                    <label>Address</label>
                    <textarea name="address" id="edit_address"></textarea>
                </div>
                <div class="form-group">
                    <label>Role *</label>
                    <select name="role" id="edit_role" required>
                        <option value="customer">Customer</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <button type="submit" name="update_user" class="btn btn-primary" style="width: 100%;">Update User</button>
            </form>
        </div>
    </div>

    <script>
        function showAddModal() {
            document.getElementById('addModal').classList.add('active');
        }
        
        function showEditModal(user) {
            document.getElementById('edit_user_id').value = user.user_id;
            document.getElementById('edit_username').value = user.username;
            document.getElementById('edit_email').value = user.email;
            document.getElementById('edit_full_name').value = user.full_name;
            document.getElementById('edit_phone').value = user.phone || '';
            document.getElementById('edit_address').value = user.address || '';
            document.getElementById('edit_role').value = user.role;
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
