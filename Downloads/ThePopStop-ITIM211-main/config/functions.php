<?php

function startSession() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function isLoggedIn() {
    startSession();
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    startSession();
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: /ThePopStop/login.php");
        exit();
    }
}

function requireAdmin() {
    if (!isLoggedIn()) {
        setAlert('error', 'Please login to access this page');
        header("Location: /ThePopStop/login.php");
        exit();
    }
    if (!isAdmin()) {
        setAlert('error', 'Access denied. Admin privileges required.');
        header("Location: /ThePopStop/index.php");
        exit();
    }
}

function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function formatCurrency($amount) {
    return '₱' . number_format($amount, 2);
}

function formatDate($date) {
    return date('M d, Y', strtotime($date));
}

function formatDateTime($datetime) {
    return date('M d, Y g:i A', strtotime($datetime));
}

function getCartCount($conn, $user_id) {
    $stmt = $conn->prepare("SELECT SUM(quantity) as total FROM cart WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['total'] ?? 0;
}

function updateProductStatus($conn, $product_id) {
    $stmt = $conn->prepare("SELECT stock_quantity FROM products WHERE product_id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    
    if ($product) {
        $status = 'Out of Stock';
        if ($product['stock_quantity'] > 10) {
            $status = 'In Stock';
        } elseif ($product['stock_quantity'] > 0) {
            $status = 'Low Stock';
        }
        
        $update = $conn->prepare("UPDATE products SET status = ? WHERE product_id = ?");
        $update->bind_param("si", $status, $product_id);
        $update->execute();
    }
}

function generateReceipt($conn, $order_id) {
    $stmt = $conn->prepare("
        SELECT o.*, u.full_name, u.email, u.phone
        FROM orders o
        JOIN users u ON o.user_id = u.user_id
        WHERE o.order_id = ?
    ");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $order = $stmt->get_result()->fetch_assoc();
    
    if (!$order) return null;
    
    $items_stmt = $conn->prepare("
        SELECT oi.*, p.name, p.sku
        FROM order_items oi
        JOIN products p ON oi.product_id = p.product_id
        WHERE oi.order_id = ?
    ");
    $items_stmt->bind_param("i", $order_id);
    $items_stmt->execute();
    $items = $items_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    // Compute totals from item rows instead of relying on stored totals
    $subtotal = 0;
    foreach ($items as $item) {
        $subtotal += $item['unit_price'] * $item['quantity'];
    }

    if (!isset($order['discount_amount'])) {
        $order['discount_amount'] = 0;
    }

    $order['total_amount'] = $subtotal;
    $order['final_amount'] = max(0, $subtotal - $order['discount_amount']);
    
    return [
        'order' => $order,
        'items' => $items
    ];
}

function setAlert($type, $message) {
    startSession();
    $_SESSION['alert'] = [
        'type' => $type,
        'message' => $message
    ];
}

function getAlert() {
    startSession();
    if (isset($_SESSION['alert'])) {
        $alert = $_SESSION['alert'];
        unset($_SESSION['alert']);
        return $alert;
    }
    return null;
}

function showAlert() {
    $alert = getAlert();
    if ($alert) {
        $class = $alert['type'] === 'success' ? 'alert-success' : 'alert-error';
        echo "<div class='alert {$class}'>{$alert['message']}</div>";
    }
}

function filterBadWords($text) {
    // English bad words
    $englishBadWords = [
        'damn', 'hell', 'crap', 'stupid', 'idiot', 'fuck', 'shit', 
        'ass', 'bitch', 'bastard', 'asshole', 'dick', 'pussy', 'cock'
    ];
    
    // Filipino bad words
    $filipinoBadWords = [
        'putangina', 'putang ina', 'puta', 'gago', 'gaga', 'tangina', 
        'tarantado', 'tanga', 'bobo', 'ulol', 'kupal', 'leche', 
        'peste', 'pokpok', 'tarantada', 'bwisit', 'hinayupak', 
        'hayop', 'animal', 'shunga', 'inutil', 'walang kwenta',
        'walanghiya', 'lintik', 'yawa', 'buwisit', 'punyeta',
        'puñeta', 'kingina', 'taena', 'tangna', 'amputa'
    ];
    
    $allBadWords = array_merge($englishBadWords, $filipinoBadWords);
    $filtered = $text;
    
    foreach ($allBadWords as $word) {
        $pattern = '/' . preg_quote($word, '/') . '/i';
        $replacement = str_repeat('*', strlen(str_replace(' ', '', $word)));
        $filtered = preg_replace($pattern, $replacement, $filtered);
    }
    
    return $filtered;
}

function uploadPhoto($file, $directory = 'uploads/', $prefix = 'photo_') {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    
    $allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if (!in_array($file_ext, $allowed_ext)) {
        return null;
    }
    
    if (!is_dir($directory)) {
        mkdir($directory, 0755, true);
    }
    
    $filename = $prefix . time() . '_' . uniqid() . '.' . $file_ext;
    $upload_path = $directory . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $upload_path)) {
        return $upload_path;
    }
    
    return null;
}

function sendOrderEmail($conn, $order_id, $to_email, $to_name) {
    $order_stmt = $conn->prepare("SELECT * FROM orders WHERE order_id = ?");
    $order_stmt->bind_param("i", $order_id);
    $order_stmt->execute();
    $order = $order_stmt->get_result()->fetch_assoc();
    
    if (!$order) {
        return false;
    }
    
    $items_stmt = $conn->prepare("
        SELECT oi.*, p.name as product_name
        FROM order_items oi
        JOIN products p ON oi.product_id = p.product_id
        WHERE oi.order_id = ?
    ");
    $items_stmt->bind_param("i", $order_id);
    $items_stmt->execute();
    $items = $items_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    if (empty($items)) {
        return false;
    }
    
    // Compute order totals from items
    $subtotal = 0;
    foreach ($items as $item) {
        $subtotal += $item['unit_price'] * $item['quantity'];
    }

    if (!isset($order['discount_amount'])) {
        $order['discount_amount'] = 0;
    }

    $order['total_amount'] = $subtotal;
    $order['final_amount'] = max(0, $subtotal - $order['discount_amount']);
    
    $subject = "Order #{$order_id} Status Update - The Pop Stop";
    
    $message = "<html><body style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>";
    $message .= "<div style='background: #8B4513; color: white; padding: 20px; text-align: center;'>";
    $message .= "<h1 style='margin: 0;'>The Pop Stop</h1>";
    $message .= "</div>";
    $message .= "<div style='padding: 20px; background: #f9f9f9;'>";
    $message .= "<h2 style='color: #8B4513;'>Order Status Update</h2>";
    $message .= "<p>Dear <strong>{$to_name}</strong>,</p>";
    $message .= "<p>Your order <strong>#" . $order_id . "</strong> status has been updated to:</p>";
    $message .= "<div style='background: #FFF3CD; padding: 15px; border-left: 4px solid #F39C12; margin: 20px 0;'>";
    $message .= "<h3 style='margin: 0; color: #8B4513;'>Status: " . htmlspecialchars($order['status']) . "</h3>";
    $message .= "</div>";
    $message .= "<h3 style='color: #8B4513; margin-top: 30px;'>Order Details</h3>";
    $message .= "<table style='width: 100%; border-collapse: collapse; background: white;'>";
    $message .= "<thead><tr style='background: #8B4513; color: white;'>";
    $message .= "<th style='padding: 12px; text-align: left;'>Product</th>";
    $message .= "<th style='padding: 12px; text-align: center;'>Qty</th>";
    $message .= "<th style='padding: 12px; text-align: right;'>Price</th>";
    $message .= "<th style='padding: 12px; text-align: right;'>Subtotal</th>";
    $message .= "</tr></thead><tbody>";
    
    foreach ($items as $item) {
        $line_total = $item['unit_price'] * $item['quantity'];
        $message .= "<tr style='border-bottom: 1px solid #ddd;'>";
        $message .= "<td style='padding: 12px;'>" . htmlspecialchars($item['product_name']) . "</td>";
        $message .= "<td style='padding: 12px; text-align: center;'>{$item['quantity']}</td>";
        $message .= "<td style='padding: 12px; text-align: right;'>₱" . number_format($item['unit_price'], 2) . "</td>";
        $message .= "<td style='padding: 12px; text-align: right;'>₱" . number_format($line_total, 2) . "</td>";
        $message .= "</tr>";
    }
    
    $message .= "</tbody>";
    $message .= "<tfoot>";
    $message .= "<tr><td colspan='3' style='padding: 12px; text-align: right; border-top: 2px solid #ddd;'><strong>Subtotal:</strong></td>";
    $message .= "<td style='padding: 12px; text-align: right; border-top: 2px solid #ddd;'>₱" . number_format($order['total_amount'], 2) . "</td></tr>";
    $message .= "<tr><td colspan='3' style='padding: 12px; text-align: right;'><strong>Discount:</strong></td>";
    $message .= "<td style='padding: 12px; text-align: right; color: #27AE60;'>-₱" . number_format($order['discount_amount'], 2) . "</td></tr>";
    $message .= "<tr style='background: #f5f5f5;'><td colspan='3' style='padding: 12px; text-align: right;'><strong style='font-size: 1.2em;'>Grand Total:</strong></td>";
    $message .= "<td style='padding: 12px; text-align: right;'><strong style='font-size: 1.2em; color: #8B4513;'>₱" . number_format($order['final_amount'], 2) . "</strong></td></tr>";
    $message .= "</tfoot></table>";
    $message .= "<div style='margin-top: 30px; padding: 15px; background: #E3F2FD; border-radius: 5px;'>";
    $message .= "<p style='margin: 0; color: #1976D2;'><strong>📦 Shipping Address:</strong></p>";
    $message .= "<p style='margin: 5px 0 0 0;'>" . nl2br(htmlspecialchars($order['shipping_address'])) . "</p>";
    $message .= "</div>";
    $message .= "<p style='margin-top: 30px; color: #666;'>Thank you for shopping with The Pop Stop!</p>";
    $message .= "<p style='color: #666;'>If you have any questions, please contact us.</p>";
    $message .= "</div>";
    $message .= "<div style='background: #333; color: white; padding: 15px; text-align: center; font-size: 0.9em;'>";
    $message .= "<p style='margin: 0;'>© 2025 The Pop Stop. All rights reserved.</p>";
    $message .= "</div>";
    $message .= "</body></html>";
    
    $test_mode = false;
    
    if ($test_mode) {
        $logs_dir = __DIR__ . '/../logs/emails/';
        if (!is_dir($logs_dir)) {
            mkdir($logs_dir, 0755, true);
        }
        
        $filename = 'email_' . $order_id . '_' . time() . '.html';
        $filepath = $logs_dir . $filename;
        
        $email_log = "<!-- \n";
        $email_log .= "To: {$to_name} <{$to_email}>\n";
        $email_log .= "From: The Pop Stop <noreply@thepopstop.com>\n";
        $email_log .= "Subject: {$subject}\n";
        $email_log .= "Date: " . date('Y-m-d H:i:s') . "\n";
        $email_log .= "Order ID: #{$order_id}\n";
        $email_log .= "-->\n\n";
        $email_log .= $message;
        
        file_put_contents($filepath, $email_log);
        
        return true;
    } else {
        $phpmailer_path = __DIR__ . '/../vendor/phpmailer/PHPMailer.php';
        
        if (file_exists($phpmailer_path)) {
            require_once __DIR__ . '/../vendor/phpmailer/PHPMailer.php';
            require_once __DIR__ . '/../vendor/phpmailer/SMTP.php';
            require_once __DIR__ . '/../vendor/phpmailer/Exception.php';
            
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            
            try {
                $mail->isSMTP();
                $mail->Host = 'sandbox.smtp.mailtrap.io'; 
                $mail->SMTPAuth = true;
                
                $mail->Username = '226eb576de978c'; 
                $mail->Password = 'd98872328df2ad'; 
                
                $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 2525; 
                
                $mail->setFrom('noreply@thepopstop.com', 'The Pop Stop');
                $mail->addAddress($to_email, $to_name);
                $mail->addReplyTo('support@thepopstop.com', 'The Pop Stop');
                
                $mail->isHTML(true);
                $mail->CharSet = 'UTF-8';
                $mail->Subject = $subject;
                $mail->Body = $message;
                $mail->AltBody = strip_tags($message);
                
                $mail->send();
                return true;
            } catch (Exception $e) {
                error_log("Mailtrap Error: {$mail->ErrorInfo}");
                
                $logs_dir = __DIR__ . '/../logs/emails/';
                if (!is_dir($logs_dir)) {
                    mkdir($logs_dir, 0755, true);
                }
                
                $filename = 'email_failed_' . $order_id . '_' . time() . '.html';
                $error_log = "<!-- ERROR: {$mail->ErrorInfo} -->\n\n" . $message;
                file_put_contents($logs_dir . $filename, $error_log);
                
                return false;
            }
        } else {
            $logs_dir = __DIR__ . '/../logs/emails/';
            if (!is_dir($logs_dir)) {
                mkdir($logs_dir, 0755, true);
            }
            
            $filename = 'email_' . $order_id . '_' . time() . '.html';
            file_put_contents($logs_dir . $filename, $message);
            return true;
        }
    }
}

function canReviewProduct($conn, $user_id, $product_id) {
    $stmt = $conn->prepare("
        SELECT COUNT(*) as can_review 
        FROM order_items oi
        JOIN orders o ON oi.order_id = o.order_id
        WHERE o.user_id = ? AND oi.product_id = ? AND o.status = 'Delivered'
    ");
    $stmt->bind_param("ii", $user_id, $product_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    return $result['can_review'] > 0;
}
?>
