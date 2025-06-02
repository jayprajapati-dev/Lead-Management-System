<?php
// Get user data from session
$user_name = $_SESSION['user_name'] ?? 'User';
$user_email = $_SESSION['user_email'] ?? '';
$notification_count = 0; // You can update this dynamically from your database
?>

<!-- Dashboard Header CSS -->
<style>
/* Main Layout Adjustments */
body {
    margin: 0;
    padding: 0;
}

.dashboard-header {
    background: #fff;
    border-bottom: 1px solid #e5e7eb;
    padding: 0;
    position: fixed;
    top: 0;
    right: 0;
    left: 250px;
    z-index: 1000;
    height: 50px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
}

.header-container {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    height: 100%;
    padding: 0 0.75rem;
}

/* Quick Actions Section */
.quick-actions {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-right: auto;
    background: #f8fafc;
    padding: 0.25rem;
    border-radius: 0.5rem;
    border: 1px solid #e2e8f0;
}

.quick-action-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    padding: 0.5rem 0.75rem;
    color: #4b5563;
    font-size: 0.8rem;
    font-weight: 500;
    border-radius: 0.375rem;
    transition: all 0.2s ease;
    border: 1px solid #e5e7eb;
    background: white;
    cursor: pointer;
    white-space: nowrap;
    height: 32px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.05);
}

.quick-action-btn i {
    font-size: 0.7rem;
    padding: 0.3rem;
    border-radius: 0.25rem;
    background: #f3f4f6;
    color: #4b5563;
    transition: all 0.2s ease;
    width: 18px;
    height: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.quick-action-btn:hover {
    color: #4f46e5;
    background: #f9fafb;
    border-color: #d1d5db;
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.quick-action-btn:hover i {
    background: #4f46e5;
    color: white;
}

.quick-action-btn:active {
    transform: translateY(0);
    box-shadow: 0 1px 2px rgba(0,0,0,0.05);
}

/* Header Icons */
.header-icons {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.icon-button {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: none;
    border: none;
    padding: 0;
    cursor: pointer;
    color: #64748b;
    border-radius: 0.375rem;
    transition: all 0.15s;
    position: relative;
}

.icon-button i {
    font-size: 0.875rem;
}

.icon-button:hover {
    background: #f1f5f9;
    color: #6366f1;
}

.icon-button.active {
    background: #f1f5f9;
    color: #6366f1;
}

/* Mobile Menu Button */
.mobile-menu-btn {
    display: none;
    width: 32px;
    height: 32px;
    align-items: center;
    justify-content: center;
    background: none;
    border: none;
    padding: 0;
    cursor: pointer;
    color: #64748b;
    margin-right: 0.5rem;
    transition: color 0.15s;
}

.mobile-menu-btn:hover {
    color: #6366f1;
}

/* Mobile Quick Actions Menu */
.mobile-quick-actions {
    display: none;
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    background: white;
    border-top: 1px solid #e5e7eb;
    padding: 10px;
    z-index: 1000;
    box-shadow: 0 -2px 10px rgba(0,0,0,0.05);
}

.mobile-quick-actions .quick-actions-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 8px;
}

.mobile-quick-actions .quick-action-btn {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: auto;
    width: 100%;
    padding: 10px 5px;
    gap: 5px;
}

.mobile-quick-actions .quick-action-btn i {
    margin-bottom: 5px;
    font-size: 0.8rem;
    width: 24px;
    height: 24px;
}

.mobile-quick-actions .quick-action-btn span {
    font-size: 0.7rem;
    text-align: center;
}

/* Responsive Design */
@media (max-width: 768px) {
    .dashboard-header {
        left: 0;
    }

    .mobile-menu-btn {
        display: flex;
    }

    .quick-actions {
        display: none;
    }
    
    .mobile-quick-actions {
        display: block;
    }

    .header-icons {
        margin-left: auto;
    }

    /* Adjust icon sizes for mobile */
    .icon-button {
        width: 36px;
        height: 36px;
    }

    .icon-button i {
        font-size: 1rem;
    }

    /* Make dropdowns full width on mobile */
    .dropdown-menu {
        width: calc(100vw - 2rem);
        max-width: none;
        margin: 0.5rem 1rem;
        right: 0;
        left: 0;
    }
    
    /* Adjust main content to account for mobile quick actions bar */
    .main-content-wrapper {
        padding-bottom: 70px;
    }
}

/* Dark mode support */
@media (prefers-color-scheme: dark) {
    .dashboard-header {
        background: #1e293b;
        border-color: #334155;
    }

    .quick-actions {
        background: #334155;
        border-color: #475569;
    }

    .quick-action-btn {
        color: #e2e8f0;
    }

    .quick-action-btn i {
        background: #475569;
        color: #e2e8f0;
    }

    .quick-action-btn:hover {
        background: #1e293b;
        color: #818cf8;
    }

    .icon-button {
        color: #e2e8f0;
    }

    .icon-button:hover {
        background: #334155;
        color: #818cf8;
    }

    .mobile-quick-actions {
        background: #1e293b;
        border-color: #334155;
    }
}

/* Dropdown Menus */
.dropdown-menu {
    position: absolute;
    top: calc(100% + 0.5rem);
    right: 0;
    background-color: white;
    border: 1px solid #e5e7eb;
    border-radius: 0.5rem;
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    min-width: 280px;
    display: none;
    z-index: 1001; /* Increased z-index to ensure it appears above other elements */
    overflow: hidden;
    backdrop-filter: none;
}

/* Mobile dropdown positioning */
@media (max-width: 768px) {
    .dropdown-menu {
        position: fixed;
        top: 60px; /* Adjust based on your header height */
        right: 10px;
        left: 10px;
        width: calc(100% - 20px);
        max-width: 350px;
        margin: 0 auto;
    }
    
    /* Ensure the dropdown has a solid background */
    #profileDropdown {
        background-color: white;
    }
}

.dropdown-menu.show {
    display: block;
}

.profile-header {
    padding: 1rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    background-color: #f0f4f8;
    border-bottom: 1px solid #e5e7eb;
}

.user-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #6366f1;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.5rem;
    margin-right: 0.5rem;
}

