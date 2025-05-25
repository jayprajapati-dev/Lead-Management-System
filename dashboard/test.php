<?php
require_once '../includes/config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('index.php');
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
    <title>Application</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Link to your custom CSS file -->
    <link rel="stylesheet" href="css/dashboard_style.css">
</head>
<body>
    <div class="dashboard-container container-fluid">
        <div class="row">
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
                
                <!-- Main Content Body -->
                <div class="dashboard-body">
                    <!-- Page content goes here -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h4>Page Title</h4>
                                </div>
                                <div class="card-body">
                                    <!-- Your page-specific content will go here -->
                                    <p>This is the main content area. Replace this with your specific page content.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Mobile sidebar toggle functionality
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebarMenu = document.getElementById('sidebarMenu');
            const sidebarOverlay = document.getElementById('sidebarOverlay');

            if (sidebarToggle && sidebarMenu && sidebarOverlay) {
                // Toggle sidebar on button click
                sidebarToggle.addEventListener('click', function() {
                    sidebarMenu.classList.toggle('show');
                    sidebarOverlay.classList.toggle('show');
                    document.body.classList.toggle('sidebar-open');
                });

                // Close sidebar when overlay is clicked
                sidebarOverlay.addEventListener('click', function() {
                    sidebarMenu.classList.remove('show');
                    sidebarOverlay.classList.remove('show');
                    document.body.classList.remove('sidebar-open');
                });

                // Close sidebar on window resize if screen becomes larger
                window.addEventListener('resize', function() {
                    if (window.innerWidth >= 768) { // md breakpoint
                        sidebarMenu.classList.remove('show');
                        sidebarOverlay.classList.remove('show');
                        document.body.classList.remove('sidebar-open');
                    }
                });
            }

            // Embed PHP variable containing users list into JavaScript
            const usersForDropdown = <?php echo json_encode($usersList); ?>;

            // Function to populate user dropdowns (if needed on the page)
            function populateUserDropdowns(users) {
                const userSelects = document.querySelectorAll('select[data-populate="users"]');
                
                userSelects.forEach(select => {
                    if (users && users.length > 0) {
                        // Clear existing options except the first one (usually "Select User")
                        const firstOption = select.querySelector('option');
                        select.innerHTML = '';
                        if (firstOption) {
                            select.appendChild(firstOption);
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

            // Add any common JavaScript functionality here
            // For example: form validation, common modal handling, etc.
        });
    </script>

    <!-- Footer -->
    <?php include '../includes/dashboard-footer.php'; ?>

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

</body>
</html>