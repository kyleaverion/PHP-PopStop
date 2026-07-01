<?php
require_once 'config/database.php';
require_once 'config/functions.php';

requireLogin();

$conn = getConnection();
$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = sanitize($_POST['full_name']);
    $phone = sanitize($_POST['phone']);
    $address = sanitize($_POST['address']);
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($full_name)) {
        $error = 'Full name is required';
    } else {
        $profile_photo = $user['profile_photo'];
        
        if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
            $new_photo = uploadPhoto($_FILES['profile_photo'], 'uploads/profiles/', 'profile_');
            if ($new_photo) {
                if ($profile_photo && file_exists($profile_photo)) {
                    unlink($profile_photo);
                }
                $profile_photo = $new_photo;
            }
        }
        
        if (!empty($new_password)) {
            if (empty($current_password)) {
                $error = 'Current password is required to change password';
            } elseif (!password_verify($current_password, $user['password'])) {
                $error = 'Current password is incorrect';
            } elseif (strlen($new_password) < 6) {
                $error = 'New password must be at least 6 characters';
            } elseif ($new_password !== $confirm_password) {
                $error = 'New passwords do not match';
            } else {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE users SET full_name = ?, phone = ?, address = ?, profile_photo = ?, password = ? WHERE user_id = ?");
                $stmt->bind_param("sssssi", $full_name, $phone, $address, $profile_photo, $hashed_password, $user_id);
                
                if ($stmt->execute()) {
                    $success = 'Profile and password updated successfully!';
                    $_SESSION['full_name'] = $full_name;
                    $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
                    $stmt->bind_param("i", $user_id);
                    $stmt->execute();
                    $user = $stmt->get_result()->fetch_assoc();
                } else {
                    $error = 'Failed to update profile';
                }
            }
        } else {
            $stmt = $conn->prepare("UPDATE users SET full_name = ?, phone = ?, address = ?, profile_photo = ? WHERE user_id = ?");
            $stmt->bind_param("ssssi", $full_name, $phone, $address, $profile_photo, $user_id);
            
            if ($stmt->execute()) {
                $success = 'Profile updated successfully!';
                $_SESSION['full_name'] = $full_name;
                $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $user = $stmt->get_result()->fetch_assoc();
            } else {
                $error = 'Failed to update profile';
            }
        }
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
    <title>My Profile - The Pop Stop</title>
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
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="container">
        <h1 style="color: var(--dark-brown); margin-bottom: 2rem;">My Profile</h1>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <div class="card">
            <form method="POST" enctype="multipart/form-data" id="profileForm">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
                    <div>
                        <h3 style="color: var(--dark-brown); margin-bottom: 1rem;">Profile Information</h3>
                        
                        <div class="form-group">
                            <label>Username</label>
                            <input type="text" value="<?php echo htmlspecialchars($user['username']); ?>" disabled style="background: #f5f5f5;">
                            <small style="color: var(--secondary);">Username cannot be changed</small>
                        </div>
                        
                        <div class="form-group">
                            <label>Email</label>
                            <input type="text" value="<?php echo htmlspecialchars($user['email']); ?>" disabled style="background: #f5f5f5;">
                            <small style="color: var(--secondary);">Email cannot be changed</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="full_name">Full Name *</label>
                            <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="phone">Phone</label>
                            <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="address">Address</label>
                            <textarea id="address" name="address" rows="3"><?php echo htmlspecialchars($user['address']); ?></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="profile_photo">Profile Photo</label>
                            <?php if ($user['profile_photo'] && file_exists($user['profile_photo'])): ?>
                                <div style="margin-bottom: 1rem;">
                                    <img src="<?php echo htmlspecialchars($user['profile_photo']); ?>" alt="Profile" style="width: 150px; height: 150px; object-fit: cover; border-radius: 10px; border: 3px solid var(--primary);">
                                    <p style="margin-top: 0.5rem; font-size: 0.9rem; color: var(--secondary);">Current Photo</p>
                                </div>
                            <?php endif; ?>
                            <input type="file" id="profile_photo" name="profile_photo" accept="image/*">
                            <small style="color: var(--secondary);">Leave empty to keep current photo. Supported: JPG, PNG, GIF, WEBP</small>
                        </div>
                    </div>
                    
                    <div>
                        <h3 style="color: var(--dark-brown); margin-bottom: 1rem;">Change Password (Optional)</h3>
                        <p style="color: var(--secondary); margin-bottom: 1rem; font-size: 0.9rem;">Leave these fields empty if you don't want to change your password</p>
                        
                        <div class="form-group">
                            <label for="current_password">Current Password</label>
                            <input type="password" id="current_password" name="current_password">
                        </div>
                        
                        <div class="form-group">
                            <label for="new_password">New Password</label>
                            <input type="password" id="new_password" name="new_password">
                            <small style="color: var(--secondary);">Minimum 6 characters</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="confirm_password">Confirm New Password</label>
                            <input type="password" id="confirm_password" name="confirm_password">
                        </div>
                        
                        <div style="margin-top: 2rem; padding-top: 2rem; border-top: 1px solid var(--light-beige);">
                            <p style="margin-bottom: 1rem;"><strong>Account Information</strong></p>
                            <p style="font-size: 0.9rem;">Role: <span style="background: var(--primary); color: white; padding: 0.3rem 0.8rem; border-radius: 15px;"><?php echo ucfirst($user['role']); ?></span></p>
                            <p style="font-size: 0.9rem; margin-top: 0.5rem;">Member since: <?php echo formatDate($user['created_at']); ?></p>
                        </div>
                    </div>
                </div>
                
                <div style="margin-top: 2rem; display: flex; gap: 1rem;">
                    <button type="submit" class="btn btn-primary">Update Profile</button>
                    <a href="index.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <footer>
        <p>&copy; 2025 The Pop Stop. All rights reserved.</p>
    </footer>
</body>
</html>
