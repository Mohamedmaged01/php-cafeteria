<?php
$servername = "localhost";
$username = "root"; 
$password = ""; 
$dbname = "PHP_Project"; 

$conn = mysqli_connect($servername, $username, $password, $dbname);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
session_start();

$records_per_page = 5;
$page = isset($_GET['page']) ? $_GET['page'] : 1;
$offset = ($page - 1) * $records_per_page;
$sql = "SELECT * FROM products LIMIT $offset, $records_per_page";
$result = mysqli_query($conn, $sql);
$total_sql = "SELECT COUNT(*) AS total FROM products";
$total_result = mysqli_query($conn, $total_sql);
$total_row = mysqli_fetch_assoc($total_result);
$total_pages = ceil($total_row['total'] / $records_per_page);


$toasts = [];
if (isset($_GET['deleted']) && $_GET['deleted'] == 'success') {
    $toasts[] = ['type' => 'success', 'message' => 'Product deleted successfully'];
}
if (isset($_GET['created']) && $_GET['created'] == 'success') {
    $toasts[] = ['type' => 'success', 'message' => 'Product added successfully'];
}
if (isset($_GET['updated']) && $_GET['updated'] == 'success') {
    $toasts[] = ['type' => 'success', 'message' => 'Product updated successfully'];
}

if(isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $user_sql = "SELECT * FROM users WHERE id = $user_id";
    $user_result = mysqli_query($conn, $user_sql);
    if($user_result && mysqli_num_rows($user_result) > 0) {
        $user = mysqli_fetch_assoc($user_result);
        $username = $user['name'];
        if(isset($user['picture']) && !empty($user['picture'])) {
            $user_image = $user['picture'];
        }
    }
}

$debug_info = "<!-- Debug info: ";
$debug_info .= "Session user_id: " . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'Not set') . ", ";
$debug_info .= "Username: " . $username . ", ";
$debug_info .= "Image path: " . $user_image;
$debug_info .= " -->";

if(!isset($_SESSION['user_image'])) {
    $_SESSION['user_image'] = 'default.png';
    $_SESSION['user_name'] = 'Admin'; 
}

include '../navbar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - All Products</title>
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
        .product-img {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 4px;
            border: 1px solid #ddd;
        }
        .user-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #C4A484;
        }
        
        
        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1100;
        }
        .toast {
            width: 300px;
            border: none;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }
        .toast-success {
            background-color: #6F4E37;
            color: white;
        }
    </style>
</head>
<body>
   
    
    <!-- Toast Container -->
    <div class="toast-container">
        <?php foreach ($toasts as $toast): ?>
        <div class="toast align-items-center text-white bg-<?php echo $toast['type']; ?> border-0" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="5000">
            <div class="d-flex">
                <div class="toast-body">
                    <i class="fas fa-check-circle me-2"></i>
                    <?php echo $toast['message']; ?>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
  

    <div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Products List</h1>
        <a href="create.php" class="btn btn-coffee">
            <i class="fas fa-plus me-2"></i> Add Product
        </a>
    </div>
    
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Price</th>
                                <th>Image</th>
                                <th>Availability</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['name']); ?></td>
                                <td><?php echo htmlspecialchars($row['price']); ?> EGP</td>
                                <td>
                                    <?php if(!empty($row['image'])): ?>
                                        <img src="/php-cafeteria/public/uploads/products/<?php echo htmlspecialchars($row['image']); ?>" 
                                             alt="<?php echo htmlspecialchars($row['name']); ?>" 
                                             class="product-img"
                                             onerror="this.onerror=null; this.src='/php-cafeteria/public/assets/images/default-product.png';">
                                    <?php else: ?>
                                        <div class="no-image bg-light text-center" style="width:50px;height:50px;line-height:50px;border-radius:4px;">X</div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($row['available'] == 1): ?>
                                        <span class="badge bg-success">Available</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Unavailable</span>
                                    <?php endif; ?>
                                </td>
                                <td>
    <a href="edit.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-primary me-2">
        <i class="fas fa-edit"></i> Edit
    </a>
    <a href="#" onclick="confirmDelete(<?php echo $row['id']; ?>, '<?php echo addslashes($row['name']); ?>')" 
       class="btn btn-sm btn-danger">
       <i class="fas fa-trash"></i> Delete
    </a>
</td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <?php if($total_pages > 1): ?>
            <nav aria-label="Page navigation">
                <ul class="pagination">
                    <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=1" aria-label="First">
                            <span aria-hidden="true">&laquo;&laquo;</span>
                        </a>
                    </li>
                    <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $page-1; ?>" aria-label="Previous">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>
                    
                    <?php for($i = max(1, $page-2); $i <= min($page+2, $total_pages); $i++): ?>
                        <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
                    
                    <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $page+1; ?>" aria-label="Next">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                    <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $total_pages; ?>" aria-label="Last">
                            <span aria-hidden="true">&raquo;&raquo;</span>
                        </a>
                    </li>
                </ul>
            </nav>
            <?php endif; ?>
        </div>
    </div>
<!-- Bootstrap Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Confirm Deletion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete "<span id="productNameToDelete"></span>"?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <a id="confirmDeleteBtn" href="#" class="btn btn-danger">Delete</a>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Initialize all toasts
    document.addEventListener('DOMContentLoaded', function() {
        var toastElList = [].slice.call(document.querySelectorAll('.toast'));
        var toastList = toastElList.map(function(toastEl) {
            return new bootstrap.Toast(toastEl).show();
        });
        
     
        function confirmDelete(productId, productName) {
            if (confirm(`Are you sure you want to delete "${productName}"?`)) {
                window.location.href = `delete.php?id=${productId}`;
            }
        }
    });

    
     function confirmDelete(productId, productName) {
      
        document.getElementById('productNameToDelete').textContent = productName;
        
       
        document.getElementById('confirmDeleteBtn').href = 'delete.php?id=' + productId;
        
      
        var deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
        deleteModal.show();
    }
    </script>
</body>
</html>
<?php
mysqli_close($conn);
?>