.user-info {
    display: flex;
    flex-direction: column;
}

.user-name {
    font-weight: 600;
    color: #1f2937;
    font-size: 1rem;
}

.user-email {
    color: #6b7280;
    font-size: 0.85rem;
}

.profile-menu-items {
    padding: 0.5rem 0;
    background-color: white;
}

.badge {
    background-color: #e5e7eb;
    color: #4b5563;
    border-radius: 9999px;
    padding: 0.15rem 0.5rem;
    font-size: 0.75rem;
    font-weight: 600;
    min-width: 1.5rem;
    text-align: center;
    display: inline-block;
}

/* Add this to make the Today's Leads text more prominent */
.user-name {
    font-weight: 600;
    color: #1f2937;
    font-size: 1.1rem;
}

.user-email {
    color: #6b7280;
    font-size: 0.85rem;
}

.referral-link-container {
    padding: 0;
    background-color: white;
}

.dropdown-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.75rem 1rem;
    color: #4b5563;
    text-decoration: none;
    transition: all 0.2s;
    cursor: pointer;
    font-size: 0.95rem;
}

.dropdown-item i {
    margin-right: 0.75rem;
    width: 20px;
    text-align: center;
    color: #6b7280;
}

.dropdown-item:hover {
    background: #f1f5f9;
    color: #4f46e5;
}

.dropdown-divider {
    height: 1px;
    background: #e5e7eb;
    margin: 0.5rem 0;
}

.referral-button {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);
    color: white;
    padding: 0.75rem 1rem;
    margin: 0;
    border-radius: 0 0 0.5rem 0.5rem;
    text-decoration: none;
    font-weight: 500;
    transition: all 0.2s;
    width: 100%;
    justify-content: center;
    font-size: 0.9rem;
}

.referral-button:hover {
    background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
    color: white;
}

/* Add this new class for main content wrapper */
.main-content-wrapper {
    margin-top: 60px; /* Height of header */
    margin-left: 250px; /* Width of sidebar */
    padding: 1.5rem;
    min-height: calc(100vh - 60px);
}

@media (max-width: 768px) {
    .dashboard-header {
        left: 0;
    }
    .main-content-wrapper {
        margin-left: 0;
        padding: 1rem;
    }
}
</style>

