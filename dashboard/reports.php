<?php
// Start session if not already started
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>General Reports</title>
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
            --primary-light: #E8E5F7;
            --text-primary: #1E293B;
            --text-secondary: #64748B;
            --card-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            --header-height: 60px;
            --sidebar-width: 250px;
        }

        /* General Styles */
        body {
            font-family: 'Inter', sans-serif;
            background-color: #F8FAFC;
            color: var(--text-primary);
            padding-top: var(--header-height);
            min-height: 100vh;
            margin: 0;
            overflow-x: hidden;
        }

        /* Page Title */
        .page-title {
            font-size: 2rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 2rem;
            padding: 1rem 0;
        }

        /* Reports Grid */
        .reports-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 2rem;
            padding: 0 1rem;
        }

        /* Report Card */
        .report-card {
            background: white;
            border-radius: 12px;
            box-shadow: var(--card-shadow);
            overflow: hidden;
        }

        .report-card-header {
            background: var(--primary-light);
            padding: 1rem;
            text-align: center;
        }

        .report-card-header h2 {
            color: var(--primary-color);
            font-size: 1.5rem;
            font-weight: 600;
            margin: 0;
        }

        /* Report List */
        .report-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .report-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #E2E8F0;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .report-item:last-child {
            border-bottom: none;
        }

        .report-item:hover {
            background-color: #F1F5F9;
        }

        .report-item span {
            color: var(--text-secondary);
            font-weight: 500;
        }

        .report-item i {
            color: var(--primary-color);
            font-size: 0.875rem;
        }

        /* Sidebar Styles */
        .sidebar {
            position: fixed;
            top: var(--header-height);
            right: 0;
            bottom: 0;
            width: var(--sidebar-width);
            background: white;
            box-shadow: -2px 0 4px rgba(0, 0, 0, 0.1);
            z-index: 1020;
            overflow-y: auto;
            transition: transform 0.3s ease;
        }

        /* Main Content Area */
        .main-content-area {
            margin-right: var(--sidebar-width);
            padding: 20px;
            min-height: calc(100vh - var(--header-height));
            transition: margin-right 0.3s ease;
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            :root {
                --sidebar-width: 240px;
            }

            .sidebar {
                transform: translateX(100%);
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .main-content-area {
                margin-right: 0;
                width: 100%;
                padding: 16px;
            }

            .reports-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
                padding: 0;
            }

            .page-title {
                font-size: 1.5rem;
                padding: 0.75rem 0;
                margin-bottom: 1rem;
            }

            /* Overlay for mobile */
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

        /* Report Card Responsive Adjustments */
        @media (max-width: 576px) {
            .report-card-header h2 {
                font-size: 1.125rem;
            }

            .report-item {
                padding: 0.75rem 1rem;
                font-size: 0.875rem;
            }

            .report-item i {
                font-size: 0.75rem;
            }
        }

        /* Improved Scrollbar for Sidebar */
        .sidebar::-webkit-scrollbar {
            width: 4px;
        }

        .sidebar::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 4px;
        }

        .sidebar::-webkit-scrollbar-thumb:hover {
            background: #555;
        }

        /* High Contrast and Accessibility Improvements */
        @media (prefers-contrast: high) {
            .sidebar {
                border-left: 2px solid #000;
            }
        }

        /* Touch-friendly adjustments */
        @media (hover: none) {
            .report-item {
                padding: 0.875rem 1rem;
            }

            .report-item:active {
                background-color: var(--primary-light);
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
        <!-- Sidebar -->
        <div class="sidebar" id="sidebarMenu">
            <?php include '../includes/sidebar.php'; ?>
        </div>

        <!-- Main Content Area -->
        <div class="main-content-area">
            <div class="container-fluid">
                <h1 class="page-title">General Reports</h1>

                <div class="reports-grid">
                    <!-- Lead Reports Card -->
                    <div class="report-card">
                        <div class="report-card-header">
                            <h2>Lead</h2>
                        </div>
                        <ul class="report-list">
                            <li class="report-item" onclick="location.href='reports/lead-status.php'" tabindex="0">
                                <span>Status Wise Report</span>
                                <i class="fas fa-chevron-right"></i>
                            </li>
                            <li class="report-item" onclick="location.href='reports/lead-status-time.php'" tabindex="0">
                                <span>Status With Time Wise Report</span>
                                <i class="fas fa-chevron-right"></i>
                            </li>
                            <li class="report-item" onclick="location.href='reports/lead-third-party.php'" tabindex="0">
                                <span>Third Party Source Wise Report</span>
                                <i class="fas fa-chevron-right"></i>
                            </li>
                            <li class="report-item" onclick="location.href='reports/lead-source.php'" tabindex="0">
                                <span>Source Wise Report</span>
                                <i class="fas fa-chevron-right"></i>
                            </li>
                            <li class="report-item" onclick="location.href='reports/lead-label.php'" tabindex="0">
                                <span>Label Wise Report</span>
                                <i class="fas fa-chevron-right"></i>
                            </li>
                            <li class="report-item" onclick="location.href='reports/lead-staff-source.php'" tabindex="0">
                                <span>Staff & Source Wise Lead Status Report</span>
                                <i class="fas fa-chevron-right"></i>
                            </li>
                            <li class="report-item" onclick="location.href='reports/lead-staff-label.php'" tabindex="0">
                                <span>Staff & Label Wise Lead Status Report</span>
                                <i class="fas fa-chevron-right"></i>
                            </li>
                        </ul>
                    </div>

                    <!-- Task Reports Card -->
                    <div class="report-card">
                        <div class="report-card-header">
                            <h2>Task</h2>
                        </div>
                        <ul class="report-list">
                            <li class="report-item" onclick="location.href='reports/task-status.php'" tabindex="0">
                                <span>Status Wise Report</span>
                                <i class="fas fa-chevron-right"></i>
                            </li>
                            <li class="report-item" onclick="location.href='reports/task-label.php'" tabindex="0">
                                <span>Label Wise Report</span>
                                <i class="fas fa-chevron-right"></i>
                            </li>
                            <li class="report-item" onclick="location.href='reports/task-priority.php'" tabindex="0">
                                <span>Priority Wise Report</span>
                                <i class="fas fa-chevron-right"></i>
                            </li>
                            <li class="report-item" onclick="location.href='reports/task-delay.php'" tabindex="0">
                                <span>Delay Task Report</span>
                                <i class="fas fa-chevron-right"></i>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>

    <script>
        // Add keyboard navigation support
        document.querySelectorAll('.report-item').forEach(item => {
            item.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    item.click();
                }
            });
        });
    </script>

    <!-- Mobile Sidebar Toggle Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebarMenu = document.getElementById('sidebarMenu');
            const sidebarOverlay = document.getElementById('sidebarOverlay');
            const toggleSidebarBtn = document.querySelector('.navbar-toggler');

            // Add overlay div if not present
            if (!sidebarOverlay) {
                const overlay = document.createElement('div');
                overlay.id = 'sidebarOverlay';
                overlay.className = 'sidebar-overlay';
                document.body.appendChild(overlay);
            }

            if (toggleSidebarBtn) {
                toggleSidebarBtn.addEventListener('click', () => {
                    sidebarMenu.classList.toggle('show');
                    document.getElementById('sidebarOverlay').classList.toggle('show');
                });
            }

            document.getElementById('sidebarOverlay').addEventListener('click', () => {
                sidebarMenu.classList.remove('show');
                document.getElementById('sidebarOverlay').classList.remove('show');
            });

            // Close sidebar on window resize (if in mobile view)
            window.addEventListener('resize', () => {
                if (window.innerWidth > 768) {
                    sidebarMenu.classList.remove('show');
                    document.getElementById('sidebarOverlay').classList.remove('show');
                }
            });
        });
    </script>
</body>
</html> 