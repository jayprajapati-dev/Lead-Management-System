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

// Fetch active users for dropdowns if needed (example)
// $usersList = [];
// try { ... fetch users ... } catch (Exception $e) { ... }

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reminders Dashboard</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Link to your custom CSS file -->
    <link rel="stylesheet" href="css/dashboard_style.css">
    <!-- Google Fonts (Poppins) -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Add or override styles specific to the reminders page here */
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .crm-section-card {
            background-color: #fff;
            border-radius: 15px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }
         .empty-state-message {
            text-align: center;
            color: #6c757d; /* Muted text color */
            padding: 30px 0;
        }
         /* Custom styles for the quick action buttons next to title */
        .action-buttons-row .btn {
            margin-right: 0.5rem; /* Space between buttons */
            display: flex;
            align-items: center;
            gap: 0.25rem; /* Space between icon and text */
            padding: 0.5rem 1rem; /* Adjust padding */
            border-radius: 0.5rem; /* Rounded corners */
        }
         /* Specific button colors as requested */
         .action-buttons-row .btn-lead { background-color: #007bff; border-color: #007bff; color: white; }
         .action-buttons-row .btn-task { background-color: #28a745; border-color: #28a745; color: white; }
         .action-buttons-row .btn-note { background-color: #ffc107; border-color: #ffc107; color: white; }
         .action-buttons-row .btn-reminder { background-color: #6f42c1; border-color: #6f42c1; color: white; }

         /* Adjustments for filter row alignment */
         .filter-row .col-md-3, .filter-row .col-md-2, .filter-row .col-md-4, .filter-row .col-md-6 {
            display: flex;
            align-items: center;
         }
         .filter-row .form-select, .filter-row .form-control {
             flex-grow: 1;
         }
         .filter-row .form-control, .filter-row .form-select {
             border-radius: 8px; /* Rounded input/select */
             box-shadow: inset 0 1px 2px rgba(0,0,0,.075); /* Subtle shadow */
         }
          .input-group .btn {
             border-radius: 0 8px 8px 0 !important; /* Rounded right side of input group buttons */
         }
           .input-group .form-control {
             border-radius: 8px 0 0 8px !important; /* Rounded left side of input group control */
         }

        /* Styles for the new action button group */
        .reminder-action-buttons .btn {
            width: 40px; /* Set a fixed width for square shape */
            height: 40px; /* Set a fixed height for square shape */
            padding: 0; /* Remove padding to center icon */
            display: flex; /* Use flex to center icon */
            justify-content: center; /* Center horizontally */
            align-items: center; /* Center vertically */
            border-radius: 8px; /* Rounded corners */
            font-size: 1.2rem; /* Adjust icon size */
            margin-left: 0.5rem; /* Space to the left of each button */
        }
        .reminder-action-buttons .btn:first-child {
             margin-left: 0; /* No left margin for the first button */
        }
         /* Specific colors for the new action buttons */
         .btn-purple-square { background-color: #6f42c1; border-color: #6f42c1; color: white; }
         .btn-white-outline-red { background-color: white; border-color: #dc3545; color: #dc3545; }
         .btn-darkblue-square { background-color: #00008b; border-color: #00008b; color: white; }
         .btn-red-square { background-color: #dc3545; border-color: #dc3545; color: white; }

         /* Dashboard Layout Styles */
        .dashboard-container {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .sidebar {
            background-color: #fff;
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;
            transition: all 0.3s;
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
            margin-top: 0 !important;
            padding-top: 1rem; /* Add consistent top padding */
        }
        @media (max-width: 767.98px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .sidebar.show {
                transform: translateX(0);
            }
            .main-content-area {
                margin-left: 0;
            }
             /* Stack buttons and filters on mobile */
            .filter-row {
                flex-direction: column;
                align-items: stretch;
            }
             .filter-row .col-md-3, .filter-row .col-md-2, .filter-row .col-md-4, .filter-row .col-md-6 {
                 width: 100%; /* Full width on mobile */
                 margin-bottom: 0.5rem; /* Space between stacked items */
             }
             .filter-row .input-group {
                 width: 100%; /* Full width input group */
             }
             .reminder-action-buttons {
                 justify-content: center; /* Center buttons when stacked */
                 margin-top: 1rem; /* Space above buttons when stacked */
             }
             .reminder-action-buttons .btn {
                 margin: 0.25rem; /* Reduce margin when stacked */
             }
        }
        .sidebar-toggle {
            position: fixed;
            top: 1rem;
            left: 1rem;
            z-index: 1001;
            background: #fff;
            border: none;
            border-radius: 4px;
            padding: 0.5rem;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .navbar {
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        footer {
            margin-top: auto;
            background-color: #f8f9fa;
            border-top: 1px solid #dee2e6;
        }
         /* Styles for active sidebar link */
        .sidebar .nav-link.active {
            color: #007bff; /* Bootstrap primary blue */
            font-weight: 600;
            /* Add other styles as needed, e.g., background-color */
             background-color: #e9ecef; /* Light grey background */
             border-radius: 5px;
        }
         .sidebar .nav-link.active i {
             color: #007bff; /* Match icon color to text */
         }
    </style>
</head>
<body>
    <!-- Mobile Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    
    <!-- Header first, outside the main container -->
    <?php include '../includes/dashboard-header.php'; ?>
    
    <div class="dashboard-container container-fluid">
        <div class="row">
            <div class="col-md-3 col-lg-2 sidebar" id="sidebarMenu">
                <?php include '../includes/sidebar.php'; ?>
            </div>
            
            <!-- Main Content Area -->
            <div class="col-md-9 col-lg-10 main-content-area">

            <!-- Main Content Body -->
            <div class="dashboard-body">
                <div class="container-fluid py-4">
                    <div class="crm-section-card">
                        <!-- Page Title and Action Buttons (Add New) -->
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h2 class="mb-0">Reminders</h2>
                        </div>

                        <!-- Filter and Secondary Action Buttons Section -->
                        <div class="card p-3 mb-4">
                            <div class="row g-3 align-items-center filter-row">
                                <!-- Filter Dropdowns -->
                                <div class="col-md-3 col-lg-3">
                                    <label for="remindersCreatedBy" class="form-label visually-hidden">Created By</label>
                                    <select class="form-select" id="remindersCreatedBy">
                                        <option selected>Created By: All Reminders</option>
                                        <!-- Options will go here -->
                                    </select>
                                </div>
                                <div class="col-md-3 col-lg-3">
                                     <label for="remindersAssignTo" class="form-label visually-hidden">Assign To</label>
                                    <select class="form-select" id="remindersAssignTo">
                                        <option selected>Assign To: All Reminders</option>
                                         <!-- Options will go here -->
                                    </select>
                                </div>
                                 <div class="col-md-2 col-lg-2">
                                     <label for="remindersType" class="form-label visually-hidden">Type</label>
                                     <select class="form-select" id="remindersType">
                                        <option selected>Type: All</option>
                                         <option value="All">All</option>
                                         <option value="Once">Once</option>
                                         <option value="Daily">Daily</option>
                                         <option value="Weekly">Weekly</option>
                                         <option value="Monthly">Monthly</option>
                                         <option value="Quarterly">Quarterly</option>
                                         <option value="Half-Yearly">Half-Yearly</option>
                                         <option value="Yearly">Yearly</option>
                                     </select>
                                 </div>
                                 <!-- Search Input and Secondary Action Buttons -->
                                 <div class="col-md-4 col-lg-4">
                                      <label for="remindersSearchInput" class="form-label visually-hidden">Search</label>
                                     <div class="input-group">
                                        <input type="text" class="form-control" id="remindersSearchInput" placeholder="Search...">
                                        <button class="btn btn-outline-secondary" type="button">🔍</button>
                                        <button class="btn btn-outline-secondary" type="button">✖️</button>
                                    </div>
                                </div>

                                <!-- New row for secondary action buttons on mobile, next to search on desktop -->
                                <div class="col-12 d-md-none"></div> <!-- Spacer column for mobile stacking -->

                                <div class="col-md-12 col-lg-auto d-flex justify-content-end reminder-action-buttons">
                                    <button class="btn btn-purple-square" title="Add"><i class="fas fa-plus"></i></button>
                                    <button class="btn btn-white-outline-red" title="Cancel Selection"><i class="fas fa-times"></i></button>
                                    <button class="btn btn-darkblue-square" title="View Calendar"><i class="fas fa-calendar-alt"></i></button>
                                    <button class="btn btn-darkblue-square" title="View Analytics"><i class="fas fa-chart-bar"></i></button>
                                    <button class="btn btn-red-square" title="Delete Selected"><i class="fas fa-trash"></i></button>
                                </div>

                            </div>
                        </div>

                        <!-- Main Content Area -->
                        <div class="card p-4">
                             <div class="empty-state-message">
                                 <p>There are no records to display</p>
                            </div>
                            <!-- Reminder records will be dynamically loaded here -->
                             <div id="remindersDataDisplay" class="d-none">
                                 <!-- Dynamic reminder data will go here -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End Main Content Body -->

            <!-- Footer -->
            <?php include '../includes/dashboard-footer.php'; ?>
        </div>
        <!-- End Main Content Area -->
    </div>
</div>

<!-- Bootstrap 5 JS and dependencies -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>

<!-- You might need additional scripts here for filtering, searching, or dynamic loading -->

<!-- Sidebar toggle functionality is now handled in dashboard-header.php -->

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add any reminders-specific JavaScript here
});
</script>
</body>
</html> 