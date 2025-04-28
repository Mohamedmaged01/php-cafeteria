<?php
include_once '../../../config/db.php';

session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../../Authentication/login.php");
    exit;
}

$per_page = 8;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page > 1) ? ($page * $per_page) - $per_page : 0;

$total = mysqli_query($myconnection, "SELECT COUNT(*) as total FROM products WHERE available = 1");
$total = mysqli_fetch_assoc($total)['total'];
$pages = ceil($total / $per_page);

$products = mysqli_query($myconnection, 
    "SELECT * FROM products WHERE available = 1 LIMIT $start, $per_page");

$rooms = mysqli_query($myconnection, "SELECT * FROM rooms WHERE status = 'available'");
$categories = mysqli_query($myconnection, "SELECT * FROM categories");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cafeteria Order System</title>
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
        .search-box {
            transition: all 0.3s ease;
        }
        .search-box:focus-within {
            box-shadow: 0 0 0 0.25rem rgba(111, 78, 55, 0.25);
        }
        #search-input {
            border-color: #6F4E37;
        }
        #search-input:focus {
            border-color: #6F4E37;
            box-shadow: 0 0 0 0.25rem rgba(111, 78, 55, 0.25);
        }
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            20%, 60% { transform: translateX(-5px); }
            40%, 80% { transform: translateX(5px); }
        }
        .animate__headShake {
            animation-name: shake;
            animation-duration: 1s;
            animation-fill-mode: both;
        }
        .alert-warning {
            background-color: #fff3cd;
            color: #856404;
            border-left: 4px solid #ffeeba;
        }
        .btn-outline-coffee {
            color: #6F4E37;
            border-color: #6F4E37;
        }
        .btn-outline-coffee:hover {
            background-color: #6F4E37;
            color: white;
        }
        .btn-outline-coffee.active {
            background-color: #6F4E37;
            color: white;
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-coffee me-2"></i> Café Delight
            </a>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="/php-cafeteria/views/user/order/list.php">
                            <i class="fas fa-list-alt me-1"></i> My Orders
                        </a>
                    </li>
                </ul>
                
                <!-- <div class="d-flex align-items-center">
                    <span class="text-white me-2"><?= $_SESSION['user_name'] ?></span>
                    <img src="/php-cafeteria/public/uploads/<?= $_SESSION['user_image'] ?>" 
                         class="user-avatar" 
                         alt="User Avatar">
                </div> -->
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-8 p-4">
            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
    <h2 class="mb-3 mb-md-0 text-coffee"><i class="fas fa-coffee me-2"></i>Our Menu</h2>
    <div class="d-flex gap-2 flex-wrap" style="width: 100%; max-width: 500px;">
        <div class="search-box flex-grow-1" style="min-width: 200px;">
            <div class="input-group">
                <input type="text" id="search-input" class="form-control" placeholder="Search products..." 
                       aria-label="Search products">
                <button class="btn btn-coffee" type="button" id="search-button">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </div>
        <div class="search-box" style="width: 200px;">
            <select id="category-filter" class="form-select" onchange="filterProducts(this.value)">
                <option value="all">All Categories</option>
                <?php 
                mysqli_data_seek($categories, 0); // Reset categories pointer
                while($category = mysqli_fetch_assoc($categories)): ?>
                    <option value="<?= $category['id'] ?>">
                        <?= htmlspecialchars($category['name']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
    </div>
</div>

                <!-- Filter Section -->
                <div class="mb-4">
                    <div class="btn-group" role="group" aria-label="Product filters">
                        <button type="button" class="btn btn-outline-coffee active" onclick="filterProducts('all')">
                            <i class="fas fa-list me-1"></i> All Items
                        </button>
                        <?php while($category = mysqli_fetch_assoc($categories)): ?>
                            <button type="button" class="btn btn-outline-coffee" onclick="filterProducts(<?= $category['id'] ?>)">
                                <?= htmlspecialchars($category['name']) ?>
                            </button>
                        <?php endwhile; ?>
                    </div>
                </div>

                <div class="row" id="products-container">
                    <?php 
                    mysqli_data_seek($products, 0);
                    $hasProducts = false;
                    while($product = mysqli_fetch_assoc($products)): 
                        $hasProducts = true;
                    ?>
                        <div class="col-xl-3 col-lg-4 col-md-6 mb-4 product-item" data-category="<?= $product['category_id'] ?>">
                            <div class="card product-card h-100" 
                                 onclick="addToOrder(<?= $product['id'] ?>, '<?= htmlspecialchars($product['name'], ENT_QUOTES) ?>', <?= $product['price'] ?>, '<?= $product['image'] ?>')">
                                <img src="/php-cafeteria/public/uploads/<?= $product['image'] ?>" 
                                     class="card-img-top" 
                                     style="height: 180px; object-fit: cover;" 
                                     alt="<?= htmlspecialchars($product['name'], ENT_QUOTES) ?>">
                                <div class="card-body">
                                    <h5 class="card-title"><?= htmlspecialchars($product['name'], ENT_QUOTES) ?></h5>
                                    <p class="card-text text-success"><?= number_format($product['price'], 2) ?> LE</p>
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
                                    <a class="page-link" href="?page=<?= $page-1 ?>" aria-label="Previous">
                                        <span aria-hidden="true">&laquo;</span>
                                    </a>
                                </li>
                            <?php endif; ?>
                            
                            <?php for($i = 1; $i <= $pages; $i++): ?>
                                <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                                    <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>
                            
                            <?php if($page < $pages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?= $page+1 ?>" aria-label="Next">
                                        <span aria-hidden="true">&raquo;</span>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            </div>
            
            <div class="col-lg-4 p-4 order-section">
                <h3 class="mb-4"><i class="fas fa-receipt me-2"></i>Your Order</h3>
                <form method="post" action="create.php" id="order-form">
                    <div class="mb-3" id="order-list" style="max-height: 300px; overflow-y: auto;">
                        <p class="text-muted text-center py-3">No items selected</p>
                    </div>
                    
                    <div class="mb-3">
                        <label for="notes" class="form-label"><i class="fas fa-edit me-2"></i>Special Instructions</label>
                        <textarea name="notes" id="notes" class="form-control" rows="3" placeholder="e.g. Extra sugar, less ice..."></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="room" class="form-label"><i class="fas fa-door-open me-2"></i>Room Number</label>
                        <select name="room_id" id="room" class="form-select">
                            <?php while($room = mysqli_fetch_assoc($rooms)): ?>
                                <option value="<?= $room['id'] ?>"><?= $room['number'] ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5>Total:</h5>
                        <h4><span id="total-price">0.00</span> LE</h4>
                    </div>
                    
                    <input type="hidden" name="quantities" id="quantities-input">
                    <button type="submit" class="btn btn-coffee btn-lg w-100 py-3" id="confirm-btn" disabled>
                        <i class="fas fa-paper-plane me-2"></i> Confirm Order
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let order = {};
        
        function addToOrder(id, name, price, image) {
            if (!order[id]) {
                order[id] = { name, price, quantity: 1, image };
            } else {
                order[id].quantity += 1;
            }
            updateOrderList();
        }
        
        function updateOrderList() {
            const list = document.getElementById('order-list');
            const totalEl = document.getElementById('total-price');
            const quantitiesInput = document.getElementById('quantities-input');
            const confirmBtn = document.getElementById('confirm-btn');
            
            list.innerHTML = '';
            let total = 0;
            let quantities = {};
            
            if (Object.keys(order).length === 0) {
                list.innerHTML = '<p class="text-muted text-center py-3">No items selected</p>';
                totalEl.textContent = '0.00';
                quantitiesInput.value = JSON.stringify({});
                confirmBtn.disabled = true;
                confirmBtn.classList.remove('btn-coffee');
                confirmBtn.classList.add('btn-secondary');
                return;
            }
            
            confirmBtn.disabled = false;
            confirmBtn.classList.add('btn-coffee');
            confirmBtn.classList.remove('btn-secondary');
            
            for (let id in order) {
                const item = order[id];
                total += item.price * item.quantity;
                quantities[id] = item.quantity;
                
                const itemElement = document.createElement('div');
                itemElement.className = 'order-item';
                itemElement.innerHTML = `
                    <img src="/php-cafeteria/public/uploads/${item.image}" class="order-item-img" alt="${item.name}">
                    <div style="flex: 1;">
                        <strong>${item.name}</strong>
                        <div class="text-muted small">${item.price} LE × ${item.quantity}</div>
                    </div>
                    <div>
                        <button type="button" class="btn btn-sm btn-outline-secondary" 
                                onclick="event.stopPropagation(); updateQuantity(${id}, -1)">
                            <i class="fas fa-minus"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" 
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
                    delete order[id];
                }
                updateOrderList();
            }
        }
        
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

        function filterProducts(categoryId) {
            const allItems = document.querySelectorAll('.product-item');
            
            allItems.forEach(item => {
                if (categoryId === 'all') {
                    item.style.display = 'block';
                } else {
                    if (item.dataset.category == categoryId) {
                        item.style.display = 'block';
                    } else {
                        item.style.display = 'none';
                    }
                }
            });
            
            // Reset search input when filtering
            document.getElementById('search-input').value = '';
            
            // Remove any no-results message if present
            const noResultsMessage = document.querySelector('.no-results-message');
            if (noResultsMessage) noResultsMessage.remove();
            
            // Highlight the active filter button
            document.querySelectorAll('.btn-outline-coffee').forEach(btn => {
                btn.classList.remove('active', 'btn-coffee');
                btn.classList.add('btn-outline-coffee');
            });
            
            const buttons = document.querySelectorAll('.btn-outline-coffee');
            if (categoryId === 'all') {
                buttons[0].classList.add('active', 'btn-coffee');
                buttons[0].classList.remove('btn-outline-coffee');
            } else {
                buttons[categoryId].classList.add('active', 'btn-coffee');
                buttons[categoryId].classList.remove('btn-outline-coffee');
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
            if (Object.keys(order).length === 0) {
                e.preventDefault();
                alert('Please add at least one item to your order before confirming');
                
                const orderList = document.getElementById('order-list');
                orderList.innerHTML = `
                    <div class="alert alert-warning text-center py-3">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        Please add at least one item to your order
                    </div>
                `;
                
                document.querySelector('.order-section').classList.add('animate__animated', 'animate__headShake');
                setTimeout(() => {
                    document.querySelector('.order-section').classList.remove('animate__animated', 'animate__headShake');
                }, 1000);
            }
        });

        function filterProducts(categoryId) {
    const allItems = document.querySelectorAll('.product-item');
    
    allItems.forEach(item => {
        if (categoryId === 'all') {
            item.style.display = 'block';
        } else {
            if (item.dataset.category == categoryId) {
                item.style.display = 'block';
            } else {
                item.style.display = 'none';
            }
        }
    });
    
    // Reset search input when filtering
    document.getElementById('search-input').value = '';
    
    // Remove any no-results message if present
    const noResultsMessage = document.querySelector('.no-results-message');
    if (noResultsMessage) noResultsMessage.remove();
    
    // Update the dropdown to show selected option
    document.getElementById('category-filter').value = categoryId;
}
    </script>
</body>
</html>