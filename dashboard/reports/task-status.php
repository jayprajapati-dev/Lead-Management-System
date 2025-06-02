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
$status_id = isset($_GET['status_id']) ? (int)$_GET['status_id'] : 0;

// Fetch task status data
try {
    // Get list of task statuses
    $statuses_sql = "SELECT id, name, color FROM task_status ORDER BY display_order";
    $statuses_result = $conn->query($statuses_sql);
    $statuses = $statuses_result->fetch_all(MYSQLI_ASSOC);

    // Build the main query
    $sql = "SELECT 
                ts.name as status_name,
                ts.color as status_color,
                COUNT(t.id) as total_tasks,
                COUNT(CASE WHEN t.created_at BETWEEN ? AND ? THEN 1 END) as period_tasks,
                COUNT(CASE WHEN t.completed_at IS NOT NULL THEN 1 END) as completed_tasks,
                COUNT(CASE WHEN t.created_at BETWEEN ? AND ? AND t.completed_at IS NOT NULL THEN 1 END) as period_completed,
                AVG(CASE WHEN t.completed_at IS NOT NULL THEN TIMESTAMPDIFF(DAY, t.created_at, t.completed_at) END) as avg_completion_days,
                COUNT(CASE WHEN t.due_date < CURRENT_DATE AND t.completed_at IS NULL THEN 1 END) as overdue_tasks
            FROM task_status ts
            LEFT JOIN tasks t ON ts.id = t.status_id
            WHERE 1=1
            " . ($status_id > 0 ? "AND ts.id = ?" : "") . "
            " . ($staff_id > 0 ? "AND t.assigned_to = ?" : "") . "
            GROUP BY ts.id, ts.name, ts.color
            ORDER BY ts.display_order";

    $params = [$start_date . ' 00:00:00', $end_date . ' 23:59:59', $start_date . ' 00:00:00', $end_date . ' 23:59:59'];
    if ($status_id > 0) $params[] = $status_id;
    if ($staff_id > 0) $params[] = $staff_id;

    $stmt = executeQuery($sql, $params);
    $result = $stmt->get_result();
    $status_data = $result->fetch_all(MYSQLI_ASSOC);

    // Calculate totals
    $total_all_tasks = 0;
    $total_completed = 0;
    $total_period_tasks = 0;
    $total_period_completed = 0;
    $total_overdue = 0;
    $total_completion_days = 0;
    $completion_count = 0;

    foreach ($status_data as $status) {
        $total_all_tasks += $status['total_tasks'];
        $total_completed += $status['completed_tasks'];
        $total_period_tasks += $status['period_tasks'];
        $total_period_completed += $status['period_completed'];
        $total_overdue += $status['overdue_tasks'];
        if ($status['avg_completion_days'] !== null) {
            $total_completion_days += ($status['avg_completion_days'] * $status['completed_tasks']);
            $completion_count += $status['completed_tasks'];
        }
    }

    $avg_completion_days = $completion_count > 0 ? round($total_completion_days / $completion_count, 1) : 0;

    // Fetch staff list for filter
    $staff_sql = "SELECT id, CONCAT(first_name, ' ', last_name) as name FROM users WHERE role != 'admin' ORDER BY first_name";
    $staff_result = $conn->query($staff_sql);
    $staff_list = $staff_result->fetch_all(MYSQLI_ASSOC);

} catch (Exception $e) {
    error_log("Error fetching task status data: " . $e->getMessage());
    $error_message = "An error occurred while fetching the report data.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Status Report</title>
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
            --success-color: #10B981;
            --warning-color: #F59E0B;
            --danger-color: #EF4444;
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

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: var(--primary-light);
            border-radius: 8px;
            padding: 1.25rem;
        }

        .stat-label {
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--text-secondary);
            margin-bottom: 0.5rem;
        }

        .stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-primary);
        }

        .stat-subtext {
            font-size: 0.875rem;
            color: var(--text-secondary);
            margin-top: 0.25rem;
        }

        .status-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        .status-table th {
            background: var(--primary-light);
            padding: 1rem;
            font-weight: 600;
            text-align: left;
            border-bottom: 2px solid var(--primary-color);
        }

        .status-table td {
            padding: 1rem;
            border-bottom: 1px solid #E2E8F0;
        }

        .status-table tr:last-child td {
            border-bottom: none;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .completion-rate {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .completion-rate.high {
            background-color: rgba(16, 185, 129, 0.1);
            color: var(--success-color);
        }

        .completion-rate.medium {
            background-color: rgba(245, 158, 11, 0.1);
            color: var(--warning-color);
        }

        .completion-rate.low {
            background-color: rgba(239, 68, 68, 0.1);
            color: var(--danger-color);
        }

        .overdue-count {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.875rem;
            font-weight: 500;
            background-color: rgba(239, 68, 68, 0.1);
            color: var(--danger-color);
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

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .status-table {
                display: block;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
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
                            <i class="fas fa-tasks me-2"></i>
                            Task Status Report
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
                                <label class="filter-label">Status</label>
                                <select class="form-select" name="status_id">
                                    <option value="0">All Statuses</option>
                                    <?php foreach ($statuses as $status): ?>
                                        <option value="<?php echo $status['id']; ?>" 
                                                <?php echo $status_id == $status['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($status['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
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

                    <!-- Statistics Overview -->
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-label">Total Tasks</div>
                            <div class="stat-value"><?php echo number_format($total_all_tasks); ?></div>
                            <div class="stat-subtext">
                                <?php echo number_format($total_period_tasks); ?> in selected period
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-label">Completed Tasks</div>
                            <div class="stat-value"><?php echo number_format($total_completed); ?></div>
                            <div class="stat-subtext">
                                <?php echo number_format($total_period_completed); ?> in selected period
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-label">Completion Rate</div>
                            <div class="stat-value">
                                <?php 
                                $completion_rate = $total_all_tasks > 0 ? 
                                    round(($total_completed / $total_all_tasks) * 100, 1) : 0;
                                echo $completion_rate . '%';
                                ?>
                            </div>
                            <div class="stat-subtext">
                                <?php 
                                $period_rate = $total_period_tasks > 0 ? 
                                    round(($total_period_completed / $total_period_tasks) * 100, 1) : 0;
                                echo $period_rate . '% in selected period';
                                ?>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-label">Overdue Tasks</div>
                            <div class="stat-value"><?php echo number_format($total_overdue); ?></div>
                            <div class="stat-subtext">
                                <?php 
                                $overdue_rate = $total_all_tasks > 0 ? 
                                    round(($total_overdue / $total_all_tasks) * 100, 1) : 0;
                                echo $overdue_rate . '% of total tasks';
                                ?>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-label">Avg. Completion Time</div>
                            <div class="stat-value"><?php echo $avg_completion_days; ?> days</div>
                            <div class="stat-subtext">From task creation to completion</div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Status Performance Table -->
                        <div class="col-12 mb-4">
                            <div class="report-card">
                                <h2 class="h5 mb-4">Status Performance</h2>
                                <div class="table-responsive">
                                    <table class="status-table">
                                        <thead>
                                            <tr>
                                                <th>Status</th>
                                                <th>Total Tasks</th>
                                                <th>Period Tasks</th>
                                                <th>Completed</th>
                                                <th>Completion Rate</th>
                                                <th>Avg. Days to Complete</th>
                                                <th>Overdue</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($status_data as $status): ?>
                                                <tr>
                                                    <td>
                                                        <div class="status-badge" style="background-color: <?php echo htmlspecialchars($status['status_color']); ?>20; color: <?php echo htmlspecialchars($status['status_color']); ?>;">
                                                            <?php echo htmlspecialchars($status['status_name']); ?>
                                                        </div>
                                                    </td>
                                                    <td><?php echo number_format($status['total_tasks']); ?></td>
                                                    <td><?php echo number_format($status['period_tasks']); ?></td>
                                                    <td><?php echo number_format($status['completed_tasks']); ?></td>
                                                    <td>
                                                        <?php
                                                        $rate = $status['total_tasks'] > 0 ? 
                                                            round(($status['completed_tasks'] / $status['total_tasks']) * 100, 1) : 0;
                                                        $rate_class = $rate >= 75 ? 'high' : ($rate >= 50 ? 'medium' : 'low');
                                                        ?>
                                                        <div class="completion-rate <?php echo $rate_class; ?>">
                                                            <?php echo $rate; ?>%
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <?php
                                                        echo $status['avg_completion_days'] !== null ? 
                                                            round($status['avg_completion_days'], 1) . ' days' : 'N/A';
                                                        ?>
                                                    </td>
                                                    <td>
                                                        <?php if ($status['overdue_tasks'] > 0): ?>
                                                            <div class="overdue-count">
                                                                <?php echo number_format($status['overdue_tasks']); ?>
                                                            </div>
                                                        <?php else: ?>
                                                            -
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Charts -->
                        <div class="col-md-6 mb-4">
                            <div class="report-card">
                                <h2 class="h5 mb-3">Task Distribution</h2>
                                <div class="chart-container">
                                    <canvas id="taskDistributionChart"></canvas>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 mb-4">
                            <div class="report-card">
                                <h2 class="h5 mb-3">Completion Performance</h2>
                                <div class="chart-container">
                                    <canvas id="completionChart"></canvas>
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
        const statusNames = <?php echo json_encode(array_column($status_data, 'status_name')); ?>;
        const statusColors = <?php echo json_encode(array_column($status_data, 'status_color')); ?>;
        const totalTasks = <?php echo json_encode(array_column($status_data, 'total_tasks')); ?>;
        const completedTasks = <?php echo json_encode(array_column($status_data, 'completed_tasks')); ?>;
        const periodTasks = <?php echo json_encode(array_column($status_data, 'period_tasks')); ?>;
        const periodCompleted = <?php echo json_encode(array_column($status_data, 'period_completed')); ?>;

        // Task Distribution Chart
        new Chart(document.getElementById('taskDistributionChart'), {
            type: 'doughnut',
            data: {
                labels: statusNames,
                datasets: [{
                    data: totalTasks,
                    backgroundColor: statusColors.map(color => color + '80'),
                    borderColor: statusColors
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            boxWidth: 12,
                            font: {
                                size: 11
                            }
                        }
                    }
                }
            }
        });

        // Completion Performance Chart
        new Chart(document.getElementById('completionChart'), {
            type: 'bar',
            data: {
                labels: statusNames,
                datasets: [{
                    label: 'Total Tasks',
                    data: totalTasks,
                    backgroundColor: '#4F46E5'
                }, {
                    label: 'Completed',
                    data: completedTasks,
                    backgroundColor: '#10B981'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        grid: {
                            display: false
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: '#E2E8F0'
                        }
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