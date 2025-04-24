<?php
session_start();
include 'connect.php';




$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $myconnection->real_escape_string($_POST['email']);
    $password = $_POST['password'];

    $stmt = $myconnection->prepare("SELECT id, password, is_admin FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($user = $result->fetch_assoc()) {
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['is_admin'] = $user['is_admin'];
            
            // Remember me functionality
            if (isset($_POST['remember_me'])) {
                $token = bin2hex(random_bytes(32));
                setcookie('remember_token', $token, time() + (86400 * 30), "/"); // 30 days
                
                // Store token in database
                $update_stmt = $myconnection->prepare("UPDATE users SET remember_token = ? WHERE id = ?");
                $update_stmt->bind_param("si", $token, $user['id']);
                $update_stmt->execute();
                $update_stmt->close();
            }
            
            header("Location: home.php");
            exit();
        } else {
            $error = 'Invalid email or password';
        }
    } else {
        $error = 'Invalid email or password';
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Cafeteria System</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
    :root {
        --primary-color: #6F4E37; /* Coffee brown */
        --secondary-color: #5a3c2a; /* Darker coffee */
        --accent-color: #C4A484; /* Light coffee */
        --light-color: #f8f9fa;
        --dark-color: #212529;
    }
    
    body {
        background: linear-gradient(135deg, #C4A484 0%, #6F4E37 100%);
        height: 100vh;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    
    .login-container {
        max-width: 450px;
        border-radius: 20px;
        box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.3);
        transition: all 0.3s ease;
        overflow: hidden;
    }
    
    .login-container:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.25);
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
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
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
    
    .btn-outline-primary {
        color: var(--primary-color);
        border-color: var(--primary-color);
    }
    
    .btn-outline-primary:hover {
        background-color: var(--primary-color);
        color: white;
    }
    
    .alert {
        border-radius: 10px;
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
    
    .toggle-password {
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .toggle-password:hover {
        color: var(--primary-color);
    }
    
    /* Coffee-themed additional styles */
    .btn-coffee {
        background-color: #6F4E37;
        color: white;
    }
    
    .btn-coffee:hover {
        background-color: #5a3c2a;
        color: white;
    }
    
    .page-item.active .page-link {
        background-color: #6F4E37;
        border-color: #6F4E37;
    }
    
    .page-link {
        color: #6F4E37;
    }
</style>
</head>
<body>
    <div class="container d-flex justify-content-center align-items-center h-100">
        <div class="login-container p-5">
            <div class="text-center mb-4">
                <img src="./download.jpeg" alt="Logo" class="logo-img">
                <h3 class="form-title">Welcome Back!</h3>
                <p class="form-subtitle">Please login to your account</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?= $error ?>
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
                
                <div class="mb-4">
                    <label for="password" class="form-label fw-semibold">Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
                        <button class="btn btn-outline-secondary toggle-password" type="button">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>
                
                <div class="mb-4 form-check">
                    <input type="checkbox" class="form-check-input" id="remember_me" name="remember_me">
                    <label class="form-check-label" for="remember_me">Remember me</label>
                </div>
                
                <button type="submit" class="btn btn-primary w-100 mb-3">
                    <i class="fas fa-sign-in-alt me-2"></i>Login
                </button>
                
                <div class="text-center mt-3">
                    <a href="forgot_password.php" class="text-decoration-none">
                        <i class="fas fa-key me-1"></i>Forgot Password?
                    </a>
                    <p class="mt-3">Don't have an account? <a href="register.php" class="text-decoration-none">Register here</a></p>
                </div>
            </form>
        </div>
    </div>

    <div class="wave-decoration">
        <svg data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1200 120" preserveAspectRatio="none">
            <path d="M321.39,56.44c58-10.79,114.16-30.13,172-41.86,82.39-16.72,168.19-17.73,250.45-.39C823.78,31,906.67,72,985.66,92.83c70.05,18.48,146.53,26.09,214.34,3V0H0V27.35A600.21,600.21,0,0,0,321.39,56.44Z" class="shape-fill"></path>
        </svg>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle password visibility
        document.querySelectorAll('.toggle-password').forEach(button => {
            button.addEventListener('click', function() {
                const input = this.parentNode.querySelector('input');
                const icon = this.querySelector('i');
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.replace('fa-eye', 'fa-eye-slash');
                } else {
                    input.type = 'password';
                    icon.classList.replace('fa-eye-slash', 'fa-eye');
                }
            });
        });

        // Animation for input focus
        document.querySelectorAll('.form-control').forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.style.transform = 'scale(1.02)';
                this.parentElement.style.transition = 'all 0.3s ease';
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.style.transform = 'scale(1)';
            });
        });
    </script>
</body>
</html>