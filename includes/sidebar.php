<?php
// Get current page for active state
$current_page = basename($_SERVER['PHP_SELF']);
?>
 <style>
        :root {
            --primary-color: #6366f1;
            --primary-dark: #4f46e5;
            --primary-light: #a5b4fc;
            --secondary-color: #f8fafc;
            --border-color: #e2e8f0;
            --text-primary: #1e293b;
            --text-secondary: #64748b;
            --text-light: #94a3b8;
            --success-color: #10b981;
            --danger-color: #ef4444;
            --warning-color: #f59e0b;
            --sidebar-width: 280px;
            --sidebar-collapsed-width: 80px;
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
        .sidebar-container {
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: #ffffff;
            border-right: 1px solid var(--border-color);
            z-index: 1000;
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
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
            color: white;
            border: none;
            border-radius: 8px;
            width: 44px;
            height: 44px;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
        }

        .sidebar-toggle:hover {
            background: var(--primary-dark);
            transform: translateY(-1px);
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
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .sidebar-overlay.show {
            opacity: 1;
        }

        /* Sidebar Header */
        .sidebar-header {
            padding: 24px 20px;
            border-bottom: 1px solid var(--border-color);
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            color: white;
            text-align: center;
            position: relative;
        }

        .sidebar-header h4 {
            font-size: 18px;
            font-weight: 600;
            margin: 0;
            transition: opacity 0.3s ease;
        }

        .sidebar-container.collapsed .sidebar-header h4 {
            opacity: 0;
        }

        .sidebar-header .logo-icon {
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 12px;
            font-size: 20px;
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
            border-radius: 2px;
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
            padding: 0 8px;
            letter-spacing: 0.5px;
            transition: opacity 0.3s ease;
        }

        .sidebar-container.collapsed .nav-section-title {
            opacity: 0;
        }

        /* Navigation Links */
        .nav-link {
            display: flex;
            align-items: center;
            padding: 12px 16px;
            color: var(--text-secondary);
            text-decoration: none;
            border-radius: 8px;
            margin-bottom: 4px;
            transition: all 0.2s ease;
            position: relative;
            overflow: hidden;
        }

        .nav-link:hover {
            background: var(--secondary-color);
            color: var(--text-primary);
            transform: translateX(4px);
        }

        .nav-link.active {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
        }

        .nav-link.active:hover {
            transform: translateX(0);
        }

        .nav-link i {
            width: 20px;
            font-size: 16px;
            margin-right: 12px;
            text-align: center;
            transition: transform 0.2s ease;
        }

        .nav-link span {
            font-size: 14px;
            font-weight: 500;
            transition: opacity 0.3s ease;
        }

        .sidebar-container.collapsed .nav-link span {
            opacity: 0;
        }

        .sidebar-container.collapsed .nav-link {
            justify-content: center;
            padding: 12px;
        }

        .sidebar-container.collapsed .nav-link i {
            margin-right: 0;
        }

        /* Badge/Count */
        .nav-badge {
            background: var(--danger-color);
            color: white;
            font-size: 10px;
            font-weight: 600;
            padding: 2px 6px;
            border-radius: 10px;
            margin-left: auto;
            min-width: 18px;
            text-align: center;
        }

        .sidebar-container.collapsed .nav-badge {
            display: none;
        }

        /* Free Trial Section */
        .free-trial-section {
            margin-top: auto;
            padding: 20px 16px;
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            margin: 16px;
            border-radius: 12px;
            border: 1px solid #fbbf24;
            text-align: center;
            transition: opacity 0.3s ease;
        }

        .sidebar-container.collapsed .free-trial-section {
            opacity: 0;
            pointer-events: none;
        }

        .free-trial-section .trial-icon {
            width: 40px;
            height: 40px;
            background: var(--warning-color);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 8px;
            font-size: 18px;
        }

        .free-trial-section .trial-text {
            font-size: 13px;
            color: #92400e;
            margin-bottom: 8px;
            font-weight: 600;
        }

        .free-trial-section .trial-days {
            font-size: 18px;
            font-weight: 700;
            color: #92400e;
            margin-bottom: 12px;
        }

        .upgrade-btn {
            background: var(--warning-color);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .upgrade-btn:hover {
            background: #d97706;
            transform: translateY(-1px);
        }

        /* Responsive Design */
        @media (max-width: 991.98px) {
            .sidebar-toggle {
                display: block;
            }

            .sidebar-container {
                transform: translateX(-100%);
            }

            .sidebar-container.show {
                transform: translateX(0);
            }

            .sidebar-overlay.show {
                display: block;
            }

            body.sidebar-open {
                overflow: hidden;
            }
        }

        @media (min-width: 992px) {
            .main-content {
                margin-left: var(--sidebar-width);
                transition: margin-left 0.3s ease;
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

        .dark-theme .nav-link {
            color: #cbd5e1;
        }

        .dark-theme .nav-link:hover {
            background: #334155;
            color: #f1f5f9;
        }

        /* Animations */
        @keyframes slideIn {
            from {
                transform: translateX(-100%);
            }
            to {
                transform: translateX(0);
            }
        }

        .sidebar-container.show {
            animation: slideIn 0.3s ease;
        }

        /* Tooltip for collapsed state */
        .tooltip-collapsed {
            position: absolute;
            left: 100%;
            top: 50%;
            transform: translateY(-50%);
            background: var(--text-primary);
            color: white;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 12px;
            white-space: nowrap;
            opacity: 0;
            visibility: hidden;
            transition: all 0.2s ease;
            margin-left: 8px;
            z-index: 1000;
        }

        .tooltip-collapsed::before {
            content: '';
            position: absolute;
            top: 50%;
            left: -4px;
            transform: translateY(-50%);
            border: 4px solid transparent;
            border-right-color: var(--text-primary);
        }

        .sidebar-container.collapsed .nav-link:hover .tooltip-collapsed {
            opacity: 1;
            visibility: visible;
        }
    </style>
<!-- Mobile Toggle Button -->
<button class="sidebar-toggle d-md-none" id="sidebarToggle">
    <i class="fas fa-bars"></i>
</button>

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
            <i class="fas fa-envelope"></i> <!-- Using envelope icon for Greetings -->
            <span>Greetings</span>
        </a>
         <a class="nav-link <?php echo $current_page === 'reports.php' ? 'active' : ''; ?>" 
           href="<?php echo SITE_URL; ?>/dashboard/reports.php">
            <i class="fas fa-chart-pie"></i> <!-- Using chart-pie for Reports -->
            <span>Reports</span>
        </a>
         <a class="nav-link <?php echo $current_page === 'general-settings.php' ? 'active' : ''; ?>" 
           href="<?php echo SITE_URL; ?>/dashboard/general-settings.php">
            <i class="fas fa-cog"></i>
            <span>General Settings</span>
        </a>
    </nav>
</div>

<!-- Free Trial Info -->
<div class="free-trial-info">
    <p>Free Trial : 6 Day Left</p>
    <p><a href="#">click to upgrade</a></p>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebarMenu');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebarOverlay = document.getElementById('sidebarOverlay');
    
    // Toggle sidebar on mobile
    sidebarToggle.addEventListener('click', function() {
        sidebar.classList.toggle('show');
        sidebarOverlay.classList.toggle('show');
        document.body.classList.toggle('sidebar-open');
    });
    
    // Close sidebar when clicking overlay
    sidebarOverlay.addEventListener('click', function() {
        sidebar.classList.remove('show');
        sidebarOverlay.classList.remove('show');
        document.body.classList.remove('sidebar-open');
    });
    
    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(event) {
        const isClickInsideSidebar = sidebar.contains(event.target);
        const isClickOnToggle = sidebarToggle.contains(event.target);
        
        if (!isClickInsideSidebar && !isClickOnToggle && window.innerWidth < 992) {
            sidebar.classList.remove('show');
            sidebarOverlay.classList.remove('show');
            document.body.classList.remove('sidebar-open');
        }
    });
    
    // Handle window resize
    window.addEventListener('resize', function() {
        if (window.innerWidth >= 992) {
            sidebar.classList.remove('show');
            sidebarOverlay.classList.remove('show');
            document.body.classList.remove('sidebar-open');
        }
    });
    
    // Add hover animation to nav links
    const navLinks = document.querySelectorAll('.nav-link');
    navLinks.forEach(link => {
        link.addEventListener('mouseenter', function() {
            const icon = this.querySelector('i');
            icon.style.transform = 'scale(1.2)';
        });
        
        link.addEventListener('mouseleave', function() {
            const icon = this.querySelector('i');
            icon.style.transform = 'scale(1)';
        });
    });
});
</script> 