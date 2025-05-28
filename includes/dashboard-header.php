<?php
// Include necessary functions or configuration
// require_once '../includes/config.php';

// User data from session
$userName = ($_SESSION['user_first_name'] ?? 'User') . ' ' . ($_SESSION['user_last_name'] ?? '');
$userProfileImage = $_SESSION['user_profile_image'] ?? 'https://via.placeholder.com/40x40/6366f1/ffffff?text=U';

// Get the current page filename
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #6366f1;
            --primary-dark: #4f46e5;
            --secondary-color: #f8fafc;
            --border-color: #e2e8f0;
            --text-primary: #1e293b;
            --text-secondary: #64748b;
            --success-color: #10b981;
            --danger-color: #ef4444;
            --warning-color: #f59e0b;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #f8fafc;
            color: var(--text-primary);
            line-height: 1.6;
        }

        /* Header Styles */
        .header-container {
            background: #ffffff;
            border-bottom: 1px solid var(--border-color);
            padding: 12px 24px;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
        }

        .header-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            max-width: 1400px;
            margin: 0 auto;
        }

        /* Quick Action Buttons */
        .quick-actions {
            display: flex;
            gap: 12px;
            align-items: center;
            flex-wrap: nowrap;
        }

        .quick-action-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 16px;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            min-height: 40px;
            white-space: nowrap;
            flex-shrink: 0;
        }

        .quick-action-btn:hover {
            background: var(--primary-dark);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
            color: white;
        }

        .quick-action-btn:active {
            transform: translateY(0);
        }

        .quick-action-btn i {
            font-size: 12px;
            opacity: 0.9;
        }

        .quick-action-btn .btn-text {
            font-weight: 500;
        }

        /* Header Right Section */
        .header-right {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        /* Icon Buttons */
        .icon-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 8px;
            background: transparent;
            border: none;
            color: var(--text-secondary);
            cursor: pointer;
            transition: all 0.2s ease;
            position: relative;
        }

        .icon-btn:hover {
            background: var(--secondary-color);
            color: var(--text-primary);
        }

        .icon-btn i {
            font-size: 18px;
        }

        /* Notification Badge */
        .notification-btn {
            position: relative;
        }

        .notification-badge {
            position: absolute;
            top: 8px;
            right: 8px;
            width: 8px;
            height: 8px;
            background: var(--danger-color);
            border-radius: 50%;
            border: 2px solid white;
        }

        /* User Profile Dropdown */
        .user-profile {
            position: relative;
        }

        .profile-trigger {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 6px;
            border-radius: 8px;
            cursor: pointer;
            transition: background 0.2s ease;
            text-decoration: none;
            color: var(--text-primary);
        }

        .profile-trigger:hover {
            background: var(--secondary-color);
            color: var(--text-primary);
        }

        .profile-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--border-color);
        }

        .profile-name {
            font-size: 14px;
            font-weight: 500;
            color: var(--text-primary);
        }

        .profile-dropdown-icon {
            font-size: 12px;
            color: var(--text-secondary);
            transition: transform 0.2s ease;
        }

        .user-profile.show .profile-dropdown-icon {
            transform: rotate(180deg);
        }

        /* Dropdown Menu */
        .dropdown-menu {
            position: absolute;
            top: 100%;
            right: 0;
            min-width: 240px;
            background: white;
            border: 1px solid var(--border-color);
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            padding: 8px 0;
            margin-top: 8px;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.2s ease;
            z-index: 1000;
        }

        .dropdown-menu.show {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .dropdown-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            color: var(--text-primary);
            text-decoration: none;
            font-size: 14px;
            transition: background 0.2s ease;
        }

        .dropdown-item:hover {
            background: var(--secondary-color);
            color: var(--text-primary);
        }

        .dropdown-item i {
            width: 16px;
            font-size: 14px;
            color: var(--text-secondary);
        }

        .dropdown-divider {
            height: 1px;
            background: var(--border-color);
            margin: 8px 0;
            border: none;
        }

        .referral-section {
            padding: 12px 16px;
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
            margin: 8px;
            border-radius: 8px;
            border: 1px solid #bae6fd;
        }

        .referral-section .dropdown-item {
            padding: 0;
            background: transparent;
            font-size: 13px;
            color: #0369a1;
        }

        .referral-section .dropdown-item:hover {
            background: transparent;
            color: #0284c7;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .header-container {
                padding: 8px 16px;
            }

            .header-content {
                flex-wrap: nowrap;
            }

            .quick-actions {
                gap: 8px;
                flex-shrink: 0;
            }

            .quick-action-btn {
                padding: 8px 12px;
                min-height: 36px;
                font-size: 13px;
            }

            .quick-action-btn .btn-text {
                display: none;
            }

            .profile-name {
                display: none;
            }

            .icon-btn {
                width: 36px;
                height: 36px;
            }

            .header-right {
                gap: 8px;
            }
        }

        @media (max-width: 992px) {
            .quick-action-btn .btn-text {
                font-size: 13px;
            }

            .quick-action-btn {
                padding: 9px 14px;
            }
        }

        @media (max-width: 480px) {
            .quick-actions {
                gap: 4px;
            }

            .quick-action-btn {
                padding: 6px 8px;
                min-width: 32px;
            }

            .quick-action-btn .btn-text {
                display: none;
            }
        }
    </style>
