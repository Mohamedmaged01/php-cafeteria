<?php
include_once '../../../config/db.php';

session_start();
// Admin login check (commented out for testing)
// if (!isset($_SESSION['admin_id'])) {
//     header("Location: /php-cafeteria/views/admin/login.php");
//     exit();
// }


$category_filter = isset($_GET['category']) ? (int)$_GET['category'] : 0;

$per_page = 8;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page > 1) ? ($page * $per_page) - $per_page : 0;


$products_query = "SELECT p.*, c.name as category_name FROM products p 
                  LEFT JOIN categories c ON p.category_id = c.id 
                  WHERE p.available = 1";


if ($category_filter > 0) {
    $products_query .= " AND p.category_id = $category_filter";
}


$total_query = "SELECT COUNT(*) as total FROM products WHERE available = 1";
if ($category_filter > 0) {
    $total_query .= " AND category_id = $category_filter";
}

$total = mysqli_query($myconnection, $total_query);
$total = mysqli_fetch_assoc($total)['total'];
$pages = ceil($total / $per_page);


$products_query .= " LIMIT $start, $per_page";


$products = mysqli_query($myconnection, $products_query);


$categories = mysqli_query($myconnection, "SELECT * FROM categories");


$rooms = mysqli_query($myconnection, "SELECT * FROM rooms WHERE status = 'available'");


