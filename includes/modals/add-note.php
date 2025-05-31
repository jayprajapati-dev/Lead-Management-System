<?php
// Assuming user data (like who created the note) might be needed later
// For now, no specific PHP variables are required for this basic modal structure.
?>

<!-- Include Modal Styles -->
<link rel="stylesheet" href="../includes/modals/modal_styles.css">

<!-- Add Note Modal -->
<div class="modal fade" id="addNoteModal" tabindex="-1" aria-labelledby="addNoteModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addNoteModalLabel"><i class="fas fa-sticky-note"></i> Add Note</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="addNoteForm">
          <div class="mb-3">
            <label for="noteContent" class="form-label">Notes: *</label>
            <textarea class="form-control" id="noteContent" name="content" rows="5" placeholder="Enter your notes here..." required></textarea>
          </div>

          <!-- You might add fields here later to link to a lead, task, etc. -->

        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" form="addNoteForm" class="btn btn-primary">Save Note</button>
      </div>
    </div>
  </div>
</div>

<!-- Include Modal Enhancements Script -->
<script src="../includes/modals/modal_enhancements.js"></script>