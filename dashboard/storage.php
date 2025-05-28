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

// Fetch users for the assign to dropdown (for task modal)
$usersList = [];
try {
    $usersResult = executeQuery("SELECT id, first_name, last_name FROM users WHERE status = 'active' ORDER BY first_name")->get_result();
    if ($usersResult) {
        while ($user = $usersResult->fetch_assoc()) {
            $usersList[] = [
                'id' => $user['id'],
                'name' => htmlspecialchars($user['first_name'] . ' ' . $user['last_name'])
            ];
        }
    }
} catch (Exception $e) {
    error_log("Error fetching users for dropdown: " . $e->getMessage());
}

// Placeholder for storage items (in a real application, this would come from a database)
$storageItems = []; // Empty for now to show the "no records" message

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Storage Management</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/dashboard_style.css">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts (Poppins) -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4f46e5;
            --primary-hover: #4338ca;
            --secondary-color: #6366f1;
            --light-bg: #f9fafb;
            --border-color: #e5e7eb;
            --text-primary: #111827;
            --text-secondary: #4b5563;
            --text-muted: #6b7280;
            --success-color: #10b981;
            --danger-color: #ef4444;
            --warning-color: #f59e0b;
            --card-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --transition: all 0.3s ease;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--light-bg);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            color: var(--text-primary);
            line-height: 1.5;
        }
        
        /* Dashboard Layout Styles */
        .dashboard-container {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        .sidebar {
            background-color: #fff;
            box-shadow: var(--card-shadow);
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;
            transition: var(--transition);
            border-right: 1px solid var(--border-color);
        }
        
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0,0,0,0.5);
            z-index: 999;
            backdrop-filter: blur(2px);
        }
        
        .sidebar-overlay.show {
            display: block;
        }
        
        .main-content-area {
            margin-left: 16.666667%; /* col-md-2 width */
            transition: var(--transition);
            padding: 1rem;
        }
        
        @media (max-width: 767.98px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .sidebar.show {
                transform: translateX(0);
            }
            .main-content-area {
                margin-left: 0;
            }
        }
        
        .sidebar-toggle {
            position: fixed;
            top: 1rem;
            left: 1rem;
            z-index: 1001;
            background: #fff;
            border: none;
            border-radius: 8px;
            padding: 0.5rem;
            box-shadow: var(--card-shadow);
            color: var(--primary-color);
            transition: var(--transition);
        }
        
        .sidebar-toggle:hover {
            background-color: var(--primary-color);
            color: white;
        }
        
        .navbar {
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            background-color: white;
            padding: 0.75rem 1.5rem;
        }
        
        footer {
            margin-top: auto;
            background-color: white;
            border-top: 1px solid var(--border-color);
            padding: 1rem 0;
            text-align: center;
            color: var(--text-muted);
            font-size: 0.875rem;
        }
        
        /* Storage Management Specific Styles */
        .storage-card {
            background-color: #fff;
            border-radius: 12px;
            box-shadow: var(--card-shadow);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            border: 1px solid var(--border-color);
            transition: var(--transition);
        }
        
        .storage-card:hover {
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
        
        .storage-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--border-color);
        }
        
        .storage-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--text-primary);
            position: relative;
            padding-left: 0.5rem;
        }
        
        .storage-title::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0.25rem;
            bottom: 0.25rem;
            width: 4px;
            background-color: var(--primary-color);
            border-radius: 4px;
        }
        
        .storage-usage {
            background-color: var(--light-bg);
            border-radius: 10px;
            padding: 1rem 1.25rem;
            margin-bottom: 1.5rem;
            border: 1px solid var(--border-color);
        }
        
        .storage-usage span {
            font-weight: 500;
            color: var(--text-secondary);
        }
        
        .progress {
            height: 8px;
            border-radius: 4px;
            margin-top: 0.75rem;
            background-color: #e5e7eb;
            overflow: hidden;
        }
        
        .progress-bar {
            background-color: var(--primary-color);
            transition: width 0.6s ease;
        }
        
        .storage-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }
        
        .storage-table th {
            background-color: var(--light-bg);
            color: var(--text-secondary);
            font-weight: 500;
            padding: 0.875rem 1rem;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        
        .storage-table td {
            padding: 1rem;
            border-bottom: 1px solid var(--border-color);
            color: var(--text-primary);
            vertical-align: middle;
        }
        
        .storage-table tr:hover {
            background-color: rgba(99, 102, 241, 0.05);
        }
        
        .storage-table tr:last-child td {
            border-bottom: none;
        }
        
        .storage-table .empty-state {
            text-align: center;
            padding: 3rem 0;
            color: var(--text-muted);
        }
        
        .btn-delete {
            background-color: var(--danger-color);
            color: white;
            border: none;
            padding: 0.5rem 1.25rem;
            border-radius: 8px;
            transition: var(--transition);
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .btn-delete:hover {
            background-color: #dc2626;
            transform: translateY(-1px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        
        .btn-delete:active {
            transform: translateY(0);
        }
        
        .category-item {
            display: flex;
            align-items: center;
            padding: 0.875rem 1.25rem;
            color: var(--text-secondary);
            text-decoration: none;
            border-radius: 8px;
            margin-bottom: 0.5rem;
            transition: var(--transition);
            font-weight: 500;
            border-left: 3px solid transparent;
        }
        
        .category-item:hover {
            background-color: rgba(99, 102, 241, 0.1);
            color: var(--primary-color);
            border-left-color: var(--primary-color);
        }
        
        .category-item.active {
            background-color: var(--primary-color);
            color: white;
            border-left-color: white;
        }
        
        .category-header {
            font-weight: 600;
            color: var(--text-muted);
            padding: 0.75rem 1.25rem;
            margin-top: 1.5rem;
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        
        .form-check-input:checked {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .action-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.25rem;
            padding: 0.75rem 1rem;
            background-color: var(--light-bg);
            border-radius: 8px;
        }
        
        .btn-sm {
            padding: 0.375rem 0.75rem;
            font-size: 0.875rem;
            border-radius: 6px;
        }
        
        .btn-link {
            color: var(--primary-color);
            text-decoration: none;
            padding: 0.375rem 0.75rem;
            border-radius: 6px;
            transition: var(--transition);
        }
        
        .btn-link:hover {
            background-color: rgba(99, 102, 241, 0.1);
            color: var(--primary-hover);
        }
        
        .btn-outline-secondary {
            border-color: var(--border-color);
            color: var(--text-secondary);
        }
        
        .btn-outline-secondary:hover {
            background-color: var(--light-bg);
            color: var(--text-primary);
            border-color: var(--text-secondary);
        }
        
        /* Add nav icons */
        .nav-icon {
            margin-right: 0.75rem;
            font-size: 1.125rem;
            width: 1.5rem;
            text-align: center;
            opacity: 0.8;
        }
        
        /* Empty state styling */
        .empty-state-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 3rem 1rem;
            color: var(--text-muted);
        }
        
        .empty-state-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: var(--border-color);
        }
        
        .empty-state-text {
            font-size: 1rem;
            margin-bottom: 0.5rem;
        }
        
        .empty-state-subtext {
            font-size: 0.875rem;
            max-width: 400px;
            text-align: center;
        }
    
    </style>
</head>
<body>

<div class="dashboard-container">
    <div class="row g-0">
        <!-- Mobile Toggle Button -->
        <button class="sidebar-toggle d-md-none" id="sidebarToggle">
            <i class="fas fa-bars"></i>
        </button>
        <!-- Mobile Overlay -->
        <div class="sidebar-overlay" id="sidebarOverlay"></div>
        <!-- Sidebar -->
        <div class="col-md-3 col-lg-2 sidebar" id="sidebarMenu">
            <?php include '../includes/sidebar.php'; ?>
        </div>

        <!-- Main Content Area -->
        <div class="col-md-9 col-lg-10 main-content-area">
            <!-- Header -->
            <?php include '../includes/dashboard-header.php'; ?>

            <!-- Main Content -->
            <div class="container-fluid py-4">
                <!-- Page content starts here -->
                
                <!-- Storage Management Interface -->
                <div class="row">
                    <!-- Left Sidebar with Categories -->
                    <div class="col-md-3 mb-4">
                        <div class="storage-card">
                            <a href="#" class="category-item active" id="taskTemplate">
                                Task
                            </a>
                            <a href="#" class="category-item" id="whatsappTemplate">
                                Whatsapp template
                            </a>
                        </div>
                    </div>
                    
                    <!-- Main Content Area -->
                    <div class="col-md-9">
                        <div class="storage-card">
                            <div class="storage-header">
                                <h2 class="storage-title">Task</h2>
                            </div>
                            

                            
                            <!-- Storage Content Area -->
                            <div class="storage-content-area" id="storageContentArea">
                            </div>
                            
                            <!-- Action Buttons -->
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <div>
                                    <button class="btn btn-sm btn-link text-decoration-none" id="selectAllButton">
                                        Select All
                                    </button>
                                </div>
                                <button class="btn btn-delete" id="deleteButton">
                                    <i class="fas fa-trash me-1"></i> Delete
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <?php include '../includes/dashboard-footer.php'; ?>
        </div>
    </div>
</div>

<!-- Bootstrap 5 JS and dependencies -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>

<!-- Include Add Lead Modal -->
<?php include '../includes/modals/add-lead.php'; ?>

<!-- Include Add Task Modal -->
<?php include '../includes/modals/add-task.php'; ?>

<!-- Storage Management Scripts -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Select All functionality
        document.getElementById('selectAllButton').addEventListener('click', function() {
            const checkboxes = document.querySelectorAll('.storage-table .form-check-input');
            const allChecked = Array.from(checkboxes).every(checkbox => checkbox.checked);
            
            checkboxes.forEach(checkbox => {
                checkbox.checked = !allChecked;
            });
            
            // Update button text
            this.textContent = allChecked ? 'Select All' : 'Deselect All';
        });
        
        // Delete confirmation
        document.getElementById('deleteButton').addEventListener('click', function() {
            const selectedItems = document.querySelectorAll('.storage-table .form-check-input:checked').length;
            
            if (selectedItems > 0) {
                if (confirm(`Are you sure you want to delete ${selectedItems} item(s)?`)) {
                    // Delete logic would go here
                    alert('Items deleted successfully!');
                    // Reload page or update UI
                    const activeItem = document.querySelector('.category-item.active');
                    if (activeItem) {
                        activeItem.click(); // Refresh the view
                    }
                }
            } else {
                alert('Please select at least one item to delete.');
            }
        });

        // Handle sidebar navigation
        const sidebarLinks = document.querySelectorAll('.sidebar .nav-link');
        sidebarLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                const href = this.getAttribute('href');
                if (href.includes('leads.php')) {
                    e.preventDefault();
                    // Show Add Lead Modal instead of navigating
                    const addLeadModal = new bootstrap.Modal(document.getElementById('addLeadModal'));
                    addLeadModal.show();
                } else if (href.includes('tasks.php')) {
                    e.preventDefault();
                    // Show Add Task Modal instead of navigating
                    const addTaskModal = new bootstrap.Modal(document.getElementById('addTaskModal'));
                    addTaskModal.show();
                }
                // Other links will navigate normally
            });
        });

        // Handle sidebar item clicks
        const sidebarItems = document.querySelectorAll('.category-item');
        const contentArea = document.getElementById('storageContentArea');
        const deleteButton = document.getElementById('deleteButton');
        
        sidebarItems.forEach(item => {
            item.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Remove active class from all items
                sidebarItems.forEach(i => i.classList.remove('active'));
                // Add active class to clicked item
                this.classList.add('active');
                
                // Update title based on which item was clicked
                const storageTitle = document.querySelector('.storage-title');
                if (storageTitle) {
                    storageTitle.textContent = this.textContent.trim();
                }
                
                // Common storage usage HTML
                const storageUsageHTML = `
                    <div class="storage-usage mb-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <span>Storage Usage</span>
                            <span>0/100</span>
                        </div>
                        <div class="progress">
                            <div class="progress-bar" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                `;
                
                // Show content for both items - without checkboxes when no data
                if (this.id === 'whatsappTemplate') {
                    contentArea.innerHTML = storageUsageHTML + `
                        <div class="table-responsive">
                            <table class="storage-table">
                                <tbody>
                                    <tr>
                                        <td class="text-center py-4">There are no records to display.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    `;
                } else if (this.id === 'taskTemplate') {
                    contentArea.innerHTML = storageUsageHTML + `
                        <div class="table-responsive">
                            <table class="storage-table">
                                <tbody>
                                    <tr>
                                        <td class="text-center py-4">There are no records to display.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    `;
                }
                
                // Hide delete button when there's no data
                deleteButton.classList.add('d-none');
            });
        });
        
        // Trigger click on Task by default
        document.getElementById('taskTemplate').click();
    });
</script>

</body>
</html>