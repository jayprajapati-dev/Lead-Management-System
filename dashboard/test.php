<?php
// Start session if not already started - MUST be the first thing
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include configuration
require_once '../includes/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . SITE_URL . '/login.php');
    exit();
}

// You can include any necessary data fetching logic here for the test page

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Page</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/dashboard_style.css">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Link to your custom CSS file (if you have one with common styles) -->
    <!-- <link rel="stylesheet" href="css/dashboard_style.css"> -->
    <!-- Google Fonts (Poppins) -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        /* Add any specific styles for your test page here */

        /* Dashboard Layout Styles (copied from other dashboard pages) */
        .dashboard-container {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .sidebar {
            background-color: #fff;
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
            height: calc(100vh - 56px); /* Subtract header height */
            position: fixed;
            top: 56px; /* Start below header */
            left: 0;
            width: 16.666667%; /* col-md-2 width */
            overflow-y: auto;
            z-index: 1000;
            transition: all 0.3s;
            padding-top: 1rem;
        }
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0,0,0,0.5);
            z-index: 999;
        }
        .sidebar-overlay.show {
            display: block;
        }
        .main-content-area {
            margin-left: 16.666667%; /* col-md-2 width */
            transition: all 0.3s;
            padding-top: 1rem;
            min-height: calc(100vh - 56px); /* Subtract header height */
        }
        @media (max-width: 767.98px) {
            .sidebar {
                transform: translateX(-100%);
                width: 250px; /* Fixed width on mobile */
                top: 56px; /* Below header */
                z-index: 1030;
            }
            .sidebar.show {
                transform: translateX(0);
            }
            .main-content-area {
                margin-left: 0;
                width: 100%;
            }
            body.sidebar-open {
                overflow: hidden; /* Prevent scrolling when sidebar is open */
            }
        }
        /* We're using the navbar-toggler from header instead of this */
        .sidebar-toggle {
            display: none; /* Hide this since we're using header toggle */
        }
        .navbar {
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        footer {
            margin-top: auto;
            background-color: #f8f9fa;
            border-top: 1px solid #dee2e6;
        }
    </style>
</head>
<body>

<!-- Header -->
<?php include '../includes/dashboard-header.php'; ?>

<!-- Mobile Overlay -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<div class="container-fluid dashboard-container">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3 col-lg-2 d-md-block sidebar" id="sidebarMenu">
            <?php include '../includes/sidebar.php'; ?>
        </div>
        
        <!-- Main Content Area -->
        <div class="col-md-9 col-lg-10 ms-sm-auto px-md-4 main-content-area">

            <!-- Main Content -->
            <div class="container-fluid py-4 px-3">
                <div class="crm-section-card">
                    <h2>Test Page Content</h2>
                    <p>This is a test page to verify the dashboard layout and sidebar toggle.</p>
                    <!-- Add your test page specific content here -->
                </div>
            </div>

            <!-- Footer -->
            <?php include '../includes/dashboard-footer.php'; ?>
        </div>
    </div>
</div>

<!-- Bootstrap 5 JS and dependencies -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>

<!-- Script for sidebar toggle functionality -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Get references to sidebar elements
        const sidebarMenu = document.getElementById('sidebarMenu');
        const sidebarOverlay = document.getElementById('sidebarOverlay');
        const sidebarToggle = document.querySelector('.navbar-toggler'); // Using header toggle button
        
        // Toggle sidebar when the toggle button is clicked
        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', function() {
                sidebarMenu.classList.toggle('show');
                sidebarOverlay.classList.toggle('show');
                document.body.classList.toggle('sidebar-open');
            });
        }
        
        // Close sidebar when clicking on the overlay
        if (sidebarOverlay) {
            sidebarOverlay.addEventListener('click', function() {
                sidebarMenu.classList.remove('show');
                sidebarOverlay.classList.remove('show');
                document.body.classList.remove('sidebar-open');
            });
        }
        
        // Close sidebar when window is resized to desktop size
        window.addEventListener('resize', function() {
            if (window.innerWidth >= 768) {
                sidebarMenu.classList.remove('show');
                sidebarOverlay.classList.remove('show');
                document.body.classList.remove('sidebar-open');
            }
        });
    });
</script>

</body>
</html>