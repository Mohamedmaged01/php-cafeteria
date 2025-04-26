<?php
include_once '../../../config/db.php';
session_start();


if (!isset($_SESSION['admin_id'])) {
    header("Location: /php-cafeteria/views/admin/login.php");
    exit();
}

$order_id = intval($_GET['id']);


$order = mysqli_query($myconnection, "SELECT * FROM orders WHERE id = $order_id");
$order_data = mysqli_fetch_assoc($order);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
   
    mysqli_query($myconnection, "UPDATE orders SET status = 'cancelled' WHERE id = $order_id");
    
    
    header("Location: details.php?id=$order_id");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cancel Order | Cafeteria System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
        }
        .confirmation-card {
            max-width: 600px;
            margin: 2rem auto;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .btn-danger {
            background-color: #dc3545;
            border-color: #dc3545;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="card confirmation-card">
            <div class="card-header bg-danger text-white">
                <h4 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i>Cancel Order</h4>
            </div>
            <div class="card-body">
                <h5 class="card-title">Confirm Order Cancellation</h5>
                <p class="card-text">
                    You are about to cancel Order #<?= $order_id ?> for <?= $order_data['user'] ?>.
                    This action cannot be undone.
                </p>
                
                <div class="alert alert-warning">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Note:</strong> Cancelling this order will notify the customer.
                </div>
                
                <form method="post">
                    <div class="mb-3">
                        <label for="reason" class="form-label">Reason for cancellation (optional):</label>
                        <textarea class="form-control" id="reason" name="reason" rows="3"></textarea>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="details.php?id=<?= $order_id ?>" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i> Back to Order
                        </a>
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-times-circle me-2"></i> Confirm Cancellation
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>