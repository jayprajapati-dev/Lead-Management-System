<?php
require_once '../includes/config.php';

// Get the remember me token if it exists
$remember_token = $_COOKIE['remember_token'] ?? null;

// Clear the remember me token from database if it exists
if ($remember_token && isLoggedIn()) {
    try {
        executeQuery(
            "DELETE FROM user_sessions WHERE user_id = ? AND session_token = ?",
            [$_SESSION['user_id'], $remember_token]
        );
    } catch (Exception $e) {
        error_log("Logout error (token cleanup): " . $e->getMessage());
    }
}

// Clear the remember me cookie
setcookie('remember_token', '', time() - 3600, '/', '', true, true);

// Destroy the session
session_unset();
session_destroy();

// Redirect to login page
header('Location: index.php');
exit();
?> 