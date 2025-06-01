<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include configuration
require_once '../includes/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    die(json_encode([
        'status' => 'error',
        'message' => 'Unauthorized access'
    ]));
}

// Get today's date
$today = date('Y-m-d');

try {
    // Prepare the query to get today's leads
    $query = "SELECT 
        l.*,
        CONCAT(u.first_name, ' ', u.last_name) as assigned_to_name,
        ls.name as source_name
    FROM leads l
    LEFT JOIN users u ON l.assigned_to = u.id
    LEFT JOIN lead_sources ls ON l.source_id = ls.id
    WHERE DATE(l.created_at) = ?
    ORDER BY l.created_at DESC";

    $stmt = $conn->prepare($query);
    $stmt->bind_param('s', $today);
    $stmt->execute();
    $result = $stmt->get_result();

    $leads = [];
    while ($row = $result->fetch_assoc()) {
        // Format the lead data
        $leads[] = [
            'id' => $row['id'],
            'name' => htmlspecialchars($row['name']),
            'email' => htmlspecialchars($row['email']),
            'phone' => htmlspecialchars($row['phone']),
            'company' => htmlspecialchars($row['company']),
            'status' => $row['status'],
            'source' => $row['source_name'],
            'assigned_to' => $row['assigned_to_name'],
            'created_at' => date('Y-m-d H:i:s', strtotime($row['created_at']))
        ];
    }

    // Get counts for each status
    $countQuery = "SELECT 
        status, 
        COUNT(*) as count 
    FROM leads 
    WHERE DATE(created_at) = ? 
    GROUP BY status";

    $countStmt = $conn->prepare($countQuery);
    $countStmt->bind_param('s', $today);
    $countStmt->execute();
    $countResult = $countStmt->get_result();

    $counts = [
        'new' => 0,
        'processing' => 0,
        'close-by' => 0,
        'confirm' => 0,
        'cancel' => 0
    ];

    while ($row = $countResult->fetch_assoc()) {
        $status = strtolower($row['status']);
        $counts[$status] = (int)$row['count'];
    }

    // Return the response
    echo json_encode([
        'status' => 'success',
        'leads' => $leads,
        'counts' => $counts
    ]);

} catch (Exception $e) {
    error_log("Error fetching leads: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to fetch leads'
    ]);
} 