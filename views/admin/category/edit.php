<?php
include_once '../../../config/db.php';
session_start();

if (($_SESSION['role'] ?? 'customer') !== 'admin') {
    header("Location: /php-cafeteria/views/user/order/index.php");
    exit;
}

if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1;
    $_SESSION['user_name'] = "Aya";
    $_SESSION['role'] = "admin"; 
}

$category_id = intval($_GET['id'] ?? 0);
$category = mysqli_fetch_assoc(mysqli_query($myconnection, "SELECT * FROM categories WHERE id = $category_id"));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = mysqli_real_escape_string($myconnection, $_POST['name'] ?? '');

    if (!empty($name)) {
       
        $check_query = mysqli_query($myconnection, "SELECT * FROM categories WHERE name = '$name' AND id != $category_id");

        if (mysqli_num_rows($check_query) > 0) {
            $error_message = "This category name already exists.";
        } else {
            mysqli_query($myconnection, "UPDATE categories SET name = '$name' WHERE id = $category_id");
            $_SESSION['success_message'] = "Category updated successfully.";
            header("Location: list.php");
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Category - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --coffee-color: #6F4E37;
            --light-coffee: #C4A484;
        }
        .btn-coffee {
            background-color: var(--coffee-color);
            color: white;
        }
        .btn-coffee:hover {
            background-color: var(--light-coffee);
            color: white;
        }

        .main-content {
            margin-left: 250px;
            padding: 20px;
        }
        .table-responsive {
            overflow-x: auto;
        }
    </style>
</head>
<body>
    <div class="d-flex">
        

        <!-- Main Content -->
        <div class="main-content w-75">
            <div class="container-fluid">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fas fa-edit me-2"></i>Edit Category</h2>
                    <a href="list.php" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back to List
                    </a>
                </div>

                <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?= $error_message ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <div class="card shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Category Details</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" class="needs-validation" novalidate>
                            <div class="mb-3">
                                <label for="name" class="form-label">Category Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="name" name="name" 
                                       value="<?= htmlspecialchars($category['name'] ?? '') ?>" required>
                                <div class="invalid-feedback">
                                    Please provide a category name.
                                </div>
                            </div>
                            <button type="submit" class="btn btn-coffee">
                                <i class="fas fa-save me-1"></i> Update Category
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
      
        (() => {
            'use strict';
            const forms = document.querySelectorAll('.needs-validation');
            Array.from(forms).forEach(form => {
                form.addEventListener('submit', event => {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        })();
    </script>
</body>
</html>
