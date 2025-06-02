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

        body {
            font-family: 'Inter', sans-serif;
            background-color: #F8FAFC;
            color: var(--text-primary);
            margin: 0;
            overflow-x: hidden;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .dashboard-header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1030;
            background: white;
            box-shadow: var(--card-shadow);
            height: var(--header-height);
        }

        .dashboard-container {
            display: flex;
            min-height: calc(100vh - var(--header-height));
            padding-top: var(--header-height);
            position: relative;
        }

        .main-content-area {
            flex: 1;
            padding: 2rem;
            margin-left: var(--sidebar-width);
            transition: margin-left 0.3s ease;
            background-color: #F8FAFC;
            min-height: calc(100vh - var(--header-height));
            display: flex;
            flex-direction: column;
        }

        .page-title {
            font-size: 1.75rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 2rem;
            padding: 0;
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: var(--card-shadow);
        }

        .reports-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 2rem;
            padding: 0;
        }

        .report-card {
            background: white;
            border-radius: 12px;
            box-shadow: var(--card-shadow);
            overflow: hidden;
            height: 100%;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            border: 1px solid #E2E8F0;
        }

        .report-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .report-card-header {
            background: white;
            padding: 1.5rem;
            border-bottom: 2px solid var(--primary-color);
        }

        .report-card-header h2 {
            color: var(--primary-color);
            font-size: 1.25rem;
            font-weight: 600;
            margin: 0;
            text-align: left;
        }

        .report-list {
            list-style: none;
            padding: 0;
            margin: 0;
            background: white;
        }

        .report-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #E2E8F0;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            color: var(--text-primary);
        }

        .report-item:last-child {
            border-bottom: none;
        }

        .report-item:hover {
            background-color: #F1F5F9;
        }

        .report-item-content {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            flex: 1;
        }

        .report-item-icon {
            color: var(--primary-color);
            width: 20px;
            text-align: center;
        }

        .report-item span {
            color: var(--text-secondary);
            font-weight: 500;
            font-size: 0.95rem;
        }

        .report-item i.fa-chevron-right {
            color: var(--text-secondary);
            font-size: 0.875rem;
            opacity: 0.5;
            transition: opacity 0.2s ease, transform 0.2s ease;
        }

        .report-item:hover i.fa-chevron-right {
            opacity: 1;
            transform: translateX(2px);
        }

        @media (max-width: 1200px) {
            .reports-grid {
                grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
                gap: 1.5rem;
            }
        }

        @media (max-width: 991.98px) {
            .main-content-area {
                margin-left: 0;
                padding: 1.5rem;
            }

            .page-title {
                font-size: 1.5rem;
                margin-bottom: 1.5rem;
            }

            .dashboard-footer {
                margin-left: 0;
            }
        }

        @media (max-width: 767.98px) {
            .reports-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            .report-card-header {
                padding: 1rem;
            }

            .report-card-header h2 {
                font-size: 1.125rem;
            }

            .report-item {
                padding: 0.875rem 1.25rem;
            }

            .report-item span {
                font-size: 0.875rem;
            }

            .dashboard-container {
                flex-direction: column;
            }

            .main-content-area {
                padding: 1rem;
            }

            .dashboard-footer {
                padding: 0.75rem 0;
            }

            .footer-content {
                flex-direction: column;
                text-align: center;
                gap: 0.5rem;
                padding: 0 1rem;
            }
        }

        /* Accessibility Improvements */
        .report-item:focus {
            outline: 2px solid var(--primary-color);
            outline-offset: -2px;
        }

        .report-item:focus:not(:focus-visible) {
            outline: none;
        }

        /* High Contrast Mode */
        @media (prefers-contrast: high) {
            .report-card {
                border: 2px solid var(--text-primary);
            }

            .report-item {
                border-bottom: 2px solid var(--text-primary);
            }
        }

        /* Footer Styles */
        .dashboard-footer {
            background: white;
            padding: 1rem 0;
            border-top: 1px solid #E2E8F0;
            margin-top: auto;
            width: 100%;
        }

        .footer-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 2rem;
        }

        .footer-text {
            color: var(--text-secondary);
            font-size: 0.875rem;
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
            <div class="container-fluid px-0">
                <h1 class="page-title">General Reports</h1>

                <div class="reports-grid">
                    <!-- Lead Reports Card -->
                    <div class="report-card">
                        <div class="report-card-header">
                            <h2>Lead Reports</h2>
                        </div>
                        <ul class="report-list">
                            <li>
                                <a href="reports/lead-status.php" class="report-item" tabindex="0">
                                    <div class="report-item-content">
                                        <i class="fas fa-chart-bar report-item-icon"></i>
                                        <span>Status Wise Report</span>
                                    </div>
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                            <li>
                                <a href="reports/lead-status-time.php" class="report-item" tabindex="0">
                                    <div class="report-item-content">
                                        <i class="fas fa-clock report-item-icon"></i>
                                        <span>Status With Time Wise Report</span>
                                    </div>
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                            <li>
                                <a href="reports/lead-third-party.php" class="report-item" tabindex="0">
                                    <div class="report-item-content">
                                        <i class="fas fa-external-link-alt report-item-icon"></i>
                                        <span>Third Party Source Wise Report</span>
                                    </div>
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                            <li>
                                <a href="reports/lead-source.php" class="report-item" tabindex="0">
                                    <div class="report-item-content">
                                        <i class="fas fa-filter report-item-icon"></i>
                                        <span>Source Wise Report</span>
                                    </div>
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                            <li>
                                <a href="reports/lead-label.php" class="report-item" tabindex="0">
                                    <div class="report-item-content">
                                        <i class="fas fa-tags report-item-icon"></i>
                                        <span>Label Wise Report</span>
                                    </div>
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                            <li>
                                <a href="reports/lead-staff-source.php" class="report-item" tabindex="0">
                                    <div class="report-item-content">
                                        <i class="fas fa-users report-item-icon"></i>
                                        <span>Staff & Source Wise Lead Status Report</span>
                                    </div>
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                            <li>
                                <a href="reports/lead-staff-label.php" class="report-item" tabindex="0">
                                    <div class="report-item-content">
                                        <i class="fas fa-user-tag report-item-icon"></i>
                                        <span>Staff & Label Wise Lead Status Report</span>
                                    </div>
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                        </ul>
                    </div>

                    <!-- Task Reports Card -->
                    <div class="report-card">
                        <div class="report-card-header">
                            <h2>Task Reports</h2>
                        </div>
                        <ul class="report-list">
                            <li>
                                <a href="reports/task-status.php" class="report-item" tabindex="0">
                                    <div class="report-item-content">
                                        <i class="fas fa-tasks report-item-icon"></i>
                                        <span>Status Wise Report</span>
                                    </div>
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                            <li>
                                <a href="reports/task-label.php" class="report-item" tabindex="0">
                                    <div class="report-item-content">
                                        <i class="fas fa-tag report-item-icon"></i>
                                        <span>Label Wise Report</span>
                                    </div>
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                            <li>
                                <a href="reports/task-priority.php" class="report-item" tabindex="0">
                                    <div class="report-item-content">
                                        <i class="fas fa-flag report-item-icon"></i>
                                        <span>Priority Wise Report</span>
                                    </div>
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                            <li>
                                <a href="reports/task-delay.php" class="report-item" tabindex="0">
                                    <div class="report-item-content">
                                        <i class="fas fa-hourglass-half report-item-icon"></i>
                                        <span>Delay Task Report</span>
                                    </div>
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <footer class="dashboard-footer">
                <div class="footer-content">
                    <div class="footer-text">
                        © 2024 Lead Management System. All rights reserved.
                    </div>
                </div>
            </footer>
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

        // Mobile sidebar toggle
        document.addEventListener('DOMContentLoaded', function() {
            const sidebarMenu = document.getElementById('sidebarMenu');
            const toggleSidebarBtn = document.querySelector('.navbar-toggler');

            if (toggleSidebarBtn) {
                toggleSidebarBtn.addEventListener('click', () => {
                    sidebarMenu.classList.toggle('show');
                    
                    // Add overlay if not present
                    let overlay = document.getElementById('sidebarOverlay');
                    if (!overlay) {
                        overlay = document.createElement('div');
                        overlay.id = 'sidebarOverlay';
                        overlay.className = 'sidebar-overlay';
                        document.body.appendChild(overlay);
                    }
                    overlay.classList.toggle('show');
                });
            }

            // Close sidebar on overlay click
            document.addEventListener('click', (e) => {
                if (e.target.classList.contains('sidebar-overlay')) {
                    sidebarMenu.classList.remove('show');
                    e.target.classList.remove('show');
                }
            });

            // Close sidebar on window resize (if in mobile view)
            window.addEventListener('resize', () => {
                if (window.innerWidth > 991.98) {
                    sidebarMenu.classList.remove('show');
                    const overlay = document.getElementById('sidebarOverlay');
                    if (overlay) {
                        overlay.classList.remove('show');
                    }
                }
            });
        });
    </script>
</body>
</html> 