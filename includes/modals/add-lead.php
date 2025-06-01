<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get user information from session
$loggedInUserId = $_SESSION['user_id'] ?? '';
$loggedInUserName = $_SESSION['user_first_name'] ?? 'Unknown User';
?>

<!-- Add Lead Modal -->
<div class="modal fade" id="addLeadModal" tabindex="-1" aria-labelledby="addLeadModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="addLeadModalLabel"><i class="fas fa-user-plus"></i> Add New Lead</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="addLeadForm" class="needs-validation" novalidate>
          <div class="row g-3">
            <!-- Status -->
            <div class="col-md-6">
              <label for="leadStatus" class="form-label">Status: *</label>
              <select class="form-select" id="leadStatus" name="status" required>
                <option value="New" selected data-badge-class="badge-status-new">New</option>
                <option value="Processing" data-badge-class="badge-status-processing">Processing</option>
                <option value="Close-by" data-badge-class="badge-status-feedback">Close-by</option>
                <option value="Confirm" data-badge-class="badge-status-confirm">Confirm</option>
                <option value="Cancel" data-badge-class="badge-status-cancel">Cancel</option>
              </select>
              <div class="invalid-feedback">Please select a status</div>
            </div>

            <!-- Source -->
            <div class="col-md-6">
              <label for="leadSource" class="form-label">Source: *</label>
              <select class="form-select" id="leadSource" name="source" required>
                <option value="Online" selected>Online</option>
                <option value="Website">Website</option>
                <option value="Whatsapp">Whatsapp</option>
                <option value="Facebook">Facebook</option>
                <option value="Offline">Offline</option>
              </select>
              <div class="invalid-feedback">Please select a source</div>
             </div>

            <!-- Customer Name -->
            <div class="col-md-6">
              <label for="customerName" class="form-label">Customer Name: *</label>
              <input type="text" class="form-control" id="customerName" name="customer_name" required>
              <div class="invalid-feedback">Please enter customer name</div>
            </div>

            <!-- Customer Mobile Number -->
            <div class="col-md-6">
              <label for="customerMobile" class="form-label">Mobile Number: *</label>
              <input type="tel" class="form-control" id="customerMobile" name="customer_mobile" required pattern="[0-9]{10}">
              <div class="invalid-feedback">Please enter a valid 10-digit mobile number</div>
            </div>

            <!-- Email -->
            <div class="col-md-6">
              <label for="customerEmail" class="form-label">Email:</label>
              <input type="email" class="form-control" id="customerEmail" name="email">
              <div class="invalid-feedback">Please enter a valid email address</div>
            </div>

            <!-- Company Name -->
            <div class="col-md-6">
              <label for="companyName" class="form-label">Company Name:</label>
              <input type="text" class="form-control" id="companyName" name="company_name">
            </div>

            <!-- Comment -->
            <div class="col-12">
              <label for="leadComment" class="form-label">Comment:</label>
              <textarea class="form-control" id="leadComment" name="comment" rows="2"></textarea>
            </div>

            <!-- Hidden Fields -->
            <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($loggedInUserId); ?>">
            <input type="hidden" name="date" value="<?php echo date('Y-m-d'); ?>">
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" id="saveLeadBtn" class="btn btn-primary">
          <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
          Save Lead
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Toast Container -->
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1070;">
  <!-- Success Toast -->
    <div id="leadSuccessToast" class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body">
      <i class="fas fa-check-circle me-2"></i>
                Lead added successfully with status: <span id="successLeadStatus" class="badge bg-light text-dark"></span>
    </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
  </div>
  
  <!-- Error Toast -->
    <div id="leadErrorToast" class="toast align-items-center text-white bg-danger border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body">
      <i class="fas fa-exclamation-circle me-2"></i>
                <span id="leadErrorToastMessage">An error occurred while adding the lead.</span>
    </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
  </div>
</div>

<style>
/* Toast Styles */
.toast-container {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 1070;
}

.toast {
    opacity: 0;
    transform: translateY(-100%);
    transition: all 0.3s ease-out;
    min-width: 300px;
}

.toast.show {
    opacity: 1;
    transform: translateY(0);
}

/* Badge Styles */
.badge {
    font-size: 0.875rem;
    padding: 0.35em 0.65em;
}

/* Animation Styles */
@keyframes badgeHighlight {
    0% { transform: scale(1); }
    50% { transform: scale(1.2); }
    100% { transform: scale(1); }
}

.badge-highlight {
    animation: badgeHighlight 0.5s ease-in-out;
}

.highlight-status {
    animation: statusHighlight 2s ease-in-out;
}

@keyframes statusHighlight {
    0% { box-shadow: 0 0 0 0 rgba(79, 70, 229, 0.4); }
    70% { box-shadow: 0 0 0 10px rgba(79, 70, 229, 0); }
    100% { box-shadow: 0 0 0 0 rgba(79, 70, 229, 0); }
}
</style>

<!-- Include Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- Include Modal Enhancements Script -->
<script src="../includes/modals/modal_enhancements.js"></script>
