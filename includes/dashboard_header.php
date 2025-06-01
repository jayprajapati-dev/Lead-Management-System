<?php
// Get user information
$user_id = $_SESSION['user_id'] ?? null;
$user = null;
$user_full_name = 'Guest';
$userEmail = 'guest@example.com';
$profile_image = SITE_URL . '/public/images/default-avatar.png';

if ($user_id) {
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    if ($user) {
        $user_full_name = htmlspecialchars($user['first_name'] . ' ' . $user['last_name']);
        $userEmail = htmlspecialchars($user['email']);
        $profile_image = !empty($user['profile_image']) ? 
            SITE_URL . '/uploads/profile_images/' . $user['profile_image'] : 
            SITE_URL . '/public/images/default-avatar.png';
    } else {
        // User not found in database, clear session and redirect to login
        session_destroy();
        header("Location: " . SITE_URL . "/public/login.php");
        exit;
    }
}

// Get user's name for display
$user_first_name = isset($user['first_name']) ? htmlspecialchars($user['first_name']) : '';
$user_last_name = isset($user['last_name']) ? htmlspecialchars($user['last_name']) : '';
$user_display_name = !empty($user_first_name) ? $user_first_name : 'User';
$user_full_name = trim($user_first_name . ' ' . $user_last_name);

// Get user's profile image
$profile_image = '';
if (isset($user['profile_image']) && !empty($user['profile_image'])) {
    // Get the site root path for consistent image references
    $site_root = SITE_URL;
    
    // Remove any leading slashes from the profile image path
    $user_image = ltrim($user['profile_image'], '/');
    
    // Create the full URL to the profile image
    $profile_image = $site_root . '/' . $user_image;
}

// If no profile image, use generated avatar
if (empty($profile_image)) {
    $profile_image = "https://ui-avatars.com/api/?name=" . urlencode($user_full_name) . "&background=4f46e5&color=fff&size=128";
}
?>

