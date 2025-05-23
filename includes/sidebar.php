<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<style>
    .sidebar {
        background-color: var(--sidebar-bg);
        min-height: calc(100vh - 56px);
        padding: 1rem 0;
        position: sticky;
        top: 56px;
    }
    
    .sidebar .nav-link {
        color: rgba(255, 255, 255, 0.8);
        padding: 0.75rem 1.5rem;
        display: flex;
        align-items: center;
        transition: all 0.3s;
    }
    
    .sidebar .nav-link:hover {
        color: #ffffff;
        background-color: rgba(255, 255, 255, 0.1);
    }
    
    .sidebar .nav-link.active {
        color: #ffffff;
        background-color: var(--primary-color);
    }
    
    .sidebar .nav-link i {
        width: 20px;
        margin-right: 10px;
    }
    
    .sidebar .nav-divider {
        border-top: 1px solid rgba(255, 255, 255, 0.1);
        margin: 0.5rem 0;
    }
    
    .sidebar-heading {
        color: rgba(255, 255, 255, 0.4);
        font-size: 0.75rem;
        text-transform: uppercase;
        padding: 0.5rem 1.5rem;
        margin-top: 1rem;
    }
    
    /* Mobile sidebar */
    @media (max-width: 991.98px) {
        .sidebar {
            position: fixed;
            top: 56px;
            left: -100%;
            width: 100%;
            height: calc(100vh - 56px);
            z-index: 1000;
            transition: all 0.3s;
        }
        
        .sidebar.show {
            left: 0;
        }
    }
    
    /* Dark mode styles */
    .dark-mode .sidebar {
        background-color: #2d2d2d;
    }
</style>

<nav class="sidebar">
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
</nav>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Mobile sidebar toggle
    const sidebarToggle = document.querySelector('.sidebar-toggle');
    const sidebar = document.querySelector('.sidebar');
    
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('show');
        });
    }
    
    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(event) {
        if (window.innerWidth < 992) {
            if (!sidebar.contains(event.target) && !sidebarToggle.contains(event.target)) {
                sidebar.classList.remove('show');
            }
        }
    });
});
</script> 