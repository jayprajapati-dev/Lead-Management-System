<?php
/**
 * Authentication Check
 * 
 * This file checks if the user is logged in and redirects to login page if not.
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include required files
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/trial_functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page
    header("Location: " . SITE_URL . "/public/login.php");
    exit;
}

// Check if user exists in database
$check_sql = "SELECT id, status FROM users WHERE id = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("i", $_SESSION['user_id']);
$check_stmt->execute();
$result = $check_stmt->get_result();

if ($result->num_rows === 0) {
    // User doesn't exist, clear session and redirect to login
    session_destroy();
    header("Location: " . SITE_URL . "/public/login.php?error=account_deleted");
    exit;
}

// Get user status
$user = $result->fetch_assoc();

// Check trial status
if ($user['status'] === 'trial' && isTrialExpired($_SESSION['user_id'])) {
    // Get current page
    $current_page = basename($_SERVER['PHP_SELF']);
    
    // Allow access to upgrade page and essential endpoints
    $allowed_pages = ['upgrade.php', 'logout.php'];
    
    // Redirect to upgrade page if not on an allowed page
    if (!in_array($current_page, $allowed_pages)) {
        // Update user status to 'expired'
        $status_sql = "UPDATE users SET status = 'expired' WHERE id = ? AND status = 'trial'";
        $status_stmt = $conn->prepare($status_sql);
        $status_stmt->bind_param("i", $_SESSION['user_id']);
        $status_stmt->execute();
        
        header("Location: " . SITE_URL . "/dashboard/upgrade.php");
        exit;
    }
}