$users = mysqli_query($myconnection, "SELECT u.id, u.name, u.email, u.picture, r.number as room_number 
                                     FROM users u 
                                     LEFT JOIN rooms r ON u.room_id = r.id 
                                     WHERE u.role = 'customer'");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Place Order</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
      
      body {
          font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      }
      
  
      .product-card {
          transition: all 0.3s ease;
          cursor: pointer;
          border: none;
          box-shadow: 0 2px 10px rgba(0,0,0,0.1);
          border-radius: 10px;
          overflow: hidden;
      }
      
      .product-card:hover {
          transform: translateY(-5px);
          box-shadow: 0 10px 20px rgba(0,0,0,0.15);
      }
      
     
      .order-item {
          display: flex;
          align-items: center;
          gap: 15px;
          padding: 10px 0;
          border-bottom: 1px solid #eee;
      }
      
      .order-item-img {
          width: 60px;
          height: 60px;
          object-fit: cover;
          border-radius: 8px;
          border: 1px solid #eee;
      }
      
      .order-item-details {
          flex: 1;
          min-width: 0;
      }
      
      .order-item-buttons {
          display: flex;
          gap: 8px;
          align-items: center;
      }
      
    
      .btn-brown {
          background-color: #6F4E37;
          color: white;
          border-color: #5a3c2a;
          transition: all 0.3s;
      }
      
      .btn-brown:hover {
          background-color: #5a3c2a;
          color: white;
          transform: translateY(-2px);
      }
      
      .btn-outline-brown {
          color: #6F4E37;
          border-color: #6F4E37;
      }
      
      .btn-outline-brown:hover {
          background-color: #6F4E37;
          color: white;
      }
     
      .pagination .page-link {
          color: #6F4E37;
          border-color: #d2b48c;
      }
      
      .pagination .page-item.active .page-link {
          background-color: #6F4E37;
          border-color: #6F4E37;
          color: white;
      }
      
      .pagination .page-item:hover .page-link {
          background-color: #f5f5f5;
      }
      
      
      .toast-container {
          position: fixed;
          top: 20px;
          right: 20px;
          z-index: 1100;
      }
      
  
      .order-section {
          background: #f9f9f9;
          border-left: 1px solid #eee;
          height: 100vh;
          position: sticky;
          top: 0;
      }
      
    
      @media (max-width: 992px) {
          .order-section {
              height: auto;
              position: relative;
          }
      }
  </style>
</head>
<body>
    
    <div class="toast-container">
        <div id="liveToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header">
                <strong class="me-auto">Notification</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body"></div>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row">
            <!-- Menu Section -->
            <div class="col-lg-8 p-4">
                <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
                    <h2 class="mb-3 mb-md-0"><i class="fas fa-coffee me-2"></i>Our Menu</h2>
                    <div class="d-flex gap-3">
                        <!-- Category Filter Dropdown -->
                        <div class="search-box" style="width: 200px;">
                            <select id="category-filter" class="form-select" onchange="filterByCategory(this.value)">
                                <option value="0">All Categories</option>
                                <?php while($category = mysqli_fetch_assoc($categories)): ?>
                                    <option value="<?= $category['id'] ?>" <?= $category_filter == $category['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($category['name']) ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        
                        <!-- Search Box -->
                        <div class="search-box" style="width: 100%; max-width: 300px;">
                            <div class="input-group">
                                <input type="text" id="search-input" class="form-control" placeholder="Search products..." 
                                       aria-label="Search products">
                                <button class="btn btn-brown" type="button" id="search-button">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row" id="products-container">
                    <?php 
                    mysqli_data_seek($products, 0);
                    $hasProducts = false;
                    while($product = mysqli_fetch_assoc($products)): 
                        $hasProducts = true;
                    ?>
                        <div class="col-xl-3 col-lg-4 col-md-6 mb-4 product-item">
                            <div class="card product-card h-100" 
                                 onclick="addToOrder(<?= $product['id'] ?>, '<?= htmlspecialchars($product['name'], ENT_QUOTES) ?>', <?= $product['price'] ?>, '<?= $product['image'] ?>')">
                                <img src="/php-cafeteria/public/uploads/<?= $product['image'] ?>" 
                                     class="card-img-top" 
                                     style="height: 180px; object-fit: cover;" 
                                     alt="<?= htmlspecialchars($product['name'], ENT_QUOTES) ?>">
                                <div class="card-body">
                                    <h5 class="card-title"><?= htmlspecialchars($product['name'], ENT_QUOTES) ?></h5>
                                    <p class="card-text text-success"><?= number_format($product['price'], 2) ?> EGP</p>
                                    <?php if(!empty($product['category_name'])): ?>
                                        <span class="badge bg-secondary"><?= $product['category_name'] ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                    
                    <?php if(!$hasProducts): ?>
                        <div class="col-12 text-center py-5 no-products-message">
                            <i class="fas fa-coffee fa-3x mb-3 text-muted"></i>
                            <h4 class="text-muted">No products available</h4>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Pagination -->
                <?php if($pages > 1): ?>
                    <nav aria-label="Page navigation">
                        <ul class="pagination justify-content-center">
                            <?php if($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?= $page-1 ?><?= $category_filter ? '&category='.$category_filter : '' ?>" aria-label="Previous">
                                        <span aria-hidden="true">&laquo;</span>
                                    </a>
                                </li>
                            <?php endif; ?>
                            
                            <?php for($i = 1; $i <= $pages; $i++): ?>
                                <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                                    <a class="page-link" href="?page=<?= $i ?><?= $category_filter ? '&category='.$category_filter : '' ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>
                            
                            <?php if($page < $pages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?= $page+1 ?><?= $category_filter ? '&category='.$category_filter : '' ?>" aria-label="Next">
                                        <span aria-hidden="true">&raquo;</span>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            </div>
            
            <div class="col-lg-4 p-4 order-section">
                <h3 class="mb-4"><i class="fas fa-receipt me-2"></i>Place Order</h3>
                <form method="post" action="create.php" id="order-form">
                    <!-- Customer Selection -->
                    <div class="mb-3">
                        <label for="user_id" class="form-label"><i class="fas fa-user me-2"></i>Select Customer</label>
                        <select name="user_id" id="user_id" class="form-select" required>
                            <option value="">-- Select Customer --</option>
                            <?php while($user = mysqli_fetch_assoc($users)): ?>
                                <option value="<?= $user['id'] ?>" 
                                        data-picture="<?= $user['picture'] ?>"
                                        data-email="<?= $user['email'] ?>"
                                        data-room="<?= $user['room_number'] ?>">
                                    <?= htmlspecialchars($user['name']) ?> 
                                    <?php if($user['room_number']): ?>
                                        (Room <?= $user['room_number'] ?>)
                                    <?php endif; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <!-- Selected Customer Info -->
                    <div id="selected-user-info" class="bg-light p-3 rounded mb-3" style="display: none;">
                        <div class="d-flex align-items-center mb-2">
                            <img id="selected-user-image" src="" class="rounded-circle me-3" style="width: 50px; height: 50px; object-fit: cover;">
                            <div>
                                <h5 id="selected-user-name" class="mb-0"></h5>
                                <small class="text-muted" id="selected-user-email"></small>
                            </div>
                        </div>
                        <div>
                            <i class="fas fa-door-open me-2"></i>
                            <span id="selected-user-room">No room assigned</span>
                        </div>
                    </div>
                    
                   
                    <div class="mb-3" id="order-list" style="max-height: 300px; overflow-y: auto;">
                        <p class="text-muted text-center py-3">No items selected</p>
                    </div>
                    
                   
                    <div class="mb-3">
                        <label for="notes" class="form-label"><i class="fas fa-edit me-2"></i>Special Instructions</label>
                        <textarea name="notes" id="notes" class="form-control" rows="3" placeholder="e.g. Extra sugar, less ice..."></textarea>
                    </div>
                    
                   
                    <div class="mb-3">
                        <label for="room_id" class="form-label"><i class="fas fa-door-open me-2"></i>Delivery Room</label>
                        <select name="room_id" id="room_id" class="form-select">
                            <?php 
                            mysqli_data_seek($rooms, 0);
                            while($room = mysqli_fetch_assoc($rooms)): ?>
                                <option value="<?= $room['id'] ?>"><?= $room['number'] ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                  
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5>Total:</h5>
                        <h4><span id="total-price">0.00</span> EGP</h4>
                    </div>
                    
                    <input type="hidden" name="quantities" id="quantities-input">
                    <button type="submit" class="btn btn-brown btn-lg w-100 py-3" id="confirm-btn" disabled>
                        <i class="fas fa-paper-plane me-2"></i> Place Order for Customer
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function filterByCategory(categoryId) {
            const url = new URL(window.location.href);
            
            if (categoryId > 0) {
                url.searchParams.set('category', categoryId);
            } else {
                url.searchParams.delete('category');
            }
            
            window.location.href = url.toString();
        }

        let order = {};
        const toastLiveExample = document.getElementById('liveToast');
        const toastBootstrap = bootstrap.Toast.getOrCreateInstance(toastLiveExample);
        
        
        function showToast(message) {
            const toastBody = document.querySelector('.toast-body');
            toastBody.textContent = message;
            toastBootstrap.show();
        }
        
        
        function addToOrder(id, name, price, image) {
            const userId = document.getElementById('user_id').value;
            if (!userId) {
                showToast('Please select a customer first');
                return;
            }
            
            if (!order[id]) {
                order[id] = { name, price, quantity: 1, image };
                showToast(`${name} added to order`);
            } else {
                order[id].quantity += 1;
                showToast(`${name} quantity increased to ${order[id].quantity}`);
            }
            updateOrderList();
        }
        
    
        function updateOrderList() {
            const list = document.getElementById('order-list');
            const totalEl = document.getElementById('total-price');
            const quantitiesInput = document.getElementById('quantities-input');
            const confirmBtn = document.getElementById('confirm-btn');
            const userId = document.getElementById('user_id').value;
            
            list.innerHTML = '';
            let total = 0;
            let quantities = {};
            
            if (Object.keys(order).length === 0 || !userId) {
                list.innerHTML = '<p class="text-muted text-center py-3">No items selected</p>';
                totalEl.textContent = '0.00';
                quantitiesInput.value = JSON.stringify({});
                confirmBtn.disabled = true;
                confirmBtn.classList.add('btn-secondary');
                return;
            }
            
          
            confirmBtn.disabled = false;
            confirmBtn.classList.remove('btn-secondary');
         
            for (let id in order) {
                const item = order[id];
                total += item.price * item.quantity;
                quantities[id] = item.quantity;
                
                const itemElement = document.createElement('div');
                itemElement.className = 'order-item';
                itemElement.innerHTML = `
                    <img src="/php-cafeteria/public/uploads/${item.image}" class="order-item-img" alt="${item.name}">
                    <div class="order-item-details">
                        <strong>${item.name}</strong>
                        <div class="text-muted small">${item.price} EGP × ${item.quantity}</div>
                    </div>
                    <div class="order-item-buttons">
                        <button type="button" class="btn btn-outline-brown" 
                                onclick="event.stopPropagation(); updateQuantity(${id}, -1)">
                            <i class="fas fa-minus"></i>
                        </button>
                        <button type="button" class="btn btn-outline-brown" 
                                onclick="event.stopPropagation(); updateQuantity(${id}, 1)">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                `;
                list.appendChild(itemElement);
            }
            
           
            totalEl.textContent = total.toFixed(2);
            quantitiesInput.value = JSON.stringify(quantities);
        }
        
      
        function updateQuantity(id, delta) {
            if (order[id]) {
                order[id].quantity += delta;
                if (order[id].quantity <= 0) {
                    const itemName = order[id].name;
                    delete order[id];
                    showToast(`${itemName} removed from order`);
                } else {
                    showToast(`${order[id].name} quantity updated to ${order[id].quantity}`);
                }
                updateOrderList();
            }
        }
       
        document.getElementById('user_id').addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const userInfoDiv = document.getElementById('selected-user-info');
            const userImage = document.getElementById('selected-user-image');
            const userName = document.getElementById('selected-user-name');
            const userEmail = document.getElementById('selected-user-email');
            const userRoom = document.getElementById('selected-user-room');
            
            if (this.value) {
                userInfoDiv.style.display = 'block';
                userImage.src = '/php-cafeteria/public/uploads/' + selectedOption.getAttribute('data-picture');
                userName.textContent = selectedOption.textContent.split(' (Room')[0];
                userEmail.textContent = selectedOption.getAttribute('data-email');
                
                const roomNumber = selectedOption.getAttribute('data-room');
                userRoom.textContent = roomNumber ? 'Room ' + roomNumber : 'No room assigned';
            } else {
                userInfoDiv.style.display = 'none';
            }
            
            updateOrderList();
        });
        
       
        document.getElementById('search-input').addEventListener('input', performSearch);

        function performSearch() {
            const searchTerm = document.getElementById('search-input').value.toLowerCase();
            const productItems = document.querySelectorAll('.product-item');
            let hasResults = false;
            
            productItems.forEach(item => {
                const productName = item.querySelector('.card-title').textContent.toLowerCase();
                if (productName.includes(searchTerm)) {
                    item.style.display = 'block';
                    hasResults = true;
                } else {
                    item.style.display = 'none';
                }
            });
            
            const noResultsMessage = document.querySelector('.no-results-message');
            if (!hasResults && searchTerm.length > 0) {
                if (!noResultsMessage) {
                    const productsContainer = document.getElementById('products-container');
                    const messageDiv = document.createElement('div');
                    messageDiv.className = 'col-12 text-center py-5 no-results-message';
                    messageDiv.innerHTML = `
                        <i class="fas fa-search fa-3x mb-3 text-muted"></i>
                        <h4 class="text-muted">No results found for "${searchTerm}"</h4>
                        <p class="text-muted">Try different keywords</p>
                    `;
                    productsContainer.appendChild(messageDiv);
                }
            } else if (noResultsMessage) {
                noResultsMessage.remove();
            }
        }

       
        document.querySelectorAll('.page-link').forEach(link => {
            link.addEventListener('click', () => {
                document.getElementById('search-input').value = '';
                
                document.querySelectorAll('.product-item').forEach(item => {
                    item.style.display = 'block';
                });
                
                const noResultsMessage = document.querySelector('.no-results-message');
                if (noResultsMessage) noResultsMessage.remove();
            });
        });

        
        document.getElementById('order-form').addEventListener('submit', function(e) {
            const userId = document.getElementById('user_id').value;
            
            if (Object.keys(order).length === 0 || !userId) {
                e.preventDefault();
                showToast('Please select a customer and add at least one item to the order');
                
                const orderList = document.getElementById('order-list');
                orderList.innerHTML = `
                    <div class="alert alert-warning text-center py-3">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        ${!userId ? 'Please select a customer first' : 'Please add at least one item to the order'}
                    </div>
                `;
            }
        });
    </script>
</body>
</html>