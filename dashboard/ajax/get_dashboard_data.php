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
    // Get today's leads summary
    $summaryQuery = "SELECT * FROM vw_todays_leads_summary";
    $summaryResult = $conn->query($summaryQuery);
    $leadsSummary = $summaryResult->fetch_all(MYSQLI_ASSOC);

    // Get today's leads by source
    $sourceQuery = "SELECT * FROM vw_todays_leads_by_source";
    $sourceResult = $conn->query($sourceQuery);
    $leadsSource = $sourceResult->fetch_all(MYSQLI_ASSOC);

    // Get today's new leads
    $newLeadsQuery = "
        SELECT 
            l.*,
            CONCAT(u.first_name, ' ', u.last_name) as assigned_to_name,
            ls.name as source_name,
            lst.name as status_name,
            lst.color_code as status_color
        FROM leads l
        LEFT JOIN users u ON l.assigned_to = u.id
        LEFT JOIN lead_sources ls ON l.source_id = ls.id
        LEFT JOIN lead_status_types lst ON l.status_id = lst.id
        WHERE DATE(l.created_at) = CURDATE()
        ORDER BY l.created_at DESC";
    
    $newLeadsResult = $conn->query($newLeadsQuery);
    $newLeads = $newLeadsResult->fetch_all(MYSQLI_ASSOC);

    // Return the response
    echo json_encode([
        'status' => 'success',
        'data' => [
            'summary' => $leadsSummary,
            'sources' => $leadsSource,
            'newLeads' => $newLeads
        ]
    ]);

} catch (Exception $e) {
    error_log("Error fetching dashboard data: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to fetch dashboard data'
    ]);
} 