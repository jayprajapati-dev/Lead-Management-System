<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../includes/config.php';

$loggedInUserId = $_SESSION['user_id'] ?? '';
$loggedInUserName = $_SESSION['user_first_name'] ?? 'Unknown User';

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
        <!-- Use novalidate with Bootstrap validation, form submission handled by JS -->
        <form id="addLeadForm" class="needs-validation" novalidate>
          <!-- Status and Source -->
          <div class="row mb-4">
            <div class="col-md-6">
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
            <div class="col-md-6">
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

          <!-- Assigned To and Customer Mobile -->
          <div class="row mb-4">
            <div class="col-md-6">
              <label for="assigned_to" class="form-label fw-bold required-field">
                <i class="fas fa-user-check me-2"></i>Assigned To
              </label>
              <select class="form-select form-select-lg" id="assigned_to" name="user_id" required>
                <option value="" disabled>Select User</option>
                <?php foreach ($usersList as $user): ?>
                  <option value="<?php echo htmlspecialchars($user['id']); ?>"
                    <?php echo ($loggedInUserId == $user['id']) ? 'selected' : ''; ?>>
                    <?php echo $user['name']; ?>
                  </option>
                <?php endforeach; ?>
              </select>
              <div class="invalid-feedback">Please select a user</div>
            </div>
            <div class="col-md-6">
              <label for="customer_mobile" class="form-label fw-bold required-field">
                <i class="fas fa-phone me-2"></i>Customer Mobile Number
              </label>
              <div class="input-group input-group-lg">
                <button class="btn btn-outline-secondary dropdown-toggle country-flag-dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                  <span class="flag-icon flag-icon-in"></span> +91
                </button>
                <ul class="dropdown-menu country-code-dropdown">
                  <!-- Populate via JS -->
                </ul>
                <input type="tel" class="form-control form-control-lg" id="customer_mobile"
                       name="customer_mobile" required pattern="[0-9]{10}" maxlength="10"
                       placeholder="Enter 10-digit mobile number" />
                <div class="invalid-feedback">Please enter a valid 10-digit mobile number</div>
              </div>
            </div>
          </div>

          <!-- Company and Date -->
          <div class="row mb-4">
            <div class="col-md-6">
              <label for="company_name" class="form-label fw-bold">
                <i class="fas fa-building me-2"></i>Company Name
              </label>
              <input type="text" class="form-control form-control-lg" id="company_name"
                     name="company_name" placeholder="Enter Company Name" />
            </div>
            <div class="col-md-6">
              <label for="lead_date" class="form-label fw-bold">
                <i class="fas fa-calendar me-2"></i>Date
              </label>
              <input type="date" class="form-control form-control-lg" id="lead_date"
                     name="date" value="<?php echo date('Y-m-d'); ?>" readonly />
            </div>
          </div>

          <!-- Customer Name and Email -->
          <div class="row mb-4">
            <div class="col-md-6">
              <label for="customer_name" class="form-label fw-bold required-field">
                <i class="fas fa-user me-2"></i>Customer Name
              </label>
              <input type="text" class="form-control form-control-lg" id="customer_name"
                     name="customer_name" required placeholder="Enter Customer Name" />
              <div class="invalid-feedback">Please enter customer name</div>
            </div>
            <div class="col-md-6">
              <label for="email" class="form-label fw-bold">
                <i class="fas fa-envelope me-2"></i>Email
              </label>
              <input type="email" class="form-control form-control-lg" id="email"
                     name="email" placeholder="Enter Customer Email" />
              <div class="invalid-feedback">Please enter a valid email address</div>
            </div>
          </div>

          <!-- Label and Reference -->
          <div class="row mb-4">
            <div class="col-md-6">
              <label for="label" class="form-label fw-bold">
                <i class="fas fa-tag me-2"></i>Label
              </label>
              <select class="form-select form-select-lg" id="label" name="label">
                <option value="">Select Label...</option>
                <!-- Populate via JS if needed -->
              </select>
            </div>
            <div class="col-md-6">
              <label for="reference" class="form-label fw-bold">
                <i class="fas fa-link me-2"></i>Reference
              </label>
              <input type="text" class="form-control form-control-lg" id="reference"
                     name="reference" placeholder="Enter Reference" />
            </div>
          </div>

          <!-- Address -->
          <div class="mb-4">
            <label for="address" class="form-label fw-bold">
              <i class="fas fa-map-marker-alt me-2"></i>Address
            </label>
            <textarea class="form-control form-control-lg" id="address" name="address" rows="2" placeholder="Enter Address"></textarea>
          </div>

          <!-- Comment -->
          <div class="mb-4">
            <label for="comment" class="form-label fw-bold">
              <i class="fas fa-comments me-2"></i>Comment
            </label>
            <textarea class="form-control form-control-lg" id="comment" name="comment" rows="3" placeholder="Enter Comment"></textarea>
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

<!-- Toast container as is -->

<!-- Styles and scripts as is -->

<!-- Important: Add your JavaScript logic to handle form validation and AJAX submit -->

<script>
  document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('addLeadForm');
    const submitBtn = document.getElementById('submitLeadBtn');

    // Bootstrap custom validation
    form.addEventListener('submit', e => {
      e.preventDefault();
      e.stopPropagation();

      if (!form.checkValidity()) {
        form.classList.add('was-validated');
        return;
      }
      // Submit form if valid
      submitLead();
    });

    submitBtn.addEventListener('click', () => {
      if (!form.checkValidity()) {
        form.classList.add('was-validated');
        return;
      }
      submitLead();
    });

    function submitLead() {
      // Collect form data
      const formData = new FormData(form);

      // Optional: Add extra data if needed

      fetch('../leads/save_lead.php', {
        method: 'POST',
        body: formData,
        credentials: 'include' // send cookies/session
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          // Show success toast with status
          const toastEl = document.getElementById('leadSuccessToast');
          document.getElementById('successLeadStatus').textContent = formData.get('status');
          const toast = new bootstrap.Toast(toastEl);
          toast.show();
          form.reset();
          form.classList.remove('was-validated');
          // Optional: close modal
          const modalEl = document.getElementById('addLeadModal');
          const modal = bootstrap.Modal.getInstance(modalEl);
          modal.hide();

          // Optional: trigger any list refresh here
        } else {
          showErrorToast(data.message || 'Failed to add lead');
        }
      })
      .catch(() => {
        showErrorToast('Network or server error occurred.');
      });
    }

    function showErrorToast(message) {
      const toastEl = document.getElementById('leadErrorToast');
      document.getElementById('leadErrorToastMessage').textContent = message;
      const toast = new bootstrap.Toast(toastEl);
      toast.show();
    }
  });
</script>