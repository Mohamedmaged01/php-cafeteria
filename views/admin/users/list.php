<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "DB_Project_Database";

// Create myconnection
$myconnection = mysqli_connect($servername, $username, $password, $dbname);
// Check myconnection
if ($myconnection) {
} else {
    die("myconnection failed: " . mysqli_connect_error());
} 
require_once __DIR__ . '/auth.php';

// Admin authentication
if (!isset($_SESSION['user_id'])) {
    header('Location: ../../../../../Authentication/login.php');
    exit();
}

if ($_SESSION['user_role'] != 'admin') {
    header('Location: ../../../../../Authentication/login.php');
    exit();
}

// Search functionality
$search = '';
$users = [];
$query = "SELECT id, name, email, room, ext, role, picture, created_at FROM users";

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = trim($myconnection->real_escape_string($_GET['search']));
    $query .= " WHERE name LIKE '%$search%' OR email LIKE '%$search%'";
}

$query .= " ORDER BY created_at DESC";
$result = $myconnection->query($query);

if ($result) {
    $users = $result->fetch_all(MYSQLI_ASSOC);
    $result->free();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="./style.css">
    <style>
        .search-container {
            margin-bottom: 20px;
        }
        .profile-img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="../../dashboard.php">Cafeteria Admin</a>
            <div class="ms-auto d-flex align-items-center">
                <span class="navbar-text me-3">Welcome, <?= htmlspecialchars($_SESSION['name'] ?? 'Admin') ?></span>
                <a href="../../logout.php" class="btn btn-outline-light">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="text-center">User Management</h2>
            <a href="create.php" class="btn btn-coffee">
                <i class="fas fa-plus me-1"></i> Add New User
            </a>
        </div>
        
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-info alert-dismissible fade show">
                <?= $_SESSION['message'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>
        
        <!-- Search Form -->
        <div class="search-container mb-3">
            <form method="get" action="" class="row g-2">
                <div class="col-md-8">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" 
                               placeholder="Search by name or email" value="<?= htmlspecialchars($search) ?>">
                        <button class="btn btn-coffee" type="submit">
                            <i class="fas fa-search"></i> Search
                        </button>
                    </div>
                </div>
                <div class="col-md-4">
                    <?php if (!empty($search)): ?>
                        <a href="list.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Clear Search
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
        
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Photo</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Room</th>
                        <th>Ext</th>
                        <th>Role</th>
                        <th>Registered</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="9" class="text-center">No users found</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?= $user['id'] ?></td>
                            <td>
                                <img src="<?= !empty($user['picture']) ? '../../uploads/'.$user['picture'] : '../../assets/images/default-user.png' ?>" 
                                     class="profile-img" alt="User Photo">
                            </td>
                            <td><?= htmlspecialchars($user['name']) ?></td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td><?= htmlspecialchars($user['room'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($user['ext'] ?? '-') ?></td>
                            <td>
                                <span class="badge bg-<?= $user['role'] == 'admin' ? 'dark' : 'secondary' ?>">
                                    <?= ucfirst($user['role']) ?>
                                </span>
                            </td>
                            <td><?= date('M d, Y', strtotime($user['created_at'])) ?></td>
                            <td>
                                <div class="btn-group">
                                    <a href="edit.php?id=<?= $user['id'] ?>" class="btn btn-sm btn-coffee">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="delete.php?id=<?= $user['id'] ?>" 
                                       class="btn btn-sm btn-danger"
                                       onclick="return confirm('Are you sure?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>