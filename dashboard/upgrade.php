<?php
// Start output buffering
ob_start();

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include config file
require_once '../includes/config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page
    header("Location: " . SITE_URL . "/public/login.php");
    exit;
}

// Include trial functions
require_once '../includes/trial_functions.php';

// Get user information
$user_id = $_SESSION['user_id'];
try {
    $sql = "SELECT * FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    // If user not found in database, clear session and redirect to login
    if (!$user) {
        session_destroy();
        header("Location: " . SITE_URL . "/public/login.php?error=account_deleted");
        exit;
    }

    // Check if trial is expired
    $trial_expired = isTrialExpired($user_id);
    $days_remaining = getTrialDaysRemaining($user_id);

    // Page title
    $page_title = "Upgrade Your Account";
} catch (Exception $e) {
    error_log("Error in upgrade.php: " . $e->getMessage());
    session_destroy();
    header("Location: " . SITE_URL . "/public/login.php?error=system_error");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - <?php echo SITE_NAME; ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="<?php echo SITE_URL; ?>/public/css/style.css" rel="stylesheet">
    <!-- Dashboard CSS -->
    <link href="<?php echo SITE_URL; ?>/dashboard/assets/css/dashboard.css" rel="stylesheet">
    <style>
        .upgrade-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        .trial-expired-alert {
            background: linear-gradient(135deg, #ff6b6b 0%, #ff8e8e 100%);
            color: white;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .trial-active-alert {
            background: linear-gradient(135deg, #4f46e5 0%, #7e74f1 100%);
            color: white;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .trial-active-alert h3,
        .trial-active-alert p,
        .trial-expired-alert h3,
        .trial-expired-alert p {
            color: white !important;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
        }
        
        .trial-active-alert strong {
            color: #ffffff;
            font-weight: 700;
            text-decoration: underline;
        }
        
        .pricing-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            margin-bottom: 2rem;
        }
        
        .pricing-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
        
        .contact-options {
            margin-top: 3rem;
            text-align: center;
        }
        
        .contact-option {
            display: inline-flex;
            align-items: center;
            margin: 0 1rem;
            padding: 0.5rem 1rem;
            border-radius: 50px;
            background-color: #f8f9fa;
            color: #333;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .contact-option:hover {
            background-color: #4f46e5;
            color: white;
        }
        
        .contact-option i {
            margin-right: 0.5rem;
            font-size: 1.2rem;
        }
    </style>
</head>
<body>

<!-- Mobile Toggle Button -->
<button class="sidebar-toggle d-md-none" id="sidebarToggle">
    <i class="fas fa-bars"></i>
</button>

<!-- Sidebar Overlay -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- Main Container -->
<div class="dashboard-container">
    <!-- Sidebar -->
    <div class="sidebar-container" id="sidebarMenu">
        <?php include '../includes/sidebar.php'; ?>
    </div>
    
    <!-- Main Content -->
    <div class="main-content">
        <!-- Include dashboard header -->
        <?php include '../includes/dashboard_header.php'; ?>
            
            <div class="upgrade-container">
                <?php if ($trial_expired): ?>
                    <div class="trial-expired-alert">
                        <h3><i class="fas fa-exclamation-circle me-2"></i> Your Free Trial Has Expired</h3>
                        <p class="mb-0">Your 7-day free trial period has ended. To continue using all features of <?php echo SITE_NAME; ?>, please upgrade to one of our premium plans below.</p>
                    </div>
                <?php else: ?>
                    <div class="trial-active-alert">
                        <h3><i class="fas fa-info-circle me-2"></i> Your Free Trial Is Active</h3>
                        <p class="mb-0">You have <strong><?php echo $days_remaining; ?> days</strong> remaining in your free trial. Consider upgrading to a premium plan to ensure uninterrupted access to all features.</p>
                    </div>
                <?php endif; ?>
                
                <h2 class="text-center mb-4">Choose Your Plan</h2>
                
                <div class="row">
                    <!-- Basic Plan -->
                    <div class="col-md-4">
                        <div class="pricing-card card h-100">
                            <div class="card-header bg-light">
                                <h4 class="my-0 fw-normal text-center">Basic</h4>
                            </div>
                            <div class="card-body d-flex flex-column">
                                <h1 class="card-title pricing-card-title text-center">₹365 <small class="text-muted fw-light">/month</small></h1>
                                <ul class="list-unstyled mt-3 mb-4">
                                    <li><i class="fas fa-check text-success me-2"></i> Lead Management</li>
                                    <li><i class="fas fa-check text-success me-2"></i> Task Management</li>
                                    <li><i class="fas fa-check text-success me-2"></i> Reminders</li>
                                    <li><i class="fas fa-check text-success me-2"></i> Calendar</li>
                                    <li><i class="fas fa-check text-success me-2"></i> 1GB Storage</li>
                                    <li><i class="fas fa-times text-danger me-2"></i> Staff Management</li>
                                </ul>
                                <a href="https://wa.me/919913299890?text=I'm%20interested%20in%20the%20Basic%20plan" class="btn btn-lg btn-outline-primary mt-auto w-100" target="_blank">
                                    <i class="fab fa-whatsapp me-2"></i> Get Started
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Silver Plan -->
                    <div class="col-md-4">
                        <div class="pricing-card card h-100 border-primary">
                            <div class="card-header bg-primary text-white">
                                <h4 class="my-0 fw-normal text-center">Silver</h4>
                            </div>
                            <div class="card-body d-flex flex-column">
                                <h1 class="card-title pricing-card-title text-center">₹1000 <small class="text-muted fw-light">/month</small></h1>
                                <ul class="list-unstyled mt-3 mb-4">
                                    <li><i class="fas fa-check text-success me-2"></i> All Basic Features</li>
                                    <li><i class="fas fa-check text-success me-2"></i> 1 Staff Member</li>
                                    <li><i class="fas fa-check text-success me-2"></i> Chat Feature</li>
                                    <li><i class="fas fa-check text-success me-2"></i> Enhanced Reporting</li>
                                    <li><i class="fas fa-check text-success me-2"></i> 1GB Storage</li>
                                    <li><i class="fas fa-times text-danger me-2"></i> Automation Rules</li>
                                </ul>
                                <a href="https://wa.me/919913299890?text=I'm%20interested%20in%20the%20Silver%20plan" class="btn btn-lg btn-primary mt-auto w-100" target="_blank">
                                    <i class="fab fa-whatsapp me-2"></i> Get Started
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Diamond Plan -->
                    <div class="col-md-4">
                        <div class="pricing-card card h-100">
                            <div class="card-header bg-dark text-white">
                                <h4 class="my-0 fw-normal text-center">Diamond</h4>
                            </div>
                            <div class="card-body d-flex flex-column">
                                <h1 class="card-title pricing-card-title text-center">₹2500 <small class="text-muted fw-light">/month</small></h1>
                                <ul class="list-unstyled mt-3 mb-4">
                                    <li><i class="fas fa-check text-success me-2"></i> All Silver Features</li>
                                    <li><i class="fas fa-check text-success me-2"></i> 5 Staff Members</li>
                                    <li><i class="fas fa-check text-success me-2"></i> Automation Rules</li>
                                    <li><i class="fas fa-check text-success me-2"></i> Integrations (WhatsApp, Email, SMS)</li>
                                    <li><i class="fas fa-check text-success me-2"></i> Lead Dynamic Fields</li>
                                    <li><i class="fas fa-check text-success me-2"></i> Department Management</li>
                                </ul>
                                <a href="https://wa.me/919913299890?text=I'm%20interested%20in%20the%20Diamond%20plan" class="btn btn-lg btn-outline-dark mt-auto w-100" target="_blank">
                                    <i class="fab fa-whatsapp me-2"></i> Get Started
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="text-center mt-4">
                    <a href="<?php echo SITE_URL; ?>/public/pricing.php" class="btn btn-link">View All Plans</a>
                </div>
                
                <div class="contact-options">
                    <p class="mb-3">Need help choosing the right plan?</p>
                    <a href="https://wa.me/919913299890" class="contact-option" target="_blank">
                        <i class="fab fa-whatsapp"></i> WhatsApp
                    </a>
                    <a href="mailto:contact@example.com" class="contact-option">
                        <i class="fas fa-envelope"></i> Email
                    </a>
                    <a href="tel:+919913299890" class="contact-option">
                        <i class="fas fa-phone"></i> Call
                    </a>
                </div>
            </div>
        </div>
    </div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- Custom JS -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Add animation to pricing cards
        const pricingCards = document.querySelectorAll('.pricing-card');
        pricingCards.forEach((card, index) => {
            setTimeout(() => {
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, 100 * index);
        });
    });
</script>
</body>
</html>
