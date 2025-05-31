<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get user information from session
$loggedInFirstName = $_SESSION['user_first_name'] ?? '';
$loggedInLastName = $_SESSION['user_last_name'] ?? '';
$loggedInUserName = trim($loggedInFirstName . ' ' . $loggedInLastName);
$loggedInUserId = $_SESSION['user_id'] ?? '';

// If name is empty, try to get it from the database
if (empty($loggedInUserName) && !empty($loggedInUserId)) {
    try {
        require_once '../includes/config.php';
        $stmt = executeQuery(
            "SELECT first_name, last_name FROM users WHERE id = ?",
            [$loggedInUserId]
        );
        $result = $stmt->get_result();
        if ($user = $result->fetch_assoc()) {
            $loggedInUserName = trim($user['first_name'] . ' ' . $user['last_name']);
        }
    } catch (Exception $e) {
        error_log("Error fetching user details: " . $e->getMessage());
    }
}

// If still empty, use a default value
if (empty($loggedInUserName)) {
    $loggedInUserName = 'Unknown User';
}
?>

<!-- Add Lead Modal -->
<div class="modal fade" id="addLeadModal" tabindex="-1" aria-labelledby="addLeadModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addLeadModalLabel">Add New Lead</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="addLeadForm">
          <div class="row g-3">
            <!-- Status -->
            <div class="col-md-6">
              <label for="leadStatus" class="form-label">Status:</label>
              <select class="form-select" id="leadStatus" name="status">
                <option value="New" selected>New</option>
                <option value="Processing">Processing</option>
                <option value="Close-by">Close-by</option>
                <option value="Confirm">Confirm</option>
                <option value="Cancel">Cancel</option>
              </select>
            </div>

            <!-- Source -->
            <div class="col-md-6">
              <label for="leadSource" class="form-label">Source:</label>
              <select class="form-select" id="leadSource" name="source">
                <option value="Online" selected>Online</option>
                <option value="Offline">Offline</option>
                <option value="Website">Website</option>
                <option value="Whatsapp">Whatsapp</option>
                <option value="Customer Reminder">Customer Reminder</option>
                <option value="Indiamart">Indiamart</option>
                <option value="Facebook">Facebook</option>
                <option value="Google Form">Google Form</option>
              </select>
            </div>

            <!-- User (Auto-filled) -->
            <div class="col-md-6">
              <label for="leadUser" class="form-label">User:</label>
              <input type="text" class="form-control" id="leadUser" value="<?php echo htmlspecialchars($loggedInUserName); ?>" readonly>
              <input type="hidden" id="leadUserId" name="user_id" value="<?php echo htmlspecialchars($loggedInUserId); ?>">
            </div>

            <!-- Customer Mobile Number -->
            <div class="col-md-6">
              <label for="customerMobile" class="form-label">Customer Mobile Number: *</label>
              <input type="text" class="form-control" id="customerMobile" name="customer_mobile" required>
            </div>

            <!-- Company Name -->
            <div class="col-md-6">
              <label for="companyName" class="form-label">Company Name (Optional):</label>
              <input type="text" class="form-control" id="companyName" name="company_name">
            </div>

             <!-- Date -->
             <div class="col-md-6">
                <label for="leadDate" class="form-label">Date:</label>
                <input type="date" class="form-control" id="leadDate" name="date">
             </div>

            <!-- Customer Name -->
            <div class="col-md-6">
              <label for="customerName" class="form-label">Customer Name: *</label>
              <input type="text" class="form-control" id="customerName" name="customer_name" required>
            </div>

            <!-- Email -->
            <div class="col-md-6">
              <label for="customerEmail" class="form-label">Email (Optional):</label>
              <input type="email" class="form-control" id="customerEmail" name="email">
            </div>

            <!-- Label -->
            <div class="col-md-6">
              <label for="leadLabel" class="form-label">Label (Optional):</label>
              <select class="form-select" id="leadLabel" name="label">
                <option value="" selected>Select</option>
                <!-- Label options will be loaded dynamically if needed -->
              </select>
            </div>

             <!-- Reference -->
             <div class="col-md-6">
                <label for="leadReference" class="form-label">Reference (Optional):</label>
                <input type="text" class="form-control" id="leadReference" name="reference">
             </div>

            <!-- Address -->
            <div class="col-12">
              <label for="leadAddress" class="form-label">Address (Optional):</label>
              <input type="text" class="form-control" id="leadAddress" name="address">
            </div>

            <!-- Comment -->
            <div class="col-12">
              <label for="leadComment" class="form-label">Comment (Optional):</label>
              <textarea class="form-control" id="leadComment" name="comment" rows="3"></textarea>
            </div>

          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" form="addLeadForm" class="btn btn-primary">Save Lead</button>
      </div>
    </div>
  </div>
</div>
