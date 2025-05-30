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
            /* Base colors */
            --primary-color: #6366f1;
            --primary-dark: #4f46e5;
            --primary-light: #818cf8;
            --success-color: #10b981;
            --danger-color: #ef4444;
            --warning-color: #f59e0b;
            
            /* Light theme colors (default) */
            --bg-main: #ffffff;
            --bg-secondary: #f8fafc;
            --bg-tertiary: #f1f5f9;
            --bg-sidebar: #ffffff;
            --bg-card: #ffffff;
            --bg-dropdown: #ffffff;
            --bg-input: #ffffff;
            --bg-button: #6366f1;
            --bg-hover: #f1f5f9;
            
            --text-primary: #1e293b;
            --text-secondary: #64748b;
            --text-tertiary: #94a3b8;
            --text-on-primary: #ffffff;
            --text-on-success: #ffffff;
            --text-on-danger: #ffffff;
            --text-on-warning: #ffffff;
            
            --border-color: #e2e8f0;
            --border-secondary: #f1f5f9;
            --divider-color: #e2e8f0;
            
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
        
        /* Dark theme colors */
        [data-theme="dark"] {
            --bg-main: #121212;
            --bg-secondary: #1e1e1e;
            --bg-tertiary: #2d2d2d;
            --bg-sidebar: #1a1a1a;
            --bg-card: #1e1e1e;
            --bg-dropdown: #2d2d2d;
            --bg-input: #2d2d2d;
            --bg-button: #4f46e5;
            --bg-hover: #3a3a3a;
            
            --text-primary: #f1f5f9;
            --text-secondary: #cbd5e1;
            --text-tertiary: #94a3b8;
            --text-on-primary: #ffffff;
            --text-on-success: #ffffff;
            --text-on-danger: #ffffff;
            --text-on-warning: #ffffff;
            
            --border-color: #3a3a3a;
            --border-secondary: #3a3a3a;
            --divider-color: #3a3a3a;
            
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.3);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.3), 0 2px 4px -1px rgba(0, 0, 0, 0.2);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.3), 0 4px 6px -2px rgba(0, 0, 0, 0.2);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: var(--bg-main);
            color: var(--text-primary);
            line-height: 1.5;
            font-size: 14px;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        /* Modern Header Styles */
        .header-container {
            background-color: var(--bg-main);
            box-shadow: var(--shadow-sm);
            position: sticky;
            top: 0;
            z-index: 1000;
            width: 100%;
            padding: 10px 20px;
            height: auto;
            min-height: 60px;
            overflow: visible;
        }
        
        .header-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 100%;
            max-width: 1400px;
            margin: 0 auto;
            position: relative;
        }
        
        .header-left {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-shrink: 0;
        }
        
        .header-right {
            display: flex;
            align-items: center;
            gap: 16px;
            flex-shrink: 0;
        }
        
        .menu-toggle {
            display: flex;
            align-items: center;
            justify-content: center;
            background: transparent;
            border: none;
            color: var(--text-secondary);
            font-size: 20px;
            cursor: pointer;
            padding: 8px;
            margin-right: 10px;
            transition: color 0.2s ease;
        }
        
        .menu-toggle:focus {
            outline: none;
            color: var(--text-secondary) !important;
            box-shadow: none !important;
            border: none !important;
        }
        
        /* Action Buttons */
        .action-button {
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: var(--bg-button);
            border: none;
            border-radius: 50px; /* Full pill shape */
            color: var(--text-on-primary);
            padding: 8px 16px;
            font-size: 13px;
            cursor: pointer;
            transition: all 0.2s ease, background-color 0.3s ease, color 0.3s ease;
            white-space: nowrap;
            box-shadow: var(--shadow-sm);
            margin: 0 5px;
        }
        
        .action-button i {
            margin-right: 6px;
            font-size: 12px;
        }
        
        .action-button i.ml-1 {
            margin-left: 4px;
            margin-right: 0;
            opacity: 0.7;
        }
        
        .action-button:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }
        
        .action-button:active {
            transform: translateY(0);
        }

        /* Header Icons */
        .header-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            border-radius: 6px;
            background: transparent;
            border: none;
            color: var(--text-secondary);
            font-size: 16px;
            cursor: pointer;
            transition: all 0.2s ease, color 0.3s ease, background-color 0.3s ease;
            position: relative;
        }
        
        .header-icon:hover {
            background-color: var(--bg-hover);
            color: var(--text-primary);
        }
        
        .header-icon:active {
            transform: scale(0.95);
        }
        
        /* Notification Badge */
        .notification-badge {
            position: absolute;
            top: 4px;
            right: 4px;
            background-color: var(--danger-color);
            color: var(--text-on-danger);
            font-size: 10px;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            border: 2px solid var(--bg-main);
            transition: background-color 0.3s ease, border-color 0.3s ease;
        }

        .notification-container {
            position: relative;
        }
        
        .notification-dropdown {
            width: 320px;
            padding: 0;
            max-height: 400px;
            overflow-y: auto;
        }
        
        .dropdown-header {
            padding: 12px 16px;
            border-bottom: 1px solid var(--divider-color);
            font-weight: 500;
        }
        
        .notification-list {
            max-height: 300px;
            overflow-y: auto;
        }
        
        .dropdown-footer {
            padding: 10px 16px;
            border-top: 1px solid var(--divider-color);
            text-align: center;
        }
        
        .empty-notifications {
            color: var(--text-secondary);
            padding: 20px 0;
        }

        /* User Profile Styling */
        .user-profile {
            position: relative;
            margin-left: 8px;
        }
        
        .profile-button {
            background: transparent;
            border: none;
            padding: 6px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: background-color 0.2s ease;
        }
        
        .profile-button:hover {
            background-color: var(--bg-hover);
        }
        
        .avatar-container {
            position: relative;
            width: 36px;
            height: 36px;
        }
        
        .profile-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--border-secondary);
            transition: border-color 0.3s ease;
        }
        
        .status-indicator {
            position: absolute;
            bottom: 0;
            right: 0;
            width: 10px;
            height: 10px;
            background-color: var(--success-color); /* Green for online status */
            border-radius: 50%;
            border: 2px solid var(--bg-main);
            transition: border-color 0.3s ease, background-color 0.3s ease;
        }

        /* Dropdown Menu */
        .dropdown-menu {
            position: absolute;
            top: calc(100% + 5px);
            right: 0;
            background: var(--bg-dropdown);
            border-radius: 10px;
            box-shadow: var(--shadow-lg);
            width: 240px;
            padding: 8px 0;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: opacity 0.2s ease, visibility 0.2s ease, transform 0.2s ease, background-color 0.3s ease, box-shadow 0.3s ease;
            z-index: 1000;
            pointer-events: none; /* Prevent interaction when hidden */
        }

        .dropdown-menu.show {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
            pointer-events: auto; /* Allow interaction when visible */
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
            background: var(--bg-hover);
            color: var(--text-primary);
        }

        .dropdown-item i {
            width: 16px;
            font-size: 14px;
            color: var(--text-secondary);
        }

        .dropdown-divider {
            height: 1px;
            background: var(--divider-color);
            margin: 8px 0;
            border: none;
            transition: background-color 0.3s ease;
        }

        /* Responsive Design */
        @media (max-width: 991.98px) {
            .header-container {
                padding: 0 16px;
                min-height: 56px;
            }
            
            .header-left {
                flex-wrap: wrap;
                justify-content: flex-start;
            }
            
            .action-button {
                padding: 6px 12px;
                font-size: 12px;
                margin: 2px;
            }
            
            .header-icon {
                width: 32px;
                height: 32px;
                padding: 0;
            }
            
            .profile-avatar {
                width: 28px;
                height: 28px;
                min-width: 28px;
                margin: 0;
                padding: 0;
            }
            
            .user-profile {
                position: relative;
                display: flex;
                align-items: center;
                justify-content: center;
                min-width: 36px;
                height: 100%;
                padding: 0;
                margin: 0;
            }
        }

        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                align-items: stretch;
                padding: 10px 0;
            }
            
            .header-left {
                width: 100%;
                justify-content: center;
                margin-bottom: 10px;
            }
            
            .header-right {
                justify-content: flex-end;
                width: 100%;
            }
            
            .action-button {
                padding: 5px 10px;
                font-size: 11px;
                flex: 1;
                text-align: center;
                justify-content: center;
            }
        }
        
        @media (max-width: 480px) {
            .action-button span {
                display: none;
            }
            
            .action-button i {
                margin-right: 0;
                font-size: 14px;
            }
            
            .action-button {
                padding: 8px;
                width: 36px;
                height: 36px;
                border-radius: 50%;
            }
        }
    </style>
