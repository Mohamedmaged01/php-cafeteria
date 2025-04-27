<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "DB_Project_Database";

// Create myconnection
$myconnection = mysqli_connect($servername, $username, $password, $dbname);
// Check myconnection
if ($myconnection) {
} else {
    die("myconnection failed: " . mysqli_connect_error());
} 
require_once __DIR__ . '/auth.php';

checkAdminAuth();

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    // Get user data first
    $stmt = $myconnection->prepare("SELECT picture FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    
    // Delete user
    $stmt = $myconnection->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        // Delete picture if exists
        if ($user['picture'] && file_exists("../../uploads/".$user['picture'])) {
            unlink("../../uploads/".$user['picture']);
        }
        
        $_SESSION['message'] = "User deleted successfully";
    } else {
        $_SESSION['error'] = "Error deleting user: ".$stmt->error;
    }
    
    $stmt->close();
}

header("Location: list.php");
exit();
?>