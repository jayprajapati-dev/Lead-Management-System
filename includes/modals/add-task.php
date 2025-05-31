<?php
// Assuming $userName is available from the session or included file like dashboard-header.php
// Assuming user data (like list of users) is available for the "Assign To" dropdown
// For now, we'll use a placeholder and assume a PHP variable $usersList is available
$usersList = []; // Replace with actual fetching of users

// Assuming the logged-in user's name and ID are available from the session
$loggedInUserName = ($_SESSION['user_first_name'] ?? '') . ' ' . ($_SESSION['user_last_name'] ?? '');
$loggedInUserId = $_SESSION['user_id'] ?? '';
?>

<!-- Include Modal Styles -->
<link rel="stylesheet" href="../includes/modals/modal_styles.css">

<!-- Add Task Modal -->
<div class="modal fade" id="addTaskModal" tabindex="-1" aria-labelledby="addTaskModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addTaskModalLabel"><i class="fas fa-tasks"></i> Add Task</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="addTaskForm">
          <div class="mb-3">
            <label for="taskSubject" class="form-label">Subject: *</label>
            <input type="text" class="form-control" id="taskSubject" name="subject" placeholder="Enter Subject" required>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-md-4">
              <label for="taskPriority" class="form-label">Priority:</label>
              <select class="form-select" id="taskPriority" name="priority">
                <option value="Low" selected>Low</option>
                <option value="Medium">Medium</option>
                <option value="High">High</option>
              </select>
            </div>
            <div class="col-md-4">
              <label for="taskStatus" class="form-label">Status:</label>
              <select class="form-select" id="taskStatus" name="status">
                <option value="New" selected>New</option>
                <option value="Processing">Processing</option>
                 <option value="In Feedback">In Feedback</option>
                <option value="Completed">Completed</option>
                <option value="Rejected">Rejected</option>
              </select>
            </div>
            <div class="col-md-4">
              <label for="taskLabel" class="form-label">Label (Optional):</label>
              <select class="form-select" id="taskLabel" name="label">
                <option value="" selected>Select...</option>
                <!-- Label options will be loaded dynamically if needed -->
              </select>
            </div>
          </div>

           <div class="row g-3 mb-3 align-items-center">
                <div class="col-md-4">
                    <label for="taskRecursive" class="form-label">Recursive</label>
                    <div class="form-check form-switch mt-2">
                        <input class="form-check-input" type="checkbox" id="taskRecursive" name="is_recursive">
                        <label class="form-check-label" for="taskRecursive"></label>
                    </div>
                </div>
                 <!-- Fields to show when Recursive is OFF -->
                 <div class="col-md-8 row g-3" id="taskNonRecursiveDates">
                     <div class="col-md-6">
                        <label for="taskStartDate" class="form-label">Start Date: *</label>
                        <input type="datetime-local" class="form-control" id="taskStartDate" name="start_date" required>
                    </div>
                     <div class="col-md-6">
                        <label for="taskEndDate" class="form-label">End Date: *</label>
                        <input type="datetime-local" class="form-control" id="taskEndDate" name="end_date" required>
                    </div>
                 </div>

                 <!-- Fields to show when Recursive is ON (initially hidden) -->
                 <div class="col-md-8 d-none" id="taskRecursiveOptions">
                     <div class="row g-3">
                         <div class="col-md-6">
                            <label for="taskRecursionType" class="form-label">Recursion Type: *</label>
                            <select class="form-select" id="taskRecursionType" name="recursion_type">
                                <option value="daily" selected>Once</option>
                                <option value="daily">Daily</option>
                                <option value="weekly">Weekly</option>
                                <option value="monthly">Monthly</option>
                                <option value="quarterly">Quarterly</option>
                                <option value="yearly">Yearly</option>
                            </select>
                         </div>
                         <div class="col-md-6">
                            <label for="taskDate" class="form-label">Date: *</label>
                            <input type="datetime-local" class="form-control" id="taskDate" name="task_date">
                         </div>
                     </div>
                 </div>
           </div>

           <div class="mb-3">
                <label for="taskAssignTo" class="form-label">Assign To: *</label>
                <?php
                // Get users from database - in a real implementation, this would fetch from DB
                // For now, we'll create a sample array with the logged-in user
                $usersList = [
                    ['id' => $loggedInUserId, 'name' => $loggedInUserName]
                ];
                
                // In a real implementation, you'd fetch all users from database
                // For example:
                // $query = "SELECT id, CONCAT(first_name, ' ', last_name) AS name FROM users ORDER BY name";
                // $result = mysqli_query($conn, $query);
                // while($row = mysqli_fetch_assoc($result)) {
                //     $usersList[] = $row;
                // }
                ?>
                <select class="form-select" id="taskAssignTo" name="assigned_to" required>
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
                <div class="form-text"><small><i class="fas fa-info-circle me-1"></i>Task will be assigned to this user</small></div>
            </div>

          <div class="mb-3">
            <label for="taskDescription" class="form-label">Description:</label>
            <!-- Placeholder for a rich text editor -->
            <textarea class="form-control" id="taskDescription" name="description" rows="5" placeholder="Enter Description"></textarea>
          </div>

           <div class="mb-3">
              <label for="taskAttachment" class="form-label">Attachment (Optional):</label>
              <div class="border rounded p-3 text-center">
                  <i class="fas fa-cloud-upload-alt fa-2x text-muted"></i>
                  <p class="text-muted mt-2">Drop Files here or click to upload</p>
                  <p class="text-muted small">Allowed IMAGES, VIDEOS, PDF, DOC, EXCEL, PPT, TEXT<br>max size of 5 MB</p>
                  <input type="file" id="taskAttachment" name="attachment" class="d-none" accept="image/*,video/*,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt">
              </div>
           </div>

        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" form="addTaskForm" class="btn btn-primary">Submit</button>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const taskRecursiveToggle = document.getElementById('taskRecursive');
    const nonRecursiveDates = document.getElementById('taskNonRecursiveDates');
    const recursiveOptions = document.getElementById('taskRecursiveOptions');
    const taskStartDate = document.getElementById('taskStartDate');
    const taskEndDate = document.getElementById('taskEndDate');
    const taskDate = document.getElementById('taskDate');
    const taskRecursionType = document.getElementById('taskRecursionType');

    // Function to toggle visibility based on recursion toggle
    function toggleRecursiveFields() {
        const isRecurring = taskRecursiveToggle.checked;
        
        if (isRecurring) {
            nonRecursiveDates.classList.add('d-none');
            recursiveOptions.classList.remove('d-none');
            // Set required/disabled states
            taskStartDate.removeAttribute('required');
            taskEndDate.removeAttribute('required');
            taskDate.setAttribute('required', 'required');
            taskRecursionType.setAttribute('required', 'required');
        } else {
            nonRecursiveDates.classList.remove('d-none');
            recursiveOptions.classList.add('d-none');
            // Set required/disabled states
            taskStartDate.setAttribute('required', 'required');
            taskEndDate.setAttribute('required', 'required');
            taskDate.removeAttribute('required');
            taskRecursionType.removeAttribute('required');
        }
    }

    // Add event listener to the recursion toggle
    if (taskRecursiveToggle) {
        taskRecursiveToggle.addEventListener('change', toggleRecursiveFields);

        // Set initial state on page load
        toggleRecursiveFields();
    }
});
</script>

<!-- Include Modal Enhancements Script -->
<script src="../includes/modals/modal_enhancements.js"></script>