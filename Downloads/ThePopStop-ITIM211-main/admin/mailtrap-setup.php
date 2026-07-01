<?php
require_once '../config/database.php';
require_once '../config/functions.php';

requireAdmin();

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_config'])) {
    $mode = $_POST['mode'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $host = $_POST['host'];
    $port = intval($_POST['port']);
    
    $functions_file = __DIR__ . '/../config/functions.php';
    $content = file_get_contents($functions_file);
    
    // Update host
    $content = preg_replace(
        "/\\\$mail->Host = '[^']+';.*\/\/ Transactional Stream host/",
        "\$mail->Host = '{$host}'; // " . ($mode === 'transactional' ? 'Transactional Stream host' : 'Sandbox host'),
        $content
    );
    
    // Update username
    $content = preg_replace(
        "/\\\$mail->Username = '[^']+';.*\/\/ Use 'api' for Transactional Stream/",
        "\$mail->Username = '{$username}'; // " . ($mode === 'transactional' ? "Use 'api' for Transactional Stream" : 'Sandbox username'),
        $content
    );
    
    // Update password
    $content = preg_replace(
        "/\\\$mail->Password = '[^']+';.*\/\/ ⚠️ REPLACE with your actual API token/",
        "\$mail->Password = '{$password}'; // " . ($mode === 'transactional' ? 'API Token' : 'Sandbox password'),
        $content
    );
    
    // Update port
    $content = preg_replace(
        "/\\\$mail->Port = \d+;.*\/\/ Recommended port/",
        "\$mail->Port = {$port}; // Recommended port for " . ($mode === 'transactional' ? 'STARTTLS' : 'Sandbox'),
        $content
    );
    
    if (file_put_contents($functions_file, $content)) {
        $message = '<div class="alert alert-success">✅ Configuration updated successfully!</div>';
    } else {
        $message = '<div class="alert alert-error">❌ Failed to save configuration.</div>';
    }
}

$functions_file = __DIR__ . '/../config/functions.php';
$content = file_get_contents($functions_file);

$current_host = 'live.smtp.mailtrap.io';
$current_username = 'api';
$current_password = 'YOUR_API_TOKEN_HERE';
$current_port = 587;

if (preg_match("/\\\$mail->Host = '([^']+)';/", $content, $matches)) {
    $current_host = $matches[1];
}
if (preg_match("/\\\$mail->Username = '([^']+)';/", $content, $matches)) {
    $current_username = $matches[1];
}
if (preg_match("/\\\$mail->Password = '([^']+)';/", $content, $matches)) {
    $current_password = $matches[1];
}
if (preg_match("/\\\$mail->Port = (\d+);/", $content, $matches)) {
    $current_port = intval($matches[1]);
}

