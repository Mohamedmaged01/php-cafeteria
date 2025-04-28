<?php
function checkAuth() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: /../Authentication/login.php');
        exit();
    }
}

function checkAdminAuth() {
    checkAuth();
    if ($_SESSION['user_role'] != 'admin') {
        header('Location: /../Authentication/login.php');
        exit();
    }
}
?>