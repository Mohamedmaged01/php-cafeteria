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

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = mysqli_real_escape_string($myconnection, $_POST['name'] ?? '');
    
    if (!empty($name)) {
        try {
         
            $check_query = "SELECT id FROM categories WHERE name = '$name'";
            $result = mysqli_query($myconnection, $check_query);
            
            if (mysqli_num_rows($result) > 0) {
                $error = "Category name already exists!";
            } else {
                $insert_query = "INSERT INTO categories (name) VALUES ('$name')";
                if (mysqli_query($myconnection, $insert_query)) {
                    $_SESSION['success_message'] = "Category added successfully!";
                    header("Location: list.php");
                    exit;
                } else {
                    throw new Exception("Database error: " . mysqli_error($myconnection));
                }
            }
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() == 1062) { 
                $error = "Category name already exists!";
            } else {
                $error = "An error occurred: " . $e->getMessage();
            }
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    } else {
        $error = "Category name is required!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Category - Admin Panel</title>
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
            width: calc(100% - 250px);
        }
    </style>
</head>
<body>
    <div class="d-flex">
   
        <div class="main-content">
            <div class="container-fluid">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fas fa-plus me-2"></i>Add New Category</h2>
                    <a href="list.php" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back to List
                    </a>
                </div>
              
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?= htmlspecialchars($error) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($success)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?= htmlspecialchars($success) ?>
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
                                <input type="text" class="form-control <?= !empty($error) ? 'is-invalid' : '' ?>" 
                                       id="name" name="name" required
                                       value="<?= isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '' ?>">
                                <div class="invalid-feedback">
                                    <?= !empty($error) ? $error : 'Please provide a category name.' ?>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-coffee">
                                <i class="fas fa-save me-1"></i> Save Category
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
   
        (function () {
            'use strict'
            
            var forms = document.querySelectorAll('.needs-validation')
            
          
            Array.prototype.slice.call(forms)
                .forEach(function (form) {
                    form.addEventListener('submit', function (event) {
                        if (!form.checkValidity()) {
                            event.preventDefault()
                            event.stopPropagation()
                        }
                        
                        form.classList.add('was-validated')
                    }, false)
                })
        })()
    </script>
</body>
</html>