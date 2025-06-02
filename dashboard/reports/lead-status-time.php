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
$interval = isset($_GET['interval']) ? $_GET['interval'] : 'daily';

// Fetch lead status time data
try {
    // Define the date format based on interval
    $date_format = match($interval) {
        'hourly' => '%Y-%m-%d %H:00:00',
        'daily' => '%Y-%m-%d',
        'weekly' => '%Y-%u',
        'monthly' => '%Y-%m',
        default => '%Y-%m-%d'
    };

    $sql = "SELECT 
                DATE_FORMAT(l.created_at, ?) as time_period,
                ls.name as status_name,
                COUNT(l.id) as lead_count
            FROM lead_status ls
            LEFT JOIN leads l ON ls.id = l.status_id
            WHERE l.created_at BETWEEN ? AND ?
            " . ($staff_id > 0 ? "AND l.assigned_to = ?" : "") . "
            GROUP BY time_period, ls.id, ls.name
            ORDER BY time_period ASC, ls.display_order";

    $params = [$date_format, $start_date . ' 00:00:00', $end_date . ' 23:59:59'];
    if ($staff_id > 0) {
        $params[] = $staff_id;
    }

    $stmt = executeQuery($sql, $params);
    $result = $stmt->get_result();
    $lead_time_data = $result->fetch_all(MYSQLI_ASSOC);

    // Process data for chart
    $time_periods = array_unique(array_column($lead_time_data, 'time_period'));
    $status_names = array_unique(array_column($lead_time_data, 'status_name'));
    
    // Create data structure for chart
    $chart_data = [];
    foreach ($status_names as $status) {
        $data_points = [];
        foreach ($time_periods as $period) {
            $count = 0;
            foreach ($lead_time_data as $row) {
                if ($row['status_name'] === $status && $row['time_period'] === $period) {
                    $count = $row['lead_count'];
                    break;
                }
            }
            $data_points[] = $count;
        }
        $chart_data[$status] = $data_points;
    }

    // Fetch staff list for filter
    $staff_sql = "SELECT id, CONCAT(first_name, ' ', last_name) as name FROM users WHERE role != 'admin' ORDER BY first_name";
    $staff_result = $conn->query($staff_sql);
    $staff_list = $staff_result->fetch_all(MYSQLI_ASSOC);

} catch (Exception $e) {
    error_log("Error fetching lead status time data: " . $e->getMessage());
    $error_message = "An error occurred while fetching the report data.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lead Status Time Report</title>
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

        .chart-container {
            position: relative;
            height: 400px;
            margin-top: 1rem;
        }

        .legend-container {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin-top: 1rem;
            padding: 1rem;
            background: var(--primary-light);
            border-radius: 8px;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.25rem 0.75rem;
            background: white;
            border-radius: 4px;
            font-size: 0.875rem;
        }

        .legend-color {
            width: 12px;
            height: 12px;
            border-radius: 2px;
        }

        @media (max-width: 768px) {
            .filter-group {
                min-width: 100%;
            }

            .chart-container {
                height: 300px;
            }

            .legend-item {
                font-size: 0.75rem;
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
                            Lead Status Time Report
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
                                <label class="filter-label">Time Interval</label>
                                <select class="form-select" name="interval">
                                    <option value="hourly" <?php echo $interval === 'hourly' ? 'selected' : ''; ?>>Hourly</option>
                                    <option value="daily" <?php echo $interval === 'daily' ? 'selected' : ''; ?>>Daily</option>
                                    <option value="weekly" <?php echo $interval === 'weekly' ? 'selected' : ''; ?>>Weekly</option>
                                    <option value="monthly" <?php echo $interval === 'monthly' ? 'selected' : ''; ?>>Monthly</option>
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

                    <div class="row">
                        <!-- Time Series Chart -->
                        <div class="col-12 mb-4">
                            <div class="report-card">
                                <h2 class="h5 mb-3">Lead Status Over Time</h2>
                                <div class="chart-container">
                                    <canvas id="timeSeriesChart"></canvas>
                                </div>
                                <div class="legend-container" id="customLegend"></div>
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
        // Chart colors
        const colors = [
            '#4F46E5', '#10B981', '#F59E0B', '#EF4444',
            '#8B5CF6', '#EC4899', '#6366F1', '#14B8A6'
        ];

        // Prepare data for chart
        const timeLabels = <?php echo json_encode(array_values($time_periods)); ?>;
        const datasets = [];
        
        <?php
        $index = 0;
        foreach ($chart_data as $status => $data) {
            echo "datasets.push({
                label: " . json_encode($status) . ",
                data: " . json_encode($data) . ",
                borderColor: colors[" . ($index % count($colors)) . "],
                backgroundColor: colors[" . ($index % count($colors)) . "],
                tension: 0.4,
                fill: false
            });\n";
            $index++;
        }
        ?>

        // Create time series chart
        const ctx = document.getElementById('timeSeriesChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: timeLabels,
                datasets: datasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false
                },
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
                        display: false
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false
                    }
                }
            }
        });

        // Create custom legend
        const legendContainer = document.getElementById('customLegend');
        datasets.forEach((dataset, index) => {
            const item = document.createElement('div');
            item.className = 'legend-item';
            
            const color = document.createElement('div');
            color.className = 'legend-color';
            color.style.backgroundColor = dataset.borderColor;
            
            const label = document.createElement('span');
            label.textContent = dataset.label;
            
            item.appendChild(color);
            item.appendChild(label);
            legendContainer.appendChild(item);
        });
    </script>
    <?php endif; ?>
</body>
</html> 