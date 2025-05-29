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

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page
    header("Location: " . SITE_URL . "/public/login.php");
    exit;
}
