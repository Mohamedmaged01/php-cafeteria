<?php
include_once '../../../config/db.php';
session_start();


if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1;
    $_SESSION['user_name'] = "Aya";
    $_SESSION['user_image'] = "default-avatar.jpg";
    $_SESSION['role'] = "admin";
}

$per_page = 6;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page > 1) ? ($page * $per_page) - $per_page : 0;


$search = isset($_GET['search']) ? mysqli_real_escape_string($myconnection, $_GET['search']) : '';
$search_condition = $search ? "WHERE name LIKE '%$search%'" : "";


$total_query = mysqli_query($myconnection, "SELECT COUNT(*) as total FROM categories $search_condition");
$total = mysqli_fetch_assoc($total_query)['total'];
$pages = ceil($total / $per_page);


$categories = mysqli_query($myconnection, 
    "SELECT * FROM categories $search_condition ORDER BY name LIMIT $start, $per_page");

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
        .page-item.active .page-link {
            background-color: var(--coffee-color);
            border-color: var(--coffee-color);
        }
        .page-link {
            color: var(--coffee-color);
        }
        .search-box {
            max-width: 300px;
        }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
        <h2 class="mb-3 mb-md-0"><i class="fas fa-list me-2"></i>Categories</h2>
        
        <div class="d-flex gap-3">
    <form class="search-box" method="GET" action="" id="searchForm">
        <div class="input-group">
            <input type="text" class="form-control" name="search" id="searchInput" 
                   placeholder="Search categories..." value="<?= htmlspecialchars($search) ?>">
            <button class="btn btn-coffee" type="submit">
                <i class="fas fa-search"></i>
            </button>
            <?php if($search): ?>
            <a href="list.php" class="btn btn-outline-secondary">
                <i class="fas fa-times"></i>
            </a>
            <?php endif; ?>
        </div>
    </form>
    
    <a href="create.php" class="btn btn-coffee">
        <i class="fas fa-plus me-1"></i> Add New
    </a>
</div>
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
                
                <!-- Pagination -->
                <?php if($pages > 1): ?>
                <nav aria-label="Page navigation" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <?php if($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?= $page-1 ?>&search=<?= urlencode($search) ?>" aria-label="Previous">
                                    <span aria-hidden="true">&laquo;</span>
                                </a>
                            </li>
                        <?php endif; ?>
                        
                        <?php for($i = 1; $i <= $pages; $i++): ?>
                            <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                                <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                        
                        <?php if($page < $pages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?= $page+1 ?>&search=<?= urlencode($search) ?>" aria-label="Next">
                                    <span aria-hidden="true">&raquo;</span>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
                <?php endif; ?>
                
            <?php else: ?>
                <div class="alert alert-info">
                    <?php if($search): ?>
                        No categories found matching "<?= htmlspecialchars($search) ?>". 
                        <a href="list.php" class="alert-link">Show all categories</a>
                    <?php else: ?>
                        No categories found. <a href="create.php" class="alert-link">Create a new category</a>.
                    <?php endif; ?>
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
                <p class="text-danger">This action cannot be undone!</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <a href="#" class="btn btn-danger" id="confirmDelete">Delete</a>
            </div>
        </div>
    </div>
</div>


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

    document.addEventListener('DOMContentLoaded', function() {
  
    const searchInput = document.getElementById('searchInput');
    const searchForm = document.getElementById('searchForm');
    let searchTimer;
    
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => {
            searchForm.submit();
        }, 500);
    })});
  
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