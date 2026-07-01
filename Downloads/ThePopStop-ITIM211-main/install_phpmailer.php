<?php
/**
 * PHPMailer Auto-Installer
 * 
 * This script downloads and installs PHPMailer automatically
 * Run this file once to enable real email sending via Mailtrap
 */

echo "<!DOCTYPE html>
<html>
<head>
    <title>PHPMailer Installer</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
        .success { background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .info { background: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .step { background: #f8f9fa; padding: 15px; border-left: 4px solid #007bff; margin: 10px 0; }
        code { background: #f4f4f4; padding: 2px 5px; border-radius: 3px; }
    </style>
</head>
<body>
    <h1>📦 PHPMailer Auto-Installer</h1>";

// Step 1: Check if already installed
$vendor_dir = __DIR__ . '/vendor/phpmailer/src';
if (is_dir($vendor_dir) && file_exists($vendor_dir . '/PHPMailer.php')) {
    echo "<div class='success'>✅ PHPMailer is already installed!</div>";
    echo "<div class='info'>
        <strong>Next Steps:</strong>
        <ol>
            <li>Get Mailtrap credentials from <a href='https://mailtrap.io' target='_blank'>https://mailtrap.io</a></li>
            <li>Update credentials in <code>config/functions.php</code> (lines 337-338)</li>
            <li>Set <code>\$test_mode = false;</code> in <code>config/functions.php</code> (line 295)</li>
            <li>Test by updating an order status</li>
        </ol>
    </div>";
    echo "</body></html>";
    exit;
}

// Step 2: Create vendor directory
echo "<div class='step'><strong>Step 1:</strong> Creating vendor directory...</div>";
$vendor_base = __DIR__ . '/vendor';
if (!is_dir($vendor_base)) {
    mkdir($vendor_base, 0755, true);
    echo "<div class='success'>✅ Created: vendor/</div>";
} else {
    echo "<div class='info'>ℹ️ Directory exists: vendor/</div>";
}

// Step 3: Download PHPMailer from GitHub
echo "<div class='step'><strong>Step 2:</strong> Downloading PHPMailer from GitHub...</div>";

$phpmailer_url = "https://github.com/PHPMailer/PHPMailer/archive/refs/heads/master.zip";
$zip_file = $vendor_base . '/phpmailer.zip';

// Download using file_get_contents
$zip_content = @file_get_contents($phpmailer_url);

if ($zip_content === false) {
    echo "<div class='error'>❌ Failed to download PHPMailer. Please check your internet connection.</div>";
    echo "<div class='info'>
        <strong>Manual Installation:</strong>
        <ol>
            <li>Download: <a href='https://github.com/PHPMailer/PHPMailer/archive/refs/heads/master.zip'>PHPMailer ZIP</a></li>
            <li>Extract the ZIP file</li>
            <li>Copy the <code>src</code> folder to <code>C:\\xampp\\htdocs\\ThePopStop\\vendor\\phpmailer\\src</code></li>
        </ol>
    </div>";
    echo "</body></html>";
    exit;
}

file_put_contents($zip_file, $zip_content);
echo "<div class='success'>✅ Downloaded: " . round(strlen($zip_content) / 1024 / 1024, 2) . " MB</div>";

// Step 4: Extract ZIP
echo "<div class='step'><strong>Step 3:</strong> Extracting PHPMailer...</div>";

$zip = new ZipArchive;
if ($zip->open($zip_file) === TRUE) {
    $extract_path = $vendor_base . '/phpmailer-temp';
    $zip->extractTo($extract_path);
    $zip->close();
    echo "<div class='success'>✅ Extracted successfully</div>";
    
    // Step 5: Move files to correct location
    echo "<div class='step'><strong>Step 4:</strong> Installing files...</div>";
    
    $source = $extract_path . '/PHPMailer-master/src';
    $destination = $vendor_base . '/phpmailer/src';
    
    if (is_dir($source)) {
        // Create destination
        if (!is_dir($vendor_base . '/phpmailer')) {
            mkdir($vendor_base . '/phpmailer', 0755, true);
        }
        
        // Copy src folder
        recurse_copy($source, $destination);
        
        echo "<div class='success'>✅ PHPMailer installed successfully!</div>";
        
        // Clean up
        unlink($zip_file);
        deleteDirectory($extract_path);
        echo "<div class='info'>🗑️ Cleaned up temporary files</div>";
        
        // Success message
        echo "<div class='success' style='margin-top: 30px; font-size: 1.2em;'>
            <h2>🎉 Installation Complete!</h2>
            <p>PHPMailer has been installed successfully.</p>
        </div>";
        
        echo "<div class='info'>
            <h3>📧 Next Steps to Enable Mailtrap:</h3>
            <ol>
                <li><strong>Sign up at Mailtrap:</strong>
                    <ul>
                        <li>Go to <a href='https://mailtrap.io' target='_blank'>https://mailtrap.io</a></li>
                        <li>Create a free account</li>
                        <li>Go to 'Email Testing' → 'Inboxes'</li>
                        <li>Click on your inbox</li>
                        <li>Go to 'SMTP Settings' tab</li>
                    </ul>
                </li>
                <li><strong>Copy your credentials:</strong>
                    <ul>
                        <li>Host: <code>sandbox.smtp.mailtrap.io</code></li>
                        <li>Port: <code>2525</code></li>
                        <li>Username: <code>(your username)</code></li>
                        <li>Password: <code>(your password)</code></li>
                    </ul>
                </li>
                <li><strong>Update configuration:</strong>
                    <ul>
                        <li>Open <code>config/functions.php</code></li>
                        <li>Find lines 337-338</li>
                        <li>Replace with your actual Mailtrap username and password</li>
                    </ul>
                </li>
                <li><strong>Enable production mode:</strong>
                    <ul>
                        <li>In <code>config/functions.php</code> line 295</li>
                        <li>Change <code>\$test_mode = true;</code> to <code>\$test_mode = false;</code></li>
                    </ul>
                </li>
                <li><strong>Test email sending:</strong>
                    <ul>
                        <li>Go to Admin → Orders</li>
                        <li>Update any order status</li>
                        <li>Check your Mailtrap inbox</li>
                    </ul>
                </li>
            </ol>
        </div>";
        
        echo "<div class='step'>
            <strong>📁 Installation Path:</strong><br>
            <code>" . realpath($destination) . "</code>
        </div>";
        
    } else {
        echo "<div class='error'>❌ Error: Could not find source directory</div>";
    }
    
} else {
    echo "<div class='error'>❌ Failed to extract ZIP file</div>";
}

echo "</body></html>";

// Helper function to copy directory recursively
function recurse_copy($src, $dst) {
    $dir = opendir($src);
    @mkdir($dst, 0755, true);
    while(false !== ( $file = readdir($dir)) ) {
        if (( $file != '.' ) && ( $file != '..' )) {
            if ( is_dir($src . '/' . $file) ) {
                recurse_copy($src . '/' . $file, $dst . '/' . $file);
            }
            else {
                copy($src . '/' . $file, $dst . '/' . $file);
            }
        }
    }
    closedir($dir);
}

// Helper function to delete directory recursively
function deleteDirectory($dir) {
    if (!file_exists($dir)) {
        return true;
    }
    
    if (!is_dir($dir)) {
        return unlink($dir);
    }
    
    foreach (scandir($dir) as $item) {
        if ($item == '.' || $item == '..') {
            continue;
        }
        
        if (!deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) {
            return false;
        }
    }
    
    return rmdir($dir);
}
?>
