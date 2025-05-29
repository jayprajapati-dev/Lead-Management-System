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

// Fetch all active users for dropdowns
$usersList = [];
try {
    $usersResult = executeQuery("SELECT id, first_name, last_name FROM users WHERE status = 'active' ORDER BY first_name")->get_result();
    if ($usersResult) {
        while ($user = $usersResult->fetch_assoc()) {
            $usersList[] = [
                'id' => $user['id'],
                'name' => htmlspecialchars($user['first_name'] . ' ' . $user['last_name'])
            ];
        }
        $usersResult->free();
    }
} catch (Exception $e) {
    error_log("Error fetching users for dropdown: " . $e->getMessage());
    $usersList = [];
}

// Get user's email from session
$userEmail = $_SESSION['user_email'] ?? 'Guest';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leads Dashboard</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Link to your custom CSS file -->
    <link rel="stylesheet" href="css/dashboard_style.css">
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
        .card {
            border-radius: 15px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
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
            background-color: rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            padding: 5px 10px;
            font-weight: bold;
        }
        /* Custom colors for status boxes */
        .status-new { background-color: #20c997; } /* Teal */
        .status-processing { background-color: #6f42c1; } /* Dark Purple */
        .status-close-by { background-color: #28a745; } /* Green */
        .status-confirm { background-color: #1e7e34; } /* Dark Green */
        .status-cancel { background-color: #dc3545; } /* Red */

        .floating-button {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

         /* Adjustments for filter row alignment */
         .filter-row .col-md-3, .filter-row .col-md-2, .filter-row .col-md-4, .filter-row .col-md-6 {
            display: flex;
            align-items: center;
         }
         .filter-row .form-select, .filter-row .form-control {
             flex-grow: 1;
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
        /* Fix sizing issues */
        body {
            padding-top: 0; /* Remove any top padding */
            margin-top: 0; /* Remove any top margin */
        }
        
        .dashboard-container {
            padding-top: 0; /* Remove any top padding */
            margin-top: 0; /* Remove any top margin */
        }
        
        .main-content-area {
            padding-top: 0; /* Remove any top padding */
            margin-top: 0; /* Remove any top margin */
            transition: all 0.3s;
        }
        
        /* Responsive sidebar behavior */
        @media (max-width: 767.98px) {
            .sidebar {
                position: fixed;
                top: 0;
                left: 0;
                height: 100vh;
                z-index: 1000;
                transform: translateX(-100%);
                transition: transform 0.3s ease;
                box-shadow: none;
            }
            .sidebar.show {
                transform: translateX(0);
                box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            }
            .main-content-area {
                width: 100%;
                margin-left: 0;
            }
            .sidebar-overlay {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.5);
                z-index: 999;
                opacity: 0;
                transition: opacity 0.3s ease;
            }
            .sidebar-overlay.show {
                display: block;
                opacity: 1;
            }
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
                    <!-- Page content goes here -->
                <div class="container-fluid py-4">
                    <div class="crm-section-card pt-3">
                        <!-- Action Buttons Top Bar -->
                        <div class="d-flex justify-content-between align-items-center action-buttons-top-bar">
                            <div class="d-flex align-items-center view-toggle-group">
                                <button type="button" class="btn btn-outline-secondary" id="gridViewBtn" title="Grid View">
                                    <i class="fas fa-th-large"></i>
                                </button>
                                <button type="button" class="btn btn-outline-secondary active" id="listViewBtn" title="List View">
                                    <i class="fas fa-list"></i>
                                </button>
                                <h2 class="page-title ms-3 mb-0">Leads</h2>
                            </div>
                            
                            <!-- Board View Buttons -->
                            <div class="action-buttons-group d-none" id="boardViewButtons">
                                <button class="btn btn-primary" title="Add New Lead">
                                    <i class="fas fa-plus"></i>
                                    <span>Add</span>
                                </button>
                                <button class="btn btn-secondary" title="Board Settings">
                                    <i class="fas fa-cog"></i>
                                    <span>Settings</span>
                                </button>
                                <button class="btn btn-info" title="Sort Leads">
                                    <i class="fas fa-sort"></i>
                                    <span>Sort</span>
                                </button>
                             </div>

                            <!-- List View Buttons -->
                            <div class="action-buttons-group" id="listViewButtons">
                                <button class="btn btn-primary" title="Add New Lead">
                                    <i class="fas fa-plus"></i>
                                    <span>Add</span>
                                </button>
                                <button class="btn btn-secondary" title="Sort Leads">
                                    <i class="fas fa-sort"></i>
                                    <span>Sort</span>
                                </button>
                                <button class="btn btn-info" title="Download Leads">
                                    <i class="fas fa-download"></i>
                                    <span>Download</span>
                                </button>
                                <button class="btn btn-warning" title="View Analytics">
                                    <i class="fas fa-chart-bar"></i>
                                    <span>Graph</span>
                                </button>
                                <button class="btn btn-dark" title="Manage Tags">
                                    <i class="fas fa-tags"></i>
                                    <span>Tag</span>
                                </button>
                                <button class="btn btn-danger" title="Delete Selected">
                                    <i class="fas fa-trash"></i>
                                    <span>Delete</span>
                                </button>
                              </div>
                        </div>

                        <!-- Top Filter Section -->
                        <div class="card p-3 mb-4">
                            <div class="row g-3 filter-row">
                                <!-- First row of filters -->
                                <div class="col-md-3">
                                    <label for="createdBySelect" class="form-label visually-hidden">Created By</label>
                                    <select class="form-select" id="createdBySelect">
                                        <option selected>Created By: All Lead</option>
                                        <?php foreach ($usersList as $user): ?>
                                            <option value="<?php echo $user['id']; ?>"><?php echo $user['name']; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                     <label for="assignToSelect" class="form-label visually-hidden">Assign To</label>
                                    <select class="form-select" id="assignToSelect">
                                        <option selected>Assign To: All Assign</option>
                                         <?php foreach ($usersList as $user): ?>
                                            <option value="<?php echo $user['id']; ?>"><?php echo $user['name']; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                     <label for="labelsSelect" class="form-label visually-hidden">Labels</label>
                                     <select class="form-select" id="labelsSelect">
                                        <option selected>Labels: All Labels</option>
                                         <!-- Options for Labels will go here -->
                                    </select>
                                </div>
                                <div class="col-md-3">
                                     <label for="sourceSelect" class="form-label visually-hidden">Source</label>
                                     <select class="form-select" id="sourceSelect">
                                        <option selected>Source: All Source</option>
                                         <!-- Options for Source will go here -->
                                    </select>
                                </div>

                                <!-- Second row of filters -->
                                <div class="col-md-3">
                                     <label for="statusSelect" class="form-label visually-hidden">Status</label>
                                     <select class="form-select" id="statusSelect">
                                        <option selected>Status: All Status</option>
                                         <!-- Options for Status will go here -->
                                    </select>
                                </div>
                                <div class="col-md-3">
                                     <label for="searchDateInput" class="form-label visually-hidden">Search By Date</label>
                                    <div class="input-group">
                                         <input type="text" class="form-control" id="searchDateInput" placeholder="Search By Date">
                                         <button class="btn btn-outline-secondary" type="button">📅</button>
                                    </div>
                                </div>
                                 <div class="col-md-6">
                                      <label for="searchInput" class="form-label visually-hidden">Search</label>
                                     <div class="input-group">
                                        <input type="text" class="form-control" id="searchInput" placeholder="Search...">
                                        <button class="btn btn-outline-secondary" type="button">🔍</button>
                                        <button class="btn btn-outline-secondary" type="button">✖️</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Status Boxes Section -->
                        <div class="row mb-4" id="statusBoxesRow">
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
                                <div class="status-box status-close-by">
                                    Close-by <span class="count-badge">0</span>
                                </div>
                            </div>
                            <div class="col-md col-sm-6">
                                <div class="status-box status-confirm">
                                    Confirm <span class="count-badge">0</span>
                                </div>
                            </div>
                            <div class="col-md col-sm-6">
                                <div class="status-box status-cancel">
                                    Cancel <span class="count-badge">0</span>
                                </div>
                            </div>
                        </div>

                        <!-- Main Content Area -->
                        <div class="card p-4">
                            <!-- View Toggled Content -->
                            <div id="leadsGridView" class="d-none">
                                <div class="text-center text-muted mb-3 no-records-message">
                                    There are no records to display (Grid View)
                                </div>
                                <!-- Placeholder for Grid View Lead Records -->
                                <div class="row leads-grid-container">
                                    <!-- Lead records will be dynamically loaded/displayed here in a grid format -->
                                </div>
                            </div>

                            <div id="leadsListView">
                                <div class="text-center text-muted mb-3 no-records-message">
                                    There are no records to display (List View)
                                </div>
                                <!-- Placeholder for List View Lead Records -->
                                <ul class="list-group leads-list-container d-none">
                                     <!-- Lead records will be dynamically loaded/displayed here in a list format -->
                                </ul>
                            </div>
                        </div>

                        <!-- Floating Action Button -->
                        <button class="btn btn-primary rounded-circle floating-button">↑</button>

                        <!-- Bottom Loading Bar Placeholder -->
                        <div class="progress fixed-bottom" style="height: 5px; z-index: 1001;">
                            <div class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>

                    </div>
                    <!-- End Page content -->
                </div>
                <!-- End Main Content Body -->

                <!-- Footer -->
                <?php include '../includes/dashboard-footer.php'; ?>
            </div>
            <!-- End Main Content Area -->
        </div>
    </div>
    <!-- End Dashboard Container -->

    <!-- Bootstrap 5 JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Embed PHP variable containing users list into JavaScript
            const usersForDropdown = <?php echo json_encode($usersList); ?>;

            // Function to populate user dropdowns
            function populateUserDropdowns(users) {
                const createdBySelect = document.getElementById('createdBySelect');
                const assignToSelect = document.getElementById('assignToSelect');

                [createdBySelect, assignToSelect].forEach(select => {
                    if (select && users && users.length > 0) {
                         // Preserve the first option and clear the rest
                        const initialOption = select.querySelector('option[selected]');
                        select.innerHTML = ''; // Clear all options
                        if(initialOption) { // Add the initial option back
                            select.appendChild(initialOption);
                        }
                        
                        // Add user options
                        users.forEach(user => {
                            const option = document.createElement('option');
                            option.value = user.id;
                            option.textContent = user.name;
                            select.appendChild(option);
                        });
                    }
                });
            }

            // Call the function to populate dropdowns if users data is available
            if (typeof usersForDropdown !== 'undefined' && usersForDropdown.length > 0) {
                populateUserDropdowns(usersForDropdown);
            }

            // View toggle functionality
            const gridViewBtn = document.getElementById('gridViewBtn');
            const listViewBtn = document.getElementById('listViewBtn');
            const gridView = document.getElementById('leadsGridView');
            const listView = document.getElementById('leadsListView');
            const boardViewButtons = document.getElementById('boardViewButtons');
            const listViewButtons = document.getElementById('listViewButtons');
            const statusBoxesRow = document.getElementById('statusBoxesRow');

            // Function to update view based on button click
            function updateView(activeView) {
                // Toggle active class on view buttons
                if (activeView === 'grid') {
                    listViewBtn.classList.remove('active');
                    gridViewBtn.classList.add('active');
                    listView.classList.add('d-none');
                    gridView.classList.remove('d-none');
                    boardViewButtons.classList.remove('d-none');
                    listViewButtons.classList.add('d-none');
                    // Show status filter for Board View
                    if (statusBoxesRow) statusBoxesRow.classList.remove('d-none');

                     // Check for lead items and toggle messages/status based on data for grid view
                    const gridLeadItemsContainer = gridView.querySelector('.leads-grid-container');
                    const gridNoRecordsMessage = gridView.querySelector('.no-records-message');
                     if (gridLeadItemsContainer && gridLeadItemsContainer.querySelectorAll('.lead-item').length > 0) {
                         if (gridNoRecordsMessage) gridNoRecordsMessage.classList.add('d-none');
                         // Status boxes are always shown in grid view
                     } else {
                         if (gridNoRecordsMessage) gridNoRecordsMessage.classList.remove('d-none');
                         // Status boxes are always shown in grid view
                     }

                } else { // list view
                    gridViewBtn.classList.remove('active');
                    listViewBtn.classList.add('active');
                    gridView.classList.add('d-none');
                    listView.classList.remove('d-none');
                    boardViewButtons.classList.add('d-none');
                    listViewButtons.classList.remove('d-none');

                    // Hide status filter for List View
                    if (statusBoxesRow) statusBoxesRow.classList.add('d-none');

                    // Check for lead items and toggle messages/data based on data for list view
                    const listLeadItemsContainer = listView.querySelector('.leads-list-container');
                    const listNoRecordsMessage = listView.querySelector('.no-records-message');

                    if (listLeadItemsContainer && listLeadItemsContainer.querySelectorAll('.lead-item').length > 0) {
                         if (listNoRecordsMessage) listNoRecordsMessage.classList.add('d-none');
                         listLeadItemsContainer.classList.remove('d-none'); // Show list items
                    } else {
                        if (listNoRecordsMessage) listNoRecordsMessage.classList.remove('d-none');
                        listLeadItemsContainer.classList.add('d-none'); // Hide list items if no data
                    }
                }

                 // Ensure the 'no records' message in the other view is hidden
                 const otherViewElement = (activeView === 'grid') ? listView : gridView;
                 const otherNoRecordsMessage = otherViewElement.querySelector('.no-records-message');
                 if(otherNoRecordsMessage) otherNoRecordsMessage.classList.add('d-none');

            }

            // Initial setup: Set Board View as default active and update view display
             // Check if elements exist before setting initial state
             if (gridViewBtn && listViewBtn && boardViewButtons && listViewButtons && statusBoxesRow && gridView && listView) {
                updateView('grid'); // Set initial view to Board (grid)
             }

            if (gridViewBtn && listViewBtn) {
                gridViewBtn.addEventListener('click', function() {
                    updateView('grid');
                });

                listViewBtn.addEventListener('click', function() {
                    updateView('list');
                });
            }
        });
    </script>

    <!-- Common Modals can be included here if needed -->
    <!-- Example: Success Toast -->
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
        <div id="successToast" class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    Operation completed successfully!
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>

    <!-- Error Toast -->
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
        <div id="errorToast" class="toast align-items-center text-white bg-danger border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    An error occurred. Please try again.
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>

<!-- Sidebar toggle functionality is now handled in dashboard-header.php -->
</body>
</html>