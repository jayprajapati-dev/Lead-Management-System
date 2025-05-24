<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'crm_dashboard');

// Application settings
define('SITE_NAME', 'CRM Dashboard');
define('SITE_URL', 'http://localhost/Lead-Management-System');
define('TRIAL_DAYS', 7);
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 30); // minutes

// Security settings
define('PASSWORD_MIN_LENGTH', 8);
define('SESSION_LIFETIME', 3600); // 1 hour
define('REMEMBER_ME_LIFETIME', 2592000); // 30 days

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../logs/error.log');

// Create logs directory if it doesn't exist
if (!file_exists(__DIR__ . '/../logs')) {
    mkdir(__DIR__ . '/../logs', 0777, true);
}

// Database connection
try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    $conn->set_charset("utf8mb4");
} catch (Exception $e) {
    error_log("Database connection error: " . $e->getMessage());
    die("Database connection failed. Please try again later.");
}

// Helper functions
function executeQuery($sql, $params = []) {
    global $conn;
    try {
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Query preparation failed: " . $conn->error);
        }
        
        if (!empty($params)) {
            $types = str_repeat('s', count($params)); // Default to string type
            $stmt->bind_param($types, ...$params);
        }
        
        if (!$stmt->execute()) {
            throw new Exception("Query execution failed: " . $stmt->error);
        }
        
        return $stmt;
    } catch (Exception $e) {
        error_log("Query execution error: " . $e->getMessage());
        throw $e;
    }
}

function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function generateToken($length = 32) {
    return bin2hex(random_bytes($length));
}

function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . SITE_URL . '/index.php');
        exit();
    }
}

function checkTrialStatus($user_id) {
    $stmt = executeQuery(
        "SELECT status, trial_end_date FROM users WHERE id = ?",
        [$user_id]
    );
    $result = $stmt->get_result()->fetch_assoc();
    
    if ($result['status'] === 'trial' && strtotime($result['trial_end_date']) < time()) {
        // Update user status to expired
        executeQuery(
            "UPDATE users SET status = 'expired' WHERE id = ?",
            [$user_id]
        );
        return false;
    }
    return $result['status'] === 'trial';
}

function logLoginAttempt($user_id, $status, $reason = '') {
    executeQuery(
        "INSERT INTO login_history (user_id, ip_address, user_agent, status, failure_reason) VALUES (?, ?, ?, ?, ?)",
        [
            $user_id,
            $_SERVER['REMOTE_ADDR'],
            $_SERVER['HTTP_USER_AGENT'],
            $status,
            $reason
        ]
    );
}

function updateLoginAttempts($user_id, $increment = true) {
    if ($increment) {
        executeQuery(
            "UPDATE users SET login_attempts = login_attempts + 1, 
             is_locked = CASE WHEN login_attempts + 1 >= ? THEN TRUE ELSE is_locked END,
             lock_until = CASE WHEN login_attempts + 1 >= ? THEN DATE_ADD(NOW(), INTERVAL ? MINUTE) ELSE lock_until END
             WHERE id = ?",
            [MAX_LOGIN_ATTEMPTS, MAX_LOGIN_ATTEMPTS, LOGIN_LOCKOUT_TIME, $user_id]
        );
    } else {
        executeQuery(
            "UPDATE users SET login_attempts = 0, is_locked = FALSE, lock_until = NULL WHERE id = ?",
            [$user_id]
        );
    }
}

function isAccountLocked($user_id) {
    $stmt = executeQuery(
        "SELECT is_locked, lock_until FROM users WHERE id = ?",
        [$user_id]
    );
    $result = $stmt->get_result()->fetch_assoc();
    
    if ($result['is_locked'] && strtotime($result['lock_until']) > time()) {
        return true;
    }
    
    // Reset lock if lock period has expired
    if ($result['is_locked'] && strtotime($result['lock_until']) <= time()) {
        executeQuery(
            "UPDATE users SET is_locked = FALSE, lock_until = NULL, login_attempts = 0 WHERE id = ?",
            [$user_id]
        );
    }
    
    return false;
}

// Helper function to redirect
function redirect($url) {
    header("Location: $url");
    exit();
}

// Helper function to sanitize input
function sanitize($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
} 