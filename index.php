<?php
require_once 'includes/config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    redirect('dashboard.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'Please enter both email and password';
    } else {
        try {
            $stmt = executeQuery(
                "SELECT id, name, email, password, role FROM users WHERE email = ? AND status = 'active'",
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
                    
                    redirect('dashboard.php');
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
    <title>Login - CRM System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-box {
            max-width: 400px;
            width: 90%;
            padding: 2rem;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .login-header h1 {
            font-size: 1.5rem;
            color: #333;
        }
    </style>
</head>
<body>
    <div class="login-box">
        <div class="login-header">
            <h1>CRM System Login</h1>
        </div>
        
        <?php if ($error): ?>
        <div class="alert alert-danger" role="alert">
            <?php echo $error; ?>
        </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="mb-3">
                <label for="email" class="form-label">Email address</label>
                <input type="email" 
                       class="form-control" 
                       id="email" 
                       name="email" 
                       value="<?php echo isset($_POST['email']) ? sanitize($_POST['email']) : ''; ?>"
                       required>
            </div>
            
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" 
                       class="form-control" 
                       id="password" 
                       name="password" 
                       required>
            </div>
            
            <button type="submit" class="btn btn-primary w-100">Login</button>
        </form>
        
        <div class="text-center mt-3">
            <small class="text-muted">
                Demo Credentials:<br>
                Admin: admin@crm.com / admin123<br>
                User: user@crm.com / user123
            </small>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 