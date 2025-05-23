<?php
session_start();

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: /dashboard/dashboard.php");
    exit();
}

// Include database connection
require_once 'includes/db.php';

$error = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'Please enter both email and password';
    } else {
        try {
            $stmt = executeQuery(
                "SELECT id, name, email, password, role, status FROM users WHERE email = ? AND status = 'active'",
                [$email]
            );
            $result = $stmt->get_result();
            
            if ($user = $result->fetch_assoc()) {
                if (password_verify($password, $user['password'])) {
                    // Set session variables
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['name'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_role'] = $user['role'];
                    
                    // Update last login
                    executeQuery(
                        "UPDATE users SET last_login = NOW() WHERE id = ?",
                        [$user['id']]
                    );
                    
                    // Redirect to dashboard
                    header("Location: /dashboard/dashboard.php");
                    exit();
                } else {
                    $error = 'Invalid email or password';
                }
            } else {
                $error = 'Invalid email or password';
            }
        } catch (Exception $e) {
            $error = 'An error occurred. Please try again later.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - CRM Dashboard</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts - Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .login-container {
            max-width: 400px;
            width: 90%;
            padding: 2rem;
        }
        
        .login-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .login-header img {
            width: 80px;
            margin-bottom: 1rem;
        }
        
        .login-header h1 {
            font-size: 1.5rem;
            color: #333;
            margin-bottom: 0.5rem;
        }
        
        .login-header p {
            color: #6c757d;
            margin: 0;
        }
        
        .form-floating {
            margin-bottom: 1rem;
        }
        
        .form-floating > .form-control {
            padding: 1rem 0.75rem;
        }
        
        .btn-login {
            padding: 0.8rem;
            font-weight: 500;
        }
        
        .alert {
            margin-bottom: 1rem;
        }
        
        /* Dark mode styles */
        .dark-mode {
            background-color: #1a1a1a;
            color: #ffffff;
        }
        
        .dark-mode .login-card {
            background-color: #2d2d2d;
            color: #ffffff;
        }
        
        .dark-mode .login-header h1 {
            color: #ffffff;
        }
        
        .dark-mode .login-header p {
            color: #adb5bd;
        }
        
        .dark-mode .form-control {
            background-color: #3d3d3d;
            border-color: #4d4d4d;
            color: #ffffff;
        }
        
        .dark-mode .form-control:focus {
            background-color: #3d3d3d;
            color: #ffffff;
        }
        
        .dark-mode .form-floating > label {
            color: #adb5bd;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card p-4">
            <div class="login-header">
                <img src="https://ui-avatars.com/api/?name=CRM&background=007bff&color=fff" alt="CRM Logo">
                <h1>Welcome Back</h1>
                <p>Sign in to your CRM Dashboard</p>
            </div>
            
            <?php if ($error): ?>
            <div class="alert alert-danger" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
            <?php endif; ?>
            
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                <div class="form-floating">
                    <input type="email" 
                           class="form-control" 
                           id="email" 
                           name="email" 
                           placeholder="name@example.com"
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                           required>
                    <label for="email">Email address</label>
                </div>
                
                <div class="form-floating">
                    <input type="password" 
                           class="form-control" 
                           id="password" 
                           name="password" 
                           placeholder="Password"
                           required>
                    <label for="password">Password</label>
                </div>
                
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="remember" name="remember">
                        <label class="form-check-label" for="remember">Remember me</label>
                    </div>
                    <a href="forgot-password.php" class="text-decoration-none">Forgot password?</a>
                </div>
                
                <button type="submit" class="btn btn-primary w-100 btn-login">
                    <i class="fas fa-sign-in-alt me-2"></i>Sign In
                </button>
            </form>
            
            <div class="text-center mt-4">
                <button class="btn btn-link" id="darkModeToggle">
                    <i class="fas fa-moon"></i> Toggle Dark Mode
                </button>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Dark mode toggle
        const darkModeToggle = document.getElementById('darkModeToggle');
        const body = document.body;
        const icon = darkModeToggle.querySelector('i');
        
        // Check for saved dark mode preference
        const darkMode = localStorage.getItem('darkMode') === 'true';
        if (darkMode) {
            body.classList.add('dark-mode');
            icon.classList.replace('fa-moon', 'fa-sun');
        }
        
        // Toggle dark mode
        darkModeToggle.addEventListener('click', function() {
            body.classList.toggle('dark-mode');
            const isDarkMode = body.classList.contains('dark-mode');
            localStorage.setItem('darkMode', isDarkMode);
            
            // Update icon
            if (isDarkMode) {
                icon.classList.replace('fa-moon', 'fa-sun');
            } else {
                icon.classList.replace('fa-sun', 'fa-moon');
            }
        });
    });
    </script>
</body>
</html> 