<!-- Dashboard Header HTML -->
<header class="dashboard-header">
    <div class="header-container">
        <!-- Mobile Menu Button -->
        <button class="mobile-menu-btn" id="sidebarToggle" aria-label="Toggle Sidebar">
            <i class="fas fa-bars"></i>
        </button>

        <!-- Quick Actions (Desktop Only) -->
        <div class="quick-actions">
            <button class="quick-action-btn" title="Add New Lead" data-bs-toggle="modal" data-bs-target="#addLeadModal">
                <i class="fas fa-plus"></i>
                <span>Lead</span>
            </button>
            <button class="quick-action-btn" title="Add New Task" data-bs-toggle="modal" data-bs-target="#addTaskModal">
                <i class="fas fa-plus"></i>
                <span>Task</span>
            </button>
            <button class="quick-action-btn" title="Add New Note" data-bs-toggle="modal" data-bs-target="#addNoteModal">
                <i class="fas fa-plus"></i>
                <span>Note</span>
            </button>
            <button class="quick-action-btn" title="Add New Reminder" data-bs-toggle="modal" data-bs-target="#addReminderModal">
                <i class="fas fa-plus"></i>
                <span>Reminder</span>
            </button>
        </div>

        <!-- Header Icons -->
        <div class="header-icons">
            <button class="icon-button" id="searchButton" title="Search">
                <i class="fas fa-search"></i>
            </button>
            
            <button class="icon-button" id="themeToggle" title="Toggle Theme">
                <i class="fas fa-moon"></i>
            </button>
            
            <button class="icon-button" id="notificationButton" title="Notifications">
                <i class="fas fa-bell"></i>
                <?php if ($notification_count > 0): ?>
                    <span class="notification-badge"><?php echo $notification_count; ?></span>
                <?php endif; ?>
            </button>
            
            <button class="icon-button" id="profileButton" title="Profile">
                <i class="fas fa-user-circle"></i>
            </button>
        </div>
    </div>

    <!-- Notification Dropdown -->
    <div class="dropdown-menu" id="notificationDropdown">
        <div class="dropdown-item">
            <h6 style="margin: 0; font-size: 0.875rem;">Notifications <?php if ($notification_count > 0): ?><span class="notification-badge" style="position: static; margin-left: 0.5rem;"><?php echo $notification_count; ?></span><?php endif; ?></h6>
        </div>
        <div class="dropdown-divider"></div>
        <?php if ($notification_count === 0): ?>
            <div class="dropdown-item">
                <span>No Notifications</span>
            </div>
        <?php endif; ?>
    </div>

    <!-- Profile Dropdown -->
    <div class="dropdown-menu" id="profileDropdown">
        <div class="profile-header">
            <div class="user-avatar">
                <i class="fas fa-user-circle"></i>
            </div>
            <div class="user-info">
                <div class="user-name"><?php echo htmlspecialchars($user_name); ?></div>
                <div class="user-email"><?php echo htmlspecialchars($user_email); ?></div>
            </div>
        </div>
        <div class="dropdown-divider"></div>
        <div class="profile-menu-items">
            <a href="profile.php" class="dropdown-item">
                <div>
                    <i class="fas fa-user"></i>
                    <span>Profile</span>
                </div>
            </a>
            <a href="sync-data.php" class="dropdown-item">
                <div>
                    <i class="fas fa-sync"></i>
                    <span>Sync Data</span>
                </div>
            </a>
            <div class="dropdown-divider"></div>
            <a href="billing.php" class="dropdown-item">
                <div>
                    <i class="fas fa-credit-card"></i>
                    <span>Billing</span>
                </div>
            </a>
            <a href="packages.php" class="dropdown-item">
                <div>
                    <i class="fas fa-box"></i>
                    <span>Packages</span>
                </div>
            </a>
            <div class="dropdown-divider"></div>
            <a href="logout.php" class="dropdown-item">
                <div>
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </div>
            </a>
        </div>
        <div class="referral-link-container">
            <a href="#" class="referral-button">
                <i class="fas fa-gift"></i>
                <span>Set up a Referral Link and earn free Points</span>
            </a>
        </div>
    </div>
</header>

