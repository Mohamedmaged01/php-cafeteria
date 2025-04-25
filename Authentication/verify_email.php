<?php
session_start();
include_once 'connect.php';

if (isset($_GET['token']) && isset($_SESSION['verification_data'])) {
    $token = $_GET['token'];
    $session_data = $_SESSION['verification_data'];
    
    if ($token === $session_data['token'] && time() < $session_data['expires']) {
        // إدخال المستخدم في قاعدة البيانات بعد التحقق
        $stmt = $myconnection->prepare("INSERT INTO users (name, email, password, room, ext, picture) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", 
            $session_data['user_data']['name'],
            $session_data['email'],
            $session_data['user_data']['password'],
            $session_data['user_data']['room'],
            $session_data['user_data']['ext'],
            $session_data['user_data']['picture']
        );
        
        if ($stmt->execute()) {
            unset($_SESSION['verification_data']);
            $_SESSION['success_message'] = 'Account verified successfully! You can now login.';
            header("Location: login.php");
        } else {
            $_SESSION['error_message'] = 'Failed to complete registration. Please try again.';
            header("Location: register.php");
        }
        $stmt->close();
    } else {
        $_SESSION['error_message'] = 'Invalid or expired verification link.';
        header("Location: register.php");
    }
} else {
    $_SESSION['error_message'] = 'Invalid verification link.';
    header("Location: register.php");
}
exit();
?>