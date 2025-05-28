<?php
// Start session if not already started - MUST be the first thing
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include configuration
require_once '../includes/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . SITE_URL . '/login.php');
    exit();
}

// Fetch greetings data (placeholder for now)
$greetings = [];
// In a real implementation, you would fetch this data from the database
// Example: $greetings = fetchGreetingsData($_SESSION['user_id']);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Greetings Management</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/dashboard_style.css">
    <style>
        :root {
            --primary-color: #6366f1;
            --primary-dark: #4f46e5;
            --secondary-color: #f8fafc;
            --border-color: #e2e8f0;
            --text-primary: #1e293b;
            --text-secondary: #64748b;
            --success-color: #10b981;
            --danger-color: #ef4444;
            --warning-color: #f59e0b;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #f8fafc;
            color: var(--text-primary);
            line-height: 1.6;
        }

        /* Greetings Page Specific Styles */
        .greetings-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            padding: 24px;
            margin-bottom: 24px;
        }

        .greetings-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            flex-wrap: wrap;
            gap: 16px;
        }

        .greetings-title {
            font-size: 24px;
            font-weight: 600;
            color: var(--text-primary);
            margin: 0;
        }

        .greetings-filters {
            display: flex;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
        }

        .filter-group {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .filter-label {
            font-size: 14px;
            font-weight: 500;
            color: var(--text-secondary);
            margin: 0;
        }

        .filter-select {
            padding: 8px 16px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            background-color: white;
            color: var(--text-primary);
            font-size: 14px;
            min-width: 120px;
        }

        .search-box {
            position: relative;
        }

        .search-input {
            padding: 8px 16px 8px 40px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            background-color: white;
            color: var(--text-primary);
            font-size: 14px;
            width: 240px;
        }

        .search-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-secondary);
        }

        .action-buttons {
            display: flex;
            gap: 8px;
        }

        .action-btn {
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: var(--primary-color);
            color: white;
            border: none;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .action-btn:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
        }

        .action-btn.delete-btn {
            background-color: var(--danger-color);
        }

        .action-btn.delete-btn:hover {
            background-color: #dc2626;
        }

        .greetings-content {
            background-color: white;
            border-radius: 8px;
            min-height: 200px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-secondary);
            font-size: 16px;
        }

        /* Modal Styles */
        .modal-header {
            background-color: var(--primary-color);
            color: white;
            border-bottom: none;
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
            padding: 16px 24px;
        }

        .modal-title {
            font-weight: 600;
            font-size: 18px;
        }

        .modal-header .btn-close {
            color: white;
            opacity: 1;
            text-shadow: none;
            box-shadow: none;
        }

        .modal-body {
            padding: 24px;
        }

        .modal-footer {
            border-top: none;
            padding: 16px 24px 24px;
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            font-weight: 500;
            padding: 8px 24px;
            border-radius: 8px;
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
        }

        .btn-secondary {
            background-color: #94a3b8;
            border-color: #94a3b8;
            font-weight: 500;
            padding: 8px 24px;
            border-radius: 8px;
        }

        .btn-secondary:hover {
            background-color: #64748b;
            border-color: #64748b;
        }

        /* Date Range Modal Styles */
        .date-range-sidebar {
            background-color: #f8fafc;
            border-right: 1px solid var(--border-color);
            padding: 16px;
        }

        .date-option {
            padding: 10px 16px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
            margin-bottom: 8px;
            font-size: 14px;
            color: var(--text-primary);
        }

        .date-option:hover {
            background-color: #e2e8f0;
        }

        .date-option.active {
            background-color: var(--primary-color);
            color: white;
        }

        .custom-range-input {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-top: 16px;
            padding: 8px;
            border-top: 1px solid var(--border-color);
        }

        .custom-range-input input {
            width: 50px;
            text-align: center;
            padding: 4px;
            border: 1px solid var(--border-color);
            border-radius: 4px;
        }

        .calendar-container {
            padding: 16px;
        }

        .date-input {
            padding: 10px 16px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            text-align: center;
            margin-bottom: 16px;
            width: 100%;
        }

        .month-navigation {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
        }

        .month-selector {
            display: flex;
            gap: 8px;
        }

        .month-selector select {
            padding: 4px 8px;
            border: 1px solid var(--border-color);
            border-radius: 4px;
        }

        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 4px;
        }

        .calendar-header {
            text-align: center;
            font-weight: 500;
            color: var(--text-secondary);
            font-size: 12px;
            padding: 8px 0;
        }

        .calendar-day {
            aspect-ratio: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.2s ease;
        }

        .calendar-day:hover {
            background-color: #e2e8f0;
        }

        .calendar-day.active {
            background-color: var(--primary-color);
            color: white;
        }

        .calendar-day.disabled {
            color: #cbd5e1;
            cursor: not-allowed;
        }

        /* Import Modal Styles */
        .download-btn {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 8px;
            font-weight: 500;
            margin-bottom: 16px;
            transition: all 0.2s ease;
        }

        .download-btn:hover {
            background-color: var(--primary-dark);
        }

        .notes-section {
            margin-bottom: 24px;
        }

        .notes-title {
            font-size: 14px;
            font-weight: 600;
            color: #dc2626;
            margin-bottom: 8px;
        }

        .notes-list {
            list-style: none;
            padding-left: 16px;
            margin: 0;
        }

        .notes-list li {
            position: relative;
            padding-left: 16px;
            margin-bottom: 4px;
            font-size: 13px;
            color: #dc2626;
        }

        .notes-list li::before {
            content: '◦';
            position: absolute;
            left: 0;
            color: #dc2626;
        }

        .toggle-group {
            display: flex;
            justify-content: space-between;
            margin-bottom: 24px;
        }

        .toggle-label {
            font-size: 14px;
            font-weight: 500;
        }

        .form-switch {
            padding-left: 2.5em;
        }

        .form-check-input:checked {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .file-upload-section {
            margin-bottom: 24px;
        }

        .file-upload-label {
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 8px;
            display: block;
        }

        .file-upload-btn {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .file-upload-btn:hover {
            background-color: var(--primary-dark);
        }

        .file-name {
            margin-left: 8px;
            font-size: 14px;
            color: var(--text-secondary);
        }

        /* Add Greetings Modal Styles */
        .user-type-selection {
            display: flex;
            gap: 24px;
            margin-bottom: 24px;
        }

        .form-check-label {
            font-size: 14px;
            font-weight: 500;
        }

        .form-group {
            margin-bottom: 16px;
        }

        .form-label {
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 8px;
            display: block;
        }

        .form-control {
            padding: 10px 16px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-size: 14px;
        }

        /* Alert Modal Styles */
        .alert-modal .modal-body {
            text-align: center;
            padding: 32px;
        }

        .alert-icon {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            background-color: rgba(245, 158, 11, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
            color: var(--warning-color);
            font-size: 32px;
        }

        .alert-message {
            font-size: 18px;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 24px;
        }

        .alert-btn {
            background-color: var(--warning-color);
            color: white;
            border: none;
            padding: 8px 32px;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .alert-btn:hover {
            background-color: #d97706;
        }

        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .greetings-container {
                padding: 16px;
                margin-bottom: 16px;
            }
            
            .greetings-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
                margin-bottom: 16px;
            }

            .greetings-title {
                font-size: 20px;
            }

            .greetings-filters {
                width: 100%;
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
            }
            
            .filter-group {
                width: 100%;
                justify-content: space-between;
            }
            
            .filter-select {
                flex-grow: 1;
                max-width: 70%;
            }

            .search-box {
                width: 100%;
            }
            
            .search-input {
                width: 100%;
            }

            .action-buttons {
                width: 100%;
                justify-content: space-between;
            }
            
            /* Date Range Modal Mobile Adjustments */
            .modal-dialog.modal-lg {
                max-width: 100%;
                margin: 0.5rem;
            }
            
            .date-range-sidebar {
                padding: 12px;
            }
            
            .date-option {
                padding: 8px 12px;
                font-size: 13px;
            }
            
            .calendar-container {
                padding: 12px;
            }
            
            .row.mb-4 {
                margin-bottom: 0 !important;
            }
            
            .col-md-6 {
                margin-bottom: 16px;
            }
            
            .calendar-grid {
                gap: 2px;
            }
            
            .calendar-header {
                font-size: 10px;
            }
            
            .calendar-day {
                font-size: 12px;
            }
            
            /* Import Modal Mobile Adjustments */
            .notes-list li {
                font-size: 12px;
            }
            
            /* Add Greetings Modal Mobile Adjustments */
            .user-type-selection {
                gap: 16px;
            }
            
            .form-group {
                margin-bottom: 12px;
            }
        }
        
        /* Small Mobile Devices */
        @media (max-width: 480px) {
            .action-buttons {
                gap: 4px;
            }
            
            .action-btn {
                width: 36px;
                height: 36px;
            }
            
            .modal-body {
                padding: 16px;
            }
            
            .modal-footer {
                padding: 12px 16px;
            }
            
            .btn {
                padding: 6px 12px;
                font-size: 14px;
            }
        }
    </style>
</head>

<body>

<div class="dashboard-container">
    <div class="row g-0">
        <!-- Mobile Toggle Button -->
        <button class="sidebar-toggle d-md-none" id="sidebarToggle">
            <i class="fas fa-bars"></i>
        </button>
        <!-- Mobile Overlay -->
        <div class="sidebar-overlay" id="sidebarOverlay"></div>
        <!-- Sidebar -->
        <div class="col-md-3 col-lg-2 sidebar" id="sidebarMenu">
            <?php include '../includes/sidebar.php'; ?>
        </div>

        <!-- Main Content Area -->
        <div class="col-md-9 col-lg-10 main-content-area">
            <!-- Header -->
            <?php include '../includes/dashboard-header.php'; ?>

            <!-- Main Content -->
            <div class="container-fluid py-4">
                <!-- Greetings Management System -->
                <div class="greetings-container">
                    <!-- Header Section -->
                    <div class="greetings-header">
                        <h1 class="greetings-title">Greetings</h1>
                        
                        <div class="greetings-filters">
                            <!-- Type Filter -->
                            <div class="filter-group">
                                <label class="filter-label">Type</label>
                                <select class="filter-select" id="typeFilter">
                                    <option value="all">All</option>
                                    <option value="birthday">Birthday</option>
                                    <option value="anniversary">Anniversary</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            
                            <!-- Search Box -->
                            <div class="search-box">
                                <i class="fas fa-search search-icon"></i>
                                <input type="text" class="search-input" placeholder="Search..." id="searchInput">
                            </div>
                        </div>
                        
                        <!-- Action Buttons -->
                        <div class="action-buttons">
                            <button type="button" class="action-btn" id="dateRangeBtn" title="Date Range">
                                <i class="fas fa-calendar-alt"></i>
                            </button>
                            <button type="button" class="action-btn" id="uploadBtn" title="Import">
                                <i class="fas fa-upload"></i>
                            </button>
                            <button type="button" class="action-btn" id="addNewBtn" title="Add Greeting">
                                <i class="fas fa-plus"></i>
                            </button>
                            <button type="button" class="action-btn delete-btn" id="deleteBtn" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Content Area -->
                    <div class="greetings-content" id="greetingsContent">
                        <p>There are no records to display</p>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <?php include '../includes/dashboard-footer.php'; ?>
        </div>
    </div>
</div>

<!-- MODAL COMPONENTS -->

<!-- 1. Date Range Modal -->
<div class="modal fade" id="dateRangeModal" tabindex="-1" aria-labelledby="dateRangeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="dateRangeModalLabel">Date Range</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div class="row g-0">
                    <!-- Left Sidebar Options -->
                    <div class="col-md-4 col-12 date-range-sidebar">
                        <div class="date-option active" data-option="today">Today</div>
                        <div class="date-option" data-option="yesterday">Yesterday</div>
                        <div class="date-option" data-option="this-week">This Week</div>
                        <div class="date-option" data-option="last-week">Last Week</div>
                        <div class="date-option" data-option="this-month">This Month</div>
                        <div class="date-option" data-option="last-month">Last Month</div>
                        
                        <!-- Custom Range Inputs -->
                        <div class="custom-range-input">
                            <input type="number" id="daysUpToToday" value="1" min="1" max="365">
                            <span>days up to today</span>
                        </div>
                        <div class="custom-range-input">
                            <input type="number" id="daysStartingToday" value="1" min="1" max="365">
                            <span>days starting today</span>
                        </div>
                    </div>
                    
                    <!-- Date Picker Section -->
                    <div class="col-md-8 col-12 calendar-container">
                        <!-- Date Input Fields -->
                        <div class="row">
                            <div class="col-md-6 col-6 mb-2">
                                <input type="text" class="date-input" id="startDate" value="May 28, 2025" readonly>
                            </div>
                            <div class="col-md-6 col-6 mb-2">
                                <input type="text" class="date-input" id="endDate" value="May 28, 2025" readonly>
                            </div>
                        </div>
                        
                        <!-- Month Navigation -->
                        <div class="month-navigation">
                            <button class="btn btn-sm btn-outline-secondary" id="prevMonth">
                                <i class="fas fa-chevron-left"></i>
                            </button>
                            
                            <div class="month-selector">
                                <select id="monthSelect" class="form-select form-select-sm">
                                    <option value="0">January</option>
                                    <option value="1">February</option>
                                    <option value="2">March</option>
                                    <option value="3">April</option>
                                    <option value="4">May</option>
                                    <option value="5">June</option>
                                    <option value="6">July</option>
                                    <option value="7">August</option>
                                    <option value="8">September</option>
                                    <option value="9">October</option>
                                    <option value="10">November</option>
                                    <option value="11">December</option>
                                </select>
                                
                                <select id="yearSelect" class="form-select form-select-sm">
                                    <option value="2023">2023</option>
                                    <option value="2024">2024</option>
                                    <option value="2025">2025</option>
                                    <option value="2026">2026</option>
                                    <option value="2027">2027</option>
                                </select>
                            </div>
                            
                            <button class="btn btn-sm btn-outline-secondary" id="nextMonth">
                                <i class="fas fa-chevron-right"></i>
                            </button>
                        </div>
                        
                        <!-- Calendar Grid - Current Month -->
                        <div class="row mb-4">
                            <div class="col-md-6 col-12">
                                <h6 class="text-center mb-2">May 2025</h6>
                                <div class="calendar-grid" id="currentMonthCalendar">
                                    <!-- Calendar Headers -->
                                    <div class="calendar-header">S</div>
                                    <div class="calendar-header">M</div>
                                    <div class="calendar-header">T</div>
                                    <div class="calendar-header">W</div>
                                    <div class="calendar-header">T</div>
                                    <div class="calendar-header">F</div>
                                    <div class="calendar-header">S</div>
                                    
                                    <!-- Calendar Days (Example) -->
                                    <!-- First row (partial) -->
                                    <div class="calendar-day disabled">27</div>
                                    <div class="calendar-day disabled">28</div>
                                    <div class="calendar-day disabled">29</div>
                                    <div class="calendar-day disabled">30</div>
                                    <div class="calendar-day">1</div>
                                    <div class="calendar-day">2</div>
                                    <div class="calendar-day">3</div>
                                    
                                    <!-- Second row -->
                                    <div class="calendar-day">4</div>
                                    <div class="calendar-day">5</div>
                                    <div class="calendar-day">6</div>
                                    <div class="calendar-day">7</div>
                                    <div class="calendar-day">8</div>
                                    <div class="calendar-day">9</div>
                                    <div class="calendar-day">10</div>
                                    
                                    <!-- More rows would be dynamically generated -->
                                    <!-- Last row showing the currently selected date -->
                                    <div class="calendar-day">25</div>
                                    <div class="calendar-day">26</div>
                                    <div class="calendar-day">27</div>
                                    <div class="calendar-day active">28</div>
                                    <div class="calendar-day">29</div>
                                    <div class="calendar-day">30</div>
                                    <div class="calendar-day">31</div>
                                </div>
                            </div>
                            
                            <div class="col-md-6 col-12 mt-3 mt-md-0">
                                <h6 class="text-center mb-2">Jun 2025</h6>
                                <div class="calendar-grid" id="nextMonthCalendar">
                                    <!-- Calendar Headers -->
                                    <div class="calendar-header">S</div>
                                    <div class="calendar-header">M</div>
                                    <div class="calendar-header">T</div>
                                    <div class="calendar-header">W</div>
                                    <div class="calendar-header">T</div>
                                    <div class="calendar-header">F</div>
                                    <div class="calendar-header">S</div>
                                    
                                    <!-- Calendar Days (Example) -->
                                    <!-- First row (partial) -->
                                    <div class="calendar-day">1</div>
                                    <div class="calendar-day">2</div>
                                    <div class="calendar-day">3</div>
                                    <div class="calendar-day">4</div>
                                    <div class="calendar-day">5</div>
                                    <div class="calendar-day">6</div>
                                    <div class="calendar-day">7</div>
                                    
                                    <!-- More rows would be dynamically generated -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="submitDateRange">Submit</button>
            </div>
        </div>
    </div>
</div>

<!-- 2. Import Greetings Users Modal - Disabled -->
<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false" style="display: none !important; visibility: hidden !important;">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importModalLabel">Import Greetings Users</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Download Format Button -->
                <button type="button" class="download-btn" id="downloadFormat">
                    Download Format
                </button>
                
                <!-- Notes Section -->
                <div class="notes-section">
                    <div class="notes-title">Notes:-</div>
                    <ul class="notes-list">
                        <li>Anniversary and Birth dates entered in this format. DD-MM-YYYY.</li>
                        <li>Other Type Greetings dates entered in this format. DD-MM-YYYY HH:mm.</li>
                        <li>Birth and Anniversary dates cannot be future dates.</li>
                        <li>Country code is required with Mobile numbers.</li>
                    </ul>
                </div>
                
                <!-- Greeting Type Selection -->
                <div class="toggle-group">
                    <div class="toggle-label">Birthday:</div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="birthdayToggle">
                    </div>
                </div>
                
                <div class="toggle-group">
                    <div class="toggle-label">Anniversary:</div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="anniversaryToggle">
                    </div>
                </div>
                
                <div class="toggle-group">
                    <div class="toggle-label">Other:</div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="otherToggle">
                    </div>
                </div>
                
                <!-- File Upload -->
                <div class="file-upload-section">
                    <label class="file-upload-label">Upload File: <span class="text-danger">*</span></label>
                    <div class="d-flex align-items-center">
                        <input type="file" id="fileUpload" class="d-none">
                        <button type="button" class="file-upload-btn" id="fileUploadBtn">Choose File</button>
                        <span class="file-name" id="fileName">No file chosen</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="importBtn">Import</button>
            </div>
        </div>
    </div>
</div>

<!-- 3. Add Greetings Modal -->
<div class="modal fade" id="addGreetingModal" tabindex="-1" aria-labelledby="addGreetingModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addGreetingModalLabel">Add Greetings</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- User Type Selection -->
                <div class="user-type-selection">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="userType" id="customerType" checked>
                        <label class="form-check-label" for="customerType">Customer</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="userType" id="otherType">
                        <label class="form-check-label" for="otherType">Other</label>
                    </div>
                </div>
                
                <!-- Greeting Type Selection -->
                <div class="toggle-group">
                    <div class="toggle-label">Birthday:</div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="addBirthdayToggle">
                    </div>
                </div>
                
                <div class="toggle-group">
                    <div class="toggle-label">Anniversary:</div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="addAnniversaryToggle">
                    </div>
                </div>
                
                <div class="toggle-group">
                    <div class="toggle-label">Other:</div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="addOtherToggle">
                    </div>
                </div>
                
                <!-- Form Fields -->
                <div class="form-group">
                    <label for="customerSelect" class="form-label">Customer: <span class="text-danger">*</span></label>
                    <select class="form-control" id="customerSelect">
                        <option value="">Select Customer</option>
                        <!-- Options would be populated dynamically -->
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="contactNo" class="form-label">Contact No:</label>
                    <input type="text" class="form-control" id="contactNo" placeholder="Contact No">
                </div>
                
                <div class="form-group">
                    <label for="companyName" class="form-label">Company Name:</label>
                    <input type="text" class="form-control" id="companyName" placeholder="Company Name">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="submitGreeting">Submit</button>
            </div>
        </div>
    </div>
</div>

<!-- 4. Delete Confirmation Modal -->
<div class="modal fade alert-modal" id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body">
                <div class="alert-icon">
                    <i class="fas fa-exclamation"></i>
                </div>
                <div class="alert-message">Please Select Greetings</div>
                <button type="button" class="alert-btn" data-bs-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap 5 JS and dependencies -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>

<!-- Custom JavaScript for Greetings Management System -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Bootstrap modals - except for importModal which we want to disable
        const dateRangeModal = new bootstrap.Modal(document.getElementById('dateRangeModal'));
        // Intentionally not initializing importModal to prevent it from showing
        // const importModal = new bootstrap.Modal(document.getElementById('importModal'));
        const addGreetingModal = new bootstrap.Modal(document.getElementById('addGreetingModal'));
        const deleteConfirmModal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
        
        // Prevent the importModal from being shown by any means
        const importModalElement = document.getElementById('importModal');
        importModalElement.addEventListener('show.bs.modal', function(event) {
            // Prevent the modal from being shown
            event.preventDefault();
            event.stopPropagation();
            return false;
        });
        
        // Button click handlers - all 4 buttons
        
        // 1. Date Range Button (already working)
        document.getElementById('dateRangeBtn').addEventListener('click', function() {
            dateRangeModal.show();
            console.log('Date Range button clicked');
        });
        
        // 2. Upload Button
        document.getElementById('uploadBtn').addEventListener('click', function() {
            // Custom functionality for upload button
            console.log('Upload button clicked');
            // Show a notification instead of modal
            showNotification('Upload feature', 'Upload functionality will be implemented soon');
        });
        
        // 3. Add New Button
        document.getElementById('addNewBtn').addEventListener('click', function() {
            // Custom functionality for add new button
            console.log('Add New button clicked');
            // Show a notification instead of modal
            showNotification('Add greeting', 'Add greeting functionality will be implemented soon');
        });
        
        // 4. Delete Button
        document.getElementById('deleteBtn').addEventListener('click', function() {
            // Show delete confirmation modal
            deleteConfirmModal.show();
            console.log('Delete button clicked');
        });
        
        // Helper function to show notifications
        function showNotification(title, message) {
            // Create notification element
            const notification = document.createElement('div');
            notification.className = 'notification';
            notification.innerHTML = `
                <div class="notification-header">
                    <h5>${title}</h5>
                    <button type="button" class="close-notification">&times;</button>
                </div>
                <div class="notification-body">
                    <p>${message}</p>
                </div>
            `;
            
            // Add to document
            document.body.appendChild(notification);
            
            // Show with animation
            setTimeout(() => notification.classList.add('show'), 10);
            
            // Add close button functionality
            notification.querySelector('.close-notification').addEventListener('click', function() {
                notification.classList.remove('show');
                setTimeout(() => notification.remove(), 300);
            });
            
            // Auto-close after 3 seconds
            setTimeout(() => {
                if (document.body.contains(notification)) {
                    notification.classList.remove('show');
                    setTimeout(() => notification.remove(), 300);
                }
            }, 3000);
        }
        
        // CSS for notifications
        const notificationStyle = document.createElement('style');
        notificationStyle.textContent = `
            .notification {
                position: fixed;
                top: 20px;
                right: 20px;
                background: white;
                border-left: 4px solid #6366f1;
                border-radius: 4px;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
                width: 300px;
                max-width: 90%;
                z-index: 9999;
                overflow: hidden;
                transform: translateX(110%);
                transition: transform 0.3s ease;
            }
            
            .notification.show {
                transform: translateX(0);
            }
            
            .notification-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 12px 15px;
                background: #f8fafc;
                border-bottom: 1px solid #e2e8f0;
            }
            
            .notification-header h5 {
                margin: 0;
                font-size: 16px;
                font-weight: 600;
                color: #1e293b;
            }
            
            .close-notification {
                background: none;
                border: none;
                font-size: 20px;
                line-height: 1;
                color: #64748b;
                cursor: pointer;
            }
            
            .notification-body {
                padding: 15px;
            }
            
            .notification-body p {
                margin: 0;
                color: #334155;
            }
        `;
        document.head.appendChild(notificationStyle);
        
        // Import button in modal
        document.querySelector('.modal-footer #importBtn').addEventListener('click', function() {
            // Handle import submission
            console.log('Importing data...');
            // Validate and process the import
            const birthdaySelected = document.getElementById('birthdayToggle').checked;
            const anniversarySelected = document.getElementById('anniversaryToggle').checked;
            const otherSelected = document.getElementById('otherToggle').checked;
            const fileInput = document.getElementById('fileUpload');
            
            if (!birthdaySelected && !anniversarySelected && !otherSelected) {
                alert('Please select at least one greeting type');
                return;
            }
            
            if (!fileInput.files || fileInput.files.length === 0) {
                alert('Please select a file to upload');
                return;
            }
            
            // In a real implementation, you would submit the form data to the server
            alert('Import successful!');
            importModal.hide();
        });
        
        // Submit greeting button in add modal
        document.getElementById('submitGreeting').addEventListener('click', function() {
            // Handle add greeting submission
            console.log('Adding greeting...');
            // Validate and process the new greeting
            const customerType = document.getElementById('customerType').checked;
            const customerSelect = document.getElementById('customerSelect');
            
            if (customerType && (!customerSelect.value || customerSelect.value === '')) {
                alert('Please select a customer');
                return;
            }
            
            // In a real implementation, you would submit the form data to the server
            alert('Greeting added successfully!');
            addGreetingModal.hide();
        });
        
        // File upload handling
        document.getElementById('fileUploadBtn').addEventListener('click', function() {
            document.getElementById('fileUpload').click();
        });
        
        document.getElementById('fileUpload').addEventListener('change', function(e) {
            const fileName = e.target.files[0] ? e.target.files[0].name : 'No file chosen';
            document.getElementById('fileName').textContent = fileName;
        });
        
        // Date range selection handlers
        const dateOptions = document.querySelectorAll('.date-option');
        dateOptions.forEach(option => {
            option.addEventListener('click', function() {
                // Remove active class from all options
                dateOptions.forEach(opt => opt.classList.remove('active'));
                // Add active class to clicked option
                this.classList.add('active');
                
                // In a real implementation, you would update the date inputs based on the selection
                console.log('Selected date option:', this.dataset.option);
            });
        });
        
        // Calendar day selection
        const calendarDays = document.querySelectorAll('.calendar-day:not(.disabled)');
        calendarDays.forEach(day => {
            day.addEventListener('click', function() {
                // In a real implementation, you would handle date selection logic
                // For now, we'll just toggle the active class
                this.classList.toggle('active');
            });
        });
        
        // Type filter change handler
        document.getElementById('typeFilter').addEventListener('change', function() {
            const selectedType = this.value;
            console.log('Filtering by type:', selectedType);
            // In a real implementation, you would filter the displayed greetings
        });
        
        // Search input handler
        document.getElementById('searchInput').addEventListener('input', function() {
            const searchTerm = this.value.trim().toLowerCase();
            console.log('Searching for:', searchTerm);
            // In a real implementation, you would filter the displayed greetings based on the search term
        });
    });
</script>

</body>
</html>