<?php
session_start();
include_once"./connect.php"; 
require_once __DIR__ . './allusers/auth.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

checkAdminAuth();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #6F4E37; /* Coffee brown */
            --secondary-color: #5a3c2a; /* Darker coffee */
            --accent-color: #C4A484; /* Light coffee */
            --light-color: #f8f9fa;
            --dark-color: #212529;
        }
        
        body {
            background-color: #f5f5f5;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .sidebar {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            height: 100vh;
            position: fixed;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
        }
        
        .sidebar-brand {
            padding: 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .sidebar-nav {
            padding: 0;
            list-style: none;
        }
        
        .sidebar-nav li {
            padding: 0.5rem 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .sidebar-nav li a {
            color: white;
            text-decoration: none;
            display: block;
            transition: all 0.3s;
        }
        
        .sidebar-nav li a:hover {
            background-color: rgba(255, 255, 255, 0.1);
            padding-left: 1rem;
        }
        
        .sidebar-nav li.active a {
            background-color: rgba(255, 255, 255, 0.2);
            border-left: 4px solid white;
        }
        
        .main-content {
            margin-left: 250px;
            padding: 2rem;
        }
        
        .card {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: all 0.3s;
            border: none;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
        
        .card-header {
            background-color: var(--primary-color);
            color: white;
            border-radius: 10px 10px 0 0 !important;
        }
        
        .btn-coffee {
            background-color: var(--primary-color);
            color: white;
        }
        
        .btn-coffee:hover {
            background-color: var(--secondary-color);
            color: white;
        }
        
        .table th {
            background-color: var(--primary-color);
            color: white;
        }
        
        .badge-coffee {
            background-color: var(--accent-color);
            color: var(--dark-color);
        }
        
        .dashboard-card {
            border-left: 4px solid var(--primary-color);
        }
        
        .profile-img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }
        
        .navbar {
            background-color: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .page-item.active .page-link {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .page-link {
            color: var(--primary-color);
        }
    </style>
</head>
<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <div class="sidebar" style="width: 250px;">
            <div class="sidebar-brand">
                <h3><i class="fas fa-coffee me-2"></i> Cafe Admin</h3>
            </div>
            <ul class="sidebar-nav">
                <li class="active">
                    <a href="dashboard.php"><i class="fas fa-home me-2"></i> Dashboard</a>
                </li>
                <li>
                    <a href="./allusers/list.php"><i class="fas fa-users me-2"></i> Users</a>
                </li>
                <li>
                    <a href="./category/list.php"><i class="fas fa-tags me-2"></i> Categories</a>
                </li>
                <li>
                    <a href="./product/list.php"><i class="fas fa-mug-hot me-2"></i> Products</a>
                </li>
                <li>
                    <a href="./order/list.php"><i class="fas fa-receipt me-2"></i> Orders</a>
                </li>
                <li>
                    <a href="../../Authentication/logout.php"><i class="fas fa-sign-out-alt me-2"></i> Logout</a>
                </li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content" style="flex: 1;">
            <!-- Top Navbar -->
            <nav class="navbar navbar-expand-lg mb-4 rounded">
                <div class="container-fluid">
                    <h4 class="mb-0">Admin Dashboard</h4>
                    <div class="d-flex align-items-center">
                        <span class="me-3">Welcome, <?= htmlspecialchars($_SESSION['name'] ?? 'Admin') ?></span>
                        <img src="../../uploads/profile_pics/<?= $_SESSION['picture'] ?? 'download.jpeg' ?>" 
                             class="profile-img" 
                             onerror="this.src='../uploads/profile_pics/680cab30bbf82.jpeg';">
                    </div>
                </div>
            </nav>

            <!-- Dashboard Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card dashboard-card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted mb-2">Total Users</h6>
                                    <h3>
                                        <?php 
                                        $result = $myconnection->query("SELECT COUNT(*) FROM users");
                                        echo $result->fetch_row()[0];
                                        ?>
                                    </h3>
                                </div>
                                <div class="bg-accent p-3 rounded d-flex justify-content-center align-items-center" style="width: 60px; height: 60px;">
                                 <img src="./photo.png" alt="Users" style="width: 80px; height: 80px; object-fit: contain;">
                                 </div>

                            </div>
                            <a href="../Authentication/allusers/list.php" class="btn btn-sm btn-coffee mt-2">View Users</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card dashboard-card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted mb-2">Total Products</h6>
                                    <h3>
                                        <?php 
                                        $result = $myconnection->query("SELECT COUNT(*) FROM products");
                                        echo $result->fetch_row()[0];
                                        ?>
                                    </h3>
                                </div>
                                <div class="bg-accent p-3 rounded">
                                    <i class="fas fa-mug-hot fa-2x text-primary"></i>
                                </div>
                            </div>
                            <a href="../products/list.php" class="btn btn-sm btn-coffee mt-2">View Products</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card dashboard-card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted mb-2">Total Orders</h6>
                                    <h3>
                                        <?php 
                                        $result = $myconnection->query("SELECT COUNT(*) FROM orders");
                                        echo $result->fetch_row()[0];
                                        ?>
                                    </h3>
                                </div>
                                <div class="bg-accent p-3 rounded">
                                    <i class="fas fa-receipt fa-2x text-primary"></i>
                                </div>
                            </div>
                            <a href="../orders/list.php" class="btn btn-sm btn-coffee mt-2">View Orders</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card dashboard-card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted mb-2">Today's Orders</h6>
                                    <h3>
                                        <?php 
                                        $result = $myconnection->query("SELECT COUNT(*) FROM orders WHERE DATE(created_at) = CURDATE()");
                                        echo $result->fetch_row()[0];
                                        ?>
                                    </h3>
                                </div>
                                <div class="bg-accent p-3 rounded">
                                    <i class="fas fa-calendar-day fa-2x text-primary"></i>
                                </div>
                            </div>
                            <a href="../orders/list.php?filter=today" class="btn btn-sm btn-coffee mt-2">View Today</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Orders -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-history me-2"></i> Recent Orders</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Customer</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $query = "SELECT o.*, u.name FROM orders o 
                                          JOIN users u ON o.user_id = u.id 
                                          ORDER BY o.created_at DESC LIMIT 5";
                                $result = $myconnection->query($query);
                                
                                if ($result->num_rows > 0) {
                                    while ($order = $result->fetch_assoc()) {
                                        $statusClass = [
                                            'Processing' => 'bg-warning',
                                            'out for delivery' => 'bg-info',
                                            'completed' => 'bg-success',
                                            'cancelled' => 'bg-danger'
                                        ];
                                ?>
                                <tr>
                                    <td>#<?= $order['id'] ?></td>
                                    <td><?= htmlspecialchars($order['name']) ?></td>
                                    <td>$<?= number_format($order['total'], 2) ?></td>
                                    <td>
                                        <span class="badge <?= $statusClass[$order['status']] ?>">
                                            <?= $order['status'] ?>
                                        </span>
                                    </td>
                                    <td><?= date('M d, Y h:i A', strtotime($order['created_at'])) ?></td>
                                    <td>
                                        <a href="../orders/view.php?id=<?= $order['id'] ?>" class="btn btn-sm btn-coffee">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                                <?php
                                    }
                                } else {
                                    echo '<tr><td colspan="6" class="text-center">No recent orders found</td></tr>';
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="text-end mt-3">
                        <a href="../orders/list.php" class="btn btn-coffee">View All Orders</a>
                    </div>
                </div>
            </div>

            <!-- Recent Users -->
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-user-plus me-2"></i> Recent Users</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Role</th>
                                            <th>Joined</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $query = "SELECT name, email, role, created_at FROM users ORDER BY created_at DESC LIMIT 5";
                                        $result = $myconnection->query($query);
                                        
                                        if ($result->num_rows > 0) {
                                            while ($user = $result->fetch_assoc()) {
                                        ?>
                                        <tr>
                                            <td><?= htmlspecialchars($user['name']) ?></td>
                                            <td><?= htmlspecialchars($user['email']) ?></td>
                                            <td>
                                                <span class="badge <?= $user['role'] == 'admin' ? 'bg-dark' : 'bg-secondary' ?>">
                                                    <?= ucfirst($user['role']) ?>
                                                </span>
                                            </td>
                                            <td><?= date('M d, Y', strtotime($user['created_at'])) ?></td>
                                        </tr>
                                        <?php
                                            }
                                        } else {
                                            echo '<tr><td colspan="4" class="text-center">No users found</td></tr>';
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="text-end mt-3">
                                <a href="./allusers/list.php" class="btn btn-coffee">View All Users</a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Low Stock Products -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i> Low Stock Products</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Product</th>
                                            <th>Category</th>
                                            <th>Price</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $query = "SELECT p.name, p.price, p.available, c.name as category 
                                                  FROM products p 
                                                  JOIN categories c ON p.category_id = c.id 
                                                  WHERE p.available = FALSE 
                                                  ORDER BY p.name LIMIT 5";
                                        $result = $myconnection->query($query);
                                        
                                        if ($result->num_rows > 0) {
                                            while ($product = $result->fetch_assoc()) {
                                        ?>
                                        <tr>
                                            <td><?= htmlspecialchars($product['name']) ?></td>
                                            <td><?= htmlspecialchars($product['category']) ?></td>
                                            <td>$<?= number_format($product['price'], 2) ?></td>
                                            <td>
                                                <span class="badge bg-danger">Out of Stock</span>
                                            </td>
                                        </tr>
                                        <?php
                                            }
                                        } else {
                                            echo '<tr><td colspan="4" class="text-center">All products are in stock</td></tr>';
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="text-end mt-3">
                                <a href="../products/list.php" class="btn btn-coffee">View All Products</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.card');
            cards.forEach((card, index) => {
                setTimeout(() => {
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });
        });
    </script>
</body>
</html>