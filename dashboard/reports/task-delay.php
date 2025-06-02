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
$priority_id = isset($_GET['priority_id']) ? (int)$_GET['priority_id'] : 0;

// Fetch task delay data
try {
    // Get list of priorities
    $priorities_sql = "SELECT id, name, color FROM task_priorities ORDER BY name";
    $priorities_result = $conn->query($priorities_sql);
    $priorities = $priorities_result->fetch_all(MYSQLI_ASSOC);

    // Get list of staff members
    $staff_sql = "SELECT id, CONCAT(first_name, ' ', last_name) as name FROM users WHERE role != 'admin' ORDER BY first_name";
    $staff_result = $conn->query($staff_sql);
    $staff_list = $staff_result->fetch_all(MYSQLI_ASSOC);

    // Build the main query for overdue tasks
    $sql = "SELECT 
                u.id as staff_id,
                CONCAT(u.first_name, ' ', u.last_name) as staff_name,
                tp.name as priority_name,
                tp.color as priority_color,
                COUNT(DISTINCT t.id) as total_tasks,
                COUNT(DISTINCT CASE WHEN t.status_id IN (SELECT id FROM task_status WHERE is_completed = 1) THEN t.id END) as completed_tasks,
                COUNT(DISTINCT CASE WHEN t.due_date < CURRENT_DATE AND t.status_id NOT IN (SELECT id FROM task_status WHERE is_completed = 1) THEN t.id END) as overdue_tasks,
                AVG(CASE WHEN t.status_id IN (SELECT id FROM task_status WHERE is_completed = 1) 
                    THEN TIMESTAMPDIFF(DAY, t.due_date, t.completed_at) END) as avg_delay_days,
                COUNT(DISTINCT CASE WHEN t.status_id IN (SELECT id FROM task_status WHERE is_completed = 1) 
                    AND t.completed_at > t.due_date THEN t.id END) as delayed_completed_tasks,
                COUNT(DISTINCT CASE WHEN t.created_at BETWEEN ? AND ? THEN t.id END) as period_tasks,
                COUNT(DISTINCT CASE WHEN t.created_at BETWEEN ? AND ? 
                    AND t.status_id IN (SELECT id FROM task_status WHERE is_completed = 1) 
                    AND t.completed_at > t.due_date THEN t.id END) as period_delayed_tasks
            FROM users u
            CROSS JOIN task_priorities tp
            LEFT JOIN tasks t ON u.id = t.assigned_to AND tp.id = t.priority_id
            WHERE u.role != 'admin'
            " . ($staff_id > 0 ? "AND u.id = ?" : "") . "
            " . ($priority_id > 0 ? "AND tp.id = ?" : "") . "
            GROUP BY u.id, u.first_name, u.last_name, tp.id, tp.name, tp.color
            ORDER BY staff_name, priority_name";

    $params = [$start_date . ' 00:00:00', $end_date . ' 23:59:59', $start_date . ' 00:00:00', $end_date . ' 23:59:59'];
    if ($staff_id > 0) $params[] = $staff_id;
    if ($priority_id > 0) $params[] = $priority_id;

    $stmt = executeQuery($sql, $params);
    $result = $stmt->get_result();
    $task_delay_data = $result->fetch_all(MYSQLI_ASSOC);

    // Calculate totals
    $total_all_tasks = 0;
    $total_completed = 0;
    $total_overdue = 0;
    $total_delayed_completed = 0;
    $total_period_tasks = 0;
    $total_period_delayed = 0;
    $total_delay_days = 0;
    $delay_count = 0;

    foreach ($task_delay_data as $data) {
        $total_all_tasks += $data['total_tasks'];
        $total_completed += $data['completed_tasks'];
        $total_overdue += $data['overdue_tasks'];
        $total_delayed_completed += $data['delayed_completed_tasks'];
        $total_period_tasks += $data['period_tasks'];
        $total_period_delayed += $data['period_delayed_tasks'];
        if ($data['avg_delay_days'] !== null) {
            $total_delay_days += ($data['avg_delay_days'] * $data['delayed_completed_tasks']);
            $delay_count += $data['delayed_completed_tasks'];
        }
    }

    $avg_delay_days = $delay_count > 0 ? round($total_delay_days / $delay_count, 1) : 0;
    $delay_rate = $total_completed > 0 ? round(($total_delayed_completed / $total_completed) * 100, 1) : 0;

} catch (Exception $e) {
    error_log("Error fetching task delay data: " . $e->getMessage());
    $error_message = "An error occurred while fetching the report data.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Delay Report</title>
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

        .performance-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        .performance-table th {
            background: var(--primary-light);
            padding: 1rem;
            font-weight: 600;
            text-align: left;
            border-bottom: 2px solid var(--primary-color);
        }

        .performance-table td {
            padding: 1rem;
            border-bottom: 1px solid #E2E8F0;
        }

        .performance-table tr:last-child td {
            border-bottom: none;
        }

        .staff-name {
            font-weight: 600;
            color: var(--text-primary);
        }

        .priority-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .delay-rate {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .delay-rate.low {
            background-color: rgba(16, 185, 129, 0.1);
            color: var(--success-color);
        }

        .delay-rate.medium {
            background-color: rgba(245, 158, 11, 0.1);
            color: var(--warning-color);
        }

        .delay-rate.high {
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

            .performance-table {
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
                            <i class="fas fa-clock me-2"></i>
                            Task Delay Report
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
                            <div class="filter-group">
                                <label class="filter-label">Priority</label>
                                <select class="form-select" name="priority_id">
                                    <option value="0">All Priorities</option>
                                    <?php foreach ($priorities as $priority): ?>
                                        <option value="<?php echo $priority['id']; ?>" 
                                                <?php echo $priority_id == $priority['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($priority['name']); ?>
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
                            <div class="stat-label">Overdue Tasks</div>
                            <div class="stat-value"><?php echo number_format($total_overdue); ?></div>
                            <div class="stat-subtext">Currently past due date</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-label">Delayed Tasks</div>
                            <div class="stat-value"><?php echo number_format($total_delayed_completed); ?></div>
                            <div class="stat-subtext">
                                <?php echo number_format($total_period_delayed); ?> in selected period
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-label">Delay Rate</div>
                            <div class="stat-value"><?php echo $delay_rate; ?>%</div>
                            <div class="stat-subtext">Of completed tasks</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-label">Avg. Delay Time</div>
                            <div class="stat-value"><?php echo $avg_delay_days; ?> days</div>
                            <div class="stat-subtext">Past due date</div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Performance Table -->
                        <div class="col-12 mb-4">
                            <div class="report-card">
                                <h2 class="h5 mb-4">Staff Delay Performance</h2>
                                <div class="table-responsive">
                                    <table class="performance-table">
                                        <thead>
                                            <tr>
                                                <th>Staff Member / Priority</th>
                                                <th>Total Tasks</th>
                                                <th>Completed</th>
                                                <th>Overdue</th>
                                                <th>Delayed</th>
                                                <th>Delay Rate</th>
                                                <th>Avg. Delay Days</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            $current_staff = '';
                                            foreach ($task_delay_data as $data): 
                                                $is_new_staff = $current_staff !== $data['staff_name'];
                                                if ($is_new_staff) {
                                                    $current_staff = $data['staff_name'];
                                                }
                                            ?>
                                                <tr>
                                                    <td>
                                                        <?php if ($is_new_staff): ?>
                                                            <div class="staff-name"><?php echo htmlspecialchars($data['staff_name']); ?></div>
                                                        <?php endif; ?>
                                                        <div class="priority-badge" style="background-color: <?php echo htmlspecialchars($data['priority_color']); ?>20; color: <?php echo htmlspecialchars($data['priority_color']); ?>;">
                                                            <?php echo htmlspecialchars($data['priority_name']); ?>
                                                        </div>
                                                    </td>
                                                    <td><?php echo number_format($data['total_tasks']); ?></td>
                                                    <td><?php echo number_format($data['completed_tasks']); ?></td>
                                                    <td><?php echo number_format($data['overdue_tasks']); ?></td>
                                                    <td><?php echo number_format($data['delayed_completed_tasks']); ?></td>
                                                    <td>
                                                        <?php
                                                        $rate = $data['completed_tasks'] > 0 ? 
                                                            round(($data['delayed_completed_tasks'] / $data['completed_tasks']) * 100, 1) : 0;
                                                        $rate_class = $rate <= 25 ? 'low' : ($rate <= 50 ? 'medium' : 'high');
                                                        ?>
                                                        <div class="delay-rate <?php echo $rate_class; ?>">
                                                            <?php echo $rate; ?>%
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <?php
                                                        echo $data['avg_delay_days'] !== null ? 
                                                            round($data['avg_delay_days'], 1) . ' days' : 'N/A';
                                                        ?>
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
                                <h2 class="h5 mb-3">Overdue Tasks by Staff</h2>
                                <div class="chart-container">
                                    <canvas id="overdueChart"></canvas>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 mb-4">
                            <div class="report-card">
                                <h2 class="h5 mb-3">Delay Performance by Priority</h2>
                                <div class="chart-container">
                                    <canvas id="delayChart"></canvas>
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
        const taskData = <?php echo json_encode($task_delay_data); ?>;
        
        // Group data by staff member and priority
        const staffData = {};
        const priorityData = {};
        
        taskData.forEach(data => {
            // Staff data
            if (!staffData[data.staff_name]) {
                staffData[data.staff_name] = {
                    overdue: 0,
                    total: 0
                };
            }
            staffData[data.staff_name].overdue += data.overdue_tasks;
            staffData[data.staff_name].total += data.total_tasks;
            
            // Priority data
            if (!priorityData[data.priority_name]) {
                priorityData[data.priority_name] = {
                    delayed: 0,
                    completed: 0,
                    color: data.priority_color
                };
            }
            priorityData[data.priority_name].delayed += data.delayed_completed_tasks;
            priorityData[data.priority_name].completed += data.completed_tasks;
        });

        // Overdue Tasks Chart
        new Chart(document.getElementById('overdueChart'), {
            type: 'bar',
            data: {
                labels: Object.keys(staffData),
                datasets: [{
                    label: 'Overdue Tasks',
                    data: Object.values(staffData).map(d => d.overdue),
                    backgroundColor: '#EF4444'
                }, {
                    label: 'Total Tasks',
                    data: Object.values(staffData).map(d => d.total),
                    backgroundColor: '#4F46E5'
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

        // Delay Performance Chart
        new Chart(document.getElementById('delayChart'), {
            type: 'bar',
            data: {
                labels: Object.keys(priorityData),
                datasets: [{
                    label: 'Delayed Tasks',
                    data: Object.values(priorityData).map(d => d.delayed),
                    backgroundColor: Object.values(priorityData).map(d => d.color)
                }, {
                    label: 'Completed Tasks',
                    data: Object.values(priorityData).map(d => d.completed),
                    backgroundColor: Object.values(priorityData).map(d => d.color + '40')
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