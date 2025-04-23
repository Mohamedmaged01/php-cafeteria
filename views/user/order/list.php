<?php
include_once '../../../config/db.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1;
    $_SESSION['user_name'] = "Aya";
}

$user_id = $_SESSION['user_id'];
$orders = mysqli_query($myconnection, "SELECT * FROM orders WHERE user_id = $user_id ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .order-card {
            transition: all 0.3s;
            border: none;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .order-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <h1 class="mb-4"><i class="fas fa-history me-2"></i>My Orders</h1>
        
        <div class="row">
            <?php while($order = mysqli_fetch_assoc($orders)): ?>
                <div class="col-md-6 mb-4">
                    <div class="card order-card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <h5 class="card-title">Order #<?= $order['id'] ?></h5>
                                <span class="badge 
                                    <?= $order['status'] == 'Processing' ? 'bg-warning' : 
                                       ($order['status'] == 'completed' ? 'bg-success' : 'bg-info') ?>">
                                    <?= $order['status'] ?>
                                </span>
                            </div>
                            
                            <p class="card-text mt-3">
                                <strong><i class="fas fa-calendar-alt me-2"></i>Date:</strong> 
                                <?= date('Y-m-d H:i', strtotime($order['created_at'])) ?>
                                <br>
                                <strong><i class="fas fa-coins me-2"></i>Total:</strong> 
                                <?= $order['total'] ?> LE
                                <br>
                                <strong><i class="fas fa-door-open me-2"></i>Room:</strong> 
                                <?= $order['room'] ?>
                            </p>
                            
                            <?php if(!empty($order['notes'])): ?>
                                <div class="alert alert-light mt-2 mb-3">
                                    <strong><i class="fas fa-edit me-2"></i>Notes:</strong>
                                    <?= $order['notes'] ?>
                                </div>
                            <?php endif; ?>
                            
                            <a href="details.php?id=<?= $order['id'] ?>" class="btn btn-outline-primary">
                                <i class="fas fa-eye me-2"></i>View Details
                            </a>
                        </div>
                        <div class="card-footer bg-white text-muted">
                            <small>
                                <i class="fas fa-clock me-1"></i>
                                <?= date('h:i A', strtotime($order['created_at'])) ?>
                            </small>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
        
        <?php if(mysqli_num_rows($orders) === 0): ?>
            <div class="text-center py-5">
                <i class="fas fa-box-open fa-4x text-muted mb-4"></i>
                <h3>No Orders Yet</h3>
                <p class="text-muted">You haven't placed any orders yet</p>
                <a href="../index.php" class="btn btn-primary">
                    <i class="fas fa-utensils me-2"></i>Order Now
                </a>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>