<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../includes/config.php';

// Check if user is already logged in
if (isLoggedIn()) {
    header('Location: ../dashboard/dashboard.php');
    exit();
}

$error = '';
$success = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitizeInput($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);
    
    if (empty($email) || empty($password)) {
        $error = 'Please enter both email and password.';
    } else {
        try {
            // Get user by email
            $stmt = executeQuery(
                "SELECT id, email, password, status, is_locked, lock_until, first_name, last_name FROM users WHERE email = ?",
                [$email]
            );
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                $error = 'Invalid email or password.';
                logLoginAttempt(0, 'failed', 'Email not found');
            } else {
                $user = $result->fetch_assoc();
                
                // Check if account is locked
                if (isAccountLocked($user['id'])) {
                    $error = 'Account is temporarily locked. Please try again later.';
                    logLoginAttempt($user['id'], 'failed', 'Account locked');
                } else {
                    // Verify password
                    if (password_verify($password, $user['password'])) {
                        // Check account status
                        if ($user['status'] === 'expired') {
                            $error = '
                                <div class="d-flex flex-column align-items-center">
                                    <div class="mb-3">Your trial period has expired. Please upgrade your account to continue.</div>
                                    <a href="' . SITE_URL . '/public/upgrade.php" class="btn btn-primary">
                                        <i class="fas fa-arrow-circle-up me-2"></i>Upgrade Now
                                    </a>
                                </div>';
                            logLoginAttempt($user['id'], 'failed', 'Trial expired');
                        } elseif ($user['status'] === 'suspended') {
                            $error = 'Your account has been suspended. Please contact support.';
                            logLoginAttempt($user['id'], 'failed', 'Account suspended');
                        } else {
                            // Reset login attempts on successful login
                            updateLoginAttempts($user['id'], false);
                            
                            // Set session variables
                            $_SESSION['user_id'] = $user['id'];
                            $_SESSION['user_email'] = $user['email'];
                            $_SESSION['user_first_name'] = $user['first_name'];
                            $_SESSION['user_last_name'] = $user['last_name'];
                            $_SESSION['last_activity'] = time();
                            
                            // Handle remember me
                            if ($remember) {
                                $token = generateToken();
                                $expiry = time() + REMEMBER_ME_LIFETIME;
                                
                                // Store token in database
                                executeQuery(
                                    "INSERT INTO user_sessions (user_id, session_token, ip_address, user_agent) VALUES (?, ?, ?, ?)",
                                    [$user['id'], $token, $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']]
                                );
                                
                                // Set secure cookie
                                setcookie(
                                    'remember_token',
                                    $token,
                                    [
                                        'expires' => $expiry,
                                        'path' => '/',
                                        'secure' => true,
                                        'httponly' => true,
                                        'samesite' => 'Strict'
                                    ]
                                );
                            }
                            
                            // Log successful login
                            logLoginAttempt($user['id'], 'success');
                            
                            // Update last login time
                            executeQuery(
                                "UPDATE users SET last_login = NOW() WHERE id = ?",
                                [$user['id']]
                            );
                            
                            // Redirect to dashboard
                            header('Location: ../dashboard/dashboard.php');
                            exit();
                        }
                    } else {
                        $error = 'Invalid email or password.';
                        updateLoginAttempts($user['id']);
                        logLoginAttempt($user['id'], 'failed', 'Invalid password');
                    }
                }
            }
        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            $error = 'An error occurred. Please try again later.';
        }
    }
}