<!-- Header -->
<header class="dashboard-header">
    <div class="header-content">
        <h1 class="header-title" style="color: #333; font-weight: 600; margin-bottom: 0;"><?php echo isset($page_title) ? $page_title : 'Dashboard'; ?></h1>
        <div class="header-actions">
            <!-- Search Button -->
            <div class="header-icon-btn">
                <a href="#" id="search-toggle" class="icon-btn" title="Search">
                    <i class="fas fa-search"></i>
                </a>
            </div>
            
            <!-- Dark/Light Mode Toggle -->
            <div class="header-icon-btn">
                <a href="#" id="theme-toggle" class="icon-btn" title="Toggle Dark/Light Mode">
                    <i class="fas fa-moon dark-icon"></i>
                    <i class="fas fa-sun light-icon" style="display: none;"></i>
                </a>
            </div>
            
            <!-- Notifications -->
            <div class="header-icon-btn notification-dropdown">
                <a href="#" class="icon-btn notification-toggle" title="Notifications">
                    <i class="fas fa-bell"></i>
                    <span class="notification-badge">3</span>
                </a>
                <div class="dropdown-menu notification-menu">
                    <div class="dropdown-header">
                        <h6 class="mb-0">Notifications</h6>
                        <a href="#" class="text-muted small">Mark all as read</a>
                    </div>
                    <div class="notification-list">
                        <a href="#" class="dropdown-item notification-item unread">
                            <div class="notification-icon bg-primary">
                                <i class="fas fa-user-plus"></i>
                            </div>
                            <div class="notification-content">
                                <p class="mb-1">New lead assigned to you</p>
                                <small class="text-muted">2 minutes ago</small>
                            </div>
                        </a>
                        <a href="#" class="dropdown-item notification-item">
                            <div class="notification-icon bg-success">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="notification-content">
                                <p class="mb-1">Task completed successfully</p>
                                <small class="text-muted">1 hour ago</small>
                            </div>
                        </a>
                        <a href="#" class="dropdown-item notification-item">
                            <div class="notification-icon bg-warning">
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                            <div class="notification-content">
                                <p class="mb-1">Meeting scheduled for tomorrow</p>
                                <small class="text-muted">Yesterday</small>
                            </div>
                        </a>
                    </div>
                    <div class="dropdown-footer">
                        <a href="#" class="text-center">View all notifications</a>
                    </div>
                </div>
            </div>
            
            <!-- User Profile Dropdown -->
            <div class="user-dropdown">
                <a href="#" class="dropdown-toggle">
                    <div class="profile-info">
                        <span class="profile-name"><?php echo $user_display_name; ?></span>
                        <span class="profile-role"><?php echo isset($user['role']) ? ucfirst($user['role']) : 'User'; ?></span>
                    </div>
                    <img src="<?php echo $profile_image; ?>" alt="<?php echo $user_full_name; ?>" class="profile-avatar">
                </a>
                <div class="dropdown-menu">
                    <div class="dropdown-header">
                        <div class="d-flex align-items-center">
                            <img src="<?php echo $profile_image; ?>" alt="<?php echo $user_full_name; ?>" class="dropdown-profile-image">
                            <div class="ms-2">
                                <h6 class="mb-0"><?php echo $user_full_name; ?></h6>
                                <small class="text-muted"><?php echo $userEmail ?? $user['email']; ?></small>
                            </div>
                        </div>
                    </div>
                    <div class="dropdown-divider"></div>
                    <a href="<?php echo SITE_URL; ?>/dashboard/profile.php" class="dropdown-item">
                        <i class="fas fa-user"></i> Profile
                    </a>
                    <a href="<?php echo SITE_URL; ?>/dashboard/profile.php?tab=change-password" class="dropdown-item">
                        <i class="fas fa-key"></i> Change Password
                    </a>
                    <a href="<?php echo SITE_URL; ?>/dashboard/settings.php" class="dropdown-item">
                        <i class="fas fa-cog"></i> Settings
                    </a>
                    <div class="dropdown-divider"></div>
                    <a href="<?php echo SITE_URL; ?>/dashboard/logout.php" class="dropdown-item">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>
        </div>
    </div>
</header>

