<?php
require_once '../includes/config.php';

header('Content-Type: application/json');

// Basic validation (optional, depending on if this endpoint should be public)
if (!isLoggedIn()) {
    // Optionally return an error or empty data if user is not logged in
    // http_response_code(401); // Uncomment for unauthorized access
    // echo json_encode(['error' => 'Authentication required']);
    // exit();
}

// Get filter parameters (for future use)
$startDate = $_POST['startDate'] ?? null;
$endDate = $_POST['endDate'] ?? null;
$userId = $_POST['userId'] ?? null;

// --- Placeholder Data (Replace with Database Queries) ---

// Example data structure for Lead Status
$leadStatusData = [
    'labels' => ['New', 'Processing', 'Close-by', 'Confirm', 'Cancel'],
    'data' => [50, 20, 15, 10, 5], // Example counts
    'backgroundColors' => ['#007bff', '#28a745', '#ffc107', '#dc3545', '#6f42c1'], // Matching colors
    'total' => 100 // Sum of data
];

// Example data structure for Lead Source
$leadSourceData = [
    'labels' => ['Online', 'Offline', 'Website', 'Whatsapp', 'Customer Reminder', 'Indiamart', 'Facebook', 'Google Form'],
    'data' => [30, 15, 20, 10, 5, 10, 5, 5], // Example counts
    'backgroundColors' => ['#007bff', '#28a745', '#ffc107', '#dc3545', '#6610f2', '#6f42c1', '#e83e8c', '#20c997'], // Matching colors
    'total' => 100 // Sum of data
];

// Example User Data (for populating the filter dropdown)
$users = [
    ['id' => 1, 'name' => 'Kavan Patel'],
    ['id' => 2, 'name' => 'Jane Doe'],
    ['id' => 3, 'name' => 'John Smith']
];

// --- Combine data for response ---

$response = [
    'leadStatus' => $leadStatusData,
    'leadSource' => $leadSourceData,
    'users' => $users
];

echo json_encode($response);

?> 