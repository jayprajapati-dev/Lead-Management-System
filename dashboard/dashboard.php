<?php
// Include authentication check (must be first)
require_once '../includes/auth_check.php';

// Include other required files
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
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h4 class="mb-0"><i class="fas fa-user-plus me-2"></i>Today's Leads</h4>
                                </div>
                                <div class="card-body p-0">
                                    <ul class="nav nav-tabs card-tabs" role="tablist">
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link active" id="new-tab" data-bs-toggle="tab" data-bs-target="#new" type="button" role="tab">
                                                New <span class="badge badge-count bg-primary">0</span>
                                            </button>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link" id="processing-tab" data-bs-toggle="tab" data-bs-target="#processing" type="button" role="tab">
                                                Processing <span class="badge badge-count bg-purple">0</span>
                                            </button>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link" id="close-by-tab" data-bs-toggle="tab" data-bs-target="#close-by" type="button" role="tab">
                                                Close-By <span class="badge badge-count bg-warning">0</span>
                                            </button>
                                        </li>
                                    </ul>
                                    <div class="tab-content p-3">
                                        <div class="tab-pane fade show active" id="new" role="tabpanel">
                                            <div class="empty-state">
                                                <i class="fas fa-clipboard-list"></i>
                                                <h5>No New Leads</h5>
                                                <p>There are no new leads assigned to you today</p>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="processing" role="tabpanel">
                                            <div class="empty-state">
                                                <i class="fas fa-spinner"></i>
                                                <h5>No Processing Leads</h5>
                                                <p>There are no leads in processing status today</p>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="close-by" role="tabpanel">
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
                        <div class="sticky-notes-section mt-4">
                            <div class="section-header d-flex justify-content-between align-items-center mb-3">
                                <h5 class="mb-0">Sticky Notes</h5>
                                <div class="note-actions">
                                    <!-- Add Note Button (+ icon) -->
                                    <button class="btn btn-sm add-note-icon" onclick="showAddNoteForm()">
                                    <i class="fas fa-plus"></i>
                                </button>
                                    <!-- Add Note Form Button (initially hidden) -->
                                    <button class="btn btn-sm btn-primary add-note-button" onclick="showAddNoteForm()" style="display: none;">
                                        <i class="fas fa-plus me-2"></i>Add Note
                                </button>
                                </div>
                            </div>

                            <!-- Add Note Form -->
                            <div id="addNoteForm" class="add-note-form" style="display: none;">
                                <div class="sticky-note-form">
                                    <div class="form-floating mb-3">
                                        <textarea id="noteContent" class="form-control" placeholder="Enter your note here..." style="height: 120px"></textarea>
                                        <label for="noteContent">Enter your note here...</label>
                                    </div>
                                    <div class="form-buttons mt-2">
                                        <button class="btn btn-secondary btn-sm" onclick="hideAddNoteForm()">Cancel</button>
                                        <button class="btn btn-primary btn-sm" onclick="saveNote()">Save Note</button>
                                    </div>
                                </div>
                            </div>

                            <!-- Success Toast for real-time feedback -->
                            <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1100">
                                <div id="noteToast" class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
                                    <div class="d-flex">
                                        <div class="toast-body">
                                            <i class="fas fa-check-circle me-2"></i>
                                            <span id="toastMessage">Note Successfully added</span>
                                        </div>
                                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                                    </div>
                                </div>
                            </div>

                            <!-- Sticky Notes Grid -->
                            <div class="sticky-notes-grid w-100" id="stickyNotesContainer">
                                <?php
                                // Get user's notes
                                $notes_query = $conn->prepare("SELECT id, content, created_at FROM notes WHERE user_id = ? ORDER BY created_at DESC");
                                $notes_query->bind_param("i", $_SESSION['user_id']);
                                $notes_query->execute();
                                $notes_result = $notes_query->get_result();
                                $has_notes = $notes_result->num_rows > 0;
                                ?>
                                
                                <!-- Empty State (initially hidden if there are notes) -->
                                <div class="empty-state text-center p-4" id="emptyState" style="display: <?php echo $has_notes ? 'none' : 'block'; ?>">
                                    <i class="fas fa-sticky-note fa-3x text-muted mb-3"></i>
                                    <h5>No Notes Yet</h5>
                                    <p class="text-muted">Click the "+" button to add your first note</p>
                                </div>

                                <!-- Notes Container - Responsive grid layout -->
                                <div id="notesWrapper" class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4" style="display: <?php echo $has_notes ? 'flex' : 'none'; ?>;">
                                    <?php while ($note = $notes_result->fetch_assoc()): ?>
                                    <div class="col">
                                        <div class="sticky-note" data-note-id="<?php echo $note['id']; ?>">
                                            <button class="delete-btn" data-note-id="<?php echo $note['id']; ?>" onclick="confirmDelete(<?php echo $note['id']; ?>)">
                                                <i class="fas fa-times"></i>
                                            </button>
                                            <div class="note-content" id="note-content-<?php echo $note['id']; ?>">
                                                <?php echo htmlspecialchars($note['content']); ?>
                                            </div>
                                            <button class="view-full-note" onclick="viewFullNote(<?php echo $note['id']; ?>, this)">
                                                View More
                                            </button>
                                            <span class="d-none note-full-content"><?php echo htmlspecialchars($note['content']); ?></span>
                                            <span class="d-none note-full-date"><?php echo date('M d, Y g:i A', strtotime($note['created_at'])); ?></span>
                                            <div class="note-date small text-muted mt-2">
                                                <?php echo date('M d, Y g:i A', strtotime($note['created_at'])); ?>
                                            </div>
                                            <div class="note-fold"></div>
                                        </div>
                                    </div>
                                    <?php endwhile; ?>
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
                                            <div class="date-range"><i class="fas fa-calendar-alt me-2"></i>FROM 01-06-2025 TO 01-06-2025</div>
                                            <span class="staff-badge badge"><i class="fas fa-user-tie me-1"></i>Star Tech</span>
                                        </div>
                                        <div class="chart-container position-relative" style="height: 300px;">
                                            <canvas id="leadStatusChart"></canvas>
                                            <div class="loading-spinner d-none">
                                                <div class="spinner-border text-primary" role="status">
                                                    <span class="visually-hidden">Loading...</span>
                                        </div>
                                            </div>
                                        </div>
                                        <div class="chart-legend mt-3">
                                            <ul class="list-unstyled d-flex flex-wrap justify-content-center gap-3 mb-0">
                                                <li><span class="legend-dot" style="background-color: #0d6efd;"></span> New</li>
                                                <li><span class="legend-dot" style="background-color: #6610f2;"></span> Processing</li>
                                                <li><span class="legend-dot" style="background-color: #ffc107;"></span> Close-by</li>
                                                <li><span class="legend-dot" style="background-color: #198754;"></span> Confirm</li>
                                                <li><span class="legend-dot" style="background-color: #dc3545;"></span> Cancel</li>
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
                                            <div class="date-range"><i class="fas fa-calendar-alt me-2"></i>FROM 01-06-2025 TO 01-06-2025</div>
                                            <span class="staff-badge badge"><i class="fas fa-user-tie me-1"></i>Star Tech</span>
                                        </div>
                                        <div class="chart-container position-relative" style="height: 300px;">
                                            <canvas id="leadSourceChart"></canvas>
                                            <div class="loading-spinner d-none">
                                                <div class="spinner-border text-primary" role="status">
                                                    <span class="visually-hidden">Loading...</span>
                                        </div>
                                            </div>
                                        </div>
                                        <div class="chart-legend mt-3">
                                            <ul class="list-unstyled d-flex flex-wrap justify-content-center gap-3 mb-0">
                                                <li><span class="legend-dot" style="background-color: #0d6efd;"></span> Online</li>
                                                <li><span class="legend-dot" style="background-color: #6c757d;"></span> Offline</li>
                                                <li><span class="legend-dot" style="background-color: #198754;"></span> Website</li>
                                                <li><span class="legend-dot" style="background-color: #ffc107;"></span> Whatsapp</li>
                                                <li><span class="legend-dot" style="background-color: #6610f2;"></span> Customer Reminder</li>
                                                <li><span class="legend-dot" style="background-color: #0dcaf0;"></span> Indiamart</li>
                                                <li><span class="legend-dot" style="background-color: #20c997;"></span> Facebook</li>
                                                <li><span class="legend-dot" style="background-color: #fd7e14;"></span> Google Form</li>
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
    
    <!-- Include Modal Forms -->
    <?php include '../includes/modals/add-lead.php'; ?>
    <?php include '../includes/modals/add-task.php'; ?>
    <?php include '../includes/modals/add-note.php'; ?>
    <?php include '../includes/modals/add-reminder.php'; ?>
    
    <!-- Full Note Modal -->
    <div class="modal fade" id="fullNoteModal" tabindex="-1" aria-labelledby="fullNoteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="fullNoteModalLabel">Note Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="fullNoteContent"></div>
                    <div id="fullNoteDate" class="note-date"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="editNoteBtn">Edit</button>
                </div>
            </div>
        </div>
    </div>

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

    <!-- Delete Confirmation Modal -->
    <div class="modal fade delete-modal" id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center p-4">
                    <div class="warning-icon mb-3">
                        <i class="fas fa-exclamation"></i>
                    </div>
                    <h4 class="modal-title mb-3">Are You Sure?</h4>
                    <div class="modal-buttons">
                        <button type="button" class="btn btn-delete" onclick="deleteNote()">Yes, Delete It!</button>
                        <button type="button" class="btn btn-cancel" data-bs-dismiss="modal">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Success Toast -->
    <div class="toast-container position-fixed top-0 end-0 p-3">
        <div id="successToast" class="toast align-items-center text-white border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    <i class="fas fa-check-circle me-2"></i>
                    <span class="toast-message"></span>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
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
            document.querySelectorAll('.delete-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const noteId = this.getAttribute('data-note-id');
                    confirmDelete(noteId);
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

    <!-- Add Chart.js before your custom scripts -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Add your custom dashboard.js -->
    <script src="js/dashboard.js"></script>

    <style>
        .chart-container {
            position: relative;
            margin: auto;
        }
        
        .loading-spinner {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(255, 255, 255, 0.8);
            width: 100%;
            height: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        .chart-center-text {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
        }
        
        .chart-center-text .total-value {
            font-size: 24px;
            font-weight: bold;
            color: #333;
        }
        
        .chart-center-text .total-label {
            font-size: 14px;
            color: #666;
        }
        
        .legend-dot {
            display: inline-block;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin-right: 5px;
        }
        
        .chart-legend ul {
            margin: 0;
            padding: 0;
        }
        
        .chart-legend li {
            display: inline-flex;
            align-items: center;
            margin-right: 15px;
            font-size: 0.875rem;
            color: #666;
        }

        .sticky-notes-section {
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 10px;
        }

        .section-header {
            padding: 10px 0;
        }

        .note-actions {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .add-note-icon {
            background: none;
            border: none;
            color: #4CAF50;
            font-size: 20px;
            padding: 8px;
            border-radius: 50%;
            transition: all 0.3s ease;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .add-note-icon:hover {
            background-color: rgba(76, 175, 80, 0.1);
            transform: rotate(90deg);
        }

        .add-note-button {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 20px;
            transition: all 0.3s ease;
        }

        .add-note-button:hover {
            background-color: #388E3C;
            transform: translateY(-1px);
        }

        .add-note-form {
            max-width: 400px;
            margin: 20px 0;
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .form-floating textarea {
            border: 1px solid #ddd;
            resize: vertical;
        }

        .form-floating textarea:focus {
            border-color: #4CAF50;
            box-shadow: 0 0 0 0.2rem rgba(76, 175, 80, 0.25);
        }

        .form-buttons {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }

        .sticky-notes-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            padding: 20px 0;
        }

        .sticky-note {
            background: #00e676;
            min-height: 200px;
            padding: 20px;
            position: relative;
            border-radius: 8px;
            box-shadow: 0 3px 6px rgba(0,0,0,0.16);
            color: white;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .sticky-note:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .sticky-note .delete-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
            font-size: 16px;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            opacity: 0;
            transition: all 0.3s ease;
        }

        .sticky-note:hover .delete-btn {
            opacity: 1;
        }

        .sticky-note .delete-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: scale(1.1);
        }

        .note-content {
            word-wrap: break-word;
            overflow-wrap: break-word;
            font-size: 14px;
            line-height: 1.6;
        }

        .note-fold {
            position: absolute;
            right: 0;
            bottom: 0;
            width: 25px;
            height: 25px;
            background: linear-gradient(135deg, transparent 50%, rgba(0,0,0,0.1) 50%);
            border-radius: 0 0 8px 0;
        }

        .empty-state {
            grid-column: 1 / -1;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .empty-state i {
            color: #4CAF50;
        }

        .empty-state h5 {
            color: #333;
            margin: 10px 0;
        }

        .empty-state p {
            color: #666;
        }

        /* Toast Styling */
        .toast-container {
            z-index: 1051;
        }

        .toast {
            opacity: 0;
            transition: opacity 0.3s ease-in-out;
        }

        .toast.show {
            opacity: 1;
        }

        .toast-body {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            font-size: 14px;
            font-weight: 500;
        }

        .toast-body i {
            font-size: 18px;
        }

        /* Success Toast */
        .bg-success {
            background-color: #4CAF50 !important;
        }

        /* Error Toast */
        .bg-danger {
            background-color: #f44336 !important;
        }

        /* Add animation styles for notes */
        .sticky-note {
            opacity: 1;
            transform: scale(1);
            transition: all 0.3s ease-in-out;
        }
    </style>

    <script>
    let currentDeleteId = null;
    let currentNoteId = null;
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
    const fullNoteModal = new bootstrap.Modal(document.getElementById('fullNoteModal'));
    const successToast = new bootstrap.Toast(document.getElementById('successToast'), {
        delay: 3000,
        animation: true
    });
    let isAddButtonVisible = false;

    function showAddNoteForm() {
        const addNoteForm = document.getElementById('addNoteForm');
        const addNoteIcon = document.querySelector('.add-note-icon');
        const addNoteButton = document.querySelector('.add-note-button');
        
        if (addNoteForm.style.display === 'none') {
            addNoteForm.style.display = 'block';
            if (!isAddButtonVisible) {
                addNoteIcon.style.display = 'none';
                addNoteButton.style.display = 'block';
                isAddButtonVisible = true;
            }
            document.getElementById('noteContent').focus();
        }
    }

    function hideAddNoteForm() {
        const addNoteForm = document.getElementById('addNoteForm');
        addNoteForm.style.display = 'none';
        document.getElementById('noteContent').value = '';
    }

    function saveNote() {
        const content = document.getElementById('noteContent').value.trim();
        if (!content) {
            showToast('Please enter some content for your note.', 'error');
            return;
        }
        
        // Check if we're editing an existing note
        const noteId = document.getElementById('noteId').value.trim();
        const formData = {
            content: content
        };
        
        // Include note ID if it exists (for editing)
        if (noteId) {
            formData.id = noteId;
        }

        // Use absolute path to ensure it works consistently
        fetch('/Lead-Management-System/dashboard/ajax/save-note.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(formData)
        })
        .then(response => response.json())
        .then(data => {
            const toastEl = document.getElementById('noteToast');
            
            if (data.status === 'success') {
                // Show success message
                toastEl.classList.remove('bg-danger');
                toastEl.classList.add('bg-success');
                document.getElementById('toastMessage').textContent = noteId ? 'Note successfully updated' : 'Note successfully added';
                const toast = new bootstrap.Toast(toastEl);
                toast.show();
                
                // Clear the note ID field
                document.getElementById('noteId').value = '';
                
                // Clear form and hide it
                hideAddNoteForm();
                
                // Hide empty state and show notes wrapper
                document.getElementById('emptyState').style.display = 'none';
                const notesWrapper = document.getElementById('notesWrapper');
                notesWrapper.style.display = 'flex';
                
                // Get current date for display
                const now = new Date();
                const formattedDate = now.toLocaleDateString('en-US', { 
                    month: 'short', 
                    day: 'numeric', 
                    year: 'numeric',
                    hour: 'numeric',
                    minute: 'numeric',
                    hour12: true
                });
                
                // Create column element
                const colElement = document.createElement('div');
                colElement.className = 'col';
                
                // Create note element
                const noteElement = createNoteElement(data.note_id, content, formattedDate);
                noteElement.style.opacity = '0';
                noteElement.style.transform = 'scale(0.8)';
                colElement.appendChild(noteElement);
                
                // Add the column at the beginning of the wrapper
                notesWrapper.insertBefore(colElement, notesWrapper.firstChild);
                
                // Trigger animation
                setTimeout(() => {
                    noteElement.style.opacity = '1';
                    noteElement.style.transform = 'scale(1)';
                }, 50);
            } else {
                // Show error message
                toastEl.classList.remove('bg-success');
                toastEl.classList.add('bg-danger');
                document.getElementById('toastMessage').textContent = data.message || 'Error saving note';
                const toast = new bootstrap.Toast(toastEl);
                toast.show();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            const toastEl = document.getElementById('noteToast');
            toastEl.classList.remove('bg-success');
            toastEl.classList.add('bg-danger');
            document.getElementById('toastMessage').textContent = 'Error saving note';
            const toast = new bootstrap.Toast(toastEl);
            toast.show();
        });
    }

    // Check if notes have overflow content and add the has-overflow class
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(checkNotesOverflow, 100); // Slight delay to ensure content is rendered
    });
    
    function checkNotesOverflow() {
        const noteContents = document.querySelectorAll('.note-content');
        noteContents.forEach(note => {
            if (note.scrollHeight > note.clientHeight || note.scrollWidth > note.clientWidth) {
                note.classList.add('has-overflow');
            }
        });
    }
    
    // Function to view the full note content in a modal
    function viewFullNote(noteId, button) {
        // Get the parent sticky note element
        const noteElement = button.closest('.sticky-note');
        
        // Get content and date from hidden spans
        const content = noteElement.querySelector('.note-full-content').textContent;
        const date = noteElement.querySelector('.note-full-date').textContent;
        
        // Set current note ID for potential editing
        currentNoteId = noteId;
        
        // Populate the modal
        document.getElementById('fullNoteContent').textContent = content;
        document.getElementById('fullNoteDate').textContent = date;
        fullNoteModal.show();
        
        // Set up the edit button
        document.getElementById('editNoteBtn').onclick = function() {
            fullNoteModal.hide();
            // Trigger the edit note functionality
            editNote(noteId, content);
        };
    }
    
    // Function to edit a note
    function editNote(noteId, content) {
        // Populate the add note form with existing content
        document.getElementById('noteId').value = noteId;
        document.getElementById('noteContent').value = content;
        
        // Show the add/edit note form
        showAddNoteForm();
    }
    
    function createNoteElement(noteId, content, dateStr) {
        const div = document.createElement('div');
        div.className = 'sticky-note';
        div.dataset.noteId = noteId;
        div.innerHTML = `
            <button class="delete-btn" data-note-id="${noteId}" onclick="confirmDelete(${noteId})">
                <i class="fas fa-times"></i>
            </button>
            <div class="note-content" id="note-content-${noteId}">
                ${content}
            </div>
            <button class="view-full-note" onclick="viewFullNote(${noteId}, this)">
                View More
            </button>
            <span class="d-none note-full-content">${content}</span>
            <span class="d-none note-full-date">${dateStr}</span>
            <div class="note-date small text-muted mt-2">
                ${dateStr}
            </div>
            <div class="note-fold"></div>
        `;
        
        // Check for overflow after the element is added to DOM
        setTimeout(() => {
            const noteContent = div.querySelector('.note-content');
            if (noteContent && (noteContent.scrollHeight > noteContent.clientHeight || noteContent.scrollWidth > noteContent.clientWidth)) {
                noteContent.classList.add('has-overflow');
            }
        }, 10);
        
        return div;
    }

    function confirmDelete(noteId) {
        if (!noteId) return;
        currentDeleteId = noteId;
        deleteModal.show();
    }

    function deleteNote() {
        if (!currentDeleteId) return;
        
        fetch('ajax/delete-note.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ id: currentDeleteId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                // Hide modal
                deleteModal.hide();
                
                // Show real-time success message with toast
                const toastEl = document.getElementById('noteToast');
                document.getElementById('toastMessage').textContent = 'Note deleted successfully';
                const toast = new bootstrap.Toast(toastEl);
                toast.show();
                
                // Find the column containing the note
                const noteElement = document.querySelector(`.sticky-note[data-note-id="${currentDeleteId}"]`);
                if (noteElement) {
                    // Get the parent column
                    const colElement = noteElement.closest('.col');
                    
                    // Fade out animation
                    noteElement.style.opacity = '0';
                    noteElement.style.transform = 'scale(0.8)';
                    
                    // Remove after animation completes
                    setTimeout(() => {
                        // Remove the entire column
                        if (colElement) {
                            colElement.remove();
                        } else {
                            noteElement.remove();
                        }
                        
                        // Check if there are any remaining notes
                        const notesWrapper = document.getElementById('notesWrapper');
                        if (!notesWrapper.children.length) {
                            notesWrapper.style.display = 'none';
                            document.getElementById('emptyState').style.display = 'block';
                        }
                    }, 300);
                }
                
                // Reset currentDeleteId
                currentDeleteId = null;
            } else {
                // Show error message
                const toastEl = document.getElementById('noteToast');
                toastEl.classList.remove('bg-success');
                toastEl.classList.add('bg-danger');
                document.getElementById('toastMessage').textContent = data.message || 'Error deleting note';
                const toast = new bootstrap.Toast(toastEl);
                toast.show();
                
                // Reset toast color after it's hidden
                toastEl.addEventListener('hidden.bs.toast', function () {
                    toastEl.classList.remove('bg-danger');
                    toastEl.classList.add('bg-success');
                }, { once: true });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            // Show error message
            const toastEl = document.getElementById('noteToast');
            toastEl.classList.remove('bg-success');
            toastEl.classList.add('bg-danger');
            document.getElementById('toastMessage').textContent = 'Error deleting note';
            const toast = new bootstrap.Toast(toastEl);
            toast.show();
            
            // Reset toast color after it's hidden
            toastEl.addEventListener('hidden.bs.toast', function () {
                toastEl.classList.remove('bg-danger');
                toastEl.classList.add('bg-success');
            }, { once: true });
        });
    }

    // Close form when clicking outside
    document.addEventListener('click', function(e) {
        const form = document.getElementById('addNoteForm');
        const addNoteIcon = document.querySelector('.add-note-icon');
        const addNoteButton = document.querySelector('.add-note-button');
        
        if (form.style.display === 'block' && 
            !form.contains(e.target) && 
            !addNoteIcon.contains(e.target) &&
            !addNoteButton.contains(e.target)) {
            hideAddNoteForm();
        }
    });
    </script>
    
    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteConfirmModalLabel">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this note? This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" onclick="deleteNote()">Delete</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Full Note Modal -->
    <div class="modal fade" id="fullNoteModal" tabindex="-1" aria-labelledby="fullNoteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="fullNoteModalLabel">Note Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3" id="fullNoteContent"></div>
                    <div class="text-muted small" id="fullNoteDate"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="editNoteBtn">Edit</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Note Toast -->
    <div class="toast-container position-fixed top-0 end-0 p-3">
        <div id="noteToast" class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body" id="toastMessage">
                    Note action successful
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>
</body>
</html>