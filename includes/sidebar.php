<?php
// Get current page for active state
$current_page = basename($_SERVER['PHP_SELF']);
?>
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
    </nav>
</div>

<!-- Reports Section -->
<div class="sidebar-section">
    <div class="sidebar-section-title">Reports</div>
    <nav class="nav flex-column">
        <a class="nav-link <?php echo $current_page === 'reports.php' ? 'active' : ''; ?>" 
           href="<?php echo SITE_URL; ?>/dashboard/reports.php">
            <i class="fas fa-chart-bar"></i>
            <span>Analytics</span>
        </a>
        <a class="nav-link <?php echo $current_page === 'performance.php' ? 'active' : ''; ?>" 
           href="<?php echo SITE_URL; ?>/dashboard/performance.php">
            <i class="fas fa-chart-line"></i>
            <span>Performance</span>
        </a>
    </nav>
</div>

<!-- Settings Section -->
<div class="sidebar-section">
    <div class="sidebar-section-title">Settings</div>
    <nav class="nav flex-column">
        <a class="nav-link <?php echo $current_page === 'profile.php' ? 'active' : ''; ?>" 
           href="<?php echo SITE_URL; ?>/dashboard/profile.php">
            <i class="fas fa-user-cog"></i>
            <span>Profile</span>
        </a>
        <a class="nav-link <?php echo $current_page === 'settings.php' ? 'active' : ''; ?>" 
           href="<?php echo SITE_URL; ?>/dashboard/settings.php">
            <i class="fas fa-cog"></i>
            <span>Settings</span>
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