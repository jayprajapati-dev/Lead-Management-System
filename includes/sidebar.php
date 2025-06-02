<?php
// Get current page for active state
$current_page = basename($_SERVER['PHP_SELF']);

// Include trial functions if not already included
if (!function_exists('getTrialDaysRemaining')) {
    require_once __DIR__ . '/trial_functions.php';
}

// Get trial information if user is logged in
$trial_days_remaining = 0;
$is_trial_expired = false;
$user_status = '';

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    
    // Get user status
    $status_sql = "SELECT status FROM users WHERE id = ?";
    $status_stmt = $conn->prepare($status_sql);
    $status_stmt->bind_param("i", $user_id);
    $status_stmt->execute();
    $status_result = $status_stmt->get_result();
    
    if ($status_row = $status_result->fetch_assoc()) {
        $user_status = $status_row['status'];
    }
    
    // Get trial days remaining
    $trial_days_remaining = getTrialDaysRemaining($user_id);
    $is_trial_expired = isTrialExpired($user_id);
    
    // Enforce trial restrictions
    enforceTrialRestrictions($user_id);
}
?>
<style>
    :root {
        /* Main colors - Professional Business Theme */
        --primary-color: #2c3e50; /* Dark blue/slate for primary elements */
        --primary-dark: #1a252f; /* Darker shade for gradients */
        --primary-light: #34495e; /* Lighter shade for highlights */
        
        /* Secondary colors */
        --secondary-color: #ecf0f1; /* Light gray background */
        --hover-color: #e0e6e9; /* Subtle hover state background */
        
        /* Accent colors */
        --accent-color: #3498db; /* Accent blue */
        --accent-light: #5dade2; /* Lighter accent */
        --accent-dark: #2980b9; /* Darker accent */
        
        /* Text colors */
        --text-primary: #2c3e50; /* Main text color */
        --text-secondary: #7f8c8d; /* Secondary text */
        --text-light: #95a5a6; /* Lighter text for less emphasis */
        --text-active: #ffffff; /* Text color for active items */
        
        /* Border and shadow */
        --border-color: #dfe4ea;
        --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.05);
        --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.06), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
        
        /* Status colors */
        --success-color: #27ae60;
        --danger-color: #e74c3c;
        --warning-color: #f39c12;
        --info-color: #3498db;
        
        /* Dimensions */
        --sidebar-width: 250px;
        --sidebar-collapsed-width: 60px;
        
        /* Transitions */
        --transition-fast: 0.2s ease;
        --transition-normal: 0.3s ease;
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
    }

    /* Sidebar Container */
    .sidebar {
        background-color: var(--secondary-color);
        padding: 0;
        box-shadow: var(--shadow-md);
        height: 100vh;
        position: fixed;
        top: 0;
        left: 0;
        width: var(--sidebar-width);
        transition: all 0.3s ease;
        z-index: 1000;
        overflow-y: auto;
        display: flex;
        flex-direction: column;
        border-right: 1px solid var(--border-color);
        padding-bottom: 100px; /* Add significant padding at the bottom */
    }

    .sidebar-container.collapsed {
        width: var(--sidebar-collapsed-width);
    }

    /* Mobile Toggle Button */
    .sidebar-toggle {
        display: none;
        position: fixed;
        top: 15px;
        left: 15px;
        z-index: 1001;
        background: var(--primary-color);
        color: var(--text-active);
        border: none;
        border-radius: 10px;
        width: 44px;
        height: 44px;
        cursor: pointer;
        transition: all var(--transition-fast);
        box-shadow: var(--shadow-md);
        align-items: center;
        justify-content: center;
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

    /* Mobile Overlay */
    .sidebar-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(15, 23, 42, 0.6);
        backdrop-filter: blur(2px);
        z-index: 999;
        opacity: 0;
        transition: opacity var(--transition-normal);
        pointer-events: none; /* Don't block clicks by default */
    }

    .sidebar-overlay.show {
        opacity: 1;
        pointer-events: auto; /* Only block clicks when visible */
    }

    /* Sidebar Header */
    .sidebar-header {
        padding: 25px 15px;
        border-bottom: 1px solid var(--border-color);
        margin-bottom: 15px;
        text-align: center;
        position: relative;
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
        color: var(--text-active);
        box-shadow: var(--shadow-sm);
    }
    
    .sidebar-header h4 {
        font-weight: 600;
        font-size: 18px;
        letter-spacing: 0.5px;
        margin: 0;
    }

    .sidebar-header h4 {
        font-size: 18px;
        font-weight: 700;
        margin: 0;
        letter-spacing: 0.5px;
        transition: opacity var(--transition-normal), transform var(--transition-normal);
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
    }

    .sidebar-container.collapsed .sidebar-header h4 {
        opacity: 0;
        transform: translateX(-20px);
    }

    .sidebar-header .logo-icon {
        width: 40px;
        height: 40px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 12px;
        font-size: 20px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    /* Sidebar Content */
    .sidebar-content {
        flex: 1;
        overflow-y: auto;
        padding: 16px 0;
    }

    .sidebar-content::-webkit-scrollbar {
        width: 4px;
    }

    .sidebar-content::-webkit-scrollbar-track {
        background: transparent;
    }

    .sidebar-content::-webkit-scrollbar-thumb {
        background: var(--border-color);
        border-radius: 4px;
    }
    
    .sidebar-content::-webkit-scrollbar-thumb:hover {
        background: var(--text-light);
    }

    /* Navigation Section */
    .nav-section {
        padding: 0 16px;
        margin-bottom: 24px;
    }

    .nav-section-title {
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        color: var(--text-light);
        margin-bottom: 12px;
        padding: 0 12px;
        letter-spacing: 0.5px;
        transition: opacity var(--transition-normal), transform var(--transition-normal);
    }

    .sidebar-container.collapsed .nav-section-title {
        opacity: 0;
        transform: translateX(-20px);
    }

    /* Navigation Links */
    .nav-link {
        display: flex;
        align-items: center;
        padding: 12px 20px;
        color: var(--text-primary);
        text-decoration: none;
        transition: all 0.3s ease;
        border-left: 4px solid transparent;
        position: relative;
        font-weight: 500;
        font-size: 14px;
        margin: 3px 0;
        letter-spacing: 0.2px;
    }

    .nav-link:hover {
        background: var(--hover-color);
        color: var(--primary-color);
        transform: translateX(4px);
    }

    .nav-link.active {
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
        color: #ffffff !important;
        box-shadow: var(--shadow-md);
        font-weight: 500;
    }

    .nav-link.active:hover {
        transform: translateX(0);
        background: linear-gradient(135deg, var(--primary-color) 10%, var(--primary-dark) 90%);
    }

    .nav-link i {
        font-size: 16px;
        margin-right: 12px;
        color: var(--text-secondary);
        transition: all 0.3s ease;
        width: 20px;
        text-align: center;
    }
    
    .nav-link:hover i {
        color: var(--accent-color);
        transform: translateX(2px);
    }
    
    .nav-link.active i {
        color: var(--accent-color);
    }

    .nav-link span {
        font-size: 13px;
        font-weight: 500;
        transition: opacity var(--transition-normal), transform var(--transition-normal);
        white-space: nowrap;
    }

    .sidebar-container.collapsed .nav-link span {
        opacity: 0;
        transform: translateX(-10px);
    }

    .sidebar-container.collapsed .nav-link {
        justify-content: center;
        padding: 12px;
    }

    .sidebar-container.collapsed .nav-link i {
        margin-right: 0;
        font-size: 18px;
    }

    /* Badge/Count */
    .nav-badge {
        background: var(--danger-color);
        color: var(--text-active);
        font-size: 10px;
        font-weight: 600;
        padding: 3px 8px;
        border-radius: 12px;
        margin-left: auto;
        min-width: 20px;
        text-align: center;
        box-shadow: 0 2px 4px rgba(239, 68, 68, 0.3);
        transition: all var(--transition-fast);
    }
    
    .nav-link:hover .nav-badge {
        transform: scale(1.1);
    }

    .sidebar-container.collapsed .nav-badge {
        display: none;
    }

    /* Free Trial Section */
    /* Sidebar Footer */
    .sidebar-footer {
        margin-top: auto;
        padding: 0 15px 15px;
        width: 100%;
    }
    
    .free-trial-section {
        margin: 0;
        margin-bottom: 20px; /* Add bottom margin */
        padding: 20px;
        background: linear-gradient(135deg, #4F46E5 0%, #4338CA 100%);
        color: white;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(79, 70, 229, 0.3);
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .free-trial-section::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(45deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 100%);
        pointer-events: none;
    }

    .trial-icon {
        width: 48px;
        height: 48px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 12px;
        font-size: 24px;
        backdrop-filter: blur(4px);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .trial-title {
        font-size: 16px;
        font-weight: 600;
        margin-bottom: 8px;
        letter-spacing: 0.5px;
    }

    .trial-days {
        font-size: 24px;
        font-weight: 700;
        margin: 8px 0;
        color: #FCD34D;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .trial-text {
        font-size: 13px;
        opacity: 0.9;
        margin-bottom: 12px;
        line-height: 1.4;
    }

    .trial-button {
        background: rgba(255, 255, 255, 0.2);
        color: white;
        border: 1px solid rgba(255, 255, 255, 0.4);
        padding: 8px 20px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 500;
        text-decoration: none;
        transition: all 0.3s ease;
        backdrop-filter: blur(4px);
        width: 100%;
        display: inline-block;
    }

    .trial-button:hover {
        background: rgba(255, 255, 255, 0.3);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    /* Premium section styling */
    .premium-section {
        background: linear-gradient(135deg, #10B981 0%, #059669 100%);
        box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
    }

    /* Expired section styling */
    .expired-section {
        background: linear-gradient(135deg, #EF4444 0%, #DC2626 100%);
        box-shadow: 0 4px 15px rgba(239, 68, 68, 0.3);
    }

    .sidebar-container.collapsed .free-trial-section {
        opacity: 0;
        pointer-events: none;
        transform: scale(0.9) translateX(-10px);
    }

    @media (max-width: 768px) {
        .free-trial-section {
            margin: 10px;
            margin-bottom: 30px; /* Add more bottom margin for mobile */
            padding: 15px;
            position: relative; /* Ensure proper positioning */
            bottom: 0; /* Stick to bottom */
            max-height: none; /* Ensure no height restriction */
            z-index: 10; /* Ensure it's above other elements */
        }

        .trial-icon {
            width: 40px;
            height: 40px;
            font-size: 20px;
        }

        .trial-title {
            font-size: 14px;
        }

        .trial-days {
            font-size: 20px;
        }

        .trial-text {
            font-size: 12px;
        }

        .trial-button {
            padding: 6px 16px;
            font-size: 12px;
        }
    }

    /* Responsive Design */
    @media (max-width: 991.98px) {
        .sidebar-toggle {
            display: flex;
        }
        
        .sidebar {
            transform: translateX(-100%);
            box-shadow: none;
            z-index: 1030;
            width: 280px;
            height: 100vh; /* Full viewport height */
            max-height: none; /* Remove max-height restriction */
            overflow-y: auto; /* Enable scrolling */
            display: flex;
            flex-direction: column;
            padding-bottom: 150px; /* Add significant padding at bottom for mobile */
        }
        
        .sidebar.show {
            transform: translateX(0);
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.2);
        }
        
        .sidebar-overlay {
            display: block;
        }
        
        body.sidebar-open {
            overflow: hidden;
        }
        
        .main-content-wrapper {
            margin-left: 0;
            width: 100%;
        }
        
        .header {
            left: 0;
        }
        
        /* Ensure the free trial section is visible */
        .sidebar-section {
            flex: 1 0 auto; /* Allow it to grow but not shrink */
        }
    }
    
    @media (min-width: 992px) {
        .main-content {
            margin-left: var(--sidebar-width);
            transition: margin-left var(--transition-normal);
        }

        .sidebar-container.collapsed + .main-content {
            margin-left: var(--sidebar-collapsed-width);
        }
    }

    /* Dark Mode Support */
    .dark-theme .sidebar-container {
        background: #1e293b;
        border-right-color: #334155;
    }

    .dark-theme .sidebar-header {
        background: linear-gradient(135deg, #4338ca 0%, #312e81 100%);
    }

    .dark-theme .nav-link {
        color: #cbd5e1;
    }
    
    .dark-theme .nav-link:hover {
        background: #334155;
        color: #f1f5f9;
    }
    
    .dark-theme .nav-link.active {
        background: linear-gradient(135deg, #4338ca 0%, #312e81 100%);
        color: #ffffff;
    }

    /* Animations */
    @keyframes slideIn {
        from {
            transform: translateX(-100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes fadeIn {
        from {
            opacity: 0;
        }
        to {
            opacity: 1;
        }
    }

    .sidebar-container.show {
        animation: slideIn 0.3s ease-out;
    }

    /* Tooltip for collapsed state */
    .tooltip-collapsed {
        position: absolute;
        left: 100%;
        top: 50%;
        transform: translateY(-50%);
        background: var(--primary-color);
        color: var(--text-active);
        padding: 8px 12px;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 500;
        white-space: nowrap;
        opacity: 0;
        visibility: hidden;
        transition: all var(--transition-fast);
        margin-left: 10px;
        z-index: 1000;
        box-shadow: var(--shadow-md);
        pointer-events: none;
    }

    .tooltip-collapsed::before {
        content: '';
        position: absolute;
        left: -6px;
        top: 50%;
        transform: translateY(-50%);
        border-width: 6px 6px 6px 0;
        border-style: solid;
        border-color: transparent var(--primary-color) transparent transparent;
    }

    .sidebar-container.collapsed .nav-link:hover .tooltip-collapsed {
        opacity: 1;
        visibility: visible;
        transform: translateY(-50%) translateX(5px);
    }

    /* Dropdown Styles */
    .dropdown-container {
        position: relative;
        margin: 4px 8px;
        width: 100%;
    }
    
    /* Nested Dropdown Styles */
    .nested-dropdown {
        position: relative;
        width: 100%;
    }
    
    .nested-dropdown-menu {
        display: none;
        padding-left: 20px;
        background-color: var(--sidebar-bg-light);
        border-radius: 8px;
        margin-top: 5px;
        overflow: hidden;
    }
    
    .nested-dropdown-menu.show {
        display: block;
    }
    
    .nested-dropdown-item {
        display: flex;
        align-items: center;
        padding: 8px 15px;
        color: var(--text-color);
        text-decoration: none;
        transition: all 0.3s ease;
        font-size: 0.9rem;
    }
    
    .nested-dropdown-item:hover {
        background-color: rgba(0, 0, 0, 0.05);
        color: var(--primary-color);
    }
    
    .nested-dropdown-item i {
        margin-right: 10px;
        font-size: 0.7rem;
        width: 15px;
        text-align: center;
    }
    
    .nested-dropdown-item.active {
        color: var(--primary-color);
        background-color: rgba(var(--primary-rgb), 0.1);
    }
    
    .dropdown-toggle {
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 8px 12px;
        color: var(--text-secondary);
        text-decoration: none;
        border-radius: 8px;
        transition: all var(--transition-fast);
        position: relative;
        overflow: hidden;
    }
    
    .dropdown-toggle:hover {
        background: var(--hover-color);
        color: var(--primary-color);
        transform: translateX(4px);
    }
    
    .dropdown-toggle.active {
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
        color: #ffffff !important;
        box-shadow: var(--shadow-md);
    }
    
    .dropdown-toggle .dropdown-icon {
        position: absolute;
        right: 15px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 10px;
        transition: transform 0.3s ease;
        color: var(--text-light);
    }
    
    .dropdown-toggle.active .dropdown-icon {
        transform: translateY(-50%) rotate(180deg);
        color: var(--accent-color);
    }
    
    .dropdown-menu {
        padding: 5px 0 5px 20px;
        margin: 0;
        display: none;
        list-style: none;
        transition: all 0.3s ease;
        overflow: hidden;
        background-color: rgba(0, 0, 0, 0.03);
        border-radius: 0 0 4px 4px;
    }
    
    .dropdown-menu.show {
        display: block;
        animation: fadeIn 0.3s ease-out;
    }
    
    .dropdown-item {
        display: flex;
        align-items: center;
        padding: 8px 15px;
        color: var(--text-primary);
        text-decoration: none;
        margin: 2px 0;
        transition: all 0.3s ease;
        font-size: 13px;
        font-weight: 400;
        border-left: 3px solid transparent;
        letter-spacing: 0.1px;
    }
        
    .dropdown-item:hover {
        background-color: var(--hover-color);
        color: var(--accent-color);
        border-left: 3px solid var(--accent-light);
    }
    
    .dropdown-item.active {
        background-color: var(--hover-color);
        color: var(--accent-color) !important;
        border-left: 3px solid var(--accent-color);
        font-weight: 500;
    }
    
    .dropdown-item i.nav-icon {
        font-size: 8px;
        margin-right: 10px;
        color: var(--text-secondary);
        transition: all 0.3s ease;
    }
    
    .dropdown-item:hover i.nav-icon {
        color: var(--accent-color);
        transform: translateX(2px);
    }
    
    .dropdown-item.active i.nav-icon {
        color: var(--accent-color) !important;
    }
    
    .dropdown-item i.ml-auto {
        margin-left: auto;
        font-size: 10px;
        opacity: 0.7;
        transition: all var(--transition-fast);
    }
    
    .dropdown-item:hover i.ml-auto {
        opacity: 1;
        transform: translateX(2px);
    }
    </style>

<!-- Mobile Overlay -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- Sidebar Header -->
<div class="sidebar-header text-center">
    <h4>Lead Management</h4>
</div>

<!-- Main Navigation -->
<div class="sidebar-section">

    <nav class="nav flex-column">
        <a class="nav-link <?php echo $current_page === 'dashboard.php' ? 'active' : ''; ?>" 
           href="<?php echo SITE_URL; ?>/dashboard/dashboard.php">
            <i class="fas fa-home"></i>
            <span>Dashboard</span>
        </a>
        <a class="nav-link <?php echo $current_page === 'leads.php' ? 'active' : ''; ?>" 
           href="<?php echo SITE_URL; ?>/dashboard/leads.php">
            <i class="fas fa-users"></i>
            <span>Leads</span>
        </a>
        <a class="nav-link <?php echo $current_page === 'tasks.php' ? 'active' : ''; ?>" 
           href="<?php echo SITE_URL; ?>/dashboard/tasks.php">
            <i class="fas fa-tasks"></i>
            <span>Tasks</span>
        </a>
        <a class="nav-link <?php echo $current_page === 'reminders.php' ? 'active' : ''; ?>" 
           href="<?php echo SITE_URL; ?>/dashboard/reminders.php">
            <i class="fas fa-bell"></i>
            <span>Reminders</span>
        </a>
        <!-- New navigation links -->
         <a class="nav-link <?php echo $current_page === 'notes.php' ? 'active' : ''; ?>" 
           href="<?php echo SITE_URL; ?>/dashboard/notes.php">
            <i class="fas fa-clipboard"></i> <!-- Using clipboard icon for notes -->
            <span>Notes</span>
        </a>
         <a class="nav-link <?php echo $current_page === 'calendar.php' ? 'active' : ''; ?>" 
           href="<?php echo SITE_URL; ?>/dashboard/calendar.php">
            <i class="fas fa-calendar-alt"></i>
            <span>Calendar</span>
        </a>
         <a class="nav-link <?php echo $current_page === '' ? 'active' : ''; ?>" 
           href="<?php echo SITE_URL; ?>/dashboard/dashboard.php">
            <i class="fas fa-users-cog"></i> <!-- Using users-cog for HR/Users -->
            <span>HR</span>
        </a>
         <a class="nav-link <?php echo $current_page === 'storage.php' ? 'active' : ''; ?>" 
           href="<?php echo SITE_URL; ?>/dashboard/storage.php">
            <i class="fas fa-database"></i> <!-- Using database icon for Storage -->
            <span>Storage</span>
        </a>
        <a class="nav-link <?php echo $current_page === 'greetings.php' ? 'active' : ''; ?>" 
           href="<?php echo SITE_URL; ?>/dashboard/greetings.php">
            <i class="fas fa-envelope"></i>
            <span>Greetings</span>
        </a>
        <a class="nav-link <?php echo $current_page === 'reports.php' ? 'active' : ''; ?>" 
           href="<?php echo SITE_URL; ?>/dashboard/reports.php">
            <i class="fas fa-chart-pie"></i>
            <span>Reports</span>
        </a>
        <!-- General Settings Dropdown -->
        <div class="dropdown-container" id="generalSettingsDropdown">
            <a class="nav-link dropdown-toggle <?php echo in_array($current_page, ['web-settings.php', 'lead-trash.php', 'attributes.php', 'templates.php', 'automation-rules.php']) ? 'active' : ''; ?>" href="javascript:void(0);" onclick="toggleDropdown(event, 'generalSettingsDropdown')">
                <i class="fas fa-cog"></i>
                <span>General Settings</span>
                <i class="fas fa-chevron-down dropdown-icon"></i>
            </a>
            <div class="dropdown-menu">
                <a class="dropdown-item <?php echo $current_page === 'web-settings.php' ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>/dashboard/web-settings.php">
                    <i class="fas fa-circle nav-icon"></i>
                    <span>Web Settings</span>
                </a>
                <a class="dropdown-item <?php echo $current_page === 'lead-trash.php' ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>/dashboard/lead-trash.php">
                    <i class="fas fa-circle nav-icon"></i>
                    <span>Lead Trash</span>
                </a>
                <div class="nested-dropdown" id="attributesDropdown">
                    <a class="dropdown-item dropdown-toggle <?php echo $current_page === 'attributes.php' ? 'active' : ''; ?>" href="javascript:void(0);" onclick="toggleNestedDropdown(event, 'attributesDropdown')">
                        <i class="fas fa-circle nav-icon"></i>
                        <span>Attributes</span>
                        <i class="fas fa-chevron-down ml-auto"></i>
                    </a>
                    <div class="nested-dropdown-menu">
                        <a class="nested-dropdown-item" href="<?php echo SITE_URL; ?>/dashboard/attributes.php?type=lead_status">
                            <i class="far fa-circle nav-icon"></i>
                            <span>Lead Status</span>
                        </a>
                        <a class="nested-dropdown-item" href="<?php echo SITE_URL; ?>/dashboard/attributes.php?type=lead_source">
                            <i class="far fa-circle nav-icon"></i>
                            <span>Lead Source</span>
                        </a>
                        <a class="nested-dropdown-item" href="<?php echo SITE_URL; ?>/dashboard/attributes.php?type=lead_label">
                            <i class="far fa-circle nav-icon"></i>
                            <span>Lead Label</span>
                        </a>
                        <a class="nested-dropdown-item" href="<?php echo SITE_URL; ?>/dashboard/attributes.php?type=task_status">
                            <i class="far fa-circle nav-icon"></i>
                            <span>Task Status</span>
                        </a>
                        <a class="nested-dropdown-item" href="<?php echo SITE_URL; ?>/dashboard/attributes.php?type=task_label">
                            <i class="far fa-circle nav-icon"></i>
                            <span>Task Label</span>
                        </a>
                        <a class="nested-dropdown-item" href="<?php echo SITE_URL; ?>/dashboard/attributes.php?type=lead_followup">
                            <i class="far fa-circle nav-icon"></i>
                            <span>Lead Follow Up...</span>
                        </a>
                    </div>
                </div>
                <div class="nested-dropdown" id="templatesDropdown">
                    <a class="dropdown-item dropdown-toggle <?php echo $current_page === 'templates.php' ? 'active' : ''; ?>" href="javascript:void(0);" onclick="toggleNestedDropdown(event, 'templatesDropdown')">
                        <i class="fas fa-circle nav-icon"></i>
                        <span>Templates</span>
                        <i class="fas fa-chevron-down ml-auto"></i>
                    </a>
                    <div class="nested-dropdown-menu">
                        <a class="nested-dropdown-item" href="<?php echo SITE_URL; ?>/dashboard/templates.php?type=general">
                            <i class="far fa-circle nav-icon"></i>
                            <span>General Template</span>
                        </a>
                    </div>
                </div>
                <div class="nested-dropdown" id="automationRulesDropdown">
                    <a class="dropdown-item dropdown-toggle <?php echo $current_page === 'automation-rules.php' ? 'active' : ''; ?>" href="javascript:void(0);" onclick="toggleNestedDropdown(event, 'automationRulesDropdown')">
                        <i class="fas fa-circle nav-icon"></i>
                        <span>Automation Rules</span>
                        <i class="fas fa-chevron-down ml-auto"></i>
                    </a>
                    <div class="nested-dropdown-menu">
                        <a class="nested-dropdown-item" href="<?php echo SITE_URL; ?>/dashboard/automation-rules.php?type=whatsapp">
                            <i class="far fa-circle nav-icon"></i>
                            <span>WhatsApp Automation Rules</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </nav>
</div>

<!-- Free Trial Section -->
<div class="sidebar-footer">
    <?php if ($user_status !== 'premium'): ?>
        <div class="free-trial-section <?php echo $is_trial_expired ? 'expired-section' : ''; ?>">
            <div class="trial-icon">
                <?php if ($is_trial_expired): ?>
                    <i class="fas fa-clock"></i>
                <?php else: ?>
                    <i class="fas fa-gift"></i>
                <?php endif; ?>
            </div>
            
            <?php if ($is_trial_expired): ?>
                <div class="trial-title">Trial Expired</div>
                <div class="trial-text">Your free trial has ended. Upgrade now to continue using all features.</div>
                <a href="upgrade.php" class="trial-button">Upgrade Now</a>
            <?php else: ?>
                <div class="trial-title">Free Trial</div>
                <div class="trial-days"><?php echo $trial_days_remaining; ?> Days</div>
                <div class="trial-text">Remaining in your free trial</div>
                <a href="upgrade.php" class="trial-button">Upgrade to Premium</a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="free-trial-section premium-section">
            <div class="trial-icon">
                <i class="fas fa-crown"></i>
            </div>
            <div class="trial-title">Premium Member</div>
            <div class="trial-text">Thank you for being a premium member!</div>
        </div>
    <?php endif; ?>
</div>

<script>
    // Add dropdown functionality
    document.addEventListener('DOMContentLoaded', function() {
        const dropdownToggles = document.querySelectorAll('.dropdown-toggle');
        const freeTrialSection = document.querySelector('.free-trial-section');
        
        // Function to save dropdown state to localStorage
        function saveDropdownState(dropdownId, isOpen) {
            try {
                localStorage.setItem('dropdown_' + dropdownId, isOpen ? 'open' : 'closed');
            } catch (e) {
                console.log('localStorage not available');
            }
        }
        
        // Function to get dropdown state from localStorage
        function getDropdownState(dropdownId) {
            try {
                return localStorage.getItem('dropdown_' + dropdownId) === 'open';
            } catch (e) {
                console.log('localStorage not available');
                return false;
            }
        }
        
        // Function to toggle dropdown
        window.toggleDropdown = function(event, dropdownId) {
            event.preventDefault();
            event.stopPropagation();
            
            const dropdown = document.getElementById(dropdownId);
            if (!dropdown) return;
            
            const toggle = dropdown.querySelector('.dropdown-toggle');
            const menu = dropdown.querySelector('.dropdown-menu');
            
            // Close all other dropdowns
            document.querySelectorAll('.dropdown-container').forEach(container => {
                if (container.id !== dropdownId) {
                    container.querySelector('.dropdown-toggle')?.classList.remove('open');
                    container.querySelector('.dropdown-menu')?.classList.remove('show');
                    saveDropdownState(container.id, false);
                }
            });
            
            // Toggle current dropdown
            const isOpen = toggle.classList.toggle('open');
            menu.classList.toggle('show');
            
            // Save state to localStorage
            saveDropdownState(dropdownId, isOpen);
            
            // Adjust free trial section position
            setTimeout(adjustFreeTrialPosition, 50);
        };
        
        // Function to toggle nested dropdown
        window.toggleNestedDropdown = function(event, dropdownId) {
            event.preventDefault();
            event.stopPropagation();
            
            const nestedDropdown = document.getElementById(dropdownId);
            if (!nestedDropdown) return;
            
            const toggle = nestedDropdown.querySelector('.dropdown-toggle');
            const menu = nestedDropdown.querySelector('.nested-dropdown-menu');
            
            // Close other nested dropdowns
            document.querySelectorAll('.nested-dropdown').forEach(container => {
                if (container.id !== dropdownId) {
                    container.querySelector('.dropdown-toggle')?.classList.remove('open');
                    container.querySelector('.nested-dropdown-menu')?.classList.remove('show');
                    saveDropdownState(container.id, false);
                }
            });
            
            // Toggle current nested dropdown
            const isOpen = toggle.classList.toggle('open');
            menu.classList.toggle('show');
            
            // Save state to localStorage
            saveDropdownState(dropdownId, isOpen);
            
            // Make sure parent dropdown is also open
            const parentDropdown = nestedDropdown.closest('.dropdown-container');
            if (parentDropdown && isOpen) {
                parentDropdown.querySelector('.dropdown-toggle')?.classList.add('open');
                parentDropdown.querySelector('.dropdown-menu')?.classList.add('show');
                saveDropdownState(parentDropdown.id, true);
            }
            
            // Adjust free trial section position
            setTimeout(adjustFreeTrialPosition, 50);
        };
        
        // Fix positioning of free trial section when dropdown is open
        function adjustFreeTrialPosition() {
            const openDropdown = document.querySelector('.dropdown-menu.show');
            if (openDropdown && freeTrialSection) {
                // Calculate if dropdown extends beyond the visible area
                const dropdownBottom = openDropdown.getBoundingClientRect().bottom;
                const sidebarBottom = document.querySelector('.sidebar-section').getBoundingClientRect().bottom;
                
                if (dropdownBottom > sidebarBottom) {
                    // Add some margin to the free trial section
                    freeTrialSection.style.marginTop = (dropdownBottom - sidebarBottom + 10) + 'px';
                } else {
                    freeTrialSection.style.marginTop = 'auto'; // Use auto to push to bottom
                }
            } else if (freeTrialSection) {
                freeTrialSection.style.marginTop = 'auto'; // Use auto to push to bottom
            }
            
            // Ensure the free trial section is visible on mobile
            if (window.innerWidth <= 991.98 && freeTrialSection) {
                // Make sure the free trial section is visible in the viewport
                const viewportHeight = window.innerHeight;
                const freeTrialHeight = freeTrialSection.offsetHeight;
                const freeTrialTop = freeTrialSection.getBoundingClientRect().top;
                
                // If the free trial section would be cut off, scroll to make it visible
                if (freeTrialTop + freeTrialHeight > viewportHeight) {
                    // Add more space at the bottom
                    document.querySelector('.sidebar-footer').style.paddingBottom = '50px';
                    
                    // If the trial section is not fully visible, ensure it's in view
                    if (freeTrialTop > viewportHeight / 2) {
                        freeTrialSection.scrollIntoView({behavior: 'smooth', block: 'end'});
                    }
                }
            }
        }
        
        // For any legacy dropdown toggles without the onclick attribute
        dropdownToggles.forEach(toggle => {
            if (!toggle.hasAttribute('onclick')) {
                toggle.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    // Close any other open dropdowns first
                    dropdownToggles.forEach(otherToggle => {
                        if (otherToggle !== this && otherToggle.classList.contains('open')) {
                            otherToggle.classList.remove('open');
                            const nextMenu = otherToggle.nextElementSibling;
                            if (nextMenu && nextMenu.classList.contains('dropdown-menu')) {
                                nextMenu.classList.remove('show');
                            }
                        }
                    });
                    
                    // Toggle current dropdown
                    this.classList.toggle('open');
                    const dropdownMenu = this.nextElementSibling;
                    if (dropdownMenu && dropdownMenu.classList.contains('dropdown-menu')) {
                        dropdownMenu.classList.toggle('show');
                    }
                    
                    // Adjust free trial section position
                    setTimeout(adjustFreeTrialPosition, 50);
                });
            }
        });
        
        // Restore dropdown states from localStorage or auto-open if a child is active
        function restoreDropdownStates() {
            // First check for active items
            const activeDropdownItem = document.querySelector('.dropdown-item.active');
            if (activeDropdownItem) {
                const parentDropdown = activeDropdownItem.closest('.dropdown-container');
                if (parentDropdown) {
                    const dropdownToggle = parentDropdown.querySelector('.dropdown-toggle');
                    const dropdownMenu = parentDropdown.querySelector('.dropdown-menu');
                    if (dropdownToggle && dropdownMenu) {
                        dropdownToggle.classList.add('open');
                        dropdownMenu.classList.add('show');
                        saveDropdownState(parentDropdown.id, true);
                    }
                }
                
                // If it's in a nested dropdown, open that too
                const nestedParent = activeDropdownItem.closest('.nested-dropdown');
                if (nestedParent) {
                    const nestedToggle = nestedParent.querySelector('.dropdown-toggle');
                    const nestedMenu = nestedParent.querySelector('.nested-dropdown-menu');
                    if (nestedToggle && nestedMenu) {
                        nestedToggle.classList.add('open');
                        nestedMenu.classList.add('show');
                        saveDropdownState(nestedParent.id, true);
                    }
                }
            }
            
            // Then restore states from localStorage
            try {
                // Restore main dropdowns
                document.querySelectorAll('.dropdown-container').forEach(container => {
                    if (getDropdownState(container.id)) {
                        const toggle = container.querySelector('.dropdown-toggle');
                        const menu = container.querySelector('.dropdown-menu');
                        if (toggle && menu) {
                            toggle.classList.add('open');
                            menu.classList.add('show');
                        }
                    }
                });
                
                // Restore nested dropdowns
                document.querySelectorAll('.nested-dropdown').forEach(container => {
                    if (getDropdownState(container.id)) {
                        const toggle = container.querySelector('.dropdown-toggle');
                        const menu = container.querySelector('.nested-dropdown-menu');
                        if (toggle && menu) {
                            toggle.classList.add('open');
                            menu.classList.add('show');
                            
                            // Make sure parent is open too
                            const parentDropdown = container.closest('.dropdown-container');
                            if (parentDropdown) {
                                parentDropdown.querySelector('.dropdown-toggle')?.classList.add('open');
                                parentDropdown.querySelector('.dropdown-menu')?.classList.add('show');
                            }
                        }
                    }
                });
            } catch (e) {
                console.log('Error restoring dropdown states:', e);
            }
            
            // Adjust free trial section position
            setTimeout(adjustFreeTrialPosition, 50);
        }
        
        // Call restore function
        restoreDropdownStates();
        
        // Close dropdowns when clicking outside
        document.addEventListener('click', function(e) {
            // Close main dropdowns when clicking outside
            if (!e.target.closest('.dropdown-container')) {
                document.querySelectorAll('.dropdown-container').forEach(container => {
                    container.querySelector('.dropdown-toggle')?.classList.remove('open');
                    container.querySelector('.dropdown-menu')?.classList.remove('show');
                });
            }
            
            // Close nested dropdowns when clicking outside
            if (!e.target.closest('.nested-dropdown')) {
                document.querySelectorAll('.nested-dropdown').forEach(container => {
                    container.querySelector('.dropdown-toggle')?.classList.remove('open');
                    container.querySelector('.nested-dropdown-menu')?.classList.remove('show');
                });
            }
        });
        
        // Adjust on window resize
        window.addEventListener('resize', adjustFreeTrialPosition);
        
        // Initial adjustment
        adjustFreeTrialPosition();
    });
    
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebarMenu');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebarOverlay = document.getElementById('sidebarOverlay');
    const mainContentWrapper = document.querySelector('.main-content-wrapper');
    const header = document.querySelector('.header');
    
    // Toggle sidebar on mobile
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function(e) {
            e.stopPropagation(); // Prevent event from bubbling up
            sidebar.classList.toggle('show');
            sidebarOverlay.classList.toggle('show');
            document.body.classList.toggle('sidebar-open');
        });
    }
    
    // Close sidebar when clicking overlay
    if (sidebarOverlay) {
        sidebarOverlay.addEventListener('click', function(e) {
            e.stopPropagation(); // Prevent event from bubbling up
            sidebar.classList.remove('show');
            sidebarOverlay.classList.remove('show');
            document.body.classList.remove('sidebar-open');
        });
    }
    
    // Handle window resize
    window.addEventListener('resize', function() {
        if (window.innerWidth >= 992) {
            sidebar.classList.remove('show');
            if (sidebarOverlay) sidebarOverlay.classList.remove('show');
            document.body.classList.remove('sidebar-open');
            
            // Reset main content and header positioning for desktop
            if (mainContentWrapper) mainContentWrapper.style.marginLeft = '250px';
            if (header) header.style.left = '250px';
        } else {
            // Adjust for mobile view
            if (mainContentWrapper) mainContentWrapper.style.marginLeft = '0';
            if (header) header.style.left = '0';
        }
    });
    
    // Initial setup based on window size
    if (window.innerWidth >= 992) {
        if (mainContentWrapper) mainContentWrapper.style.marginLeft = '250px';
        if (header) header.style.left = '250px';
    } else {
        if (mainContentWrapper) mainContentWrapper.style.marginLeft = '0';
        if (header) header.style.left = '0';
    }
    
    // Add hover animation to nav links
    const navLinks = document.querySelectorAll('.nav-link');
    navLinks.forEach(link => {
        link.addEventListener('mouseenter', function() {
            const icon = this.querySelector('i');
            if (icon) icon.style.transform = 'scale(1.2)';
        });
        
        link.addEventListener('mouseleave', function() {
            const icon = this.querySelector('i');
            if (icon) icon.style.transform = 'scale(1)';
        });
        
        // Close sidebar when clicking on any nav link on mobile
        link.addEventListener('click', function(e) {
            if (window.innerWidth < 992) {
                // Don't stop propagation here to allow the link to work
                sidebar.classList.remove('show');
                sidebarOverlay.classList.remove('show');
                document.body.classList.remove('sidebar-open');
            }
        });
    });
    
    // Also handle dropdown items to close sidebar on mobile
    const dropdownItems = document.querySelectorAll('.dropdown-item');
    dropdownItems.forEach(item => {
        item.addEventListener('click', function(e) {
            if (window.innerWidth < 992) {
                // Don't stop propagation here to allow the link to work
                sidebar.classList.remove('show');
                sidebarOverlay.classList.remove('show');
                document.body.classList.remove('sidebar-open');
            }
        });
    });
    
    // Ensure the sidebar doesn't block clicks on the main content
    document.addEventListener('click', function(e) {
        // If sidebar is open and click is outside sidebar and toggle button
        if (document.body.classList.contains('sidebar-open')) {
            const isClickInsideSidebar = sidebar.contains(e.target);
            const isClickOnToggle = sidebarToggle && sidebarToggle.contains(e.target);
            
            if (!isClickInsideSidebar && !isClickOnToggle) {
                sidebar.classList.remove('show');
                sidebarOverlay.classList.remove('show');
                document.body.classList.remove('sidebar-open');
            }
        }
    });
});
</script> 