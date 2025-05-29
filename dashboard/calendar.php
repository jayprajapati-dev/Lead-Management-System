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

// Include necessary functions or data fetching if needed for initial load
// For example, fetching a list of users for an assignee dropdown in the modal

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendar - Lead Management System</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Custom Dashboard CSS -->
    <link rel="stylesheet" href="css/dashboard_style.css">
    <!-- Google Fonts (Poppins) -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f5f5; /* Light gray background */
            min-height: 100vh;
            margin: 0; /* Reset default body margin */
            padding: 0; /* Reset default body padding */
        }

        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }

         /* Left Navigation Sidebar - Styling should primarily come from includes/sidebar.php */
        /* Added basic flex properties here for overall container layout */
        /* The fixed positioning and width are expected to be handled by includes/sidebar.php */
        .left-sidebar {
             flex-shrink: 0; /* Prevent shrinking */
             /* Example styles, adjust based on actual sidebar.php */
             width: 250px; /* Assuming sidebar.php is 250px wide */
             position: fixed;
             top: 0;
             bottom: 0;
             left: 0;
             z-index: 1000;
             background-color: #fff;
             box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
             overflow-y: auto; /* Add scrolling to sidebar */
        }

         /* Main Content Area - Takes remaining space to the right of the left sidebar */
        .main-content-area {
            flex-grow: 1;
            display: flex; /* Use flexbox for internal layout */
            flex-direction: column; /* Stack header and content vertically */
            margin-left: 250px; /* Adjust based on actual left sidebar width */
             min-height: calc(100vh - 0px); /* Ensure main content area is at least viewport height minus header/footer if they were fixed/of known height - adjust as needed */
        }

         /* Fixed Header within Main Content Area */
        .fixed-header {
             width: calc(100% - 250px); /* Take full width minus left sidebar width */
             background-color: #fff; /* Add background to header */
             box-shadow: 0 2px 4px rgba(0,0,0,0.05); /* Subtle shadow */
             height: 60px; /* Fixed height */
             position: fixed; /* Fix header position */
             top: 0; /* Align to top */
             left: 250px; /* Align to the right of the left sidebar */
             z-index: 900; /* Below left sidebar */
             padding: 0 15px; /* Keep some horizontal padding */
             display: flex; /* Use flex for header content alignment */
             justify-content: space-between;
             align-items: center;
        }

        /* Ensure the right-side header content fills space and aligns right */
        .fixed-header .d-flex.ms-auto {
            flex-grow: 1; /* Allow this div to grow and take available space */
            justify-content: flex-end; /* Align content to the right within this div */
            /* Remove any potential extra space on the right of this container */
             padding-right: 0; 
             margin-right: 0; 
        }

        /* Adjust spacing of items within the right-side header */
        .fixed-header .d-flex.ms-auto .nav-link, /* Search and Notification links */
        .fixed-header .d-flex.ms-auto .dropdown /* User Profile dropdown */
        {
             margin-left: 15px; /* Add some space between items */
             margin-right: 0 !important; /* Ensure no right margin on these items */
             padding-right: 0 !important; /* Ensure no right padding on these items */
        }

        /* Adjust margin for the last item before the right edge if necessary */
        /* This rule might be redundant with the one above but kept for safety */
        .fixed-header .d-flex.ms-auto > *:last-child {
            margin-right: 0 !important; /* Remove right margin from the last direct child */
        }

         /* Content Area Below Fixed Header */
        .content-below-header {
            flex-grow: 1; /* Takes remaining vertical space */
            display: flex; /* Use flexbox for calendar grid and right sidebar */
            padding-top: 60px; /* Add padding to avoid content hiding behind fixed header */
            flex-direction: row; /* Ensure horizontal layout on larger screens */
        }

         /* Calendar Main Area - Contains Calendar Grid and Right Filter Sidebar */
         .calendar-main-area {
             flex-grow: 1; /* Calendar grid takes remaining horizontal space */
             display: flex; /* Flex container for calendar header and content */
             flex-direction: column; /* Stack calendar header and content */
             padding: 15px;
             order: 2; /* Set order to 2 to place it on the right */
         }

         /* Calendar Content Area - Contains the month/day/list views */
         .calendar-content-area {
             flex-grow: 1; /* Calendar content takes remaining vertical space */
             background-color: #fff;
             border-radius: 15px;
             box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
             padding: 20px;
             overflow-y: auto; /* Add scrolling if calendar content is too tall */
             min-height: 400px; /* Add a minimum height to ensure space */
             display: flex; /* Use flex to manage views inside */
             flex-direction: column; /* Stack views vertically */
         }

         /* Right Filter Sidebar */
         .right-filter-sidebar {
             width: 280px; /* Fixed width for filter sidebar */
             flex-shrink: 0;
             background-color: #fff;
             box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1); /* Add shadow on the right side now */
             padding: 15px;
             overflow-y: auto; /* Add scrolling if filter content is too tall */
             order: 1; /* Set order to 1 to place it on the left */
         }

        @media (max-width: 767.98px) {
            /* Mobile adjustments - professional mobile experience */
            .dashboard-container {
                flex-direction: column; /* Stack sidebar and main content */
            }
            
            .left-sidebar {
                width: 100%; /* Full width on mobile */
                position: static; /* Don't fix position on mobile */
                height: auto; /* Auto height */
                box-shadow: none;
            }
            
            .main-content-area {
                margin-left: 0; /* Remove margin */
                width: 100%;
            }
            
            .fixed-header {
                position: fixed; /* Keep header fixed on mobile */
                left: 0;
                width: 100%;
                z-index: 1010;
            }
            
            .content-below-header {
                flex-direction: column; /* Stack calendar and right sidebar */
                padding-top: 110px; /* Add padding for fixed header and toggle button */
            }
            
            .calendar-main-area {
                width: 100%; /* Full width */
                padding: 10px;
            }
            
            /* Right filter sidebar - slide in panel */
            .right-filter-sidebar {
                position: fixed;
                top: 60px; /* Position below fixed header */
                right: -280px; /* Hide off-screen by default */
                width: 280px; /* Fixed width */
                height: calc(100vh - 60px); /* Full height minus header */
                background-color: #fff;
                z-index: 1050;
                transition: right 0.3s ease;
                box-shadow: -2px 0 10px rgba(0, 0, 0, 0.15);
                padding: 15px;
                overflow-y: auto;
            }
            
            .right-filter-sidebar.show {
                right: 0; /* Slide in when 'show' class is added */
            }
            
            /* Sidebar toggle button for mobile (left side) */
            .sidebar-toggle-btn {
                display: flex;
                align-items: center;
                justify-content: center;
                width: 40px;
                height: 40px;
                border-radius: 4px;
                background: #5B47B3;
                color: white;
                border: none;
                position: fixed;
                top: 15px;
                left: 15px;
                z-index: 1060;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
                transition: all 0.2s ease;
                font-size: 1.2rem;
            }
            
            .sidebar-toggle-btn:hover {
                background: #4a3a9c;
                transform: translateY(-2px);
                box-shadow: 0 6px 16px rgba(0, 0, 0, 0.25);
            }
            
            .sidebar-toggle-btn:active {
                transform: scale(0.95);
            }
            
            /* Filter toggle button for mobile */
            .filter-toggle-btn {
                display: flex;
                align-items: center;
                justify-content: center;
                width: 40px;
                height: 40px;
                border-radius: 4px;
                background: #5B47B3;
                color: white;
                border: none;
                position: fixed;
                top: 15px;
                right: 15px;
                z-index: 1060;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
                transition: all 0.2s ease;
                font-size: 1.2rem;
            }
            
            .filter-toggle-btn:hover {
                background: #4a3a9c;
                transform: translateY(-2px);
                box-shadow: 0 6px 16px rgba(0, 0, 0, 0.25);
            }
            
            .filter-toggle-btn:active {
                transform: scale(0.95);
            }
            
            /* Hide text labels on mobile, show only icons */
            .btn-text {
                display: none;
            }
            
            /* Left sidebar mobile adjustments */
            .left-sidebar .sidebar-content {
                display: none;
            }
            
            .left-sidebar.show .sidebar-content {
                display: block;
            }
            
            /* Mobile overlay for both sidebars */
            .sidebar-overlay {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.5);
                z-index: 1040;
                opacity: 0;
                transition: opacity 0.3s ease;
            }
            
            .sidebar-overlay.show {
                display: block;
                opacity: 1;
            }
        }

        /* Mobile Overlay */
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0,0,0,0.5);
            z-index: 999;
        }

        .sidebar-overlay.show {
            display: block;
        }

        /* Remove all other sidebar toggle related styles */
        /* Mobile sidebar toggle styles removed */
        /* Adjust positioning for the new mobile left sidebar toggle styles removed */

        /* Add Event Button Styling */
        .add-event-btn {
            background-color: #5B47B3; /* Purple */
            color: white;
            border: none;
            border-radius: 8px;
            padding: 10px 15px;
            width: 100%;
            text-align: center;
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 20px;
            transition: background-color 0.2s ease;
        }

        .add-event-btn:hover {
            background-color: #4a3a99; /* Darker purple */
            color: white; /* Keep text white on hover */
        }

        /* Filter Section Styling */
        .filter-section {
            margin-bottom: 20px;
        }

        .filter-section .filter-label {
            font-size: 0.8rem;
            color: #6c757d; /* Gray */
            text-transform: uppercase;
            margin-bottom: 10px;
            display: block;
        }

        .filter-checkboxes .form-check {
            margin-bottom: 8px;
            display: flex;
            align-items: center;
        }

        .filter-checkboxes .form-check-input {
            margin-right: 10px; /* Space between checkbox and label */
            border-radius: 4px; /* Subtle rounded corners */
            width: 1.2em;
            height: 1.2em;
            flex-shrink: 0;
             /* Ensure checkmark is visible */
            background-color: #e9ecef; /* Light background when unchecked */
            border: 1px solid #ced4da; /* Subtle border */
             appearance: none; /* Remove default browser styling */
             -webkit-appearance: none;
             vertical-align: middle; /* Align with text */
             cursor: pointer;
        }
         .form-check-input:checked {
            background-color: var(--checkbox-color);
            border-color: var(--checkbox-color);
             /* Ensure checkmark is white */
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20'%3e%3cpath fill='none' stroke='%23fff' stroke-linecap='round' stroke-linejoin='round' stroke-width='3' d='M6 10l3 3l6-6'/%3e%3c/svg%3e") !important;
            background-size: 100% 100%; /* Ensure checkmark fills the box */
            background-position: center; /* Center the checkmark */
            background-repeat: no-repeat; /* Prevent repeating */
         }

        /* Custom Checkbox Colors */
        .form-check-input[data-color="orange"] {
             --checkbox-color: #FF8C00; /* Specific orange */
        }
        .form-check-input[data-color="green"] {
             --checkbox-color: #00C896; /* Specific green */
        }
        .form-check-input[data-color="purple"] {
             --checkbox-color: #5B47B3; /* Specific purple */
        }
        .form-check-input[data-color="gray"] {
             --checkbox-color: #888888; /* Specific gray */
        }
        .form-check-label {
             cursor: pointer; /* Indicate it's clickable */
             vertical-align: middle; /* Align with checkbox */
        }

         /* Calendar Header Styling */
        /* Positioned relative to .calendar-main-area */
        .calendar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px; /* Space below header */
            padding: 0 15px;
             background-color: #fff; /* Add background to header */
             border-radius: 8px; /* Rounded corners */
             box-shadow: 0 2px 4px rgba(0,0,0,0.05); /* Subtle shadow */
             height: 60px; /* Fixed height */
        }


        .calendar-header .navigation-controls button {
             background: none;
             border: none;
             font-size: 1.2rem;
             cursor: pointer;
             margin: 0 5px;
             transition: color 0.2s ease; /* Smooth transition for hover */
        }
        .calendar-header .navigation-controls button:hover {
            color: #007bff; /* Highlight on hover */
        }

        .calendar-header .current-date {
            font-size: 1.1rem; /* Adjusted font size */
            font-weight: 600;
             margin: 0 10px; /* Space around date */
        }

        .calendar-header .view-toggle button {
            border: 1px solid #ced4da;
            background-color: #fff;
            color: #495057;
            padding: 5px 15px;
            cursor: pointer;
             transition: all 0.2s ease; /* Smooth transition */
        }

        .calendar-header .view-toggle button:first-child {
            border-top-left-radius: 5px;
            border-bottom-left-radius: 5px;
        }

        .calendar-header .view-toggle button:last-child {
             border-top-right-radius: 5px;
            border-bottom-right-radius: 5px;
             border-left: none; /* Remove double border */
        }

         .calendar-header .view-toggle button:not(:last-child) {
             border-right: none; /* Remove border between buttons */
         }

        .calendar-header .view-toggle button.active {
             background-color: #007bff;
             color: white;
             border-color: #007bff;
             z-index: 1; /* Bring active button to front to overlap border */
        }
        .calendar-header .view-toggle button:hover:not(.active) {
             background-color: #e9ecef; /* Subtle hover for inactive buttons */
        }

        /* Calendar Content Area */
        /* Background, border-radius, shadow, padding moved to .calendar-content-area */
        /* Removed .calendar-content background, etc. to avoid nested styles */
        .calendar-content {
            /* Placeholder for view-specific styling if needed */
        }

        .empty-state-message {
            text-align: center;
            color: #6c757d;
            padding: 50px 0;
        }

        /* Basic Month View Grid */
        .month-view .days-grid {
             display: grid;
             grid-template-columns: repeat(7, 1fr);
             gap: 1px;
             border: 1px solid #dee2e6;
             /* Ensure grid fits within container */
             width: 100%;
             overflow: hidden;
             flex-grow: 1; /* Allow grid to grow vertically */
             display: grid; /* Explicitly set display to grid */
         }
        .month-view .day-label, .month-view .day-cell {
             border: 1px solid #dee2e6;
             padding: 5px; /* Reduce padding slightly */
             min-height: 80px; /* Adjust minimum height */
             text-align: right;
             position: relative;
             background-color: #fff; /* Ensure cell background is white */
             overflow: hidden; /* Hide overflowing event text */
             text-overflow: ellipsis; /* Add ellipsis for overflow */
             white-space: nowrap; /* Prevent wrapping */
             font-size: 0.9rem; /* Adjust font size for day numbers/text */
         }
         .month-view .day-label {
             text-align: center;
             font-weight: 600;
             background-color: #e9ecef; /* Light gray background for labels */
             border-bottom: none;
             padding: 10px 5px; /* Keep vertical padding for labels */
             font-size: 1rem; /* Keep label font size */
         }
         .month-view .day-cell .day-number {
             font-size: 1.1rem; /* Slightly smaller day number */
             font-weight: 600;
             position: absolute;
             top: 5px;
             right: 5px;
         }
         /* Style for days from previous/next month */
         .month-view .day-cell.outside-month {
             color: #ced4da; /* Lighter color */
             background-color: #f8f9fa; /* Slightly different background */
         }
         /* Style for current day */
         .month-view .day-cell.current-day {
              border: 2px solid #007bff; /* Highlight current day */
         }

        /* Basic Day View */
        .day-view .time-slot {
            border-bottom: 1px solid #dee2e6;
            padding: 10px 0;
             min-height: 50px;
        }

        /* Basic List View */
        .list-view .event-item {
            border-bottom: 1px solid #dee2e6;
            padding: 10px 0;
        }

         /* Modal Styling */
         .modal-header {
             background-color: #5B47B3; /* Purple */
             color: white;
             border-top-left-radius: var(--bs-modal-inner-border-radius);
             border-top-right-radius: var(--bs-modal-inner-border-radius);
             padding: 15px;
         }
         .modal-header .btn-close {
             filter: invert(1) grayscale(100%) brightness(200%); /* Make close button white */
         }
         .modal-footer .btn-primary {
             background-color: #5B47B3;
             border-color: #5B47B3;
         }
         .modal-footer .btn-secondary {
             background-color: #6c757d;
             border-color: #6c757d;
         }

        /* Ensure view containers take up space */
        .month-view, .day-view, .list-view {
            flex-grow: 1; /* Allow views to take available space */
            display: flex; /* Use flex on views */
            flex-direction: column; /* Stack content inside views */
        }

        .day-view .time-slots, .list-view .event-list {
            flex-grow: 1; /* Allow content within views to grow */
        }

        /* --- Day View Specific Styles --- */
        .day-view-header {
            text-align: center;
            margin-bottom: 20px;
        }

        .day-view-header h4 {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .day-view-header p {
            font-size: 1.1rem;
            color: #6c757d;
            margin-bottom: 0;
        }

        .day-view-all-day {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 10px;
            margin-bottom: 20px;
            background-color: #e9ecef; /* Light background for all-day section */
        }

        .day-view-all-day .all-day-label {
            font-weight: 600;
            margin-right: 10px;
        }

        .day-view-all-day .all-day-events {
            /* Styles for events within all-day */
        }

        .day-view-hourly {
            flex-grow: 1; /* Make hourly section fill remaining space */
            overflow-y: auto; /* Enable vertical scrolling */
            border: 1px solid #dee2e6;
            border-radius: 8px;
             min-height: 300px; /* Ensure a minimum height for scrolling */
        }

        .day-view-hourly .time-slot {
            display: flex;
            border-bottom: 1px solid #eee;
            padding: 0; /* Remove padding from here, add to content */
            min-height: 50px; /* Keep minimum height */
             align-items: stretch; /* Stretch items to fill height */
        }

        .day-view-hourly .time-slot:last-child {
            border-bottom: none; /* Remove border from the last time slot */
        }

        .day-view-hourly .time-slot .time-label {
            width: 80px; /* Fixed width for time label column */
            flex-shrink: 0;
            text-align: right;
            padding: 10px; /* Padding within the time label column */
            border-right: 1px solid #eee;
            background-color: #f8f9fa; /* Light background for time labels */
            font-weight: 500;
            font-size: 0.9rem;
        }

        .day-view-hourly .time-slot .time-slot-content {
            flex-grow: 1; /* Allow content area to fill remaining width */
            padding: 10px; /* Padding within the content area */
            /* Styles for events within time slot */
        }

    </style>
</head>
<body>
    <!-- Mobile Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    
    <!-- Header first, outside the main container -->
    <?php include '../includes/dashboard-header.php'; ?>
    
    <div class="dashboard-container container-fluid">
        <div class="row">
            <div class="col-md-3 col-lg-2 sidebar" id="sidebarMenu">
                <?php include '../includes/sidebar.php'; ?>
            </div>
            
            <!-- Main Content Area -->
            <div class="col-md-9 col-lg-10 main-content-area">

        <!-- Content Area Below Fixed Header -->
        <div class="content-below-header">

             <!-- Calendar Main Area (Calendar Grid + Right Filter Sidebar) -->
             <div class="calendar-main-area">
                 <!-- Calendar Header Controls (Month/Day/List, Arrows) -->
                 <div class="calendar-header">
                     <div class="navigation-controls">
                         <button id="prevBtn"><i class="fas fa-chevron-left"></i></button>
                         <button id="nextBtn"><i class="fas fa-chevron-right"></i></button>
                         <span class="current-date" id="dateDisplay">May 2025</span>
                     </div>

                     <div class="view-toggle btn-group" role="group">
                         <button type="button" class="btn btn-outline-secondary active" data-view="month">
                             <span class="d-none d-md-inline">Month</span>
                             <i class="fas fa-calendar-alt d-md-none"></i>
                         </button>
                         <button type="button" class="btn btn-outline-secondary" data-view="day">
                             <span class="d-none d-md-inline">Day</span>
                             <i class="fas fa-calendar-day d-md-none"></i>
                         </button>
                         <button type="button" class="btn btn-outline-secondary" data-view="list">
                             <span class="d-none d-md-inline">List</span>
                             <i class="fas fa-list d-md-none"></i>
                         </button>
                     </div>
                 </div>

                  <!-- Empty State Message (initially visible for list view or when no events) -->
                  <div class="empty-state-message" id="emptyCalendarMessage">
                       <p>No events to display</p>
                  </div>

                  <!-- Calendar Views (placeholders) -->
                  <div class="month-view" id="monthViewContent">
                       <!-- Month view grid will be generated by JS -->
                       <div class="days-grid">
                           <!-- Day labels and cells generated by JS -->
                       </div>
                  </div>

                  <div class="day-view d-none" id="dayViewContent">
                       <!-- Day Name Display (below header) -->
                       <div class="day-name-display text-center mb-3">
                           <h3 id="dayNameDisplay" class="mb-0">Wednesday</h3>
                       </div>

                        <!-- All-Day section -->
                        <div class="day-view-all-day">
                            <span class="all-day-label">All-Day</span>
                            <div class="all-day-events">
                                 <!-- All-day events will be added here -->
                            </div>
                        </div>

                        <!-- Daily Time Slots Grid (Scrollable) -->
                        <div class="time-slots-grid">
                            <!-- Time slots will be generated by JS here -->
                        </div>
                  </div>

                  <div class="list-view d-none" id="listViewContent">
                       <!-- List view of events goes here -->
                       <div class="event-list">
                            <!-- Example event item -->
                            <!-- <div class="event-item">Event Title - Date Time</div> -->
                       </div>
                  </div>
             </div>

             <!-- Right Filter Sidebar -->
             <div class="right-filter-sidebar">
                  <button class="add-event-btn" data-bs-toggle="modal" data-bs-target="#addEventModal">
                     <span class="d-none d-md-inline">Add Event</span>
                     <i class="fas fa-plus d-md-none"></i>
                 </button>

                 <div class="filter-section">
                     <span class="filter-label">FILTER</span>
                     <div class="filter-checkboxes">
                         <div class="form-check">
                             <input class="form-check-input" type="checkbox" value="event" id="filterEvent" checked data-color="orange">
                             <label class="form-check-label" for="filterEvent">
                                 Event
                             </label>
                         </div>
                          <div class="form-check">
                             <input class="form-check-input" type="checkbox" value="lead" id="filterLead" checked data-color="green">
                             <label class="form-check-label" for="filterLead">
                                 Lead
                             </label>
                         </div>
                          <div class="form-check">
                             <input class="form-check-input" type="checkbox" value="reminder" id="filterReminder" checked data-color="purple">
                             <label class="form-check-label" for="filterReminder">
                                 Reminder
                             </label>
                         </div>
                          <div class="form-check">
                             <input class="form-check-input" type="checkbox" value="service" id="filterService" checked data-color="gray">
                             <label class="form-check-label" for="filterService">
                                 Service
                             </label>
                         </div>
                     </div>
                 </div>
                 <!-- You can add more filter sidebar content here -->
             </div>

        </div>

        <!-- Footer -->
        <?php include '../includes/dashboard-footer.php'; ?>

    </div>
    <!-- End Main Content Area -->

</div>

<!-- Add Event Modal -->
<div class="modal fade" id="addEventModal" tabindex="-1" aria-labelledby="addEventModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addEventModalLabel">Add Event</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="addEventForm">
          <div class="mb-3">
            <label for="eventTitle" class="form-label">Title</label>
            <input type="text" class="form-control" id="eventTitle" placeholder="Title">
          </div>
          <div class="mb-3">
            <label for="eventDate" class="form-label">Date</label>
            <input type="text" class="form-control" id="eventDate" placeholder="26-05-2025 07:09">
             <!-- Ideally, this would be a date/time picker -->
          </div>
          <div class="mb-3">
            <label for="eventDescription" class="form-label">Description</label>
            <textarea class="form-control" id="eventDescription" rows="3" placeholder="Description"></textarea>
          </div>
          <!-- Add other fields like Assign To, Type, etc. if needed -->

        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="saveEventBtn">Add</button>
      </div>
    </div>
  </div>
</div>

<!-- Include other quick action modals -->
<?php include '../includes/modals/add-lead.php'; ?>
<?php include '../includes/modals/add-task.php'; ?>
<?php include '../includes/modals/add-note.php'; ?>
<?php include '../includes/modals/add-reminder.php'; ?>

<!-- Bootstrap 5 JS and dependencies -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>

<!-- Custom JavaScript for Calendar Page -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // --- Mobile Left Sidebar Toggle (in header) ---
    const mobileLeftSidebarToggle = document.getElementById('mobileLeftSidebarToggleInHeader');
    const leftSidebar = document.getElementById('sidebarMenu');
    const leftSidebarOverlay = document.getElementById('sidebarOverlay');

    if (mobileLeftSidebarToggle && leftSidebar && leftSidebarOverlay) {
        mobileLeftSidebarToggle.addEventListener('click', function() {
            leftSidebar.classList.toggle('show');
            leftSidebarOverlay.classList.toggle('show');
        });

        leftSidebarOverlay.addEventListener('click', function() {
            leftSidebar.classList.remove('show');
            leftSidebarOverlay.classList.remove('show');
        });
    }

    // --- Mobile Toggles ---
    // Get both sidebar toggle buttons
    const sidebarToggleBtn = document.getElementById('sidebarToggleBtn');
    const filterToggleBtn = document.getElementById('filterToggleBtn');
    const sidebar = document.getElementById('sidebarMenu');
    const rightFilterSidebar = document.querySelector('.right-filter-sidebar');
    const sidebarOverlay = document.getElementById('sidebarOverlay');
    
    // Sidebar Toggle (Left side hamburger menu)
    if (sidebarToggleBtn && sidebar && sidebarOverlay) {
        sidebarToggleBtn.addEventListener('click', function() {
            sidebar.classList.toggle('show');
            sidebarOverlay.classList.toggle('show');
            // Close filter sidebar if open
            rightFilterSidebar.classList.remove('show');
        });
    }
    
    // Filter Toggle Button
    if (filterToggleBtn && rightFilterSidebar && sidebarOverlay) {
        filterToggleBtn.addEventListener('click', function() {
            rightFilterSidebar.classList.toggle('show');
            sidebarOverlay.classList.toggle('show');
            // Close main sidebar if open
            sidebar.classList.remove('show');
        });
    }
    
    // Close both sidebars when overlay is clicked
    if (sidebarOverlay) {
        sidebarOverlay.addEventListener('click', function() {
            sidebar.classList.remove('show');
            rightFilterSidebar.classList.remove('show');
            sidebarOverlay.classList.remove('show');
        });
    }

    // --- Calendar Functionality ---

    const currentDateDisplay = document.getElementById('dateDisplay');
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    const monthViewContent = document.getElementById('monthViewContent');
    const dayViewContent = document.getElementById('dayViewContent');
    const listViewContent = document.getElementById('listViewContent');
    const emptyCalendarMessage = document.getElementById('emptyCalendarMessage');
    const viewToggleButtons = document.querySelectorAll('.view-toggle .btn');
    const daysGrid = monthViewContent.querySelector('.days-grid');
    const dayNameDisplay = document.getElementById('dayNameDisplay');

    let activeDate = new Date(); // Start with the current date

    // Function to render the calendar for a given date
    function renderCalendar(date) {
        const year = date.getFullYear();
        const month = date.getMonth(); // 0-indexed
        const today = new Date(); // Get today's date for highlighting

        // Clear previous content
        daysGrid.innerHTML = '';

        // Add day labels (Sun, Mon, etc.)
        const dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
        dayNames.forEach(dayName => {
            const dayLabel = document.createElement('div');
            dayLabel.classList.add('day-label');
            dayLabel.textContent = dayName;
            daysGrid.appendChild(dayLabel);
        });

        // Get the first day of the month and the number of days in the month
        const firstDayOfMonth = new Date(year, month, 1);
        const lastDayOfMonth = new Date(year, month + 1, 0);
        const daysInMonth = lastDayOfMonth.getDate();

        // Get the day of the week for the first day of the month (0 for Sunday, 6 for Saturday)
        const firstDayOfWeek = firstDayOfMonth.getDay();

        // Calculate the number of days to show from the previous month
        const daysFromPrevMonth = firstDayOfWeek;

        // Calculate the number of cells needed (at least 6 weeks) to maintain consistent height
        // This ensures 42 cells (6 rows * 7 days) are always displayed if possible.
        const totalCells = 42; // Fixed for consistent grid height
        const daysFromNextMonth = totalCells - (daysFromPrevMonth + daysInMonth);

        // Get the last day of the previous month
        const lastDayOfPrevMonth = new Date(year, month, 0).getDate();

        // Add days from the previous month
        for (let i = daysFromPrevMonth; i > 0; i--) {
            const dayCell = document.createElement('div');
            dayCell.classList.add('day-cell', 'outside-month');
            dayCell.innerHTML = `<span class="day-number">${lastDayOfPrevMonth - i + 1}</span>`;
            daysGrid.appendChild(dayCell);
        }

        // Add days of the current month
        for (let i = 1; i <= daysInMonth; i++) {
            const dayCell = document.createElement('div');
            dayCell.classList.add('day-cell');
            dayCell.innerHTML = `<span class="day-number">${i}</span>`;

            // Add class for current day if it matches
            if (year === today.getFullYear() && month === today.getMonth() && i === today.getDate()) {
                dayCell.classList.add('current-day');
            }

            daysGrid.appendChild(dayCell);

            // Add click listener to open modal
            dayCell.addEventListener('click', function() {
                const addEventModalElement = document.getElementById('addEventModal');
                if (addEventModalElement) {
                    const addEventModal = new bootstrap.Modal(addEventModalElement);

                    // Populate date/time fields in modal
                    const clickedDayNumber = parseInt(this.querySelector('.day-number').textContent);
                    const clickedDate = new Date(year, month, clickedDayNumber);

                    // Format date as DD-MM-YYYY
                    const day = String(clickedDate.getDate()).padStart(2, '0');
                    const monthFormatted = String(clickedDate.getMonth() + 1).padStart(2, '0'); // Month is 0-indexed
                    const yearFormatted = clickedDate.getFullYear();

                    const formattedDate = `${day}-${monthFormatted}-${yearFormatted} 00:00`;

                    document.getElementById('eventDate').value = formattedDate;

                    addEventModal.show();
                }
            });
        }

        // Add days from the next month
        for (let i = 1; i <= daysFromNextMonth; i++) {
             // Prevent adding days if we already have enough cells (e.g., 6 rows = 42 cells)
             if (daysGrid.children.length < totalCells) {
                const dayCell = document.createElement('div');
                dayCell.classList.add('day-cell', 'outside-month');
                dayCell.innerHTML = `<span class="day-number">${i}</span>`;
                daysGrid.appendChild(dayCell);
             }
        }
         // If after adding current and previous month days, we still have less than totalCells, add remaining next month days
         while(daysGrid.children.length < totalCells) {
              const dayCell = document.createElement('div');
              dayCell.classList.add('day-cell', 'outside-month');
              const nextDayNum = daysGrid.children.length - (daysFromPrevMonth + daysInMonth) + 1;
              dayCell.innerHTML = `<span class="day-number">${nextDayNum}</span>`;
              daysGrid.appendChild(dayCell);
         }

        // Update the displayed month and year
        const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
        currentDateDisplay.textContent = `${monthNames[month]} ${year}`;

        // Initially hide empty message for Month view
        emptyCalendarMessage.classList.add('d-none');

        // Ensure Day view specific elements are hidden in Month view
        dayNameDisplay.parentElement.classList.add('d-none'); // Hide day name display div
        currentDateDisplay.classList.remove('d-none'); // Show Month/Year in header
    }

    // Function to update the day name and full date display for Day view
    function updateDayDateDisplay(date) {
        const optionsDayOfWeek = { weekday: 'long' };
        dayNameDisplay.textContent = date.toLocaleDateString(undefined, optionsDayOfWeek);

        // Format date as DD Month YYYY (e.g., 28 May 2025)
        const day = String(date.getDate()).padStart(2, '0');
        const month = date.toLocaleString('default', { month: 'long' });
        const year = date.getFullYear();
        currentDateDisplay.textContent = `${day} ${month} ${year}`; // Update header date display
    }

    // Function to update only the day name display
    function updateDayNameDisplay(date) {
        const optionsDayOfWeek = { weekday: 'long' };
        dayNameDisplay.textContent = date.toLocaleDateString(undefined, optionsDayOfWeek);
    }

    // Event listeners for navigation buttons
    prevBtn.addEventListener('click', function() {
        const selectedView = document.querySelector('.view-toggle .btn.active').getAttribute('data-view');
        if (selectedView === 'day') {
            activeDate.setDate(activeDate.getDate() - 1);
            updateDayDateDisplay(activeDate);
            updateDayNameDisplay(activeDate);
            // Note: Re-rendering events within the time slots for the new date is a separate task.
        } else if (selectedView === 'month') {
            activeDate.setMonth(activeDate.getMonth() - 1);
            renderCalendar(activeDate);
        }
    });

    nextBtn.addEventListener('click', function() {
        const selectedView = document.querySelector('.view-toggle .btn.active').getAttribute('data-view');
        if (selectedView === 'day') {
            activeDate.setDate(activeDate.getDate() + 1);
            updateDayDateDisplay(activeDate);
            updateDayNameDisplay(activeDate);
            // Note: Re-rendering events within the time slots for the new date is a separate task.
        } else if (selectedView === 'month') {
            activeDate.setMonth(activeDate.getMonth() + 1);
            renderCalendar(activeDate);
        }
    });

    // --- View Toggle Functionality ---
    viewToggleButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Remove 'active' class from all buttons
            viewToggleButtons.forEach(btn => btn.classList.remove('active'));

            // Add 'active' class to the clicked button
            this.classList.add('active');

            // Hide all view contents
            monthViewContent.classList.add('d-none');
            dayViewContent.classList.add('d-none');
            listViewContent.classList.add('d-none');

            const selectedView = this.getAttribute('data-view');
            if (selectedView === 'month') {
                monthViewContent.classList.remove('d-none');
                 renderCalendar(activeDate); // Re-render calendar for month view
                 emptyCalendarMessage.classList.add('d-none'); // Hide empty message for month view
            } else if (selectedView === 'day') {
                console.log('Switching to Day view.'); // Log view switch
                dayViewContent.classList.remove('d-none');
                 // TODO: Render Day view content
                 emptyCalendarMessage.classList.add('d-none'); // Hide empty message for day view

                 // Show and update Day view date and day name display
                  updateDayDateDisplay(activeDate);
                  updateDayNameDisplay(activeDate);
                  dayNameDisplay.parentElement.classList.remove('d-none'); // Show day name display div
                  currentDateDisplay.classList.remove('d-none'); // Show Full Date in header

                  // Generate and display hourly time slots
                  renderDayViewTimeSlots();

            } else if (selectedView === 'list') {
                listViewContent.classList.remove('d-none');
                 // TODO: Render List view content
                 // TODO: Decide whether to show empty message based on actual list data
                 // For now, always show empty message if no data is loaded for list view
                 // You'll need to add logic here to check if there are events for the list view
                 emptyCalendarMessage.classList.remove('d-none'); // Show empty for list view if no data
                 // Hide Day view specific elements and show Month view specific elements
                 dayNameDisplay.parentElement.classList.add('d-none'); // Hide day name display div
                 currentDateDisplay.classList.remove('d-none'); // Show Month/Year in header
                 // Update header date display to Month/Year for List view
                 const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
                 currentDateDisplay.textContent = `${monthNames[activeDate.getMonth()]} ${activeDate.getFullYear()}`;
            }
        });
    });

    // --- Add Event Modal ---
    const addEventModalElement = document.getElementById('addEventModal');
    const addEventForm = document.getElementById('addEventForm');
    const saveEventBtn = document.getElementById('saveEventBtn');

    if(addEventModalElement && addEventForm && saveEventBtn) {
        const addEventModal = new bootstrap.Modal(addEventModalElement);

        // Example: Handle Save button click (replace with actual form submission logic)
        saveEventBtn.addEventListener('click', function() {
            // Get form data
            const title = document.getElementById('eventTitle').value;
            const date = document.getElementById('eventDate').value;
            const description = document.getElementById('eventDescription').value;

            console.log('Saving event:', { title, date, description });

            // TODO: Add logic to save the event (e.g., send data to backend)

            // Close modal after saving
            addEventModal.hide();

            // TODO: Refresh calendar view or add the new event to the display
        });

         // Optional: Clear form when modal is hidden
        addEventModalElement.addEventListener('hidden.bs.modal', function () {
            addEventForm.reset();
        });
    }

    // --- Initialization ---
    // Render the calendar for the current month on page load
    renderCalendar(activeDate);

    // TODO: Implement event filtering based on checkboxes
    // TODO: Implement fetching and displaying actual event data

    // Add click listeners to Day View time slots
    const timeSlots = document.querySelectorAll('.day-view .time-slot');
    timeSlots.forEach(slot => {
        slot.addEventListener('click', function() {
            const addEventModalElement = document.getElementById('addEventModal');
            if (addEventModalElement) {
                const addEventModal = new bootstrap.Modal(addEventModalElement);
                addEventModal.show();
                // Optional: Populate date/time fields in modal if needed
                // const clickedTime = this.textContent; // Example: "6 AM"
                // TODO: Combine with current day for full date/time
            }
        });
    });

    // Function to render hourly time slots for Day View
    function renderDayViewTimeSlots() {
        const timeSlotsGrid = document.querySelector('.time-slots-grid'); // Get the correct div
        if (!timeSlotsGrid) {
            console.error('Time slots grid div not found!'); // Log error if div is missing
            return;
         } // Exit if container not found

        timeSlotsGrid.innerHTML = ''; // Clear previous time slots

        // Loop from 6 AM (hour 6) to 11 PM (hour 23)
        for (let i = 6; i <= 23; i++) {
            const timeSlotDiv = document.createElement('div');
            timeSlotDiv.classList.add('time-slot');

            const timeLabelSpan = document.createElement('span');
            timeLabelSpan.classList.add('time-label');

            const hour = i;
            const ampm = hour >= 12 ? 'PM' : 'AM';
            const displayHour = (hour % 12) || 12; // Convert 0 (midnight) or 12 (noon) to 12
            timeLabelSpan.textContent = `${displayHour}${ampm}`; // Format as 6AM, 7AM, 12PM, etc.

            const timeSlotContentDiv = document.createElement('div');
            timeSlotContentDiv.classList.add('slot-content');
            // Add click listener to open modal for this time slot
            timeSlotDiv.addEventListener('click', function() {
                 const addEventModalElement = document.getElementById('addEventModal');
                 if (addEventModalElement) {
                     const addEventModal = new bootstrap.Modal(addEventModalElement);
                     // Optional: Populate date/time fields in modal if needed
                     // const clickedDate = new Date(activeDate);
                     // const clickedTimeLabel = this.querySelector('.time-label').textContent;
                     // TODO: Combine date and time for modal input
                     addEventModal.show();
                 }
            });

            timeSlotDiv.appendChild(timeLabelSpan);
            timeSlotDiv.appendChild(timeSlotContentDiv);

            timeSlotsGrid.appendChild(timeSlotDiv);
        }
         console.log(`Rendered ${timeSlotsGrid.children.length} time slots into ${timeSlotsGrid.id || timeSlotsGrid.className}.`); // Log number of time slots and target
    }
});
</script>

<!-- Sidebar toggle functionality is now handled in dashboard-header.php -->

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add any calendar-specific JavaScript here
});
</script>
</body>
</html>