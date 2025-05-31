<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}

require_once '../includes/config.php';

$error = '';
$success = '';
$email = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    
    if (empty($email)) {
        $error = 'Please enter your email address.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        try {
            // Check if email exists in database
            $stmt = executeQuery(
                "SELECT id, name, email, status FROM users WHERE email = ?",
                [$email]
            );
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            
            if ($user && $user['status'] === 'active') {
                // Generate reset token
                $token = bin2hex(random_bytes(32));
                $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
                
                // Store reset token in database
                $update_stmt = executeQuery(
                    "UPDATE users SET reset_token = ?, reset_expires = ? WHERE id = ?",
                    [$token, $expires, $user['id']]
                );
                
                if ($update_stmt->affected_rows > 0) {
                    // In a real application, you would send an email here
                    // For demo purposes, we'll just show the reset link
                    $reset_link = "http://{$_SERVER['HTTP_HOST']}/Lead-Management-System/public/reset-password.php?token=" . $token;
                    $success = "Password reset instructions have been sent to your email address.";
                    
                    // For demo purposes only - remove in production
                    $demo_message = "<div class='alert alert-info mt-3'>
                        <strong>Demo Mode:</strong> In a real application, an email would be sent to {$email} with the reset link.<br>
                        For testing, you can use this reset link: <a href='{$reset_link}'>{$reset_link}</a>
                    </div>";
                } else {
                    $error = 'Failed to generate reset token. Please try again.';
                }
            } else {
                if ($user && $user['status'] !== 'active') {
                    $error = 'This account is not active. Please contact administrator.';
                } else {
                    // Don't reveal if email exists or not for security
                    $success = 'If your email is registered, you will receive password reset instructions.';
                }
            }
        } catch (Exception $e) {
            $error = 'An error occurred. Please try again later.';
            error_log("Password reset error: " . $e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Lead Management System</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <style>
        body {
            background-color: #f8f9fa;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 0;
        }
        .forgot-container {
            max-width: 450px;
            width: 90%;
            padding: 2.5rem;
            background: white;
            border-radius: 15px;
            box-shadow: 0 0 30px rgba(0,0,0,0.1);
        }
        .forgot-header {
            text-align: center;
            margin-bottom: 2.5rem;
        }
        .forgot-header img {
            width: 80px;
            height: 80px;
            margin-bottom: 1rem;
        }
        .forgot-header h1 {
            color: #2c3e50;
            font-size: 2rem;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }
        .forgot-header p {
            color: #7f8c8d;
            margin: 0;
            font-size: 1.1rem;
        }
        .form-control {
            padding: 0.75rem 1rem;
            border-radius: 8px;
            border: 1px solid #e0e0e0;
            font-size: 1rem;
        }
        .form-control:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
        }
        .form-label {
            font-weight: 500;
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }
        .btn-submit {
            padding: 0.75rem 1rem;
            font-weight: 500;
            background-color: #3498db;
            border: none;
            width: 100%;
            border-radius: 8px;
            font-size: 1.1rem;
            margin-top: 1rem;
        }
        .btn-submit:hover {
            background-color: #2980b9;
        }
        .alert {
            margin-bottom: 1.5rem;
            border-radius: 8px;
            padding: 1rem;
        }
        .auth-links {
            margin-top: 1.5rem;
            text-align: center;
        }
        .auth-links a {
            color: #3498db;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s;
        }
        .auth-links a:hover {
            color: #2980b9;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="forgot-container">
        <div class="forgot-header">
            <a href="index.php" class="text-decoration-none">
                <h1 class="display-4 mb-3">Lead Management System</h1>
            </a>
            <p class="lead">Enter your email to reset your password</p>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-danger" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                <?php echo htmlspecialchars($success); ?>
            </div>
            <?php echo $demo_message ?? ''; ?>
        <?php endif; ?>
        
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
            <div class="mb-4">
                <label for="email" class="form-label">Email address</label>
                <input type="email" 
                       class="form-control" 
                       id="email" 
                       name="email" 
                       value="<?php echo htmlspecialchars($email); ?>"
                       placeholder="Enter your registered email"
                       required 
                       autofocus>
                <div class="form-text text-muted mt-2">
                    We'll send you a link to reset your password.
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary btn-submit">
                <i class="fas fa-paper-plane me-2"></i>Send Reset Link
            </button>
        </form>
        
        <div class="auth-links">
            <p>Remember your password? <a href="index.php">Back to Login</a></p>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 