</head>
<body>

<header class="header-container">
    <div class="header-content">
        <!-- Quick Action Buttons -->
        <div class="quick-actions">
            <button type="button" class="quick-action-btn" data-bs-toggle="modal" data-bs-target="#addLeadModal">
                + <span class="btn-text">Lead</span> 
                <i class="fas fa-filter"></i>
            </button>
            <button type="button" class="quick-action-btn" data-bs-toggle="modal" data-bs-target="#addTaskModal">
                + <span class="btn-text">Task</span> 
                <i class="fas fa-calendar-alt"></i>
            </button>
            <button type="button" class="quick-action-btn" data-bs-toggle="modal" data-bs-target="#addNoteModal">
                + <span class="btn-text">Note</span> 
                <i class="fas fa-sticky-note"></i>
            </button>
            <button type="button" class="quick-action-btn" data-bs-toggle="modal" data-bs-target="#addReminderModal">
                + <span class="btn-text">Reminder</span> 
                <i class="fas fa-bell"></i>
            </button>
        </div>

        <!-- Header Right Section -->
        <div class="header-right">
            <!-- Search Button -->
            <button type="button" class="icon-btn" id="searchBtn" title="Search">
                <i class="fas fa-search"></i>
            </button>

            <!-- Theme Toggle -->
            <button type="button" class="icon-btn" id="themeToggle" title="Toggle Theme">
                <i class="fas fa-moon"></i>
            </button>

            <!-- Notifications -->
            <button type="button" class="icon-btn notification-btn" id="notificationBtn" title="Notifications">
                <i class="fas fa-bell"></i>
                <span class="notification-badge"></span>
            </button>

            <!-- User Profile Dropdown -->
            <div class="user-profile" id="userProfile">
                <a href="#" class="profile-trigger" id="profileTrigger">
                    <img src="<?php echo htmlspecialchars($userProfileImage); ?>" 
                         alt="Profile" class="profile-avatar">
                    <span class="profile-name"><?php echo htmlspecialchars($userName); ?></span>
                    <i class="fas fa-chevron-down profile-dropdown-icon"></i>
                </a>

                <div class="dropdown-menu" id="profileDropdown">
                    <a href="<?php echo SITE_URL ?? ''; ?>/dashboard/profile.php" class="dropdown-item">
                        <i class="fas fa-user"></i>
                        <span>Profile</span>
                    </a>
                    <a href="<?php echo SITE_URL ?? ''; ?>/dashboard/sync-data.php" class="dropdown-item">
                        <i class="fas fa-sync-alt"></i>
                        <span>Sync Data</span>
                    </a>
                    <a href="<?php echo SITE_URL ?? ''; ?>/dashboard/billing.php" class="dropdown-item">
                        <i class="fas fa-credit-card"></i>
                        <span>Billing</span>
                    </a>
                    <a href="<?php echo SITE_URL ?? ''; ?>/dashboard/packages.php" class="dropdown-item">
                        <i class="fas fa-box"></i>
                        <span>Packages</span>
                    </a>
                    
                    <hr class="dropdown-divider">
                    
                    <a href="<?php echo SITE_URL ?? ''; ?>/dashboard/settings.php" class="dropdown-item">
                        <i class="fas fa-cog"></i>
                        <span>Settings</span>
                    </a>
                    <a href="<?php echo SITE_URL ?? ''; ?>/dashboard/help.php" class="dropdown-item">
                        <i class="fas fa-question-circle"></i>
                        <span>Help & Support</span>
                    </a>
                    
                    <hr class="dropdown-divider">
                    
                    <div class="referral-section">
                        <a href="<?php echo SITE_URL ?? ''; ?>/dashboard/referral.php" class="dropdown-item">
                            <i class="fas fa-gift"></i>
                            <span>Set up Referral Link & earn free Points</span>
                        </a>
                    </div>
                    
                    <hr class="dropdown-divider">
                    
                    <a href="<?php echo SITE_URL ?? ''; ?>/dashboard/logout.php" class="dropdown-item">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</header>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Profile dropdown functionality
    const profileTrigger = document.getElementById('profileTrigger');
    const profileDropdown = document.getElementById('profileDropdown');
    const userProfile = document.getElementById('userProfile');

    profileTrigger.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        userProfile.classList.toggle('show');
        profileDropdown.classList.toggle('show');
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!userProfile.contains(e.target)) {
            userProfile.classList.remove('show');
            profileDropdown.classList.remove('show');
        }
    });

    // Theme toggle functionality
    const themeToggle = document.getElementById('themeToggle');
    const themeIcon = themeToggle.querySelector('i');
    
    themeToggle.addEventListener('click', function() {
        document.body.classList.toggle('dark-theme');
        
        if (document.body.classList.contains('dark-theme')) {
            themeIcon.className = 'fas fa-sun';
            localStorage.setItem('theme', 'dark');
        } else {
            themeIcon.className = 'fas fa-moon';
            localStorage.setItem('theme', 'light');
        }
    });

    // Load saved theme
    const savedTheme = localStorage.getItem('theme');
    if (savedTheme === 'dark') {
        document.body.classList.add('dark-theme');
        themeIcon.className = 'fas fa-sun';
    }

    // Search functionality
    document.getElementById('searchBtn').addEventListener('click', function() {
        // Add your search functionality here
        console.log('Search clicked');
    });

    // Notification functionality
    document.getElementById('notificationBtn').addEventListener('click', function() {
        // Add your notification functionality here
        console.log('Notifications clicked');
    });
});
</script>

<?php
// Conditionally include modals based on the current page
switch ($currentPage) {
    case 'dashboard.php':
    case 'leads.php':
    case 'tasks.php':
        // Include all four modals on dashboard, leads, and tasks pages
        if (file_exists('../includes/modals/add-lead.php')) {
            include '../includes/modals/add-lead.php';
        }
        if (file_exists('../includes/modals/add-task.php')) {
            include '../includes/modals/add-task.php';
        }
        if (file_exists('../includes/modals/add-note.php')) {
            include '../includes/modals/add-note.php';
        }
        if (file_exists('../includes/modals/add-reminder.php')) {
            include '../includes/modals/add-reminder.php';
        }
        break;
    case 'notes.php':
        if (file_exists('../includes/modals/add-note.php')) {
            include '../includes/modals/add-note.php';
        }
        break;
    case 'reminders.php':
        if (file_exists('../includes/modals/add-reminder.php')) {
            include '../includes/modals/add-reminder.php';
        }
        break;
    default:
        // No specific modals included by default
        break;
}
?>