<?php
$servername = "localhost";
$username = "root"; 
$password = ""; 
$dbname = "PHP_Project"; 
$conn = mysqli_connect($servername, $username, $password, $dbname);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';
$selected_user = isset($_GET['user']) ? $_GET['user'] : '';
$users_query = "SELECT DISTINCT id, name FROM users ORDER BY name";
$users_result = mysqli_query($conn, $users_query);
$orders_query = "SELECT o.id, o.user_id, u.name as username, o.total, o.status, o.created_at 
                FROM orders o
                JOIN users u ON o.user_id = u.id
                WHERE 1=1";
if (!empty($date_from)) {
    $orders_query .= " AND DATE(o.created_at) >= '" . mysqli_real_escape_string($conn, $date_from) . "'";
}
if (!empty($date_to)) {
    $orders_query .= " AND DATE(o.created_at) <= '" . mysqli_real_escape_string($conn, $date_to) . "'";
}
if (!empty($selected_user)) {
    $orders_query .= " AND o.user_id = " . mysqli_real_escape_string($conn, $selected_user);
}
$records_per_page = 5;
$page = isset($_GET['page']) ? $_GET['page'] : 1;
$offset = ($page - 1) * $records_per_page;
$total_result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM (" . $orders_query . ") as subquery");
$total_row = mysqli_fetch_assoc($total_result);
$total_pages = ceil($total_row['total'] / $records_per_page);
$orders_query .= " ORDER BY o.created_at DESC LIMIT $offset, $records_per_page";
$orders_result = mysqli_query($conn, $orders_query);
$category_counts = [];
$category_query = "SELECT c.name, COUNT(op.id) as count
                  FROM categories c
                  LEFT JOIN products p ON c.id = p.category_id
                  LEFT JOIN order_products op ON p.id = op.product_id
                  LEFT JOIN orders o ON op.order_id = o.id
                  WHERE 1=1";
if (!empty($date_from)) {
    $category_query .= " AND DATE(o.created_at) >= '" . mysqli_real_escape_string($conn, $date_from) . "'";
}
if (!empty($date_to)) {
    $category_query .= " AND DATE(o.created_at) <= '" . mysqli_real_escape_string($conn, $date_to) . "'";
}

