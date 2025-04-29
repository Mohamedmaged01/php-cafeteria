<?php
function checkAdminAuth() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: ../../../Authentication/login.php');
        exit();
    }
    if ($_SESSION['user_role'] != 'admin') {
        header('Location: ../../../Authentication/login.php');
        exit();
    }
}
?>