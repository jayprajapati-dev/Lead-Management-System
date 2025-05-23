<?php
require_once '../includes/config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('index.php');
}

// Get user's email from session
$userEmail = $_SESSION['user_email'] ?? 'Guest'; // Provide a default email if not set

// Get lead statistics
$stats = []; // Initialize $stats as an empty array
$userStats = []; // Initialize $userStats as an empty array

try {
    // Fetch all lead statistics
    $statsResult = executeQuery("SELECT * FROM lead_statistics")->get_result();
    if ($statsResult) {
        $stats = $statsResult->fetch_all(MYSQLI_ASSOC);
    }

    // Fetch user-specific statistics
    $userStatsResult = executeQuery("SELECT * FROM user_performance WHERE user_id = ?", [$_SESSION['user_id']])->get_result();
    if ($userStatsResult) {
         $userStats = $userStatsResult->fetch_assoc();
    }

} catch (Exception $e) {
    $error = "Error loading statistics: " . $e->getMessage();
    // Log the actual error for debugging
    error_log("Dashboard statistics loading error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
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
            <div class="col-md-3 col-lg-2 sidebar" id="sidebarMenu">
<?php include '../includes/sidebar.php'; ?>
            </div>
            <div class="col-md-9 col-lg-10 main-content-area">
<?php include '../includes/dashboard-header.php'; ?>
                <div class="dashboard-body">
                    <div class="top-cards">
                        <div class="card">
                            <h4>Today's Leads</h4>
                            <p>New 0 Processing 0 Close-By 0</p>
                            <p>No Leads For Today</p>
                        </div>
                        <div class="card">
                            <h4>Today's Tasks</h4>
                            <p>Today 0 Tomorrow 0</p>
                            <p>No Task For Today</p>
                        </div>
                        <div class="card">
                            <h4>Today's Reminders</h4>
                            <p>Reminders 0 Events 0</p>
                            <p>No Reminders For Today</p>
                        </div>
                    </div>
                    <div class="sticky-notes-section">
                        <h4>Sticky Notes</h4>
                        <button>+ Add Notes</button>
                        <p>There are no records to display.</p>
                    </div>
                    <div class="bottom-cards">
                        <div class="card">
                            <h4>Lead Status</h4>
                            <p>FROM 01-05-2025 TO 22-05-2025</p>
                            <p>Kavan Patel</p>
                            <p>No Lead Found</p>
                            <p>0</p>
                            <p>New Processing Close-by Confirm Cancel</p>
                        </div>
                        <div class="card">
                            <h4>Lead Source</h4>
                            <p>FROM 01-05-2025 TO 22-05-2025</p>
                            <p>Kavan Patel</p>
                            <p>No Lead Found</p>
                            <p>0</p>
                            <p>Online Offline Website Whatsapp Customer Reminder Indiamart Facebook Google Form</p>
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
        document.addEventListener('DOMContentLoaded', function() {
            const sidebarToggle = document.querySelector('.btn.d-lg-none'); // Select the button by its classes
            const sidebar = document.querySelector('.sidebar'); // Select the sidebar by its class
            
            if (sidebarToggle && sidebar) {
                sidebarToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('show');
                });
            }
            
            // Optional: Close sidebar when clicking outside on mobile
            document.addEventListener('click', function(event) {
                if (window.innerWidth < 992) {
                    if (sidebar && !sidebar.contains(event.target) && !sidebarToggle.contains(event.target)) {
                        sidebar.classList.remove('show');
                    }
                }
            });
        });
    </script>
</body>
</html> 