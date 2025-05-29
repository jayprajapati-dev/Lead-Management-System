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
            line-height: 1.5;
            font-size: 14px;
        }

        /* Modern Header Styles */
        .header-container {
            background-color: #fff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);
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
            max-width: 1400px;
            margin: 0 auto;
            width: 100%;
            height: 100%;
            overflow: visible;
            position: relative;
        }
        
        /* Center section for action buttons */
        .header-center {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            flex-wrap: nowrap;
            padding: 0 10px;
            flex: 1;
        }
        
        /* Left side - Menu toggle and quick actions */
        .header-left {
            display: flex;
            align-items: center;
            flex: 1;
            overflow: visible;
            min-width: 0;
            margin-right: 20px;
            position: relative;
            z-index: 100;
        }
        
        .menu-toggle {
            background: transparent;
            border: none;
            color: #6b7280;
            font-size: 20px;
            cursor: pointer;
            padding: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: color 0.2s ease;
        }
        
        .menu-toggle:hover {
            color: #374151;
        }

        /* Quick Action Buttons Styles */
        .quick-actions {
            display: flex;
            align-items: center;
            margin-left: 20px;
            gap: 12px;
            flex-wrap: nowrap;
            overflow: visible;
            flex: 1;
            position: relative;
            z-index: 10;
        }
        
        /* Media queries for responsive button display */
        @media (max-width: 1200px) and (min-width: 992px) {
            .quick-actions {
                margin-left: 15px;
                gap: 8px;
            }
            
            .action-button {
                padding: 6px 12px;
                font-size: 13px;
            }
        }
        
        @media (max-width: 991px) and (min-width: 768px) {
            .quick-actions {
                margin-left: 10px;
                gap: 6px;
            }
            
            .action-button {
                padding: 5px 10px;
                font-size: 12px;
            }
        }
        
        .action-button {
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #4a3f8c; /* Dark purple/indigo */
            border: none;
            border-radius: 50px; /* Full pill shape */
            color: white;
            font-size: 14px;
            font-weight: 500;
            padding: 8px 18px;
            transition: all 0.2s ease;
            cursor: pointer;
            white-space: nowrap;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        .action-button:hover {
            background-color: #5a4da6; /* Slightly lighter on hover */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }
        
        .action-button i {
            font-size: 14px;
            margin-right: 6px;
        }
        
        .action-button i.ml-1 {
            margin-left: 6px;
            margin-right: 0;
        }

        .quick-action-btn .btn-text {
            font-weight: 500;
        }

        /* Header Right Section */
        .header-right {
            display: flex;
            align-items: center;
            gap: 16px;
            flex-shrink: 0;
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
            color: #6b7280;
            cursor: pointer;
            transition: all 0.2s ease;
            position: relative;
            padding: 0;
        }

        .header-icon:hover {
            color: #374151;
            background-color: #f9fafb;
        }

        .header-icon i {
            font-size: 18px;
        }
        
        /* Notification Badge */
        .notification-badge {
            position: absolute;
            top: 4px;
            right: 4px;
            background-color: #ef4444;
            color: white;
            border-radius: 50%;
            font-size: 10px;
            width: 16px;
            height: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }

        .notification-btn {
            position: relative;
        }

        /* User Profile Styling */
        .user-profile {
            position: relative;
            margin-left: 8px;
        }
        
        .profile-button {
            background: transparent;
            border: none;
            padding: 0;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
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
            border: 2px solid #f3f4f6;
        }
        
        .status-indicator {
            position: absolute;
            bottom: 0;
            right: 0;
            width: 10px;
            height: 10px;
            background-color: #10b981; /* Green for online status */
            border-radius: 50%;
            border: 2px solid white;
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
            min-width: 36px; /* Ensure minimum width on mobile */
            justify-content: center; /* Center the avatar on mobile */
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
            display: block; /* Ensure it's always displayed */
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
            top: calc(100% + 5px);
            right: 0;
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            width: 240px;
            padding: 8px 0;
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

        /* Sidebar Toggle Button in Header */
        .sidebar-toggle {
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 8px;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            margin-right: 16px;
            flex-shrink: 0;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            transition: all 0.2s ease;
            position: relative; /* Ensure proper stacking */
            z-index: 1010; /* Higher than sidebar but lower than overlay */
        }
        
        .sidebar-toggle:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }
        
        .sidebar-toggle:active {
            transform: translateY(0);
        }
        
        .sidebar-toggle i {
            font-size: 18px;
        }
        
        /* Responsive Design */
        @media (max-width: 991.98px) {
            .header-container {
                padding: 0 16px;
                height: 50px; /* Reduced height for header */
            }

            .header-content {
                flex-wrap: nowrap;
                justify-content: space-between;
                align-items: center;
                height: 100%;
            }

            .quick-actions {
                display: none; /* Hide quick action buttons on mobile */
            }
            
            .profile-name {
                display: none;
            }
            
            .profile-dropdown-icon {
                display: none;
            }
            
            .icon-btn {
                width: 36px;
                height: 36px;
                flex-shrink: 0;
            }
            
            .header-right {
                gap: 8px;
                display: flex;
                align-items: center;
                justify-content: flex-end;
            }

            .quick-action-btn {
                padding: 6px 8px;
                min-width: 32px;
                flex-shrink: 0;
            }

            .quick-action-btn .btn-text {
                display: none;
            }
            
            /* Ensure dropdown menu doesn't get cut off */
            .dropdown-menu {
                right: 0;
                left: auto;
                width: 280px;
                max-width: 90vw;
                position: absolute;
            }
            
            /* Ensure user profile is always visible */
            .user-profile {
                position: relative;
                display: flex;
                align-items: center;
                min-width: 36px;
                flex-shrink: 0;
            }
        }
        
        /* Extra small devices */
        @media (max-width: 575.98px) {
            .header-container {
                padding: 0 10px;
                height: 45px; /* Even smaller height */
            }
            
            .header-content {
                align-items: center; /* Ensure vertical centering */
                height: 100%;
            }
            
            .quick-actions {
                display: none; /* Hide on smallest screens */
            }
            
            .header-right {
                gap: 4px;
                min-width: 120px; /* Ensure minimum width for icons */
                justify-content: flex-end;
                align-items: center; /* Ensure vertical centering */
                height: 100%; /* Full height */
                padding: 0; /* Remove padding */
                margin: 0; /* Remove margin */
            }
            
            .icon-btn {
                width: 32px;
                height: 32px;
                padding: 0; /* Remove padding */
            }
            
            .profile-avatar {
                width: 28px;
                height: 28px;
                min-width: 28px; /* Prevent shrinking */
                margin: 0; /* Remove any margin */
                padding: 0; /* Remove padding */
            }
            
            /* Ensure user profile is always visible */
            .user-profile {
                position: relative;
                display: flex;
                align-items: center;
                justify-content: center;
                min-width: 36px;
                height: 100%; /* Full height */
                padding: 0; /* Remove padding */
                margin: 0; /* Remove margin */
            }
            
            /* Adjust sidebar toggle for smaller header */
            .sidebar-toggle {
                width: 36px;
                height: 36px;
                margin-right: 10px;
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
        <!-- Left Side - Only Hamburger Menu -->
        <div class="header-left">
            <button class="menu-toggle" id="sidebarToggle" aria-label="Toggle Menu">
                <i class="fas fa-bars"></i>
            </button>
        </div>
        
        <!-- Center - Quick Action Buttons (visible on desktop/tablet only) -->
        <div class="header-center d-none d-md-flex">
            <button type="button" class="action-button" data-bs-toggle="modal" data-bs-target="#addLeadModal">
                <i class="fas fa-plus"></i> Lead <i class="fas fa-filter ml-1"></i>
            </button>
            
            <button type="button" class="action-button" data-bs-toggle="modal" data-bs-target="#addTaskModal">
                <i class="fas fa-plus"></i> Task <i class="fas fa-calendar-alt ml-1"></i>
            </button>
            
            <button type="button" class="action-button" data-bs-toggle="modal" data-bs-target="#addNoteModal">
                <i class="fas fa-plus"></i> Note <i class="fas fa-edit ml-1"></i>
            </button>
            
            <button type="button" class="action-button" data-bs-toggle="modal" data-bs-target="#addReminderModal">
                <i class="fas fa-plus"></i> Reminder <i class="fas fa-bell ml-1"></i>
            </button>
        </div>

        <!-- Right Side Navigation -->
        <div class="header-right">
            <!-- Search Icon -->
            <button type="button" class="header-icon" id="searchBtn" title="Search">
                <i class="fas fa-search"></i>
            </button>

            <!-- Theme Toggle -->
            <button type="button" class="header-icon" id="themeToggle" title="Toggle Theme">
                <i class="fas fa-moon"></i>
            </button>
            
            <!-- Notifications -->
            <button type="button" class="header-icon" id="notificationBtn" title="Notifications">
                <i class="fas fa-bell"></i>
                <span class="notification-badge">0</span>
            </button>

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

    // Copy/Duplicate functionality
    document.getElementById('copyBtn').addEventListener('click', function() {
        // Add your copy functionality here
        console.log('Copy clicked');
    });

    // Notification functionality
    document.getElementById('notificationBtn').addEventListener('click', function() {
        // Add your notification functionality here
        console.log('Notifications clicked');
    });
    
    // Sidebar toggle functionality
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('sidebarMenu');
    const sidebarOverlay = document.getElementById('sidebarOverlay');
    
    sidebarToggle.addEventListener('click', function(e) {
        e.stopPropagation();
        sidebar.classList.toggle('show');
        sidebarOverlay.classList.toggle('show');
        document.body.classList.toggle('sidebar-open');
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