// Check for remember me cookie
if (!isLoggedIn() && isset($_COOKIE['remember_token'])) {
    try {
        $token = $_COOKIE['remember_token'];
        $stmt = executeQuery(
            "SELECT u.id, u.status FROM users u 
             INNER JOIN user_sessions s ON u.id = s.user_id 
             WHERE s.session_token = ? AND s.last_activity > DATE_SUB(NOW(), INTERVAL ? SECOND)",
            [$token, REMEMBER_ME_LIFETIME]
        );
        
        if ($stmt->get_result()->num_rows > 0) {
            $user = $stmt->get_result()->fetch_assoc();
            
            if ($user['status'] !== 'expired' && $user['status'] !== 'suspended') {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['last_activity'] = time();
                
                // Update session last activity
                executeQuery(
                    "UPDATE user_sessions SET last_activity = NOW() WHERE session_token = ?",
                    [$token]
                );
                
                header('Location: ../dashboard/dashboard.php');
                exit();
            }
        }
        
        // Clear invalid remember me cookie
        setcookie('remember_token', '', time() - 3600, '/', '', true, true);
    } catch (Exception $e) {
        error_log("Remember me error: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo SITE_NAME; ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="css/style.css" rel="stylesheet">
    <link href="assets/css/login.css" rel="stylesheet">
</head>
<body class="professional-theme">
    <div class="login-wrapper">
        <div class="login-container shadow-lg">
            <div class="login-header text-center mb-4">
                <a href="index.php" class="logo-link">
                    <h1 class="site-name"><?php echo SITE_NAME; ?></h1>
                </a>
                <h2 class="fw-bold">Welcome Back</h2>
                <p class="text-muted">Enter your credentials to access your account</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?php echo $error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    <?php echo htmlspecialchars($success); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <form method="post" class="needs-validation" novalidate>
                <div class="mb-4">
                    <label for="email" class="form-label fw-medium">Email Address</label>
                    <div class="input-group input-group-lg">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="fas fa-envelope text-primary"></i>
                        </span>
                        <input type="email" 
                               class="form-control border-start-0" 
                               id="email" 
                               name="email" 
                               placeholder="Enter your email"
                               value="<?php echo htmlspecialchars($email ?? ''); ?>"
                               required
                               autocomplete="email">
                    </div>
                    <div class="invalid-feedback">
                        Please enter a valid email address.
                    </div>
                </div>
                
                <div class="mb-4">
                    <div class="d-flex justify-content-between">
                        <label for="password" class="form-label fw-medium">Password</label>
                        <a href="forgot-password.php" class="forgot-password-link small">Forgot Password?</a>
                    </div>
                    <div class="input-group input-group-lg">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="fas fa-lock text-primary"></i>
                        </span>
                        <input type="password" 
                               class="form-control border-start-0" 
                               id="password" 
                               name="password" 
                               placeholder="Enter your password"
                               required
                               autocomplete="current-password">
                        <span class="toggle-password" onclick="togglePassword('password')" title="Toggle password visibility">
                            <i class="fas fa-eye"></i>
                        </span>
                    </div>
                    <div class="invalid-feedback">
                        Please enter your password.
                    </div>
                </div>
                
                <div class="mb-4">
                    <div class="form-check">
                        <input type="checkbox" 
                               class="form-check-input" 
                               id="remember" 
                               name="remember"
                               <?php echo isset($_POST['remember']) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="remember">Remember me</label>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary btn-lg w-100 mb-3">
                    <span class="button-text">Sign In</span>
                    <span class="spinner-border spinner-border-sm ms-2 d-none" role="status" aria-hidden="true"></span>
                </button>
            </form>
            
            <div class="auth-links text-center mt-4">
                <p class="mb-0">
                    Don't have an account? 
                    <a href="register.php" class="fw-bold text-primary">Create an account</a>
                </p>
            </div>
            
            <div class="text-center mt-4">
                <a href="index.php" class="btn btn-link text-muted">
                    <i class="fas fa-arrow-left me-1"></i> Back to Home
                </a>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Custom Script -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Password Toggle Script
        window.togglePassword = function(inputId) {
            const passwordInput = document.getElementById(inputId);
            const toggleIcon = passwordInput.parentElement.querySelector('.toggle-password i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        };
        
        // Form validation and loading state
        const forms = document.querySelectorAll('.needs-validation');
        Array.from(forms).forEach(form => {
            form.addEventListener('submit', function(event) {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                } else {
                    const loginButton = form.querySelector('.btn-login');
                    const buttonText = loginButton.querySelector('.button-text');
                    const spinner = loginButton.querySelector('.spinner-border');
                    
                    loginButton.classList.add('loading');
                    buttonText.textContent = 'Signing in...';
                    spinner.classList.remove('d-none');
                    loginButton.disabled = true;
                }
                form.classList.add('was-validated');
            }, false);
        });
    });
    </script>
</body>
</html> 