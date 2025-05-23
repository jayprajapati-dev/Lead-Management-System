<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once '../includes/config.php';

// Test database connection
echo "<h2>Database Connection Test</h2>";
try {
    $test_query = executeQuery("SELECT 1");
    $test_query->get_result()->free();
    echo "✅ Database connection successful<br>";
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "<br>";
    exit;
}

// Test users table
echo "<h2>Users Table Test</h2>";
try {
    $stmt = executeQuery("SELECT id, name, email, role, status FROM users");
    $result = $stmt->get_result();
    $users = $result->fetch_all(MYSQLI_ASSOC);
    $result->free();
    $stmt->close();
    
    echo "✅ Users table exists and contains " . count($users) . " users<br>";
    echo "<pre>";
    print_r($users);
    echo "</pre>";
} catch (Exception $e) {
    echo "❌ Error accessing users table: " . $e->getMessage() . "<br>";
}

// Test specific user credentials
echo "<h2>Testing Admin Credentials</h2>";
$test_email = 'admin@crm.com';
$test_password = 'admin123';

try {
    $stmt = executeQuery(
        "SELECT id, name, email, password, role, status FROM users WHERE email = ?",
        [$test_email]
    );
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $result->free();
    $stmt->close();
    
    if ($user) {
        echo "✅ User found in database<br>";
        echo "User details:<br>";
        echo "ID: " . $user['id'] . "<br>";
        echo "Name: " . $user['name'] . "<br>";
        echo "Email: " . $user['email'] . "<br>";
        echo "Role: " . $user['role'] . "<br>";
        echo "Status: " . $user['status'] . "<br>";
        
        // Test password verification
        if (password_verify($test_password, $user['password'])) {
            echo "✅ Password verification successful<br>";
        } else {
            echo "❌ Password verification failed<br>";
            echo "Stored hash: " . $user['password'] . "<br>";
            echo "Test password: " . $test_password . "<br>";
            
            // Generate a new hash for comparison
            $new_hash = password_hash($test_password, PASSWORD_DEFAULT);
            echo "New hash for comparison: " . $new_hash . "<br>";
        }
    } else {
        echo "❌ User not found in database<br>";
        
        // Let's check if the database has any users at all
        $check_stmt = executeQuery("SELECT COUNT(*) as count FROM users");
        $count_result = $check_stmt->get_result();
        $count = $count_result->fetch_assoc()['count'];
        $count_result->free();
        $check_stmt->close();
        
        echo "Total users in database: " . $count . "<br>";
        
        if ($count == 0) {
            echo "<br>⚠️ No users found in database. You may need to import the database schema.<br>";
            echo "Please import the file: database/crm_dashboard.sql<br>";
        }
    }
} catch (Exception $e) {
    echo "❌ Error testing user credentials: " . $e->getMessage() . "<br>";
}

// Test password hash
echo "<h2>Password Hash Test</h2>";
$test_hash = password_hash($test_password, PASSWORD_DEFAULT);
echo "New hash for 'admin123': " . $test_hash . "<br>";
echo "Verification test: " . (password_verify($test_password, $test_hash) ? "✅" : "❌") . "<br>";

// Show PHP version and configuration
echo "<h2>PHP Configuration</h2>";
echo "PHP Version: " . phpversion() . "<br>";
echo "password_hash() available: " . (function_exists('password_hash') ? "✅" : "❌") . "<br>";
echo "password_verify() available: " . (function_exists('password_verify') ? "✅" : "❌") . "<br>";

// Show database configuration
echo "<h2>Database Configuration</h2>";
echo "Database Host: " . DB_HOST . "<br>";
echo "Database Name: " . DB_NAME . "<br>";
echo "Database User: " . DB_USER . "<br>";
?> 