if (!empty($selected_user)) {
    $category_query .= " AND o.user_id = " . mysqli_real_escape_string($conn, $selected_user);
}
$category_query .= " GROUP BY c.name LIMIT 4";
$category_result = mysqli_query($conn, $category_query);
while ($row = mysqli_fetch_assoc($category_result)) {
    $category_counts[$row['name']] = $row['count'];
}
function get_order_details($conn, $order_id) {
    $query = "SELECT op.id, op.quantity, op.price, op.notes, p.name as product_name, c.name as category_name, o.created_at
              FROM order_products op
              JOIN products p ON op.product_id = p.id
              JOIN categories c ON p.category_id = c.id
              JOIN orders o ON op.order_id = o.id
              WHERE op.order_id = " . mysqli_real_escape_string($conn, $order_id);
    
    return mysqli_query($conn, $query);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Checks</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .navbar-custom {
            background-color: #6F4E37;
            padding: 15px 0; 
            height: 80px; 
            box-shadow: 0 4px 12px rgba(0,0,0,0.1); 
        }
        .navbar-brand {
            font-size: 1.8rem; 
            font-weight: bold;
            display: flex;
            align-items: center;
        }
        .navbar-brand i {
            font-size: 2rem;
            margin-right: 10px;
        }
        .nav-link {
            font-size: 1.1rem; 
            padding: 10px 15px !important;
            margin: 0 5px;
            border-radius: 5px;
            transition: all 0.3s;
        }
        .nav-link:hover {
            background-color: rgba(255,255,255,0.1);
        }
        .user-avatar {
            width: 50px; 
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #C4A484; 
            transition: all 0.3s;
        }
        .user-avatar:hover {
            transform: scale(1.05); 
        }
        .user-name {
            font-size: 1.1rem;
            font-weight: 500;
            margin-right: 15px;
            color: white;
        }
        .navbar-toggler {
            padding: 0.5rem 0.75rem;
            font-size: 1.25rem;
        }
        .nav-link {
            margin: 5px 0;
            padding: 8px 12px !important;
        }
        .user-info {
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid rgba(255,255,255,0.1);
        }
        .btn-coffee {
            background-color: #6F4E37;
            color: white;
        }
        .btn-coffee:hover {
            background-color: #5a3c2a;
            color: white;
        }
        .page-item.active .page-link {
            background-color: #6F4E37;
            border-color: #6F4E37;
        }
        .page-link {
            color: #6F4E37;
        }
        .product-card {
            transition: all 0.3s;
            cursor: pointer;
            border: none;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        .product-img {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 4px;
        }
        h1, h2, h3 {
            color: #5a3c2a;
        }
        .pagination {
            justify-content: center;
            margin-top: 20px;
        }
        .filter-section {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .summary-container {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            justify-content: center;
            margin-bottom: 30px;
        }
        .summary-box {
            background-color: #fff;
            border-radius: 10px;
            padding: 20px;
            min-width: 160px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: all 0.3s;
        }
        .summary-box:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.15);
        }
        .summary-box h3 {
            font-size: 1.2rem;
            margin-bottom: 10px;
            color: #5a3c2a;
        }
        .summary-box p {
            font-size: 2rem;
            font-weight: bold;
            margin: 0;
            color: #6F4E37;
        }
        .expandable-row {
            cursor: pointer;
        }
        .expandable-row td:first-child:before {
            content: "+";
            margin-right: 8px;
            font-weight: bold;
            color: #6F4E37;
        }
        .expandable-row.expanded td:first-child:before {
            content: "-";
        }
        .detail-row {
            background-color: #f9f9f9;
        }
        .detail-row td {
            padding: 0;
        }
        .detail-content {
            padding: 15px;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom">
        <div class="container">
            <a class="navbar-brand" href="#">
                Coffee  
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="list.php">Products</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Users</a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link active" href="#">Checks</a>
                    </li>
                </ul>
                <div class="d-flex align-items-center">
                    <span class="user-name">Admin</span>
                    <img src="https://via.placeholder.com/150" alt="Admin" class="user-avatar">
                </div>
            </div>
        </div>
    </nav>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Checks</h1>
        </div>
        <div class="filter-section">
            <form method="GET" action="" class="row g-3">
                <div class="col-md-4">
                    <label for="date_from" class="form-label">Date from:</label>
                    <input type="date" class="form-control" id="date_from" name="date_from" value="<?php echo $date_from; ?>">
                </div>
                <div class="col-md-4">
                    <label for="date_to" class="form-label">Date to:</label>
                    <input type="date" class="form-control" id="date_to" name="date_to" value="<?php echo $date_to; ?>">
                </div>
                <div class="col-md-4">
                    <label for="user" class="form-label">User:</label>
                    <select class="form-select" id="user" name="user">
                        <option value="">All Users</option>
                        <?php while ($user = mysqli_fetch_assoc($users_result)): ?>
                        <option value="<?php echo $user['id']; ?>" <?php if($selected_user == $user['id']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($user['name']); ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-12 text-end">
                    <button type="submit" class="btn btn-coffee">
                        <i class="fas fa-filter me-2"></i>Apply Filters
                    </button>
                </div>
            </form>
        </div>
        <div class="summary-container">
            <?php
            if (count($category_counts) > 0) {
                foreach ($category_counts as $category => $count) {
                    echo "<div class='summary-box'>";
                    echo "<h3>" . htmlspecialchars($category) . "</h3>";
                    echo "<p>" . htmlspecialchars($count) . "</p>";
                    echo "</div>";
                }
            } else {
                
                echo "<div class='summary-box'><h3>Tea</h3><p>0</p></div>";
                echo "<div class='summary-box'><h3>Coffee</h3><p>0</p></div>";
                echo "<div class='summary-box'><h3>Popcorn</h3><p>0</p></div>";
                echo "<div class='summary-box'><h3>Cake</h3><p>0</p></div>";
            }
            ?>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Order Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(mysqli_num_rows($orders_result) > 0): ?>
                                <?php while($row = mysqli_fetch_assoc($orders_result)): ?>
                                    <tr class="expandable-row" id="row-<?php echo $row['id']; ?>" 
                                        onclick="toggleDetails(<?php echo $row['id']; ?>)">
                                        <td><?php echo htmlspecialchars($row['username']); ?></td>
                                        <td><?php echo htmlspecialchars($row['total']); ?> EGP</td>
                                    </tr>
                                    <tr class="detail-row" id="details-<?php echo $row['id']; ?>" style="display: none;">
                                        <td colspan="2">
                                            <div class="detail-content">
                                                <?php
                                                $details = get_order_details($conn, $row['id']);
                                                if(mysqli_num_rows($details) > 0):
                                                ?>
                                                    <table class="table table-sm">
                                                        <thead>
                                                            <tr>
                                                                <th>Date</th>
                                                                <th>Amount</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php while($detail = mysqli_fetch_assoc($details)): ?>
                                                                <tr>
                                                                    <td><?php echo date('Y/m/d h:i A', strtotime($detail['created_at'])); ?></td>
                                                                    <td><?php echo htmlspecialchars($detail['price'] * $detail['quantity']); ?> EGP</td>
                                                                </tr>
                                                            <?php endwhile; ?>
                                                        </tbody>
                                                    </table>
                                                <?php else: ?>
                                                    <p class="text-muted">No details available for this order.</p>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="2" class="text-center py-4">No orders found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <?php if($total_pages > 1): ?>
                <nav aria-label="Page navigation">
                    <ul class="pagination">
                        <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=1&date_from=<?php echo $date_from; ?>&date_to=<?php echo $date_to; ?>&user=<?php echo $selected_user; ?>" aria-label="First">
                                <span aria-hidden="true">&laquo;&laquo;</span>
                            </a>
                        </li>
                        <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $page-1; ?>&date_from=<?php echo $date_from; ?>&date_to=<?php echo $date_to; ?>&user=<?php echo $selected_user; ?>" aria-label="Previous">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>
                        
                        <?php for($i = max(1, $page-2); $i <= min($page+2, $total_pages); $i++): ?>
                            <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>&date_from=<?php echo $date_from; ?>&date_to=<?php echo $date_to; ?>&user=<?php echo $selected_user; ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>
                        
                        <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $page+1; ?>&date_from=<?php echo $date_from; ?>&date_to=<?php echo $date_to; ?>&user=<?php echo $selected_user; ?>" aria-label="Next">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                        <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $total_pages; ?>&date_from=<?php echo $date_from; ?>&date_to=<?php echo $date_to; ?>&user=<?php echo $selected_user; ?>" aria-label="Last">
                                <span aria-hidden="true">&raquo;&raquo;</span>
                            </a>
                        </li>
                    </ul>
                </nav>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleDetails(id) {
            const row = document.getElementById('row-' + id);
            const details = document.getElementById('details-' + id);
            
            if (details.style.display === 'none' || details.style.display === '') {
                details.style.display = 'table-row';
                row.classList.add('expanded');
            } else {
                details.style.display = 'none';
                row.classList.remove('expanded');
            }
        }
        window.addEventListener('DOMContentLoaded', (event) => {
            setTimeout(function() {
                const alertElements = document.querySelectorAll('.alert');
                alertElements.forEach(function(alert) {
                    const closeButton = alert.querySelector('.btn-close');
                    if (closeButton) {
                        closeButton.click();
                    }
                });
            }, 3000);
        });
    </script>
</body>
</html>

<?php
mysqli_close($conn);
?>