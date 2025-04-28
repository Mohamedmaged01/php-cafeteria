<?php
include_once '../../../config/db.php';
session_start();

if (($_SESSION['role'] ?? 'customer') !== 'admin') {
    header("Location: /php-cafeteria/views/user/order/index.php");
    exit;
}

$category_id = intval($_GET['id'] ?? 0);

if ($category_id > 0) {
    
    $check_products = mysqli_query($myconnection, "SELECT COUNT(*) as product_count FROM products WHERE category_id = $category_id");
    $result = mysqli_fetch_assoc($check_products);
    
    if ($result['product_count'] > 0) {
        $_SESSION['toast'] = [
            'type' => 'danger',
            'message' => 'Cannot delete category because it has associated products.'
        ];
    } else {
        $delete_query = mysqli_query($myconnection, "DELETE FROM categories WHERE id = $category_id");
        if ($delete_query) {
            $_SESSION['toast'] = [
                'type' => 'success',
                'message' => 'Category deleted successfully.'
            ];
        } else {
            $_SESSION['toast'] = [
                'type' => 'danger',
                'message' => 'Failed to delete category. ' . mysqli_error($myconnection)
            ];
        }
    }
}

header("Location: list.php");
exit;
?>