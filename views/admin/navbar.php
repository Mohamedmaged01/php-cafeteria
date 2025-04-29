<?php


$current_page = basename($_SERVER['PHP_SELF']);
$user_image = $_SESSION['user_image'] ?? 'default.png';
$username = $_SESSION['user_name'] ?? 'Admin';
?>

<nav class="navbar navbar-expand-lg navbar-dark" style="background-color: #6F4E37;">
    <div class="container-fluid">
        <a class="navbar-brand" href="/php-cafeteria/views/admin/dashboard.php">
            <i class="fas fa-mug-hot me-2"></i> Cafe Admin
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link <?= ($current_page == 'dashboard.php') ? 'active' : '' ?>" 
                       href="/php-cafeteria/views/admin/dashboard.php">
                        <i class="fas fa-home me-1"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= (strpos($_SERVER['REQUEST_URI'], 'allusers') !== false) ? 'active' : '' ?>" 
                       href="/php-cafeteria/views/admin/allusers/list.php">
                        <i class="fas fa-users me-1"></i> Users
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= (strpos($_SERVER['REQUEST_URI'], 'category') !== false) ? 'active' : '' ?>" 
                       href="/php-cafeteria/views/admin/category/list.php">
                        <i class="fas fa-tags me-1"></i> Categories
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= (strpos($_SERVER['REQUEST_URI'], 'product') !== false) ? 'active' : '' ?>" 
                       href="/php-cafeteria/views/admin/product/list.php">
                        <i class="fas fa-mug-hot me-1"></i> Products
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= (strpos($_SERVER['REQUEST_URI'], 'order') !== false) ? 'active' : '' ?>" 
                       href="/php-cafeteria/views/admin/order/index.php">
                        <i class="fas fa-receipt me-1"></i> Orders
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= (strpos($_SERVER['REQUEST_URI'], 'checks') !== false) ? 'active' : '' ?>" 
                       href="/php-cafeteria/views/admin/checks/check.php">
                        <i class="fas fa-clipboard-check me-1"></i> Checks
                    </a>
                </li>
            </ul>
            
            <div class="d-flex align-items-center gap-3">
                <div class="text-white d-flex align-items-center">
                    <img src="/php-cafeteria/public/uploads/users/<?= htmlspecialchars($user_image) ?>" 
                         alt="User" class="rounded-circle me-2" width="40" height="40">
                    <span><?= htmlspecialchars($username) ?></span>
                </div>
                
                <a href="/php-cafeteria/Authentication/logout.php" class="btn btn-outline-light">
                    <i class="fas fa-sign-out-alt me-1"></i> Logout
                </a>
            </div>
        </div>
    </div>
</nav>

<style>
    .navbar {
        padding: 0.5rem 1rem;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    .nav-link {
        color: white !important;
        transition: all 0.3s;
        padding: 0.5rem 1rem;
        display: flex;
        align-items: center;
    }
    .nav-link:hover {
        background-color: rgba(255,255,255,0.1) !important;
    }
    .nav-link.active {
        background-color: rgba(255,255,255,0.2) !important;
        border-radius: 4px;
        font-weight: 500;
    }
    .navbar-brand {
        font-weight: 600;
        color: white !important;
    }
    .btn-outline-light {
        border-color: rgba(255,255,255,0.3);
        color: white;
    }
    .btn-outline-light:hover {
        background-color: rgba(255,255,255,0.1);
    }
</style>