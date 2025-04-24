<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include_once 'connect.php';

$token = $_GET['token'] ?? '';
$error = '';
$success = '';
$user = null;

if (!empty($token)) {
    $stmt = $myconnection->prepare("SELECT * FROM users WHERE reset_token = ? AND reset_token_expiry > NOW()");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    if (!$user) {
        $error = 'Invalid or expired reset link';
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && $user) {
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];

        if ($password !== $confirm_password) {
            $error = 'Passwords do not match';
        } elseif (strlen($password) < 8) {
            $error = 'Password must be at least 8 characters';
        } elseif (!preg_match('/[A-Z]/', $password)) {
            $error = 'Password must contain an uppercase letter';
        } elseif (!preg_match('/[0-9]/', $password)) {
            $error = 'Password must contain a number';
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $myconnection->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_token_expiry = NULL WHERE id = ?");
            $stmt->bind_param("si", $hashed_password, $user['id']);
            
            if ($stmt->execute()) {
                $success = 'Password reset successfully!';
                header("Location: login.php");
               
              } else {
                $error = 'Failed to update password';
            }
            $stmt->close();
        }
    }
} else {
    $error = 'Invalid reset link';
}
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Reset - Cafeteria System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-rtl@5.3.0/dist/css/bootstrap-rtl.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  
    <style>
    :root {
        --coffee-dark: #6F4E37; 
        --coffee-medium: #8B4513; 

        --coffee-light: #d2b48c; 

        --cream-color: #f5f0eb; 

      }
    
    body {
        background: linear-gradient(135deg, #d2b48c 0%, #6F4E37 100%); 

        height: 100vh;
        font-family: 'Tajawal', sans-serif;
        text-align: left;
    }
    
    .reset-container {
        max-width: 450px;
        border-radius: 20px;
        box-shadow: 0 15px 30px rgba(0, 0, 0, 0.2);
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border: 1px solid var(--coffee-light);
        transition: all 0.3s ease;
        overflow: hidden;
    }

    .reset-container:hover {
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
        color: var(--coffee-dark);
        font-weight: 700;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .form-subtitle {
        color: #6c757d;
        font-weight: 400;
    }

    .input-group-text {
        background-color: var(--coffee-dark);
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
        border-color: var(--coffee-dark);
    }

    .btn-primary {
        background-color: var(--coffee-dark);
        border: none;
        padding: 12px;
        font-weight: 600;
        letter-spacing: 0.5px;
        transition: all 0.3s ease;
        background: linear-gradient(135deg, var(--coffee-dark) 0%, var(--coffee-medium) 100%);
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 15px rgba(111, 78, 55, 0.3);
        background: linear-gradient(135deg, var(--coffee-medium) 0%, var(--coffee-dark) 100%);
    }

    .alert {
        border-radius: 10px;
    }

    .password-strength {
        height: 5px;
        margin-top: 5px;
        border-radius: 5px;
        transition: all 0.3s ease;
    }

    .toggle-password {
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .toggle-password:hover {
        color: var(--coffee-dark);
    }

    .btn-outline-secondary {
        border-color: var(--coffee-light);
        color: var(--coffee-dark);
    }

    .btn-outline-secondary:hover {
        background-color: var(--coffee-light);
        color: white;
    }
</style>
</head>
<body>
    <div class="container d-flex justify-content-center align-items-center h-100">
        <div class="reset-container p-5">
            <div class="text-center mb-4">
                <img src="./download.jpeg" alt="System Logo" class="logo-img">
                <h3 class="form-title">Reset Password</h3>
                <p class="form-subtitle">Please enter your new password</p>
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
                <div class="text-center mt-4">
                    <a href="login.php" class="btn btn-primary w-100">
                        <i class="fas fa-sign-in-alt me-2"></i>Back to Login
                    </a>
                </div>
            <?php endif; ?>

            <?php if ($user && empty($success)): ?>
                <form method="POST">
                    <div class="mb-4">
                        <label for="password" class="form-label fw-semibold">New Password</label>
                        <div class="input-group mb-3">
                            <input type="password" class="form-control" id="password" name="password" 
                                   placeholder="Enter new password" required
                                   oninput="checkPasswordStrength(this.value)">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <button class="btn btn-outline-secondary toggle-password" type="button">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="password-strength" id="passwordStrength"></div>
                        <small class="text-muted">Password must be at least 8 characters and contain an uppercase letter and a number.</small>
                    </div>
                    
                    <div class="mb-4">
                        <label for="confirm_password" class="form-label fw-semibold">Confirm Password</label>
                        <div class="input-group mb-3">
                            <input type="password" class="form-control" id="confirm_password" 
                                   name="confirm_password" placeholder="Re-enter new password" required>
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <button class="btn btn-outline-secondary toggle-password" type="button">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100 mb-3">
                        <i class="fas fa-sync-alt me-2"></i>Change Password
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function checkPasswordStrength(password) {
            const strengthBar = document.getElementById('passwordStrength');
            let strength = 0;

            if (password.length >= 8) strength += 1;
            if (password.match(/([a-z].*[A-Z])|([A-Z].*[a-z])/)) strength += 1;
            if (password.match(/([0-9])/)) strength += 1;
            if (password.match(/([!,%,&,@,#,$,^,*,?,_,~])/)) strength += 1;

            switch(strength) {
                case 0:
                    strengthBar.style.width = '0%';
                    strengthBar.style.backgroundColor = 'transparent';
                    break;
                case 1:
                    strengthBar.style.width = '25%';
                    strengthBar.style.backgroundColor = '#ff4d4d';
                    break;
                case 2:
                    strengthBar.style.width = '50%';
                    strengthBar.style.backgroundColor = '#ffa64d';
                    break;
                case 3:
                    strengthBar.style.width = '75%';
                    strengthBar.style.backgroundColor = '#66b3ff';
                    break;
                case 4:
                    strengthBar.style.width = '100%';
                    strengthBar.style.backgroundColor = '#00cc66';
                    break;
            }
        }

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
    </script>
</body>
</html>