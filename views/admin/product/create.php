<?php
$servername = "localhost";
$username = "root"; 
$password = ""; 
$dbname = "PHP_Project"; 

$conn = mysqli_connect($servername, $username, $password, $dbname);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}



$name = '';
$price = '';
$category_id = '';
$available = 1;
$error = '';


$categories = [];
$categorySql = "SELECT id, name FROM categories ORDER BY name ASC";
$categoryResult = mysqli_query($conn, $categorySql);

if ($categoryResult) {
    while ($row = mysqli_fetch_assoc($categoryResult)) {
        $categories[] = $row;
    }
} else {
    $error = "Error fetching categories: " . mysqli_error($conn);
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_product'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $price = mysqli_real_escape_string($conn, $_POST['price']);
    $category_id = mysqli_real_escape_string($conn, $_POST['category_id']);
    $available = isset($_POST['available']) ? 1 : 0;
    
    if (empty($name)) {
        $error = "Product name is required";
    } elseif (empty($price) || !is_numeric($price)) {
        $error = "Valid price is required";
    } elseif (empty($category_id)) {
        $error = "Please select a category";
    } else {
        $image = ""; 
        
       
        if(isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $target_dir = "../../../public/uploads/products/";
            $image = time() . '_' . basename($_FILES["image"]["name"]); 
            $target_file = $target_dir . $image;
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
                } else {
                    $error = "Sorry, there was an error uploading your file";
                }
            }
        }
        
        if (empty($error)) {
            $sql = "INSERT INTO products (name, price, image, category_id, available) VALUES ('$name', '$price', '$image', '$category_id', '$available')";
            
            if (mysqli_query($conn, $sql)) {
                header("Location: list.php?created=success");
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
    <title>Add New Product</title>
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
        h1, h2, h3 {
            color: #5a3c2a;
        }
        .form-container {
            max-width: 700px;
            margin: 0 auto;
        }
        .custom-file-input::-webkit-file-upload-button {
            visibility: hidden;
        }
        .custom-file-label {
            border: 1px solid #ced4da;
            border-radius: 4px;
            padding: 8px 12px;
            cursor: pointer;
            background-color: #f8f9fa;
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
<?php include('../navbar.php');
 ?>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Add New Product</h1>
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
                            <label for="category_id" class="form-label">Category <span class="text-danger">*</span></label>
                            <select class="form-select" id="category_id" name="category_id" required>
                                <option value="">Select a category</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>" <?php echo ($category_id == $category['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="image" class="form-label">Product Image</label>
                            <input type="file" class="form-control" id="image" name="image" accept="image/*" onchange="previewImage(this)">
                            <small class="form-text text-muted">Supported formats: JPG, JPEG, PNG, GIF (Max size: 2MB)</small>
                            <img id="imagePreview" class="preview-image mt-2" src="#" alt="Image Preview">
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="available" name="available" <?php echo ($available == 1) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="available">Available</label>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="list.php" class="btn btn-secondary">Cancel</a>
                            <button type="submit" name="add_product" class="btn btn-coffee">Add Product</button>
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
mysqli_close($conn);
?>