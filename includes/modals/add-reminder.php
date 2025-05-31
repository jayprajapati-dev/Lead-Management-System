<?php
// Get logged-in user information from session
$loggedInUserId = $_SESSION['user_id'] ?? '';
$loggedInUserName = ($_SESSION['user_first_name'] ?? '') . ' ' . ($_SESSION['user_last_name'] ?? '');

// If we don't have the user name in session, try to fetch it from the database
if (empty(trim($loggedInUserName)) && !empty($loggedInUserId)) {
    try {
        // Assuming a database connection is available
        $stmt = $conn->prepare("SELECT first_name, last_name FROM users WHERE id = ?");
        $stmt->bind_param("i", $loggedInUserId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($user = $result->fetch_assoc()) {
            $loggedInUserName = trim($user['first_name'] . ' ' . $user['last_name']);
        }
    } catch (Exception $e) {
        error_log("Error fetching user details: " . $e->getMessage());
    }
}

// If still empty, use a default value
if (empty(trim($loggedInUserName))) {
    $loggedInUserName = 'Unknown User';
}

// Placeholder for user list - in a real implementation, this would fetch all users from the database
$usersList = [
    // This is just a placeholder, replace with actual database query
    ['id' => 1, 'name' => 'John Doe'],
    ['id' => 2, 'name' => 'Jane Smith']
];

// Placeholder for reminder templates - in a real implementation, this would fetch from the database
$reminderTemplates = [];
?>

<!-- Include Modal Styles -->
<link rel="stylesheet" href="../includes/modals/modal_styles.css">

<!-- Add Reminder Modal -->
<div class="modal fade" id="addReminderModal" tabindex="-1" aria-labelledby="addReminderModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addReminderModalLabel"><i class="fas fa-bell"></i> Add Reminder</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="addReminderForm">
          <!-- Recurrence Options -->
          <div class="mb-3">
            <label class="form-label">Repeat:</label>
            <div>
              <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="reminderRepeat" id="reminderRepeatOnce" value="once" checked>
                <label class="form-check-label" for="reminderRepeatOnce">Once</label>
              </div>
              <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="reminderRepeat" id="reminderRepeatDaily" value="daily">
                <label class="form-check-label" for="reminderRepeatDaily">Daily</label>
              </div>
              <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="reminderRepeat" id="reminderRepeatWeekly" value="weekly">
                <label class="form-check-label" for="reminderRepeatWeekly">Weekly</label>
              </div>
              <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="reminderRepeat" id="reminderRepeatMonthly" value="monthly">
                <label class="form-check-label" for="reminderRepeatMonthly">Monthly</label>
              </div>
              <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="reminderRepeat" id="reminderRepeatQuarterly" value="quarterly">
                <label class="form-check-label" for="reminderRepeatQuarterly">Quarterly</label>
              </div>
              <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="reminderRepeat" id="reminderRepeatHalfYearly" value="half-yearly">
                <label class="form-check-label" for="reminderRepeatHalfYearly">Half-Yearly</label>
              </div>
              <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="reminderRepeat" id="reminderRepeatYearly" value="yearly">
                <label class="form-check-label" for="reminderRepeatYearly">Yearly</label>
              </div>
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-6">
              <label for="reminderDate" class="form-label">Date: *</label>
              <input type="datetime-local" class="form-control" id="reminderDate" name="date" required>
            </div>
            <div class="col-md-6">
              <label for="reminderUser" class="form-label">User: *</label>
              <select class="form-select" id="reminderUser" name="user_id" required>
                <?php if (!empty($loggedInUserId)): ?>
                    <option value="<?php echo $loggedInUserId; ?>" selected><?php echo htmlspecialchars($loggedInUserName); ?> (You)</option>
                <?php else: ?>
                    <option value="" selected disabled>Select User</option>
                <?php endif; ?>
                
                <?php foreach ($usersList as $user): ?>
                    <?php if ($user['id'] != $loggedInUserId): // Skip logged-in user as it's already at the top ?>
                        <option value="<?php echo $user['id']; ?>"><?php echo htmlspecialchars($user['name']); ?></option>
                    <?php endif; ?>
                <?php endforeach; ?>
              </select>
              <div class="form-text"><small><i class="fas fa-info-circle me-1"></i>Reminder will be assigned to this user</small></div>
            </div>
          </div>

          <div class="mb-3">
            <label for="reminderTemplate" class="form-label">Reminder Template:</label>
            <select class="form-select" id="reminderTemplate" name="template_id">
              <?php if (empty($reminderTemplates)): ?>
                <option value="" selected disabled>No options</option>
              <?php else: ?>
                <option value="" selected disabled>Select...</option>
                <?php foreach ($reminderTemplates as $template): ?>
                  <option value="<?php echo $template['id']; ?>"><?php echo htmlspecialchars($template['name']); ?></option>
                <?php endforeach; ?>
              <?php endif; ?>
            </select>
            <div class="form-text"><small><i class="fas fa-info-circle me-1"></i>Select a template to auto-fill reminder details</small></div>
          </div>

          <div class="mb-3">
            <label for="reminderTitle" class="form-label">Title: *</label>
            <input type="text" class="form-control" id="reminderTitle" name="title" placeholder="Enter Title" required>
          </div>

          <div class="mb-3">
            <label for="reminderMessage" class="form-label">Message: *</label>
            <textarea class="form-control" id="reminderMessage" name="message" rows="3" placeholder="Enter Message" required></textarea>
          </div>

          <!-- Whatsapp Automation Section -->
          <div class="card mt-4 border-0 shadow-sm">
            <div class="card-header bg-gradient d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #25D366 0%, #128C7E 100%); color: white;">
              <h6 class="mb-0"><i class="fab fa-whatsapp me-2"></i> Whatsapp Automation</h6>
              <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" id="reminderWhatsappAutomationToggle" name="whatsapp_automation">
                <label class="form-check-label text-white ms-2" for="reminderWhatsappAutomationToggle">Enable WhatsApp notifications</label>
              </div>
            </div>
            <div class="card-body" id="reminderWhatsappAutomationDetails" style="display: none; background-color: #f9fafb;">
              <div class="mb-3">
                <label for="reminderCustomerMobile" class="form-label">Customer Mobile:</label>
                <div class="input-group">
                  <span class="input-group-text" style="background-color: #25D366; color: white; border-color: #25D366;">🇮🇳 +91</span>
                  <input type="text" class="form-control" id="reminderCustomerMobile" name="customer_mobile" placeholder="Enter Mobile Number">
                  <button class="btn btn-outline-success" type="button" title="Add Mobile Number"><i class="fas fa-plus"></i></button>
                  <button class="btn btn-outline-primary" type="button" title="View Customer Details"><i class="fas fa-eye"></i></button>
                </div>
                <div class="form-text text-muted mt-1"><small><i class="fas fa-info-circle me-1"></i>Message will be sent to this number via WhatsApp</small></div>
              </div>
              
              <div class="mb-3">
                <label for="whatsappTemplate" class="form-label">Message Template:</label>
                <select class="form-select" id="whatsappTemplate" name="whatsapp_template">
                  <option value="" selected>Default Message</option>
                  <option value="meeting">Meeting Reminder</option>
                  <option value="followup">Follow-up</option>
                  <option value="payment">Payment Reminder</option>
                </select>
              </div>
              
              <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" id="sendAttachment" name="send_attachment">
                <label class="form-check-label" for="sendAttachment">Include Attachment</label>
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

<script>
document.addEventListener('DOMContentLoaded', function () {
    const whatsappAutomationToggle = document.getElementById('reminderWhatsappAutomationToggle');
    const whatsappAutomationDetails = document.getElementById('reminderWhatsappAutomationDetails');

    // Function to toggle visibility
    function toggleWhatsappAutomationFields() {
        if (whatsappAutomationToggle.checked) {
            whatsappAutomationDetails.style.display = 'block';
        } else {
            whatsappAutomationDetails.style.display = 'none';
        }
    }

    // Add event listener to the toggle switch
    if (whatsappAutomationToggle) {
        whatsappAutomationToggle.addEventListener('change', toggleWhatsappAutomationFields);

        // Set initial state on page load
        toggleWhatsappAutomationFields(); // Hide details initially if toggle is off
    }
});
</script>

<!-- Include Modal Enhancements Script -->
<script src="../includes/modals/modal_enhancements.js"></script>