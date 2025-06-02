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

// Get current page and rows per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$rows_per_page = isset($_GET['rows']) ? (int)$_GET['rows'] : 10;
$offset = ($page - 1) * $rows_per_page;

// Get total number of notes
$total_query = $conn->prepare("SELECT COUNT(*) as total FROM notes WHERE user_id = ?");
$total_query->bind_param("i", $_SESSION['user_id']);
$total_query->execute();
$total_result = $total_query->get_result();
$total_rows = $total_result->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $rows_per_page);

// Get notes for current page
$notes = [];
try {
    $stmt = $conn->prepare("SELECT id, content, created_at FROM notes WHERE user_id = ? ORDER BY created_at DESC LIMIT ? OFFSET ?");
    $stmt->bind_param("iii", $_SESSION['user_id'], $rows_per_page, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $notes[] = $row;
    }
} catch (Exception $e) {
    error_log("Error fetching notes: " . $e->getMessage());
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notes | Lead Management System</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts (Poppins) -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/dashboard_style.css">
    <style>
        :root {
            --header-height: 60px;
            --sidebar-width: 250px;
        }

        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            min-height: 100vh;
            background-color: #f8f9fa;
        }

        /* Header Styles */
        .header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: var(--header-height);
            background: #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            z-index: 1030;
        }

        /* Sidebar Styles */
        .sidebar {
            position: fixed;
            top: var(--header-height);
            left: 0;
            width: var(--sidebar-width);
            height: calc(100vh - var(--header-height));
            background: #fff;
            box-shadow: 2px 0 5px rgba(0,0,0,0.1);
            overflow-y: auto;
            z-index: 1020;
            transition: transform 0.3s ease;
        }

        /* Main Content Area */
        .main-content {
            margin-left: var(--sidebar-width);
            margin-top: var(--header-height);
            padding: 20px;
            min-height: calc(100vh - var(--header-height));
            background-color: #f8f9fa;
            transition: margin-left 0.3s ease;
        }

        /* Notes Container */
        .notes-container {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            padding: 20px;
            margin-bottom: 20px;
        }

        .notes-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .search-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .search-box {
            min-width: 300px;
        }

        /* Mobile Responsiveness */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .notes-header {
                flex-direction: column;
                align-items: stretch;
            }

            .search-actions {
                flex-direction: column;
            }

            .search-box {
                min-width: 100%;
            }

            .btn-add-note {
                width: 100%;
            }
        }

        /* Overlay */
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 1015;
        }

        .sidebar-overlay.show {
            display: block;
        }

        /* Footer */
        .footer {
            margin-left: var(--sidebar-width);
            padding: 1rem 0;
            background: #fff;
            border-top: 1px solid #dee2e6;
            transition: margin-left 0.3s ease;
        }

        @media (max-width: 768px) {
            .footer {
                margin-left: 0;
            }
        }

        /* Sidebar Toggle Button */
        .sidebar-toggle {
            display: none; /* Hidden by default on desktop */
            position: fixed;
            top: 15px;
            left: 15px;
            z-index: 1031;
            padding: 8px;
            background: #fff;
            border: none;
            border-radius: 4px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        /* Mobile Styles */
        @media (max-width: 768px) {
            .sidebar-toggle {
                display: block; /* Show on mobile */
            }

            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .footer {
                margin-left: 0;
            }

            .sidebar-overlay {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0,0,0,0.5);
                z-index: 1015;
                opacity: 0;
                transition: opacity 0.3s ease;
            }

            .sidebar-overlay.show {
                display: block;
                opacity: 1;
            }
        }

        .table th {
            background-color: #f8f9fa;
        }
        .action-buttons {
            white-space: nowrap;
        }
        .action-buttons .btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }
        .search-box {
            max-width: 300px;
        }
        .pagination-info {
            font-size: 0.875rem;
            color: #6c757d;
        }
        .no-records {
            text-align: center;
            padding: 40px 20px;
            color: #6c757d;
        }
        .no-records i {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <?php include '../includes/dashboard-header.php'; ?>
    </header>

    <!-- Sidebar Toggle Button -->
    <button class="sidebar-toggle d-md-none" id="sidebarToggle">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Sidebar Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <?php include '../includes/sidebar.php'; ?>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <div class="notes-container">
            <!-- Notes Header -->
            <div class="notes-header">
                <h2 class="mb-0">Notes</h2>
                <div class="search-actions">
                    <!-- Search Box -->
                    <div class="input-group search-box">
                        <input type="text" class="form-control" placeholder="Search notes...">
                        <button class="btn btn-outline-secondary" type="button">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                    <!-- Add Note Button -->
                    <button class="btn btn-primary btn-add-note" data-bs-toggle="modal" data-bs-target="#addNoteModal">
                        <i class="fas fa-plus me-2"></i>Add Note
                    </button>
                </div>
            </div>

            <!-- Notes Content -->
            <div class="notes-content">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>Note</th>
                                <th>Type</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($notes)): ?>
                            <tr>
                                <td colspan="4">
                                    <div class="no-records">
                                        <i class="fas fa-clipboard-list"></i>
                                        <p class="mb-0">There are no records to display</p>
                                    </div>
                                </td>
                            </tr>
                            <?php else: ?>
                                <?php foreach ($notes as $index => $note): ?>
                                <tr>
                                    <td><?php echo $offset + $index + 1; ?></td>
                                    <td><?php echo htmlspecialchars($note['content']); ?></td>
                                    <td>Note</td>
                                    <td class="action-buttons">
                                        <button class="btn btn-warning btn-sm edit-note" data-id="<?php echo $note['id']; ?>">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-danger btn-sm delete-note" data-id="<?php echo $note['id']; ?>">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-between align-items-center">
                    <div class="pagination-info">
                        Rows per page: 
                        <select id="rowsPerPage" class="form-select form-select-sm d-inline-block w-auto ms-1">
                            <option value="10" <?php echo $rows_per_page == 10 ? 'selected' : ''; ?>>10</option>
                            <option value="25" <?php echo $rows_per_page == 25 ? 'selected' : ''; ?>>25</option>
                            <option value="50" <?php echo $rows_per_page == 50 ? 'selected' : ''; ?>>50</option>
                        </select>
                        <span class="ms-3"><?php echo ($offset + 1) . ' - ' . min($offset + $rows_per_page, $total_rows) . ' of ' . $total_rows; ?></span>
                    </div>
                    <nav aria-label="Page navigation">
                        <ul class="pagination mb-0">
                            <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=1" aria-label="First">
                                    <span aria-hidden="true">&laquo;&laquo;</span>
                                </a>
                            </li>
                            <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $page - 1; ?>" aria-label="Previous">
                                    <span aria-hidden="true">&laquo;</span>
                                </a>
                            </li>
                            <li class="page-item">
                                <input type="number" class="form-control form-control-sm text-center" 
                                       style="width: 60px;" value="<?php echo $page; ?>" 
                                       min="1" max="<?php echo $total_pages; ?>">
                            </li>
                            <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $page + 1; ?>" aria-label="Next">
                                    <span aria-hidden="true">&raquo;</span>
                                </a>
                            </li>
                            <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $total_pages; ?>" aria-label="Last">
                                    <span aria-hidden="true">&raquo;&raquo;</span>
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <?php include '../includes/dashboard-footer.php'; ?>
    </footer>

    <!-- Add Note Modal -->
    <?php include '../includes/modals/add-note.php'; ?>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteNoteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Are You Sure?</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <i class="fas fa-exclamation-circle text-warning fa-3x mb-3"></i>
                    <p class="mb-0">Do you really want to delete this note?</p>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDelete">Yes, Delete It!</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Get DOM elements
        const sidebar = document.getElementById('sidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebarOverlay = document.getElementById('sidebarOverlay');
        const navbarToggler = document.querySelector('.navbar-toggler');

        // Function to toggle sidebar
        function toggleSidebar() {
            sidebar.classList.toggle('show');
            sidebarOverlay.classList.toggle('show');
            document.body.style.overflow = sidebar.classList.contains('show') ? 'hidden' : '';
        }

        // Event listeners for sidebar toggle
        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', toggleSidebar);
        }

        // Event listener for navbar toggler (if exists)
        if (navbarToggler) {
            navbarToggler.addEventListener('click', toggleSidebar);
        }

        // Close sidebar when clicking overlay
        if (sidebarOverlay) {
            sidebarOverlay.addEventListener('click', function() {
                sidebar.classList.remove('show');
                sidebarOverlay.classList.remove('show');
                document.body.style.overflow = '';
            });
        }

        // Close sidebar when pressing Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && sidebar.classList.contains('show')) {
                sidebar.classList.remove('show');
                sidebarOverlay.classList.remove('show');
                document.body.style.overflow = '';
            }
        });

        // Close sidebar when clicking outside
        document.addEventListener('click', function(e) {
            if (sidebar.classList.contains('show') && 
                !sidebar.contains(e.target) && 
                !sidebarToggle.contains(e.target) &&
                !navbarToggler.contains(e.target)) {
                sidebar.classList.remove('show');
                sidebarOverlay.classList.remove('show');
                document.body.style.overflow = '';
            }
        });

        // Handle window resize
        window.addEventListener('resize', function() {
            if (window.innerWidth > 768 && sidebar.classList.contains('show')) {
                sidebar.classList.remove('show');
                sidebarOverlay.classList.remove('show');
                document.body.style.overflow = '';
            }
        });

        // Handle delete button clicks
        let noteToDelete = null;
        const deleteModal = new bootstrap.Modal(document.getElementById('deleteNoteModal'));
        
        document.querySelectorAll('.delete-note').forEach(button => {
            button.addEventListener('click', function() {
                noteToDelete = this.dataset.id;
                deleteModal.show();
            });
        });
        
        // Handle delete confirmation
        document.getElementById('confirmDelete').addEventListener('click', function() {
            if (noteToDelete) {
                fetch('ajax/delete-note.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ id: noteToDelete })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        deleteModal.hide();
                        window.location.reload();
                    }
                })
                .catch(error => console.error('Error:', error));
            }
        });
        
        // Handle rows per page change
        document.getElementById('rowsPerPage').addEventListener('change', function() {
            window.location.href = '?rows=' + this.value;
        });
    });
    </script>
</body>
</html>