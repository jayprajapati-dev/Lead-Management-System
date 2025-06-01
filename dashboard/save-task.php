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

// Get POST data
$response = ['success' => false, 'message' => ''];

try {
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $priority = $_POST['priority'] ?? 'medium';
    $dueDate = $_POST['dueDate'] ?? '';
    $assignedTo = $_POST['assignedTo'] ?? '';
    $status = $_POST['status'] ?? 'pending';
    $taskId = $_POST['taskId'] ?? null; // For updates

    // Validate required fields
    if (empty($title) || empty($dueDate) || empty($assignedTo)) {
        throw new Exception('Please fill in all required fields');
    }

    // Validate priority
    if (!in_array($priority, ['low', 'medium', 'high'])) {
        throw new Exception('Invalid priority value');
    }

    // Validate status
    if (!in_array($status, ['pending', 'in-progress', 'completed', 'cancelled'])) {
        throw new Exception('Invalid status value');
    }

    // Format due date
    $formattedDueDate = date('Y-m-d H:i:s', strtotime($dueDate));

    if ($taskId) {
        // Update existing task
        $query = "UPDATE tasks SET 
                  title = ?, 
                  description = ?, 
                  priority = ?, 
                  due_date = ?, 
                  assigned_to = ?, 
                  status = ?,
                  updated_at = CURRENT_TIMESTAMP
                  WHERE id = ? AND (created_by = ? OR assigned_to = ?)";
        
        $params = [
            $title, 
            $description, 
            $priority, 
            $formattedDueDate, 
            $assignedTo, 
            $status, 
            $taskId,
            $_SESSION['user_id'],
            $_SESSION['user_id']
        ];
    } else {
        // Create new task
        $query = "INSERT INTO tasks (
                    title, description, priority, due_date, assigned_to, 
                    status, created_by, created_at, updated_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)";
        
        $params = [
            $title, 
            $description, 
            $priority, 
            $formattedDueDate, 
            $assignedTo, 
            $status,
            $_SESSION['user_id']
        ];
    }

    $stmt = executeQuery($query, $params);

    if ($stmt->affected_rows > 0) {
        $response['success'] = true;
        $response['message'] = $taskId ? 'Task updated successfully' : 'Task created successfully';
    } else {
        throw new Exception($taskId ? 'No changes made or unauthorized' : 'Failed to create task');
    }

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
    error_log("Error saving task: " . $e->getMessage());
}

// Send JSON response
header('Content-Type: application/json');
echo json_encode($response); 