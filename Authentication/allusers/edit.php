<?php
session_start();
require_once __DIR__ . '/../connect.php';
require_once __DIR__ . '/auth.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

checkAdminAuth();

$user = null;
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $myconnection->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    if (!$user) {
        $_SESSION['error'] = "User not found";
        header("Location: list.php");
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = intval($_POST['id']);
    $name = $myconnection->real_escape_string($_POST['name']);
    $email = $myconnection->real_escape_string($_POST['email']);
    $room = $myconnection->real_escape_string($_POST['room']);
    $ext = $myconnection->real_escape_string($_POST['ext']);
    $role = strtolower($myconnection->real_escape_string($_POST['role']));

    $picture = $user['picture'] ?? null;

    if ($_FILES['picture']['error'] == UPLOAD_ERR_OK) {
        $basePath = '/opt/lampp/htdocs/project/php-cafeteria/Authentication/';
        $uploadDir = $basePath . 'uploads/profile_pics/';

        // Check base folder permissions
        if (!is_writable($basePath)) {
            $_SESSION['error'] = "Base directory is not writable. Please run:<br>
            <code>sudo chown -R www-data:www-data $basePath</code><br>
            <code>sudo chmod -R 755 $basePath</code>";
            header("Location: edit.php?id=" . $id);
            exit();
        }

        // Create upload directory if it doesn't exist
        if (!file_exists($uploadDir)) {
            if (!mkdir($uploadDir, 0755, true)) {
                $_SESSION['error'] = "Failed to create upload directory. Please run:<br>
                <code>sudo mkdir -p $uploadDir</code><br>
                <code>sudo chmod 755 $uploadDir</code>";
                header("Location: edit.php?id=" . $id);
                exit();
            }
            // Protect the directory with htaccess
            file_put_contents($uploadDir . ".htaccess", "Deny from all");
        }

        // Check write permissions
        if (!is_writable($uploadDir)) {
            $_SESSION['error'] = "Upload directory is not writable. Please run:<br>
            <code>sudo chmod 755 $uploadDir</code>";
            header("Location: edit.php?id=" . $id);
            exit();
        }

        // Delete old picture if exists
        if (!empty($user['picture']) && file_exists($uploadDir . $user['picture'])) {
            unlink($uploadDir . $user['picture']);
        }

        // Validate file type
        $allowedTypes = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $_FILES['picture']['tmp_name']);
        finfo_close($finfo);

        if (!array_key_exists($mime, $allowedTypes)) {
            $_SESSION['error'] = "Invalid file type. Only JPG, PNG, and GIF are allowed.";
            header("Location: edit.php?id=" . $id);
            exit();
        }

        // Generate unique file name
        $filename = uniqid() . '.' . $allowedTypes[$mime];
        $targetPath = $uploadDir . $filename;

        // Move uploaded file
        if (!move_uploaded_file($_FILES['picture']['tmp_name'], $targetPath)) {
            $_SESSION['error'] = "Failed to upload file. Please try again.";
            header("Location: edit.php?id=" . $id);
            exit();
        }

        $picture = $filename;
    }

    // Validate role
    if (!in_array($role, ['user', 'admin'])) {
        $_SESSION['error'] = "Invalid role. Role must be 'user' or 'admin'.";
        header("Location: edit.php?id=" . $id);
        exit();
    }

    // Update user data
    try {
        $stmt = $myconnection->prepare("UPDATE users SET name=?, email=?, room=?, ext=?, role=?, picture=? WHERE id=?");
        if (!$stmt) {
            throw new Exception("Failed to prepare statement: " . $myconnection->error);
        }

        $stmt->bind_param("ssssssi", $name, $email, $room, $ext, $role, $picture, $id);

        if ($stmt->execute()) {
            $_SESSION['message'] = "User data updated successfully";
            header("Location: list.php");
            exit();
        } else {
            throw new Exception("Failed to execute statement: " . $stmt->error);
        }
    } catch (Exception $e) {
        error_log("Database error: " . $e->getMessage());
        $_SESSION['error'] = "Error updating data: " . $e->getMessage();
        header("Location: edit.php?id=" . $id);
        exit();
    }
}
?>


<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            font-family: 'Tajawal', sans-serif;
        }
        .profile-img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 50%;
            border: 3px solid #6F4E37;
        }
        .card {
            border-radius: 15px;
            box-shadow: 0 4px 15px hsla(28, 100.00%, 12.00%, 0.86);
        }
        .btn-coffee {
            background-color: #6F4E37;
            color: white;
            font-weight: bold;
        }
        .btn-coffee:hover {
            background-color: #5a3c2a;
            color: white;
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand" href="../../dashboard.php">Cafeteria System</a>
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
                <div class="card-header bg-coffee text-white">
                    <h4 class="mb-0">Edit User</h4>
                </div>
                <div class="card-body">
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <?= $_SESSION['error'] ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <?php unset($_SESSION['error']); ?>
                    <?php endif; ?>

                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="id" value="<?= $user['id'] ?>">

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Full Name</label>
                                <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Room Number</label>
                                <input type="text" class="form-control" name="room" value="<?= htmlspecialchars($user['room'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Extension</label>
                                <input type="text" class="form-control" name="ext" value="<?= htmlspecialchars($user['ext'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Role</label>
                                <select class="form-select" name="role" required>
                                    <option value="user" <?= $user['role'] == 'user' ? 'selected' : '' ?>>User</option>
                                    <option value="admin" <?= $user['role'] == 'admin' ? 'selected' : '' ?>>Admin</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Profile Picture</label>
                                <input type="file" class="form-control" name="picture" accept="image/*">
                                <?php if ($user['picture'] && file_exists('/opt/lampp/htdocs/project/uploads/' . $user['picture'])): ?>
                                    <div class="mt-3 text-center">
                                        <img src="../../uploads/<?= $user['picture'] ?>" class="profile-img mb-2">
                                        <p class="text-muted">Current Image</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="col-12 mt-4">
                                <button type="submit" class="btn btn-coffee me-2">
                                    <i class="fas fa-save me-1"></i> Save Changes
                                </button>
                                <a href="list.php" class="btn btn-secondary">
                                    <i class="fas fa-arrow-right me-1"></i> Back to List
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                alert.classList.add('fade');
                setTimeout(() => alert.remove(), 300);
            });
        }, 5000);
    });
</script>
</body>
</html>
