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

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die(json_encode([
        'status' => 'error',
        'message' => 'Invalid request method'
    ]));
}

// Get and sanitize form data
$data = [
    'status' => strtolower(sanitizeInput($_POST['status'] ?? '')), // Convert to lowercase
    'source_name' => sanitizeInput($_POST['source'] ?? ''),
    'assigned_to' => (int)($_POST['user_id'] ?? $_SESSION['user_id']),
    'phone' => sanitizeInput($_POST['customer_mobile'] ?? ''),
    'company' => sanitizeInput($_POST['company_name'] ?? ''),
    'lead_date' => sanitizeInput($_POST['date'] ?? date('Y-m-d')),
    'name' => sanitizeInput($_POST['customer_name'] ?? ''),
    'email' => sanitizeInput($_POST['email'] ?? ''),
    'reference' => sanitizeInput($_POST['reference'] ?? ''),
    'address' => sanitizeInput($_POST['address'] ?? ''),
    'comment' => sanitizeInput($_POST['comment'] ?? '')
];

// Validate required fields
$errors = [];

if (empty($data['status'])) {
    $errors[] = 'Status is required';
}

if (empty($data['source_name'])) {
    $errors[] = 'Source is required';
}

if (empty($data['phone'])) {
    $errors[] = 'Customer mobile number is required';
}

if (empty($data['name'])) {
    $errors[] = 'Customer name is required';
}

// If there are validation errors, return them
if (!empty($errors)) {
    die(json_encode([
        'status' => 'error',
        'message' => 'Validation failed',
        'errors' => $errors
    ]));
}

try {
    // Begin transaction
    $conn->begin_transaction();

    // Get source_id from source name
    $source_stmt = $conn->prepare("SELECT id FROM lead_sources WHERE name = ?");
    $source_stmt->bind_param("s", $data['source_name']);
    
    if (!$source_stmt->execute()) {
        throw new Exception("Error getting source ID: " . $source_stmt->error);
    }
    
    $source_result = $source_stmt->get_result();
    if ($source_result->num_rows === 0) {
        throw new Exception("Invalid source selected");
    }
    
    $source_id = $source_result->fetch_assoc()['id'];

    // Insert lead into database
    $sql = "INSERT INTO leads (
        status,
        source_id,
        assigned_to,
        phone,
        company,
        lead_date,
        name,
        email,
        reference,
        address,
        created_by,
        created_at,
        updated_at
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "ssisssssssi",
        $data['status'],
        $source_id,
        $data['assigned_to'],
        $data['phone'],
        $data['company'],
        $data['lead_date'],
        $data['name'],
        $data['email'],
        $data['reference'],
        $data['address'],
        $_SESSION['user_id']
    );

    // Execute the statement
    if (!$stmt->execute()) {
        throw new Exception("Error inserting lead: " . $stmt->error);
    }

    // Get the inserted lead ID
    $lead_id = $stmt->insert_id;

    // Add comment to lead_notes if provided
    if (!empty($data['comment'])) {
        $note_sql = "INSERT INTO lead_notes (
            lead_id,
            user_id,
            note,
            created_at
        ) VALUES (?, ?, ?, NOW())";

        $note_stmt = $conn->prepare($note_sql);
        $note_stmt->bind_param("iis", $lead_id, $_SESSION['user_id'], $data['comment']);

        if (!$note_stmt->execute()) {
            throw new Exception("Error adding note: " . $note_stmt->error);
        }
    }

    // Add an activity log entry
    $activity_sql = "INSERT INTO lead_activities (
        lead_id,
        user_id,
        activity_type,
        description,
        created_at
    ) VALUES (?, ?, 'note', 'Lead created', NOW())";

    $activity_stmt = $conn->prepare($activity_sql);
    $activity_stmt->bind_param("ii", $lead_id, $_SESSION['user_id']);

    if (!$activity_stmt->execute()) {
        throw new Exception("Error logging activity: " . $activity_stmt->error);
    }

    // Commit transaction
    $conn->commit();

    // Return success response
    echo json_encode([
        'status' => 'success',
        'message' => 'Lead Inserted Successfully',
        'lead_id' => $lead_id
    ]);

} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();

    // Log the error
    error_log("Error creating lead: " . $e->getMessage());

    // Return error response with more details in development
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to create lead: ' . $e->getMessage()
    ]);
} 