<style>
    /* Header Styles */
    .dashboard-header {
        background-color: #ffffff;
        padding: 1rem 1.5rem;
        border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        margin-bottom: 1.5rem;
        position: sticky;
        top: 0;
        z-index: 1000;
    }
    
    .header-content {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .header-title {
        font-size: 1.5rem;
        font-weight: 600;
        color: #333;
        margin: 0;
    }
    
    .header-actions {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    
    /* Header Icon Buttons */
    .header-icon-btn {
        position: relative;
    }
    
    .icon-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 38px;
        height: 38px;
        border-radius: 50%;
        background-color: #f8f9fa;
        color: #333;
        text-decoration: none;
        transition: all 0.2s ease;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }
    
    .icon-btn:hover {
        background-color: #4f46e5;
        color: #ffffff;
        transform: translateY(-2px);
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
    
    .icon-btn i {
        font-size: 1.1rem;
    }
    
    /* Notification Badge */
    .notification-badge {
        position: absolute;
        top: -5px;
        right: -5px;
        background-color: #ef4444;
        color: white;
        border-radius: 50%;
        width: 18px;
        height: 18px;
        font-size: 0.7rem;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        box-shadow: 0 2px 4px rgba(239, 68, 68, 0.3);
    }
    
    /* Notification Dropdown */
    .notification-dropdown {
        position: relative;
    }
    
    .notification-menu {
        width: 320px !important;
        padding: 0 !important;
        max-height: 400px;
        overflow-y: auto;
    }
    
    .notification-list {
        max-height: 300px;
        overflow-y: auto;
    }
    
    .notification-item {
        display: flex !important;
        align-items: flex-start !important;
        padding: 0.75rem 1rem !important;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    }
    
    .notification-item.unread {
        background-color: rgba(79, 70, 229, 0.05);
    }
    
    .notification-icon {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 0.75rem;
        flex-shrink: 0;
    }
    
    .notification-icon i {
        color: white;
        font-size: 0.9rem;
    }
    
    .bg-primary {
        background-color: #4f46e5;
    }
    
    .bg-success {
        background-color: #10b981;
    }
    
    .bg-warning {
        background-color: #f59e0b;
    }
    
    .notification-content {
        flex: 1;
    }
    
    .dropdown-footer {
        padding: 0.75rem 1rem;
        text-align: center;
        border-top: 1px solid rgba(0, 0, 0, 0.05);
    }
    
    .dropdown-footer a {
        color: #4f46e5;
        text-decoration: none;
        font-size: 0.9rem;
    }
    
    /* User Dropdown */
    .user-dropdown {
        position: relative;
    }
    
    .user-dropdown .dropdown-toggle {
        display: flex;
        align-items: center;
        text-decoration: none;
        color: #333;
        padding: 0.5rem;
        border-radius: 0.5rem;
        transition: all 0.2s ease;
        background-color: #f8f9fa;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }
    
    .user-dropdown .dropdown-toggle:hover {
        background-color: #f3f4f6;
        transform: translateY(-2px);
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
    
    .profile-info {
        display: flex;
        flex-direction: column;
        margin-right: 0.75rem;
        text-align: right;
    }
    
    .profile-name {
        font-weight: 600;
        font-size: 0.9rem;
        color: #111827;
    }
    
    .profile-role {
        font-size: 0.75rem;
        color: #6b7280;
    }
    
    .user-dropdown .dropdown-toggle img.profile-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid #4f46e5;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        background-color: #f8f9fa;
    }
    
    .user-dropdown .dropdown-menu {
        position: absolute;
        top: 100%;
        right: 0;
        background-color: #ffffff;
        border-radius: 0.5rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        padding: 0.5rem 0;
        min-width: 200px;
        z-index: 1000;
        display: none;
    }
    
    .user-dropdown:hover .dropdown-menu {
        display: block;
    }
    
    .user-dropdown .dropdown-item {
        display: flex;
        align-items: center;
        padding: 0.5rem 1rem;
        color: #333;
        text-decoration: none;
        transition: background-color 0.2s ease;
    }
    
    .user-dropdown .dropdown-item:hover {
        background-color: rgba(0, 0, 0, 0.05);
    }
    
    .user-dropdown .dropdown-item i {
        margin-right: 0.5rem;
        width: 20px;
        text-align: center;
    }
    
    .dropdown-divider {
        height: 1px;
        background-color: rgba(0, 0, 0, 0.1);
        margin: 0.5rem 0;
    }
    
    .dropdown-header {
        padding: 0.75rem 1rem;
        background-color: #f8f9fa;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
    }
    
    .dropdown-profile-image {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid #4f46e5;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }
    
    /* Improve dropdown animation */
    .user-dropdown .dropdown-menu {
        transition: all 0.2s ease;
        transform-origin: top right;
        opacity: 0;
        transform: scale(0.95);
    }
    
    .user-dropdown:hover .dropdown-menu {
        opacity: 1;
        transform: scale(1);
    }
    
    /* Improve header responsiveness */
    @media (max-width: 768px) {
        .header-title {
            font-size: 1.25rem;
        }
        
        .profile-info {
            display: none;
        }
        
        .notification-menu {
            width: 280px !important;
            right: -120px;
        }
        
        .notification-menu:before {
            right: 140px;
        }
    }
    
    @media (max-width: 576px) {
        .header-title {
            font-size: 1.1rem;
            max-width: 150px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // User dropdown functionality
        const userDropdown = document.querySelector('.user-dropdown');
        const dropdownToggle = userDropdown.querySelector('.dropdown-toggle');
        const dropdownMenu = userDropdown.querySelector('.dropdown-menu');
        
        // Toggle dropdown on click
        dropdownToggle.addEventListener('click', function(e) {
            e.preventDefault();
            dropdownMenu.style.display = dropdownMenu.style.display === 'block' ? 'none' : 'block';
        });
        
        // Notification dropdown functionality
        const notificationDropdown = document.querySelector('.notification-dropdown');
        const notificationToggle = notificationDropdown.querySelector('.notification-toggle');
        const notificationMenu = notificationDropdown.querySelector('.dropdown-menu');
        
        // Toggle notification dropdown on click
        notificationToggle.addEventListener('click', function(e) {
            e.preventDefault();
            notificationMenu.style.display = notificationMenu.style.display === 'block' ? 'none' : 'block';
        });
        
        // Search toggle functionality
        const searchToggle = document.getElementById('search-toggle');
        searchToggle.addEventListener('click', function(e) {
            e.preventDefault();
            // You can implement search functionality here
            alert('Search functionality will be implemented here');
        });
        
        // Theme toggle functionality
        const themeToggle = document.getElementById('theme-toggle');
        const darkIcon = themeToggle.querySelector('.dark-icon');
        const lightIcon = themeToggle.querySelector('.light-icon');
        let darkMode = localStorage.getItem('darkMode') === 'true';
        
        // Set initial theme based on localStorage
        if (darkMode) {
            document.body.classList.add('dark-mode');
            darkIcon.style.display = 'none';
            lightIcon.style.display = 'inline-block';
        }
        
        themeToggle.addEventListener('click', function(e) {
            e.preventDefault();
            darkMode = !darkMode;
            localStorage.setItem('darkMode', darkMode);
            
            if (darkMode) {
                document.body.classList.add('dark-mode');
                darkIcon.style.display = 'none';
                lightIcon.style.display = 'inline-block';
            } else {
                document.body.classList.remove('dark-mode');
                darkIcon.style.display = 'inline-block';
                lightIcon.style.display = 'none';
            }
        });
        
        // Close dropdowns when clicking outside
        document.addEventListener('click', function(e) {
            if (!userDropdown.contains(e.target)) {
                dropdownMenu.style.display = 'none';
            }
            
            if (!notificationDropdown.contains(e.target)) {
                notificationMenu.style.display = 'none';
            }
        });
    });
</script>

<style>
    /* Dark Mode Styles */
    body.dark-mode {
        background-color: #1f2937;
        color: #f9fafb;
    }
    
    body.dark-mode .dashboard-header {
        background-color: #111827;
        border-bottom-color: rgba(255, 255, 255, 0.1);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    }
    
    body.dark-mode .header-title {
        color: #f9fafb;
    }
    
    body.dark-mode .icon-btn {
        background-color: #374151;
        color: #f9fafb;
    }
    
    body.dark-mode .icon-btn:hover {
        background-color: #4f46e5;
    }
    
    body.dark-mode .user-dropdown .dropdown-toggle {
        background-color: #374151;
        color: #f9fafb;
    }
    
    body.dark-mode .profile-name {
        color: #f9fafb;
    }
    
    body.dark-mode .profile-role {
        color: #d1d5db;
    }
    
    body.dark-mode .dropdown-menu {
        background-color: #1f2937;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
    }
    
    body.dark-mode .dropdown-header {
        background-color: #111827;
        border-bottom-color: rgba(255, 255, 255, 0.1);
    }
    
    body.dark-mode .dropdown-item {
        color: #f9fafb;
    }
    
    body.dark-mode .dropdown-item:hover {
        background-color: #374151;
    }
    
    body.dark-mode .dropdown-divider {
        background-color: rgba(255, 255, 255, 0.1);
    }
    
    body.dark-mode .notification-item.unread {
        background-color: rgba(79, 70, 229, 0.15);
    }
    
    body.dark-mode .dropdown-footer {
        border-top-color: rgba(255, 255, 255, 0.1);
    }
</style>
