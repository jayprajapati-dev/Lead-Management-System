<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once '../includes/config.php';

// Only allow this script to run from localhost
if ($_SERVER['REMOTE_ADDR'] !== '127.0.0.1' && $_SERVER['REMOTE_ADDR'] !== '::1') {
    die('This script can only be run from localhost');
}

$stored_hash = '$2y$10$CZgxShTfNMWeZykkUX71Tu1JGvSsTYMrbUzxMf4wfI7W1TuQ0rqsu';
$test_password = 'admin123';

echo "<h2>Password Hash Test</h2>";

// Test 1: Verify the stored hash
echo "<h3>Test 1: Verifying stored hash</h3>";
echo "<p>Stored hash: " . $stored_hash . "</p>";
echo "<p>Testing password: " . $test_password . "</p>";
$verify_result = password_verify($test_password, $stored_hash);
echo "<p>Verification result: " . ($verify_result ? "✅ SUCCESS" : "❌ FAILED") . "</p>";

// Test 2: Generate a new hash and verify it
echo "<h3>Test 2: Generating new hash</h3>";
$new_hash = password_hash($test_password, PASSWORD_DEFAULT);
echo "<p>New hash: " . $new_hash . "</p>";
echo "<p>Verification of new hash: " . (password_verify($test_password, $new_hash) ? "✅ SUCCESS" : "❌ FAILED") . "</p>";

// Test 3: Check database state
echo "<h3>Test 3: Database State</h3>";
try {
    $stmt = executeQuery("SELECT id, email, password FROM users WHERE email = ?", ['admin@crm.com']);
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    if ($user) {
        echo "<p>User found in database:</p>";
        echo "<ul>";
        echo "<li>ID: " . $user['id'] . "</li>";
        echo "<li>Email: " . $user['email'] . "</li>";
        echo "<li>Current hash in DB: " . $user['password'] . "</li>";
        echo "<li>Hash matches stored: " . ($user['password'] === $stored_hash ? "✅ YES" : "❌ NO") . "</li>";
        echo "<li>Verification with DB hash: " . (password_verify($test_password, $user['password']) ? "✅ SUCCESS" : "❌ FAILED") . "</li>";
        echo "</ul>";
    } else {
        echo "<p>❌ No user found with email: admin@crm.com</p>";
    }
} catch (Exception $e) {
    echo "<p>Error checking database: " . $e->getMessage() . "</p>";
}

// Test 4: Update password if needed
if (!$verify_result) {
    echo "<h3>Test 4: Updating Password</h3>";
    try {
        $new_hash = password_hash($test_password, PASSWORD_DEFAULT);
        $update_stmt = executeQuery(
            "UPDATE users SET password = ? WHERE email = ?",
            [$new_hash, 'admin@crm.com']
        );
        
        if ($update_stmt->affected_rows > 0) {
            echo "<p>✅ Password updated successfully</p>";
            echo "<p>New hash: " . $new_hash . "</p>";
            echo "<p>Verification of new hash: " . (password_verify($test_password, $new_hash) ? "✅ SUCCESS" : "❌ FAILED") . "</p>";
        } else {
            echo "<p>❌ No rows were updated</p>";
        }
    } catch (Exception $e) {
        echo "<p>Error updating password: " . $e->getMessage() . "</p>";
    }
}

// Show PHP configuration
echo "<h3>PHP Configuration</h3>";
echo "<p>PHP Version: " . phpversion() . "</p>";
echo "<p>password_hash() available: " . (function_exists('password_hash') ? "Yes" : "No") . "</p>";
echo "<p>password_verify() available: " . (function_exists('password_verify') ? "Yes" : "No") . "</p>";
?> 