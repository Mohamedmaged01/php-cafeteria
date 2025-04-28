<?php
session_start();
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'PHP_Project');

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../../Authentication/login.php");
    exit;
}
$user_id = $_SESSION['user_id'];
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if (!$conn) {
  die("Connection failed: " . mysqli_connect_error());
}
if (isset($_GET['cancel_id'])) {
  $cancel_id = mysqli_real_escape_string($conn, $_GET['cancel_id']);
  $query = "UPDATE orders SET status='cancelled' WHERE id='$cancel_id' AND user_id='$user_id' AND status='Processing'";
  $result = mysqli_query($conn, $query);
  if ($result) {
    echo "success";
  } else {
     echo "Error cancelling order: " . mysqli_error($conn);
  }
  header("Location: list.php");
  exit;
}


$from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$to = isset($_GET['date_to']) ? $_GET['date_to'] : '';
$where = "user_id = '$user_id'";

if ($from) {
  $from = mysqli_real_escape_string($conn, $from);
  $where .= " AND created_at >= '$from 00:00:00'";
}
if ($to) {
  $to = mysqli_real_escape_string($conn, $to);
  $where .= " AND created_at <= '$to 23:59:59'";
}

$sql = "
  SELECT id, created_at AS order_date, status, total
  FROM orders
  WHERE $where
  ORDER BY created_at DESC
";

$result = mysqli_query($conn, $sql);
if (!$result) {
  die("Query failed: " . mysqli_error($conn));
}

$orders = [];
while ($row = mysqli_fetch_assoc($result)) {
  $orders[] = $row;
}

$user_query = "SELECT name FROM users WHERE id = '$user_id'";
$user_result = mysqli_query($conn, $user_query);
$user_name = "";
if ($user_result && mysqli_num_rows($user_result) > 0) {
  $user_data = mysqli_fetch_assoc($user_result);
  $user_name = $user_data['name'];
}


$items_per_page = 5;
$total_items = count($orders);
$total_pages = ceil($total_items / $items_per_page);
$current_page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($current_page - 1) * $items_per_page;


