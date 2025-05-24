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
                            <button class="btn btn-primary add-note-button">+ Add Notes</button>
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

    <script>
        document.addEventListener('DOMContentLoaded', function () {
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

            // Handle Add Note button click to show modal
            const addNoteButton = document.querySelector('.add-note-button');
            const addNoteModalElement = document.getElementById('addNoteModal');
            const addNoteModal = new bootstrap.Modal(addNoteModalElement);
            const noteContentTextarea = document.getElementById('noteContent');
            const saveNoteButton = document.getElementById('saveNoteButton');
            const stickyNotesContainer = document.getElementById('stickyNotesContainer');
            const successToastElement = document.getElementById('successToast');
            const successToast = new bootstrap.Toast(successToastElement);

            if (addNoteButton) {
                addNoteButton.addEventListener('click', function() {
                    addNoteModal.show();
                });
            }

            // Handle save note button click
            if (saveNoteButton) {
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

            // Handle modal close buttons (already handled by data-bs-dismiss but adding for clarity)
            const modalCloseButton = document.querySelector('#addNoteModal .btn-close');
            const modalCancelButton = document.querySelector('#addNoteModal .btn-secondary');

            if (modalCloseButton) {
                 modalCloseButton.addEventListener('click', function() {
                     addNoteModal.hide();
                 });
            }

             if (modalCancelButton) {
                 modalCancelButton.addEventListener('click', function() {
                      addNoteModal.hide();
                 });
             }

            // --- Sticky Notes Delete Functionality ---

            let noteToDeleteId = null; // Variable to store the ID of the note to be deleted

            // Get references to the delete modal and confirm button
            const deleteNoteModalElement = document.getElementById('deleteNoteModal');
            const deleteNoteModal = new bootstrap.Modal(deleteNoteModalElement);
            const confirmDeleteNoteButton = document.getElementById('confirmDeleteNoteButton');

            // Use event delegation on the container to handle clicks on dynamically added delete icons
            stickyNotesContainer.addEventListener('click', function(event) {
                // Check if the clicked element or its parent is the delete icon
                const deleteButton = event.target.closest('.delete-note');

                if (deleteButton) {
                    noteToDeleteId = deleteButton.getAttribute('data-note-id'); // Store the note ID
                    deleteNoteModal.show(); // Show the confirmation modal
                }
            });

            // Handle click on the confirmation button in the modal
            if (confirmDeleteNoteButton) {
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
            deleteNoteModalElement.addEventListener('hidden.bs.modal', function () {
                noteToDeleteId = null;
                 // Ensure the confirm button is reset if modal is closed without clicking confirm
                 confirmDeleteNoteButton.disabled = false;
                 confirmDeleteNoteButton.textContent = 'Yes, Delete It!';
            });

        }); // End of DOMContentLoaded
    </script>

<?php include '../includes/dashboard-footer.php'; ?>
</body>
</html> 

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