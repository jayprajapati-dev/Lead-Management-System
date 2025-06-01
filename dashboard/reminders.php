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
    <title>Reminders | Lead Management System</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
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

        /* Dashboard Layout Styles */
        .dashboard-container {
            min-height: calc(100vh - 60px); /* Subtract header height */
            padding-top: 60px; /* Add padding for fixed header */
        }

        .sidebar {
            position: fixed;
            top: 60px; /* Position below header */
            left: 0;
            bottom: 0;
            width: 250px;
            background-color: #fff;
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
            z-index: 1000;
            transition: all 0.3s;
            overflow-y: auto;
        }

        .main-content-area {
            margin-left: 250px; /* Match sidebar width */
            padding: 20px;
            min-height: calc(100vh - 60px); /* Subtract header height */
            background-color: #f8f9fa;
            transition: margin-left 0.3s;
        }

        @media (max-width: 768px) {
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

        .crm-section-card {
            background-color: #fff;
            border-radius: 15px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 20px;
        }

        /* Action buttons styling */
        .action-buttons-top-bar {
            margin-bottom: 20px;
        }

        .action-buttons-top-bar .action-buttons-group {
            display: flex;
            gap: 8px;
            align-items: center;
        }

        .action-buttons-top-bar .action-buttons-group .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
        }

        /* Filter row styling */
         .filter-row .col-md-3, .filter-row .col-md-2, .filter-row .col-md-4, .filter-row .col-md-6 {
            display: flex;
            align-items: center;
         }
        
         .filter-row .form-select, .filter-row .form-control {
             flex-grow: 1;
            border-radius: 8px;
            box-shadow: inset 0 1px 2px rgba(0,0,0,.075);
        }

        /* Mobile responsiveness */
        @media (max-width: 768px) {
            .dashboard-container {
                padding-top: 56px; /* Adjust for mobile header height */
         }
            
            .action-buttons-top-bar {
                flex-direction: column;
                gap: 1rem;
         }

            .action-buttons-top-bar .action-buttons-group {
                width: 100%;
                flex-wrap: wrap;
                justify-content: center;
            }
            
            .action-buttons-top-bar .action-buttons-group .btn {
                padding: 8px;
            }
            
            .action-buttons-top-bar .action-buttons-group .btn span {
                display: none;
            }

            .filter-row {
            flex-direction: column;
        }
            
            .filter-row .col-md-3, .filter-row .col-md-2, .filter-row .col-md-4, .filter-row .col-md-6 {
                width: 100%;
                margin-bottom: 0.5rem;
        }
        }

        /* Sidebar overlay */
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
    </style>
