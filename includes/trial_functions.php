<?php
/**
 * Trial Functions - Handles free trial functionality
 * 
 * This file contains functions related to managing the free trial period,
 * checking trial status, and redirecting expired users to the upgrade page.
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check if a user's trial has expired
 * 
 * @param int $user_id The user ID to check
 * @return bool True if trial has expired, false otherwise
 */
function isTrialExpired($user_id) {
    global $conn;
    
    // If connection is not available, include config
    if (!isset($conn)) {
        require_once __DIR__ . '/config.php';
    }
    
    try {
    $sql = "SELECT status, trial_end_date FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        // If user is already on a paid plan, trial is not expired
        if ($row['status'] === 'active') {
            return false;
        }
        
        // Check if trial end date is in the past
        if ($row['trial_end_date'] !== null) {
            $trial_end = new DateTime($row['trial_end_date']);
            $now = new DateTime();
            return $now > $trial_end;
        }
        } else {
            // User not found in database
            session_destroy();
            header("Location: " . SITE_URL . "/public/login.php?error=account_deleted");
            exit;
        }
    } catch (Exception $e) {
        error_log("Error checking trial expiration: " . $e->getMessage());
        return true; // Default to expired if there's an error
    }
    
    // Default to expired if we can't determine (safety measure)
    return true;
}

/**
 * Get the number of days remaining in the trial
 * 
 * @param int $user_id The user ID to check
 * @return int Number of days remaining, 0 if expired
 */
function getTrialDaysRemaining($user_id) {
    global $conn;
    
    // If connection is not available, include config
    if (!isset($conn)) {
        require_once __DIR__ . '/config.php';
    }
    
    $sql = "SELECT status, trial_end_date FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        // If user is on a paid plan, return 0 (no trial)
        if ($row['status'] === 'active') {
            return 0;
        }
        
        // Calculate days remaining
        if ($row['trial_end_date'] !== null) {
            $trial_end = new DateTime($row['trial_end_date']);
            $now = new DateTime();
            
            // If trial has expired, return 0
            if ($now > $trial_end) {
                return 0;
            }
            
            // Calculate difference in days
            $interval = $now->diff($trial_end);
            return $interval->days;
        }
    }
    
    // Default to 0 if we can't determine
    return 0;
}

/**
 * Set the trial end date for a new user
 * 
 * @param int $user_id The user ID to set trial for
 * @param int $trial_days Number of trial days (default: 7)
 * @return bool True if successful, false otherwise
 */
function setTrialEndDate($user_id, $trial_days = 7) {
    global $conn;
    
    // If connection is not available, include config
    if (!isset($conn)) {
        require_once __DIR__ . '/config.php';
    }
    
    // Calculate trial end date (current date + trial days)
    $trial_end = new DateTime();
    $trial_end->modify("+{$trial_days} days");
    $trial_end_formatted = $trial_end->format('Y-m-d H:i:s');
    
    // Update user record
    $sql = "UPDATE users SET trial_end_date = ?, status = 'trial' WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $trial_end_formatted, $user_id);
    
    return $stmt->execute();
}

/**
 * Enforce trial restrictions - redirect to upgrade page if trial expired
 * 
 * @param int $user_id The user ID to check
 * @return void
 */
function enforceTrialRestrictions($user_id) {
    global $conn;
    
    // If connection is not available, include config
    if (!isset($conn)) {
        require_once __DIR__ . '/config.php';
    }
    
    // Skip for AJAX requests
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        return;
    }
    
    try {
        // First check if user exists
        $check_sql = "SELECT id FROM users WHERE id = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("i", $user_id);
        $check_stmt->execute();
        if ($check_stmt->get_result()->num_rows === 0) {
            // User doesn't exist, clear session and redirect to login
            session_destroy();
            if (!headers_sent()) {
                header("Location: " . SITE_URL . "/public/login.php?error=account_deleted");
                exit;
            } else {
                echo "<script>window.location.href = '" . SITE_URL . "/public/login.php?error=account_deleted';</script>";
                exit;
            }
    }
    
    // Check if trial has expired
    if (isTrialExpired($user_id)) {
        // Get current page
        $current_page = basename($_SERVER['PHP_SELF']);
        
        // Allow access to upgrade page and essential endpoints
        $allowed_pages = ['upgrade.php', 'logout.php'];
        
        // Redirect to upgrade page if not on an allowed page
        if (!in_array($current_page, $allowed_pages)) {
            // Update user status to 'expired' if not already set
            $status_sql = "UPDATE users SET status = 'expired' WHERE id = ? AND status = 'trial' AND trial_end_date < NOW()";
            $status_stmt = $conn->prepare($status_sql);
            $status_stmt->bind_param("i", $user_id);
            $status_stmt->execute();
            
                // Try PHP redirect if headers haven't been sent
                if (!headers_sent()) {
            header("Location: " . SITE_URL . "/dashboard/upgrade.php");
                    exit;
                } else {
                    // Use JavaScript redirect as fallback
                    echo "<script>window.location.href = '" . SITE_URL . "/dashboard/upgrade.php';</script>";
                    exit;
                }
            }
        }
    } catch (Exception $e) {
        error_log("Error enforcing trial restrictions: " . $e->getMessage());
        // In case of error, redirect to login page
        if (!headers_sent()) {
            header("Location: " . SITE_URL . "/public/login.php?error=system_error");
            exit;
        } else {
            echo "<script>window.location.href = '" . SITE_URL . "/public/login.php?error=system_error';</script>";
            exit;
        }
    }
}

/**
 * Add trial end date to new user during registration
 * 
 * @param int $user_id The user ID to set trial for
 * @return bool True if successful, false otherwise
 */
function addTrialToNewUser($user_id) {
    return setTrialEndDate($user_id, 7);
}
