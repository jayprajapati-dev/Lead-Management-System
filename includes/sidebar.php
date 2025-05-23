<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>

<nav class="sidebar">
    <div class="sidebar-header text-center mb-4">
        <img src="/path/to/your/logo.png" alt="Lead Management Logo" class="img-fluid mb-2">
        <h4>LEAD MANAGEMENT</h4>
    </div>
    <ul class="nav flex-column">
        <li class="nav-item">
            <a class="nav-link <?php echo $current_page === 'dashboard.php' ? 'active' : ''; ?>" href="/dashboard/dashboard.php">
                <i class="fas fa-tachometer-alt"></i>
                Dashboard
            </a>
        </li>
        
        <li class="nav-item">
            <a class="nav-link <?php echo $current_page === 'leads.php' ? 'active' : ''; ?>" href="/dashboard/leads.php">
                <i class="fas fa-users"></i>
                Leads
            </a>
        </li>
        
        <li class="nav-item">
            <a class="nav-link <?php echo $current_page === 'add-lead.php' ? 'active' : ''; ?>" href="/dashboard/add-lead.php">
                <i class="fas fa-user-plus"></i>
                Add Lead
            </a>
        </li>
        
        <li class="nav-item">
            <a class="nav-link <?php echo $current_page === 'analytics.php' ? 'active' : ''; ?>" href="/dashboard/analytics.php">
                <i class="fas fa-chart-line"></i>
                Analytics
            </a>
        </li>
        
        <div class="nav-divider"></div>
        
        <div class="sidebar-heading">Management</div>
        
        <li class="nav-item">
            <a class="nav-link <?php echo $current_page === 'users.php' ? 'active' : ''; ?>" href="/dashboard/users.php">
                <i class="fas fa-user-cog"></i>
                Users
            </a>
        </li>
        
        <li class="nav-item">
            <a class="nav-link <?php echo $current_page === 'settings.php' ? 'active' : ''; ?>" href="/dashboard/settings.php">
                <i class="fas fa-cog"></i>
                Settings
            </a>
        </li>
    </ul>
    <div class="free-trial-info text-center mt-4">
        Free Trial : 6 Day Left<br>
        <a href="#" class="text-light">click to upgrade</a>
    </div>
</nav>

<?php // Removed the script block ?> 