<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include_once 'connect.php';
$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $myconnection->real_escape_string($_POST['email']);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address';
    } else {
        $stmt = $myconnection->prepare("SELECT id, email FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();
        if ($user) {
            $reset_token = bin2hex(random_bytes(32));
            $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
            $_SESSION['reset_token'] = $reset_token;
            $_SESSION['reset_token_expiry'] = $expiry;
            $_SESSION['reset_user_id'] = $user['id'];
            $reset_link = "reset_password.php?token=$reset_token";
            header("Location: $reset_link");
            exit();
        } else {
            $error = 'Email not found in our system';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Cafeteria System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #6F4E37; 
            --secondary-color: #5a3c2a; 
            --accent-color: #C4A484; 
            --light-color: #f8f9fa;
            --dark-color: #212529;
        }
        
        body {
            background: linear-gradient(135deg, #C4A484 0%, #6F4E37 100%);
            height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .forgot-container {
            max-width: 500px;
            border-radius: 20px;
            box-shadow: 0 15px 30px hsla(42, 97.00%, 13.10%, 0.79);
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            transition: all 0.3s ease;
            overflow: hidden;
            padding: 30px;
        }
        
        .forgot-container:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(90, 42, 3, 0.64);
        }
        
        .logo-img {
            width: 80px;
            height: 80px;
            object-fit: contain;
            margin-bottom: 1rem;
            filter: drop-shadow(0 4px 6px rgba(0, 0, 0, 0.1));
        }
        
        .form-title {
            color: var(--primary-color);
            font-weight: 700;
            text-shadow: 0 2px 4px hsl(36, 93.20%, 17.30%);
        }
        
        .form-subtitle {
            color: #6c757d;
            font-weight: 400;
        }
        
        .input-group-text {
            background-color: var(--primary-color);
            color: white;
            border: none;
        }
        
        .form-control {
            border-left: none;
            padding-left: 0;
            background-color: rgba(248, 249, 250, 0.8);
        }
        
        .form-control:focus {
            box-shadow: 0 0 0 0.25rem rgba(111, 78, 55, 0.25);
            border-color: var(--primary-color);
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border: none;
            padding: 12px;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 15px rgba(111, 78, 55, 0.3);
            background-color: var(--secondary-color);
        }
        
        .alert {
            border-radius: 10px;
        }
        
        .alert a {
            color: inherit;
            text-decoration: underline;
        }
        
        .wave-decoration {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            overflow: hidden;
            line-height: 0;
            z-index: -1;
        }
        
        .wave-decoration svg {
            position: relative;
            display: block;
            width: calc(100% + 1.3px);
            height: 150px;
        }
        
        .wave-decoration .shape-fill {
            fill: rgba(255, 255, 255, 0.15);
        }
    </style>
</head>
<body>
    <div class="container d-flex justify-content-center align-items-center h-100">
        <div class="forgot-container">
            <div class="text-center mb-4">
                <img src="./download.jpeg" alt="Logo" class="logo-img">
                <h3 class="form-title">Forgot Password?</h3>
                <p class="form-subtitle">Enter your email to reset your password</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?= $error ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="fas fa-check-circle me-2"></i>
                    <?= $success ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-4">
                    <label for="email" class="form-label fw-semibold">Email Address</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                        <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email" required>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary w-100 mb-3">
                    <i class="fas fa-paper-plane me-2"></i> Reset Password
                </button>
                
                <div class="text-center mt-3">
                    <a href="login.php" class="text-decoration-none">
                        <i class="fas fa-arrow-left me-1"></i> Back to Login
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="wave-decoration">
        <svg data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120" preserveAspectRatio="none">
            <path d="M321.39,56.44c58-10.79,114.16-30.13,172-41.86,82.39-16.72,168.19-17.73,250.45-.39C823.78,31,906.67,72,985.66,92.83c70.05,18.48,146.53,26.09,214.34,3V0H0V27.35A600.21,600.21,0,0,0,321.39,56.44Z" class="shape-fill"></path>
        </svg>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>