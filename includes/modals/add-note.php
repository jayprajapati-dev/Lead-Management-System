<?php
// Assuming user data (like who created the note) might be needed later
// For now, no specific PHP variables are required for this basic modal structure.
?>

<!-- Include Modal Styles -->
<link rel="stylesheet" href="../includes/modals/modal_styles.css">

<!-- Add Note Modal -->
<div class="modal fade" id="addNoteModal" tabindex="-1" aria-labelledby="addNoteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addNoteModalLabel">Add/Edit Note</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addNoteForm" method="POST">
                <div class="modal-body">
                    <!-- Hidden field for note ID (when editing) -->
                    <input type="hidden" id="noteId" name="id" value="">
                    <div class="mb-3">
                        <label for="noteContent" class="form-label">Note Content</label>
                        <textarea class="form-control" id="noteContent" name="content" rows="4" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Success/Error Message Toast -->
<div class="toast-container position-fixed top-50 start-50 translate-middle p-3" style="z-index: 9999;">
    <div id="noteToast" class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="2000">
        <div class="d-flex">
            <div class="toast-body" id="toastMessage">
                Note saved successfully!
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const addNoteForm = document.getElementById('addNoteForm');
    const noteToast = document.getElementById('noteToast');
    const toastMessage = document.getElementById('toastMessage');
    
    // Function to show toast with message
    function showToast(message, type = 'success') {
        toastMessage.textContent = message;
        
        // Set appropriate background color
        if (type === 'success') {
            noteToast.classList.remove('bg-danger');
            noteToast.classList.add('bg-success');
        } else {
            noteToast.classList.remove('bg-success');
            noteToast.classList.add('bg-danger');
        }
        
        // Show the toast
        const bsToast = bootstrap.Toast.getOrCreateInstance(noteToast);
        bsToast.show();
        
        console.log('Toast shown with message:', message);
    }

    addNoteForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const noteContent = document.getElementById('noteContent').value.trim();
        if (!noteContent) {
            showToast('Please enter note content', 'error');
            return;
        }
        
        const noteId = document.getElementById('noteId').value.trim();
        const formData = {
            content: noteContent
        };
        
        // Include note ID if it exists (for editing)
        if (noteId) {
            formData.id = noteId;
        }

        console.log('Submitting note data:', formData);
        
        // Get the base URL from the current location
        const baseUrl = window.location.origin;
        const ajaxUrl = `${baseUrl}/Lead-Management-System/dashboard/ajax/save-note.php`;
        console.log('Using AJAX URL:', ajaxUrl);
        
        // Use absolute path with origin to ensure it works from any page
        fetch(ajaxUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(formData)
        })
        // Log the raw response for debugging
        .then(response => {
            console.log('Response status:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('Response data:', data);
            
            if (data.status === 'success') {
                console.log('Note saved successfully with ID:', data.note_id);
                
                // Clear form
                addNoteForm.reset();
                document.getElementById('noteId').value = '';
                
                // Close modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('addNoteModal'));
                modal.hide();
                
                // Show success message
                showToast(noteId ? 'Note updated successfully!' : 'Note added successfully!');
                
                // Refresh the page to show new note, but wait longer for toast to be visible
                setTimeout(() => {
                    window.location.reload();
                }, 2000);
            } else {
                console.error('Error saving note:', data.message);
                // Show error message
                showToast(data.message || 'Error saving note', 'error');
            }
        })
        .catch(error => {
            console.error('Fetch error:', error);
            // Show a more detailed error message
            showToast('Error connecting to server: ' + error.message, 'error');
            
            // Log additional details for debugging
            console.log('Form data that failed:', formData);
            console.log('Current page URL:', window.location.href);
        });
    });
});</script>

<!-- Include Modal Enhancements Script -->
<script src="../includes/modals/modal_enhancements.js"></script>