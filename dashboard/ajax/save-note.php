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

// Set proper content type header
header('Content-Type: application/json');

// Get POST data
$raw_input = file_get_contents('php://input');
error_log('Raw input: ' . $raw_input);

$data = json_decode($raw_input, true);

// Log the incoming data for debugging
error_log('Received data: ' . print_r($data, true));

// Log request headers for debugging
$headers = getallheaders();
error_log('Request headers: ' . print_r($headers, true));

// Check if JSON was properly decoded
if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
    error_log('JSON decode error: ' . json_last_error_msg());
    echo json_encode(['status' => 'error', 'message' => 'Invalid JSON data: ' . json_last_error_msg()]);
    exit();
}

// Check if content exists and is not empty
if (!isset($data['content'])) {
    echo json_encode(['status' => 'error', 'message' => 'Note content is missing']);
    exit();
}

// Trim the content and check if it's empty
$content = trim($data['content']);
if (empty($content)) {
    echo json_encode(['status' => 'error', 'message' => 'Note content cannot be empty']);
    exit();
}

$user_id = $_SESSION['user_id'];

// Check if this is an update (id is provided) or a new note
$isUpdate = isset($data['id']) && !empty($data['id']);
$note_id = $isUpdate ? (int)$data['id'] : null;

try {
    if ($isUpdate) {
        // First verify that the note belongs to the user
        $verify_stmt = $conn->prepare("SELECT id FROM notes WHERE id = ? AND user_id = ?");
        $verify_stmt->bind_param("ii", $note_id, $user_id);
        $verify_stmt->execute();
        $result = $verify_stmt->get_result();
        
        if ($result->num_rows === 0) {
            echo json_encode(['status' => 'error', 'message' => 'Note not found or unauthorized']);
            exit();
        }
        $verify_stmt->close();
        
        // Update the existing note
        $stmt = $conn->prepare("UPDATE notes SET content = ? WHERE id = ? AND user_id = ?");
        $stmt->bind_param("sii", $content, $note_id, $user_id);
        
        if ($stmt->execute()) {
            echo json_encode([
                'status' => 'success', 
                'message' => 'Note updated successfully',
                'note_id' => $note_id
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to update note']);
        }
    } else {
        // Insert a new note
        $stmt = $conn->prepare("INSERT INTO notes (user_id, content, created_at) VALUES (?, ?, NOW())");
        $stmt->bind_param("is", $user_id, $content);
        
        if ($stmt->execute()) {
            $note_id = $conn->insert_id; // Get the ID of the newly inserted note
            
            // Log successful insertion
            error_log("Note inserted successfully with ID: {$note_id}");
            
            echo json_encode([
                'status' => 'success', 
                'message' => 'Note saved successfully',
                'note_id' => $note_id
            ]);
        } else {
            // Log the error
            error_log("Failed to save note: " . $stmt->error);
            echo json_encode(['status' => 'error', 'message' => 'Failed to save note: ' . $stmt->error]);
        }
    }
} catch (Exception $e) {
    error_log("Error saving note: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'An error occurred while saving the note']);
}

if (isset($stmt)) {
    $stmt->close();
}
$conn->close();
?> 