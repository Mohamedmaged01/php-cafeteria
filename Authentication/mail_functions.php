<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require 'vendor/autoload.php';

function send_system_email($sender_email, $recipient_email, $subject, $message_body) {
    if (!filter_var($sender_email, FILTER_VALIDATE_EMAIL) || 
        !filter_var($recipient_email, FILTER_VALIDATE_EMAIL)) {
        error_log("Invalid email addresses - From: $sender_email, To: $recipient_email");
        return false;
    }

    $mail = new PHPMailer(true);
    
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'harbia305@gmail.com'; 
        $mail->Password = 'uahi toyq aswi snlc';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->SMTPDebug = SMTP::DEBUG_OFF;

        $mail->setFrom('harbia305@gmail.com', 'Cafeteria System'); 
        $mail->addReplyTo($sender_email); 
        $mail->addAddress($recipient_email);

        $mail->isHTML(true);
        $mail->Subject = $subject;
        
        $mail->Body = "
            <!DOCTYPE html>
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .footer { 
                        margin-top: 30px; 
                        font-size: 12px; 
                        color: #666666;
                    }
                </style>
            </head>
            <body>
                <div class='container'>
                    $message_body
                    <div class='footer'>
                        <p>This email was sent from Cafeteria System</p>
                        <p>&copy; " . date('Y') . " Cafeteria System. All rights reserved.</p>
                    </div>
                </div>
            </body>
            </html>
        ";

        $mail->AltBody = strip_tags($message_body) . "\n\nThis email was sent from Cafeteria System";

        if (!$mail->send()) {
            throw new Exception('Mailer Error: ' . $mail->ErrorInfo);
        }
        
        return true;
    } catch (Exception $e) {
        error_log("Mailer Error (From: $sender_email, To: $recipient_email): " . $e->getMessage());
        return false;
    }
}

function send_verification_email($email, $token) {
    $verification_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") 
                        . "://$_SERVER[HTTP_HOST]"
                        . str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME'])
                        . "verify_email.php?token=" . urlencode($token);
    
    $subject = "Verify Your Cafeteria System Account";
    $message = "
        <h2>Welcome to Cafeteria System!</h2>
        <p>Thank you for registering. Please verify your email address to activate your account.</p>
        <a href='$verification_link' style='display: inline-block; background: #4361ee; color: #ffffff; 
           padding: 12px 24px; text-decoration: none; border-radius: 4px; font-weight: bold; margin: 20px 0;'>
           Verify Email Address
        </a>
        <p>Or copy and paste this link into your browser:</p>
        <p><small>$verification_link</small></p>
        <p>If you didn't create an account with us, please ignore this email.</p>
    ";
    
    return send_system_email('noreply@cafeteria-system.com', $email, $subject, $message);
}
?>