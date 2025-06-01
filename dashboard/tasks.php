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

// Fetch users for the assign to dropdown
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
    }
} catch (Exception $e) {
    error_log("Error fetching users for dropdown: " . $e->getMessage());
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
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        .sidebar-overlay.show {
            display: block;
            opacity: 1;
        }
        .main-content-area {
            margin-left: 16.666667%; /* col-md-2 width */
            transition: all 0.3s;
            padding-top: 0; /* Remove any top padding */
            margin-top: 0; /* Remove any top margin */
        }
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

        .avatar {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 48px;
            height: 48px;
        }
        .bg-primary-subtle { background-color: rgba(13, 110, 253, 0.1); }
        .bg-info-subtle { background-color: rgba(13, 202, 240, 0.1); }
        .bg-warning-subtle { background-color: rgba(255, 193, 7, 0.1); }
        .bg-success-subtle { background-color: rgba(25, 135, 84, 0.1); }
        .bg-danger-subtle { background-color: rgba(220, 53, 69, 0.1); }
        .card {
            transition: transform 0.2s ease-in-out;
        }
        .card:hover {
            transform: translateY(-5px);
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
                            <button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#addTaskModal">
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
                            <button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#addTaskModal">
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

                    <style>
                        .status-box {
                            color: white;
                            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                            transition: transform 0.2s ease-in-out;
                        }
                        .status-box:hover {
                            transform: translateY(-3px);
                            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
                        }
                        .count-badge {
                            font-weight: 500;
                            min-width: 30px;
                            text-align: center;
                        }
                        @media (max-width: 768px) {
                            .status-box {
                                margin-bottom: 10px;
                            }
                        }
                    </style>

                
                    <!-- Main Content Area (Empty State) -->
                    <div id="tasksContentArea">
                        <div class="table-responsive">
                            <div class="d-flex flex-nowrap overflow-auto pb-3">
                                <!-- New Tasks Column -->
                                <div class="col-md-3 min-width-300 me-3">
                                    <div class="card h-100 border-0 shadow-sm">
                                        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center" style="background: linear-gradient(45deg, #0d6efd, #0dcaf0) !important;">
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-clipboard-list me-2"></i>
                                                <span>New</span>
                                            </div>
                                            <span class="badge bg-white text-primary">0</span>
                                        </div>
                                        <div class="card-body text-center py-5">
                                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                            <p class="text-muted mb-3">No new tasks yet</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Processing Tasks Column -->
                                <div class="col-md-3 min-width-300 me-3">
                                    <div class="card h-100 border-0 shadow-sm">
                                        <div class="card-header text-white d-flex justify-content-between align-items-center" style="background: linear-gradient(45deg, #6610f2, #6f42c1);">
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-cog me-2"></i>
                                                <span>Processing</span>
                                            </div>
                                            <span class="badge bg-white text-purple">0</span>
                                        </div>
                                        <div class="card-body text-center py-5">
                                            <i class="fas fa-cogs fa-3x text-muted mb-3"></i>
                                            <p class="text-muted">No tasks in processing</p>
                                        </div>
                            </div>
                        </div>

                                <!-- In Feedback Tasks Column -->
                                <div class="col-md-3 min-width-300 me-3">
                                    <div class="card h-100 border-0 shadow-sm">
                                        <div class="card-header text-white d-flex justify-content-between align-items-center" style="background: linear-gradient(45deg, #fd7e14, #ffc107);">
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-comments me-2"></i>
                                                <span>In Feedback</span>
                                            </div>
                                            <span class="badge bg-white text-warning">0</span>
                                        </div>
                                        <div class="card-body text-center py-5">
                                            <i class="fas fa-comment-dots fa-3x text-muted mb-3"></i>
                                            <p class="text-muted">No tasks in feedback</p>
                                        </div>
                            </div>
                        </div>

                                <!-- Completed Tasks Column -->
                                <div class="col-md-3 min-width-300 me-3">
                                    <div class="card h-100 border-0 shadow-sm">
                                        <div class="card-header text-white d-flex justify-content-between align-items-center" style="background: linear-gradient(45deg, #198754, #20c997);">
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-check-circle me-2"></i>
                                                <span>Completed</span>
                                            </div>
                                            <span class="badge bg-white text-success">0</span>
                                        </div>
                                        <div class="card-body text-center py-5">
                                            <i class="fas fa-check-double fa-3x text-muted mb-3"></i>
                                            <p class="text-muted">No completed tasks</p>
                                        </div>
                            </div>
                        </div>

                                <!-- Rejected Tasks Column -->
                                <div class="col-md-3 min-width-300">
                                    <div class="card h-100 border-0 shadow-sm">
                                        <div class="card-header text-white d-flex justify-content-between align-items-center" style="background: linear-gradient(45deg, #dc3545, #dc3545);">
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-times-circle me-2"></i>
                                                <span>Rejected</span>
                                            </div>
                                            <span class="badge bg-white text-danger">0</span>
                                        </div>
                                        <div class="card-body text-center py-5">
                                            <i class="fas fa-ban fa-3x text-muted mb-3"></i>
                                            <p class="text-muted">No rejected tasks</p>
                                        </div>
                            </div>
                        </div>
                            </div>
                        </div>
                    </div>

                    <style>
                        .min-width-300 {
                            min-width: 300px;
                        }
                        .table-responsive {
                            overflow-x: auto;
                            -webkit-overflow-scrolling: touch;
                        }
                        .card {
                            transition: transform 0.2s ease-in-out;
                        }
                        .card:hover {
                            transform: translateY(-5px);
                        }
                        .card-header {
                            border-bottom: none;
                            border-radius: 10px 10px 0 0 !important;
                        }
                        .text-purple {
                            color: #6f42c1;
                        }
                        @media (max-width: 768px) {
                            .min-width-300 {
                                min-width: 260px;
                            }
                        }

                        /* Custom scrollbar styles */
                        .overflow-auto::-webkit-scrollbar {
                            height: 8px;
                        }
                        .overflow-auto::-webkit-scrollbar-track {
                            background: #f1f1f1;
                            border-radius: 4px;
                        }
                        .overflow-auto::-webkit-scrollbar-thumb {
                            background: #888;
                            border-radius: 4px;
                        }
                        .overflow-auto::-webkit-scrollbar-thumb:hover {
                            background: #666;
                        }
                    </style>

                </div>
            </div>

            <!-- Footer -->
            <?php include '../includes/dashboard-footer.php'; ?>
        </div>
    </div>
</div>

<!-- Include Add Task Modal -->
<?php include '../includes/modals/add-task.php'; ?>

<!-- Bootstrap 5 JS and dependencies -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>

<!-- Custom JavaScript for Tasks Page -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Bootstrap Modal
    const addTaskModal = new bootstrap.Modal(document.getElementById('addTaskModal'));
    
    // View Toggle Functionality
    const kanbanViewBtn = document.getElementById('kanbanViewBtn');
    const listViewBtn = document.getElementById('listViewBtn');
    const kanbanViewButtons = document.getElementById('kanbanViewButtons');
    const listViewButtons = document.getElementById('listViewButtons');
    const statusFilterRow = document.getElementById('statusFilterRow');

    kanbanViewBtn.addEventListener('click', function() {
        this.classList.add('active');
        listViewBtn.classList.remove('active');
        kanbanViewButtons.classList.remove('d-none');
        listViewButtons.classList.add('d-none');
        statusFilterRow.classList.remove('d-none');
    });

    listViewBtn.addEventListener('click', function() {
        this.classList.add('active');
        kanbanViewBtn.classList.remove('active');
        listViewButtons.classList.remove('d-none');
        kanbanViewButtons.classList.add('d-none');
        statusFilterRow.classList.add('d-none');
    });

    // Handle Add Task button clicks (both in header and content area)
    document.querySelectorAll('[data-bs-target="#addTaskModal"]').forEach(button => {
        button.addEventListener('click', function() {
            addTaskModal.show();
        });
    });

    // Handle form submission
    const addTaskForm = document.getElementById('addTaskForm');
    if (addTaskForm) {
        addTaskForm.addEventListener('submit', function(e) {
            e.preventDefault();
            // Add your form submission logic here
            // After successful submission:
            addTaskModal.hide();
        });
    }

    // Populate Assign To dropdown with users
    const assignToSelect = document.getElementById('taskAssignTo');
    if (assignToSelect) {
        const users = <?php echo json_encode($usersList); ?>;
        users.forEach(user => {
            const option = document.createElement('option');
            option.value = user.id;
            option.textContent = user.name;
            assignToSelect.appendChild(option);
        });
    }
});
</script>

<!-- Sidebar toggle functionality is now handled in dashboard-header.php -->
</body>
</html> 