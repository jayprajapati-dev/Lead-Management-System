<?php
// Assuming user data (like list of users) is available for the "User" dropdown
// For now, we'll use a placeholder and assume a PHP variable $usersList is available
$usersList = []; // Replace with actual fetching of users
?>

<!-- Add Reminder Modal -->
<div class="modal fade" id="addReminderModal" tabindex="-1" aria-labelledby="addReminderModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addReminderModalLabel">Add Reminder</h5>
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
                <option value="" selected disabled>Select User</option>
                 <?php foreach ($usersList as $user): // Assuming $usersList is available ?>
                    <option value="<?php echo $user['id']; ?>"><?php echo htmlspecialchars($user['name']); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="mb-3">
            <label for="reminderTemplate" class="form-label">Reminder Template:</label>
            <select class="form-select" id="reminderTemplate" name="template_id">
              <option value="" selected disabled>Select...</option>
              <!-- Options for Reminder Templates will go here -->
            </select>
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
          <div class="card mt-4">
            <div class="card-header d-flex justify-content-between align-items-center">
              <h6 class="mb-0">Whatsapp Automation</h6>
              <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" id="reminderWhatsappAutomationToggle" name="whatsapp_automation">
                <label class="form-check-label" for="reminderWhatsappAutomationToggle"></label>
              </div>
            </div>
            <div class="card-body" id="reminderWhatsappAutomationDetails" style="display: none;">
              <div class="mb-3">
                <label for="reminderCustomerMobile" class="form-label">Customer Mobile</label>
                <div class="input-group">
                  <span class="input-group-text">🇮🇳 +91</span>
                  <input type="text" class="form-control" id="reminderCustomerMobile" name="customer_mobile" placeholder="Enter Mobile Number">
                   <button class="btn btn-outline-secondary" type="button" title="Add Mobile Number"><i class="fas fa-plus"></i></button>
                   <button class="btn btn-outline-secondary" type="button" title="View Customer Details"><i class="fas fa-eye"></i></button>
                </div>
              </div>
              <!-- More automation details could go here -->
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