</head>
<body class="bg-light">
    <!-- Header -->
    <?php include '../includes/dashboard-header.php'; ?>

    <!-- Sidebar Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    
    <!-- Main Container -->
    <div class="dashboard-container">
        <div class="sidebar" id="sidebarMenu">
                <?php include '../includes/sidebar.php'; ?>
            </div>
            
            <!-- Main Content Area -->
        <div class="main-content-area">
            <div class="container-fluid">
                    <div class="crm-section-card">
                    <!-- Action Buttons Top Bar -->
                    <div class="d-flex justify-content-between align-items-center action-buttons-top-bar">
                        <div class="d-flex align-items-center">
                            <h2 class="page-title mb-0">Reminders</h2>
                        </div>

                        <!-- Action Buttons -->
                        <div class="action-buttons-group">
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addReminderModal">
                                <i class="fas fa-plus-circle"></i>
                                <span>Add</span>
                            </button>
                            <button class="btn btn-info" title="Sort">
                                <i class="fas fa-sort"></i>
                                <span>Sort</span>
                            </button>
                            <button class="btn btn-secondary" title="Calendar">
                                <i class="fas fa-calendar"></i>
                                <span>Calendar</span>
                            </button>
                            <button class="btn btn-warning" title="Analytics">
                                <i class="fas fa-chart-bar"></i>
                                <span>Analytics</span>
                            </button>
                            <button class="btn btn-danger" title="Delete">
                                <i class="fas fa-trash"></i>
                                <span>Delete</span>
                            </button>
                        </div>
                    </div>

                    <!-- Top Filter Section -->
                        <div class="card p-3 mb-4">
                        <div class="row g-3 filter-row">
                            <!-- Mobile Filter Toggle Button -->
                            <div class="col-12 d-md-none mb-2">
                                <button class="btn btn-outline-primary w-100" type="button" id="filterToggleBtn">
                                    <i class="fas fa-filter me-2"></i> Show/Hide Filters
                                </button>
                            </div>
                            
                            <div class="collapse d-md-flex w-100" id="filterCollapse">
                                <div class="row g-3 w-100">
                                    <div class="col-md-3 col-sm-6">
                                        <select class="form-select" id="createdBySelect">
                                        <option selected>Created By: All Reminders</option>
                                            <?php foreach ($usersList as $user): ?>
                                                <option value="<?php echo $user['id']; ?>"><?php echo $user['name']; ?></option>
                                            <?php endforeach; ?>
                                    </select>
                                </div>
                                    <div class="col-md-3 col-sm-6">
                                        <select class="form-select" id="assignToSelect">
                                            <option selected>Assign To: All Users</option>
                                            <?php foreach ($usersList as $user): ?>
                                                <option value="<?php echo $user['id']; ?>"><?php echo $user['name']; ?></option>
                                            <?php endforeach; ?>
                                    </select>
                                </div>
                                    <div class="col-md-3 col-sm-6">
                                        <select class="form-select" id="typeSelect">
                                        <option selected>Type: All</option>
                                            <option>Once</option>
                                            <option>Daily</option>
                                            <option>Weekly</option>
                                            <option>Monthly</option>
                                            <option>Quarterly</option>
                                            <option>Half-Yearly</option>
                                            <option>Yearly</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3 col-sm-6">
                                        <select class="form-select" id="statusSelect">
                                            <option selected>Status: All Status</option>
                                            <option>Active</option>
                                            <option>Completed</option>
                                            <option>Cancelled</option>
                                     </select>
                                 </div>
                                    <div class="col-md-12">
                                     <div class="input-group">
                                            <input type="text" class="form-control" placeholder="Search...">
                                            <button class="btn btn-outline-secondary" type="button">
                                                <i class="fas fa-search"></i>
                                            </button>
                                            <button class="btn btn-outline-secondary" type="button">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                </div>
                            </div>
                        </div>

                        <!-- Main Content Area -->
                        <div class="card p-4">
                        <div class="text-center text-muted p-5">
                            <i class="fas fa-bell fa-3x mb-3"></i>
                            <p class="h5">There are no records to display</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <?php include '../includes/dashboard-footer.php'; ?>
        </div>
    </div>

    <!-- Include Add Reminder Modal -->
    <?php include '../includes/modals/add-reminder.php'; ?>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
        // Filter toggle functionality for mobile
        const filterToggleBtn = document.getElementById('filterToggleBtn');
        const filterCollapse = document.getElementById('filterCollapse');
        
        if (filterToggleBtn && filterCollapse) {
            const bsCollapse = new bootstrap.Collapse(filterCollapse, {
                toggle: false
            });
            
            filterToggleBtn.addEventListener('click', function() {
                bsCollapse.toggle();
                
                // Update button text based on collapse state
                filterCollapse.addEventListener('shown.bs.collapse', function() {
                    filterToggleBtn.innerHTML = '<i class="fas fa-filter me-2"></i> Hide Filters';
                });
                
                filterCollapse.addEventListener('hidden.bs.collapse', function() {
                    filterToggleBtn.innerHTML = '<i class="fas fa-filter me-2"></i> Show Filters';
                });
            });
            
            // Handle collapse on window resize
            window.addEventListener('resize', function() {
                if (window.innerWidth >= 768) { // md breakpoint
                    filterCollapse.classList.add('d-md-flex');
                    bsCollapse.hide();
                } else {
                    filterCollapse.classList.remove('d-md-flex');
                }
            });
        }

        // Mobile sidebar toggle
        const sidebarOverlay = document.getElementById('sidebarOverlay');
        const sidebarMenu = document.getElementById('sidebarMenu');
        
        document.querySelector('.navbar-toggler').addEventListener('click', function() {
            sidebarMenu.classList.toggle('show');
            sidebarOverlay.classList.toggle('show');
        });

        sidebarOverlay.addEventListener('click', function() {
            sidebarMenu.classList.remove('show');
            sidebarOverlay.classList.remove('show');
        });
});
</script>
</body>
</html> 