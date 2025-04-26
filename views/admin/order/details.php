<?php
include_once '../../../config/db.php';
session_start();

// Admin login check
// if (!isset($_SESSION['admin_id'])) {
//     header("Location: /php-cafeteria/views/admin/login.php");
//     exit();
// }

$order_id = intval($_GET['id']);
$order = mysqli_query($myconnection, "SELECT * FROM orders WHERE id = $order_id");
$order_data = mysqli_fetch_assoc($order);

$items = mysqli_query($myconnection, "
    SELECT p.name, p.image, op.quantity, op.price
    FROM order_products op
    JOIN products p ON p.id = op.product_id
    WHERE op.order_id = $order_id
");


$customer = mysqli_query($myconnection, "SELECT * FROM users WHERE id = {$order_data['user_id']}");
$customer_data = mysqli_fetch_assoc($customer);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details - Admin | Cafeteria System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #6F4E37; 
            --secondary-color: #C4A484; 
            --accent-color: #3E2723; 
        }
        
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .order-card {
            border-radius: 15px;
            border: none;
            box-shadow: 0 6px 15px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .order-header {
            background-color: var(--primary-color);
            color: white;
            padding: 1.5rem;
        }
        
        .status-badge {
            font-size: 1rem;
            padding: 8px 15px;
            border-radius: 50px;
        }
        
        .btn-coffee {
            background-color: var(--primary-color);
            color: white;
            padding: 10px 25px;
            font-size: 1.1rem;
            border-radius: 50px;
            transition: all 0.3s;
        }
        
        .btn-coffee:hover {
            background-color: var(--accent-color);
            color: white;
            transform: translateY(-2px);
        }
        
        .item-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 10px;
        }
        
        .divider {
            border-top: 2px dashed #dee2e6;
            margin: 1.5rem 0;
        }
        
        .info-icon {
            color: var(--primary-color);
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        
        .customer-card {
            border-left: 4px solid var(--primary-color);
        }
    </style>
</head>
<body>
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-receipt me-2"></i>Order Details</h2>
            <a href="index.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Orders
            </a>
        </div>
        
        <div class="order-card mb-5">
            <!-- Header Section -->
            <div class="order-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h1 class="h3 mb-0">
                        <i class="fas fa-receipt"></i> Order #<?= $order_data['id'] ?>
                    </h1>
                    <span class="badge 
                        <?= $order_data['status'] == 'Processing' ? 'bg-warning' : 
                           ($order_data['status'] == 'completed' ? 'bg-success' : 'bg-info') ?> 
                        status-badge">
                        <?= $order_data['status'] ?>
                    </span>
                </div>
                <p class="mb-0 mt-2">
                    <i class="fas fa-calendar-alt"></i> 
                    <?= date('F j, Y \a\t h:i A', strtotime($order_data['created_at'])) ?>
                </p>
                <?php if($order_data['placed_by_admin']): ?>
                    <p class="mb-0"><i class="fas fa-user-shield"></i> Order placed by admin</p>
                <?php endif; ?>
            </div>
            
            <!-- Order Details -->
            <div class="card-body p-4">
                <!-- Customer Information -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card customer-card mb-3">
                            <div class="card-body">
                                <h5 class="card-title"><i class="fas fa-user info-icon"></i>Customer Information</h5>
                                <div class="d-flex align-items-center mb-3">
                                    <img src="/php-cafeteria/public/uploads/<?= $customer_data['picture'] ?>" 
                                         class="rounded-circle me-3" 
                                         style="width: 60px; height: 60px; object-fit: cover;">
                                    <div>
                                        <h6 class="mb-1"><?= $customer_data['name'] ?></h6>
                                        <small class="text-muted"><?= $customer_data['email'] ?></small>
                                    </div>
                                </div>
                                <?php if(!empty($order_data['room_id'])): ?>
                                    <?php 
                                    $room_query = mysqli_query($myconnection, "SELECT number FROM rooms WHERE id = {$order_data['room_id']}");
                                    $room_number = mysqli_fetch_assoc($room_query)['number'];
                                    ?>
                                    <p class="mb-1"><i class="fas fa-door-open"></i> <strong>Room:</strong> <?= $room_number ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title"><i class="fas fa-coins info-icon"></i>Payment Summary</h5>
                                <h4 class="text-success">Total: <?= $order_data['total'] ?> LE</h4>
                                <?php if(!empty($order_data['notes'])): ?>
                                    <div class="mt-3">
                                        <h6><i class="fas fa-edit info-icon"></i>Special Instructions</h6>
                                        <p class="mb-0"><?= $order_data['notes'] ?></p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="divider"></div>

                <!-- Order Items -->
                <h4 class="mb-3"><i class="fas fa-list-ul info-icon"></i>Order Items</h4>
                <div class="table-responsive">
                    <table class="table">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 50px"></th>
                                <th>Item</th>
                                <th class="text-center">Qty</th>
                                <th class="text-end">Price</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($item = mysqli_fetch_assoc($items)): ?>
                                <tr>
                                    <td>
                                        <img src="/php-cafeteria/public/uploads/<?= $item['image'] ?>" 
                                             class="item-image" 
                                             alt="<?= $item['name'] ?>">
                                    </td>
                                    <td><?= $item['name'] ?></td>
                                    <td class="text-center"><?= $item['quantity'] ?></td>
                                    <td class="text-end"><?= $item['price'] ?> LE</td>
                                    <td class="text-end"><?= $item['price'] * $item['quantity'] ?> LE</td>
                                </tr>
                            <?php endwhile; ?>
                            <tr class="table-light">
                                <td colspan="4" class="text-end"><strong>Total:</strong></td>
                                <td class="text-end"><strong><?= $order_data['total'] ?> LE</strong></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Footer with Admin Actions -->
            <div class="card-footer bg-white p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <?php if($order_data['status'] == 'Processing'): ?>
                            <a href="update_status.php?id=<?= $order_id ?>&status=out for delivery" class="btn btn-warning me-2">
                                <i class="fas fa-truck me-2"></i>Mark as Out for Delivery
                            </a>
                        <?php elseif($order_data['status'] == 'out for delivery'): ?>
                            <a href="update_status.php?id=<?= $order_id ?>&status=completed" class="btn btn-success me-2">
                                <i class="fas fa-check-circle me-2"></i>Mark as Completed
                            </a>
                        <?php endif; ?>
                        
                        <?php if($order_data['status'] != 'cancelled' && $order_data['status'] != 'completed'): ?>
                            <a href="update_status.php?id=<?= $order_id ?>&status=cancelled" class="btn btn-danger me-2">
                                <i class="fas fa-times-circle me-2"></i>Cancel Order
                            </a>
                        <?php endif; ?>
                    </div>
                    <a href="index.php" class="btn btn-coffee">
                        <i class="fas fa-arrow-left me-2"></i>Back to Orders
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>