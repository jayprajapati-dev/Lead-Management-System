<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include configuration
require_once '../includes/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// Get filter parameters
$search = isset($_GET['search']) ? $_GET['search'] : '';
$priority = isset($_GET['priority']) ? $_GET['priority'] : '';
$status = isset($_GET['status']) ? $_GET['status'] : '';
$assignedTo = isset($_GET['assignedTo']) ? $_GET['assignedTo'] : '';

// Build the query
$query = "SELECT t.*, 
          u.first_name, u.last_name,
          CONCAT(cu.first_name, ' ', cu.last_name) as created_by_name
          FROM tasks t
          LEFT JOIN users u ON t.assigned_to = u.id
          LEFT JOIN users cu ON t.created_by = cu.id
          WHERE 1=1";

$params = [];

if (!empty($search)) {
    $query .= " AND (t.title LIKE ? OR t.description LIKE ?)";
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

if (!empty($priority)) {
    $query .= " AND t.priority = ?";
    $params[] = $priority;
}

if (!empty($status)) {
    $query .= " AND t.status = ?";
    $params[] = $status;
}

if (!empty($assignedTo)) {
    $query .= " AND t.assigned_to = ?";
    $params[] = $assignedTo;
}

$query .= " ORDER BY t.due_date ASC";

try {
    $stmt = executeQuery($query, $params);
    $result = $stmt->get_result();
    $tasks = [];
    
    while ($task = $result->fetch_assoc()) {
        $tasks[] = $task;
    }

    // Generate HTML for tasks
    if (count($tasks) > 0) {
        foreach ($tasks as $task) {
            ?>
            <div class="col-12 col-md-6 col-lg-4 mb-4">
                <div class="task-card">
                    <div class="task-header">
                        <h5 class="mb-0"><?php echo htmlspecialchars($task['title']); ?></h5>
                        <span class="priority-badge priority-<?php echo strtolower($task['priority']); ?>">
                            <?php echo ucfirst($task['priority']); ?>
                        </span>
                    </div>
                    <div class="task-body">
                        <p class="text-muted mb-3"><?php echo nl2br(htmlspecialchars($task['description'])); ?></p>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted"><i class="fas fa-user me-2"></i><?php echo htmlspecialchars($task['first_name'] . ' ' . $task['last_name']); ?></span>
                            <span class="status-badge status-<?php echo str_replace(' ', '-', strtolower($task['status'])); ?>">
                                <?php echo ucfirst($task['status']); ?>
                            </span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted"><i class="fas fa-calendar me-2"></i><?php echo date('M d, Y', strtotime($task['due_date'])); ?></span>
                            <div class="btn-group">
                                <button type="button" class="btn btn-sm btn-outline-primary edit-task" data-task-id="<?php echo $task['id']; ?>">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-danger delete-task" data-task-id="<?php echo $task['id']; ?>">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php
        }
    } else {
        ?>
        <div class="col-12">
            <div class="empty-state-message">
                <i class="fas fa-tasks fa-3x mb-3"></i>
                <h4>No tasks found</h4>
                <p class="text-muted">Try adjusting your filters or create a new task.</p>
            </div>
        </div>
        <?php
    }
} catch (Exception $e) {
    error_log("Error fetching tasks: " . $e->getMessage());
    echo '<div class="col-12"><div class="alert alert-danger">Error loading tasks. Please try again later.</div></div>';
}
?> 