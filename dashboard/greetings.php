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
            --primary-color: #57439F;
            --primary-dark: #4f46e5;
            --secondary-color: #f8fafc;
            --border-color: #e2e8f0;
            --text-primary: #1e293b;
            --text-secondary: #64748b;
            --success-color: #10b981;
            --danger-color: #ef4444;
            --warning-color: #f59e0b;
            --header-height: 60px;
            --sidebar-width: 250px;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background-color: #f8fafc;
            color: var(--text-primary);
            line-height: 1.6;
            padding-top: var(--header-height); /* Add padding for fixed header */
            min-height: 100vh;
            margin: 0;
        }

        /* Header Styles */
        .dashboard-header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: var(--header-height);
            background: #fff;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            z-index: 1030;
        }

        /* Dashboard Container */
        .dashboard-container {
            display: flex;
            min-height: calc(100vh - var(--header-height));
            position: relative;
        }

        /* Sidebar Styles */
        .sidebar {
            position: fixed;
            top: var(--header-height);
            left: 0;
            bottom: 0;
            width: var(--sidebar-width);
            background: #fff;
            box-shadow: 1px 0 3px rgba(0, 0, 0, 0.1);
            z-index: 1020;
            overflow-y: auto;
        }

        /* Main Content Area */
        .main-content-area {
            flex: 1;
            margin-left: var(--sidebar-width);
            padding: 20px;
            width: calc(100% - var(--sidebar-width));
            min-height: calc(100vh - var(--header-height));
            background-color: #F8FAFC;
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .main-content-area {
                margin-left: 0;
                width: 100%;
                padding: 16px;
            }

            .sidebar-overlay {
                display: none;
                position: fixed;
                top: var(--header-height);
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0, 0, 0, 0.5);
                z-index: 1015;
            }

            .sidebar-overlay.show {
                display: block;
            }
        }

        /* Greetings Page Specific Styles */
        .greetings-container {
            background: white;
            border-radius: 12px;
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
            gap: 12px;
            align-items: center;
        }

        /* Base Action Button Style */
        .action-btn {
            width: 42px;
            height: 42px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .action-btn i {
            font-size: 1.2rem;
            color: white;
            position: relative;
            z-index: 2;
            transition: transform 0.3s ease;
        }

        .action-btn::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.1);
            transform: translate(-50%, -50%) scale(0);
            border-radius: 50%;
            transition: transform 0.3s ease;
        }

        .action-btn:hover::before {
            transform: translate(-50%, -50%) scale(1.5);
        }

        .action-btn:hover i {
            transform: scale(1.1);
        }

        /* Calendar Button */
        #dateRangeBtn {
            background: linear-gradient(135deg, #4CAF50 0%, #45a049 100%);
            box-shadow: 0 4px 15px rgba(76, 175, 80, 0.2);
        }

        #dateRangeBtn:hover {
            background: linear-gradient(135deg, #45a049 0%, #3d8b40 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(76, 175, 80, 0.25);
        }

        #dateRangeBtn.active {
            animation: calendarPulse 1.5s infinite;
        }

        @keyframes calendarPulse {
            0% {
                box-shadow: 0 0 0 0 rgba(76, 175, 80, 0.4);
            }
            70% {
                box-shadow: 0 0 0 10px rgba(76, 175, 80, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(76, 175, 80, 0);
            }
        }

        /* Upload Button */
        #uploadBtn {
            background: linear-gradient(135deg, #2196F3 0%, #1976D2 100%);
            box-shadow: 0 4px 15px rgba(33, 150, 243, 0.2);
        }

        #uploadBtn:hover {
            background: linear-gradient(135deg, #1976D2 0%, #1565C0 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(33, 150, 243, 0.25);
        }

        #uploadBtn.active {
            animation: uploadPulse 1.5s infinite;
        }

        @keyframes uploadPulse {
            0% {
                box-shadow: 0 0 0 0 rgba(33, 150, 243, 0.4);
            }
            70% {
                box-shadow: 0 0 0 10px rgba(33, 150, 243, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(33, 150, 243, 0);
            }
        }

        /* Add New Button */
        #addNewBtn {
            background: linear-gradient(135deg, #9C27B0 0%, #7B1FA2 100%);
            box-shadow: 0 4px 15px rgba(156, 39, 176, 0.2);
        }

        #addNewBtn:hover {
            background: linear-gradient(135deg, #7B1FA2 0%, #6A1B9A 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(156, 39, 176, 0.25);
        }

        #addNewBtn.active {
            animation: addPulse 1.5s infinite;
        }

        @keyframes addPulse {
            0% {
                box-shadow: 0 0 0 0 rgba(156, 39, 176, 0.4);
            }
            70% {
                box-shadow: 0 0 0 10px rgba(156, 39, 176, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(156, 39, 176, 0);
            }
        }

        /* Tooltip Styles */
        .action-btn::after {
            content: attr(title);
            position: absolute;
            bottom: -40px;
            left: 50%;
            transform: translateX(-50%) scale(0);
            background: #333;
            color: white;
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 12px;
            white-space: nowrap;
            opacity: 0;
            transition: all 0.3s ease;
            pointer-events: none;
        }

        .action-btn:hover::after {
            transform: translateX(-50%) scale(1);
            opacity: 1;
        }

        /* Active State Styles */
        .action-btn.clicked {
            animation: buttonClick 0.3s ease;
        }

        @keyframes buttonClick {
            0% {
                transform: scale(1);
            }
            50% {
                transform: scale(0.95);
            }
            100% {
                transform: scale(1);
            }
        }

        /* Mobile Responsive Adjustments */
        @media (max-width: 768px) {
            .action-buttons {
                gap: 8px;
            }

            .action-btn {
                width: 38px;
                height: 38px;
            }

            .action-btn i {
                font-size: 1rem;
            }

            .action-btn::after {
                display: none;
            }
        }

        /* Button Group Separator */
        .action-buttons::before {
            content: '';
            height: 24px;
            width: 1px;
            background: #E5E7EB;
            margin: 0 4px;
        }

        /* Modal Styles */
        .modal-header {
            background: #57439F;
            color: white;
            padding: 1rem;
            border-bottom: none;
            border-top-left-radius: 8px;
            border-top-right-radius: 8px;
        }

        .modal-header .btn-close {
            color: white;
            opacity: 1;
            background: transparent url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%23fff'%3e%3cpath d='M.293.293a1 1 0 011.414 0L8 6.586 14.293.293a1 1 0 111.414 1.414L9.414 8l6.293 6.293a1 1 0 01-1.414 1.414L8 9.414l-6.293 6.293a1 1 0 01-1.414-1.414L6.586 8 .293 1.707a1 1 0 010-1.414z'/%3e%3c/svg%3e") center/1em auto no-repeat;
        }

        .modal-title {
            font-size: 1.25rem;
            font-weight: 500;
        }

        .modal-body {
            padding: 1.5rem;
        }

        /* Download Format Button */
        .download-format-btn {
            background: #57439F;
            color: white;
            border: none;
            padding: 0.5rem 1.5rem;
            border-radius: 4px;
            margin-bottom: 1.5rem;
            font-weight: 500;
        }

        /* Notes Section */
        .notes-section {
            margin-bottom: 1.5rem;
        }

        .notes-title {
            color: #dc3545;
            font-weight: 500;
            margin-bottom: 0.5rem;
        }

        .notes-list {
            list-style: none;
            padding-left: 0;
            margin: 0;
        }

        .notes-list li {
            color: #dc3545;
            padding-left: 1.2rem;
            position: relative;
            margin-bottom: 0.25rem;
            font-size: 0.9rem;
        }

        .notes-list li::before {
            content: "•";
            position: absolute;
            left: 0;
        }

        /* Toggle Switches */
        .toggle-group {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            padding: 0.5rem 0;
        }

        .toggle-label {
            font-weight: 500;
        }

        .form-switch {
            padding-left: 2.5rem;
        }

        .form-check-input {
            width: 3rem;
            height: 1.5rem;
            margin-left: -2.5rem;
            background-color: #e9ecef;
            border-color: #e9ecef;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='-4 -4 8 8'%3e%3ccircle r='3' fill='rgba%28255, 255, 255, 1%29'/%3e%3c/svg%3e");
        }

        .form-check-input:checked {
            background-color: #57439F;
            border-color: #57439F;
        }

        /* File Upload Section */
        .file-upload-section {
            margin-bottom: 1.5rem;
        }

        .file-upload-label {
            font-weight: 500;
            margin-bottom: 0.5rem;
        }

        .file-upload-label .required {
            color: #dc3545;
        }

        .file-upload-btn {
            background: #57439F;
            color: white;
            border: none;
            padding: 0.5rem 1.5rem;
            border-radius: 4px;
            font-weight: 500;
        }

        .file-name {
            margin-left: 1rem;
            color: #6c757d;
        }

        /* Modal Footer */
        .modal-footer {
            border-top: none;
            padding: 1rem 1.5rem;
        }

        .btn-primary {
            background: #57439F;
            border-color: #57439F;
            padding: 0.5rem 2rem;
        }

        .btn-secondary {
            background: #6c757d;
            border-color: #6c757d;
            padding: 0.5rem 2rem;
        }

        /* Add Greetings Modal Specific Styles */
        .contact-type {
            display: flex;
            gap: 3rem;
            margin-bottom: 1.5rem;
        }

        .contact-type .form-check {
            margin: 0;
        }

        .contact-type .form-check-input {
            width: 1.2rem;
            height: 1.2rem;
            margin-right: 0.5rem;
        }

        .contact-type .form-check-input:checked {
            background-color: #57439F;
            border-color: #57439F;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-label {
            font-weight: 500;
            margin-bottom: 0.5rem;
        }

        .form-control {
            border-radius: 4px;
            border-color: #ced4da;
        }

        .form-select {
            border-radius: 4px;
            border-color: #ced4da;
        }

        .phone-input-group {
            display: flex;
            gap: 0.5rem;
        }

        .country-select {
            width: 100px;
        }

        .phone-input {
            flex: 1;
        }

        /* Calendar Styles */
        .date-range-modal .modal-content {
            border-radius: 8px;
            border: none;
        }

        .date-range-modal .modal-header {
            background: #57439F;
            color: white;
            border-top-left-radius: 8px;
            border-top-right-radius: 8px;
            padding: 1rem 1.5rem;
        }

        .date-range-sidebar {
            background: #f8fafc;
            border-right: 1px solid #e2e8f0;
            padding: 1rem;
        }

        .date-option {
            padding: 0.75rem 1rem;
            margin-bottom: 0.5rem;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s ease;
            color: #1e293b;
        }

        .date-option:hover {
            background: rgba(87, 67, 159, 0.1);
        }

        .date-option.active {
            background: #57439F;
            color: white;
        }

        .calendar-container {
            padding: 1rem;
        }

        .calendar-days {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 4px;
            margin-top: 10px;
        }

        .calendar-day {
            aspect-ratio: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            border-radius: 50%;
            transition: all 0.2s ease;
            font-size: 14px;
        }

        .calendar-day:not(.disabled):hover {
            background: rgba(87, 67, 159, 0.1);
        }

        .calendar-day.selected {
            background: #57439F;
            color: white;
        }

        .calendar-day.disabled {
            color: #ccc;
            cursor: default;
        }

        .calendar-day.today {
            border: 2px solid #57439F;
        }

        /* Delete Functionality Styles */
        .delete-selection-mode .greeting-item {
            position: relative;
            padding: 1rem;
            margin-bottom: 0.5rem;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            background: white;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .delete-selection-mode .greeting-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .delete-selection-mode .greeting-item.selected {
            background: rgba(220, 38, 38, 0.05);
            border-color: #ef4444;
        }

        .delete-selection-mode .greeting-item::before {
            content: '';
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            width: 20px;
            height: 20px;
            border: 2px solid #e2e8f0;
            border-radius: 4px;
            transition: all 0.3s ease;
        }

        .delete-selection-mode .greeting-item.selected::before {
            background: #ef4444;
            border-color: #ef4444;
        }

        .delete-selection-mode .greeting-item.selected::after {
            content: '';
            position: absolute;
            left: calc(1rem + 6px);
            top: 50%;
            transform: translateY(-50%) rotate(45deg);
            width: 8px;
            height: 12px;
            border: solid white;
            border-width: 0 2px 2px 0;
        }

        /* Delete Button Styles */
        .action-btn.delete-btn {
            background: #ef4444;
            color: white;
            transition: all 0.3s ease;
        }

        .action-btn.delete-btn:hover {
            background: #dc2626;
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(239, 68, 68, 0.2);
        }

        .action-btn.delete-btn.active {
            animation: deleteButtonPulse 1.5s infinite;
        }

        @keyframes deleteButtonPulse {
            0% {
                box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.4);
            }
            70% {
                box-shadow: 0 0 0 10px rgba(239, 68, 68, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(239, 68, 68, 0);
            }
        }

        /* Delete Confirmation Modal */
        .delete-confirm-modal {
            animation: modalFadeIn 0.3s ease;
        }

        @keyframes modalFadeIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .delete-confirm-modal .modal-content {
            border: none;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .delete-confirm-modal .modal-body {
            padding: 2.5rem;
            text-align: center;
        }

        .delete-confirm-modal .warning-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 1.5rem;
            background: #fff5f5;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .delete-confirm-modal .warning-icon i {
            font-size: 2.5rem;
            color: #ef4444;
        }

        .delete-confirm-modal .message {
            font-size: 1.25rem;
            color: #1f2937;
            margin-bottom: 2rem;
            font-weight: 500;
        }

        .delete-confirm-modal .btn-group {
            display: flex;
            gap: 1rem;
            justify-content: center;
        }

        .delete-confirm-modal .btn-cancel {
            padding: 0.75rem 2rem;
            border: 1px solid #e5e7eb;
            background: white;
            color: #4b5563;
            border-radius: 6px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .delete-confirm-modal .btn-cancel:hover {
            background: #f9fafb;
            border-color: #d1d5db;
        }

        .delete-confirm-modal .btn-delete {
            padding: 0.75rem 2rem;
            background: #ef4444;
            color: white;
            border: none;
            border-radius: 6px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .delete-confirm-modal .btn-delete:hover {
            background: #dc2626;
            transform: translateY(-1px);
            box-shadow: 0 4px 6px rgba(239, 68, 68, 0.2);
        }

        /* Delete Success Notification */
        .delete-notification {
            position: fixed;
            top: 1.5rem;
            right: 1.5rem;
            background: white;
            border-left: 4px solid #ef4444;
            border-radius: 6px;
            padding: 1rem 1.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transform: translateX(120%);
            transition: transform 0.3s ease;
            z-index: 1060;
        }

        .delete-notification.show {
            transform: translateX(0);
        }

        .delete-notification .title {
            color: #ef4444;
            font-weight: 600;
            margin-bottom: 0.25rem;
        }

        .delete-notification .message {
            color: #4b5563;
            font-size: 0.875rem;
            margin: 0;
        }

        /* Selection Counter */
        .selection-counter {
            position: fixed;
            bottom: 1.5rem;
            right: 1.5rem;
            background: #1f2937;
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 6px;
            font-weight: 500;
            transform: translateY(100%);
            transition: transform 0.3s ease;
            z-index: 1050;
        }

        .selection-counter.show {
            transform: translateY(0);
        }

        .selection-counter .count {
            color: #ef4444;
            font-weight: 600;
            margin-right: 0.25rem;
        }

        /* Please Select Greetings Alert Modal */
        .alert-modal {
            animation: alertFadeIn 0.3s ease;
        }

        @keyframes alertFadeIn {
            from {
                opacity: 0;
                transform: scale(0.95);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        .alert-modal .modal-dialog {
            max-width: 400px;
        }

        .alert-modal .modal-content {
            border: none;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .alert-modal .modal-body {
            padding: 2.5rem;
            text-align: center;
            background: white;
        }

        .alert-modal .alert-icon {
            width: 70px;
            height: 70px;
            margin: 0 auto 1.5rem;
            background: #FEF2F2;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .alert-modal .alert-icon i {
            font-size: 2rem;
            color: #EF4444;
        }

        .alert-modal .alert-message {
            font-size: 1.25rem;
            color: #1F2937;
            margin-bottom: 1.5rem;
            font-weight: 500;
            line-height: 1.4;
        }

        .alert-modal .alert-btn {
            background: #EF4444;
            color: white;
            border: none;
            padding: 0.75rem 3rem;
            border-radius: 6px;
            font-size: 1rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .alert-modal .alert-btn:hover {
            background: #DC2626;
            transform: translateY(-1px);
            box-shadow: 0 4px 6px rgba(239, 68, 68, 0.2);
        }

        .alert-modal .alert-btn:active {
            transform: translateY(0);
            box-shadow: none;
        }

        /* Backdrop styling */
        .modal-backdrop.show {
            opacity: 0.5;
            backdrop-filter: blur(4px);
        }

        /* Mobile Responsive Adjustments */
        @media (max-width: 576px) {
            .alert-modal .modal-dialog {
                margin: 1rem;
            }

            .alert-modal .modal-body {
                padding: 2rem 1.5rem;
            }

            .alert-modal .alert-icon {
                width: 60px;
                height: 60px;
                margin-bottom: 1rem;
            }

            .alert-modal .alert-icon i {
                font-size: 1.75rem;
            }

            .alert-modal .alert-message {
                font-size: 1.125rem;
                margin-bottom: 1.25rem;
            }

            .alert-modal .alert-btn {
                width: 100%;
                padding: 0.75rem 1rem;
            }
        }

        /* Modal Base Styles */
        .modal-lg {
            max-width: 800px;
        }

        .modal-content {
            border: none;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        /* Import Modal Specific Styles */
        .import-modal .modal-header {
            background: #57439F;
            color: white;
            padding: 1rem 1.5rem;
            border-bottom: none;
        }

        .import-modal .modal-title {
            font-size: 1.25rem;
            font-weight: 500;
        }

        .import-modal .btn-close {
            color: white;
            opacity: 1;
            filter: brightness(0) invert(1);
        }

        /* Download Format Button */
        .download-format-btn {
            background: #57439F;
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 6px;
            font-weight: 500;
            transition: all 0.3s ease;
            margin-bottom: 1.5rem;
            width: auto;
        }

        .download-format-btn:hover {
            background: #4a3884;
            transform: translateY(-1px);
            box-shadow: 0 4px 6px rgba(87, 67, 159, 0.2);
        }

        /* Notes Section */
        .notes-section {
            margin-bottom: 2rem;
        }

        .notes-title {
            color: #EF4444;
            font-weight: 600;
            font-size: 1.1rem;
            margin-bottom: 1rem;
        }

        .notes-list {
            list-style: none;
            padding-left: 0;
            margin: 0;
        }

        .notes-list li {
            color: #EF4444;
            padding-left: 1.5rem;
            position: relative;
            margin-bottom: 0.75rem;
            font-size: 0.95rem;
            line-height: 1.5;
        }

        .notes-list li::before {
            content: "•";
            position: absolute;
            left: 0.5rem;
            color: #EF4444;
        }

        /* Toggle Switch Styles */
        .toggle-group {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 0;
            border-bottom: 1px solid #E5E7EB;
        }

        .toggle-label {
            font-weight: 500;
            color: #1F2937;
        }

        .form-switch {
            padding-left: 3rem;
        }

        .form-switch .form-check-input {
            width: 3rem;
            height: 1.5rem;
            border-radius: 1.5rem;
            background-color: #E5E7EB;
            border-color: #E5E7EB;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .form-switch .form-check-input:checked {
            background-color: #57439F;
            border-color: #57439F;
        }

        /* File Upload Section */
        .file-upload-section {
            margin: 2rem 0;
        }

        .file-upload-label {
            display: block;
            font-weight: 500;
            color: #1F2937;
            margin-bottom: 0.75rem;
        }

        .file-upload-label .required {
            color: #EF4444;
            margin-left: 0.25rem;
        }

        .file-upload-btn {
            background: #57439F;
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 6px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .file-upload-btn:hover {
            background: #4a3884;
            transform: translateY(-1px);
            box-shadow: 0 4px 6px rgba(87, 67, 159, 0.2);
        }

        .file-name {
            margin-left: 1rem;
            color: #6B7280;
            font-size: 0.95rem;
        }

        /* Add Greetings Modal Specific Styles */
        .add-greetings-modal .modal-body {
            padding: 1.5rem;
        }

        /* Contact Type Selection */
        .contact-type {
            display: flex;
            gap: 3rem;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #E5E7EB;
        }

        .contact-type .form-check {
            margin: 0;
        }

        .contact-type .form-check-input {
            width: 1.25rem;
            height: 1.25rem;
            margin-right: 0.5rem;
            cursor: pointer;
        }

        .contact-type .form-check-input:checked {
            background-color: #57439F;
            border-color: #57439F;
        }

        .contact-type .form-check-label {
            font-weight: 500;
            color: #1F2937;
            cursor: pointer;
        }

        /* Form Fields */
        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            font-weight: 500;
            color: #1F2937;
            margin-bottom: 0.5rem;
        }

        .form-label .required {
            color: #EF4444;
            margin-left: 0.25rem;
        }

        .form-control, .form-select {
            border: 1px solid #D1D5DB;
            border-radius: 6px;
            padding: 0.75rem 1rem;
            width: 100%;
            transition: all 0.2s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: #57439F;
            box-shadow: 0 0 0 3px rgba(87, 67, 159, 0.1);
            outline: none;
        }

        /* Phone Input Group */
        .phone-input-group {
            display: flex;
            gap: 0.75rem;
        }

        .country-select {
            width: 120px;
            flex-shrink: 0;
        }

        .country-select .flag-icon {
            margin-right: 0.5rem;
        }

        /* Dynamic Fields (Toggle-controlled) */
        .dynamic-fields {
            display: none;
            margin-top: 1rem;
            padding: 1rem;
            background: #F9FAFB;
            border-radius: 6px;
            border: 1px solid #E5E7EB;
        }

        .dynamic-fields.show {
            display: block;
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Date Picker Styles */
        .date-picker {
            position: relative;
        }

        .date-picker .form-control {
            padding-right: 2.5rem;
        }

        .date-picker::after {
            content: '\f133';
            font-family: 'Font Awesome 5 Free';
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #6B7280;
            pointer-events: none;
        }

        /* Modal Footer */
        .modal-footer {
            border-top: 1px solid #E5E7EB;
            padding: 1rem 1.5rem;
        }

        .btn-primary {
            background: #57439F;
            color: white;
            border: none;
            padding: 0.75rem 2rem;
            border-radius: 6px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background: #4a3884;
            transform: translateY(-1px);
            box-shadow: 0 4px 6px rgba(87, 67, 159, 0.2);
        }

        .btn-secondary {
            background: white;
            color: #4B5563;
            border: 1px solid #D1D5DB;
            padding: 0.75rem 2rem;
            border-radius: 6px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-secondary:hover {
            background: #F9FAFB;
            border-color: #9CA3AF;
        }

        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .modal-lg {
                margin: 1rem;
            }

            .contact-type {
                gap: 2rem;
            }

            .phone-input-group {
                flex-direction: column;
                gap: 0.5rem;
            }

            .country-select {
                width: 100%;
            }
            
            .btn-primary, .btn-secondary {
                width: 100%;
                margin: 0.5rem 0;
            }
        }

        /* Form Type Selection Styles */
        .form-type-selection {
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #E5E7EB;
        }

        .form-type-radio {
            display: flex;
            gap: 3rem;
        }

        .form-type-radio .form-check {
            margin: 0;
            padding: 0;
        }

        .form-type-radio .form-check-input {
            width: 1.25rem;
            height: 1.25rem;
            margin-right: 0.75rem;
            border: 2px solid #D1D5DB;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .form-type-radio .form-check-input:checked {
            background-color: #57439F;
            border-color: #57439F;
            box-shadow: 0 0 0 2px rgba(87, 67, 159, 0.1);
        }

        .form-type-radio .form-check-label {
            font-weight: 500;
            color: #1F2937;
            cursor: pointer;
        }

        /* Toggle Switch Section */
        .toggle-section {
            margin-bottom: 2rem;
            background: #F9FAFB;
            border-radius: 8px;
            padding: 1rem;
        }

        .toggle-switch-group {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .toggle-item {
            display: flex;
                justify-content: space-between;
            align-items: center;
            padding: 0.75rem;
            background: white;
            border-radius: 6px;
            border: 1px solid #E5E7EB;
            transition: all 0.2s ease;
        }

        .toggle-item:hover {
            border-color: #57439F;
            box-shadow: 0 2px 4px rgba(87, 67, 159, 0.1);
        }

        .toggle-item .toggle-label {
            font-weight: 500;
            color: #1F2937;
        }

        .toggle-switch {
            position: relative;
            width: 3.5rem;
            height: 2rem;
        }

        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .toggle-slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #E5E7EB;
            transition: .4s;
            border-radius: 2rem;
        }

        .toggle-slider:before {
            position: absolute;
            content: "";
            height: 1.5rem;
            width: 1.5rem;
            left: 0.25rem;
            bottom: 0.25rem;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }

        .toggle-switch input:checked + .toggle-slider {
            background-color: #57439F;
        }

        .toggle-switch input:checked + .toggle-slider:before {
            transform: translateX(1.5rem);
        }

        /* Dynamic Form Fields */
        .dynamic-fields {
            display: none;
            margin-top: 1rem;
            padding: 1rem;
            background: white;
            border-radius: 6px;
            border: 1px solid #E5E7EB;
            animation: slideDown 0.3s ease;
        }

        .dynamic-fields.show {
            display: block;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Form Fields Styling */
            .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            font-weight: 500;
            color: #1F2937;
            margin-bottom: 0.5rem;
        }

        .form-label .required {
            color: #EF4444;
            margin-left: 0.25rem;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid #D1D5DB;
            border-radius: 6px;
            font-size: 0.95rem;
            transition: all 0.2s ease;
        }

        .form-control:focus {
            border-color: #57439F;
            box-shadow: 0 0 0 3px rgba(87, 67, 159, 0.1);
            outline: none;
        }

        .form-control::placeholder {
            color: #9CA3AF;
        }

        /* Date Picker Styling */
        .date-picker-wrapper {
            position: relative;
        }

        .date-picker-wrapper .form-control {
            padding-right: 2.5rem;
        }

        .date-picker-icon {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #6B7280;
            pointer-events: none;
        }

        /* Template Dropdown Styling */
        .template-select {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid #D1D5DB;
            border-radius: 6px;
            font-size: 0.95rem;
            background-color: white;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .template-select:focus {
            border-color: #57439F;
            box-shadow: 0 0 0 3px rgba(87, 67, 159, 0.1);
            outline: none;
        }

        /* Phone Input Group */
        .phone-input-group {
            display: flex;
            gap: 0.75rem;
        }

        .country-code-select {
            width: 120px;
            flex-shrink: 0;
            padding: 0.75rem;
            border: 1px solid #D1D5DB;
            border-radius: 6px;
            background-color: white;
            cursor: pointer;
        }

        .country-code-select .flag-icon {
            margin-right: 0.5rem;
            width: 1.25rem;
            height: auto;
        }

        .phone-number-input {
            flex: 1;
        }

        /* Form Validation Styles */
        .form-control.is-invalid {
            border-color: #EF4444;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23EF4444'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23EF4444' stroke='none'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 0.75rem center;
            background-size: 1rem;
            padding-right: 2.5rem;
        }

        .invalid-feedback {
            display: none;
            color: #EF4444;
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }

        .form-control.is-invalid + .invalid-feedback {
            display: block;
        }

        /* Add Greetings Modal Enhanced Styles */
        .modal-content {
            border: none;
            border-radius: 16px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .modal-header {
            background: linear-gradient(135deg, #6366F1 0%, #4F46E5 100%);
            padding: 1.5rem;
            border: none;
        }

        .modal-header .modal-title {
            color: white;
            font-size: 1.5rem;
            font-weight: 600;
            letter-spacing: -0.025em;
        }

        .modal-header .btn-close {
            background: transparent url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%23fff'%3e%3cpath d='M.293.293a1 1 0 011.414 0L8 6.586 14.293.293a1 1 0 111.414 1.414L9.414 8l6.293 6.293a1 1 0 01-1.414 1.414L8 9.414l-6.293 6.293a1 1 0 01-1.414-1.414L6.586 8 .293 1.707a1 1 0 010-1.414z'/%3e%3c/svg%3e") center/1em auto no-repeat;
            opacity: 0.75;
            transition: opacity 0.2s;
            padding: 1rem;
        }

        .modal-header .btn-close:hover {
            opacity: 1;
        }

        .modal-body {
            padding: 2rem;
            background: #F8FAFC;
        }

        /* Form Type Selection Enhanced */
        .form-type-selection {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            margin-bottom: 2rem;
        }

        .form-type-radio {
            display: flex;
            gap: 3rem;
            justify-content: center;
        }

        .form-type-radio .form-check {
            position: relative;
            padding: 0;
            margin: 0;
        }

        .form-type-radio .form-check-input {
            position: absolute;
            opacity: 0;
            width: 0;
            height: 0;
        }

        .form-type-radio .form-check-label {
            display: flex;
            align-items: center;
            padding: 1rem 2rem;
            border-radius: 8px;
            background: #F1F5F9;
            color: #64748B;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .form-type-radio .form-check-input:checked + .form-check-label {
            background: #4F46E5;
            color: white;
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.2);
        }

        /* Toggle Section Enhanced */
        .toggle-section {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            margin-bottom: 2rem;
        }

        .toggle-switch-group {
            display: grid;
            gap: 1rem;
        }

        .toggle-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            background: #F8FAFC;
            border-radius: 8px;
            border: 1px solid #E2E8F0;
            transition: all 0.2s ease;
        }

        .toggle-item:hover {
            border-color: #4F46E5;
            box-shadow: 0 2px 8px rgba(79, 70, 229, 0.1);
        }

        .toggle-item .toggle-label {
            font-weight: 500;
            color: #1E293B;
        }

        /* Enhanced Toggle Switch */
        .toggle-switch {
            position: relative;
            width: 3.5rem;
            height: 2rem;
        }

        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .toggle-slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #E2E8F0;
            transition: .4s;
            border-radius: 2rem;
        }

        .toggle-slider:before {
            position: absolute;
            content: "";
            height: 1.5rem;
            width: 1.5rem;
            left: 0.25rem;
            bottom: 0.25rem;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .toggle-switch input:checked + .toggle-slider {
            background: linear-gradient(135deg, #6366F1 0%, #4F46E5 100%);
        }

        .toggle-switch input:checked + .toggle-slider:before {
            transform: translateX(1.5rem);
        }

        /* Form Fields Enhanced */
        .form-group {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            font-weight: 600;
            color: #1E293B;
            margin-bottom: 0.75rem;
        }

        .form-label .required {
            color: #EF4444;
            margin-left: 0.25rem;
        }

        .form-control {
            width: 100%;
            padding: 0.875rem 1rem;
            border: 2px solid #E2E8F0;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.2s ease;
            background-color: #F8FAFC;
        }

        .form-control:focus {
            border-color: #4F46E5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
            outline: none;
        }

        .form-control::placeholder {
            color: #94A3B8;
        }

        /* Dynamic Fields Enhanced */
        .dynamic-fields {
            display: none;
            margin-top: 1.5rem;
            padding: 1.5rem;
            background: #F8FAFC;
            border-radius: 12px;
            border: 2px solid #E2E8F0;
            animation: slideDown 0.3s ease;
        }

        .dynamic-fields.show {
            display: block;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Date Picker Enhanced */
        .date-picker-wrapper {
            position: relative;
        }

        .date-picker-wrapper .form-control {
            padding-right: 2.5rem;
        }

        .date-picker-icon {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #64748B;
            pointer-events: none;
        }

        /* Template Select Enhanced */
        .template-select {
            width: 100%;
            padding: 0.875rem 1rem;
            border: 2px solid #E2E8F0;
            border-radius: 8px;
            font-size: 1rem;
            background-color: #F8FAFC;
            cursor: pointer;
            transition: all 0.2s ease;
            appearance: none;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20' fill='%2364748B'%3e%3cpath fill-rule='evenodd' d='M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z' clip-rule='evenodd'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 1rem center;
            background-size: 1.25rem;
            padding-right: 2.5rem;
        }

        .template-select:focus {
            border-color: #4F46E5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
            outline: none;
        }

        /* Phone Input Group Enhanced */
        .phone-input-group {
            display: flex;
            gap: 1rem;
        }

        .country-code-select {
            width: 120px;
            flex-shrink: 0;
            padding: 0.875rem;
            border: 2px solid #E2E8F0;
            border-radius: 8px;
            background-color: #F8FAFC;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .country-code-select:focus {
            border-color: #4F46E5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
            outline: none;
        }

        .flag-icon {
            width: 1.25rem;
            height: auto;
            margin-right: 0.5rem;
            vertical-align: middle;
        }

        /* Form Actions Enhanced */
        .modal-footer {
            padding: 1.5rem;
            border-top: 1px solid #E2E8F0;
            background: white;
        }

        .btn-submit {
            padding: 0.875rem 2rem;
            background: linear-gradient(135deg, #6366F1 0%, #4F46E5 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-submit:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.2);
        }

        .btn-cancel {
            padding: 0.875rem 2rem;
            background: white;
            color: #64748B;
            border: 2px solid #E2E8F0;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
            margin-right: 1rem;
        }

        .btn-cancel:hover {
            background: #F1F5F9;
            border-color: #CBD5E1;
        }

        /* Validation Styles Enhanced */
        .form-control.is-invalid {
            border-color: #EF4444;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23EF4444'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23EF4444' stroke='none'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 1rem center;
            background-size: 1rem;
            padding-right: 3rem;
        }

        .invalid-feedback {
            display: none;
            color: #EF4444;
            font-size: 0.875rem;
            margin-top: 0.5rem;
        }

        .form-control.is-invalid + .invalid-feedback {
            display: block;
        }

        /* Responsive Design Enhanced */
        @media (max-width: 768px) {
            .modal-body {
                padding: 1.5rem;
            }

            .form-type-radio {
                flex-direction: column;
                gap: 1rem;
            }

            .form-type-radio .form-check-label {
                justify-content: center;
                padding: 0.875rem 1.5rem;
            }

            .toggle-item {
                padding: 0.875rem;
            }

            .form-group {
                padding: 1.25rem;
            }

            .phone-input-group {
                flex-direction: column;
                gap: 0.75rem;
            }

            .country-code-select {
                width: 100%;
            }

            .modal-footer {
                flex-direction: column-reverse;
                gap: 0.75rem;
            }

            .btn-submit, .btn-cancel {
                width: 100%;
                margin: 0;
            }
        }

        @media (max-width: 480px) {
            .modal-header {
                padding: 1.25rem;
            }

            .modal-header .modal-title {
                font-size: 1.25rem;
            }

            .form-group {
                padding: 1rem;
            }

            .dynamic-fields {
                padding: 1rem;
            }
        }

        /* Color Theme Classes */
        .theme-primary {
            background: linear-gradient(135deg, #6366F1 0%, #4F46E5 100%);
        }

        .theme-success {
            background: linear-gradient(135deg, #10B981 0%, #059669 100%);
        }

        .theme-warning {
            background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%);
        }

        .theme-info {
            background: linear-gradient(135deg, #0EA5E9 0%, #0284C7 100%);
        }

        .theme-danger {
            background: linear-gradient(135deg, #EF4444 0%, #DC2626 100%);
        }

        /* Animation Classes */
        .fade-in {
            animation: fadeIn 0.3s ease;
        }

        .slide-up {
            animation: slideUp 0.3s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        @keyframes slideUp {
            from {
                transform: translateY(20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        /* Accessibility Enhancements */
        .visually-hidden {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            border: 0;
        }

        .focus-ring {
            outline: 2px solid #4F46E5;
            outline-offset: 2px;
        }

        /* High Contrast Mode */
        @media (prefers-contrast: high) {
            .form-control,
            .template-select,
            .country-code-select {
                border-width: 2px;
            }

            .toggle-slider {
                border: 2px solid currentColor;
            }

            .btn-submit,
            .btn-cancel {
                border-width: 2px;
            }
        }

        /* Enhanced Calendar Modal Styles */
        @media (max-width: 768px) {
            .date-range-modal .modal-dialog {
                margin: 0;
                max-width: 100%;
                height: 100vh;
                display: flex;
                flex-direction: column;
            }

            .date-range-modal .modal-content {
                height: 100%;
                border-radius: 0;
                display: flex;
                flex-direction: column;
            }

            .date-range-modal .modal-body {
                padding: 0;
                flex: 1;
                overflow-y: auto;
                -webkit-overflow-scrolling: touch; /* Smooth scrolling on iOS */
            }

            .date-range-modal .row {
                margin: 0;
                height: 100%;
            }

            /* Sidebar improvements */
            .date-range-sidebar {
                background: #F8FAFC;
                padding: 1rem;
                border-bottom: 1px solid #E2E8F0;
                border-right: none;
            }

            /* Calendar container improvements */
            .calendar-container {
                padding: 1rem;
                height: auto;
            }

            .calendar-grid {
                gap: 4px;
                margin-bottom: 1.5rem;
            }

            .calendar-day {
                min-height: 44px;
                font-size: 1rem;
                touch-action: manipulation; /* Prevent double-tap zoom */
                -webkit-tap-highlight-color: transparent; /* Remove tap highlight on iOS */
            }

            .calendar-day-header {
                font-size: 0.9rem;
                padding: 0.75rem 0;
                color: #64748B;
                font-weight: 600;
            }

            /* Navigation improvements */
            .month-navigation {
                gap: 0.5rem;
                margin-bottom: 1rem;
            }

            .nav-btn {
                width: 44px;
                height: 44px;
                display: flex;
                align-items: center;
                justify-content: center;
                background: white;
                border: 1px solid #E2E8F0;
                border-radius: 8px;
                font-size: 1.1rem;
                color: #1E293B;
                touch-action: manipulation;
            }

            .month-selector {
                flex: 1;
                display: flex;
                gap: 0.5rem;
            }

            .month-selector select {
                height: 44px;
                padding: 0 1rem;
                font-size: 1rem;
                border: 1px solid #E2E8F0;
                border-radius: 8px;
                background-color: white;
                appearance: none;
                background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
                background-repeat: no-repeat;
                background-position: right 0.75rem center;
                background-size: 16px 12px;
                flex: 1;
            }

            /* Date options improvements */
            .date-option {
                padding: 1rem;
                margin-bottom: 0.5rem;
                border-radius: 8px;
                background: white;
                border: 1px solid #E2E8F0;
                font-weight: 500;
                touch-action: manipulation;
            }

            .date-option.active {
                background: #4F46E5;
                color: white;
                border-color: #4F46E5;
            }

            /* Custom range inputs improvements */
            .custom-range-input {
                margin: 1rem 0;
                padding: 1rem;
                background: white;
                border-radius: 8px;
                border: 1px solid #E2E8F0;
            }

            .custom-range-input input {
                width: 100%;
                height: 44px;
                padding: 0 1rem;
                margin-bottom: 0.5rem;
                border: 1px solid #E2E8F0;
                border-radius: 8px;
                font-size: 1rem;
            }

            /* Selected dates display improvements */
            .selected-dates {
                margin: 1rem;
                padding: 1rem;
                background: white;
                border-radius: 8px;
                border: 1px solid #E2E8F0;
            }

            .selected-dates input {
                height: 44px;
                padding: 0 1rem;
                margin-bottom: 0.5rem;
                font-size: 1rem;
                background: #F8FAFC;
            }

            /* Footer improvements */
            .date-range-modal .modal-footer {
                padding: 1rem;
                border-top: 1px solid #E2E8F0;
                background: white;
                position: sticky;
                bottom: 0;
                z-index: 1;
            }

            .date-range-modal .btn {
                height: 44px;
                font-size: 1rem;
                font-weight: 500;
                padding: 0 1.5rem;
                border-radius: 8px;
                width: 100%;
                margin: 0.25rem 0;
            }

            /* Active states for touch */
            .calendar-day:active,
            .nav-btn:active,
            .date-option:active {
                opacity: 0.7;
                transform: scale(0.98);
                transition: all 0.1s ease;
            }

            /* Fix for iOS scroll bounce */
            .modal.date-range-modal {
                position: fixed;
                top: 0;
                right: 0;
                bottom: 0;
                left: 0;
                -webkit-overflow-scrolling: touch;
            }

            /* Improve calendar grid layout */
            .calendar-grid {
                display: grid;
                grid-template-columns: repeat(7, 1fr);
                gap: 4px;
                padding: 0.5rem;
                background: #F8FAFC;
                border-radius: 8px;
                margin-top: 0.5rem;
            }

            /* Improve calendar day appearance */
            .calendar-day {
                position: relative;
                padding-top: 100%; /* Make it square */
            }

            .calendar-day span {
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 1rem;
                font-weight: 500;
                border-radius: 8px;
                background: white;
                border: 1px solid #E2E8F0;
            }

            .calendar-day.selected span {
                background: #4F46E5;
                color: white;
                border-color: #4F46E5;
            }

            .calendar-day.today span {
                border: 2px solid #4F46E5;
                font-weight: 600;
            }
        }

        /* Additional optimizations for very small screens */
        @media (max-width: 360px) {
            .calendar-day span {
                font-size: 0.9rem;
            }

            .calendar-day-header {
                font-size: 0.8rem;
            }

            .month-selector select,
            .nav-btn {
                height: 40px;
                font-size: 0.9rem;
            }
        }

        /* Empty State Styles */
        .greetings-content.empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 300px;
            background-color: #ffffff;
            border-radius: 12px;
            padding: 2rem;
            text-align: center;
        }

        .empty-state-icon {
            width: 120px;
            height: 120px;
            margin-bottom: 1.5rem;
            background: #F8FAFC;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .empty-state-icon i {
            font-size: 3rem;
            color: #94A3B8;
            opacity: 0.8;
        }

        .empty-state-text {
            color: #475569;
            font-size: 1.25rem;
            font-weight: 500;
            margin-bottom: 0.5rem;
        }

        .empty-state-subtext {
            color: #64748B;
            font-size: 0.95rem;
            max-width: 400px;
            line-height: 1.5;
        }

        /* Empty State Animation */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .empty-state-icon,
        .empty-state-text,
        .empty-state-subtext {
            animation: fadeInUp 0.6s ease forwards;
        }

        .empty-state-icon {
            animation-delay: 0.1s;
        }

        .empty-state-text {
            animation-delay: 0.2s;
        }

        .empty-state-subtext {
            animation-delay: 0.3s;
        }

        /* Mobile Responsive Adjustments */
        @media (max-width: 768px) {
            .greetings-content.empty-state {
                min-height: 250px;
                padding: 1.5rem;
            }

            .empty-state-icon {
                width: 100px;
                height: 100px;
                margin-bottom: 1.25rem;
            }

            .empty-state-icon i {
                font-size: 2.5rem;
            }

            .empty-state-text {
                font-size: 1.1rem;
            }

            .empty-state-subtext {
                font-size: 0.9rem;
                padding: 0 1rem;
            }
        }
    </style>
</head>

<body>
    <!-- Header -->
    <header class="dashboard-header">
    <?php include '../includes/dashboard-header.php'; ?>
    </header>

    <div class="dashboard-container">
        <!-- Sidebar Overlay for Mobile -->
        <div class="sidebar-overlay" id="sidebarOverlay"></div>
        
        <!-- Sidebar -->
        <div class="sidebar" id="sidebarMenu">
                <?php include '../includes/sidebar.php'; ?>
            </div>
            
            <!-- Main Content Area -->
        <div class="main-content-area">
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
                    <div class="greetings-content empty-state" id="greetingsContent">
                        <div class="empty-state-icon">
                            <i class="fas fa-inbox"></i>
                        </div>
                        <div class="empty-state-text">No Records Found</div>
                        <div class="empty-state-subtext">
                            There are currently no greetings to display. Click the "+" button to add your first greeting.
                        </div>
                    </div>
                </div>
        </div>
    </div>
</div>

<!-- MODAL COMPONENTS -->

<!-- 1. Date Range Modal -->
<div class="modal fade date-range-modal" id="dateRangeModal" tabindex="-1" aria-labelledby="dateRangeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="dateRangeModalLabel">Select Date Range</h5>
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
                            <label for="daysUpToToday" class="visually-hidden">Days up to today</label>
                            <input type="number" id="daysUpToToday" min="1" max="365" value="1">
                            <span>days up to today</span>
                        </div>
                        <div class="custom-range-input">
                            <label for="daysStartingToday" class="visually-hidden">Days starting today</label>
                            <input type="number" id="daysStartingToday" min="1" max="365" value="1">
                            <span>days starting today</span>
                        </div>
                    </div>
                    
                    <!-- Calendar Section -->
                    <div class="col-md-8 col-12 calendar-container">
                        <!-- Selected Dates Display -->
                        <div class="selected-dates">
                            <div class="row">
                                <div class="col-md-6 col-12 mb-2">
                                    <label for="startDate" class="visually-hidden">Start Date</label>
                                    <input type="text" id="startDate" readonly>
                                </div>
                                <div class="col-md-6 col-12 mb-2">
                                    <label for="endDate" class="visually-hidden">End Date</label>
                                    <input type="text" id="endDate" readonly>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Calendar Navigation -->
                        <div class="calendar-header">
                            <div class="month-navigation">
                                <button type="button" class="nav-btn" id="prevMonth">
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
                                
                                <button type="button" class="nav-btn" id="nextMonth">
                                    <i class="fas fa-chevron-right"></i>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Calendar Grids -->
                        <div class="row">
                            <div class="col-md-6 col-12 mb-4">
                                <div class="calendar-grid" id="currentMonthCalendar">
                                    <!-- Calendar days will be dynamically generated here -->
                                </div>
                            </div>
                            <div class="col-md-6 col-12">
                                <div class="calendar-grid" id="nextMonthCalendar">
                                    <!-- Next month calendar days will be dynamically generated here -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="submitDateRange">Apply</button>
            </div>
        </div>
    </div>
</div>

    <!-- 2. Import Greetings Users Modal -->
    <div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="importModalLabel">Import Greetings Users</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                    <button type="button" class="download-format-btn">
                    Download Format
                </button>
                
                <div class="notes-section">
                    <div class="notes-title">Notes:-</div>
                    <ul class="notes-list">
                        <li>Anniversary and Birth dates entered in this format. DD-MM-YYYY.</li>
                        <li>Other Type Greetings dates entered in this format. DD-MM-YYYY HH:mm.</li>
                        <li>Birth and Anniversary dates cannot be future dates.</li>
                        <li>Country code is required with Mobile numbers.</li>
                    </ul>
                </div>
                
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
                
                <div class="file-upload-section">
                        <label class="file-upload-label">
                            Upload File <span class="required">*</span>
                        </label>
                    <div class="d-flex align-items-center">
                        <input type="file" id="fileUpload" class="d-none">
                            <button type="button" class="file-upload-btn" onclick="document.getElementById('fileUpload').click()">
                                Choose File
                            </button>
                            <span class="file-name">No file chosen</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary">Import</button>
            </div>
        </div>
    </div>
</div>

<!-- 3. Add Greetings Modal -->
<div class="modal fade" id="addGreetingModal" tabindex="-1" aria-labelledby="addGreetingModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addGreetingModalLabel">Add Greetings</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                    <!-- Form Type Selection -->
                    <div class="form-type-selection">
                        <div class="form-type-radio">
                    <div class="form-check">
                                <input class="form-check-input" type="radio" name="contactType" id="customerType" checked>
                        <label class="form-check-label" for="customerType">Customer</label>
                    </div>
                    <div class="form-check">
                                <input class="form-check-input" type="radio" name="contactType" id="otherType">
                        <label class="form-check-label" for="otherType">Other</label>
                            </div>
                    </div>
                </div>
                
                    <!-- Customer Form -->
                    <div id="customerForm">
                        <!-- Toggle Switches Section -->
                        <div class="toggle-section">
                            <div class="toggle-switch-group">
                                <div class="toggle-item">
                                    <span class="toggle-label">Birthday</span>
                                    <label class="toggle-switch">
                                        <input type="checkbox" id="customerBirthdayToggle">
                                        <span class="toggle-slider"></span>
                                    </label>
                                </div>
                                <div class="toggle-item">
                                    <span class="toggle-label">Anniversary</span>
                                    <label class="toggle-switch">
                                        <input type="checkbox" id="customerAnniversaryToggle">
                                        <span class="toggle-slider"></span>
                                    </label>
                                </div>
                                <div class="toggle-item">
                                    <span class="toggle-label">Other</span>
                                    <label class="toggle-switch">
                                        <input type="checkbox" id="customerOtherToggle">
                                        <span class="toggle-slider"></span>
                                    </label>
                                </div>
                    </div>
                </div>
                
                        <!-- Customer Information -->
                        <div class="form-group">
                            <label class="form-label">Customer <span class="required">*</span></label>
                            <select class="form-control" required>
                                <option value="" selected disabled>Select Customer</option>
                            </select>
                    </div>

                        <div class="form-group">
                            <label class="form-label">Contact No.</label>
                            <input type="text" class="form-control" readonly>
                </div>
                
                        <div class="form-group">
                            <label class="form-label">Company Name</label>
                            <input type="text" class="form-control" readonly>
                        </div>

                        <!-- Birthday Dynamic Fields -->
                        <div class="dynamic-fields" id="customerBirthdayFields">
                            <div class="form-group">
                                <label class="form-label">Birth Date <span class="required">*</span></label>
                                <div class="date-picker-wrapper">
                                    <input type="date" class="form-control" required>
                                    <i class="fas fa-calendar date-picker-icon"></i>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Greetings Template <span class="required">*</span></label>
                                <select class="template-select" required>
                                    <option value="" selected disabled>Select Birthday Template</option>
                                </select>
                    </div>
                </div>
                
                        <!-- Anniversary Dynamic Fields -->
                        <div class="dynamic-fields" id="customerAnniversaryFields">
                <div class="form-group">
                                <label class="form-label">Anniversary Date <span class="required">*</span></label>
                                <div class="date-picker-wrapper">
                                    <input type="date" class="form-control" required>
                                    <i class="fas fa-calendar date-picker-icon"></i>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Greetings Template <span class="required">*</span></label>
                                <select class="template-select" required>
                                    <option value="" selected disabled>Select Anniversary Template</option>
                    </select>
                            </div>
                </div>
                
                        <!-- Other Dynamic Fields -->
                        <div class="dynamic-fields" id="customerOtherFields">
                <div class="form-group">
                                <label class="form-label">Date <span class="required">*</span></label>
                                <div class="date-picker-wrapper">
                                    <input type="datetime-local" class="form-control" required>
                                    <i class="fas fa-calendar date-picker-icon"></i>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Greetings Template <span class="required">*</span></label>
                                <select class="template-select" required>
                                    <option value="" selected disabled>Select Custom Template</option>
                                </select>
                            </div>
                        </div>
                </div>
                
                    <!-- Other Contact Form -->
                    <div id="otherForm" style="display: none;">
                        <!-- Toggle Switches Section -->
                        <div class="toggle-section">
                            <div class="toggle-switch-group">
                                <div class="toggle-item">
                                    <span class="toggle-label">Birthday</span>
                                    <label class="toggle-switch">
                                        <input type="checkbox" id="otherBirthdayToggle">
                                        <span class="toggle-slider"></span>
                                    </label>
                                </div>
                                <div class="toggle-item">
                                    <span class="toggle-label">Anniversary</span>
                                    <label class="toggle-switch">
                                        <input type="checkbox" id="otherAnniversaryToggle">
                                        <span class="toggle-slider"></span>
                                    </label>
                                </div>
                                <div class="toggle-item">
                                    <span class="toggle-label">Other</span>
                                    <label class="toggle-switch">
                                        <input type="checkbox" id="otherOtherToggle">
                                        <span class="toggle-slider"></span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Contact Information -->
                <div class="form-group">
                            <label class="form-label">Name <span class="required">*</span></label>
                            <input type="text" class="form-control" placeholder="Enter Name" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Contact No. <span class="required">*</span></label>
                            <div class="phone-input-group">
                                <select class="country-code-select">
                                    <option value="+91">
                                        <img src="path/to/india-flag.png" alt="India" class="flag-icon"> +91
                                    </option>
                                </select>
                                <input type="tel" class="form-control phone-number-input" required>
                            </div>
                        </div>

                        <!-- Birthday Dynamic Fields -->
                        <div class="dynamic-fields" id="otherBirthdayFields">
                            <div class="form-group">
                                <label class="form-label">Birth Date <span class="required">*</span></label>
                                <div class="date-picker-wrapper">
                                    <input type="date" class="form-control" required>
                                    <i class="fas fa-calendar date-picker-icon"></i>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Greetings Template <span class="required">*</span></label>
                                <select class="template-select" required>
                                    <option value="" selected disabled>Select Birthday Template</option>
                                </select>
                            </div>
                        </div>

                        <!-- Anniversary Dynamic Fields -->
                        <div class="dynamic-fields" id="otherAnniversaryFields">
                            <div class="form-group">
                                <label class="form-label">Anniversary Date <span class="required">*</span></label>
                                <div class="date-picker-wrapper">
                                    <input type="date" class="form-control" required>
                                    <i class="fas fa-calendar date-picker-icon"></i>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Greetings Template <span class="required">*</span></label>
                                <select class="template-select" required>
                                    <option value="" selected disabled>Select Anniversary Template</option>
                                </select>
                            </div>
                        </div>

                        <!-- Other Dynamic Fields -->
                        <div class="dynamic-fields" id="otherOtherFields">
                            <div class="form-group">
                                <label class="form-label">Date <span class="required">*</span></label>
                                <div class="date-picker-wrapper">
                                    <input type="datetime-local" class="form-control" required>
                                    <i class="fas fa-calendar date-picker-icon"></i>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Greetings Template <span class="required">*</span></label>
                                <select class="template-select" required>
                                    <option value="" selected disabled>Select Custom Template</option>
                                </select>
                            </div>
                        </div>
                </div>
            </div>
            <div class="modal-footer">
                    <button type="button" class="btn-cancel" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn-submit">Submit</button>
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
            // Initialize Bootstrap modals
            const dateRangeModal = new bootstrap.Modal(document.getElementById('dateRangeModal'));
            const importModal = new bootstrap.Modal(document.getElementById('importModal'));
            const addGreetingModal = new bootstrap.Modal(document.getElementById('addGreetingModal'));
            const deleteConfirmModal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
        
            // File upload handling
            const fileUpload = document.getElementById('fileUpload');
            const fileName = document.querySelector('.file-name');
            
            fileUpload?.addEventListener('change', function() {
                fileName.textContent = this.files[0] ? this.files[0].name : 'No file chosen';
            });

            // Form type selection handling
            const customerType = document.getElementById('customerType');
            const otherType = document.getElementById('otherType');
            const customerForm = document.getElementById('customerForm');
            const otherForm = document.getElementById('otherForm');

            function toggleFormType() {
                if (customerType.checked) {
                    customerForm.style.display = 'block';
                    otherForm.style.display = 'none';
                } else {
                    customerForm.style.display = 'none';
                    otherForm.style.display = 'block';
                }
            }

            customerType?.addEventListener('change', toggleFormType);
            otherType?.addEventListener('change', toggleFormType);

            // Toggle switch handling for Customer form
            const customerBirthdayToggle = document.getElementById('customerBirthdayToggle');
            const customerAnniversaryToggle = document.getElementById('customerAnniversaryToggle');
            const customerOtherToggle = document.getElementById('customerOtherToggle');
            const customerBirthdayFields = document.getElementById('customerBirthdayFields');
            const customerAnniversaryFields = document.getElementById('customerAnniversaryFields');
            const customerOtherFields = document.getElementById('customerOtherFields');

            function toggleCustomerFields(toggle, fields) {
                if (toggle.checked) {
                    fields.classList.add('show');
                    // Enable required validation for visible fields
                    fields.querySelectorAll('[required]').forEach(input => {
                        input.setAttribute('required', '');
                    });
                } else {
                    fields.classList.remove('show');
                    // Disable required validation for hidden fields
                    fields.querySelectorAll('[required]').forEach(input => {
                        input.removeAttribute('required');
                    });
                    // Clear fields when hiding
                    fields.querySelectorAll('input, select').forEach(input => {
                        input.value = '';
                    });
                }
            }

            customerBirthdayToggle?.addEventListener('change', () => {
                toggleCustomerFields(customerBirthdayToggle, customerBirthdayFields);
            });

            customerAnniversaryToggle?.addEventListener('change', () => {
                toggleCustomerFields(customerAnniversaryToggle, customerAnniversaryFields);
            });

            customerOtherToggle?.addEventListener('change', () => {
                toggleCustomerFields(customerOtherToggle, customerOtherFields);
            });

            // Toggle switch handling for Other form
            const otherBirthdayToggle = document.getElementById('otherBirthdayToggle');
            const otherAnniversaryToggle = document.getElementById('otherAnniversaryToggle');
            const otherOtherToggle = document.getElementById('otherOtherToggle');
            const otherBirthdayFields = document.getElementById('otherBirthdayFields');
            const otherAnniversaryFields = document.getElementById('otherAnniversaryFields');
            const otherOtherFields = document.getElementById('otherOtherFields');

            otherBirthdayToggle?.addEventListener('change', () => {
                toggleCustomerFields(otherBirthdayToggle, otherBirthdayFields);
            });

            otherAnniversaryToggle?.addEventListener('change', () => {
                toggleCustomerFields(otherAnniversaryToggle, otherAnniversaryFields);
            });

            otherOtherToggle?.addEventListener('change', () => {
                toggleCustomerFields(otherOtherToggle, otherOtherFields);
            });

            // Date validation
            function validateDate(input) {
                const selectedDate = new Date(input.value);
                const today = new Date();
                today.setHours(0, 0, 0, 0);

                if (input.type === 'date' && selectedDate > today) {
                    input.setCustomValidity('Date cannot be in the future');
                } else {
                    input.setCustomValidity('');
                }
            }

            // Add date validation to birthday and anniversary date inputs
            document.querySelectorAll('input[type="date"]').forEach(input => {
                input.addEventListener('change', () => validateDate(input));
            });

            // Customer selection handling
            const customerSelect = customerForm.querySelector('select');
            customerSelect?.addEventListener('change', function() {
                if (this.value) {
                    // Simulate fetching customer data
                    const mockCustomerData = {
                        contactNo: '+91 9876543210',
                        companyName: 'ABC Company'
                    };
                    
                    // Update readonly fields
                    customerForm.querySelector('input[placeholder="Contact No."]').value = mockCustomerData.contactNo;
                    customerForm.querySelector('input[placeholder="Company Name"]').value = mockCustomerData.companyName;
                }
            });

            // Form submission handling
            const submitBtn = document.querySelector('.btn-submit');
            submitBtn?.addEventListener('click', function(e) {
                e.preventDefault();

                // Get the active form
                const activeForm = customerType.checked ? customerForm : otherForm;

                // Validate only visible required fields
                const visibleRequiredFields = activeForm.querySelectorAll('.show [required], [required]:not(.dynamic-fields *)');
                let isValid = true;

                visibleRequiredFields.forEach(field => {
                    if (!field.value) {
                        isValid = false;
                        field.classList.add('is-invalid');
                    } else {
                        field.classList.remove('is-invalid');
                    }
                });

                if (isValid) {
                    // Handle form submission
                    console.log('Form submitted successfully');
                    // Close modal
                    const modal = document.getElementById('addGreetingModal');
                    const bsModal = bootstrap.Modal.getInstance(modal);
                    bsModal.hide();
                }
            });

            // Show modals on button click
            document.getElementById('uploadBtn')?.addEventListener('click', () => importModal.show());
            document.getElementById('addNewBtn')?.addEventListener('click', () => addGreetingModal.show());
            document.getElementById('dateRangeBtn')?.addEventListener('click', () => {
                dateRangeModal.show();
                initializeCalendar();
            });

            // Search input handler
            document.getElementById('searchInput')?.addEventListener('input', function() {
                const searchTerm = this.value.trim().toLowerCase();
                console.log('Searching for:', searchTerm);
                // In a real implementation, you would filter the displayed greetings based on the search term
            });

            // Calendar Icon Click Handler
            document.getElementById('dateRangeBtn')?.addEventListener('click', function() {
                dateRangeModal.show();
                initializeCalendar();
            });

            // Delete Button Click Handler
            document.getElementById('deleteBtn')?.addEventListener('click', function() {
                const selectedItems = document.querySelectorAll('.greeting-item.selected');
                if (selectedItems.length === 0) {
                    deleteConfirmModal.show();
                } else {
                    if (confirm(`Are you sure you want to delete ${selectedItems.length} selected greeting(s)?`)) {
                        selectedItems.forEach(item => item.remove());
                        showNotification('Success', 'Selected greetings have been deleted successfully');
                    }
                }
            });

            // Calendar Functionality
            function initializeCalendar() {
                const today = new Date();
                updateDateInputs(today, today);
                updateCalendarView(today);

                // Initialize month and year selectors
                const monthSelect = document.getElementById('monthSelect');
                const yearSelect = document.getElementById('yearSelect');
                
                monthSelect.value = today.getMonth();
                yearSelect.value = today.getFullYear();

                // Add event listeners for month/year selection
                monthSelect.addEventListener('change', () => {
                    const newDate = new Date(yearSelect.value, monthSelect.value);
                    updateCalendarView(newDate);
                });

                yearSelect.addEventListener('change', () => {
                    const newDate = new Date(yearSelect.value, monthSelect.value);
                    updateCalendarView(newDate);
                });

                // Date option clicks
                document.querySelectorAll('.date-option').forEach(option => {
                    option.addEventListener('click', function() {
                        document.querySelectorAll('.date-option').forEach(opt => 
                            opt.classList.remove('active'));
                        this.classList.add('active');
                        handleDateOptionClick(this.dataset.option);
                    });
                });

                // Custom range inputs
                document.getElementById('daysUpToToday').addEventListener('change', function() {
                    const days = parseInt(this.value) || 1;
                    const end = new Date();
                    const start = new Date();
                    start.setDate(end.getDate() - days + 1);
                    updateDateInputs(start, end);
                    updateCalendarView(start);
                });

                document.getElementById('daysStartingToday').addEventListener('change', function() {
                    const days = parseInt(this.value) || 1;
                    const start = new Date();
                    const end = new Date();
                    end.setDate(start.getDate() + days - 1);
                    updateDateInputs(start, end);
                    updateCalendarView(start);
                });

                // Month navigation
                document.getElementById('prevMonth').addEventListener('click', () => {
                    const current = new Date(yearSelect.value, monthSelect.value);
                    current.setMonth(current.getMonth() - 1);
                    monthSelect.value = current.getMonth();
                    yearSelect.value = current.getFullYear();
                    updateCalendarView(current);
                });

                document.getElementById('nextMonth').addEventListener('click', () => {
                    const current = new Date(yearSelect.value, monthSelect.value);
                    current.setMonth(current.getMonth() + 1);
                    monthSelect.value = current.getMonth();
                    yearSelect.value = current.getFullYear();
                    updateCalendarView(current);
                });
            }

            function handleDateOptionClick(option) {
                const today = new Date();
                let start = new Date();
                let end = new Date();

                switch(option) {
                    case 'today':
                        break;
                    case 'yesterday':
                        start.setDate(today.getDate() - 1);
                        end = new Date(start);
                        break;
                    case 'this-week':
                        start.setDate(today.getDate() - today.getDay());
                        end.setDate(start.getDate() + 6);
                        break;
                    case 'last-week':
                        start.setDate(today.getDate() - today.getDay() - 7);
                        end.setDate(start.getDate() + 6);
                        break;
                    case 'this-month':
                        start = new Date(today.getFullYear(), today.getMonth(), 1);
                        end = new Date(today.getFullYear(), today.getMonth() + 1, 0);
                        break;
                    case 'last-month':
                        start = new Date(today.getFullYear(), today.getMonth() - 1, 1);
                        end = new Date(today.getFullYear(), today.getMonth(), 0);
                        break;
                }

                updateDateInputs(start, end);
                updateCalendarView(start);
            }

            function updateDateInputs(start, end) {
                document.getElementById('startDate').value = formatDate(start);
                document.getElementById('endDate').value = formatDate(end);
            }

            function formatDate(date) {
                const months = ['January', 'February', 'March', 'April', 'May', 'June', 
                              'July', 'August', 'September', 'October', 'November', 'December'];
                return `${months[date.getMonth()]} ${date.getDate()}, ${date.getFullYear()}`;
            }

            function updateCalendarView(date) {
                const month = date.getMonth();
                const year = date.getFullYear();

                // Generate current month calendar
                generateCalendar(month, year, 'currentMonthCalendar');
                // Generate next month calendar
                generateCalendar(month + 1, year, 'nextMonthCalendar');
            }

            function generateCalendar(month, year, containerId) {
                const container = document.getElementById(containerId);
                if (!container) return;

                const firstDay = new Date(year, month, 1);
                const lastDay = new Date(year, month + 1, 0);
                const startDay = firstDay.getDay();
                const totalDays = lastDay.getDate();
                const today = new Date();
                const selectedStart = new Date(document.getElementById('startDate').value);
                const selectedEnd = new Date(document.getElementById('endDate').value);

                let html = `
                    <div class="calendar-day-header">S</div>
                    <div class="calendar-day-header">M</div>
                    <div class="calendar-day-header">T</div>
                    <div class="calendar-day-header">W</div>
                    <div class="calendar-day-header">T</div>
                    <div class="calendar-day-header">F</div>
                    <div class="calendar-day-header">S</div>
                `;
                
                // Empty cells for days before start of month
                for (let i = 0; i < startDay; i++) {
                    html += '<div class="calendar-day disabled"><span></span></div>';
                }

                // Days of the month
                for (let day = 1; day <= totalDays; day++) {
                    const currentDate = new Date(year, month, day);
                    const isToday = currentDate.toDateString() === today.toDateString();
                    const isSelected = currentDate >= selectedStart && currentDate <= selectedEnd;
                    const isPast = currentDate < new Date(today.setHours(0, 0, 0, 0));

                    let classes = ['calendar-day'];
                    if (isToday) classes.push('today');
                    if (isSelected) classes.push('selected');
                    if (isPast) classes.push('disabled');

                    html += `
                        <div class="${classes.join(' ')}" 
                             data-date="${year}-${month + 1}-${day}"
                             role="button"
                             tabindex="0">
                            <span>${day}</span>
                        </div>`;
                }

                container.innerHTML = html;

                // Add click handlers with improved touch feedback
                container.querySelectorAll('.calendar-day:not(.disabled)').forEach(dayEl => {
                    dayEl.addEventListener('click', function() {
                        const selectedDate = new Date(this.dataset.date);
                        updateDateInputs(selectedDate, selectedDate);
                        
                        // Update visual selection
                        document.querySelectorAll('.calendar-day').forEach(d => 
                            d.classList.remove('selected'));
                        this.classList.add('selected');

                        // Add touch feedback
                        this.style.transform = 'scale(0.95)';
                        setTimeout(() => {
                            this.style.transform = '';
                        }, 150);
                    });

                    // Add keyboard accessibility
                    dayEl.addEventListener('keydown', function(e) {
                        if (e.key === 'Enter' || e.key === ' ') {
                            e.preventDefault();
                            this.click();
                        }
                    });
                });
            }

            // Initialize calendar when modal is shown
            document.getElementById('dateRangeModal')?.addEventListener('shown.bs.modal', function() {
                initializeCalendar();
            });

            // Handle date range submission
            document.getElementById('submitDateRange')?.addEventListener('click', function() {
                const startDate = document.getElementById('startDate').value;
                const endDate = document.getElementById('endDate').value;
                console.log('Selected date range:', { startDate, endDate });
                
                // Close modal
                dateRangeModal.hide();
                
                // Show success notification
                showNotification('Success', `Date range selected: ${startDate} - ${endDate}`);
            });

            // Helper function to show notifications
            function showNotification(title, message) {
                const notification = document.createElement('div');
                notification.className = 'notification';
                notification.innerHTML = `
                    <div class="notification-content">
                        <h4>${title}</h4>
                        <p>${message}</p>
                    </div>
                `;
                document.body.appendChild(notification);
                
                setTimeout(() => {
                    notification.classList.add('show');
                }, 100);

                setTimeout(() => {
                    notification.classList.remove('show');
                    setTimeout(() => notification.remove(), 300);
                }, 3000);
            }
    });
</script>

<!-- Add mobile sidebar toggle script -->
<script>
    // Mobile sidebar toggle functionality
    const sidebarMenu = document.getElementById('sidebarMenu');
    const sidebarOverlay = document.getElementById('sidebarOverlay');
    const toggleSidebarBtn = document.querySelector('.navbar-toggler');

    if (toggleSidebarBtn) {
        toggleSidebarBtn.addEventListener('click', () => {
            sidebarMenu.classList.toggle('show');
            sidebarOverlay.classList.toggle('show');
        });
    }

    if (sidebarOverlay) {
        sidebarOverlay.addEventListener('click', () => {
            sidebarMenu.classList.remove('show');
            sidebarOverlay.classList.remove('show');
        });
    }

    // Close sidebar on window resize (if in mobile view)
    window.addEventListener('resize', () => {
        if (window.innerWidth > 768) {
            sidebarMenu.classList.remove('show');
            sidebarOverlay.classList.remove('show');
        }
    });
</script>

</body>
</html>