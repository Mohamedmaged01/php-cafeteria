<?php
include_once '../../config/db.php';
session_start();


if (!isset($_SESSION['user_id'])) {
    header("Location: ../../Authentication/login.php");
    exit;
}


$per_page = 6;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page > 1) ? ($page * $per_page) - $per_page : 0;


$total = mysqli_query($myconnection, "SELECT COUNT(*) as total FROM products WHERE available = 1");
$total = mysqli_fetch_assoc($total)['total'];
$pages = ceil($total / $per_page);


$products = mysqli_query($myconnection, 
    "SELECT * FROM products WHERE available = 1 LIMIT $start, $per_page");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Our Cafeteria</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #6F4E37;
            --secondary-color: #C4A484;
            --accent-color: #3E2723;
            --light-coffee: #F5F0E6;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--light-coffee);
        }
        
        
        .hero-section {
            background: linear-gradient(rgba(0, 0, 0, 0.5), url('https://t4.ftcdn.net/jpg/09/18/25/37/360_F_918253796_lcXm9jXRawgMoz228HlYrfSVPzpCYohe.jpg')) !important;
            background-size: cover !important;
            background-position: center !important;
            color: white;
            padding: 120px 0;
            position: relative;
            display: flex;
            justify-content: center;
            align-items: center;
            text-align: center;
        }
        
        .hero-text {
            color: var(--primary-color);
            text-shadow: 1px 1px 3px rgba(255, 255, 255, 0.8);
        }
        
        .hero-section .lead {
            color: var(--primary-color);
            text-shadow: 1px 1px 2px rgba(255, 255, 255, 0.8);
            font-weight: 500;
        }

        .hero-section h1 {
            font-size: 3rem;
            font-weight: bold;
        }

        .hero-section .lead {
            font-size: 1.25rem;
        }

        .navbar-custom {
    background-color: var(--primary-color);
    padding: 1.5rem 0; 
    height: 80px; 
    box-shadow: 0 4px 12px rgba(0,0,0,0.15); 
    position: relative;
    z-index: 1000;
}

.navbar-brand {
    font-size: 1.8rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    color: white !important;
    transition: all 0.3s ease;
}

.navbar-brand i {
    font-size: 2rem;
    margin-right: 12px;
    color: var(--secondary-color);
}

.navbar-brand:hover {
    transform: translateY(-2px);
}

.nav-link {
    font-size: 1.1rem;
    font-weight: 500;
    padding: 0.8rem 1.5rem !important;
    margin: 0 0.5rem;
    color: white !important;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.nav-link:hover {
    background-color: rgba(255,255,255,0.15);
    transform: translateY(-2px);
    color: var(--secondary-color) !important;
}

.nav-link.active {
    background-color: var(--secondary-color);
    color: var(--accent-color) !important;
}
        .btn-login {
            background-color: var(--secondary-color);
            color: var(--accent-color);
            border: none;
        }
        
        .btn-login:hover {
            background-color: var(--accent-color);
            color: white;
        }
        
        .btn-coffee {
            background-color: var(--primary-color);
            color: white;
        }
        
        .btn-coffee-outline {
            border: 2px solid var(--primary-color);
            color: var(--primary-color);
            background-color: transparent;
        }
        
        .btn-coffee-outline:hover {
            background-color: var(--primary-color);
            color: white;
        }
        
        .product-card {
            border: none;
            border-radius: 15px;
            overflow: hidden;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            cursor: pointer;
            margin-bottom: 20px;
            height: 100%;
        }
        
        .product-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.2);
            animation: pulse 1.5s infinite;
        }
        
        @keyframes pulse {
            0% { transform: translateY(-10px) scale(1); }
            50% { transform: translateY(-10px) scale(1.03); }
            100% { transform: translateY(-10px) scale(1); }
        }
        
        .product-img {
            height: 200px;
            object-fit: cover;
            transition: transform 0.5s;
        }
        
        .product-card:hover .product-img {
            transform: scale(1.1);
        }
        
        .feature-card {
            border: none;
            border-radius: 15px;
            overflow: hidden;
            transition: all 0.3s;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            background-color: white;
        }
        
        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
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
   
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom">
        <div class="container">
            <a class="navbar-brand" href="#">
                Café Delight
            </a>
            <div class="ml-auto">
                <a href="./../user/auth/login.php" class="btn btn-login">
                    Login
                </a>
            </div>
        </div>
    </nav>

    
    <section class="hero-section">
        <div class="container">
            <h1 class="display-3 fw-bold mb-4 hero-text">Welcome to Our Cafeteria</h1>
            <p class="lead mb-5">Discover the finest coffee and delicious treats in town</p>
            <div class="d-flex justify-content-center gap-3">
                <a href="#products" class="btn btn-coffee btn-lg px-4">
                    Explore Menu
                </a>
                <a href="./../user/auth/login.php" class="btn btn-coffee-outline btn-lg px-4">
                     Sign In
                </a>
            </div>
        </div>
    </section>

   
    <section class="py-5">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-3">
                    <div class="feature-card text-center p-4 h-100">
                       
                        <h4>Supreme Quality</h4>
                        <p class="text-muted">Premium ingredients for perfect taste</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="feature-card text-center p-4 h-100">
                       
                        <h4>Fast Service</h4>
                        <p class="text-muted">Quick preparation and delivery</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="feature-card text-center p-4 h-100">
                       
                        <h4>Made with Love</h4>
                        <p class="text-muted">Every cup prepared with care</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="feature-card text-center p-4 h-100">
                       
                        <h4>Affordable</h4>
                        <p class="text-muted">Great quality at reasonable prices</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="products" class="py-5 bg-white">
        <div class="container">
            <h2 class="text-center mb-5 fw-bold" style="color: var(--primary-color);">Our Menu</h2>
            
            <div class="row g-4">
                <?php while($product = mysqli_fetch_assoc($products)): ?>
                    <div class="col-lg-4 col-md-6">
                    <div class="product-card" onclick="window.location='../../views/user/auth/login.php?redirect=product&id=<?= $product['id'] ?>'">
                            <img src="/php-cafeteria/public/uploads/<?= $product['image'] ?>" 
                                 class="product-img w-100" 
                                 alt="<?= $product['name'] ?>">
                            <div class="p-3">
                                <h5 class="fw-bold"><?= $product['name'] ?></h5>
                                <p class="text-muted mb-2"><?= substr($product['description'] ?? 'Delicious item', 0, 60) ?>...</p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fw-bold fs-5" style="color: var(--primary-color);">
                                        <?= $product['price'] ?> LE
                                    </span>
                                    <button class="btn btn-sm" style="background-color: var(--secondary-color);" onclick="event.stopPropagation(); window.location='login.php'">
                                        Order Now
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
            
            
            <?php if($pages > 1): ?>
                <nav class="mt-5">
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
    </section>

  
    <footer class="py-4" style="background-color: var(--primary-color); color: white;">
        <div class="container text-center">
            <p class="mb-0">&copy; <?= date('Y') ?> Café Delight. All rights reserved.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>