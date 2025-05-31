<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include config file
require_once '../includes/config.php';

// Get current currency
$current_currency = isset($_COOKIE['currency']) ? $_COOKIE['currency'] : 'INR';

// Pricing data
$pricing_plans = [
    'basic' => [
        'name' => 'Basic',
        'price' => ['INR' => 365, 'USD' => 7],
        'features' => [
            'Lead Management',
            'Task Management',
            'Reminders',
            'Calendar',
            '1GB Storage',
            'Basic Support'
        ]
    ],
    'silver' => [
        'name' => 'Silver',
        'price' => ['INR' => 1000, 'USD' => 17],
        'popular' => true,
        'features' => [
            'All Basic Features',
            '1 Staff Member',
            'Chat Feature',
            'Enhanced Reporting',
            '1GB Storage',
            'Priority Support'
        ]
    ],
    'diamond' => [
        'name' => 'Diamond',
        'price' => ['INR' => 2500, 'USD' => 42],
        'features' => [
            'All Silver Features',
            '5 Staff Members',
            'Automation Rules',
            'Integrations (WhatsApp, Email, SMS)',
            'Lead Dynamic Fields',
            'Department Management',
            'Premium Support'
        ]
    ]
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upgrade Your Account - <?php echo SITE_NAME; ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="<?php echo SITE_URL; ?>/public/css/style.css" rel="stylesheet">
    <style>
        .upgrade-header {
            background: linear-gradient(135deg, #4f46e5 0%, #7e74f1 100%);
            color: white;
            padding: 4rem 0;
            margin-bottom: 3rem;
        }
        
        .pricing-card {
            border: 1px solid rgba(0, 0, 0, 0.1);
            border-radius: 1rem;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            background: white;
            overflow: hidden;
            height: 100%;
        }
        
        .pricing-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
        
        .pricing-card.popular {
            border: 2px solid #4f46e5;
            position: relative;
        }
        
        .popular-badge {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: #4f46e5;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 2rem;
            font-size: 0.8rem;
            font-weight: 600;
        }
        
        .contact-options {
            margin-top: 4rem;
            text-align: center;
            padding: 2rem;
            background: #f8f9fa;
            border-radius: 1rem;
        }
        
        .contact-option {
            display: inline-flex;
            align-items: center;
            margin: 0.5rem;
            padding: 1rem 2rem;
            border-radius: 50px;
            background-color: white;
            color: #333;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }
        
        .contact-option:hover {
            background-color: #4f46e5;
            color: white;
            transform: translateY(-2px);
        }
        
        .contact-option i {
            margin-right: 0.5rem;
            font-size: 1.2rem;
        }
        
        .currency-toggle {
            background: rgba(255, 255, 255, 0.1);
            padding: 0.5rem;
            border-radius: 2rem;
            display: inline-flex;
        }
        
        .currency-toggle button {
            color: white;
            border: none;
            padding: 0.5rem 1.5rem;
            border-radius: 2rem;
            background: transparent;
            transition: all 0.3s ease;
        }
        
        .currency-toggle button.active {
            background: white;
            color: #4f46e5;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <?php include '../includes/navbar.php'; ?>

    <!-- Header Section -->
    <div class="upgrade-header text-center">
        <div class="container">
            <h1 class="display-4 fw-bold mb-3">Upgrade Your Account</h1>
            <p class="lead mb-4 text-white">Choose the perfect plan for your business needs</p>
            
            <!-- Currency Toggle -->
            <div class="currency-toggle">
                <button type="button" class="<?php echo $current_currency === 'INR' ? 'active' : ''; ?>" onclick="setCurrency('INR')">
                    <i class="fas fa-rupee-sign me-1"></i> INR
                </button>
                <button type="button" class="<?php echo $current_currency === 'USD' ? 'active' : ''; ?>" onclick="setCurrency('USD')">
                    <i class="fas fa-dollar-sign me-1"></i> USD
                </button>
            </div>
        </div>
    </div>

    <!-- Pricing Section -->
    <div class="container mb-5">
        <div class="row g-4">
            <?php foreach ($pricing_plans as $plan_key => $plan): ?>
            <div class="col-md-4">
                <div class="pricing-card <?php echo isset($plan['popular']) && $plan['popular'] ? 'popular' : ''; ?>">
                    <?php if (isset($plan['popular']) && $plan['popular']): ?>
                        <div class="popular-badge">Most Popular</div>
                    <?php endif; ?>
                    
                    <div class="card-body p-4">
                        <h3 class="text-center mb-4"><?php echo $plan['name']; ?></h3>
                        
                        <div class="text-center mb-4">
                            <h2 class="display-4 fw-bold mb-0">
                                <?php echo $current_currency === 'INR' ? '₹' : '$'; ?><?php echo $plan['price'][$current_currency]; ?>
                            </h2>
                            <p class="text-muted">per month</p>
                        </div>
                        
                        <ul class="list-unstyled mb-4">
                            <?php foreach ($plan['features'] as $feature): ?>
                            <li class="mb-2">
                                <i class="fas fa-check text-success me-2"></i>
                                <?php echo $feature; ?>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                        
                        <div class="text-center">
                            <a href="https://wa.me/919913299890?text=I'm%20interested%20in%20the%20<?php echo urlencode($plan['name']); ?>%20plan" 
                               class="btn btn-lg <?php echo isset($plan['popular']) && $plan['popular'] ? 'btn-primary' : 'btn-outline-primary'; ?> w-100" 
                               target="_blank">
                                <i class="fab fa-whatsapp me-2"></i>Get Started
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Contact Options -->
        <div class="contact-options">
            <h3 class="mb-4">Need help choosing the right plan?</h3>
            <div>
                <a href="https://wa.me/919913299890" class="contact-option" target="_blank">
                    <i class="fab fa-whatsapp"></i> Chat on WhatsApp
                </a>
                <a href="mailto:contact@example.com" class="contact-option">
                    <i class="fas fa-envelope"></i> Send an Email
                </a>
                <a href="tel:+919913299890" class="contact-option">
                    <i class="fas fa-phone"></i> Call Us
                </a>
            </div>
        </div>

        <!-- FAQ Section -->
        <div class="mt-5 pt-5">
            <h3 class="text-center mb-4">Frequently Asked Questions</h3>
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="accordion" id="upgradeFAQ">
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                    Can I upgrade my plan later?
                                </button>
                            </h2>
                            <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#upgradeFAQ">
                                <div class="accordion-body">
                                    Yes, you can upgrade your plan at any time. When you upgrade, we'll prorate the remaining days on your current plan and apply them to your new plan.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                    What payment methods do you accept?
                                </button>
                            </h2>
                            <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#upgradeFAQ">
                                <div class="accordion-body">
                                    We accept all major credit cards, debit cards, and UPI payments. For custom payment arrangements, please contact our support team.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                    Is there a long-term contract?
                                </button>
                            </h2>
                            <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#upgradeFAQ">
                                <div class="accordion-body">
                                    No, all our plans are month-to-month with no long-term commitment required. You can cancel or change your plan at any time.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer mt-auto py-5 bg-light">
        <div class="container">
            <div class="row g-4">
                <!-- Company Info -->
                <div class="col-lg-4 mb-4 mb-lg-0">
                    <h5 class="fw-bold mb-3"><?php echo SITE_NAME; ?></h5>
                    <p class="text-muted mb-3">The complete solution for managing your leads and growing your business efficiently.</p>
                    <div class="social-links">
                        <a href="#" class="me-3 text-dark"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="me-3 text-dark"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="me-3 text-dark"><i class="fab fa-linkedin-in"></i></a>
                        <a href="#" class="text-dark"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>

                <!-- Quick Links -->
                <div class="col-lg-2 col-md-4 mb-4 mb-lg-0">
                    <h6 class="fw-bold mb-3">Quick Links</h6>
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2"><a href="<?php echo SITE_URL; ?>/public/index.php" class="text-muted text-decoration-none">Home</a></li>
                        <li class="mb-2"><a href="<?php echo SITE_URL; ?>/public/features.php" class="text-muted text-decoration-none">Features</a></li>
                        <li class="mb-2"><a href="<?php echo SITE_URL; ?>/public/pricing.php" class="text-muted text-decoration-none">Pricing</a></li>
                        <li class="mb-2"><a href="<?php echo SITE_URL; ?>/public/contact.php" class="text-muted text-decoration-none">Contact</a></li>
                    </ul>
                </div>

                <!-- Legal -->
                <div class="col-lg-2 col-md-4 mb-4 mb-lg-0">
                    <h6 class="fw-bold mb-3">Legal</h6>
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2"><a href="<?php echo SITE_URL; ?>/public/privacy.php" class="text-muted text-decoration-none">Privacy Policy</a></li>
                        <li class="mb-2"><a href="<?php echo SITE_URL; ?>/public/terms.php" class="text-muted text-decoration-none">Terms of Service</a></li>
                        <li class="mb-2"><a href="<?php echo SITE_URL; ?>/public/refund.php" class="text-muted text-decoration-none">Refund Policy</a></li>
                        <li class="mb-2"><a href="<?php echo SITE_URL; ?>/public/faq.php" class="text-muted text-decoration-none">FAQ</a></li>
                    </ul>
                </div>

                <!-- Contact Info -->
                <div class="col-lg-4 col-md-4">
                    <h6 class="fw-bold mb-3">Contact Us</h6>
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <i class="fas fa-phone me-2 text-primary"></i>
                            <a href="tel:+919913299890" class="text-muted text-decoration-none">+91 99132 99890</a>
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-envelope me-2 text-primary"></i>
                            <a href="mailto:contact@example.com" class="text-muted text-decoration-none">contact@example.com</a>
                        </li>
                        <li class="mb-2">
                            <i class="fab fa-whatsapp me-2 text-primary"></i>
                            <a href="https://wa.me/919913299890" class="text-muted text-decoration-none">WhatsApp Support</a>
                        </li>
                    </ul>
                </div>
            </div>

            <hr class="my-4">

            <!-- Copyright -->
            <div class="row align-items-center">
                <div class="col-md-6 text-center text-md-start">
                    <p class="small text-muted mb-0">© <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <img src="<?php echo SITE_URL; ?>/public/assets/images/payment-methods.png" alt="Payment Methods" class="payment-methods" style="height: 24px;">
                </div>
            </div>
        </div>
    </footer>

    <style>
        .footer {
            background-color: #f8f9fa;
            border-top: 1px solid rgba(0, 0, 0, 0.05);
        }
        
        .footer h5, .footer h6 {
            color: #2d3748;
        }
        
        .footer .social-links a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }
        
        .footer .social-links a:hover {
            background: #4f46e5;
            color: white !important;
            transform: translateY(-2px);
        }
        
        .footer a {
            transition: all 0.2s ease;
        }
        
        .footer a:hover {
            color: #4f46e5 !important;
            text-decoration: none;
        }
        
        .payment-methods {
            opacity: 0.7;
            transition: opacity 0.2s ease;
        }
        
        .payment-methods:hover {
            opacity: 1;
        }
    </style>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function setCurrency(currency) {
            document.cookie = "currency=" + currency + "; path=/";
            location.reload();
        }
        
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