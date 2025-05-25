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
            <!-- Mobile Toggle Button -->
            <button class="sidebar-toggle d-md-none" id="sidebarToggle">
                <i class="fas fa-bars"></i>
            </button>

            <!-- Mobile Overlay -->
            <div class="sidebar-overlay" id="sidebarOverlay"></div>

            <div class="col-md-3 col-lg-2 sidebar" id="sidebarMenu">
<?php include '../includes/sidebar.php'; ?>
            </div>
            <div class="col-md-9 col-lg-10 main-content-area">
<?php include '../includes/dashboard-header.php'; ?>
                <div class="dashboard-body">
                    <div class="row">
                        <!-- Today's Leads Card -->
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card dashboard-card">
                                <div class="card-header">
                            <h4>Today's Leads</h4>
                                </div>
                                <div class="card-body">
                                    <ul class="nav nav-tabs card-tabs" role="tablist">
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link active" id="leads-new-tab" data-bs-toggle="tab" data-bs-target="#leads-new" type="button" role="tab" aria-controls="leads-new" aria-selected="true">New <span class="badge rounded-pill">0</span></button>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link" id="leads-processing-tab" data-bs-toggle="tab" data-bs-target="#leads-processing" type="button" role="tab" aria-controls="leads-processing" aria-selected="false">Processing <span class="badge rounded-pill">0</span></button>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link" id="leads-close-by-tab" data-bs-toggle="tab" data-bs-target="#leads-close-by" type="button" role="tab" aria-controls="leads-close-by" aria-selected="false">Close-By <span class="badge rounded-pill">0</span></button>
                                        </li>
                                    </ul>
                                    <div class="tab-content mt-3">
                                        <div class="tab-pane fade show active" id="leads-new" role="tabpanel" aria-labelledby="leads-new-tab">
                            <p>No Leads For Today</p>
                                        </div>
                                        <div class="tab-pane fade" id="leads-processing" role="tabpanel" aria-labelledby="leads-processing-tab">
                                            <p>No Processing Leads Today</p>
                                        </div>
                                        <div class="tab-pane fade" id="leads-close-by" role="tabpanel" aria-labelledby="leads-close-by-tab">
                                            <p>No Close-By Leads Today</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Today's Tasks Card -->
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card dashboard-card">
                                <div class="card-header">
                            <h4>Today's Tasks</h4>
                                </div>
                                <div class="card-body">
                                    <ul class="nav nav-tabs card-tabs" role="tablist">
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link active" id="tasks-today-tab" data-bs-toggle="tab" data-bs-target="#tasks-today" type="button" role="tab" aria-controls="tasks-today" aria-selected="true">Today <span class="badge rounded-pill">0</span></button>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link" id="tasks-tomorrow-tab" data-bs-toggle="tab" data-bs-target="#tasks-tomorrow" type="button" role="tab" aria-controls="tasks-tomorrow" aria-selected="false">Tomorrow <span class="badge rounded-pill">0</span></button>
                                        </li>
                                    </ul>
                                    <div class="tab-content mt-3">
                                        <div class="tab-pane fade show active" id="tasks-today" role="tabpanel" aria-labelledby="tasks-today-tab">
                                            <p><span class="status-dot red"></span> No Task For Today</p>
                                        </div>
                                        <div class="tab-pane fade" id="tasks-tomorrow" role="tabpanel" aria-labelledby="tasks-tomorrow-tab">
                                             <p>No Task For Tomorrow</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Today's Reminders Card -->
                         <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card dashboard-card">
                                <div class="card-header">
                            <h4>Today's Reminders</h4>
                                </div>
                                <div class="card-body">
                                    <ul class="nav nav-tabs card-tabs" role="tablist">
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link active" id="reminders-reminders-tab" data-bs-toggle="tab" data-bs-target="#reminders-reminders" type="button" role="tab" aria-controls="reminders-reminders" aria-selected="true">Reminders <span class="badge rounded-pill">0</span></button>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <button class="nav-link" id="reminders-events-tab" data-bs-toggle="tab" data-bs-target="#reminders-events" type="button" role="tab" aria-controls="reminders-events" aria-selected="false">Events <span class="badge rounded-pill">0</span></button>
                                        </li>
                                    </ul>
                                    <div class="tab-content mt-3">
                                        <div class="tab-pane fade show active" id="reminders-reminders" role="tabpanel" aria-labelledby="reminders-reminders-tab">
                                            <p><span class="status-dot red"></span> No Reminders For Today</p>
                                        </div>
                                        <div class="tab-pane fade" id="reminders-events" role="tabpanel" aria-labelledby="reminders-events-tab">
                                             <p>No Events For Today</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Sticky Notes Section -->
                    <div class="sticky-notes-section card dashboard-card mt-4">
                        <div class="card-header">
                        <h4>Sticky Notes</h4>
                        </div>
                         <div class="card-body text-center">
                            <button class="quick-action-button" data-bs-toggle="modal" data-bs-target="#addNoteModal">
                                + <span class="button-text">Notes</span> <i class="fas fa-check-square"></i>
                            </button>
                            <!-- Container for displaying sticky notes -->
                            <div id="stickyNotesContainer" class="row mt-3">
                                <?php
                                // Fetch existing notes for the logged-in user
                                if (isLoggedIn()) {
                                    $userId = $_SESSION['user_id'];
                                    try {
                                        $notesStmt = executeQuery("SELECT id, content FROM sticky_notes WHERE user_id = ? ORDER BY created_at DESC", [$userId]);
                                        $notesResult = $notesStmt->get_result();
                                        if ($notesResult->num_rows > 0) {
                                            while ($note = $notesResult->fetch_assoc()) {
                                                // Output HTML for each note (will style with CSS later)
                                                echo '<div class="col-md-4 mb-3"><div class="sticky-note" data-note-id="' . htmlspecialchars($note['id']) . '"><span class="note-pin"></span><span class="delete-note" data-note-id="' . htmlspecialchars($note['id']) . '">&times;</span><div class="note-content">' . htmlspecialchars($note['content']) . '</div></div></div>';
                                            }
                                        } else {
                                            echo '<p class="text-muted mt-2 no-notes-message">There are no records to display.</p>';
                                        }
                                        $notesStmt->close();
                                    } catch (Exception $e) {
                                        error_log("Error fetching sticky notes for user " . $userId . ": " . $e->getMessage());
                                        echo '<p class="text-danger mt-2">Error loading notes.</p>';
                                    }
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-4">
                        <!-- Lead Status Analytics Card -->
                        <div class="col-md-6 mb-4">
                            <div class="card analytics-card">
                                <div class="card-header">
                            <h4>Lead Status</h4>
                                     <i class="fas fa-bars filter-icon" data-bs-toggle="modal" data-bs-target="#analyticsFilterModal"></i> <!-- Menu icon with data attributes for modal -->
                                </div>
                                <div class="card-body">
                                    <div class="analytics-info mb-3">
                                         <p class="date-range"></p>
                                         <span class="badge bg-primary staff-badge"></span>
                                    </div>
                                    <div class="chart-container" style="display: none;">
                                        <canvas id="leadStatusChart"></canvas>
                                        <!-- Center text and percentage will be added via Chart.js plugin or custom JS -->
                                    </div>
                                    <div class="chart-legend lead-status-legend text-center mt-3" style="display: none;">
                                        <!-- Legend will be generated by Chart.js or rendered statically -->
                                    </div>
                                    <!-- No Data Message -->
                                    <div class="no-data-message text-center">
                                        <p class="no-data-text">No Lead Found</p>
                                        <p class="no-data-count">0</p>
                                        <!-- Legend will be shown below this by JS -->
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Lead Source Analytics Card -->
                        <div class="col-md-6 mb-4">
                             <div class="card analytics-card">
                                <div class="card-header">
                            <h4>Lead Source</h4>
                                     <i class="fas fa-bars filter-icon" data-bs-toggle="modal" data-bs-target="#analyticsFilterModal"></i> <!-- Menu icon with data attributes for modal -->
                                </div>
                                <div class="card-body">
                                     <div class="analytics-info mb-3">
                                         <p class="date-range"></p>
                                         <span class="badge bg-primary staff-badge"></span>
                                     </div>
                                     <div class="chart-container" style="display: none;">
                                        <canvas id="leadSourceChart"></canvas>
                                        <!-- Center text and percentage will be added via Chart.js plugin or custom JS -->
                                     </div>
                                    <div class="chart-legend lead-source-legend text-center mt-3" style="display: none;">
                                         <!-- Legend will be generated by Chart.js or rendered statically -->
                                    </div>
                                    <!-- No Data Message -->
                                    <div class="no-data-message text-center">
                                        <p class="no-data-text">No Lead Found</p>
                                        <p class="no-data-count">0</p>
                                        <!-- Legend will be shown below this by JS -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- End of Analytics Section -->
                </div>
            </div>
        </div>
    </div>
    <!-- Bootstrap JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
    <!-- Chart.js for charts -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Chart.js Plugin for center text -->
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Initialize all modals
            const addNoteModalElement = document.getElementById('addNoteModal');
            const addNoteModal = addNoteModalElement ? new bootstrap.Modal(addNoteModalElement) : null;
            
            // Clear note content when modal is shown
            if (addNoteModalElement) {
                addNoteModalElement.addEventListener('show.bs.modal', function () {
                    const noteContentTextarea = document.getElementById('noteContent');
                    if (noteContentTextarea) {
                        noteContentTextarea.value = '';
                    }
                });
            }

            // Get all tab buttons
            const tabButtons = document.querySelectorAll('button[data-bs-toggle="tab"]');

            tabButtons.forEach(button => {
                button.addEventListener('click', function () {
                    // Deactivate all sibling tab buttons in the same card
                    const parentNav = this.closest('.nav-tabs');
                    parentNav.querySelectorAll('.nav-link').forEach(tab => {
                        tab.classList.remove('active');
                        const targetId = tab.getAttribute('data-bs-target').substring(1);
                        document.getElementById(targetId).classList.remove('show', 'active');
                    });

                    // Activate the clicked tab button
                    this.classList.add('active');
                    const targetId = this.getAttribute('data-bs-target').substring(1);
                    document.getElementById(targetId).classList.add('show', 'active');
                });
            });

            // Handle save note button click
            const saveNoteButton = document.getElementById('saveNoteButton');
            const noteContentTextarea = document.getElementById('noteContent');
            const stickyNotesContainer = document.getElementById('stickyNotesContainer');
            const successToastElement = document.getElementById('successToast');
            const successToast = successToastElement ? new bootstrap.Toast(successToastElement) : null;

            if (saveNoteButton && addNoteModal && successToast && stickyNotesContainer) {
                saveNoteButton.addEventListener('click', function() {
                    const noteContent = noteContentTextarea.value.trim();

                    if (noteContent === '') {
                        alert('Please enter note content.');
                        return;
                    }

                    // Disable button while saving
                    saveNoteButton.disabled = true;
                    saveNoteButton.textContent = 'Saving...';

                    // Send data via AJAX
                    fetch('save_note.php', {  // Note: using relative path since we're in dashboard folder
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'content=' + encodeURIComponent(noteContent)
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            // Show success message
                            successToast.show();

                            // Clear textarea and hide modal
                            noteContentTextarea.value = '';
                            addNoteModal.hide();

                            // Remove "no notes" message if it exists
                            const noNotesMessage = stickyNotesContainer.querySelector('.no-notes-message');
                            if (noNotesMessage) {
                                noNotesMessage.remove();
                            }

                            // Add the new note to the container
                            const newNoteHtml = `
                                <div class="col-md-4 mb-3">
                                    <div class="sticky-note" data-note-id="${data.note_id}">
                                        <span class="note-pin"></span>
                                        <span class="delete-note" data-note-id="${data.note_id}">&times;</span>
                                        <div class="note-content">${noteContent.replace(/</g, '&lt;').replace(/>/g, '&gt;')}</div>
                                    </div>
                                </div>`;
                            stickyNotesContainer.insertAdjacentHTML('afterbegin', newNoteHtml);
                        } else {
                            throw new Error(data.message || 'Failed to save note');
                        }
                    })
                    .catch(error => {
                        console.error('Error saving note:', error);
                        alert(error.message || 'An error occurred. Please try again later.');
                    })
                    .finally(() => {
                        // Re-enable button
                        saveNoteButton.disabled = false;
                        saveNoteButton.textContent = 'Submit';
                    });
                });
            }

            // --- Sticky Notes Delete Functionality ---

            let noteToDeleteId = null; // Variable to store the ID of the note to be deleted

            // Get references to the delete modal and confirm button
            const deleteNoteModalElement = document.getElementById('deleteNoteModal');
             // Check if modal element exists before creating modal instance
            const deleteNoteModal = deleteNoteModalElement ? new bootstrap.Modal(deleteNoteModalElement) : null;
            const confirmDeleteNoteButton = document.getElementById('confirmDeleteNoteButton');

            // Use event delegation on the container to handle clicks on dynamically added delete icons
             if(stickyNotesContainer && deleteNoteModal) { // Ensure container and modal instance exist
                 stickyNotesContainer.addEventListener('click', function(event) {
                    // Check if the clicked element or its parent is the delete icon
                    const deleteButton = event.target.closest('.delete-note');

                    if (deleteButton) {
                        noteToDeleteId = deleteButton.getAttribute('data-note-id'); // Store the note ID
                        deleteNoteModal.show(); // Show the confirmation modal
                    }
                 });
            }


            // Handle click on the confirmation button in the modal
            if (confirmDeleteNoteButton && deleteNoteModal && stickyNotesContainer) { // Ensure button, modal instance, and container exist
                confirmDeleteNoteButton.addEventListener('click', function() {
                    if (noteToDeleteId) {
                        // Disable button while deleting
                        confirmDeleteNoteButton.disabled = true;
                        confirmDeleteNoteButton.textContent = 'Deleting...';

                        // Send AJAX request to delete the note
                        fetch('delete_note.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: 'note_id=' + encodeURIComponent(noteToDeleteId)
                        })
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Network response was not ok');
                            }
                            return response.json();
                        })
                        .then(data => {
                            if (data.success) {
                                // Remove the note from the DOM
                                const noteElement = stickyNotesContainer.querySelector(`.sticky-note[data-note-id="${noteToDeleteId}"]`);
                                if (noteElement) {
                                    noteElement.closest('.col-md-4').remove(); // Remove the column wrapping the note
                                }
                                // Show success message (optional, could use a toast)
                                // console.log(data.message);

                                 // Check if there are any notes left, if not, show the no notes message
                                if (stickyNotesContainer.querySelectorAll('.sticky-note').length === 0) {
                                    const noNotesMessage = document.createElement('p');
                                    noNotesMessage.className = 'text-muted mt-2 no-notes-message';
                                    noNotesMessage.textContent = 'There are no records to display.';
                                    stickyNotesContainer.appendChild(noNotesMessage);
                                }

                            } else {
                                 throw new Error(data.message || 'Failed to delete note');
                            }
                        })
                        .catch(error => {
                            console.error('Error deleting note:', error);
                            alert(error.message || 'An error occurred during deletion.');
                        })
                        .finally(() => {
                            // Re-enable button and hide modal
                            confirmDeleteNoteButton.disabled = false;
                            confirmDeleteNoteButton.textContent = 'Yes, Delete It!';
                            deleteNoteModal.hide();
                            noteToDeleteId = null; // Reset the stored ID
                        });
                    }
                });
            }

            // Reset noteToDeleteId when modal is closed (e.g., by clicking cancel or outside)
             if(deleteNoteModalElement) {
                 deleteNoteModalElement.addEventListener('hidden.bs.modal', function () {
                     noteToDeleteId = null;
                     // Ensure the confirm button is reset if modal is closed without clicking confirm
                     if(confirmDeleteNoteButton) {
                          confirmDeleteNoteButton.disabled = false;
                          confirmDeleteNoteButton.textContent = 'Yes, Delete It!';
                     }
                 });
             }


        }); // End of DOMContentLoaded

        // --- Analytics Dashboard Functionality ---

        const leadStatusChartCanvas = document.getElementById('leadStatusChart');
        const leadSourceChartCanvas = document.getElementById('leadSourceChart');
        const leadStatusLegendContainer = document.querySelector('.lead-status-legend');
        const leadSourceLegendContainer = document.querySelector('.lead-source-legend');
        const leadStatusNoDataMessage = leadStatusChartCanvas ? leadStatusChartCanvas.closest('.card-body').querySelector('.no-data-message') : null; // Check if canvas exists
        const leadSourceNoDataMessage = leadSourceChartCanvas ? leadSourceChartCanvas.closest('.card-body').querySelector('.no-data-message') : null; // Check if canvas exists

        const dateRangeElements = document.querySelectorAll('.analytics-info .date-range');
        const staffBadgeElements = document.querySelectorAll('.analytics-info .staff-badge');
        const filterUserSelect = document.getElementById('filterUser');
        const applyAnalyticsFilterButton = document.getElementById('applyAnalyticsFilterButton');
        const analyticsFilterModalElement = document.getElementById('analyticsFilterModal');
        // Check if modal element exists before creating modal instance
        const analyticsFilterModal = analyticsFilterModalElement ? new bootstrap.Modal(analyticsFilterModalElement) : null;
        const startDateInput = document.getElementById('startDate');
        const endDateInput = document.getElementById('endDate');

        let leadStatusChart = null;
        let leadSourceChart = null;

        // Embed PHP variable containing users list into JavaScript
        const usersForDropdown = <?php echo json_encode($usersList); ?>;

        // Function to populate the User filter dropdown and the Lead modal user dropdown
        function populateUserDropdowns(users) {
             // Populate Analytics Filter User Dropdown (already exists)
             const filterUserSelect = document.getElementById('filterUser');
             if (filterUserSelect && users) {
                  // Clear existing options except the default 'Select User'
                  filterUserSelect.innerHTML = '<option value="">Select User</option>';
                  users.forEach(user => {
                      const option = document.createElement('option');
                      option.value = user.id;
                      option.textContent = user.name; // This populates with "First Name Last Name"
                      filterUserSelect.appendChild(option);
                  });
             }

             // Populate Add Lead Modal User Dropdown
             const leadUserSelect = document.getElementById('leadUser');
             if (leadUserSelect && users) {
                  // Clear existing options except the default 'Select User'
                  leadUserSelect.innerHTML = '<option value="">Select User</option>';
                  users.forEach(user => {
                      const option = document.createElement('option');
                      option.value = user.id;
                      option.textContent = user.name; // This populates with "First Name Last Name"
                      leadUserSelect.appendChild(option);
                  });
             }
        }

        // Call the function to populate dropdowns after the DOM is loaded and users are fetched
        // Assuming the usersList PHP variable is available in the global scope after the PHP block
        if (typeof usersForDropdown !== 'undefined') {
            populateUserDropdowns(usersForDropdown);
        }


        // Function to fetch and render analytics data
        async function fetchAndRenderAnalytics(startDate = null, endDate = null, userId = null) {
             // Show loading state (optional)
             dateRangeElements.forEach(el => el.textContent = ''); // Clear content while loading
             staffBadgeElements.forEach(el => el.textContent = ''); // Clear content while loading

            const formData = new FormData();
            if (startDate) formData.append('startDate', startDate);
            if (endDate) formData.append('endDate', endDate);
            if (userId) formData.append('userId', userId);

            try {
                const response = await fetch('fetch_analytics_data.php', {
                    method: 'POST',
                    body: formData
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();

                // Calculate and display current month's date range
                const today = new Date();
                const firstDayOfCurrentMonth = new Date(today.getFullYear(), today.getMonth(), 1);
                const formattedToday = today.toLocaleDateString('en-GB', { day: '2-digit', month: '2-digit', year: 'numeric' }); // DD/MM/YYYY format
                const formattedFirstDay = firstDayOfCurrentMonth.toLocaleDateString('en-GB', { day: '2-digit', month: '2-digit', year: 'numeric' }); // DD/MM/YYYY format
                const displayDateRange = `FROM ${formattedFirstDay} TO ${formattedToday}`;

                // Update date range and staff name
                const selectedUserOption = filterUserSelect.options[filterUserSelect.selectedIndex];
                const displayStaffName = selectedUserOption.text === 'Select User' ? 'All Staff' : selectedUserOption.text;

                // Always update date range and staff badge regardless of data
                dateRangeElements.forEach(el => el.textContent = displayDateRange);
                staffBadgeElements.forEach(el => el.textContent = displayStaffName);

                // Handle Lead Status Analytics
                if (leadStatusChartCanvas && leadStatusNoDataMessage && leadStatusLegendContainer) { // Ensure elements exist
                    if (data.leadStatus && data.leadStatus.total > 0) {
                        // Data exists - show chart and hide no data message
                        renderChart(leadStatusChartCanvas, data.leadStatus, 'Lead Status', leadStatusChart, leadStatusLegendContainer);
                        leadStatusNoDataMessage.style.display = 'none';
                        leadStatusChartCanvas.closest('.chart-container').style.display = 'block';
                    } else {
                        // No data - hide chart and show no data message
                        destroyChart(leadStatusChart);
                        leadStatusNoDataMessage.style.display = 'flex';
                        leadStatusChartCanvas.closest('.chart-container').style.display = 'none';
                    }
                     // Always render legends regardless of data availability for Lead Status
                     renderStaticLegend(leadStatusLegendContainer,
                         ['New', 'Processing', 'Close-by', 'Confirm', 'Cancel'],
                         ['#0d6efd', '#198754', '#ffc107', '#dc3545', '#6f42c1']
                     );
                }


                // Handle Lead Source Analytics
                 if (leadSourceChartCanvas && leadSourceNoDataMessage && leadSourceLegendContainer) { // Ensure elements exist
                     if (data.leadSource && data.leadSource.total > 0) {
                         // Data exists - show chart and hide no data message
                         renderChart(leadSourceChartCanvas, data.leadSource, 'Lead Source', leadSourceChart, leadSourceLegendContainer);
                         leadSourceNoDataMessage.style.display = 'none';
                         leadSourceChartCanvas.closest('.chart-container').style.display = 'block';
                     } else {
                         // No data - hide chart and show no data message
                         destroyChart(leadSourceChart);
                         leadSourceNoDataMessage.style.display = 'flex';
                         leadSourceChartCanvas.closest('.chart-container').style.display = 'none';
                     }
                      // Always render legends regardless of data availability for Lead Source
                     renderStaticLegend(leadSourceLegendContainer,
                         ['Online', 'Offline', 'Website', 'Whatsapp', 'Customer Reminder', 'Indiamart', 'Facebook', 'Google Form'],
                         ['#0d6efd', '#198754', '#ffc107', '#dc3545', '#6f42c1', '#0dcaf0', '#6610f2', '#fd7e14']
                     );
                 }


            } catch (error) {
                console.error('Error fetching analytics data:', error);
                // Display error message on cards
                dateRangeElements.forEach(el => el.textContent = 'Error loading data');
                staffBadgeElements.forEach(el => el.textContent = '');

                // Hide charts and show no data message in case of error
                if (leadStatusNoDataMessage && leadStatusChartCanvas && leadSourceNoDataMessage && leadSourceChartCanvas) {
                    leadStatusNoDataMessage.style.display = 'flex';
                    leadStatusChartCanvas.closest('.chart-container').style.display = 'none';
                    leadSourceNoDataMessage.style.display = 'flex';
                    leadSourceChartCanvas.closest('.chart-container').style.display = 'none';
                }


                // Still show legends even in error state
                 if (leadStatusLegendContainer) {
                     renderStaticLegend(leadStatusLegendContainer,
                         ['New', 'Processing', 'Close-by', 'Confirm', 'Cancel'],
                         ['#0d6efd', '#198754', '#ffc107', '#dc3545', '#6f42c1']
                     );
                 }
                 if (leadSourceLegendContainer) {
                     renderStaticLegend(leadSourceLegendContainer,
                         ['Online', 'Offline', 'Website', 'Whatsapp', 'Customer Reminder', 'Indiamart', 'Facebook', 'Google Form'],
                         ['#0d6efd', '#198754', '#ffc107', '#dc3545', '#6f42c1', '#0dcaf0', '#6610f2', '#fd7e14']
                     );
                 }
            }
        }

        // Function to render Chart.js donut chart (used when data is available)
        function renderChart(canvas, chartData, centerText, chartInstance, legendContainer) {
             // Destroy existing chart if it exists
             if (chartInstance) {
                 chartInstance.destroy();
             }

            const ctx = canvas.getContext('2d');

             // Register datalabels plugin globally if not already
             if (typeof ChartDataLabels === 'undefined') {
                  console.error('Chartjs-plugin-datalabels not loaded.');
             } else {
                 if (!Chart.isPluginRegistered(ChartDataLabels)) {
                      Chart.register(ChartDataLabels);
                 }
             }


            chartInstance = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: chartData.labels,
                    datasets: [{
                        data: chartData.data,
                        backgroundColor: chartData.backgroundColors,
                        borderColor: '#ffffff',
                        borderWidth: 2
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '70%', // Adjust for donut thickness
                    plugins: {
                         legend: {
                             display: false, // Hide default legend
                         },
                         tooltip: {
                             enabled: true,
                             callbacks: {
                                 label: function(context) {
                                     const label = context.label || '';
                                     const value = context.raw;
                                     const total = context.chart.data.datasets[0].data.reduce((sum, current) => sum + current, 0);
                                     const percentage = total > 0 ? ((value / total) * 100).toFixed(1) + '%' : '0.0%';
                                     return `${label}: ${value} (${percentage})`;
                                 }
                             }
                         },
                        datalabels: {
                            display: true, // Show datalabels
                            formatter: (value, context) => {
                                 return ''; // Hide labels on segments for this design
                            },
                            color: '#ffffff', // Label color
                            textAlign: 'center',
                            font: {
                                 weight: 'bold'
                            }
                        }
                    },
                 plugins: [{
                     id: 'centerText',
                     afterDraw: function(chart) {
                         const ctx = chart.ctx;
                         const width = chart.width;
                         const height = chart.height;

                         ctx.restore();
                         const fontSize = (height / 114).toFixed(2);
                         ctx.font = `bold ${fontSize}em sans-serif`;
                         ctx.textBaseline = 'middle';

                         const total = chartData.total;
                         // Assuming first segment data for percentage display - might need adjustment based on how you want this
                         const percentage = total > 0 && chartData.data.length > 0 ? ((chartData.data[0] / total) * 100).toFixed(0) + '%' : '0%';

                         const text = centerText;
                         const count = total;
                         const percentText = percentage;

                         // Measure text widths after setting font
                         const textWidth = ctx.measureText(text).width;
                         const countWidth = ctx.measureText(count).width;
                         const percentTextWidth = ctx.measureText(percentText).width;


                         const textX = Math.round((width - textWidth) / 2);
                         const textY = height / 2 - 20; // Adjust position

                         const countX = Math.round((width - countWidth) / 2);
                         const countY = height / 2 + 5; // Adjust position

                          const percentTextX = Math.round((width - percentTextWidth) / 2);
                          const percentTextY = height / 2 + 30; // Adjust position

                         ctx.fillStyle = '#333'; // Color for the text
                         ctx.fillText(text, textX, textY);

                          ctx.font = `${fontSize * 1.5}em sans-serif`; // Larger font for count
                          ctx.fillStyle = '#555';
                         ctx.fillText(count, countX, countY);

                          ctx.font = `${fontSize * 0.8}em sans-serif`; // Smaller font for percentage
                          ctx.fillStyle = '#555';
                         ctx.fillText(percentText, percentTextX, percentTextY);

                         ctx.save();
                     }
                 }],
            });

            return chartInstance; // Return the chart instance
        }

         // Function to destroy a chart instance
         function destroyChart(chartInstance) {
             if (chartInstance) {
                 chartInstance.destroy();
                 chartInstance = null;
             }
         }

        // Function to render a static legend with specified labels and colors
        function renderStaticLegend(legendContainer, labels, colors) {
            if (!legendContainer) return; // Ensure container exists

            legendContainer.innerHTML = ''; // Clear previous legend
            if (!labels || labels.length === 0) return; // Don't render if no labels

            const ul = document.createElement('ul');
            ul.className = 'legend-list'; // Add a class for styling if needed

            labels.forEach((label, index) => {
                const li = document.createElement('li');
                const color = colors && colors[index] ? colors[index] : '#ccc'; // Use provided color or grey default
                li.innerHTML = `
                    <span class="legend-color" style="background-color: ${color}"></span>
                    <span class="legend-label">${label}</span>
                `;
                ul.appendChild(li);
            });

            legendContainer.appendChild(ul);
            legendContainer.style.display = 'block'; // Always show the legend
        }

        // Event listener for Apply Filter button
        if (applyAnalyticsFilterButton && analyticsFilterModal) { // Ensure button and modal instance exist
            applyAnalyticsFilterButton.addEventListener('click', function() {
                const startDate = startDateInput.value;
                const endDate = endDateInput.value;
                const userId = filterUserSelect.value;

                fetchAndRenderAnalytics(startDate, endDate, userId);
                analyticsFilterModal.hide(); // Hide modal after applying filter
            });
        }

         // Initial data fetch on page load
        fetchAndRenderAnalytics();

         // Initialize the Add Lead Modal (in case data-bs-toggle doesn\'t work directly)
         const addLeadModalElement = document.getElementById('addLeadModal');
         if (addLeadModalElement) {
             const addLeadModal = new bootstrap.Modal(addLeadModalElement);
             // Note: You can use data-bs-toggle="modal" and data-bs-target="#addLeadModal"
             // directly on your button element to trigger the modal without extra JS.
             // This explicit initialization is here as a fallback or if you need to
             // open the modal programmatically later.
         }


    </script>

<?php include '../includes/dashboard-footer.php'; ?>

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

</body>
</html> 