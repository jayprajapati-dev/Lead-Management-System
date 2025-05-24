<?php
require_once '../includes/config.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set headers
header('Content-Type: application/json');

// Function to log errors
function logError($message) {
    error_log(date('[Y-m-d H:i:s] ') . $message . "\n", 3, __DIR__ . '/sticky_notes_error.log');
}

// Basic validation
if (!isLoggedIn()) {
    logError("User not logged in");
    die(json_encode(['success' => false, 'message' => 'Please log in to save notes.']));
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    logError("Invalid request method: " . $_SERVER['REQUEST_METHOD']);
    die(json_encode(['success' => false, 'message' => 'Invalid request method.']));
}

$userId = $_SESSION['user_id'] ?? null;
if (!$userId) {
    logError("No user ID in session");
    die(json_encode(['success' => false, 'message' => 'User session not found.']));
}

$content = trim($_POST['content'] ?? '');
if (empty($content)) {
    logError("Empty content submitted by user $userId");
    die(json_encode(['success' => false, 'message' => 'Note content cannot be empty.']));
}

try {
    // Use the existing executeQuery function
    $stmt = executeQuery(
        "INSERT INTO sticky_notes (user_id, content) VALUES (?, ?)",
        [$userId, $content]
    );
    
    // Get the inserted ID
    $noteId = $conn->insert_id;
    
    // Log success
    logError("Note saved successfully for user $userId, note ID: $noteId");
    
    echo json_encode([
        'success' => true,
        'message' => 'Note saved successfully!',
        'note_id' => $noteId
    ]);
    
} catch (Exception $e) {
    // Log the error
    logError("Error saving note for user $userId: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'message' => 'Failed to save note. Please try again.'
    ]);
}
?> 