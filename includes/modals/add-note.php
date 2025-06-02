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
                <h5 class="modal-title" id="addNoteModalLabel">Add Note</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addNoteForm" method="POST" action="ajax/save-note.php">
                <div class="modal-body">
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

<!-- Success Message Toast -->
<div class="toast-container position-fixed top-0 end-0 p-3">
    <div id="successToast" class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body">
                Note added successfully!
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const addNoteForm = document.getElementById('addNoteForm');
    const successToast = new bootstrap.Toast(document.getElementById('successToast'));

    addNoteForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = {
            content: document.getElementById('noteContent').value.trim()
        };

        fetch('ajax/save-note.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(formData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                // Clear form
                addNoteForm.reset();
                
                // Close modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('addNoteModal'));
                modal.hide();
                
                // Show success message
                successToast.show();
                
                // Refresh the page to show new note
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            }
        })
        .catch(error => console.error('Error:', error));
    });
});</script>

<!-- Include Modal Enhancements Script -->
<script src="../includes/modals/modal_enhancements.js"></script>