$is_transactional = strpos($current_host, 'live.smtp.mailtrap.io') !== false;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mailtrap Setup - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .setup-card { max-width: 900px; margin: 0 auto; }
        .mode-selector { display: flex; gap: 20px; margin: 20px 0; }
        .mode-card { flex: 1; padding: 20px; border: 3px solid #ddd; border-radius: 10px; cursor: pointer; transition: all 0.3s; }
        .mode-card:hover { border-color: #8B4513; transform: translateY(-2px); }
        .mode-card.active { border-color: #8B4513; background: #fff3e0; }
        .mode-card h3 { margin-top: 0; color: #8B4513; }
        .info-box { background: #e3f2fd; border-left: 4px solid #2196F3; padding: 15px; margin: 20px 0; border-radius: 5px; }
        .warning-box { background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0; border-radius: 5px; }
        code { background: #f4f4f4; padding: 2px 6px; border-radius: 3px; }
    </style>
</head>
<body>
    <header>
        <div class="header-container">
            <a href="../index.php" class="logo">The Pop Stop</a>
            <nav>
                <ul>
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="orders.php">Orders</a></li>
                    <li><a href="../logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="container">
        <div class="setup-card">
            <h1 style="margin-bottom: 1rem; color: var(--dark-brown);">📧 Mailtrap Email Setup</h1>
            
            <?php echo $message; ?>

            <div class="warning-box">
                <h3 style="margin-top: 0; color: #856404;">⚠️ Choose Your Mode:</h3>
                <p><strong>Sandbox:</strong> Emails only go to Mailtrap inbox (testing)</p>
                <p><strong>Transactional:</strong> Emails go to REAL customer addresses (production)</p>
            </div>

            <form method="POST" id="configForm">
                <div class="mode-selector">
                    <div class="mode-card <?php echo !$is_transactional ? 'active' : ''; ?>" onclick="selectMode('sandbox')">
                        <h3>🧪 Sandbox Mode</h3>
                        <p><strong>For Testing Only</strong></p>
                        <ul>
                            <li>✅ Safe testing</li>
                            <li>✅ No real emails sent</li>
                            <li>✅ View in Mailtrap inbox</li>
                            <li>❌ Customers won't receive</li>
                        </ul>
                    </div>
                    
                    <div class="mode-card <?php echo $is_transactional ? 'active' : ''; ?>" onclick="selectMode('transactional')">
                        <h3>📧 Transactional Stream</h3>
                        <p><strong>For Real Emails</strong></p>
                        <ul>
                            <li>✅ Real email delivery</li>
                            <li>✅ Customers receive emails</li>
                            <li>✅ Goes to actual Gmail/addresses</li>
                            <li>⚠️ Needs API token</li>
                        </ul>
                    </div>
                </div>

                <input type="hidden" name="mode" id="modeInput" value="<?php echo $is_transactional ? 'transactional' : 'sandbox'; ?>">

                <div class="card">
                    <h2 style="color: var(--dark-brown);">SMTP Configuration</h2>
                    
                    <div id="sandboxConfig" style="display: <?php echo !$is_transactional ? 'block' : 'none'; ?>;">
                        <div class="info-box">
                            <strong>📍 Get Sandbox Credentials:</strong><br>
                            Mailtrap → <strong>Email Testing</strong> → <strong>Inboxes</strong> → Click your inbox → <strong>SMTP Settings</strong>
                        </div>
                        
                        <div class="form-group">
                            <label>Host</label>
                            <input type="text" id="sandbox_host" value="sandbox.smtp.mailtrap.io" readonly style="background: #f5f5f5;">
                        </div>
                        
                        <div class="form-group">
                            <label>Port</label>
                            <input type="number" id="sandbox_port" value="2525" readonly style="background: #f5f5f5;">
                        </div>
                        
                        <div class="form-group">
                            <label>Username <span style="color: red;">*</span></label>
                            <input type="text" id="sandbox_username" value="<?php echo !$is_transactional ? htmlspecialchars($current_username) : ''; ?>" placeholder="e.g., 226eb576de978c">
                            <small>From Mailtrap SMTP Settings</small>
                        </div>
                        
                        <div class="form-group">
                            <label>Password <span style="color: red;">*</span></label>
                            <input type="text" id="sandbox_password" value="<?php echo !$is_transactional ? htmlspecialchars($current_password) : ''; ?>" placeholder="e.g., d98872328df2ad">
                            <small>From Mailtrap SMTP Settings</small>
                        </div>
                    </div>

                    <div id="transactionalConfig" style="display: <?php echo $is_transactional ? 'block' : 'none'; ?>;">
                        <div class="warning-box">
                            <strong>⚠️ Get API Token:</strong><br>
                            Mailtrap → <strong>Sending Domains</strong> → <strong>thepopstop.com</strong> → <strong>Integration</strong> → <strong>SMTP</strong> → Look for <code>&lt;YOUR_API_TOKEN&gt;</code>
                        </div>
                        
                        <div class="form-group">
                            <label>Host</label>
                            <input type="text" id="trans_host" value="live.smtp.mailtrap.io" readonly style="background: #f5f5f5;">
                        </div>
                        
                        <div class="form-group">
                            <label>Port</label>
                            <input type="number" id="trans_port" value="587" readonly style="background: #f5f5f5;">
                        </div>
                        
                        <div class="form-group">
                            <label>Username</label>
                            <input type="text" id="trans_username" value="api" readonly style="background: #f5f5f5;">
                            <small>Always use "api" for Transactional Stream</small>
                        </div>
                        
                        <div class="form-group">
                            <label>API Token (Password) <span style="color: red;">*</span></label>
                            <input type="text" id="trans_password" value="<?php echo $is_transactional ? htmlspecialchars($current_password) : ''; ?>" placeholder="Paste your API token here">
                            <small>Get from Mailtrap Sending Domains → Integration</small>
                        </div>
                    </div>

                    <input type="hidden" name="host" id="host">
                    <input type="hidden" name="port" id="port">
                    <input type="hidden" name="username" id="username">
                    <input type="hidden" name="password" id="password">

                    <div style="margin-top: 20px;">
                        <button type="submit" name="update_config" class="btn btn-primary" onclick="prepareSubmit()">
                            💾 Save Configuration
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <footer>
        <p>&copy; 2025 The Pop Stop. All rights reserved.</p>
    </footer>

    <script>
        function selectMode(mode) {
            document.getElementById('modeInput').value = mode;
            
            // Update UI
            document.querySelectorAll('.mode-card').forEach(card => card.classList.remove('active'));
            event.currentTarget.classList.add('active');
            
            if (mode === 'sandbox') {
                document.getElementById('sandboxConfig').style.display = 'block';
                document.getElementById('transactionalConfig').style.display = 'none';
            } else {
                document.getElementById('sandboxConfig').style.display = 'none';
                document.getElementById('transactionalConfig').style.display = 'block';
            }
        }
        
        function prepareSubmit() {
            const mode = document.getElementById('modeInput').value;
            
            if (mode === 'sandbox') {
                document.getElementById('host').value = document.getElementById('sandbox_host').value;
                document.getElementById('port').value = document.getElementById('sandbox_port').value;
                document.getElementById('username').value = document.getElementById('sandbox_username').value;
                document.getElementById('password').value = document.getElementById('sandbox_password').value;
            } else {
                document.getElementById('host').value = document.getElementById('trans_host').value;
                document.getElementById('port').value = document.getElementById('trans_port').value;
                document.getElementById('username').value = document.getElementById('trans_username').value;
                document.getElementById('password').value = document.getElementById('trans_password').value;
            }
        }
    </script>
</body>
</html>
