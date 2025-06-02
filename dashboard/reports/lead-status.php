<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include configuration
require_once '../../includes/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . SITE_URL . '/login.php');
    exit();
}

// Get filter parameters
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-30 days'));
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
$staff_id = isset($_GET['staff_id']) ? (int)$_GET['staff_id'] : 0;

// Fetch lead status data
try {
    $sql = "SELECT 
                ls.name as status_name,
                COUNT(l.id) as total_leads,
                COUNT(CASE WHEN l.created_at BETWEEN ? AND ? THEN 1 END) as period_leads
            FROM lead_status ls
            LEFT JOIN leads l ON ls.id = l.status_id
            " . ($staff_id > 0 ? "AND l.assigned_to = ?" : "") . "
            GROUP BY ls.id, ls.name
            ORDER BY ls.display_order";

    $params = [$start_date . ' 00:00:00', $end_date . ' 23:59:59'];
    if ($staff_id > 0) {
        $params[] = $staff_id;
    }

    $stmt = executeQuery($sql, $params);
    $result = $stmt->get_result();
    $lead_status_data = $result->fetch_all(MYSQLI_ASSOC);

    // Calculate totals
    $total_all_leads = 0;
    $total_period_leads = 0;
    foreach ($lead_status_data as $status) {
        $total_all_leads += $status['total_leads'];
        $total_period_leads += $status['period_leads'];
    }

    // Fetch staff list for filter
    $staff_sql = "SELECT id, CONCAT(first_name, ' ', last_name) as name FROM users WHERE role != 'admin' ORDER BY first_name";
    $staff_result = $conn->query($staff_sql);
    $staff_list = $staff_result->fetch_all(MYSQLI_ASSOC);

} catch (Exception $e) {
    error_log("Error fetching lead status data: " . $e->getMessage());
    $error_message = "An error occurred while fetching the report data.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lead Status Report</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary-color: #57439F;
            --primary-light: #E8E5F7;
            --text-primary: #1E293B;
            --text-secondary: #64748B;
            --card-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #F8FAFC;
            color: var(--text-primary);
        }

        .report-header {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: var(--card-shadow);
            margin-bottom: 2rem;
        }

        .report-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 1rem;
        }

        .filter-section {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            align-items: flex-end;
        }

        .filter-group {
            flex: 1;
            min-width: 200px;
        }

        .filter-label {
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--text-secondary);
            margin-bottom: 0.5rem;
        }

        .report-card {
            background: white;
            border-radius: 12px;
            box-shadow: var(--card-shadow);
            padding: 1.5rem;
            height: 100%;
        }

        .status-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .status-item {
            background: var(--primary-light);
            border-radius: 8px;
            padding: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .status-name {
            font-weight: 600;
            color: var(--primary-color);
        }

        .status-count {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--text-primary);
        }

        .chart-container {
            position: relative;
            height: 300px;
            margin-top: 1rem;
        }

        @media (max-width: 768px) {
            .filter-group {
                min-width: 100%;
            }

            .status-grid {
                grid-template-columns: 1fr;
            }

            .chart-container {
                height: 400px;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="dashboard-header">
        <?php include '../../includes/dashboard-header.php'; ?>
    </header>

    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar" id="sidebarMenu">
            <?php include '../../includes/sidebar.php'; ?>
        </div>

        <!-- Main Content Area -->
        <div class="main-content-area">
            <div class="container-fluid px-4">
                <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger" role="alert">
                        <?php echo htmlspecialchars($error_message); ?>
                    </div>
                <?php else: ?>
                    <!-- Report Header -->
                    <div class="report-header">
                        <h1 class="report-title">
                            <i class="fas fa-chart-bar me-2"></i>
                            Lead Status Report
                        </h1>
                        
                        <!-- Filter Section -->
                        <form class="filter-section" method="GET">
                            <div class="filter-group">
                                <label class="filter-label">Start Date</label>
                                <input type="date" class="form-control" name="start_date" 
                                       value="<?php echo htmlspecialchars($start_date); ?>">
                            </div>
                            <div class="filter-group">
                                <label class="filter-label">End Date</label>
                                <input type="date" class="form-control" name="end_date" 
                                       value="<?php echo htmlspecialchars($end_date); ?>">
                            </div>
                            <div class="filter-group">
                                <label class="filter-label">Staff Member</label>
                                <select class="form-select" name="staff_id">
                                    <option value="0">All Staff</option>
                                    <?php foreach ($staff_list as $staff): ?>
                                        <option value="<?php echo $staff['id']; ?>" 
                                                <?php echo $staff_id == $staff['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($staff['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="filter-group" style="flex: 0 0 auto;">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-filter me-2"></i>Apply Filter
                                </button>
                            </div>
                        </form>
                    </div>

                    <div class="row">
                        <!-- Status Overview -->
                        <div class="col-12 mb-4">
                            <div class="report-card">
                                <h2 class="h5 mb-4">Status Overview</h2>
                                <div class="status-grid">
                                    <?php foreach ($lead_status_data as $status): ?>
                                        <div class="status-item">
                                            <div>
                                                <div class="status-name"><?php echo htmlspecialchars($status['status_name']); ?></div>
                                                <small class="text-muted">
                                                    <?php echo $status['period_leads']; ?> in selected period
                                                </small>
                                            </div>
                                            <div class="status-count"><?php echo $status['total_leads']; ?></div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Charts -->
                        <div class="col-md-6 mb-4">
                            <div class="report-card">
                                <h2 class="h5 mb-3">Lead Distribution</h2>
                                <div class="chart-container">
                                    <canvas id="leadDistributionChart"></canvas>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 mb-4">
                            <div class="report-card">
                                <h2 class="h5 mb-3">Period Comparison</h2>
                                <div class="chart-container">
                                    <canvas id="periodComparisonChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>

    <?php if (!isset($error_message)): ?>
    <script>
        // Prepare data for charts
        const statusLabels = <?php echo json_encode(array_column($lead_status_data, 'status_name')); ?>;
        const totalLeads = <?php echo json_encode(array_column($lead_status_data, 'total_leads')); ?>;
        const periodLeads = <?php echo json_encode(array_column($lead_status_data, 'period_leads')); ?>;

        // Lead Distribution Chart
        new Chart(document.getElementById('leadDistributionChart'), {
            type: 'doughnut',
            data: {
                labels: statusLabels,
                datasets: [{
                    data: totalLeads,
                    backgroundColor: [
                        '#4F46E5', '#10B981', '#F59E0B', '#EF4444',
                        '#8B5CF6', '#EC4899', '#6366F1', '#14B8A6'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right'
                    }
                }
            }
        });

        // Period Comparison Chart
        new Chart(document.getElementById('periodComparisonChart'), {
            type: 'bar',
            data: {
                labels: statusLabels,
                datasets: [{
                    label: 'All Time',
                    data: totalLeads,
                    backgroundColor: '#4F46E5'
                }, {
                    label: 'Selected Period',
                    data: periodLeads,
                    backgroundColor: '#10B981'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                },
                plugins: {
                    legend: {
                        position: 'top'
                    }
                }
            }
        });
    </script>
    <?php endif; ?>
</body>
</html> 