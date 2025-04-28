<?php
include_once '../../../config/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../../Authentication/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];
$notes = mysqli_real_escape_string($myconnection, $_POST['notes'] ?? '');
$room_id = (int) ($_POST['room_id'] ?? 0); 
$quantities = json_decode($_POST['quantities'], true);

$total = 0;
foreach ($quantities as $product_id => $qty) {
    if ($qty > 0) {
        $result = mysqli_query($myconnection, "SELECT price FROM products WHERE id = $product_id");
        $price = mysqli_fetch_assoc($result)['price'];
        $total += $price * $qty;
    }
}

mysqli_begin_transaction($myconnection);

try {
   
    $order_query = "INSERT INTO orders (user_id, user, room_id, notes, total) 
                    VALUES ($user_id, '$user_name', $room_id, '$notes', $total)";
    mysqli_query($myconnection, $order_query);
    $order_id = mysqli_insert_id($myconnection);

   
    foreach ($quantities as $product_id => $qty) {
        if ($qty > 0) {
            $result = mysqli_query($myconnection, "SELECT price FROM products WHERE id = $product_id");
            $price = mysqli_fetch_assoc($result)['price'];

            $detail_query = "INSERT INTO order_products (order_id, product_id, quantity, price) 
                             VALUES ($order_id, $product_id, $qty, $price)";
            mysqli_query($myconnection, $detail_query);
        }
    }

    
    mysqli_query($myconnection, "UPDATE rooms SET status = 'occupied' WHERE id = $room_id");

    mysqli_commit($myconnection);
    header("Location: details.php?id=$order_id");
    exit;

} catch (Exception $e) {
    mysqli_rollback($myconnection);
    die("Error processing order: " . $e->getMessage());
}
?>
