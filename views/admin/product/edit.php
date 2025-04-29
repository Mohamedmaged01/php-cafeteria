<?php
$servername = "localhost";
$username = "root"; 
$password = ""; 
$dbname = "PHP_Project"; 

$conn = mysqli_connect($servername, $username, $password, $dbname);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: list.php");
    exit;
}

session_start();
$user_image = $_SESSION['user_image'] ?? 'default.png';
$username = $_SESSION['user_name'] ?? 'Admin';

$id = mysqli_real_escape_string($conn, $_GET['id']);

$name = '';
$price = '';
$current_image = '';
$availability = 0;
$error = '';

$sql = "SELECT id, name, price, image, available FROM products WHERE id=$id";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) == 0) {
    header("Location: list.php");
    exit;
}

$product = mysqli_fetch_assoc($result);
$name = $product['name'];
$price = $product['price'];
$current_image = $product['image'];
$availability = $product['available'];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_product'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $price = mysqli_real_escape_string($conn, $_POST['price']);
    $availability = isset($_POST['availability']) ? 1 : 0;
    
    if (empty($name)) {
        $error = "Product name is required.";
    } elseif (empty($price) || !is_numeric($price)) {
        $error = "Valid price is required.";
    } else {
        $new_image = $current_image; 
        
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $target_dir = "../../../public/uploads/products/";
            
         
            if (!empty($current_image) && file_exists($target_dir . $current_image)) {
                unlink($target_dir . $current_image);
            }
            
            // Upload new image
            $new_image = time() . '_' . basename($_FILES["image"]["name"]);
            $target_file = $target_dir . $new_image;
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
            
            $check = getimagesize($_FILES["image"]["tmp_name"]);
            if ($check === false) {
                $error = "File is not an image.";
            } elseif ($_FILES["image"]["size"] > 2000000) {
                $error = "File is too large (max 2MB).";
            } elseif (!in_array($imageFileType, ["jpg", "jpeg", "png", "gif"])) {
                $error = "Only JPG, JPEG, PNG, and GIF files are allowed.";
            } else {
                if (!file_exists($target_dir)) {
                    mkdir($target_dir, 0777, true);
                }
                if (!move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                    $error = "Error uploading file.";
                }
            }
        }
        
        if (empty($error)) {
            $sql_update = "UPDATE products SET name='$name', price='$price', available='$availability', image='$new_image' WHERE id=$id";
            
            if (mysqli_query($conn, $sql_update)) {
                header("Location: list.php");
                exit;
            } else {
                $error = "Error updating record: " . mysqli_error($conn);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Product</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .navbar-custom { background-color: #6F4E37; height: 80px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        .btn-coffee { background-color: #6F4E37; color: white; }
        .btn-coffee:hover { background-color: #5a3c2a; color: white; }
        .form-container { max-width: 700px; margin: 0 auto; }
        .current-image { max-width: 200px; max-height: 200px; border-radius: 4px; margin-bottom: 10px; }
        .preview-image { max-width: 200px; max-height: 200px; border-radius: 4px; margin-top: 10px; display: none; }
        .image-container { display: flex; gap: 20px; margin-bottom: 20px; }
        .image-section { margin-bottom: 20px; }
    </style>
</head>
<body>
<?php include('../navbar.php');
 ?>


    <div class="container mt-4">
        <h1>Edit Product</h1>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <form method="post" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="form-label">Product Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($name); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Price (EGP) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="0" name="price" class="form-control" value="<?php echo htmlspecialchars($price); ?>" required>
                    </div>

                    <div class="image-section">
                        <label class="form-label">Product Image</label>
                        <div class="image-container">
                            <div>
                                <p class="mb-2">Current Image:</p>
                                <?php if (!empty($current_image) && file_exists("../../../public/uploads/products/" . $current_image)): ?>
                                    <img src="../../../public/uploads/products/<?php echo htmlspecialchars($current_image); ?>" class="current-image">
                                <?php else: ?>
                                    <div class="current-image bg-light d-flex align-items-center justify-content-center">
                                        <span>No Image Available</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div>
                                <p class="mb-2">New Image Preview:</p>
                                <img id="imagePreview" class="preview-image" src="#" alt="Image Preview">
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Change Image</label>
                        <input type="file" name="image" class="form-control" accept="image/*" onchange="previewImage(this)">
                        <small class="text-muted">Leave empty to keep current image. Max size: 2MB (JPG, JPEG, PNG, GIF)</small>
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