<?php
require_once 'config/database.php';
require_once 'config/functions.php';

startSession();

if (isLoggedIn()) {
    header("Location: index.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username']);
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $full_name = sanitize($_POST['full_name']);
    $phone = sanitize($_POST['phone']);
    $address = sanitize($_POST['address']);
    
    if (empty($username) || empty($email) || empty($password) || empty($full_name)) {
        $error = 'Please fill in all required fields';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format';
    } else {
        $conn = getConnection();
        
        $stmt = $conn->prepare("SELECT user_id FROM users WHERE username = ? OR email = ?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = 'Username or email already exists';
        } else {
            $profile_photo = null;
            if (isset($_FILES['profile_photo'])) {
                $profile_photo = uploadPhoto($_FILES['profile_photo'], 'uploads/profiles/', 'profile_');
            }
            
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (username, email, password, full_name, phone, address, profile_photo) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssss", $username, $email, $hashed_password, $full_name, $phone, $address, $profile_photo);
            
            if ($stmt->execute()) {
                $success = 'Registration successful! You can now login.';
            } else {
                $error = 'Registration failed. Please try again.';
            }
        }
        
        closeConnection($conn);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - The Pop Stop</title>
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
                    <li><a href="login.php">Login</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="auth-container">
        <div class="auth-card">
            <h2>Register</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <form method="POST" id="registerForm" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="username">Username *</label>
                    <input type="text" id="username" name="username" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                    <small class="error-text" id="usernameError"></small>
                </div>
                
                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="text" id="email" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    <small class="error-text" id="emailError"></small>
                </div>
                
                <div class="form-group">
                    <label for="full_name">Full Name *</label>
                    <input type="text" id="full_name" name="full_name" value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>">
                    <small class="error-text" id="fullNameError"></small>
                </div>
                
                <div class="form-group">
                    <label for="phone">Phone</label>
                    <input type="text" id="phone" name="phone" value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="address">Address</label>
                    <textarea id="address" name="address"><?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="profile_photo">Profile Photo</label>
                    <input type="file" id="profile_photo" name="profile_photo" accept="image/*">
                    <small style="color: var(--secondary);">Supported: JPG, PNG, GIF, WEBP</small>
                    <small class="error-text" id="photoError"></small>
                </div>
                
                <div class="form-group">
                    <label for="password">Password *</label>
                    <input type="password" id="password" name="password">
                    <small class="error-text" id="passwordError"></small>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm Password *</label>
                    <input type="password" id="confirm_password" name="confirm_password">
                    <small class="error-text" id="confirmError"></small>
                </div>
                
                <button type="submit" class="btn btn-primary" style="width: 100%;">Register</button>
            </form>
            
            <div class="auth-links">
                <p>Already have an account? <a href="login.php">Login here</a></p>
            </div>
        </div>
    </div>

    <footer>
        <p>&copy; 2025 The Pop Stop. All rights reserved.</p>
    </footer>
    
    <script>
    document.getElementById('registerForm').addEventListener('submit', function(e) {
        let isValid = true;
        
        // Clear previous errors
        document.getElementById('usernameError').textContent = '';
        document.getElementById('emailError').textContent = '';
        document.getElementById('fullNameError').textContent = '';
        document.getElementById('passwordError').textContent = '';
        document.getElementById('confirmError').textContent = '';
        document.getElementById('photoError').textContent = '';
        
        // Get values
        const username = document.getElementById('username').value.trim();
        const email = document.getElementById('email').value.trim();
        const fullName = document.getElementById('full_name').value.trim();
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirm_password').value;
        const photo = document.getElementById('profile_photo').files[0];
        
        // Validation
        if (username === '') {
            document.getElementById('usernameError').textContent = 'Username is required';
            isValid = false;
        } else if (username.length < 3) {
            document.getElementById('usernameError').textContent = 'Username must be at least 3 characters';
            isValid = false;
        }
        
        if (email === '') {
            document.getElementById('emailError').textContent = 'Email is required';
            isValid = false;
        } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            document.getElementById('emailError').textContent = 'Please enter a valid email address';
            isValid = false;
        }
        
        if (fullName === '') {
            document.getElementById('fullNameError').textContent = 'Full name is required';
            isValid = false;
        }
        
        if (password === '') {
            document.getElementById('passwordError').textContent = 'Password is required';
            isValid = false;
        } else if (password.length < 6) {
            document.getElementById('passwordError').textContent = 'Password must be at least 6 characters';
            isValid = false;
        }
        
        if (confirmPassword === '') {
            document.getElementById('confirmError').textContent = 'Please confirm your password';
            isValid = false;
        } else if (password !== confirmPassword) {
            document.getElementById('confirmError').textContent = 'Passwords do not match';
            isValid = false;
        }
        
        if (photo) {
            const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
            if (!allowedTypes.includes(photo.type)) {
                document.getElementById('photoError').textContent = 'Only JPG, PNG, GIF, and WEBP images are allowed';
                isValid = false;
            } else if (photo.size > 5 * 1024 * 1024) {
                document.getElementById('photoError').textContent = 'Image size must be less than 5MB';
                isValid = false;
            }
        }
        
        if (!isValid) {
            e.preventDefault();
        }
    });
    </script>
</body>
</html>
