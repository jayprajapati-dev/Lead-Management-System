<?php
// includes/navbar.php
// This file contains the navigation bar
?>

<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top py-3">
    <div class="container">
        <a class="navbar-brand fw-bold" href="<?php echo SITE_URL; ?>/public/index.php">
            <span class="text-primary"><?php echo SITE_NAME; ?></span>
            <span class="text-secondary fs-6 ms-1 d-none d-md-inline">CRM</span>
        </a>
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNavDropdown">
            <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
                <!-- Features Dropdown -->
                <li class="nav-item dropdown mx-lg-2">
                    <a class="nav-link dropdown-toggle fw-medium" href="javascript:void(0);" id="navbarDropdownFeatures" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-cube me-1"></i> Features
                    </a>
                    <ul class="dropdown-menu shadow border-0 rounded-3" aria-labelledby="navbarDropdownFeatures">
                        <li><h6 class="dropdown-header">Core Features</h6></li>
                        <li><a class="dropdown-item" href="javascript:void(0);"><i class="fas fa-users me-2 text-primary"></i>Lead Management</a></li>
                        <li><a class="dropdown-item" href="javascript:void(0);"><i class="fas fa-tasks me-2 text-primary"></i>Task Management</a></li>
                        <li><a class="dropdown-item" href="javascript:void(0);"><i class="fas fa-bell me-2 text-primary"></i>Customer Reminders</a></li>
                        <li><a class="dropdown-item" href="javascript:void(0);"><i class="fas fa-sticky-note me-2 text-primary"></i>Notes Management</a></li>
                        <li><div class="dropdown-divider"></div></li>
                        <li><h6 class="dropdown-header">Advanced Features</h6></li>
                        <li><a class="dropdown-item" href="javascript:void(0);"><i class="fas fa-comment me-2 text-primary"></i>Live Chat</a></li>
                        <li><a class="dropdown-item" href="javascript:void(0);"><i class="fas fa-calendar me-2 text-primary"></i>Calendar</a></li>
                        <li><a class="dropdown-item" href="javascript:void(0);"><i class="fas fa-user-tie me-2 text-primary"></i>Staff Management</a></li>
                        <li><a class="dropdown-item" href="javascript:void(0);"><i class="fas fa-bullseye me-2 text-primary"></i>Target Management</a></li>
                        <li><a class="dropdown-item" href="javascript:void(0);"><i class="fas fa-bullhorn me-2 text-primary"></i>Campaign & Channels</a></li>
                        <li><a class="dropdown-item" href="javascript:void(0);"><i class="fas fa-cogs me-2 text-primary"></i>Service Management</a></li>
                    </ul>
                </li>
                <!-- Integrations Dropdown -->
                <li class="nav-item dropdown mx-lg-2">
                    <a class="nav-link dropdown-toggle fw-medium" href="javascript:void(0);" id="navbarDropdownIntegrations" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-plug me-1"></i> Integrations
                    </a>
                    <ul class="dropdown-menu shadow border-0 rounded-3" aria-labelledby="navbarDropdownIntegrations">
                        <li><h6 class="dropdown-header">Social Media</h6></li>
                        <li><a class="dropdown-item" href="javascript:void(0);"><i class="fab fa-facebook me-2 text-primary"></i>Facebook Lead</a></li>
                        <li><div class="dropdown-divider"></div></li>
                        <li><h6 class="dropdown-header">Real Estate</h6></li>
                        <li><a class="dropdown-item" href="javascript:void(0);"><i class="fas fa-building me-2 text-primary"></i>99acres</a></li>
                        <li><a class="dropdown-item" href="javascript:void(0);"><i class="fas fa-home me-2 text-primary"></i>Housing</a></li>
                        <li><a class="dropdown-item" href="javascript:void(0);"><i class="fas fa-city me-2 text-primary"></i>Magicbricks</a></li>
                        <li><div class="dropdown-divider"></div></li>
                        <li><h6 class="dropdown-header">Other Platforms</h6></li>
                        <li><a class="dropdown-item" href="javascript:void(0);"><i class="fas fa-globe me-2 text-primary"></i>Website Lead</a></li>
                        <li><a class="dropdown-item" href="javascript:void(0);"><i class="fab fa-google me-2 text-primary"></i>Google Ads</a></li>
                        <li><a class="dropdown-item" href="javascript:void(0);"><i class="fas fa-phone me-2 text-primary"></i>Just Dial</a></li>
                        <li><a class="dropdown-item" href="javascript:void(0);"><i class="fas fa-industry me-2 text-primary"></i>IndiaMart</a></li>
                        <li><a class="dropdown-item" href="javascript:void(0);"><i class="fas fa-briefcase me-2 text-primary"></i>TradeIndia</a></li>
                        <li><a class="dropdown-item" href="javascript:void(0);"><i class="fab fa-wordpress me-2 text-primary"></i>WordPress Website</a></li>
                        <li><a class="dropdown-item" href="javascript:void(0);"><i class="fab fa-google me-2 text-primary"></i>Google Form</a></li>
                    </ul>
                </li>
                <li class="nav-item mx-lg-2">
                    <a class="nav-link fw-medium" href="<?php echo SITE_URL; ?>/public/pricing.php"><i class="fas fa-tag me-1"></i> Pricing</a>
                </li>
                <li class="nav-item mx-lg-2">
                    <a class="nav-link fw-medium" href="<?php echo SITE_URL; ?>/public/blog.php"><i class="fas fa-rss me-1"></i> Blog</a>
                </li>
                <li class="nav-item mx-lg-2">
                    <a class="nav-link fw-medium" href="<?php echo SITE_URL; ?>/public/contact.php"><i class="fas fa-headset me-1"></i> Contact Us</a>
                </li>
            </ul>
            <div class="d-flex align-items-center mt-3 mt-lg-0">
                <!-- Search Icon -->
                <button class="btn btn-sm btn-outline-primary me-2 rounded-circle" type="button" id="searchButton">
                    <i class="fas fa-search"></i>
                </button>
                <!-- Login Button -->
                <a href="<?php echo SITE_URL; ?>/public/login.php" class="btn btn-primary px-4"><i class="fas fa-sign-in-alt me-2"></i>Login</a>
            </div>
        </div>
    </div>
</nav>

<!-- Placeholder for search overlay/modal -->
<div id="searchOverlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.8); z-index: 1050;">
    <div class="d-flex justify-content-center align-items-center" style="height: 100%;">
        <div class="search-popup bg-white p-4 rounded" style="width: 80%; max-width: 600px;">
            <h3>Search</h3>
            <div class="input-group mb-3">
                <input type="text" class="form-control" placeholder="Search..." aria-label="Search">
                <button class="btn btn-outline-secondary" type="button" id="closeSearch"><i class="fas fa-times"></i></button>
            </div>
            <!-- Search results would go here -->
        </div>
    </div>
</div>

<script>
document.getElementById('searchButton').addEventListener('click', function() {
    document.getElementById('searchOverlay').style.display = 'flex';
});

document.getElementById('closeSearch').addEventListener('click', function() {
    document.getElementById('searchOverlay').style.display = 'none';
});

// Close overlay if clicked outside the popup
document.getElementById('searchOverlay').addEventListener('click', function(event) {
    if (event.target === this) {
        this.style.display = 'none';
    }
});
</script> 