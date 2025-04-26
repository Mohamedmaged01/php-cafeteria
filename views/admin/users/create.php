<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "DB_Project_Database";

$myconnection = mysqli_connect($servername, $username, $password, $dbname);
if ($myconnection) {
} else {
    die("myconnection failed: " . mysqli_connect_error());
}
require_once __DIR__ . '/auth.php';

checkAdminAuth();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Handle form data
    $name = $myconnection->real_escape_string($_POST['name']);
    $email = $myconnection->real_escape_string($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $room = $myconnection->real_escape_string($_POST['room']);
    $extension = $myconnection->real_escape_string($_POST['ext']);

    // Validate role
    $allowed_roles = ['user', 'admin'];
    $role = 'user';
    if (isset($_POST['role']) && in_array(strtolower($_POST['role']), $allowed_roles)) {
        $role = strtolower($_POST['role']);
    }

    // Handle image upload
    $picture = null;
    if ($_FILES['picture']['error'] == UPLOAD_ERR_OK) {
        $uploadBase = '/opt/lampp/htdocs/project/php-cafeteria/uploads/';
        $uploadDir = $uploadBase . 'profile_pics/';

        // Create directory if not exists
        if (!file_exists($uploadDir)) {
            if (!mkdir($uploadDir, 0777, true)) {
                $_SESSION['error'] = "Failed to create upload directory";
                header("Location: create.php");
                exit();
            }
            chmod($uploadDir, 0755);
        }

        // Check permissions
        if (!is_writable($uploadDir)) {
            $_SESSION['error'] = "Upload directory is not writable";
            header("Location: create.php");
            exit();
        }

        // Validate file extension
        $file_ext = strtolower(pathinfo($_FILES['picture']['name'], PATHINFO_EXTENSION));
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];

        if (!in_array($file_ext, $allowed_ext)) {
            $_SESSION['error'] = "File type is not allowed";
            header("Location: create.php");
            exit();
        }

        $filename = uniqid() . '.' . $file_ext;
        $destination = $uploadDir . $filename;

        if (move_uploaded_file($_FILES['picture']['tmp_name'], $destination)) {
            $picture = 'profile_pics/' . $filename;
        } else {
            $_SESSION['error'] = "File upload failed";
            header("Location: create.php");
            exit();
        }
    }

    // Insert into database
    try {
        $stmt = $myconnection->prepare("INSERT INTO users (name, email, password, room, ext, role, picture) VALUES (?, ?, ?, ?, ?, ?, ?)");
        if (!$stmt) {
            throw new Exception("Statement preparation failed: " . $myconnection->error);
        }

        $stmt->bind_param("sssssss", $name, $email, $password, $room, $extension, $role, $picture);

        if (!$stmt->execute()) {
            throw new Exception("Statement execution failed: " . $stmt->error);
        }

        $_SESSION['message'] = "User created successfully";
        header("Location: list.php");
        exit();
    } catch (Exception $e) {
        error_log("Database error: " . $e->getMessage());
        $_SESSION['error'] = "A database error occurred";
        header("Location: create.php");
        exit();
    }
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create User</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-dark text-white">
                        <h4 class="mb-0">Create New User</h4>
                    </div>
                    <div class="card-body">
                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger"><?= $_SESSION['error'] ?></div>
                            <?php unset($_SESSION['error']); ?>
                        <?php endif; ?>

                        <form method="POST" enctype="multipart/form-data">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Full Name</label>
                                    <input type="text" class="form-control" name="name" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control" name="email" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Password</label>
                                    <input type="password" class="form-control" name="password" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Role</label>
                                    <select class="form-select" name="role" required>
                                        <option value="user">User</option>
                                        <option value="admin">Admin</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Room Number</label>
                                    <input type="text" class="form-control" name="room">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Extension</label>
                                    <input type="text" class="form-control" name="ext">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Profile Picture</label>
                                    <input type="file" class="form-control" name="picture" accept="image/*">
                                </div>
                                <div class="col-12 mt-4">
                                    <button type="submit" class="btn btn-primary me-2">
                                        <i class="fas fa-save me-1"></i> Create User
                                    </button>
                                    <a href="list.php" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left me-1"></i> Back to List
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>