</head>
<body>

<header class="header-container">
    <div class="header-content">
        <!-- Left Side - Quick Action Buttons -->
        <div class="header-left">
            <button type="button" class="action-button" data-bs-toggle="modal" data-bs-target="#addLeadModal">
                <i class="fas fa-plus"></i> <span>Lead</span>
            </button>
            
            <button type="button" class="action-button" data-bs-toggle="modal" data-bs-target="#addTaskModal">
                <i class="fas fa-plus"></i> <span>Task</span>
            </button>
            
            <button type="button" class="action-button" data-bs-toggle="modal" data-bs-target="#addNoteModal">
                <i class="fas fa-plus"></i> <span>Note</span>
            </button>
            
            <button type="button" class="action-button" data-bs-toggle="modal" data-bs-target="#addReminderModal">
                <i class="fas fa-plus"></i> <span>Reminder</span>
            </button>
        </div>

        <!-- Right Side Navigation -->
        <div class="header-right">
            <!-- Menu Toggle for Mobile -->
            <button class="menu-toggle d-lg-none" id="sidebarToggle" aria-label="Toggle Menu">
                <i class="fas fa-bars"></i>
            </button>
            
            <!-- Search Icon -->
            <button type="button" class="header-icon" id="searchBtn" title="Search">
                <i class="fas fa-search"></i>
            </button>

            <!-- Theme Toggle -->
            <button type="button" class="header-icon theme-toggle" id="themeToggle" title="Toggle Theme">
                <i class="fas fa-moon"></i>
            </button>
            
            <!-- Notifications -->
            <div class="notification-container" id="notificationContainer">
                <button type="button" class="header-icon" id="notificationBtn" title="Notifications">
                    <i class="fas fa-bell"></i>
                    <span class="notification-badge">0</span>
                </button>
                
                <div class="dropdown-menu notification-dropdown" id="notificationDropdown">
                    <div class="dropdown-header d-flex justify-content-between align-items-center">
                        <span>Notifications</span>
                        <a href="#" class="text-primary">Mark all as read</a>
                    </div>
                    <div class="notification-list">
                        <!-- Empty state -->
                        <div class="empty-notifications text-center p-3">
                            <i class="fas fa-bell-slash fa-2x text-muted mb-2"></i>
                            <p class="mb-0">No new notifications</p>
                        </div>
                        <!-- Notifications will be dynamically added here -->
                    </div>
                    <div class="dropdown-footer">
                        <a href="<?php echo SITE_URL ?? ''; ?>/dashboard/notifications.php" class="text-center d-block">View all notifications</a>
                    </div>
                </div>
            </div>

            <!-- User Profile -->
            <div class="user-profile" id="userProfile">
                <button class="profile-button" id="profileTrigger">
                    <div class="avatar-container">
                        <img src="<?php echo htmlspecialchars($userProfileImage); ?>" 
                             alt="Profile" class="profile-avatar">
                        <span class="status-indicator"></span>
                    </div>
                </button>

                <div class="dropdown-menu" id="profileDropdown">
                    <a href="<?php echo SITE_URL ?? ''; ?>/dashboard/profile.php" class="dropdown-item">
                        <i class="fas fa-user"></i>
                        <span>Profile</span>
                    </a>
                    <a href="<?php echo SITE_URL ?? ''; ?>/dashboard/settings.php" class="dropdown-item">
                        <i class="fas fa-cog"></i>
                        <span>Settings</span>
                    </a>
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

