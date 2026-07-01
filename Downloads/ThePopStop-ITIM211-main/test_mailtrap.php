<?php
/**
 * Mailtrap Connection Test
 * 
 * This script tests if your Mailtrap credentials are working correctly
 */

require_once 'config/database.php';
require_once 'config/functions.php';

// Only allow admin access
startSession();
if (!isLoggedIn() || !isAdmin()) {
    die("Access denied. Admin only.");
}

echo "<!DOCTYPE html>
<html>
<head>
    <title>Mailtrap Connection Test</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
        .success { background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .info { background: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .step { background: #f8f9fa; padding: 15px; border-left: 4px solid #007bff; margin: 10px 0; }
        code { background: #f4f4f4; padding: 2px 5px; border-radius: 3px; }
        pre { background: #f4f4f4; padding: 15px; border-radius: 5px; overflow-x: auto; }
    </style>
</head>
<body>
    <h1>📧 Mailtrap Connection Test</h1>";

echo "<div class='info'>
    <strong>Testing Configuration:</strong><br>
    Host: sandbox.smtp.mailtrap.io (Sandbox Mode)<br>
    Port: 2525<br>
    Username: 226eb576de978c<br>
    Password: *** (hidden)<br>
    Account Email: thepopstoppp@gmail.com
</div>";

// Check if PHPMailer is installed
$phpmailer_path = __DIR__ . '/vendor/phpmailer/PHPMailer.php';
if (!file_exists($phpmailer_path)) {
    echo "<div class='error'>❌ PHPMailer not found at: {$phpmailer_path}</div>";
    echo "</body></html>";
    exit;
}

echo "<div class='success'>✅ PHPMailer found</div>";

// Load PHPMailer
require_once __DIR__ . '/vendor/phpmailer/PHPMailer.php';
require_once __DIR__ . '/vendor/phpmailer/SMTP.php';
require_once __DIR__ . '/vendor/phpmailer/Exception.php';

echo "<div class='step'><strong>Step 1:</strong> Testing SMTP Connection...</div>";

$mail = new PHPMailer\PHPMailer\PHPMailer(true);

try {
    // Enable verbose debug output
    $mail->SMTPDebug = 2;
    $mail->Debugoutput = function($str, $level) {
        echo "<pre style='font-size: 0.85em; margin: 5px 0;'>" . htmlspecialchars($str) . "</pre>";
    };
    
    // Server settings
    $mail->isSMTP();
    $mail->Host = 'sandbox.smtp.mailtrap.io';
    $mail->SMTPAuth = true;
    $mail->Username = '226eb576de978c';
    $mail->Password = 'd98872328df2ad';
    $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 2525;
    
    // Recipients
    $mail->setFrom('noreply@thepopstop.com', 'The Pop Stop');
    $mail->addAddress('test@example.com', 'Test Recipient');
    
    // Content
    $mail->isHTML(true);
    $mail->Subject = 'Mailtrap Test Email - The Pop Stop';
    $mail->Body = '<html><body style="font-family: Arial;">
        <h2 style="color: #8B4513;">✅ Mailtrap Sandbox Test</h2>
        <p>This is a test email from The Pop Stop e-commerce system.</p>
        <p>If you see this email in your Mailtrap sandbox inbox, the configuration is working correctly!</p>
        <hr>
        <p><strong>Credentials Used:</strong></p>
        <ul>
            <li>Username: 226eb576de978c</li>
            <li>Host: sandbox.smtp.mailtrap.io (Sandbox Mode)</li>
            <li>Port: 2525</li>
        </ul>
        <p style="color: #27ae60;"><strong>✅ Sandbox Configuration is working!</strong></p>
    </body></html>';
    $mail->AltBody = 'Mailtrap test email from The Pop Stop';
    
    $mail->send();
    
    echo "<div class='success'>
        <h2>🎉 SUCCESS!</h2>
        <p>Test email sent successfully!</p>
        <p><strong>Next Steps:</strong></p>
        <ol>
            <li>Log in to your Mailtrap account at <a href='https://mailtrap.io' target='_blank'>https://mailtrap.io</a></li>
            <li>Go to your inbox</li>
            <li>You should see the test email there</li>
            <li>If you see it, your configuration is working perfectly!</li>
        </ol>
        <p><a href='admin/orders.php' style='display: inline-block; background: #8B4513; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-top: 10px;'>Go to Orders →</a></p>
    </div>";
    
} catch (Exception $e) {
    echo "<div class='error'>
        <h2>❌ Failed to Send Email</h2>
        <p><strong>Error:</strong> {$mail->ErrorInfo}</p>
        <hr>
        <p><strong>Possible Issues:</strong></p>
        <ul>
            <li>Check if your username and password are correct</li>
            <li>Make sure you're using SMTP credentials (not API keys)</li>
            <li>Verify you copied them from the SMTP Settings tab in Mailtrap</li>
            <li>Check your internet connection</li>
        </ul>
        <p><strong>How to Get Correct Credentials:</strong></p>
        <ol>
            <li>Go to <a href='https://mailtrap.io' target='_blank'>https://mailtrap.io</a></li>
            <li>Navigate to: Email Testing → Inboxes</li>
            <li>Click on your inbox</li>
            <li>Click on <strong>SMTP Settings</strong> tab</li>
            <li>Copy the <strong>Username</strong> and <strong>Password</strong></li>
            <li>Update in <code>config/functions.php</code> lines 341-342</li>
        </ol>
    </div>";
}

echo "<div class='info'>
    <h3>💡 Configuration Files:</h3>
    <ul>
        <li><strong>Main Config:</strong> <code>config/functions.php</code> (lines 341-342)</li>
        <li><strong>Config Panel:</strong> <a href='admin/mailtrap-config.php'>admin/mailtrap-config.php</a></li>
        <li><strong>View Emails:</strong> <a href='admin/view-emails.php'>admin/view-emails.php</a></li>
    </ul>
</div>";

echo "</body></html>";
?>
