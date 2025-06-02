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
                                <button class="btn btn-sm add-note-btn" onclick="showAddNoteForm()">
                                    <i class="fas fa-plus"></i>
                                </button>
                            </div>

                            <!-- Add Note Form -->
                            <div id="addNoteForm" class="add-note-form" style="display: none;">
                                <div class="sticky-note-form">
                                    <textarea id="noteContent" placeholder="Enter your note here..." class="form-control"></textarea>
                                    <div class="form-buttons mt-2">
                                        <button class="btn btn-secondary btn-sm" onclick="hideAddNoteForm()">Cancel</button>
                                        <button class="btn btn-primary btn-sm" onclick="saveNote()">Submit</button>
                                    </div>
                                </div>
                            </div>

                            <!-- Sticky Notes Grid -->
                            <div class="sticky-notes-grid" id="stickyNotesContainer">
                                <?php
                                // Get user's notes
                                $notes_query = $conn->prepare("SELECT id, content FROM notes WHERE user_id = ? ORDER BY created_at DESC");
                                $notes_query->bind_param("i", $_SESSION['user_id']);
                                $notes_query->execute();
                                $notes_result = $notes_query->get_result();
                                
                                while ($note = $notes_result->fetch_assoc()):
                                ?>
                                <div class="sticky-note" data-note-id="<?php echo $note['id']; ?>">
                                    <button class="delete-btn" data-note-id="<?php echo $note['id']; ?>" onclick="confirmDelete(<?php echo $note['id']; ?>)">
                                        <i class="fas fa-times"></i>
                                    </button>
                                    <div class="note-content">
                                        <?php echo htmlspecialchars($note['content']); ?>
                                    </div>
                                    <div class="note-fold"></div>
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
        <div id="successToast" class="toast align-items-center text-white bg-success border-0" role="alert" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body"></div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
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
        }

        .section-header {
            padding: 10px 0;
        }

        .add-note-btn {
            background: none;
            border: none;
            color: #4CAF50;
            font-size: 20px;
        }

        .add-note-btn:hover {
            color: #388E3C;
        }

        .sticky-notes-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
            padding: 20px 0;
        }

        .sticky-note {
            background: #00e676;
            min-height: 200px;
            padding: 20px;
            position: relative;
            border-radius: 2px;
            box-shadow: 0 3px 6px rgba(0,0,0,0.16);
            color: white;
            cursor: pointer;
            transition: transform 0.2s ease;
        }

        .sticky-note:hover {
            transform: translateY(-5px);
        }

        .sticky-note .delete-btn {
            position: absolute;
            top: 5px;
            right: 5px;
            background: none;
            border: none;
            color: white;
            font-size: 16px;
            opacity: 0.7;
            cursor: pointer;
            padding: 5px;
            z-index: 1;
            transition: opacity 0.2s ease;
        }

        .sticky-note .delete-btn:hover {
            opacity: 1;
        }

        .note-content {
            word-wrap: break-word;
            overflow-wrap: break-word;
            font-size: 14px;
        }

        .note-fold {
            position: absolute;
            right: 0;
            bottom: 0;
            width: 20px;
            height: 20px;
            background: linear-gradient(135deg, transparent 50%, rgba(0,0,0,0.1) 50%);
        }

        .add-note-form {
            max-width: 300px;
            margin-bottom: 20px;
        }

        .sticky-note-form textarea {
            width: 100%;
            min-height: 100px;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 10px;
            margin-bottom: 10px;
            resize: vertical;
        }

        .form-buttons {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }

        /* Delete Modal Styles */
        .delete-modal .modal-content {
            border-radius: 10px;
            border: none;
        }

        .delete-modal .modal-body {
            text-align: center;
        }

        .warning-icon {
            width: 60px;
            height: 60px;
            background: #FFF3CD;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
        }

        .warning-icon i {
            color: #FF9800;
            font-size: 24px;
        }

        .modal-buttons {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin-top: 20px;
        }

        .btn-delete {
            background-color: #2F2B3D;
            color: white;
            padding: 8px 20px;
            border-radius: 5px;
        }

        .btn-cancel {
            background-color: white;
            color: #6c757d;
            border: 1px solid #6c757d;
            padding: 8px 20px;
            border-radius: 5px;
        }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .empty-state i {
            color: #4CAF50;
            margin-bottom: 15px;
        }

        .empty-state h5 {
            color: #333;
            margin-bottom: 10px;
        }

        .empty-state p {
            color: #666;
            margin-bottom: 0;
        }

        .delete-modal .btn-delete:hover {
            background-color: #1a1a1a;
        }

        .delete-modal .btn-cancel:hover {
            background-color: #f8f9fa;
        }
    </style>

    <script>
    let currentDeleteId = null;
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
    const successToast = new bootstrap.Toast(document.getElementById('successToast'));

    function showAddNoteForm() {
        document.getElementById('addNoteForm').style.display = 'block';
    }

    function hideAddNoteForm() {
        document.getElementById('addNoteForm').style.display = 'none';
        document.getElementById('noteContent').value = '';
    }

    function saveNote() {
        const content = document.getElementById('noteContent').value.trim();
        if (!content) return;

        fetch('ajax/save-note.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ content: content })
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                // Show success message
                document.querySelector('.toast-body').textContent = 'Note added successfully!';
                successToast.show();
                
                // Clear form and hide it
                hideAddNoteForm();
                
                // Refresh the page to show new note
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            }
        })
        .catch(error => console.error('Error:', error));
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
                
                // Show success message
                document.querySelector('.toast-body').textContent = 'Note deleted successfully!';
                successToast.show();
                
                // Remove the note from view
                const noteElement = document.querySelector(`.sticky-note[data-note-id="${currentDeleteId}"]`);
                if (noteElement) {
                    noteElement.remove();
                }
                
                // Reset currentDeleteId
                currentDeleteId = null;
                
                // If no notes left, show empty state
                const remainingNotes = document.querySelectorAll('.sticky-note');
                if (remainingNotes.length === 0) {
                    const container = document.getElementById('stickyNotesContainer');
                    container.innerHTML = `
                        <div class="empty-state text-center p-4">
                            <i class="fas fa-sticky-note fa-3x text-muted mb-3"></i>
                            <h5>No Notes Yet</h5>
                            <p class="text-muted">Click the "+" button to add your first note</p>
                        </div>
                    `;
                }
            } else {
                // Show error message
                document.querySelector('.toast-body').textContent = data.message || 'Error deleting note';
                successToast.show();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.querySelector('.toast-body').textContent = 'Error deleting note';
            successToast.show();
        });
    }

    // Close form when clicking outside
    document.addEventListener('click', function(e) {
        const form = document.getElementById('addNoteForm');
        const addButton = document.querySelector('.add-note-btn');
        if (form.style.display === 'block' && 
            !form.contains(e.target) && 
            !addButton.contains(e.target)) {
            hideAddNoteForm();
        }
    });
    </script>
</body>
</html>