<!-- Mobile Quick Actions Menu -->
<div class="mobile-quick-actions">
    <div class="quick-actions-grid">
        <button class="quick-action-btn" title="Add New Lead" data-bs-toggle="modal" data-bs-target="#addLeadModal">
            <i class="fas fa-plus"></i>
            <span>Lead</span>
        </button>
        <button class="quick-action-btn" title="Add New Task" data-bs-toggle="modal" data-bs-target="#addTaskModal">
            <i class="fas fa-plus"></i>
            <span>Task</span>
        </button>
        <button class="quick-action-btn" title="Add New Note" data-bs-toggle="modal" data-bs-target="#addNoteModal">
            <i class="fas fa-plus"></i>
            <span>Note</span>
        </button>
        <button class="quick-action-btn" title="Add New Reminder" data-bs-toggle="modal" data-bs-target="#addReminderModal">
            <i class="fas fa-plus"></i>
            <span>Reminder</span>
        </button>
    </div>
</div>

<!-- JavaScript for Header Functionality -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Sidebar Toggle
    const sidebarToggle = document.getElementById('sidebarToggle');
    const body = document.body;
    
    sidebarToggle.addEventListener('click', () => {
        body.classList.toggle('sidebar-collapsed');
        // Dispatch custom event for sidebar toggle
        document.dispatchEvent(new CustomEvent('sidebar-toggle'));
    });

    // Theme Toggle
    const themeToggle = document.getElementById('themeToggle');
    const themeIcon = themeToggle.querySelector('i');
    let isDarkMode = localStorage.getItem('darkMode') === 'true';

    function updateTheme() {
        if (isDarkMode) {
            document.body.classList.add('dark-mode');
            themeIcon.classList.replace('fa-moon', 'fa-sun');
        } else {
            document.body.classList.remove('dark-mode');
            themeIcon.classList.replace('fa-sun', 'fa-moon');
        }
    }

    themeToggle.addEventListener('click', () => {
        isDarkMode = !isDarkMode;
        localStorage.setItem('darkMode', isDarkMode);
        updateTheme();
    });

    updateTheme();

    // Dropdown Toggles
    function setupDropdown(buttonId, dropdownId) {
        const button = document.getElementById(buttonId);
        const dropdown = document.getElementById(dropdownId);
        
        if (!button || !dropdown) return; // Skip if elements don't exist
        
        // Position the dropdown relative to the button
        function positionDropdown() {
            if (window.innerWidth <= 768) {
                // On mobile, center the dropdown
                dropdown.style.right = 'auto';
                dropdown.style.left = '50%';
                dropdown.style.transform = 'translateX(-50%)';
                dropdown.style.maxWidth = '350px';
                dropdown.style.width = 'calc(100% - 20px)';
                // Ensure solid background
                dropdown.style.backgroundColor = 'white';
            } else {
                // On desktop, align with the button
                dropdown.style.right = '10px';
                dropdown.style.left = 'auto';
                dropdown.style.transform = 'none';
                dropdown.style.maxWidth = 'none';
                dropdown.style.width = 'auto';
                // Ensure solid background
                dropdown.style.backgroundColor = 'white';
            }
        }

        button.addEventListener('click', (e) => {
            e.stopPropagation();
            const isActive = dropdown.classList.contains('show');
            
            // Close all dropdowns
            document.querySelectorAll('.dropdown-menu').forEach(d => {
                d.classList.remove('show');
            });
            
            // Remove active state from all buttons
            document.querySelectorAll('.icon-button').forEach(btn => {
                btn.classList.remove('active');
            });

            // Toggle current dropdown
            if (!isActive) {
                positionDropdown();
                dropdown.classList.add('show');
                
                // Add active state to button
                button.classList.add('active');
            }
        });
        
        // Update position on resize
        window.addEventListener('resize', () => {
            if (dropdown.classList.contains('show')) {
                positionDropdown();
            }
        });
    }

    setupDropdown('notificationButton', 'notificationDropdown');
    setupDropdown('profileButton', 'profileDropdown');

    // Close dropdowns when clicking outside
    document.addEventListener('click', () => {
        document.querySelectorAll('.dropdown-menu').forEach(dropdown => {
            dropdown.classList.remove('show');
        });
        
        // Remove active state from buttons
        document.querySelectorAll('.icon-button').forEach(button => {
            button.classList.remove('active');
        });
    });
    
    // Prevent clicks inside dropdown from closing it
    document.querySelectorAll('.dropdown-menu').forEach(dropdown => {
        dropdown.addEventListener('click', (e) => {
            e.stopPropagation();
        });
    });
});
</script>
