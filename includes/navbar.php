<?php
// includes/navbar.php
// This file contains the navigation bar
?>

<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container-fluid">
        <a class="navbar-brand" href="<?php echo SITE_URL; ?>">
            <!-- Replace with your actual logo -->
            <img src="<?php echo SITE_URL; ?>/public/assets/images/logo.png" alt="<?php echo SITE_NAME; ?> Logo" height="40">
            <?php // echo SITE_NAME; // Or display site name if no logo ?>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNavDropdown">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <!-- Features Dropdown -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownFeatures" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Features
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="navbarDropdownFeatures">
                        <li><a class="dropdown-item" href="#">Lead Management</a></li>
                        <li><a class="dropdown-item" href="#">Task Management</a></li>
                        <li><a class="dropdown-item" href="#">Customer Reminders & Meeting</a></li>
                        <li><a class="dropdown-item" href="#">Notes Management</a></li>
                        <li><a class="dropdown-item" href="#">Live Chat</a></li>
                        <li><a class="dropdown-item" href="#">Calendar</a></li>
                        <li><a class="dropdown-item" href="#">Staff Management</a></li>
                        <li><a class="dropdown-item" href="#">Target Management</a></li>
                        <li><a class="dropdown-item" href="#">Campaign & Channels</a></li>
                        <li><a class="dropdown-item" href="#">Service Management</a></li>
                        <li><a class="dropdown-item" href="#">Integrations</a></li>
                        <li><a class="dropdown-item" href="#">Greetings</a></li>
                    </ul>
                </li>
                <!-- Integrations Dropdown -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownIntegrations" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Integrations
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="navbarDropdownIntegrations">
                        <li><a class="dropdown-item" href="#">Facebook Lead</a></li>
                        <li><a class="dropdown-item" href="#">Website Lead</a></li>
                        <li><a class="dropdown-item" href="#">IndiaMart</a></li>
                        <li><a class="dropdown-item" href="#">99acres</a></li>
                        <li><a class="dropdown-item" href="#">Google Ads</a></li>
                        <li><a class="dropdown-item" href="#">Housing</a></li>
                        <li><a class="dropdown-item" href="#">Just Dial</a></li>
                        <li><a class="dropdown-item" href="#">Magicbricks</a></li>
                        <li><a class="dropdown-item" href="#">Software Suggest</a></li>
                        <li><a class="dropdown-item" href="#">TradeIndia</a></li>
                        <li><a class="dropdown-item" href="#">WordPress Website</a></li>
                        <li><a class="dropdown-item" href="#">Google Form</a></li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo SITE_URL; ?>/pricing.php">Pricing</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">Blog</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">Contact Us</a>
                </li>
            </ul>
            <div class="d-flex">
                <!-- Search Icon (Placeholder for now) -->
                <button class="btn btn-outline-secondary me-2" type="button" id="searchButton">
                    <i class="fas fa-search"></i>
                </button>
                <!-- Login Button -->
                <a href="<?php echo SITE_URL; ?>/login.php" class="btn btn-primary">Login</a>
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