
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    mysqli_query($myconnection, "UPDATE orders SET status = 'out for delivery' WHERE id = $order_id");
    
   
    header("Location: details.php?id=$order_id");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mark as Out for Delivery | Cafeteria System</title>
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
        .btn-warning {
            background-color: #ffc107;
            border-color: #ffc107;
            color: #212529;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="card confirmation-card">
            <div class="card-header bg-warning">
                <h4 class="mb-0"><i class="fas fa-truck me-2"></i>Out for Delivery</h4>
            </div>
            <div class="card-body">
                <h5 class="card-title">Confirm Order is Out for Delivery</h5>
                <p class="card-text">
                    You are about to mark Order #<?= $order_id ?> for <?= $order_data['user'] ?> as "Out for Delivery".
                </p>
                
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Note:</strong> This will notify the customer that their order is on the way.
                </div>
                
                <form method="post">
                    <div class="mb-3">
                        <label for="delivery_notes" class="form-label">Delivery Notes (optional):</label>
                        <textarea class="form-control" id="delivery_notes" name="delivery_notes" rows="3"></textarea>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="details.php?id=<?= $order_id ?>" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i> Back to Order
                        </a>
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-truck me-2"></i> Confirm Out for Delivery
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>