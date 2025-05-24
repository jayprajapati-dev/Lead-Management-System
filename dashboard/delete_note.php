<?php
require_once '../includes/config.php';

// Set headers
header('Content-Type: application/json');

// Basic validation
if (!isLoggedIn()) {
    die(json_encode(['success' => false, 'message' => 'Authentication required.']));
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die(json_encode(['success' => false, 'message' => 'Invalid request method.']));
}

// Get user ID from session
$userId = $_SESSION['user_id'] ?? null;
if (!$userId) {
    die(json_encode(['success' => false, 'message' => 'User session not found.']));
}

// Get and validate note ID from POST data
$noteId = $_POST['note_id'] ?? null;
if (empty($noteId) || !is_numeric($noteId)) {
    die(json_encode(['success' => false, 'message' => 'Invalid note ID.']));
}

$noteId = (int) $noteId; // Cast to integer for safety

try {
    // Prepare and execute deletion query, ensuring user owns the note
    $stmt = executeQuery(
        "DELETE FROM sticky_notes WHERE id = ? AND user_id = ?",
        [$noteId, $userId]
    );
    
    // Check if a row was affected (note was deleted)
    if ($stmt->affected_rows > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Note deleted successfully!'
        ]);
    } else {
        // Note not found or user doesn't own it
        echo json_encode([
            'success' => false,
            'message' => 'Note not found or you do not have permission to delete it.'
        ]);
    }
    
} catch (Exception $e) {
    // Log the error
    error_log("Error deleting sticky note ID " . $noteId . " for user " . $userId . ": " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => 'Failed to delete note. Please try again.'
    ]);
}

// No finally block needed as executeQuery likely handles statement closing,
// and connection is global in config.php.
?> 