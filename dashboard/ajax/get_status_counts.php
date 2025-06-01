<?php
require_once '../../includes/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    die(json_encode([
        'status' => 'error',
        'message' => 'Unauthorized access'
    ]));
}

try {
    // Get today's date in the correct format
    $today = date('Y-m-d');
    
    // Get counts for each status for today's leads
    $query = "
        SELECT 
            lst.name as status_name,
            COUNT(l.id) as count
        FROM lead_status_types lst
        LEFT JOIN leads l ON lst.id = l.status_id 
            AND DATE(l.created_at) = ?
            AND l.assigned_to = ?
        GROUP BY lst.id, lst.name
        ORDER BY lst.display_order";

    $stmt = $conn->prepare($query);
    $stmt->bind_param('si', $today, $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();

    $counts = [
        'new' => 0,
        'processing' => 0,
        'close_by' => 0,
        'confirm' => 0,
        'cancel' => 0
    ];

    while ($row = $result->fetch_assoc()) {
        $status = strtolower(str_replace('-', '_', $row['status_name']));
        $counts[$status] = (int)$row['count'];
    }

    echo json_encode([
        'status' => 'success',
        'counts' => $counts
    ]);

} catch (Exception $e) {
    error_log("Error getting status counts: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to get status counts'
    ]);
} 