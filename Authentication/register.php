<?php
session_start();
include_once 'connect.php';
include 'mail_functions.php';

$errors = [];
$success = '';




if ($_SERVER['REQUEST_METHOD'] == 'POST') {



    // Sanitize inputs
    $name = $myconnection->real_escape_string($_POST['name']);
    $email = $myconnection->real_escape_string($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $room_no = $myconnection->real_escape_string($_POST['room_no']);
    $ext = $myconnection->real_escape_string($_POST['ext']);

    // Handle profile picture upload
    $profile_pic = null;
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $file_type = $_FILES['profile_pic']['type'];
        
        if (in_array($file_type, $allowed_types)) {
            $upload_dir = 'uploads/profile_pics/';
            
            if (!file_exists($upload_dir)) {
                if (!mkdir($upload_dir, 0777, true)) {
                    error_log("Failed to create directory: " . $upload_dir);
                    $errors[] = 'Failed to create upload directory. Please contact administrator.';
                } else {
                    chmod($upload_dir, 0755);
                }
            }
            
            if (empty($errors)) {
                $filename = uniqid() . '_' . basename($_FILES['profile_pic']['name']);
                $target_path = $upload_dir . $filename;
                
                if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $target_path)) {
                    $profile_pic = $target_path;
                    chmod($target_path, 0644);
                } else {
                    $errors[] = 'Failed to upload profile picture. Please try again.';
                }
            }
        } else {
            $errors[] = 'Only JPG, PNG, and GIF files are allowed';
        }
    }

    if (empty($errors)) {
        // Check email existence first
        $check_stmt = $myconnection->prepare("SELECT id FROM users WHERE email = ?");
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();
        $check_stmt->store_result();
        
        if ($check_stmt->num_rows > 0) {
            $_SESSION['error_message'] = '
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle me-2"></i>
                Email "'.$email.'" is already registered.<br>
                <a href="login.php" class="alert-link">Click to login</a> or 
                <a href="forgot_password.php" class="alert-link">Recover password</a>
            </div>';
            header("Location: register.php");
            exit();
        }
        
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        $verification_token = bin2hex(random_bytes(32));
        
        $stmt = $myconnection->prepare("INSERT INTO users (name, email, password, room_no, ext, profile_picture, verification_token) VALUES (?, ?, ?, ?, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("sssssss", $name, $email, $hashed_password, $room_no, $ext, $profile_pic, $verification_token);
            
            if ($stmt->execute()) {
                if (send_verification_email($email, $verification_token)) {
                    $_SESSION['success_message'] = '
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle me-2"></i>
                        Registration successful! Please check your email to verify your account.
                    </div>';
                } else {
                    $_SESSION['error_message'] = '
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        Registration succeeded but failed to send verification email.
                    </div>';
                }
            } else {
                $_SESSION['error_message'] = '
                <div class="alert alert-danger">
                    <i class="fas fa-times-circle me-2"></i>
                    Registration failed: ' . htmlspecialchars($stmt->error) . '
                </div>';
            }
            $stmt->close();
        } else {
            $_SESSION['error_message'] = '
            <div class="alert alert-danger">
                <i class="fas fa-times-circle me-2"></i>
                Database error: ' . htmlspecialchars($myconnection->error) . '
            </div>';
        }
        
        header("Location: register.php");
        exit();
    } else {
        $_SESSION['error_message'] = '
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle me-2"></i>
            ' . implode('<br>', $errors) . '
        </div>';
        header("Location: register.php");
        exit();
    }
}

// Display messages
if (isset($_SESSION['error_message'])) {
    $error_message = $_SESSION['error_message'];
    unset($_SESSION['error_message']);
}