$current_page_orders = array_slice($orders, $offset, $items_per_page);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>My Orders</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
    .order-section {
        background: #f8f9fa;
        height: 100vh;
        position: sticky;
        top: 0;
        border-left: 1px solid #dee2e6;
    }
    .order-item {
        border-bottom: 1px solid #eee;
        padding: 10px 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .order-item-img {
        width: 50px;
        height: 50px;
        object-fit: cover;
        border-radius: 5px;
    }
    .btn-coffee {
        background-color: #6F4E37;
        color: white;
    }
    .btn-coffee:hover {
        background-color: #5a3c2a;
        color: white;
    }
    h1, h2, h3 {
        color: #5a3c2a;
    }
    .date-picker {
        border: 1px solid #ced4da;
        border-radius: 4px;
        padding: 8px 12px;
    }
    .filter-form {
        background-color: #f8f9fa;
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 20px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    }
    .order-table {
        box-shadow: 0 0 15px rgba(0,0,0,0.1);
        border-radius: 8px;
        overflow: hidden;
    }
    .order-table th {
        background-color: #6F4E37;
        color: white;
        font-weight: 500;
    }
    .order-row {
        cursor: pointer;
        transition: background-color 0.3s;
    }
    .order-row:hover {
        background-color: rgba(196, 164, 132, 0.1);
    }
    .cancel-btn {
        background-color: #dc3545;
        color: white;
        border: none;
        padding: 5px 10px;
        border-radius: 4px;
        font-weight: 500;
    }
    .cancel-btn:hover {
        background-color: #c82333;
    }
    .order-details {
        background-color: #f9f9f9;
    }
    .order-details-table {
        margin-bottom: 0;
    }
    .order-details-table th {
        background-color: #e9ecef;
        color: #495057;
    }
    .order-item-row {
        border-bottom: 1px solid #e9ecef;
    }
    .order-item-row:last-child {
        border-bottom: none;
    }
    .expand-icon {
        transition: transform 0.3s;
    }
    .collapsed .expand-icon {
        transform: rotate(180deg);
    }
    .product-img {
        width: 40px;
        height: 40px;
        object-fit: cover;
        border-radius: 4px;
        margin-right: 10px;
    }
    .pagination {
        justify-content: center;
        margin-top: 20px;
    }
    .total-row {
        font-weight: bold;
        background-color: #f8f9fa;
    }
  </style>
</head>
<body>
  <nav class="navbar navbar-expand-lg navbar-dark navbar-custom mb-4">
    <div class="container">
      <a class="navbar-brand" href="#"><i class="fas fa-mug-hot"></i> Coffee Shop</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav me-auto">
          <li class="nav-item">
            <a class="nav-link" href="index.php"><i class="fas fa-home"></i> Home</a>
          </li>
          <li class="nav-item">
            <a class="nav-link active" href="orders.php"><i class="fas fa-list"></i> My Orders</a>
          </li>
        </ul>
        <div class="d-flex align-items-center user-info">
          <?php if (!empty($user_name)): ?>
            <span class="user-name"><?= htmlspecialchars($user_name) ?></span>
          <?php endif; ?>
          <a href="profile.php" class="d-block">
            <img src="assets/default-avatar.jpg" alt="User" class="user-avatar">
          </a>
        </div>
      </div>
    </div>
  </nav>

  <div class="container mb-5">
    <h1 class="mb-4"><i class="fas fa-clipboard-list me-2"></i>My Orders</h1>

    <div class="filter-form">
      <form class="row g-3 align-items-end" method="get">
        <div class="col-md-4 col-sm-6">
          <label for="date_from" class="form-label"><i class="far fa-calendar-alt me-2"></i>Date from</label>
          <input type="date" id="date_from" name="date_from" class="form-control" value="<?= htmlspecialchars($from) ?>">
        </div>
        <div class="col-md-4 col-sm-6">
          <label for="date_to" class="form-label"><i class="far fa-calendar-alt me-2"></i>Date to</label>
          <input type="date" id="date_to" name="date_to" class="form-control" value="<?= htmlspecialchars($to) ?>">
        </div>
        <div class="col-md-4 col-sm-12 d-flex gap-2">
          <button type="submit" class="btn btn-coffee flex-grow-1">
            <i class="fas fa-filter me-2"></i>Filter
          </button>
          <a href="orders.php" class="btn btn-outline-secondary flex-grow-1">
            <i class="fas fa-redo me-2"></i>Reset
          </a>
        </div>
      </form>
    </div>

    <?php if (count($current_page_orders) > 0): ?>
    <div class="order-table">
      <table class="table table-hover mb-0">
        <thead>
          <tr>
            <th>Order Date</th>
            <th>Status</th>
            <th>Amount</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach($current_page_orders as $o): ?>
          <tr class="order-row" data-bs-toggle="collapse" data-bs-target="#details-<?= $o['id'] ?>" aria-expanded="false">
            <td>
              <?= date('Y/m/d h:i A', strtotime($o['order_date'])) ?>
              <i class="fas fa-chevron-down ms-2 expand-icon"></i>
            </td>
            <td>
              <?php 
                $statusClass = '';
                $statusIcon = '';
                switch(strtolower($o['status'])) {
                  case 'processing':
                    $statusClass = 'text-primary';
                    $statusIcon = 'fa-spinner fa-spin';
                    break;
                  case 'out for delivery':
                    $statusClass = 'text-warning';
                    $statusIcon = 'fa-truck';
                    break;
                  case 'completed':
                  case 'done':
                    $statusClass = 'text-success';
                    $statusIcon = 'fa-check-circle';
                    break;
                  case 'cancelled':
                    $statusClass = 'text-danger';
                    $statusIcon = 'fa-times-circle';
                    break;
                }
              ?>
              <span class="<?= $statusClass ?>">
                <i class="fas <?= $statusIcon ?> me-1"></i>
                <?= htmlspecialchars($o['status']) ?>
              </span>
            </td>
            <td>EGP <?= number_format($o['total'],2) ?></td>
            <td>
              <?php if(strtolower($o['status']) === 'processing'): ?>
                <button class="cancel-btn" 
                  onclick="event.stopPropagation(); if(confirm('Are you sure you want to cancel this order?')) window.location.href='?cancel_id=<?= $o['id'] ?>'">
                  CANCEL
                </button>
              <?php endif ?>
            </td>
          </tr>
          <tr class="collapse order-details" id="details-<?= $o['id'] ?>">
            <td colspan="4" class="p-0">
              <div class="p-3">
                <table class="table order-details-table">
                  <thead>
                    <tr>
                      <th>Product</th>
                      <th>Qty</th>
                      <th>Unit Price</th>
                      <th>Subtotal</th>
                    </tr>
                  </thead>
                  <tbody>
                  <?php
                    $item_query = "SELECT p.name, p.image, oi.quantity, oi.price 
                                  FROM order_products oi
                                  JOIN products p ON p.id=oi.product_id
                                  WHERE oi.order_id='" . mysqli_real_escape_string($conn, $o['id']) . "'";
                    $item_result = mysqli_query($conn, $item_query);
                    
                    if (!$item_result) {
                      echo "<tr><td colspan='4'>Error loading order details: " . mysqli_error($conn) . "</td></tr>";
                    } else {
                      while($item = mysqli_fetch_assoc($item_result)):
                  ?>
                    <tr class="order-item-row">
                      <td class="d-flex align-items-center">
                        <?php if (!empty($item['image'])): ?>
                          <img src="assets/products/<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="product-img">
                        <?php else: ?>
                          <div class="product-img bg-light d-flex align-items-center justify-content-center">
                            <i class="fas fa-coffee text-muted"></i>
                          </div>
                        <?php endif; ?>
                        <?= htmlspecialchars($item['name']) ?>
                      </td>
                      <td><?= (int)$item['quantity'] ?></td>
                      <td>EGP <?= number_format($item['price'],2) ?></td>
                      <td>EGP <?= number_format($item['quantity']*$item['price'],2) ?></td>
                    </tr>
                  <?php 
                      endwhile;
                      if (mysqli_num_rows($item_result) == 0) {
                        echo "<tr><td colspan='4'>No items found for this order</td></tr>";
                      }
                    }
                  ?>
                  </tbody>
                </table>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
        <tfoot>
          <tr class="total-row">
            <th colspan="2">Total (Current Page)</th>
            <th colspan="2">
              <?php
              $total = 0;
              foreach ($current_page_orders as $order) {
                $total += $order['total'];
              }
              ?>
              EGP <?= number_format($total, 2) ?>
            </th>
          </tr>
        </tfoot>
      </table>
    </div>
    
    <?php if($total_pages > 1): ?>
    <nav aria-label="Orders pagination">
      <ul class="pagination">
        <li class="page-item <?= ($current_page <= 1) ? 'disabled' : '' ?>">
          <a class="page-link" href="?page=<?= $current_page-1 ?><?= $from ? '&date_from='.$from : '' ?><?= $to ? '&date_to='.$to : '' ?>" aria-label="Previous">
            <span aria-hidden="true">&laquo;</span>
          </a>
        </li>
        
        <?php for($i = 1; $i <= $total_pages; $i++): ?>
        <li class="page-item <?= ($current_page == $i) ? 'active' : '' ?>">
          <a class="page-link" href="?page=<?= $i ?><?= $from ? '&date_from='.$from : '' ?><?= $to ? '&date_to='.$to : '' ?>">
            <?= $i ?>
          </a>
        </li>
        <?php endfor; ?>
        
        <li class="page-item <?= ($current_page >= $total_pages) ? 'disabled' : '' ?>">
          <a class="page-link" href="?page=<?= $current_page+1 ?><?= $from ? '&date_from='.$from : '' ?><?= $to ? '&date_to='.$to : '' ?>" aria-label="Next">
            <span aria-hidden="true">&raquo;</span>
          </a>
        </li>
      </ul>
    </nav>
    <?php endif; ?>
    
    <?php else: ?>
      <div class="alert alert-info">
        <i class="fas fa-info-circle me-2"></i>
        No orders found. Try adjusting your filter or create a new order.
      </div>
    <?php endif; ?>
  </div>

  <footer class="bg-dark text-white py-4 mt-5">
    <div class="container text-center">
      <p class="mb-0">&copy; <?= date('Y') ?> Coffee Shop. All rights reserved.</p>
      <p class="small text-muted">Enjoy the perfect brew every time.</p>
    </div>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    document.querySelectorAll('[data-bs-toggle="collapse"]').forEach(item => {
      item.addEventListener('click', event => {
        const icon = item.querySelector('.expand-icon');
        if (icon) {
          icon.classList.toggle('rotate-180');
        }
      })
    });
  </script>
</body>
</html>
<?php
mysqli_close($conn);
?>