<?php
include_once '../../../config/db.php';
session_start();

// Admin login check
// if (!isset($_SESSION['admin_id'])) {
//     header("Location: /php-cafeteria/views/admin/login.php");
//     exit();
// }

$order_id = intval($_GET['id']);
$new_status = $_GET['status'];


$allowed_statuses = ['Processing', 'out for delivery', 'completed', 'cancelled'];
if (!in_array($new_status, $allowed_statuses)) {
    die("Invalid status");
}


mysqli_query($myconnection, "UPDATE orders SET status = '$new_status' WHERE id = $order_id");


header("Location: details.php?id=$order_id");
exit();
?>