if (isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Cafeteria System</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
    body {
        background: #f5f0eb; /* لون خلفية فاتح يشبه لون الكريمة */
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        height: 100vh;
        display: flex;
        align-items: center;
    }
    
    .register-container {
        max-width: 650px;
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        background: #ffffff;
        margin: 0 auto;
        padding: 30px;
        border: 1px solid #d2b48c; 
    }
    
    .logo-img {
        width: 80px;
        height: 80px;
        object-fit: contain;
        margin-bottom: 1rem;
    }
    
    .form-title {
        color: #6F4E37; 
        font-weight: 700;
        margin-bottom: 1rem;
    }
    
    .input-group-text {
        background-color: #6F4E37;
        color: white;
        border: none;
    }
    
    .form-control {
        border-left: none;
    }
    
    .form-control:focus {
        box-shadow: 0 0 0 0.25rem rgba(111, 78, 55, 0.25); 
        border-color: #6F4E37;
    }
    
    .btn-primary {
        background-color: #6F4E37; 

        border: none;
        padding: 10px;
        font-weight: 600;
    }
    
    .btn-primary:hover {
        background-color: #5a3c2a; 
    }
    
    .btn-primary:disabled {
        opacity: 0.65;
        cursor: not-allowed;
    }
    
    .profile-pic-preview {
        width: 120px;
        height: 120px;
        object-fit: cover;
        border-radius: 50%;
        border: 3px solid #6F4E37; 
        cursor: pointer;
    }
    
    .invalid-feedback {
        color: #8B4513; 
        font-size: 0.875em;
        margin-top: 0.25rem;
    }
    
    .is-invalid {
        border-color: #8B4513 !important; 
    }
    
    .alert {
        position: relative;
    }
    
    .btn-close {
        position: absolute;
        top: 10px;
        right: 10px;
    }
    
    .spinner-border {
        display: none;
        margin-left: 10px;
    }
    
    .btn-outline-primary {
        color: #6F4E37;
        border-color: #6F4E37;
    }
    
    .btn-outline-primary:hover {
        background-color: #6F4E37;
        color: white;
    }
</style>
</head>
<body>
    <div class="container">
        <div class="register-container">
            <div class="text-center mb-4">
                <img src="./download.jpeg" alt="Logo" width="100" class="mb-3">
                <h3 class="form-title">Create New Account</h3>
            </div>
            <?php if (!empty($error_message)): ?>
                <?= $error_message ?>
            <?php endif; ?>

            <?php if (!empty($success_message)): ?>
                <?= $success_message ?>
            <?php endif; ?>

            <form id="registerForm" method="POST" enctype="multipart/form-data" novalidate>
                <div class="row">
                    <div class="col-md-6 text-center mb-4">
                        <div class="mb-3">
                            <img id="profilePicPreview" src="default-profile.png" alt="" class="profile-pic-preview mb-3">
                            <input type="file" class="form-control d-none" id="profile_pic" name="profile_pic" accept="image/*">
                            <label for="profile_pic" class="btn btn-outline-primary">
                                <i class="fas fa-camera me-2"></i>Choose Picture
                            </label>
                            <div class="invalid-feedback" id="profile_pic_error"></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="name" class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                                <input type="text" class="form-control" id="name" name="name" placeholder="Enter your full name" required>
                            </div>
                            <div class="invalid-feedback" id="name_error"></div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="email" class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email" required>
                                <span class="spinner-border spinner-border-sm text-primary" id="emailSpinner"></span>
                            </div>
                            <div class="invalid-feedback" id="email_error"></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="room_no" class="form-label fw-semibold">Room Number</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-door-open"></i></span>
                                <input type="text" class="form-control" id="room_no" name="room_no" placeholder="Enter room number">
                            </div>
                            <div class="invalid-feedback" id="room_no_error"></div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="password" class="form-label fw-semibold">Password <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                <input type="password" class="form-control" id="password" name="password" placeholder="Create password" required>
                                <button class="btn btn-outline-secondary toggle-password" type="button">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div class="invalid-feedback" id="password_error"></div>
                            <small class="form-text text-muted">Password must be at least 8 characters</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label fw-semibold">Confirm Password <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirm password" required>
                                <button class="btn btn-outline-secondary toggle-password" type="button">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div class="invalid-feedback" id="confirm_password_error"></div>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="ext" class="form-label fw-semibold">Extension Number</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-phone"></i></span>
                        <input type="text" class="form-control" id="ext" name="ext" placeholder="Enter extension number">
                    </div>
                    <div class="invalid-feedback" id="ext_error"></div>
                </div>

                <div class="d-grid gap-3">
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        <i class="fas fa-user-plus me-2"></i>Register
                    </button>
                    <a href="login.php" class="btn btn-outline-primary">
                        <i class="fas fa-sign-in-alt me-2"></i>Already have an account? Login
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Profile picture preview
        document.getElementById('profile_pic').addEventListener('change', function(e) {
            if (this.files && this.files[0]) {
                const allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
                if (!allowedTypes.includes(this.files[0].type)) {
                    showError('profile_pic', 'Only JPG, PNG, and GIF files are allowed');
                    this.value = '';
                    return;
                }
                
                if (this.files[0].size > 2 * 1024 * 1024) {
                    showError('profile_pic', 'File size should not exceed 2MB');
                    this.value = '';
                    return;
                }
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('profilePicPreview').src = e.target.result;
                    clearError('profile_pic');
                }
                reader.readAsDataURL(this.files[0]);
            }
        });

        // Toggle password visibility
        document.querySelectorAll('.toggle-password').forEach(button => {
            button.addEventListener('click', function() {
                const input = this.parentNode.querySelector('input');
                const icon = this.querySelector('i');
                input.type = input.type === 'password' ? 'text' : 'password';
                icon.classList.toggle('fa-eye-slash');
            });
        });

        // Email availability check
        let emailCheckTimeout;
        let lastCheckedEmail = '';

        document.getElementById('email').addEventListener('input', function() {
            clearTimeout(emailCheckTimeout);
            const email = this.value.trim();
            
            if (email === lastCheckedEmail || !validateEmail(email)) {
                return;
            }
            
            document.getElementById('emailSpinner').style.display = 'inline-block';
            emailCheckTimeout = setTimeout(() => {
                checkEmailAvailability(email);
                lastCheckedEmail = email;
            }, 800);
        });

        // Form submission
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (validateForm()) {
                this.submit();
            }
        });

        // Validation functions
        function validateForm() {
            let isValid = true;
            
            // Validate name
            const name = document.getElementById('name').value.trim();
            if (!name) {
                showError('name', 'Name is required');
                isValid = false;
            } else if (name.length < 3) {
                showError('name', 'Name must be at least 3 characters');
                isValid = false;
            } else {
                clearError('name');
            }
            
            // Validate email
            const email = document.getElementById('email').value.trim();
            if (!email) {
                showError('email', 'Email is required');
                isValid = false;
            } else if (!validateEmail(email)) {
                showError('email', 'Please enter a valid email address');
                isValid = false;
            } else {
                clearError('email');
            }
            
            // Validate password
            const password = document.getElementById('password').value;
            if (!password) {
                showError('password', 'Password is required');
                isValid = false;
            } else if (password.length < 2) {
                showError('password', 'Password must be at least 2 characters');
                isValid = false;
            } else {
                clearError('password');
            }
            
            // Validate confirm password
            const confirmPassword = document.getElementById('confirm_password').value;
            if (password !== confirmPassword) {
                showError('confirm_password', 'Passwords do not match');
                isValid = false;
            } else {
                clearError('confirm_password');
            }
            
            return isValid;
        }

        function validateEmail(email) {
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        }

        function checkEmailAvailability(email) {
            fetch('check_email.php?email=' + encodeURIComponent(email))
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok');
                    return response.json();
                })
                .then(data => {
                    if (data.error) {
                        console.error('Error:', data.error);
                    } else if (data.exists) {
                        showError('email', `Email ${data.email} is already registered`);
                        document.getElementById('submitBtn').disabled = true;
                    } else {
                        clearError('email');
                        document.getElementById('submitBtn').disabled = false;
                    }
                })
                .catch(error => console.error('Error:', error))
                .finally(() => {
                    document.getElementById('emailSpinner').style.display = 'none';
                });
        }

        function showError(fieldId, message) {
            const field = document.getElementById(fieldId);
            field.classList.add('is-invalid');
            document.getElementById(fieldId + '_error').textContent = message;
        }

        function clearError(fieldId) {
            const field = document.getElementById(fieldId);
            field.classList.remove('is-invalid');
            document.getElementById(fieldId + '_error').textContent = '';
        }
    </script>
</body>
</html>