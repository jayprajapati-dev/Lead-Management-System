<?php
require_once '../../includes/config.php';

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

submitBtn.addEventListener('click', function(e) {
    e.preventDefault();

    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    const formData = new FormData(form);

    // Add country code to phone number
    const countryCode = document.querySelector('.country-flag-dropdown').textContent.trim().split(' ')[0];
    formData.set('customer_mobile', countryCode + formData.get('customer_mobile'));

    // Generate unique submission_id
    const submissionId = 'lead_' + Date.now();
    formData.set('submission_id', submissionId);

    // Submit form
    submitLead(formData);
});


// Add duplicate submission prevention
$submission_id = $_POST['submission_id'] ?? '';
if (empty($submission_id)) {
    die(json_encode([
        'status' => 'error',
        'message' => 'Missing submission ID'
    ]));
}

// Check if this submission was already processed
$checkQuery = "SELECT id FROM leads WHERE submission_id = ?";
$stmt = $conn->prepare($checkQuery);
$stmt->bind_param('s', $submission_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    die(json_encode([
        'status' => 'error',
        'message' => 'This lead was already submitted'
    ]));
}

try {
    // Get POST data
    $data = [
        'name' => $_POST['customer_name'] ?? '',
        'email' => $_POST['email'] ?? null,
        'phone' => $_POST['customer_mobile'] ?? '',
        'company' => $_POST['company_name'] ?? null,
        'address' => $_POST['address'] ?? null,
        'notes' => $_POST['comment'] ?? null,
        'reference' => $_POST['reference'] ?? null,
        'assigned_to' => $_POST['user_id'] ?? $_SESSION['user_id'],
        'submission_id' => $submission_id
    ];

    // Get status ID from status name
    $statusName = $_POST['status'] ?? 'New';
    $statusQuery = "SELECT id FROM lead_status_types WHERE name = ?";
    $stmt = $conn->prepare($statusQuery);
    $stmt->bind_param('s', $statusName);
    $stmt->execute();
    $statusResult = $stmt->get_result();
    $statusRow = $statusResult->fetch_assoc();
    $data['status_id'] = $statusRow['id'];

    // Get source ID from source name
    $sourceName = $_POST['source'] ?? 'Online';
    $sourceQuery = "SELECT id FROM lead_sources WHERE name = ?";
    $stmt = $conn->prepare($sourceQuery);
    $stmt->bind_param('s', $sourceName);
    $stmt->execute();
    $sourceResult = $stmt->get_result();
    $sourceRow = $sourceResult->fetch_assoc();
    $data['source_id'] = $sourceRow['id'];

    // Prepare insert query
    $insertQuery = "INSERT INTO leads (name, email, phone, company, address, source_id, status_id, assigned_to, notes, reference, submission_id) 
                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($insertQuery);
    $stmt->bind_param(
        'sssssiiiiss',
        $data['name'],
        $data['email'],
        $data['phone'],
        $data['company'],
        $data['address'],
        $data['source_id'],
        $data['status_id'],
        $data['assigned_to'],
        $data['notes'],
        $data['reference'],
        $data['submission_id']
    );

    // Execute the query
    if ($stmt->execute()) {
        $leadId = $stmt->insert_id;

        // Get the newly inserted lead data
        $newLeadQuery = "
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
            WHERE l.id = ?";
        
        $stmt = $conn->prepare($newLeadQuery);
        $stmt->bind_param('i', $leadId);
        $stmt->execute();
        $result = $stmt->get_result();
        $newLead = $result->fetch_assoc();

        echo json_encode([
            'status' => 'success',
            'message' => 'Lead added successfully',
            'lead' => $newLead
        ]);
    } else {
        throw new Exception("Failed to insert lead");
    }

} catch (Exception $e) {
    error_log("Error saving lead: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to save lead'
    ]);
} 