<!-- Sidebar Overlay -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Function to close all dropdowns
    function closeAllDropdowns() {
        const dropdowns = document.querySelectorAll('.dropdown-menu');
        const containers = document.querySelectorAll('.user-profile, .notification-container');
        
        dropdowns.forEach(dropdown => dropdown.classList.remove('show'));
        containers.forEach(container => container.classList.remove('show'));
    }
    
    // Profile dropdown functionality
    const profileTrigger = document.getElementById('profileTrigger');
    const profileDropdown = document.getElementById('profileDropdown');
    const userProfile = document.getElementById('userProfile');

    if (profileTrigger && profileDropdown && userProfile) {
        profileTrigger.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            // Close other dropdowns first
            const notificationContainer = document.getElementById('notificationContainer');
            const notificationDropdown = document.getElementById('notificationDropdown');
            if (notificationContainer && notificationDropdown) {
                notificationContainer.classList.remove('show');
                notificationDropdown.classList.remove('show');
            }
            
            // Toggle profile dropdown
            userProfile.classList.toggle('show');
            profileDropdown.classList.toggle('show');
        });
    }

    // Close dropdowns when clicking outside
    document.addEventListener('click', function(e) {
        const userProfile = document.getElementById('userProfile');
        const notificationContainer = document.getElementById('notificationContainer');
        
        if (userProfile && !userProfile.contains(e.target)) {
            userProfile.classList.remove('show');
            document.getElementById('profileDropdown')?.classList.remove('show');
        }
        
        if (notificationContainer && !notificationContainer.contains(e.target)) {
            notificationContainer.classList.remove('show');
            document.getElementById('notificationDropdown')?.classList.remove('show');
        }
    });

    // Search functionality
    const searchBtn = document.getElementById('searchBtn');
    if (searchBtn) {
        searchBtn.addEventListener('click', function() {
            // Implement search functionality here
            console.log('Search clicked');
        });
    }
    
    // Notification functionality
    const notificationBtn = document.getElementById('notificationBtn');
    const notificationDropdown = document.getElementById('notificationDropdown');
    const notificationContainer = document.getElementById('notificationContainer');
    
    if (notificationBtn && notificationDropdown && notificationContainer) {
        notificationBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            // Close other dropdowns first
            const userProfile = document.getElementById('userProfile');
            const profileDropdown = document.getElementById('profileDropdown');
            if (userProfile && profileDropdown) {
                userProfile.classList.remove('show');
                profileDropdown.classList.remove('show');
            }
            
            // Toggle notification dropdown
            notificationContainer.classList.toggle('show');
            notificationDropdown.classList.toggle('show');
        });
    }
    
    // Mobile sidebar functionality
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('sidebarMenu');
    const sidebarOverlay = document.getElementById('sidebarOverlay');
    
    if (sidebarToggle && sidebar && sidebarOverlay) {
        sidebarToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            sidebar.classList.toggle('show');
            sidebarOverlay.classList.toggle('show');
            document.body.classList.toggle('sidebar-open');
        });
        
        sidebarOverlay.addEventListener('click', function() {
            sidebar.classList.remove('show');
            sidebarOverlay.classList.remove('show');
            document.body.classList.remove('sidebar-open');
        });
    }
});
</script>

<?php
// Include the modal forms
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
?>
