<?php

define('MAILTRAP_HOST', 'sandbox.smtp.mailtrap.io');
define('MAILTRAP_PORT', 2525);
define('MAILTRAP_USERNAME', '226eb576de978c'); 
define('MAILTRAP_PASSWORD', 'd98872328df2ad'); 

define('MAIL_FROM_ADDRESS', 'noreply@thepopstop.com');
define('MAIL_FROM_NAME', 'The Pop Stop');
define('MAIL_REPLY_TO', 'support@thepopstop.com');

function sendMailtrapEmail($to_email, $to_name, $subject, $html_body) {
    $phpmailer_path = __DIR__ . '/../vendor/phpmailer/PHPMailer.php';
    
    if (file_exists($phpmailer_path)) {
        require_once __DIR__ . '/../vendor/phpmailer/PHPMailer.php';
        require_once __DIR__ . '/../vendor/phpmailer/SMTP.php';
        require_once __DIR__ . '/../vendor/phpmailer/Exception.php';
        
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        
        try {
            $mail->isSMTP();
            $mail->Host = MAILTRAP_HOST;
            $mail->SMTPAuth = true;
            $mail->Username = MAILTRAP_USERNAME;
            $mail->Password = MAILTRAP_PASSWORD;
            $mail->SMTPSecure = 'tls';
            $mail->Port = MAILTRAP_PORT;
            
            $mail->setFrom(MAIL_FROM_ADDRESS, MAIL_FROM_NAME);
            $mail->addAddress($to_email, $to_name);
            $mail->addReplyTo(MAIL_REPLY_TO, MAIL_FROM_NAME);
            
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $html_body;
            $mail->AltBody = strip_tags($html_body);
            
            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Mailtrap Error: {$mail->ErrorInfo}");
            return false;
        }
    } else {
        $headers = "From: " . MAIL_FROM_NAME . " <" . MAIL_FROM_ADDRESS . ">\r\n";
        $headers .= "Reply-To: " . MAIL_REPLY_TO . "\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        
        return @mail($to_email, $subject, $html_body, $headers);
    }
}
?>
