<?php
// Include necessary functions or configuration
// require_once '../includes/config.php'; // Assuming config is already included in dashboard.php

// Assuming user data is available from the session or a previous query
// For demonstration, let's assume user name is available as $_SESSION['user_name']
$userName = $_SESSION['user_first_name'] ?? 'User';
$userProfileImage = 'https://via.placeholder.com/30'; // Placeholder for user image
?>

<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container-fluid">
        <!-- Quick Action Buttons - Hidden on small devices, visible on large and up -->
        <div class="d-none d-lg-flex align-items-center me-3">
            <button class="quick-action-button" data-bs-toggle="modal" data-bs-target="#addLeadModal">
                + <span class="button-text">Lead</span> <i class="fas fa-filter"></i>
            </button>
            <button class="quick-action-button" data-bs-toggle="modal" data-bs-target="#addTaskModal">
                + <span class="button-text">Task</span> <i class="fas fa-calendar-alt"></i>
            </button>
            <button class="quick-action-button" data-bs-toggle="modal" data-bs-target="#addNoteModal">
                + <span class="button-text">Note</span> <i class="fas fa-check-square"></i>
            </button>
            <button class="quick-action-button" data-bs-toggle="modal" data-bs-target="#addReminderModal">
                + <span class="button-text">Reminder</span> <i class="fas fa-bell"></i>
            </button>
        </div>

        <div class="d-flex align-items-center ms-auto">
            <!-- Search Icon -->
            <a class="nav-link me-3" href="#">
                <i class="fas fa-search"></i>
            </a>
            
            <!-- Notification Bell Icon -->
            <a class="nav-link notification-badge me-3" href="#">
                <i class="fas fa-bell"></i>
            </a>
            
            <!-- User Profile Dropdown -->
            <div class="dropdown">
                <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <img src="<?php echo $userProfileImage; ?>" alt="User Avatar" class="rounded-circle me-2" width="30" height="30">
                    <span class="d-none d-md-inline-block"><?php echo htmlspecialchars($userName); ?></span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end custom-dropdown-menu animate-dropdown" aria-labelledby="userDropdown">
                    <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/dashboard/profile.php"><i class="fas fa-user me-2"></i>Profile</a></li>
                    <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/dashboard/sync-data.php"><i class="fas fa-sync-alt me-2"></i>Sync Data</a></li>
                    <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/dashboard/billing.php"><i class="fas fa-dollar-sign me-2"></i>Billing</a></li>
                    <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/dashboard/packages.php"><i class="fas fa-box me-2"></i>Packages</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="<?php echo SITE_URL; ?>/dashboard/logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li class="referral-link-section">
                        <a class="dropdown-item d-flex align-items-center" href="#">
                            <i class="fas fa-gift me-2"></i>
                            <span>Set up a Referral Link and earn free Points</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</nav> 