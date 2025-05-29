<?php
// Get current page for active state
$current_page = basename($_SERVER['PHP_SELF']);
?>
<style>
    :root {
        /* Main colors */
        --primary-color: #4f46e5; /* Primary purple/blue */
        --primary-dark: #3730a3; /* Darker shade for gradients */
        --primary-light: #a5b4fc; /* Lighter shade for highlights */
        
        /* Secondary colors */
        --secondary-color: #f8fafc; /* Light background */
        --hover-color: #f1f5f9; /* Hover state background */
        
        /* Text colors */
        --text-primary: #1e293b; /* Main text color */
        --text-secondary: #64748b; /* Secondary text */
        --text-light: #94a3b8; /* Lighter text for less emphasis */
        --text-active: #ffffff; /* Text color for active items */
        
        /* Border and shadow */
        --border-color: #e2e8f0;
        --shadow-sm: 0 1px 2px rgba(0, 0, 0, 0.05);
        --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        
        /* Status colors */
        --success-color: #10b981;
        --danger-color: #ef4444;
        --warning-color: #f59e0b;
        
        /* Dimensions */
        --sidebar-width: 220px;
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
    .sidebar-container {
        position: fixed;
        top: 0;
        left: 0;
        width: var(--sidebar-width);
        height: 100vh;
        background: #ffffff;
        border-right: 1px solid var(--border-color);
        z-index: 1000;
        transition: width var(--transition-normal);
        display: flex;
        flex-direction: column;
        box-shadow: var(--shadow-sm);
        overflow-y: auto;
        overflow-x: hidden;
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
        padding: 15px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
        color: var(--text-active);
        text-align: center;
        position: relative;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
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
        padding: 8px 12px;
        color: var(--text-secondary);
        text-decoration: none;
        border-radius: 8px;
        margin: 2px 6px;
        transition: all var(--transition-fast);
        position: relative;
        overflow: hidden;
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
        width: 20px;
        font-size: 16px;
        margin-right: 12px;
        text-align: center;
        transition: all var(--transition-fast);
    }
    
    .nav-link:hover i {
        color: var(--primary-color);
    }
    
    .nav-link.active i {
        color: #ffffff !important;
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
    .free-trial-section {
        margin-top: auto;
        padding: 12px 10px;
        background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
        margin: 10px;
        border-radius: 10px;
        border: 1px solid #cbd5e1;
        text-align: center;
        transition: all var(--transition-normal);
        box-shadow: var(--shadow-sm);
    }

    .sidebar-container.collapsed .free-trial-section {
        opacity: 0;
        pointer-events: none;
        transform: scale(0.9);
    }

    .free-trial-section .trial-icon {
        width: 32px;
        height: 32px;
        background: var(--primary-color);
        color: var(--text-active);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 8px;
        font-size: 14px;
        box-shadow: var(--shadow-sm);
    }

    .free-trial-section .trial-text {
        font-size: 11px;
        color: var(--text-secondary);
        margin-bottom: 4px;
        font-weight: 500;
    }

    .free-trial-section .trial-days {
        font-size: 15px;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 8px;
    }

    .upgrade-btn {
        background: var(--primary-color);
        color: var(--text-active);
        border: none;
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 600;
        cursor: pointer;
        transition: all var(--transition-fast);
        text-transform: uppercase;
        box-shadow: var(--shadow-sm);
        letter-spacing: 0.5px;
        display: inline-block;
        text-decoration: none;
    }
    
    .upgrade-btn:hover {
        background: var(--primary-dark);
        transform: translateY(-2px);
        box-shadow: var(--shadow-md);
    }

    /* Responsive Design */
    @media (max-width: 991.98px) {
        .sidebar-toggle {
            display: flex;
        }
        
        .sidebar-container {
            transform: translateX(-100%);
            box-shadow: none;
            z-index: 1030;
            width: 280px;
        }
        
        .sidebar-container.show {
            transform: translateX(0);
            box-shadow: var(--shadow-md);
        }
        
        .sidebar-overlay {
            display: block;
        }
        
        body.sidebar-open {
            overflow: hidden;
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
    
    .dropdown-icon {
        font-size: 10px;
        margin-left: auto;
        transition: transform var(--transition-normal);
    }
    
    .dropdown-toggle.open .dropdown-icon {
        transform: rotate(180deg);
    }
    
    .dropdown-toggle.active .dropdown-icon {
        color: #ffffff !important;
    }
    
    .dropdown-menu {
        display: none;
        padding: 5px 0 5px 20px;
        margin: 4px 0;
        overflow: hidden;
    }
    
    .dropdown-menu.show {
        display: block;
        animation: fadeIn 0.3s ease-out;
    }
    
    .dropdown-item {
        display: flex;
        align-items: center;
        padding: 6px 12px;
        color: var(--text-secondary);
        text-decoration: none;
        border-radius: 6px;
        margin: 2px 0;
        transition: all var(--transition-fast);
        font-size: 12px;
        font-weight: 500;
    }
        
    .dropdown-item:hover {
        background: var(--hover-color);
        color: var(--primary-color);
        transform: translateX(4px);
    }
    
    .dropdown-item.active {
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%);
        color: #ffffff !important;
        font-weight: 500;
        box-shadow: var(--shadow-sm);
    }
    
    .dropdown-item i.nav-icon {
        font-size: 8px;
        margin-right: 10px;
        color: var(--text-light);
        transition: all var(--transition-fast);
    }
    
    .dropdown-item:hover i.nav-icon {
        color: var(--primary-color);
    }
    
    .dropdown-item.active i.nav-icon {
        color: #ffffff !important;
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
            <i class="fas fa-envelope"></i>
            <span>Greetings</span>
        </a>
        <a class="nav-link <?php echo $current_page === 'reports.php' ? 'active' : ''; ?>" 
           href="<?php echo SITE_URL; ?>/dashboard/reports.php">
            <i class="fas fa-chart-pie"></i>
            <span>Reports</span>
        </a>
        <!-- General Settings Dropdown -->
        <div class="dropdown-container">
            <a class="nav-link dropdown-toggle <?php echo in_array($current_page, ['general-settings.php', 'web-settings.php', 'lead-trash.php', 'attributes.php', 'templates.php', 'automation-rules.php']) ? 'active' : ''; ?>" href="#">
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
                <a class="dropdown-item <?php echo $current_page === 'attributes.php' ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>/dashboard/attributes.php">
                    <i class="fas fa-circle nav-icon"></i>
                    <span>Attributes</span>
                    <i class="fas fa-chevron-right ml-auto"></i>
                </a>
                <a class="dropdown-item <?php echo $current_page === 'templates.php' ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>/dashboard/templates.php">
                    <i class="fas fa-circle nav-icon"></i>
                    <span>Templates</span>
                    <i class="fas fa-chevron-right ml-auto"></i>
                </a>
                <a class="dropdown-item <?php echo $current_page === 'automation-rules.php' ? 'active' : ''; ?>" href="<?php echo SITE_URL; ?>/dashboard/automation-rules.php">
                    <i class="fas fa-circle nav-icon"></i>
                    <span>Automation Rules</span>
                    <i class="fas fa-chevron-right ml-auto"></i>
                </a>
            </div>
        </div>
    </nav>
</div>

<!-- Free Trial Info -->
<div class="free-trial-section">
    <div class="trial-icon">
        <i class="fas fa-crown"></i>
    </div>
    <div class="trial-text">Free Trial</div>
    <div class="trial-days">6 Days Left</div>
    <a href="#" class="upgrade-btn">Upgrade Now</a>
</div>

<script>
    // Add dropdown functionality
    document.addEventListener('DOMContentLoaded', function() {
        const dropdownToggles = document.querySelectorAll('.dropdown-toggle');
        const freeTrialSection = document.querySelector('.free-trial-section');
        
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
                    freeTrialSection.style.marginTop = '10px';
                }
            } else if (freeTrialSection) {
                freeTrialSection.style.marginTop = '10px';
            }
        }
        
        dropdownToggles.forEach(toggle => {
            toggle.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Close any other open dropdowns first
                dropdownToggles.forEach(otherToggle => {
                    if (otherToggle !== this && otherToggle.classList.contains('open')) {
                        otherToggle.classList.remove('open');
                        otherToggle.nextElementSibling.classList.remove('show');
                    }
                });
                
                // Toggle current dropdown
                this.classList.toggle('open');
                const dropdownMenu = this.nextElementSibling;
                dropdownMenu.classList.toggle('show');
                
                // Adjust free trial section position
                setTimeout(adjustFreeTrialPosition, 50);
            });
        });
        
        // Auto-open dropdown if a child is active
        const activeDropdownItem = document.querySelector('.dropdown-item.active');
        if (activeDropdownItem) {
            const parentDropdown = activeDropdownItem.closest('.dropdown-container');
            if (parentDropdown) {
                const dropdownToggle = parentDropdown.querySelector('.dropdown-toggle');
                const dropdownMenu = parentDropdown.querySelector('.dropdown-menu');
                if (dropdownToggle && dropdownMenu) {
                    dropdownToggle.classList.add('open');
                    dropdownMenu.classList.add('show');
                    
                    // Adjust free trial section position
                    setTimeout(adjustFreeTrialPosition, 50);
                }
            }
        }
        
        // Adjust on window resize
        window.addEventListener('resize', adjustFreeTrialPosition);
        
        // Initial adjustment
        adjustFreeTrialPosition();
    });
    
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebarMenu');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebarOverlay = document.getElementById('sidebarOverlay');
    
    // Toggle sidebar on mobile
    sidebarToggle.addEventListener('click', function(e) {
        e.stopPropagation(); // Prevent event from bubbling up
        sidebar.classList.toggle('show');
        sidebarOverlay.classList.toggle('show');
        document.body.classList.toggle('sidebar-open');
    });
    
    // Close sidebar when clicking overlay
    sidebarOverlay.addEventListener('click', function(e) {
        e.stopPropagation(); // Prevent event from bubbling up
        sidebar.classList.remove('show');
        sidebarOverlay.classList.remove('show');
        document.body.classList.remove('sidebar-open');
    });
    
    // We're removing this general document click handler as it might be causing issues with clickable elements
    // Instead, we'll rely on the overlay click and specific navigation link clicks
    
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