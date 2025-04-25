<?php
include_once '../../../config/db.php';
session_start();


if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1;
    $_SESSION['user_name'] = "Aya";
    $_SESSION['user_image'] = "default-avatar.jpg";
    $_SESSION['role'] = "admin";
}


$categories = mysqli_query($myconnection, "SELECT * FROM categories ORDER BY name");
if (!$categories) {
    die("Error in categories query: " . mysqli_error($myconnection));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categories List - Admin Panel</title>
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
        body {
            padding-top: 70px;
        }
        .card {
            border: none;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        .card-header {
            background-color: white;
            border-bottom: 1px solid rgba(0,0,0,0.1);
        }
        #deleteModal .modal-header {
            background-color: var(--coffee-color);
            color: white;
        }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-list me-2"></i>Categories</h2>
        <a href="create.php" class="btn btn-coffee">
            <i class="fas fa-plus me-1"></i> Add New
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-header">
            <h5 class="mb-0">All Categories</h5>
        </div>
        <div class="card-body">
            <?php if (mysqli_num_rows($categories) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover table-striped">
                        <thead class="table-light">
                            <tr>
                                <th>Name</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($category = mysqli_fetch_assoc($categories)): ?>
                            <tr>
                                <td><?= htmlspecialchars($category['name']) ?></td>
                                <td>
                                    <a href="edit.php?id=<?= $category['id'] ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <button class="btn btn-sm btn-outline-danger delete-btn" 
                                            data-id="<?= $category['id'] ?>" 
                                            data-name="<?= htmlspecialchars($category['name']) ?>">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    No categories found. <a href="create.php" class="alert-link">Create a new category</a>.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirm Deletion</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete the category "<span id="categoryName"></span>"?</p>
               
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <a href="#" class="btn btn-danger" id="confirmDelete">Delete</a>
            </div>
        </div>
    </div>
</div>

<!-- Toast Notification -->
<?php if (isset($_SESSION['toast'])): ?>
<div class="position-fixed top-0 end-0 p-3" style="z-index: 11">
    <div class="toast show" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header bg-<?= $_SESSION['toast']['type'] ?> text-white">
            <strong class="me-auto"><?= ucfirst($_SESSION['toast']['type']) ?></strong>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body">
            <?= $_SESSION['toast']['message'] ?>
        </div>
    </div>
</div>
<?php 
unset($_SESSION['toast']);
endif; 
?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Delete Modal Handling
    const deleteButtons = document.querySelectorAll('.delete-btn');
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    const categoryNameSpan = document.getElementById('categoryName');
    const confirmDeleteBtn = document.getElementById('confirmDelete');
    
    deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
            const categoryId = this.getAttribute('data-id');
            const categoryName = this.getAttribute('data-name');
            
            categoryNameSpan.textContent = categoryName;
            confirmDeleteBtn.href = `delete.php?id=${categoryId}`;
            
            deleteModal.show();
        });
    });
    
    // Auto-hide toast after 5 seconds
    const toast = document.querySelector('.toast');
    if (toast) {
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, 5000);
    }
});
</script>
</body>
</html>