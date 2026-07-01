<?php
require_once '../config/database.php';
require_once '../config/functions.php';

requireAdmin();

$logs_dir = __DIR__ . '/../logs/emails/';
$emails = [];

if (is_dir($logs_dir)) {
    $files = scandir($logs_dir, SCANDIR_SORT_DESCENDING);
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..' && pathinfo($file, PATHINFO_EXTENSION) === 'html') {
            $emails[] = [
                'filename' => $file,
                'path' => $logs_dir . $file,
                'date' => date('Y-m-d H:i:s', filemtime($logs_dir . $file)),
                'size' => filesize($logs_dir . $file)
            ];
        }
    }
}

$selected_email = isset($_GET['email']) ? basename($_GET['email']) : null;
$email_content = '';

if ($selected_email && file_exists($logs_dir . $selected_email)) {
    $email_content = file_get_contents($logs_dir . $selected_email);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Email Logs - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .email-list {
            max-width: 300px;
            border-right: 2px solid var(--light-beige);
            padding-right: 20px;
        }
        .email-item {
            padding: 10px;
            margin-bottom: 10px;
            background: var(--light-beige);
            border-radius: 8px;
            cursor: pointer;
            transition: background 0.3s;
        }
        .email-item:hover {
            background: var(--primary);
            color: white;
        }
        .email-item.active {
            background: var(--accent);
            color: white;
        }
        .email-viewer {
            flex: 1;
            padding-left: 20px;
        }
        .email-frame {
            border: 2px solid var(--light-beige);
            border-radius: 10px;
            min-height: 500px;
            background: white;
            padding: 20px;
        }
        .container-flex {
            display: flex;
            gap: 20px;
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
                    <li><a href="../logout.php">Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="container">
        <h1 style="margin-bottom: 2rem; color: var(--dark-brown);">📧 Email Logs (Test Mode)</h1>
        
        <div class="card" style="margin-bottom: 1rem; padding: 1rem; background: #E3F2FD;">
            <p style="margin: 0; color: #1976D2;">
                <strong>ℹ️ Test Mode Active:</strong> Emails are saved to <code>logs/emails/</code> folder instead of being sent to Mailtrap.
                To use real Mailtrap, set <code>$test_mode = false</code> in <code>config/functions.php</code> and configure PHPMailer.
            </p>
        </div>

        <div class="container-flex">
            <!-- Email List -->
            <div class="email-list">
                <h3 style="color: var(--dark-brown);">Sent Emails (<?php echo count($emails); ?>)</h3>
                <?php if (empty($emails)): ?>
                    <div class="card" style="padding: 1rem; text-align: center;">
                        <p>No emails sent yet.</p>
                        <p style="font-size: 0.9em; color: #666;">Update an order status to send a test email.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($emails as $email): ?>
                        <a href="?email=<?php echo urlencode($email['filename']); ?>" style="text-decoration: none; color: inherit;">
                            <div class="email-item <?php echo $selected_email === $email['filename'] ? 'active' : ''; ?>">
                                <div style="font-weight: bold; font-size: 0.9em;"><?php echo htmlspecialchars($email['filename']); ?></div>
                                <div style="font-size: 0.8em; margin-top: 5px; opacity: 0.8;"><?php echo $email['date']; ?></div>
                                <div style="font-size: 0.75em; margin-top: 3px; opacity: 0.7;"><?php echo round($email['size'] / 1024, 1); ?> KB</div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Email Viewer -->
            <div class="email-viewer">
                <?php if ($selected_email && $email_content): ?>
                    <div style="margin-bottom: 1rem;">
                        <h3 style="color: var(--dark-brown); margin-bottom: 0.5rem;">📨 <?php echo htmlspecialchars($selected_email); ?></h3>
                        <a href="<?php echo '../logs/emails/' . urlencode($selected_email); ?>" target="_blank" class="btn btn-secondary btn-small">
                            🔗 Open in New Tab
                        </a>
                    </div>
                    <div class="email-frame">
                        <?php echo $email_content; ?>
                    </div>
                <?php elseif ($selected_email): ?>
                    <div class="card" style="text-align: center; padding: 3rem;">
                        <h2 style="color: var(--dark-brown);">⚠️ Email Not Found</h2>
                        <p>The selected email file could not be found.</p>
                    </div>
                <?php else: ?>
                    <div class="card" style="text-align: center; padding: 3rem;">
                        <h2 style="color: var(--dark-brown);">📧 Select an Email</h2>
                        <p>Choose an email from the list to view its contents.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <footer>
        <p>&copy; 2025 The Pop Stop. All rights reserved.</p>
    </footer>
</body>
</html>
