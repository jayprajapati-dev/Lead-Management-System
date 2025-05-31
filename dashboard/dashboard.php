<?php
require_once '../includes/config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('index.php');
}

// Fetch all active users for the assigned_to dropdown in the Lead modal and Analytics filter
$usersList = [];
try {
    // Assuming 'active' is a status for usable users, adjust if needed
    $usersResult = executeQuery("SELECT id, first_name, last_name FROM users WHERE status = 'active' ORDER BY first_name")->get_result();
    if ($usersResult) {
        while ($user = $usersResult->fetch_assoc()) {
            $usersList[] = [
                'id' => $user['id'],
                'name' => htmlspecialchars($user['first_name'] . ' ' . $user['last_name'])
            ];
        }
        $usersResult->free(); // Free result set
    }
} catch (Exception $e) {
    error_log("Error fetching users for dropdown: " . $e->getMessage());
    // Handle error appropriately, maybe pass an empty array or an error flag to JS
    $usersList = [];
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="Lead Management System Dashboard">
    <title>Dashboard | Lead Management System</title>
    <!-- Favicon -->
    <link rel="shortcut icon" href="../assets/favicon.ico" type="image/x-icon">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Link to your custom CSS files -->
    <link rel="stylesheet" href="css/dashboard_style_new.css">
    <link rel="stylesheet" href="css/sticky_notes.css">
    <link rel="stylesheet" href="css/dashboard_cards.css">
</head>
<body>
    <!-- Mobile Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    
    <!-- Header first, outside the main container -->
    <?php include '../includes/dashboard-header.php'; ?>
    
    <!-- Sidebar -->
    <div class="sidebar" id="sidebarMenu">
        <?php include '../includes/sidebar.php'; ?>
    </div>

    <!-- Main Content -->
    <div class="main-content-wrapper">
        <div class="dashboard-body">
            <div class="container-fluid p-0">
                <div class="row dashboard-cards-row">
                        <!-- Today's Leads Card -->
                        <div class="col-md-4 mb-4">
                            <div class="card dashboard-card">
                                <div class="card-header">
                                    <h4><i class="fas fa-user-plus me-2"></i>Today's Leads</h4>
                                </div>
                                <div class="card-body">
                                    <ul class="nav nav-tabs card-tabs" role="tablist">
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link active" id="new-tab" data-bs-toggle="tab" data-bs-target="#new" type="button" role="tab" aria-controls="new" aria-selected="true">New <span class="badge badge-count">0</span></button>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link" id="processing-tab" data-bs-toggle="tab" data-bs-target="#processing" type="button" role="tab" aria-controls="processing" aria-selected="false">Processing <span class="badge badge-count">0</span></button>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link" id="close-by-tab" data-bs-toggle="tab" data-bs-target="#close-by" type="button" role="tab" aria-controls="close-by" aria-selected="false">Close-By <span class="badge badge-count">0</span></button>
                                        </li>
                                    </ul>
                                    <div class="tab-content">
                                        <div class="tab-pane fade show active" id="new" role="tabpanel" aria-labelledby="new-tab">
                                            <div class="empty-state">
                                                <i class="fas fa-clipboard-list"></i>
                                                <h5>No New Leads</h5>
                                                <p>There are no new leads assigned to you today</p>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="processing" role="tabpanel" aria-labelledby="processing-tab">
                                            <div class="empty-state">
                                                <i class="fas fa-spinner"></i>
                                                <h5>No Processing Leads</h5>
                                                <p>There are no leads in processing status today</p>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="close-by" role="tabpanel" aria-labelledby="close-by-tab">
                                            <div class="empty-state">
                                                <i class="fas fa-check-circle"></i>
                                                <h5>No Close-By Leads</h5>
                                                <p>There are no leads close to completion today</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Today's Tasks Card -->
                        <div class="col-md-4 mb-4">
                            <div class="card dashboard-card">
                                <div class="card-header">
                                    <h4><i class="fas fa-tasks me-2"></i>Today's Tasks</h4>
                                </div>
                                <div class="card-body">
                                    <ul class="nav nav-tabs card-tabs" role="tablist">
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link active" id="today-tab" data-bs-toggle="tab" data-bs-target="#today" type="button" role="tab" aria-controls="today" aria-selected="true">Today <span class="badge badge-count">0</span></button>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link" id="tomorrow-tab" data-bs-toggle="tab" data-bs-target="#tomorrow" type="button" role="tab" aria-controls="tomorrow" aria-selected="false">Tomorrow <span class="badge badge-count">0</span></button>
                                        </li>
                                    </ul>
                                    <div class="tab-content">
                                        <div class="tab-pane fade show active" id="today" role="tabpanel" aria-labelledby="today-tab">
                                            <div class="empty-state">
                                                <i class="fas fa-calendar-day"></i>
                                                <h5>No Tasks Today</h5>
                                                <p>You have no tasks scheduled for today</p>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="tomorrow" role="tabpanel" aria-labelledby="tomorrow-tab">
                                            <div class="empty-state">
                                                <i class="fas fa-calendar-plus"></i>
                                                <h5>No Tasks Tomorrow</h5>
                                                <p>You have no tasks scheduled for tomorrow</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Today's Reminders Card -->
                        <div class="col-md-4 mb-4">
                            <div class="card dashboard-card">
                                <div class="card-header">
                                    <h4><i class="fas fa-bell me-2"></i>Today's Reminders</h4>
                                </div>
                                <div class="card-body">
                                    <ul class="nav nav-tabs card-tabs" role="tablist">
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link active" id="reminders-tab" data-bs-toggle="tab" data-bs-target="#reminders" type="button" role="tab" aria-controls="reminders" aria-selected="true">Reminders <span class="badge badge-count">0</span></button>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link" id="events-tab" data-bs-toggle="tab" data-bs-target="#events" type="button" role="tab" aria-controls="events" aria-selected="false">Events <span class="badge badge-count">0</span></button>
                                        </li>
                                    </ul>
                                    <div class="tab-content">
                                        <div class="tab-pane fade show active" id="reminders" role="tabpanel" aria-labelledby="reminders-tab">
                                            <div class="empty-state">
                                                <i class="fas fa-clock"></i>
                                                <h5>No Reminders Today</h5>
                                                <p>You have no reminders scheduled for today</p>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="events" role="tabpanel" aria-labelledby="events-tab">
                                            <div class="empty-state">
                                                <i class="fas fa-calendar-alt"></i>
                                                <h5>No Events Today</h5>
                                                <p>You have no events scheduled for today</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        </div>
                    </div>
                        
                        <!-- Sticky Notes Section -->
                        <div class="row mt-5">
                        <div class="col-12">
                            <div class="sticky-notes-section card dashboard-card" style="min-height: 300px;">
                                <div class="card-header">
                                    <h4><i class="fas fa-sticky-note"></i> Sticky Notes</h4>
                                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addNoteModal">
                                        <i class="fas fa-plus me-1"></i> Add Note
                                    </button>
                                </div>
                                <div class="card-body">
                            <!-- Container for displaying sticky notes -->
                            <div id="stickyNotesContainer" class="row g-3 mt-2">
                                            <?php
                                            // Fetch existing notes for the logged-in user
                                            if (isLoggedIn()) {
                                                $userId = $_SESSION['user_id'];
                                                try {
                                                    $notesStmt = executeQuery("SELECT id, content, created_at FROM sticky_notes WHERE user_id = ? ORDER BY created_at DESC", [$userId]);
                                                    $notesResult = $notesStmt->get_result();
                                                    if ($notesResult->num_rows > 0) {
                                                        while ($note = $notesResult->fetch_assoc()) {
                                                            // Format the note creation date
                                                            $createdDate = new DateTime($note['created_at']);
                                                            $formattedDate = $createdDate->format('M d, Y');
                                                            
                                                            // Output HTML for each note with professional styling
                                                            echo '<div class="col-md-6 col-lg-4 mb-3">
                                                                <div class="sticky-note" data-note-id="' . htmlspecialchars($note['id']) . '">
                                                                    <span class="note-pin"></span>
                                                                    <span class="delete-note" data-note-id="' . htmlspecialchars($note['id']) . '" title="Delete note">
                                                                        <i class="fas fa-times"></i>
                                                                    </span>
                                                                    <div class="note-content">' . htmlspecialchars($note['content']) . '</div>
                                                                    <div class="note-date">' . $formattedDate . '</div>
                                                                </div>
                                                            </div>';
                                                        }
                                                    } else {
                                                        echo '<div class="col-12 empty-notes-state">
                                                            <i class="fas fa-sticky-note"></i>
                                                            <h5>No Notes Yet</h5>
                                                            <p>Click the "Add Note" button to create your first note</p>
                                                        </div>';
                                                    }
                                                    $notesStmt->close();
                                                } catch (Exception $e) {
                                                    error_log("Error fetching sticky notes for user " . $userId . ": " . $e->getMessage());
                                                    echo '<div class="col-12">
                                                        <div class="alert alert-danger">
                                                            <i class="fas fa-exclamation-circle me-2"></i>
                                                            Error loading notes. Please try again later.
                                                        </div>
                                                    </div>';
                                                }
                                            }
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                   <!-- Analytics Cards Section -->
                   <div class="row mt-5">
                            <!-- Lead Status Card -->
                            <div class="col-md-6">
                                <div class="card dashboard-card analytics-card">
                                    <div class="card-header">
                                        <h4><i class="fas fa-chart-pie me-2"></i>Lead Status</h4>
                                        <i class="fas fa-filter filter-icon" data-bs-toggle="modal" data-bs-target="#leadStatusFilterModal"></i>
                                    </div>
                                    <div class="card-body">
                                        <div class="analytics-info">
                                            <div class="date-range"><i class="fas fa-calendar-alt me-2"></i>FROM 01-05-2025 TO 31-05-2025</div>
                                            <span class="staff-badge badge"><i class="fas fa-user-tie me-1"></i>Star Tech</span>
                                        </div>
                                        <div class="no-data-message">
                                            <i class="fas fa-chart-bar"></i>
                                            <div class="no-data-text">No Lead Data Available</div>
                                            <div class="no-data-count">0</div>
                                        </div>
                                        <div class="chart-legend">
                                            <ul>
                                                <li><span style="background-color: #2c5282;"></span> New</li>
                                                <li><span style="background-color: #2b6cb0;"></span> Processing</li>
                                                <li><span style="background-color: #3182ce;"></span> Close-by</li>
                                                <li><span style="background-color: #4299e1;"></span> Confirm</li>
                                                <li><span style="background-color: #63b3ed;"></span> Cancel</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Lead Source Card -->
                            <div class="col-md-6">
                                <div class="card dashboard-card analytics-card">
                                    <div class="card-header">
                                        <h4><i class="fas fa-filter me-2"></i>Lead Source</h4>
                                        <i class="fas fa-filter filter-icon" data-bs-toggle="modal" data-bs-target="#leadSourceFilterModal"></i>
                                    </div>
                                    <div class="card-body">
                                        <div class="analytics-info">
                                            <div class="date-range"><i class="fas fa-calendar-alt me-2"></i>FROM 01-05-2025 TO 31-05-2025</div>
                                            <span class="staff-badge badge"><i class="fas fa-user-tie me-1"></i>Star Tech</span>
                                        </div>
                                        <div class="no-data-message">
                                            <i class="fas fa-chart-line"></i>
                                            <div class="no-data-text">No Source Data Available</div>
                                            <div class="no-data-count">0</div>
                                        </div>
                                        <div class="chart-legend">
                                            <ul>
                                                <li><span style="background-color: #2c5282;"></span> Online</li>
                                                <li><span style="background-color: #2b6cb0;"></span> Offline</li>
                                                <li><span style="background-color: #3182ce;"></span> Website</li>
                                                <li><span style="background-color: #4299e1;"></span> Whatsapp</li>
                                                <li><span style="background-color: #63b3ed;"></span> Customer Reminder</li>
                                                <li><span style="background-color: #90cdf4;"></span> Indiamart</li>
                                                <li><span style="background-color: #bee3f8;"></span> Facebook</li>
                                                <li><span style="background-color: #ebf8ff;"></span> Google Form</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <!-- End of Analytics Section -->
        </div>
    </div>
    <!-- Footer -->
    <?php include '../includes/dashboard-footer.php'; ?>

    <!-- Lead Status Filter Modal -->
    <div class="modal fade" id="leadStatusFilterModal" tabindex="-1" aria-labelledby="leadStatusFilterModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="leadStatusFilterModalLabel">Filter Lead Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="leadStatusFilterForm">
                        <div class="mb-3">
                            <label for="startDate" class="form-label">Start Date</label>
                            <input type="date" class="form-control" id="statusStartDate" name="startDate" value="2025-05-01">
                        </div>
                        <div class="mb-3">
                            <label for="endDate" class="form-label">End Date</label>
                            <input type="date" class="form-control" id="statusEndDate" name="endDate" value="2025-05-31">
                        </div>
                        <div class="mb-3">
                            <label for="user" class="form-label">User</label>
                            <select class="form-select" id="statusUser" name="user">
                                <option value="all">All Users</option>
                                <option value="1" selected>Star Tech</option>
                                <!-- Other users would be populated dynamically -->
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="applyStatusFilter">Submit</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Lead Source Filter Modal -->
    <div class="modal fade" id="leadSourceFilterModal" tabindex="-1" aria-labelledby="leadSourceFilterModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="leadSourceFilterModalLabel">Filter Lead Source</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="leadSourceFilterForm">
                        <div class="mb-3">
                            <label for="startDate" class="form-label">Start Date</label>
                            <input type="date" class="form-control" id="sourceStartDate" name="startDate" value="2025-05-01">
                        </div>
                        <div class="mb-3">
                            <label for="endDate" class="form-label">End Date</label>
                            <input type="date" class="form-control" id="sourceEndDate" name="endDate" value="2025-05-31">
                        </div>
                        <div class="mb-3">
                            <label for="user" class="form-label">User</label>
                            <select class="form-select" id="sourceUser" name="user">
                                <option value="all">All Users</option>
                                <option value="1" selected>Star Tech</option>
                                <!-- Other users would be populated dynamically -->
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="applySourceFilter">Submit</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Note Modal -->
    <div class="modal fade" id="addNoteModal" tabindex="-1" aria-labelledby="addNoteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addNoteModalLabel">Add Notes</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="noteContent" class="form-label">Notes:*</label>
                        <textarea class="form-control" id="noteContent" rows="5" placeholder="Enter Notes" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveNoteButton">Submit</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Success Message Toast -->
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
        <div id="successToast" class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    Note saved successfully!
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteNoteModal" tabindex="-1" aria-labelledby="deleteNoteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center">
                <div class="warning-icon mb-3">
                    <i class="fas fa-exclamation"></i>
                </div>
                <h4>Are You Sure?</h4>
                <p>Do you really want to delete this note? This process cannot be undone.</p>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteNoteButton">Yes, Delete It!</button>
            </div>
        </div>
    </div>
</div>

<!-- Analytics Filter Modal -->
<div class="modal fade" id="analyticsFilterModal" tabindex="-1" aria-labelledby="analyticsFilterModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="analyticsFilterModalLabel">Filter Analytics</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="startDate" class="form-label">Start Date:</label>
                    <input type="date" class="form-control" id="startDate">
                </div>
                <div class="mb-3">
                    <label for="endDate" class="form-label">End Date:</label>
                    <input type="date" class="form-control" id="endDate">
                </div>
                <div class="mb-3">
                    <label for="filterUser" class="form-label">User:</label>
                    <select class="form-select" id="filterUser">
                        <option value="">Select User</option>
                        <!-- User options will be loaded here by JavaScript -->
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="applyAnalyticsFilterButton">Submit</button>
            </div>
        </div>
    </div>
</div>

<!-- Add Lead Modal -->
<div class="modal fade" id="addLeadModal" tabindex="-1" aria-labelledby="addLeadModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg"> <!-- Use modal-lg for larger size -->
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addLeadModalLabel">Add Lead</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addLeadForm" action="save_lead.php" method="POST">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="leadStatus" class="form-label">Status:</label>
                            <select class="form-select" id="leadStatus" name="status">
                                <option value="New">New</option>
                                <option value="Processing">Processing</option>
                                <option value="Close-by">Close-by</option>
                                <option value="Confirm">Confirm</option>
                                <option value="Cancel">Cancel</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="leadSource" class="form-label">Source:</label>
                            <select class="form-select" id="leadSource" name="source">
                                <option value="Online">Online</option>
                                <option value="Offline">Offline</option>
                                <option value="Website">Website</option>
                                <option value="Whatsapp">Whatsapp</option>
                                <option value="Customer Reminder">Customer Reminder</option>
                                <option value="Indiamart">Indiamart</option>
                                <option value="Facebook">Facebook</option>
                                <option value="Google Form">Google Form</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="leadUser" class="form-label">User:</label>
                            <select class="form-select" id="leadUser" name="user_id">
                                <!-- User options will be loaded here dynamically -->
                                <option value="">Select User</option>
                            </select>
                        </div>
                         <div class="col-md-6">
                            <label for="customerMobileNumber" class="form-label">Customer Mobile Number:<span class="text-danger">*</span></label>
                             <div class="input-group">
                                  <!-- Country code dropdown - placeholder -->
                                 <select class="form-select country-code" name="country_code" style="flex: 0 0 auto; width: auto;">
                                     <option value="+91">+91</option>
                                      <!-- Add more country codes as needed -->
                                 </select>
                                 <input type="text" class="form-control" id="customerMobileNumber" name="mobile_number" placeholder="Enter Mobile Number" required>
                             </div>
                         </div>
                        <div class="col-md-6">
                            <label for="companyName" class="form-label">Company Name (Optional):</label>
                            <input type="text" class="form-control" id="companyName" name="company_name" placeholder="Enter Company Name">
                        </div>
                         <div class="col-md-6">
                             <label for="leadDate" class="form-label">Date:</label>
                             <input type="date" class="form-control" id="leadDate" name="lead_date" value="<?php echo date('Y-m-d'); ?>">
                         </div>
                        <div class="col-md-6">
                            <label for="customerName" class="form-label">Customer Name:<span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="customerName" name="customer_name" placeholder="Enter Customer Name" required>
                        </div>
                        <div class="col-md-6">
                            <label for="customerEmail" class="form-label">Email (Optional):</label>
                            <input type="email" class="form-control" id="customerEmail" name="customer_email" placeholder="Enter Customer Email">
                        </div>
                        <div class="col-md-6">
                            <label for="leadLabel" class="form-label">Label (Optional):</label>
                            <select class="form-select" id="leadLabel" name="label">
                                <option value="">Select...</option>
                                 <!-- Label options will likely be loaded here dynamically from your tags table -->
                            </select>
                        </div>
                         <div class="col-md-6">
                             <label for="leadReference" class="form-label">Reference (Optional):</label>
                             <input type="text" class="form-control" id="leadReference" name="reference" placeholder="Enter Reference">
                         </div>
                        <div class="col-12">
                            <label for="leadAddress" class="form-label">Address (Optional):</label>
                            <textarea class="form-control" id="leadAddress" name="address" rows="2" placeholder="Enter Address"></textarea>
                        </div>
                        <div class="col-12">
                            <label for="leadComment" class="form-label">Comment (Optional):</label>
                            <textarea class="form-control" id="leadComment" name="comment" rows="3" placeholder="Enter Comment"></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="addLeadForm" class="btn btn-primary">Submit</button>
            </div>
        </div>
    </div>
</div>

<!-- Add Reminder Modal -->
<div class="modal fade" id="addReminderModal" tabindex="-1" aria-labelledby="addReminderModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addReminderModalLabel">Add Reminder</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addReminderForm">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Recurrence:</label>
                            <div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="recurrence" id="reminderOnce" value="Once" checked>
                                    <label class="form-check-label" for="reminderOnce">Once</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="recurrence" id="reminderDaily" value="Daily">
                                    <label class="form-check-label" for="reminderDaily">Daily</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="recurrence" id="reminderWeekly" value="Weekly">
                                    <label class="form-check-label" for="reminderWeekly">Weekly</label>
                                </div>
                                 <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="recurrence" id="reminderMonthly" value="Monthly">
                                    <label class="form-check-label" for="reminderMonthly">Monthly</label>
                                </div>
                                 <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="recurrence" id="reminderQuarterly" value="Quarterly">
                                    <label class="form-check-label" for="reminderQuarterly">Quarterly</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="recurrence" id="reminderHalfYearly" value="HalfYearly">
                                    <label class="form-check-label" for="reminderHalfYearly">Half Yearly</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="recurrence" id="reminderYearly" value="Yearly">
                                    <label class="form-check-label" for="reminderYearly">Yearly</label>
                                </div>
                            </div>
                        </div>
                         <div class="col-md-6">
                            <label for="reminderDateTime" class="form-label">Date & Time:<span class="text-danger">*</span></label>
                             <!-- You might want separate date and time inputs here for better browser support and control -->
                            <input type="datetime-local" class="form-control" id="reminderDateTime" name="reminder_datetime" value="<?php echo date('Y-m-d H:i'); ?>" required>
                         </div>
                        <div class="col-md-6">
                            <label for="reminderUser" class="form-label">User:<span class="text-danger">*</span></label>
                            <select class="form-select" id="reminderUser" name="user_id" required>
                                 <!-- User options will be loaded here dynamically (similar to leadUser) -->
                                <option value="">Select User</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label for="reminderTemplate" class="form-label">Reminder Template:</label>
                            <select class="form-select" id="reminderTemplate" name="template">
                                <option value="">Select...</option>
                                <!-- Reminder template options will likely be loaded here dynamically -->
                            </select>
                        </div>
                        <div class="col-12">
                            <label for="reminderTitle" class="form-label">Title:<span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="reminderTitle" name="title" placeholder="Enter Title" required>
                        </div>
                        <div class="col-12">
                            <label for="reminderMessage" class="form-label">Message:</label>
                            <textarea class="form-control" id="reminderMessage" name="message" rows="3" placeholder="Enter Message"></textarea>
                        </div>
                         <div class="col-12">
                             <label class="form-label">Automation:</label>
                              <div>
                                  <div class="form-check form-switch">
                                      <input class="form-check-input" type="checkbox" id="whatsappAutomation" name="whatsapp_automation">
                                      <label class="form-check-label" for="whatsappAutomation">Whatsapp Automation</label>
                                  </div>
                              </div>
                         </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="addReminderForm" class="btn btn-primary">Submit</button>
            </div>
        </div>
    </div>
</div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom Dashboard JavaScript -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize tooltips
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
            
            // Handle sticky note deletion
            document.querySelectorAll('.delete-note').forEach(button => {
                button.addEventListener('click', function() {
                    const noteId = this.getAttribute('data-note-id');
                    if (confirm('Are you sure you want to delete this note?')) {
                        // Send AJAX request to delete note
                        const xhr = new XMLHttpRequest();
                        xhr.open('POST', 'ajax/delete_note.php', true);
                        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                        xhr.onload = function() {
                            if (xhr.status === 200) {
                                // Remove note from DOM
                                const noteElement = document.querySelector(`.sticky-note[data-note-id="${noteId}"]`).closest('.col-md-6');
                                noteElement.remove();
                                
                                // Check if there are no notes left
                                if (document.querySelectorAll('.sticky-note').length === 0) {
                                    document.getElementById('stickyNotesContainer').innerHTML = '<p class="text-muted mt-2 no-notes-message">There are no records to display.</p>';
                                }
                            }
                        };
                        xhr.send('note_id=' + noteId);
                    }
                });
            });
            
            // Responsive sidebar toggle
            const sidebarToggle = document.getElementById('sidebarToggle');
            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', function() {
                    document.body.classList.toggle('sidebar-open');
                    document.getElementById('sidebarOverlay').classList.toggle('show');
                });
            }
            
            // Close sidebar when clicking overlay
            const sidebarOverlay = document.getElementById('sidebarOverlay');
            if (sidebarOverlay) {
                sidebarOverlay.addEventListener('click', function() {
                    document.body.classList.remove('sidebar-open');
                    this.classList.remove('show');
                });
            }
            
            // Lead Status Filter Functionality
            const applyStatusFilter = document.getElementById('applyStatusFilter');
            if (applyStatusFilter) {
                applyStatusFilter.addEventListener('click', function() {
                    const startDate = document.getElementById('statusStartDate').value;
                    const endDate = document.getElementById('statusEndDate').value;
                    const user = document.getElementById('statusUser').value;
                    const userText = document.getElementById('statusUser').options[document.getElementById('statusUser').selectedIndex].text;
                    
                    // Format dates for display (YYYY-MM-DD to DD-MM-YYYY)
                    const formattedStartDate = formatDateForDisplay(startDate);
                    const formattedEndDate = formatDateForDisplay(endDate);
                    
                    // Update the date range and user badge in the Lead Status card
                    const dateRangeElement = document.querySelector('.analytics-card:nth-of-type(1) .date-range');
                    const staffBadgeElement = document.querySelector('.analytics-card:nth-of-type(1) .staff-badge');
                    
                    if (dateRangeElement) {
                        dateRangeElement.textContent = `FROM ${formattedStartDate} TO ${formattedEndDate}`;
                    }
                    
                    if (staffBadgeElement) {
                        staffBadgeElement.textContent = userText;
                    }
                    
                    // Here you would typically make an AJAX call to fetch new data based on filters
                    // For now, we'll just close the modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('leadStatusFilterModal'));
                    if (modal) {
                        modal.hide();
                    }
                });
            }
            
            // Lead Source Filter Functionality
            const applySourceFilter = document.getElementById('applySourceFilter');
            if (applySourceFilter) {
                applySourceFilter.addEventListener('click', function() {
                    const startDate = document.getElementById('sourceStartDate').value;
                    const endDate = document.getElementById('sourceEndDate').value;
                    const user = document.getElementById('sourceUser').value;
                    const userText = document.getElementById('sourceUser').options[document.getElementById('sourceUser').selectedIndex].text;
                    
                    // Format dates for display (YYYY-MM-DD to DD-MM-YYYY)
                    const formattedStartDate = formatDateForDisplay(startDate);
                    const formattedEndDate = formatDateForDisplay(endDate);
                    
                    // Update the date range and user badge in the Lead Source card
                    const dateRangeElement = document.querySelector('.analytics-card:nth-of-type(2) .date-range');
                    const staffBadgeElement = document.querySelector('.analytics-card:nth-of-type(2) .staff-badge');
                    
                    if (dateRangeElement) {
                        dateRangeElement.textContent = `FROM ${formattedStartDate} TO ${formattedEndDate}`;
                    }
                    
                    if (staffBadgeElement) {
                        staffBadgeElement.textContent = userText;
                    }
                    
                    // Here you would typically make an AJAX call to fetch new data based on filters
                    // For now, we'll just close the modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('leadSourceFilterModal'));
                    if (modal) {
                        modal.hide();
                    }
                });
            }
            
            // Helper function to format date from YYYY-MM-DD to DD-MM-YYYY
            function formatDateForDisplay(dateString) {
                if (!dateString) return '';
                const parts = dateString.split('-');
                if (parts.length !== 3) return dateString;
                return `${parts[2]}-${parts[1]}-${parts[0]}`;
            }
        });
    </script>
    <!-- Add Note Modal -->
    <div class="modal fade" id="addNoteModal" tabindex="-1" aria-labelledby="addNoteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addNoteModalLabel"><i class="fas fa-sticky-note me-2"></i>Add New Note</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addNoteForm">
                        <div class="mb-3">
                            <label for="noteContent" class="form-label">Note Content</label>
                            <textarea class="form-control" id="noteContent" rows="5" placeholder="Enter your note here..."></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveNoteBtn">Save Note</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Add Note functionality
        document.addEventListener('DOMContentLoaded', function() {
            // Save Note button click handler
            const saveNoteBtn = document.getElementById('saveNoteBtn');
            if (saveNoteBtn) {
                saveNoteBtn.addEventListener('click', function() {
                    const noteContent = document.getElementById('noteContent').value.trim();
                    if (!noteContent) {
                        alert('Please enter note content');
                        return;
                    }

                    // Send AJAX request to save the note
                    fetch('save_note.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'content=' + encodeURIComponent(noteContent)
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Close the modal
                            const modal = bootstrap.Modal.getInstance(document.getElementById('addNoteModal'));
                            modal.hide();
                            
                            // Clear the form
                            document.getElementById('noteContent').value = '';
                            
                            // Refresh the notes section
                            location.reload();
                        } else {
                            alert('Error saving note: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error saving note:', error);
                        alert('An error occurred while saving your note. Please try again.');
                    });
                });
            }

            // Delete Note functionality
            document.querySelectorAll('.delete-note').forEach(button => {
                button.addEventListener('click', function() {
                    const noteId = this.getAttribute('data-note-id');
                    if (confirm('Are you sure you want to delete this note?')) {
                        // Send AJAX request to delete the note
                        fetch('delete_note.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: 'note_id=' + encodeURIComponent(noteId)
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // Remove the note from the DOM
                                const noteElement = document.querySelector(`.sticky-note[data-note-id="${noteId}"]`).closest('.col-md-6');
                                noteElement.remove();
                                
                                // If no notes left, show empty state
                                if (document.querySelectorAll('.sticky-note').length === 0) {
                                    const emptyState = `
                                        <div class="col-12 empty-notes-state">
                                            <i class="fas fa-sticky-note"></i>
                                            <h5>No Notes Yet</h5>
                                            <p>Click the "Add Note" button to create your first note</p>
                                        </div>
                                    `;
                                    document.getElementById('stickyNotesContainer').innerHTML = emptyState;
                                }
                            } else {
                                alert('Error deleting note: ' + data.message);
                            }
                        })
                        .catch(error => {
                            console.error('Error deleting note:', error);
                            alert('An error occurred while deleting your note. Please try again.');
                        });
                    }
                });
            });
        });
    </script>
</body>
</html>