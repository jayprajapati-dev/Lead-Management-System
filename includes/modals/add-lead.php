<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get user information from session
$loggedInUserId = $_SESSION['user_id'] ?? '';
$loggedInUserName = $_SESSION['user_first_name'] ?? 'Unknown User';

// Get users list for the dropdown
$usersList = [];
try {
    $usersResult = executeQuery("SELECT id, first_name, last_name FROM users WHERE status = 'active' ORDER BY first_name")->get_result();
    if ($usersResult) {
        while ($user = $usersResult->fetch_assoc()) {
            $usersList[] = [
                'id' => $user['id'],
                'name' => htmlspecialchars($user['first_name'] . ' ' . $user['last_name'])
            ];
        }
        $usersResult->free();
    }
} catch (Exception $e) {
    error_log("Error fetching users for dropdown: " . $e->getMessage());
    $usersList = [];
}
?>

<!-- Add Lead Modal -->
<div class="modal fade" id="addLeadModal" tabindex="-1" aria-labelledby="addLeadModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="addLeadModalLabel">
                    <i class="fas fa-user-plus me-2"></i>Add New Lead
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addLeadForm" class="needs-validation" novalidate>
                    <!-- Status and Source Section -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="status" class="form-label fw-bold required-field">
                                    <i class="fas fa-tasks me-2"></i>Status
                                </label>
                                <select class="form-select form-select-lg" id="status" name="status" required>
                                    <option value="" disabled>Select Status</option>
                                    <option value="New" selected>New</option>
                                    <option value="Processing">Processing</option>
                                    <option value="Close-by">Close-by</option>
                                    <option value="Confirm">Confirm</option>
                                    <option value="Cancel">Cancel</option>
                                </select>
                                <div class="invalid-feedback">Please select a status</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="source" class="form-label fw-bold required-field">
                                    <i class="fas fa-filter me-2"></i>Source
                                </label>
                                <select class="form-select form-select-lg" id="source" name="source" required>
                                    <option value="" disabled>Select Source</option>
                                    <option value="Online">Online</option>
                                    <option value="Customer Reminder">Customer Reminder</option>
                                    <option value="Facebook">Facebook</option>
                                    <option value="Google Form">Google Form</option>
                                    <option value="Indiamart">Indiamart</option>
                                    <option value="Offline">Offline</option>
                                    <option value="Website">Website</option>
                                    <option value="WhatsApp">WhatsApp</option>
                                </select>
                                <div class="invalid-feedback">Please select a source</div>
                            </div>
                        </div>
                    </div>

                    <!-- Assignment and Contact Section -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="assigned_to" class="form-label fw-bold required-field">
                                    <i class="fas fa-user-check me-2"></i>Assigned To
                                </label>
                                <select class="form-select form-select-lg" id="assigned_to" name="user_id" required>
                                    <option value="" disabled>Select User</option>
                                    <?php foreach ($usersList as $user): ?>
                                        <option value="<?php echo htmlspecialchars($user['id']); ?>"
                                            <?php echo ($_SESSION['user_id'] == $user['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($user['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="invalid-feedback">Please select a user</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="customer_mobile" class="form-label fw-bold required-field">
                                    <i class="fas fa-phone me-2"></i>Customer Mobile Number
                                </label>
                                <div class="input-group input-group-lg">
                                    <button class="btn btn-outline-secondary dropdown-toggle country-flag-dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <span class="flag-icon flag-icon-in"></span> +91
                                    </button>
                                    <ul class="dropdown-menu country-code-dropdown">
                                        <!-- Country codes will be populated via JavaScript -->
                                    </ul>
                                    <input type="tel" class="form-control form-control-lg" id="customer_mobile" 
                                           name="customer_mobile" required pattern="[0-9]{10}" maxlength="10" 
                                           placeholder="Enter 10-digit mobile number">
                                    <div class="invalid-feedback">Please enter a valid 10-digit mobile number</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Company and Date Section -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="company_name" class="form-label fw-bold">
                                    <i class="fas fa-building me-2"></i>Company Name
                                </label>
                                <input type="text" class="form-control form-control-lg" id="company_name" 
                                       name="company_name" placeholder="Enter Company Name">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="lead_date" class="form-label fw-bold">
                                    <i class="fas fa-calendar me-2"></i>Date
                                </label>
                                <input type="date" class="form-control form-control-lg" id="lead_date" 
                                       name="date" value="<?php echo date('Y-m-d'); ?>" readonly>
                            </div>
                        </div>
                    </div>

                    <!-- Customer Details Section -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="customer_name" class="form-label fw-bold required-field">
                                    <i class="fas fa-user me-2"></i>Customer Name
                                </label>
                                <input type="text" class="form-control form-control-lg" id="customer_name" 
                                       name="customer_name" required placeholder="Enter Customer Name">
                                <div class="invalid-feedback">Please enter customer name</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="email" class="form-label fw-bold">
                                    <i class="fas fa-envelope me-2"></i>Email
                                </label>
                                <input type="email" class="form-control form-control-lg" id="email" 
                                       name="email" placeholder="Enter Customer Email">
                                <div class="invalid-feedback">Please enter a valid email address</div>
                            </div>
                        </div>
                    </div>

                    <!-- Label and Reference Section -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="label" class="form-label fw-bold">
                                    <i class="fas fa-tag me-2"></i>Label
                                </label>
                                <select class="form-select form-select-lg" id="label" name="label">
                                    <option value="">Select Label...</option>
                                    <!-- Labels will be populated via JavaScript -->
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="reference" class="form-label fw-bold">
                                    <i class="fas fa-link me-2"></i>Reference
                                </label>
                                <input type="text" class="form-control form-control-lg" id="reference" 
                                       name="reference" placeholder="Enter Reference">
                            </div>
                        </div>
                    </div>

                    <!-- Address Section -->
                    <div class="mb-4">
                        <div class="form-group">
                            <label for="address" class="form-label fw-bold">
                                <i class="fas fa-map-marker-alt me-2"></i>Address
                            </label>
                            <textarea class="form-control form-control-lg" id="address" name="address" 
                                      rows="2" placeholder="Enter Address"></textarea>
                        </div>
                    </div>

                    <!-- Comment Section -->
                    <div class="mb-4">
                        <div class="form-group">
                            <label for="comment" class="form-label fw-bold">
                                <i class="fas fa-comments me-2"></i>Comment
                            </label>
                            <textarea class="form-control form-control-lg" id="comment" name="comment" 
                                      rows="3" placeholder="Enter Comment"></textarea>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary btn-lg" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>Close
                </button>
                <button type="button" class="btn btn-primary btn-lg" id="submitLeadBtn">
                    <i class="fas fa-save me-2"></i>Save Lead
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
/* Custom styles for the add lead modal */
.modal-content {
    border: none;
    border-radius: 15px;
    box-shadow: 0 0 20px rgba(0,0,0,0.1);
}

.modal-header {
    border-top-left-radius: 15px;
    border-top-right-radius: 15px;
    padding: 1.5rem;
}

.modal-body {
    padding: 2rem;
}

.modal-footer {
    padding: 1.5rem;
    border-bottom-left-radius: 15px;
    border-bottom-right-radius: 15px;
}

.form-label {
    margin-bottom: 0.5rem;
    color: #495057;
}

.form-control, .form-select {
    border: 2px solid #e9ecef;
    border-radius: 10px;
    transition: all 0.3s ease;
}

.form-control:focus, .form-select:focus {
    border-color: #80bdff;
    box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
}

.required-field::after {
    content: "*";
    color: #dc3545;
    margin-left: 4px;
}

.country-flag-dropdown {
    min-width: 120px;
    background-color: #f8f9fa;
    border: 2px solid #e9ecef;
    border-radius: 10px 0 0 10px;
}

.input-group > .form-control {
    border-left: none;
}

.btn-lg {
    padding: 0.75rem 1.5rem;
    font-size: 1rem;
}

.form-group {
    margin-bottom: 0;
}

/* Improved dropdown styling */
.dropdown-menu {
    border-radius: 10px;
    box-shadow: 0 0 20px rgba(0,0,0,0.1);
    padding: 0.5rem;
}

.dropdown-item {
    border-radius: 7px;
    padding: 0.5rem 1rem;
}

.dropdown-item:hover {
    background-color: #f8f9fa;
}

/* Custom scrollbar for textareas */
textarea::-webkit-scrollbar {
    width: 8px;
}

textarea::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}

textarea::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 4px;
}

textarea::-webkit-scrollbar-thumb:hover {
    background: #555;
}

/* Mobile responsiveness */
@media (max-width: 768px) {
    .modal-body {
        padding: 1rem;
    }
    
    .form-control, .form-select, .btn {
        font-size: 16px; /* Prevent zoom on mobile */
    }
    
    .modal-footer {
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .modal-footer .btn {
        width: 100%;
    }
}

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
