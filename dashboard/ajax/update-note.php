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

if (!isset($data['id']) || !is_numeric($data['id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid note ID']);
    exit();
}

if (!isset($data['content']) || empty(trim($data['content']))) {
    echo json_encode(['status' => 'error', 'message' => 'Note content cannot be empty']);
    exit();
}

$note_id = (int)$data['id'];
$content = trim($data['content']);
$user_id = $_SESSION['user_id'];

try {
    // First verify that the note belongs to the user
    $verify_stmt = $conn->prepare("SELECT id FROM notes WHERE id = ? AND user_id = ?");
    $verify_stmt->bind_param("ii", $note_id, $user_id);
    $verify_stmt->execute();
    $result = $verify_stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['status' => 'error', 'message' => 'Note not found or unauthorized']);
        exit();
    }
    
    // Update the note
    $update_stmt = $conn->prepare("UPDATE notes SET content = ? WHERE id = ? AND user_id = ?");
    $update_stmt->bind_param("sii", $content, $note_id, $user_id);
    
    if ($update_stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Note updated successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update note']);
    }
} catch (Exception $e) {
    error_log("Error updating note: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'An error occurred while updating the note']);
}

$verify_stmt->close();
$update_stmt->close();
$conn->close();
?>
