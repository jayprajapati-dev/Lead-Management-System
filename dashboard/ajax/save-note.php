<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include configuration
require_once '../../includes/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in']);
    exit();
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['content']) || empty(trim($data['content']))) {
    echo json_encode(['status' => 'error', 'message' => 'Note content is required']);
    exit();
}

$content = trim($data['content']);
$user_id = $_SESSION['user_id'];

try {
    // Prepare and execute the insert statement
    $stmt = $conn->prepare("INSERT INTO notes (user_id, content, created_at) VALUES (?, ?, NOW())");
    $stmt->bind_param("is", $user_id, $content);
    
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Note saved successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to save note']);
    }
} catch (Exception $e) {
    error_log("Error saving note: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'An error occurred while saving the note']);
}

$stmt->close();
$conn->close();
?> 