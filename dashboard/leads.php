<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include configuration and required files
require_once '../includes/config.php';
require_once '../includes/trial_functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . SITE_URL . '/login.php');
    exit();
}

// Check trial restrictions before any output
enforceTrialRestrictions($_SESSION['user_id']);

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

// Only start HTML output after all checks and redirects
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leads | Lead Management System</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Google Fonts (Poppins) -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Country Flags CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/flag-icon-css/3.5.0/css/flag-icon.min.css">
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.18/dist/sweetalert2.min.css">
    <!-- Link to dashboard CSS files -->
    <link rel="stylesheet" href="css/dashboard_style_new.css">
    <style>
        /* Base styles */
        body {
            margin: 0;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #f8fafc;
            overflow-x: hidden;
            font-size: 14px;
            line-height: 1.5;
            color: #333;
            min-height: 100vh;
            position: relative;
        }
        
        .dashboard-container {
            width: 100%;
            max-width: 1920px;
            margin: 0 auto;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--secondary-color);
            min-height: 100vh;
            margin: 0;
            padding: 0;
            color: var(--text-primary);
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
            display: flex;
            justify-content: space-between;
            transition: all 0.3s ease;
        }
        
        .highlight-status {
            box-shadow: 0 0 15px rgba(255, 215, 0, 0.8);
            transform: scale(1.02);
            border: 2px solid gold;
            align-items: center;
        }
        .status-box .count-badge {
            position: absolute;
            right: 15px;
            background-color: rgba(255, 255, 255, 0.3);
            color: white;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        
        .badge-highlight {
            transform: scale(1.3);
            box-shadow: 0 0 10px rgba(255, 255, 255, 0.8);
            background-color: rgba(255, 255, 255, 0.6) !important;
        }
        /* Custom colors for status boxes */
        .status-new { background-color: #0d6efd; } /* Blue */
        .status-processing { background-color: #6f42c1; } /* Dark Purple */
        .status-close-by { background-color: #ffc107; } /* Yellow */
        .status-confirm { background-color: #198754; } /* Green */
        .status-cancel { background-color: #dc3545; } /* Red */

        .floating-button {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            box-shadow: var(--shadow-md);
            background-color: var(--primary-color);
            color: var(--text-active);
            opacity: 0;
            transform: translateY(20px);
            transition: opacity var(--transition-normal), transform var(--transition-normal);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        /* Adjust floating button for mobile */
        @media (max-width: 767.98px) {
            .floating-button {
                bottom: 15px;
                right: 15px;
                width: 45px;
                height: 45px;
                font-size: 14px;
            }
        }
        
        .floating-button.show {
            opacity: 1;
            transform: translateY(0);
        }
        
        .floating-button:hover {
            background-color: var(--primary-dark);
            transform: translateY(-3px);
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

        .action-buttons-top-bar .action-buttons-group .btn i {
            font-size: 14px;
        }

        /* Ensure proper spacing between icon and text */
        .action-buttons-top-bar .action-buttons-group .btn span {
            display: inline-block;
            margin-left: 4px;
        }

        @media (max-width: 768px) {
            .action-buttons-top-bar {
                flex-direction: column;
                gap: 1rem;
            }
            
            .action-buttons-top-bar .action-buttons-group {
                width: 100%;
                flex-wrap: wrap;
            }
            
            .action-buttons-top-bar .action-buttons-group .btn {
                padding: 8px;
            }
            
            .action-buttons-top-bar .action-buttons-group .btn span {
                display: none;
            }
        }

        /* Dashboard Layout Styles */
        .dashboard-container {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        /* Main content wrapper */
        .main-content-wrapper {
            margin-top: 60px;
            margin-left: 250px;
            padding: 1.5rem;
            min-height: calc(100vh - 60px);
            width: calc(100% - 250px);
            box-sizing: border-box;
            transition: all 0.3s ease;
            background-color: var(--secondary-color);
            position: relative;
            overflow-x: hidden;
        }
        
        /* Custom styles for leads page */
        /* All sidebar styling is now in dashboard_style_new.css */
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(2px);
            z-index: 999;
            opacity: 0;
            transition: opacity var(--transition-fast);
        }
        
        /* Responsive sidebar behavior */
        @media (max-width: 767.98px) {
            .sidebar {
                transform: translateX(-100%);
                width: 280px; /* Slightly wider on mobile for better touch targets */
                z-index: 1050; /* Higher z-index to ensure it appears above other content */
            }
            .sidebar.show {
                transform: translateX(0);
                box-shadow: 5px 0 15px rgba(0, 0, 0, 0.2);
            }
            .main-content-wrapper {
                margin-left: 0;
                width: 100%;
                padding: 1rem; /* Smaller padding on mobile */
            }
            .header {
                left: 0;
                width: 100%;
            }
            .sidebar-overlay.show {
                opacity: 1;
                pointer-events: all;
            }
            
            /* Improve card padding on mobile */
            .card {
                padding: 15px !important;
            }
            
            .crm-section-card {
                padding: 15px !important;
            }
            
            /* Improve touch targets */
            .btn {
                min-height: 38px;
                display: flex;
                align-items: center;
                justify-content: center;
            }
        }

        /* Styles for active sidebar link */
        /* Custom styles specific to leads page */
        
        /* Status box styling */
        .status-box {
            border-radius: 10px;
            color: white;
            padding: 15px;
            text-align: center;
            margin-bottom: 15px;
            position: relative;
            overflow: hidden;
            box-shadow: var(--shadow-sm);
            transition: transform var(--transition-fast), box-shadow var(--transition-fast);
        }
        
        .status-box:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-md);
        }

        /* Kanban Board Layout */
        .kanban-wrapper {
            position: relative;
            margin: 0 -10px;
        }

        .kanban-container {
            display: flex;
            overflow-x: auto;
            padding: 20px 0;
            min-height: calc(100vh - 300px);
            scroll-behavior: smooth;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: thin;
            scrollbar-color: #6c757d #f8f9fa;
        }

        /* Scrollbar styling */
        .kanban-container::-webkit-scrollbar {
            height: 8px;
        }

        .kanban-container::-webkit-scrollbar-track {
            background: #f8f9fa;
            border-radius: 4px;
        }

        .kanban-container::-webkit-scrollbar-thumb {
            background: #6c757d;
            border-radius: 4px;
        }

        .kanban-container::-webkit-scrollbar-thumb:hover {
            background: #495057;
        }

        /* Column Styles */
        .kanban-column {
            flex: 0 0 300px;
            margin: 0 10px;
            background: #ffffff;
            border-radius: 12px;
            padding: 15px;
            display: flex;
            flex-direction: column;
            max-height: calc(100vh - 250px);
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            border: 1px solid #e9ecef;
        }

        .kanban-column-header {
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 15px;
            text-align: center;
            font-weight: 600;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        /* Lead Status Column Specific Styles */
        .column-new .kanban-column-header {
            background: linear-gradient(135deg, #0d6efd, #0dcaf0);
        }

        .column-processing .kanban-column-header {
            background: linear-gradient(135deg, #6610f2, #6f42c1);
        }

        .column-closeby .kanban-column-header {
            background: linear-gradient(135deg, #fd7e14, #ffc107);
        }

        .column-confirm .kanban-column-header {
            background: linear-gradient(135deg, #198754, #20c997);
        }

        .column-cancel .kanban-column-header {
            background: linear-gradient(135deg, #dc3545, #dc3545);
        }

        .kanban-column-content {
            flex: 1;
            overflow-y: auto;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 8px;
            min-height: 200px;
        }

        /* Lead Count Badge */
        .lead-count {
            background: rgba(255, 255, 255, 0.2);
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 500;
            min-width: 28px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        /* Column Header Icons */
        .kanban-column-header i {
            margin-right: 8px;
            font-size: 1rem;
        }

        /* Lead Card Styles */
        .lead-card {
            background: white;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            border-left: 4px solid;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .lead-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        /* Lead Card Status Colors */
        .column-new .lead-card { border-left-color: #0d6efd; }
        .column-processing .lead-card { border-left-color: #6610f2; }
        .column-closeby .lead-card { border-left-color: #fd7e14; }
        .column-confirm .lead-card { border-left-color: #198754; }
        .column-cancel .lead-card { border-left-color: #dc3545; }

        @media (min-width: 1200px) {
            .kanban-column {
                flex: 0 0 320px;
            }
        }

        /* Add Lead Modal Styles */
        .phone-input-group {
            display: flex;
            align-items: center;
        }
        
        .country-flag-dropdown {
            min-width: 100px;
        }
        
        .country-flag-dropdown .flag-icon {
            margin-right: 5px;
        }
        
        .required-field::after {
            content: "*";
            color: red;
            margin-left: 4px;
        }
    </style>
</head>
<body>
    <!-- Mobile Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    
    <!-- Header first, outside the main container -->
    <?php include '../includes/dashboard-header.php'; ?>
    
    <!-- Mobile Toggle Button is handled in dashboard-header.php -->
    
    <!-- Sidebar -->
    <div class="sidebar" id="sidebarMenu">
        <?php include '../includes/sidebar.php'; ?>
    </div>

    <!-- Main Content -->
    <div class="main-content-wrapper">
                
        <div class="dashboard-body">
            <div class="container-fluid p-0">
                <div class="container-fluid py-4">
                    <div class="crm-section-card pt-3">
                        <!-- Action Buttons Top Bar -->
                        <div class="d-flex justify-content-between align-items-center action-buttons-top-bar">
                            <div class="d-flex align-items-center view-toggle-group">
                                <button type="button" class="btn btn-outline-secondary active" id="gridViewBtn" title="Board View">
                                    <i class="fas fa-th-large"></i>
                                </button>
                                <button type="button" class="btn btn-outline-secondary" id="listViewBtn" title="List View">
                                    <i class="fas fa-list"></i>
                                </button>
                                <h2 class="page-title ms-3 mb-0">Leads Overview</h2>
                            </div>
                            
                            <!-- Board View Buttons -->
                            <div class="action-buttons-group" id="boardViewButtons">
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addLeadModal">
                                    <i class="fas fa-plus-circle"></i>
                                    <span>Add</span>
                                </button>
                                <button class="btn btn-secondary" title="Settings">
                                    <i class="fas fa-cog"></i>
                                    <span>Settings</span>
                                </button>
                                <button class="btn btn-info" title="Sort">
                                    <i class="fas fa-sort"></i>
                                    <span>Sort</span>
                                </button>
                            </div>

                            <!-- List View Buttons -->
                            <div class="action-buttons-group d-none" id="listViewButtons">
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addLeadModal">
                                    <i class="fas fa-plus-circle"></i>
                                    <span>Add</span>
                                </button>
                                <button class="btn btn-info" title="Sort">
                                    <i class="fas fa-sort"></i>
                                    <span>Sort</span>
                                </button>
                                <button class="btn btn-secondary" title="Download">
                                    <i class="fas fa-download"></i>
                                    <span>Download</span>
                                </button>
                                <button class="btn btn-warning" title="Graph">
                                    <i class="fas fa-chart-bar"></i>
                                    <span>Graph</span>
                                </button>
                                <button class="btn btn-dark" title="Tag">
                                    <i class="fas fa-tags"></i>
                                    <span>Tag</span>
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
                                <!-- Mobile Filter Toggle Button (visible only on mobile) -->
                                <div class="col-12 d-md-none mb-2">
                                    <button class="btn btn-outline-primary w-100" type="button" id="filterToggleBtn">
                                        <i class="fas fa-filter me-2"></i> Show/Hide Filters
                                    </button>
                                </div>
                                
                                <div class="collapse d-md-flex w-100" id="filterCollapse">
                                    <div class="row g-3 w-100">
                                        <!-- First row of filters -->
                                        <div class="col-md-3 col-sm-6">
                                            <label for="createdBySelect" class="form-label visually-hidden">Created By</label>
                                            <select class="form-select" id="createdBySelect">
                                                <option selected>Created By: All Lead</option>
                                                <?php foreach ($usersList as $user): ?>
                                                    <option value="<?php echo $user['id']; ?>"><?php echo $user['name']; ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-3 col-sm-6">
                                             <label for="assignToSelect" class="form-label visually-hidden">Assign To</label>
                                            <select class="form-select" id="assignToSelect">
                                                <option selected>Assign To: All Assign</option>
                                                 <?php foreach ($usersList as $user): ?>
                                                    <option value="<?php echo $user['id']; ?>"><?php echo $user['name']; ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-3 col-sm-6">
                                             <label for="labelsSelect" class="form-label visually-hidden">Labels</label>
                                             <select class="form-select" id="labelsSelect">
                                                <option selected>Labels: All Labels</option>
                                                 <!-- Options for Labels will go here -->
                                            </select>
                                        </div>
                                        <div class="col-md-3 col-sm-6">
                                             <label for="sourceSelect" class="form-label visually-hidden">Source</label>
                                             <select class="form-select" id="sourceSelect">
                                                <option selected>Source: All Source</option>
                                                 <!-- Options for Source will go here -->
                                            </select>
                                        </div>

                                        <!-- Second row of filters -->
                                        <div class="col-md-3 col-sm-6">
                                             <label for="statusSelect" class="form-label visually-hidden">Status</label>
                                             <select class="form-select" id="statusSelect">
                                                <option selected>Status: All Status</option>
                                                 <!-- Options for Status will go here -->
                                            </select>
                                        </div>
                                        <div class="col-md-3 col-sm-6">
                                             <label for="searchDateInput" class="form-label visually-hidden">Search By Date</label>
                                            <div class="input-group">
                                                 <input type="text" class="form-control" id="searchDateInput" placeholder="Search By Date">
                                                 <button class="btn btn-outline-secondary" type="button"><i class="fas fa-calendar"></i></button>
                                            </div>
                                        </div>
                                         <div class="col-md-6 col-sm-12">
                                              <label for="searchInput" class="form-label visually-hidden">Search</label>
                                             <div class="input-group">
                                                <input type="text" class="form-control" id="searchInput" placeholder="Search...">
                                                <button class="btn btn-outline-secondary" type="button"><i class="fas fa-search"></i></button>
                                                <button class="btn btn-outline-secondary" type="button"><i class="fas fa-times"></i></button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>                            <!-- Kanban Board -->
                            <div class="kanban-wrapper">
                                <div class="kanban-container" id="kanbanContainer">
                                    <!-- New Column -->
                                    <div class="kanban-column column-new">
                                        <div class="kanban-column-header">
                                            <div>
                                                <i class="fas fa-clipboard-list"></i>
                                                <span>New</span>
                                            </div>
                                            <span class="lead-count">0</span>
                                        </div>
                                        <div class="kanban-column-content" id="new-leads">
                                            <div class="text-center text-muted p-3">
                                                <i class="fas fa-inbox fa-2x mb-2"></i>
                                                <p>No new leads yet</p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Processing Column -->
                                    <div class="kanban-column column-processing">
                                        <div class="kanban-column-header">
                                            <div>
                                                <i class="fas fa-cog"></i>
                                                <span>Processing</span>
                                            </div>
                                            <span class="lead-count">0</span>
                                        </div>
                                        <div class="kanban-column-content" id="processing-leads">
                                            <div class="text-center text-muted p-3">
                                                <i class="fas fa-cogs fa-2x mb-2"></i>
                                                <p>No leads in processing</p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Close-by Column -->
                                    <div class="kanban-column column-closeby">
                                        <div class="kanban-column-header">
                                            <div>
                                                <i class="fas fa-clock"></i>
                                                <span>Close-by</span>
                                            </div>
                                            <span class="lead-count">0</span>
                                        </div>
                                        <div class="kanban-column-content" id="closeby-leads">
                                            <div class="text-center text-muted p-3">
                                                <i class="fas fa-hourglass-half fa-2x mb-2"></i>
                                                <p>No close-by leads</p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Confirm Column -->
                                    <div class="kanban-column column-confirm">
                                        <div class="kanban-column-header">
                                            <div>
                                                <i class="fas fa-check-circle"></i>
                                                <span>Confirm</span>
                                            </div>
                                            <span class="lead-count">0</span>
                                        </div>
                                        <div class="kanban-column-content" id="confirm-leads">
                                            <div class="text-center text-muted p-3">
                                                <i class="fas fa-check-double fa-2x mb-2"></i>
                                                <p>No confirmed leads</p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Cancel Column -->
                                    <div class="kanban-column column-cancel">
                                        <div class="kanban-column-header">
                                            <div>
                                                <i class="fas fa-times-circle"></i>
                                                <span>Cancel</span>
                                            </div>
                                            <span class="lead-count">0</span>
                                        </div>
                                        <div class="kanban-column-content" id="cancel-leads">
                                            <div class="text-center text-muted p-3">
                                                <i class="fas fa-ban fa-2x mb-2"></i>
                                                <p>No cancelled leads</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Sidebar toggle and floating action button functionality -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Sidebar toggle functionality
            const sidebarMenu = document.getElementById('sidebarMenu');
            const sidebarOverlay = document.getElementById('sidebarOverlay');
            const sidebarToggle = document.querySelector('.sidebar-toggle');
            
            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', function() {
                    sidebarMenu.classList.toggle('show');
                    sidebarOverlay.classList.toggle('show');
                });
            }
            
            if (sidebarOverlay) {
                sidebarOverlay.addEventListener('click', function() {
                    sidebarMenu.classList.remove('show');
                    sidebarOverlay.classList.remove('show');
                });
            }
            
            // Floating action button for scrolling to top
            const scrollToTopBtn = document.getElementById('scrollToTopBtn');
            
            if (scrollToTopBtn) {
                // Show button when user scrolls down 300px from the top
                window.addEventListener('scroll', function() {
                    if (document.body.scrollTop > 300 || document.documentElement.scrollTop > 300) {
                        scrollToTopBtn.classList.add('show');
                    } else {
                        scrollToTopBtn.classList.remove('show');
                    }
                });
                
                // Scroll to top when button is clicked
                scrollToTopBtn.addEventListener('click', function() {
                    window.scrollTo({
                        top: 0,
                        behavior: 'smooth'
                    });
                });
            }
        });
    </script>

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
                    
                    // Show board view buttons and hide list view buttons
                    boardViewButtons.classList.remove('d-none');
                    listViewButtons.classList.add('d-none');
                    
                    // Show kanban board and hide list view message
                    document.getElementById('kanbanContainer').classList.remove('d-none');
                    if (document.getElementById('listViewMessage')) {
                        document.getElementById('listViewMessage').classList.add('d-none');
                    }

                } else { // list view
                    gridViewBtn.classList.remove('active');
                    listViewBtn.classList.add('active');
                    
                    // Show list view buttons and hide board view buttons
                    listViewButtons.classList.remove('d-none');
                    boardViewButtons.classList.add('d-none');
                    
                    // Hide kanban board and show list view message
                    document.getElementById('kanbanContainer').classList.add('d-none');
                    
                    // Create or show the list view message
                    let listViewMessage = document.getElementById('listViewMessage');
                    if (!listViewMessage) {
                        listViewMessage = document.createElement('div');
                        listViewMessage.id = 'listViewMessage';
                        listViewMessage.className = 'text-center text-muted p-5';
                        listViewMessage.innerHTML = '<i class="fas fa-inbox fa-3x mb-3"></i><p class="h5">There are no records to display</p>';
                        document.querySelector('.crm-section-card').appendChild(listViewMessage);
                    } else {
                        listViewMessage.classList.remove('d-none');
                    }
                }
            }

            // Initial setup: Set Board View as default active and update view display
            if (gridViewBtn && listViewBtn) {
                updateView('grid'); // Set initial view to Board view
                
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

    <!-- Include Add Lead Modal -->
    <?php include '../includes/modals/add-lead.php'; ?>
    
    <!-- Floating Action Button for scrolling to top -->
    <button id="scrollToTopBtn" class="btn btn-primary floating-button d-flex align-items-center justify-content-center">
        <i class="fas fa-arrow-up"></i>
    </button>

    <!-- Custom JavaScript -->
    <script src="js/leads.js"></script>
    <script src="js/lead-display.js"></script>

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
    });
    </script>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.18/dist/sweetalert2.all.min.js"></script>
    <!-- Add Lead JS -->
    <script src="js/add-lead.js"></script>

<!-- Sidebar toggle functionality is now handled in dashboard-header.php -->
</body>
</html>