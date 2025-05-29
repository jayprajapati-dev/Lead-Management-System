<?php
// Get user information if not already available
if (isset($_SESSION['user_id']) && !isset($user)) {
    $user_id = $_SESSION['user_id'];
    $sql = "SELECT * FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
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
            <div class="user-dropdown">
                <a href="#" class="dropdown-toggle">
                    <img src="<?php echo $profile_image; ?>" alt="<?php echo $user_full_name; ?>" class="profile-avatar">
                    <span><?php echo $user_display_name; ?></span>
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
        transition: background-color 0.2s ease;
    }
    
    .user-dropdown .dropdown-toggle:hover {
        background-color: rgba(0, 0, 0, 0.05);
    }
    
    .user-dropdown .dropdown-toggle img.profile-avatar {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        margin-right: 0.5rem;
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
    @media (max-width: 576px) {
        .header-title {
            font-size: 1.25rem;
        }
        
        .user-dropdown .dropdown-toggle span {
            display: none;
        }
        
        .user-dropdown .dropdown-toggle img.profile-avatar {
            margin-right: 0;
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
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!userDropdown.contains(e.target)) {
                dropdownMenu.style.display = 'none';
            }
        });
    });
</script>
