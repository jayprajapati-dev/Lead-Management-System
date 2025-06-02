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
$source_id = isset($_GET['source_id']) ? (int)$_GET['source_id'] : 0;

// Fetch third-party source data
try {
    // Get list of third-party sources
    $sources_sql = "SELECT id, name FROM lead_sources WHERE is_third_party = 1 ORDER BY name";
    $sources_result = $conn->query($sources_sql);
    $sources = $sources_result->fetch_all(MYSQLI_ASSOC);

    // Build the main query
    $sql = "SELECT 
                ls.name as source_name,
                COUNT(l.id) as total_leads,
                COUNT(CASE WHEN l.status_id IN (SELECT id FROM lead_status WHERE is_converted = 1) THEN 1 END) as converted_leads,
                COUNT(CASE WHEN l.created_at BETWEEN ? AND ? THEN 1 END) as period_leads,
                COUNT(CASE WHEN l.created_at BETWEEN ? AND ? AND l.status_id IN (SELECT id FROM lead_status WHERE is_converted = 1) THEN 1 END) as period_converted
            FROM lead_sources ls
            LEFT JOIN leads l ON ls.id = l.source_id
            WHERE ls.is_third_party = 1
            " . ($source_id > 0 ? "AND ls.id = ?" : "") . "
            " . ($staff_id > 0 ? "AND l.assigned_to = ?" : "") . "
            GROUP BY ls.id, ls.name
            ORDER BY total_leads DESC";

    $params = [$start_date . ' 00:00:00', $end_date . ' 23:59:59', $start_date . ' 00:00:00', $end_date . ' 23:59:59'];
    if ($source_id > 0) $params[] = $source_id;
    if ($staff_id > 0) $params[] = $staff_id;

    $stmt = executeQuery($sql, $params);
    $result = $stmt->get_result();
    $source_data = $result->fetch_all(MYSQLI_ASSOC);

    // Calculate totals
    $total_all_leads = 0;
    $total_converted = 0;
    $total_period_leads = 0;
    $total_period_converted = 0;
    foreach ($source_data as $source) {
        $total_all_leads += $source['total_leads'];
        $total_converted += $source['converted_leads'];
        $total_period_leads += $source['period_leads'];
        $total_period_converted += $source['period_converted'];
    }

    // Fetch staff list for filter
    $staff_sql = "SELECT id, CONCAT(first_name, ' ', last_name) as name FROM users WHERE role != 'admin' ORDER BY first_name";
    $staff_result = $conn->query($staff_sql);
    $staff_list = $staff_result->fetch_all(MYSQLI_ASSOC);

} catch (Exception $e) {
    error_log("Error fetching third-party source data: " . $e->getMessage());
    $error_message = "An error occurred while fetching the report data.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Third Party Source Report</title>
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
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
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

        .source-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        .source-table th {
            background: var(--primary-light);
            padding: 1rem;
            font-weight: 600;
            text-align: left;
            border-bottom: 2px solid var(--primary-color);
        }

        .source-table td {
            padding: 1rem;
            border-bottom: 1px solid #E2E8F0;
        }

        .source-table tr:last-child td {
            border-bottom: none;
        }

        .conversion-rate {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .conversion-rate.high {
            background-color: rgba(16, 185, 129, 0.1);
            color: var(--success-color);
        }

        .conversion-rate.medium {
            background-color: rgba(245, 158, 11, 0.1);
            color: var(--warning-color);
        }

        .conversion-rate.low {
            background-color: rgba(239, 68, 68, 0.1);
            color: #EF4444;
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

            .source-table {
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
                            <i class="fas fa-external-link-alt me-2"></i>
                            Third Party Source Report
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
                                <label class="filter-label">Source</label>
                                <select class="form-select" name="source_id">
                                    <option value="0">All Sources</option>
                                    <?php foreach ($sources as $source): ?>
                                        <option value="<?php echo $source['id']; ?>" 
                                                <?php echo $source_id == $source['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($source['name']); ?>
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
                            <div class="stat-label">Total Leads</div>
                            <div class="stat-value"><?php echo number_format($total_all_leads); ?></div>
                            <div class="stat-subtext">
                                <?php echo number_format($total_period_leads); ?> in selected period
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-label">Converted Leads</div>
                            <div class="stat-value"><?php echo number_format($total_converted); ?></div>
                            <div class="stat-subtext">
                                <?php echo number_format($total_period_converted); ?> in selected period
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-label">Conversion Rate</div>
                            <div class="stat-value">
                                <?php 
                                $conversion_rate = $total_all_leads > 0 ? 
                                    round(($total_converted / $total_all_leads) * 100, 1) : 0;
                                echo $conversion_rate . '%';
                                ?>
                            </div>
                            <div class="stat-subtext">
                                <?php 
                                $period_rate = $total_period_leads > 0 ? 
                                    round(($total_period_converted / $total_period_leads) * 100, 1) : 0;
                                echo $period_rate . '% in selected period';
                                ?>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Source Performance Table -->
                        <div class="col-12 mb-4">
                            <div class="report-card">
                                <h2 class="h5 mb-4">Source Performance</h2>
                                <div class="table-responsive">
                                    <table class="source-table">
                                        <thead>
                                            <tr>
                                                <th>Source</th>
                                                <th>Total Leads</th>
                                                <th>Period Leads</th>
                                                <th>Converted</th>
                                                <th>Conversion Rate</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($source_data as $source): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($source['source_name']); ?></td>
                                                    <td><?php echo number_format($source['total_leads']); ?></td>
                                                    <td><?php echo number_format($source['period_leads']); ?></td>
                                                    <td><?php echo number_format($source['converted_leads']); ?></td>
                                                    <td>
                                                        <?php
                                                        $rate = $source['total_leads'] > 0 ? 
                                                            round(($source['converted_leads'] / $source['total_leads']) * 100, 1) : 0;
                                                        $rate_class = $rate >= 50 ? 'high' : ($rate >= 25 ? 'medium' : 'low');
                                                        ?>
                                                        <div class="conversion-rate <?php echo $rate_class; ?>">
                                                            <?php echo $rate; ?>%
                                                        </div>
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
                                <h2 class="h5 mb-3">Lead Distribution</h2>
                                <div class="chart-container">
                                    <canvas id="leadDistributionChart"></canvas>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 mb-4">
                            <div class="report-card">
                                <h2 class="h5 mb-3">Conversion Performance</h2>
                                <div class="chart-container">
                                    <canvas id="conversionChart"></canvas>
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
        // Chart colors
        const colors = [
            '#4F46E5', '#10B981', '#F59E0B', '#EF4444',
            '#8B5CF6', '#EC4899', '#6366F1', '#14B8A6'
        ];

        // Prepare data for charts
        const sourceLabels = <?php echo json_encode(array_column($source_data, 'source_name')); ?>;
        const totalLeads = <?php echo json_encode(array_column($source_data, 'total_leads')); ?>;
        const convertedLeads = <?php echo json_encode(array_column($source_data, 'converted_leads')); ?>;
        const periodLeads = <?php echo json_encode(array_column($source_data, 'period_leads')); ?>;
        const periodConverted = <?php echo json_encode(array_column($source_data, 'period_converted')); ?>;

        // Lead Distribution Chart
        new Chart(document.getElementById('leadDistributionChart'), {
            type: 'doughnut',
            data: {
                labels: sourceLabels,
                datasets: [{
                    data: totalLeads,
                    backgroundColor: colors
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

        // Conversion Performance Chart
        new Chart(document.getElementById('conversionChart'), {
            type: 'bar',
            data: {
                labels: sourceLabels,
                datasets: [{
                    label: 'Total Leads',
                    data: totalLeads,
                    backgroundColor: '#4F46E5'
                }, {
                    label: 'Converted',
                    data: convertedLeads,
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