<?php
require_once '../includes/header.php';

// Fetch dashboard statistics
$stats = [
    'total_leads' => 0,
    'new_leads' => 0,
    'converted_leads' => 0,
    'active_leads' => 0
];

try {
    // Total leads
    $stmt = executeQuery("SELECT COUNT(*) as count FROM leads");
    $result = $stmt->get_result();
    $stats['total_leads'] = $result->fetch_assoc()['count'];
    
    // New leads (last 7 days)
    $stmt = executeQuery("SELECT COUNT(*) as count FROM leads WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
    $result = $stmt->get_result();
    $stats['new_leads'] = $result->fetch_assoc()['count'];
    
    // Converted leads
    $stmt = executeQuery("SELECT COUNT(*) as count FROM leads WHERE status = 'converted'");
    $result = $stmt->get_result();
    $stats['converted_leads'] = $result->fetch_assoc()['count'];
    
    // Active leads
    $stmt = executeQuery("SELECT COUNT(*) as count FROM leads WHERE status = 'active'");
    $result = $stmt->get_result();
    $stats['active_leads'] = $result->fetch_assoc()['count'];
    
    // Recent leads
    $stmt = executeQuery("SELECT * FROM leads ORDER BY created_at DESC LIMIT 5");
    $recent_leads = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
} catch (Exception $e) {
    // Handle error silently for demo
    $recent_leads = [];
}
?>

<!-- Dashboard Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Dashboard Overview</h1>
    <div class="btn-group">
        <button type="button" class="btn btn-outline-primary" data-bs-toggle="tooltip" title="Refresh Data">
            <i class="fas fa-sync-alt"></i>
        </button>
        <button type="button" class="btn btn-outline-primary" data-bs-toggle="tooltip" title="Export Report">
            <i class="fas fa-download"></i>
        </button>
    </div>
</div>

<!-- Stats Cards -->
<div class="row g-4 mb-4">
    <div class="col-md-6 col-xl-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-primary bg-opacity-10 p-3 rounded">
                            <i class="fas fa-users text-primary fa-2x"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="card-subtitle text-muted mb-1">Total Leads</h6>
                        <h2 class="card-title mb-0"><?php echo number_format($stats['total_leads']); ?></h2>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 col-xl-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-success bg-opacity-10 p-3 rounded">
                            <i class="fas fa-user-plus text-success fa-2x"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="card-subtitle text-muted mb-1">New Leads</h6>
                        <h2 class="card-title mb-0"><?php echo number_format($stats['new_leads']); ?></h2>
                        <small class="text-success">Last 7 days</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 col-xl-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-info bg-opacity-10 p-3 rounded">
                            <i class="fas fa-check-circle text-info fa-2x"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="card-subtitle text-muted mb-1">Converted</h6>
                        <h2 class="card-title mb-0"><?php echo number_format($stats['converted_leads']); ?></h2>
                        <small class="text-info">Total conversions</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 col-xl-3">
        <div class="card h-100">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-warning bg-opacity-10 p-3 rounded">
                            <i class="fas fa-clock text-warning fa-2x"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="card-subtitle text-muted mb-1">Active Leads</h6>
                        <h2 class="card-title mb-0"><?php echo number_format($stats['active_leads']); ?></h2>
                        <small class="text-warning">In pipeline</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row g-4 mb-4">
    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Lead Trends</h5>
                <div class="dropdown">
                    <button class="btn btn-link dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        This Month
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="#">This Week</a></li>
                        <li><a class="dropdown-item" href="#">This Month</a></li>
                        <li><a class="dropdown-item" href="#">This Year</a></li>
                    </ul>
                </div>
            </div>
            <div class="card-body">
                <canvas id="leadsChart" height="300"></canvas>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">Lead Sources</h5>
            </div>
            <div class="card-body">
                <canvas id="sourcesChart" height="300"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Recent Leads Table -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">Recent Leads</h5>
        <a href="/dashboard/leads.php" class="btn btn-primary btn-sm">View All</a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Company</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_leads as $lead): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($lead['name']); ?></td>
                        <td><?php echo htmlspecialchars($lead['company']); ?></td>
                        <td><?php echo htmlspecialchars($lead['email']); ?></td>
                        <td>
                            <span class="badge bg-<?php 
                                echo match($lead['status']) {
                                    'active' => 'primary',
                                    'converted' => 'success',
                                    'lost' => 'danger',
                                    default => 'secondary'
                                };
                            ?>">
                                <?php echo ucfirst($lead['status']); ?>
                            </span>
                        </td>
                        <td><?php echo date('M d, Y', strtotime($lead['created_at'])); ?></td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="/dashboard/lead-details.php?id=<?php echo $lead['id']; ?>" 
                                   class="btn btn-outline-primary" 
                                   data-bs-toggle="tooltip" 
                                   title="View Details">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="/dashboard/lead-details.php?id=<?php echo $lead['id']; ?>&edit=1" 
                                   class="btn btn-outline-secondary" 
                                   data-bs-toggle="tooltip" 
                                   title="Edit Lead">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    
                    <?php if (empty($recent_leads)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-4">
                            <div class="text-muted">
                                <i class="fas fa-inbox fa-2x mb-3"></i>
                                <p>No leads found</p>
                            </div>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
// Initialize charts when the page loads
document.addEventListener('DOMContentLoaded', function() {
    // Lead Trends Chart
    const leadsCtx = document.getElementById('leadsChart').getContext('2d');
    new Chart(leadsCtx, {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            datasets: [{
                label: 'New Leads',
                data: [65, 59, 80, 81, 56, 55],
                borderColor: '#007bff',
                tension: 0.4,
                fill: false
            }, {
                label: 'Converted',
                data: [28, 48, 40, 19, 86, 27],
                borderColor: '#28a745',
                tension: 0.4,
                fill: false
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top'
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
    
    // Lead Sources Chart
    const sourcesCtx = document.getElementById('sourcesChart').getContext('2d');
    new Chart(sourcesCtx, {
        type: 'doughnut',
        data: {
            labels: ['Website', 'Referral', 'Social', 'Email', 'Other'],
            datasets: [{
                data: [30, 25, 20, 15, 10],
                backgroundColor: [
                    '#007bff',
                    '#28a745',
                    '#ffc107',
                    '#dc3545',
                    '#6c757d'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
});
</script>

<?php require_once '../includes/footer.php'; ?> 