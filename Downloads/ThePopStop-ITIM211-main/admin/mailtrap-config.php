<?php
require_once '../config/database.php';
require_once '../config/functions.php';

requireAdmin();

$message = '';
$current_config = [
    'username' => 'your_mailtrap_username',
    'password' => 'your_mailtrap_password'
];
 
$functions_file = __DIR__ . '/../config/functions.php';
$functions_content = file_get_contents($functions_file);

if (preg_match("/\\\$mail->Username = '([^']+)';/", $functions_content, $matches)) {
    $current_config['username'] = $matches[1];
}
if (preg_match("/\\\$mail->Password = '([^']+)';/", $functions_content, $matches)) {
    $current_config['password'] = $matches[1];
}

// form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_config'])) {
    $username = $_POST['mailtrap_username'];
    $password = $_POST['mailtrap_password'];
    
    $new_content = preg_replace(
        "/\\\$mail->Username = '[^']+';/",
        "\$mail->Username = '{$username}';",
        $functions_content
    );
    
    $new_content = preg_replace(
        "/\\\$mail->Password = '[^']+';/",
        "\$mail->Password = '{$password}';",
        $new_content
    );
    
    if (file_put_contents($functions_file, $new_content)) {
        $message = '<div class="alert alert-success">✅ Mailtrap credentials saved successfully!</div>';
        $current_config['username'] = $username;
        $current_config['password'] = $password;
    } else {
        $message = '<div class="alert alert-error">❌ Failed to save configuration. Check file permissions.</div>';
    }
}

if (isset($_POST['test_email'])) {
    $conn = getConnection();
    
    $test_order = $conn->query("SELECT * FROM orders LIMIT 1")->fetch_assoc();
    
    if ($test_order) {
        $user = $conn->query("SELECT * FROM users WHERE user_id = {$test_order['user_id']}")->fetch_assoc();
        
        if ($user) {
            $result = sendOrderEmail($conn, $test_order['order_id'], $user['email'], $user['full_name']);
            
            if ($result) {
                $message = '<div class="alert alert-success">✅ Test email sent successfully! Check your Mailtrap inbox.</div>';
            } else {
                $message = '<div class="alert alert-error">❌ Failed to send test email. Check error logs in logs/emails/ folder.</div>';
            }
        }
    } else {
        $message = '<div class="alert alert-error">❌ No orders found to test with.</div>';
    }
    
    closeConnection($conn);
}

$is_configured = $current_config['username'] !== 'your_mailtrap_username';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mailtrap Configuration - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .config-card {
            max-width: 700px;
            margin: 0 auto;
        }
        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 0.9em;
        }
        .status-configured {
            background: #d4edda;
            color: #155724;
        }
        .status-not-configured {
            background: #f8d7da;
            color: #721c24;
        }
        .info-box {
            background: #e3f2fd;
            border-left: 4px solid #2196F3;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }
        .steps {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .steps ol {
            margin-left: 20px;
        }
        .steps li {
            margin: 10px 0;
        }
        code {
            background: #f4f4f4;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: monospace;
        }
    </style>
</head>
<body>
    <header>
        <div class="header-container">
            <a href="../index.php" class="logo">The Pop Stop</a>
            <nav>
                <ul>
                    <li><a href="../index.php">View Site</a></li>
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="orders.php">Orders</a></li>
                    <li><a href="view-emails.php">View Emails</a></li>
                    <li><a href="../logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="container">
        <div class="config-card">
            <h1 style="margin-bottom: 1rem; color: var(--dark-brown);">📧 Mailtrap Configuration</h1>
            
            <div style="margin-bottom: 2rem;">
                <strong>Status: </strong>
                <span class="status-badge <?php echo $is_configured ? 'status-configured' : 'status-not-configured'; ?>">
                    <?php echo $is_configured ? '✅ Configured' : '⚠️ Not Configured'; ?>
                </span>
            </div>

            <?php echo $message; ?>

            <div class="info-box">
                <h3 style="margin-top: 0; color: #1976D2;">📋 How to Get Mailtrap Credentials:</h3>
                <div class="steps">
                    <ol>
                        <li>Go to <a href="https://mailtrap.io" target="_blank"><strong>https://mailtrap.io</strong></a></li>
                        <li>Sign up for a <strong>free account</strong></li>
                        <li>Navigate to <strong>Email Testing → Inboxes</strong></li>
                        <li>Click on your inbox</li>
                        <li>Go to <strong>SMTP Settings</strong> tab</li>
                        <li>Copy your <strong>Username</strong> and <strong>Password</strong></li>
                        <li>Paste them below and click <strong>Save</strong></li>
                    </ol>
                </div>
            </div>

            <div class="card">
                <h2 style="color: var(--dark-brown);">SMTP Credentials</h2>
                
                <form method="POST" style="margin-top: 20px;">
                    <div class="form-group">
                        <label>Mailtrap Host</label>
                        <input type="text" value="sandbox.smtp.mailtrap.io" disabled style="background: #f5f5f5;">
                        <small>This is the default Mailtrap SMTP host</small>
                    </div>

                    <div class="form-group">
                        <label>Port</label>
                        <input type="text" value="2525" disabled style="background: #f5f5f5;">
                        <small>Default port for Mailtrap</small>
                    </div>

                    <div class="form-group">
                        <label>Username <span style="color: red;">*</span></label>
                        <input type="text" name="mailtrap_username" value="<?php echo htmlspecialchars($current_config['username']); ?>" required>
                        <small>Your Mailtrap SMTP username</small>
                    </div>

                    <div class="form-group">
                        <label>Password <span style="color: red;">*</span></label>
                        <input type="text" name="mailtrap_password" value="<?php echo htmlspecialchars($current_config['password']); ?>" required>
                        <small>Your Mailtrap SMTP password</small>
                    </div>

                    <div style="display: flex; gap: 10px; margin-top: 20px;">
                        <button type="submit" name="save_config" class="btn btn-primary">
                            💾 Save Configuration
                        </button>
                        
                        <?php if ($is_configured): ?>
                        <button type="submit" name="test_email" class="btn btn-secondary">
                            📧 Send Test Email
                        </button>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <?php if ($is_configured): ?>
            <div class="card" style="margin-top: 20px; background: #d4edda;">
                <h3 style="margin-top: 0; color: #155724;">✅ Configuration Complete!</h3>
                <p>Your Mailtrap is now configured. Here's what happens next:</p>
                <ul>
                    <li>✅ When you update an order status, an email will be sent to Mailtrap</li>
                    <li>✅ Check your Mailtrap inbox to see the email</li>
                    <li>✅ The email includes order details, products, and totals</li>
                </ul>
                <p style="margin-top: 15px;">
                    <a href="orders.php" class="btn btn-primary">Go to Orders →</a>
                </p>
            </div>
            <?php endif; ?>

            <div class="card" style="margin-top: 20px; background: #fff3cd;">
                <h3 style="margin-top: 0; color: #856404;">💡 Troubleshooting</h3>
                <p><strong>If email sending fails:</strong></p>
                <ul>
                    <li>Check that your username and password are correct</li>
                    <li>Make sure you copied them from the <strong>SMTP Settings</strong> tab (not API Keys)</li>
                    <li>Failed emails are saved to <code>logs/emails/</code> with error details</li>
                    <li>View them in <a href="view-emails.php">Email Logs</a></li>
                </ul>
            </div>
        </div>
    </div>

    <footer>
        <p>&copy; 2025 The Pop Stop. All rights reserved.</p>
    </footer>
</body>
</html>
