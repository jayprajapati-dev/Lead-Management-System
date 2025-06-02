<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include configuration
require_once '../../includes/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    die(json_encode([
        'status' => 'error',
        'message' => 'Unauthorized access'
    ]));
}

try {
    // Fetch labels from the database
    $sql = "SELECT id, name FROM lead_labels WHERE status = 'active' ORDER BY name";
    $result = $conn->query($sql);
    
    $labels = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $labels[] = [
                'id' => $row['id'],
                'name' => htmlspecialchars($row['name'])
            ];
        }
    }
    
    echo json_encode([
        'status' => 'success',
        'labels' => $labels
    ]);
    
} catch (Exception $e) {
    error_log("Error fetching labels: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to fetch labels'
    ]);
} 