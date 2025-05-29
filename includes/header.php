<?php
// includes/header.php
// This file contains the header content and starts the HTML document

// Ensure config.php is included if not already
if (!defined('SITE_URL')) {
    require_once __DIR__ . '/config.php';
}

// User data from session (if available)
$userName = isset($_SESSION['user_first_name']) ? ($_SESSION['user_first_name'] . ' ' . ($_SESSION['user_last_name'] ?? '')) : '';
$userProfileImage = $_SESSION['user_profile_image'] ?? 'https://via.placeholder.com/40x40/6366f1/ffffff?text=U';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="<?php echo SITE_URL; ?>/public/css/style.css" rel="stylesheet">
    <link href="<?php echo SITE_URL; ?>/public/css/animations.css" rel="stylesheet">
    <!-- Custom JavaScript -->
    <script src="<?php echo SITE_URL; ?>/public/js/animations.js" defer></script>
    <script src="<?php echo SITE_URL; ?>/public/js/theme-toggle.js" defer></script>
</head>
<body>
    <header class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container">
            <a class="navbar-brand" href="<?php echo SITE_URL; ?>/">
                <img src="<?php echo SITE_URL; ?>/public/assets/img/logo.png" alt="<?php echo SITE_NAME; ?>" height="30">
                <?php echo SITE_NAME; ?>
            </a>
            
            <div class="d-flex align-items-center">
                <!-- Theme Toggle Button -->
                <button type="button" class="btn btn-sm theme-toggle me-2" id="themeToggle" title="Toggle Theme">
                    <i class="fas fa-moon"></i>
                </button>
                
                <?php if ($userName): ?>
                <!-- User is logged in -->
                <div class="dropdown">
                    <button class="btn btn-sm dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <img src="<?php echo htmlspecialchars($userProfileImage); ?>" alt="Profile" class="rounded-circle" width="30" height="30">
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                        <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/dashboard/">Dashboard</a></li>
                        <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/dashboard/profile.php">Profile</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/logout.php">Logout</a></li>
                    </ul>
                </div>
                <?php else: ?>
                <!-- User is not logged in -->
                <a href="<?php echo SITE_URL; ?>/public/login.php" class="btn btn-sm btn-primary me-2">Login</a>
                <a href="<?php echo SITE_URL; ?>/public/register.php" class="btn btn-sm btn-outline-primary">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </header>