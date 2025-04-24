<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

function send_verification_email($email, $token) {
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'harbia305@gmail.com'; // استبدل ببريدك
        $mail->Password = 'uahi toyq aswi snlc'; // كلمة مرور التطبيق
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Recipients
        $mail->setFrom('noreply@yourdomain.com', 'Cafeteria System');
        $mail->addAddress($email);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Verify Your Account';
        
        // إنشاء رابط التحقق
        $verification_link = (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]".dirname($_SERVER['PHP_SELF'])."/login.php?token=$token";
        
        $mail->Body = "
            <h2>Account Verification</h2>
            <p>Please click the button below to verify your account:</p>
            <a href='$verification_link' 
               style='background:#4361ee; color:#fff; padding:10px 15px; text-decoration:none; border-radius:5px;'>
               Verify Account
            </a>
            <p>If you didn't create an account, please ignore this email.</p>
        ";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Mailer Error: " . $e->getMessage());
        return false;
    }
}
?>