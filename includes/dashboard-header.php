<?php
// Include necessary functions or configuration
// require_once '../includes/config.php'; // Assuming config is already included in dashboard.php

// Assuming user data is available from the session or a previous query
// For demonstration, let's assume user name is available as $_SESSION['user_first_name'] and $_SESSION['user_last_name']
$userName = ($_SESSION['user_first_name'] ?? 'User') . ' ' . ($_SESSION['user_last_name'] ?? '');
$userProfileImage = 'https://via.placeholder.com/30'; // Placeholder for user image

// Get the current page filename
$currentPage = basename($_SERVER['PHP_SELF']);

// Define all modal configurations
$modalConfigs = [
    'lead' => [
        'modalId' => 'addLeadModal',
        'buttonText' => 'Lead',
        'icon' => 'filter',
        'modalFile' => 'add-lead.php'
    ],
    'task' => [
        'modalId' => 'addTaskModal',
        'buttonText' => 'Task',
        'icon' => 'calendar-alt',
        'modalFile' => 'add-task.php'
    ],
    'note' => [
        'modalId' => 'addNoteModal',
        'buttonText' => 'Note',
        'icon' => 'check-square',
        'modalFile' => 'add-note.php'
    ],
    'reminder' => [
        'modalId' => 'addReminderModal',
        'buttonText' => 'Reminder',
        'icon' => 'bell',
        'modalFile' => 'add-reminder.php'
    ]
];
?>

<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container-fluid">
        <!-- Quick Action Buttons - Hidden on small devices, visible on large and up -->
        <div class="d-none d-lg-flex align-items-center me-3">
            <?php foreach ($modalConfigs as $type => $config): ?>
            <button class="quick-action-button" data-bs-toggle="modal" data-bs-target="#<?php echo $config['modalId']; ?>">
                + <span class="button-text"><?php echo $config['buttonText']; ?></span>
                <i class="fas fa-<?php echo $config['icon']; ?>"></i>
            </button>
            <?php endforeach; ?>
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

<?php
// Include all modals on all pages
foreach ($modalConfigs as $config) {
    $modalPath = "../includes/modals/" . $config['modalFile'];
    if (file_exists($modalPath)) {
        include $modalPath;
    }
}
?>

<style>
/* Quick Action Button Styles */
.quick-action-button {
    background-color: #007bff;
    color: white;
    border: none;
    border-radius: 4px;
    padding: 8px 16px;
    margin-right: 8px;
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.2s ease;
}

.quick-action-button:hover {
    background-color: #0056b3;
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.quick-action-button i {
    font-size: 12px;
}

/* Notification Badge Styles */
.notification-badge {
    position: relative;
}

.notification-badge::after {
    content: '';
    position: absolute;
    top: 0;
    right: 0;
    width: 8px;
    height: 8px;
    background-color: #dc3545;
    border-radius: 50%;
    border: 2px solid #fff;
}

/* Dropdown Menu Animation */
.custom-dropdown-menu {
    animation: dropdownFade 0.2s ease-in-out;
}

@keyframes dropdownFade {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Responsive Adjustments */
@media (max-width: 991.98px) {
    .quick-action-button .button-text {
        display: none;
    }
    .quick-action-button {
        padding: 8px;
        margin-right: 4px;
    }
    .quick-action-button i {
        font-size: 14px;
    }
}

/* Active Button State */
.quick-action-button.active {
    background-color: #0056b3;
    box-shadow: inset 0 2px 4px rgba(0,0,0,0.1);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize all modals
    const modals = {};
    <?php foreach ($modalConfigs as $type => $config): ?>
    modals['<?php echo $type; ?>'] = new bootstrap.Modal(document.getElementById('<?php echo $config['modalId']; ?>'));
    <?php endforeach; ?>

    // Add click handlers for all quick action buttons
    document.querySelectorAll('.quick-action-button').forEach(button => {
        button.addEventListener('click', function() {
            const modalId = this.getAttribute('data-bs-target').substring(1);
            const modalType = Object.keys(modals).find(key => 
                modalConfigs[key].modalId === modalId
            );
            if (modalType && modals[modalType]) {
                modals[modalType].show();
            }
        });
    });
});
</script> 