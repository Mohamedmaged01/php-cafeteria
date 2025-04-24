<?php
include_once '../../../config/db.php';
session_start();


if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1;
    $_SESSION['user_name'] = "Aya";
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];
$notes = mysqli_real_escape_string($myconnection, $_POST['notes'] ?? '');
$room = mysqli_real_escape_string($myconnection, $_POST['room'] ?? '');
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
  
    $order_query = "INSERT INTO orders (user_id, user, notes, total) 
               VALUES ($user_id, '$user_name', '$notes', $total)";

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

    mysqli_commit($myconnection);
    header("Location: details.php?id=$order_id");
    exit;
    
} catch (Exception $e) {
    mysqli_rollback($myconnection);
    die("Error processing order: " . $e->getMessage());
}
?>