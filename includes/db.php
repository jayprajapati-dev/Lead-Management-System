<?php
session_start();

// Check if user is logged in
function checkLogin() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: /login.php");
        exit();
    }
}

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'crm_dashboard');

// Create database connection
try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    // Set charset to utf8mb4
    $conn->set_charset("utf8mb4");
} catch (Exception $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Helper function to sanitize input
function sanitize($input) {
    global $conn;
    return $conn->real_escape_string(trim($input));
}

// Helper function to execute queries safely
function executeQuery($sql, $params = []) {
    global $conn;
    $stmt = $conn->prepare($sql);
    
    if ($stmt === false) {
        throw new Exception("Query preparation failed: " . $conn->error);
    }
    
    if (!empty($params)) {
        $types = str_repeat('s', count($params)); // Default to string type
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    return $stmt;
}
?> 