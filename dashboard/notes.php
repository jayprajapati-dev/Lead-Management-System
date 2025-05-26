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
    <title>Notes - Lead Management System</title>
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
        /* Additional styles for notes page */
        .notes-container {
            min-height: calc(100vh - 60px); /* Adjust based on your header height */
            padding: 20px;
            background-color: #f8f9fa;
        }
        .notes-header {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            margin-bottom: 20px;
        }
        .notes-content {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .btn-add-note {
            padding: 8px 16px;
            font-weight: 500;
        }
        .search-box {
            min-width: 300px;
        }
        @media (max-width: 768px) {
            .search-box {
                min-width: 200px;
            }
            .notes-header {
                flex-direction: column;
                gap: 15px;
            }
            .notes-header .d-flex {
                width: 100%;
            }
            .search-box {
                width: 100% !important;
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
            <div class="notes-container">
                <!-- Notes Header Section -->
                <div class="notes-header d-flex justify-content-between align-items-center">
                    <h2 class="h3 mb-0">Notes</h2>
                    <div class="d-flex gap-3">
                        <!-- Search Box -->
                        <div class="input-group search-box">
                            <input type="text" class="form-control" placeholder="Search notes..." aria-label="Search notes">
                            <button class="btn btn-outline-secondary" type="button">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                        <!-- Add Note Button -->
                        <button class="btn btn-primary btn-add-note" type="button" data-bs-toggle="modal" data-bs-target="#addNoteModal">
                            <i class="fas fa-plus me-1"></i> Add Note
                        </button>
                    </div>
                </div>

                <!-- Notes Content Card -->
                <div class="notes-content">
                    <div class="card-body">
                        <div class="text-center py-5">
                            <i class="fas fa-clipboard-list text-muted mb-3" style="font-size: 3rem;"></i>
                            <p class="text-muted mb-0">No records to display</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <?php include '../includes/dashboard-footer.php'; ?>
        </div>
    </div>
</div>

<!-- Include Add Note Modal -->
<?php include '../includes/modals/add-note.php'; ?>

<!-- Bootstrap 5 JS and dependencies -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>

<!-- Custom JavaScript for Notes Page -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Bootstrap Modal
    const addNoteModal = new bootstrap.Modal(document.getElementById('addNoteModal'));
    
    // Add Note button click handler
    document.querySelector('.btn-add-note').addEventListener('click', function() {
        addNoteModal.show();
    });

    // Handle modal form submission
    const addNoteForm = document.getElementById('addNoteForm');
    if (addNoteForm) {
        addNoteForm.addEventListener('submit', function(e) {
            e.preventDefault();
            // Add your form submission logic here
            // After successful submission:
            addNoteModal.hide();
        });
    }
});
</script>

</body>
</html>