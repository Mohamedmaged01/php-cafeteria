<?php
$servername = "localhost";
$username = "root"; 
$password = ""; 
$dbname = "php_project"; 

$conn = mysqli_connect($servername, $username, $password, $dbname);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: list.php");
    exit;
}

$id = mysqli_real_escape_string($conn, $_GET['id']);

$name = '';
$price = '';
$current_image = '';
$availability = '';
$error = '';

$sql = "SELECT * FROM products WHERE id=$id";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) == 0) {
    header("Location: list.php");
    exit;
}

$product = mysqli_fetch_assoc($result);
$name = $product['name'];
$price = $product['price'];
$current_image = $product['image'];
$availability = $product['availability'];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_product'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $price = mysqli_real_escape_string($conn, $_POST['price']);
    $availability = isset($_POST['availability']) ? 1 : 0;
    
    
    if (empty($name)) {
        $error = "Product name is required";
    } elseif (empty($price) || !is_numeric($price)) {
        $error = "Valid price is required";
    } else {
        $image_sql = ""; 
        
        if(isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $target_dir = "uploads/";
            $new_image = time() . '_' . basename($_FILES["image"]["name"]); // Add timestamp to avoid duplicate names
            $target_file = $target_dir . $new_image;
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
            
            $check = getimagesize($_FILES["image"]["tmp_name"]);
            if($check === false) {
                $error = "File is not an image";
            }
            elseif ($_FILES["image"]["size"] > 2000000) {
                $error = "Sorry, your file is too large (max 2MB)";
            }
            elseif($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
                $error = "Sorry, only JPG, JPEG, PNG & GIF files are allowed";
            }
            else {
                if (!file_exists($target_dir)) {
                    mkdir($target_dir, 0777, true);
                }
                
                if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                    $image_sql = ", image='$new_image'";
                                        if (!empty($current_image) && file_exists($target_dir . $current_image)) {
                        unlink($target_dir . $current_image);
                    }
                } else {
                    $error = "Sorry, there was an error uploading your file";
                }
            }
        }
        
        if (empty($error)) {
            $sql = "UPDATE products SET name='$name', price='$price', availability='$availability' $image_sql WHERE id=$id";
            
            if (mysqli_query($conn, $sql)) {
                header("Location: list.php?updated=success");
                exit;
            } else {
                $error = "Error: " . mysqli_error($conn);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product</title>
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
            margin-topmargin-top: 10px;
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
        h1, h2, h3 {
            color: #5a3c2a;
        }
        .form-container {
            max-width: 700px;
            margin: 0 auto;
        }
        .current-image {
            max-width: 100px;
            max-height: 100px;
            border-radius: 4px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
        }
        .preview-image {
            max-width: 100px;
            max-height: 100px;
            margin-top: 10px;
            border-radius: 4px;
            display: none;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom">
        <div class="container">
            <a class="navbar-brand" href="#">
               Coffee Shop Admin
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="list_products.php">Products</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Users</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Manual Order</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Checks</a>
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
            <h1>Edit Product</h1>
        </div>

        <div class="form-container">
            <?php if(!empty($error)): ?>
                <div class="alert alert-danger">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-body">
                    <form method="post" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="name" class="form-label">Product Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="price" class="form-label">Price (EGP) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="0" class="form-control" id="price" name="price" value="<?php echo htmlspecialchars($price); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Current Image</label>
                            <div>
                                <?php if(!empty($current_image) && file_exists("uploads/" . $current_image)): ?>
                                    <img src="uploads/<?php echo htmlspecialchars($current_image); ?>" alt="Current Image" class="current-image">
                                <?php else: ?>
                                    <div class="no-image bg-light text-center" style="width:100px;height:100px;line-height:100px;border-radius:4px;">No Image</div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="image" class="form-label">Change Image</label>
                            <input type="file" class="form-control" id="image" name="image" accept="image/*" onchange="previewImage(this)">
                            <small class="form-text text-muted">Leave empty to keep current image. Supported formats: JPG, JPEG, PNG, GIF (Max size: 2MB)</small>
                            <img id="imagePreview" class="preview-image mt-2" src="#" alt="Image Preview">
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="availability" name="availability" <?php echo ($availability == 1) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="availability">Available</label>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="list.php" class="btn btn-secondary">Cancel</a>
                            <button type="submit" name="update_product" class="btn btn-coffee">Update Product</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function previewImage(input) {
            var preview = document.getElementById('imagePreview');
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                };
                reader.readAsDataURL(input.files[0]);
            } else {
                preview.style.display = 'none';
            }
        }
    </script>
</body>
</html>
<?php
// Close connection
mysqli_close($conn);
?>