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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Dashboard</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Link to your custom CSS file (if you have one with common styles) -->
    <!-- <link rel="stylesheet" href="css/dashboard_style.css"> -->
    <!-- Google Fonts (Poppins) -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/dashboard_style.css">
    <style>
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
        .status-box {
            border-radius: 10px;
            color: white;
            padding: 15px;
            text-align: center;
            margin-bottom: 15px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        .status-box .count-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background-color: #e83e8c; /* Pink badge color */
            color: white;
            border-radius: 50%;
            padding: 5px 10px;
            font-weight: bold;
        }
        /* Custom colors for status boxes */
        .status-new { background-color: #007a80; } /* Blue-green */
        .status-processing { background-color: #5a3e8c; } /* Dark purple */
        .status-in-feedback { background-color: #5a3e8c; } /* Dark purple */
        .status-completed { background-color: #669900; } /* Green */
        .status-rejected { background-color: #cc0000; } /* Red */

        .empty-state-message {
            text-align: center;
            color: #6c757d; /* Muted text color */
            padding: 30px 0;
        }

        .view-toggle-buttons .btn {
             font-size: 1.2rem;
             padding: 8px 15px;
         }

         .action-buttons-container {
             align-items: center;
         }
         .action-buttons-container .btn {
             font-size: 0.9rem;
             padding: 8px 15px; /* Adjust padding to match screenshot */
             border-radius: 8px; /* Rounded corners */
         }
         .action-buttons-container .btn-danger { /* Specific style for Trash button */
             background-color: #dc3545;
             border-color: #dc3545;
         }
         .action-buttons-container .btn-primary { /* Specific style for Add Task button */
             background-color: #007bff;
             border-color: #007bff;
         }
          .action-buttons-container .btn-secondary { /* Specific style for Calendar button */
             background-color: #007bff;
             border-color: #007bff;
             color: white;
         }
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

        /* Updated styles for action buttons header */
        .action-buttons-top-bar {
            background-color: #fff;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .action-buttons-top-bar .view-toggle-group {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .action-buttons-top-bar .view-toggle-group .btn {
            padding: 0.375rem 0.75rem;
            font-size: 1rem;
            border-radius: 4px;
        }

        .action-buttons-top-bar .action-buttons-group {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .action-buttons-top-bar .action-buttons-group .btn {
            padding: 0.5rem 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            white-space: nowrap;
        }

        .action-buttons-top-bar .page-title {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 600;
            color: #2c3e50;
        }

        @media (max-width: 768px) {
            .action-buttons-top-bar {
                flex-direction: column;
                gap: 1rem;
            }
            .action-buttons-top-bar .action-buttons-group {
                width: 100%;
                justify-content: flex-start;
            }
            .action-buttons-top-bar .btn span {
                display: none;
            }
            .action-buttons-top-bar .btn {
                padding: 0.5rem;
            }
        }

    </style>
</head>
<body>

<div class="dashboard-container">
    <div class="row g-0">
        <!-- Mobile Toggle Button -->
        <button class="sidebar-toggle d-md-none" id="sidebarToggle">
            <i class="fas fa-bars"></i>
        </button>
        <!-- Mobile Overlay -->
        <div class="sidebar-overlay" id="sidebarOverlay"></div>
        <!-- Sidebar -->
        <div class="col-md-3 col-lg-2 sidebar" id="sidebarMenu">
            <?php include '../includes/sidebar.php'; ?>
        </div>

        <!-- Main Content Area -->
        <div class="col-md-9 col-lg-10 main-content-area">
            <!-- Header -->
            <?php include '../includes/dashboard-header.php'; ?>

            <!-- Main Content -->
            <div class="container-fluid py-4">
                <div class="crm-section-card">
                    <!-- Top Bar -->
                    <div class="d-flex justify-content-between align-items-center action-buttons-top-bar">
                        <div class="d-flex align-items-center view-toggle-group">
                            <button type="button" class="btn btn-outline-secondary active" id="kanbanViewBtn" title="Kanban View">
                                <i class="fas fa-th-large"></i>
                            </button>
                            <button type="button" class="btn btn-outline-secondary" id="listViewBtn" title="List View">
                                <i class="fas fa-list"></i>
                            </button>
                            <h2 class="page-title ms-3 mb-0">Tasks</h2>
                        </div>

                        <!-- Kanban View Buttons -->
                        <div class="action-buttons-group" id="kanbanViewButtons">
                            <button class="btn btn-primary" title="Add New Task">
                                <i class="fas fa-plus"></i>
                                <span>Add Task</span>
                            </button>
                            <button class="btn btn-secondary" title="View Calendar">
                                <i class="fas fa-calendar"></i>
                                <span>Calendar</span>
                            </button>
                        </div>

                        <!-- List View Buttons -->
                        <div class="action-buttons-group d-none" id="listViewButtons">
                            <button class="btn btn-danger" title="View Trash">
                                <i class="fas fa-trash"></i>
                                <span>Trash</span>
                            </button>
                            <button class="btn btn-primary" title="Add New Task">
                                <i class="fas fa-plus"></i>
                                <span>Add Task</span>
                            </button>
                            <button class="btn btn-secondary" title="View Calendar">
                                <i class="fas fa-calendar"></i>
                                <span>Calendar</span>
                            </button>
                        </div>
                    </div>

                    <!-- Top Filter Section -->
                     <div class="card p-3 mb-4">
                         <div class="row g-3 filter-row">
                             <div class="col-md-3">
                                 <select class="form-select">
                                     <option selected>All Priority</option>
                                     <!-- Priority options here -->
                                 </select>
                             </div>
                              <div class="col-md-3">
                                 <select class="form-select">
                                     <option selected>Labels</option>
                                     <!-- Label options here -->
                                 </select>
                             </div>
                             <div class="col-md-3">
                                 <select class="form-select">
                                     <option selected>All Created By</option>
                                     <!-- Created By options here -->
                                 </select>
                             </div>
                              <div class="col-md-3">
                                 <select class="form-select">
                                     <option selected>All Assign To</option>
                                     <!-- Assign To options here -->
                                 </select>
                             </div>
                              <div class="col-md-3">
                                 <select class="form-select">
                                     <option selected>All Status</option>
                                     <!-- Status options here -->
                                 </select>
                             </div>
                             <div class="col-md-9">
                                  <div class="input-group">
                                     <input type="text" class="form-control" placeholder="Search...">
                                     <button class="btn btn-outline-secondary" type="button">🔍</button>
                                     <button class="btn btn-outline-secondary" type="button">✖️</button>
                                 </div>
                             </div>
                         </div>
                     </div>

                    <!-- Status Filter (Tabs) Section - Shown only in Kanban View -->
                    <div class="row mb-4" id="statusFilterRow">
                         <div class="col-md col-sm-6">
                            <div class="status-box status-new">
                                New <span class="count-badge">0</span>
                            </div>
                        </div>
                        <div class="col-md col-sm-6">
                            <div class="status-box status-processing">
                                Processing <span class="count-badge">0</span>
                            </div>
                        </div>
                         <div class="col-md col-sm-6">
                            <div class="status-box status-in-feedback">
                                In Feedback <span class="count-badge">0</span>
                            </div>
                        </div>
                        <div class="col-md col-sm-6">
                            <div class="status-box status-completed">
                                Completed <span class="count-badge">0</span>
                            </div>
                        </div>
                        <div class="col-md col-sm-6">
                            <div class="status-box status-rejected">
                                Rejected <span class="count-badge">0</span>
                            </div>
                        </div>
                    </div>


                    <!-- Main Content Area (Empty State) -->
                    <div id="tasksContentArea">
                        <div class="empty-state-message">
                            <p>There are no records to display</p>
                        </div>
                         <!-- Area where task data (Kanban board or list) would eventually be loaded -->
                         <!-- Initially hidden as there are no records -->
                         <div id="tasksDataDisplay" class="d-none">
                             <!-- Dynamic task data will go here -->
                         </div>
                    </div>


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

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const kanbanViewBtn = document.getElementById('kanbanViewBtn');
        const listViewBtn = document.getElementById('listViewBtn');
        const kanbanViewButtons = document.getElementById('kanbanViewButtons');
        const listViewButtons = document.getElementById('listViewButtons');
        const statusFilterRow = document.getElementById('statusFilterRow');
        const emptyStateMessage = document.querySelector('#tasksContentArea .empty-state-message');
        const tasksDataDisplay = document.getElementById('tasksDataDisplay');

        // Function to update view based on button click
        function updateView(activeView) {
            // Toggle active class on view buttons
            if (activeView === 'kanban') {
                listViewBtn.classList.remove('active');
                kanbanViewBtn.classList.add('active');
                listViewButtons.classList.add('d-none');
                kanbanViewButtons.classList.remove('d-none');
                 console.log('Switched to Kanban View');
                // Show status filter in Kanban View
                if (statusFilterRow) statusFilterRow.classList.remove('d-none');

            } else { // list view
                kanbanViewBtn.classList.remove('active');
                listViewBtn.classList.add('active');
                kanbanViewButtons.classList.add('d-none');
                listViewButtons.classList.remove('d-none');
                 console.log('Switched to List View');
                 // Hide status filter in List View
                 if (statusFilterRow) statusFilterRow.classList.add('d-none');
            }

            // In this version with no data, always show the empty state message
            // and hide the data display area, regardless of view.
             if (emptyStateMessage) emptyStateMessage.classList.remove('d-none');
             if (tasksDataDisplay) tasksDataDisplay.classList.add('d-none');

        }

        // Initial setup: Set Kanban View as default active and update view display
        // Check if elements exist before adding listeners or modifying classes
        if (kanbanViewBtn && listViewBtn && kanbanViewButtons && listViewButtons && statusFilterRow && emptyStateMessage && tasksDataDisplay) {

             // Set Kanban View as default active state
             kanbanViewBtn.classList.add('active');
             listViewBtn.classList.remove('active');
             kanbanViewButtons.classList.remove('d-none'); // Show kanban buttons initially
             listViewButtons.classList.add('d-none'); // Hide list buttons initially
             // Show status filter initially for the default Kanban View
             statusFilterRow.classList.remove('d-none');


            // Add click listeners
            kanbanViewBtn.addEventListener('click', function() {
                updateView('kanban');
            });

            listViewBtn.addEventListener('click', function() {
                updateView('list');
            });

            console.log('Script initialized. Event listeners attached.');

        } else {
            console.error('Could not find one or more required elements for view toggle or empty state.');
        }
    });
</script>

</body>
</html> 