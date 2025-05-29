<?php
require_once '../includes/config.php';

$current_currency = isset($_COOKIE['currency']) ? $_COOKIE['currency'] : 'INR';

// Pricing data (replace with database fetch later if needed)
$pricing_plans = [
    'basic' => ['name' => 'Basic', 'INR' => 365, 'USD' => 7, 'features' => ['Number of Staff' => '0', 'Lead' => true, 'Task' => true, 'Reminder' => true, 'Customer' => true, 'Meeting' => true, 'Chat' => false, 'Attendance' => false, 'Invoice' => false, 'Staff Leave' => false, 'Calendar' => true, 'Notes' => true, 'Holidays' => false, 'Templates' => false, 'Automations Rules' => false, 'SMS Integration' => false, 'Email Integration' => false, 'WhatsApp Integration' => false, 'Website Lead' => false, 'Facebook Lead' => false, 'Campaign' => false, 'Lead Dynamic Fields' => false, 'Department' => false, 'Storage' => '1 GB', 'Greetings' => false, 'WhatsApp Message Credits' => 'buy now']],
    'silver' => ['name' => 'Silver', 'INR' => 1000, 'original_INR' => 1250, 'USD' => 17, 'original_USD' => 21, 'features' => ['Number of Staff' => '1', 'Lead' => true, 'Task' => true, 'Reminder' => true, 'Customer' => true, 'Meeting' => true, 'Chat' => true, 'Attendance' => false, 'Invoice' => false, 'Staff Leave' => false, 'Calendar' => true, 'Notes' => true, 'Holidays' => false, 'Templates' => false, 'Automations Rules' => false, 'SMS Integration' => false, 'Email Integration' => false, 'WhatsApp Integration' => false, 'Website Lead' => false, 'Facebook Lead' => false, 'Campaign' => false, 'Lead Dynamic Fields' => false, 'Department' => false, 'Storage' => '1 GB', 'Greetings' => false, 'WhatsApp Message Credits' => 'buy now']],
    'gold' => ['name' => 'Gold', 'INR' => 1500, 'original_INR' => 1875, 'USD' => 24, 'original_USD' => 30, 'features' => ['Number of Staff' => '2', 'Lead' => true, 'Task' => true, 'Reminder' => true, 'Customer' => true, 'Meeting' => true, 'Chat' => true, 'Attendance' => true, 'Invoice' => false, 'Staff Leave' => false, 'Calendar' => true, 'Notes' => true, 'Holidays' => false, 'Templates' => false, 'Automations Rules' => false, 'SMS Integration' => false, 'Email Integration' => false, 'WhatsApp Integration' => false, 'Website Lead' => false, 'Facebook Lead' => false, 'Campaign' => false, 'Lead Dynamic Fields' => false, 'Department' => false, 'Storage' => '1 GB', 'Greetings' => false, 'WhatsApp Message Credits' => 'buy now']],
    'platinum' => ['name' => 'Platinum', 'INR' => 2000, 'original_INR' => 2500, 'USD' => 31, 'original_USD' => 39, 'features' => ['Number of Staff' => '3', 'Lead' => true, 'Task' => true, 'Reminder' => true, 'Customer' => true, 'Meeting' => true, 'Chat' => true, 'Attendance' => true, 'Invoice' => true, 'Staff Leave' => false, 'Calendar' => true, 'Notes' => true, 'Holidays' => true, 'Templates' => true, 'Automations Rules' => false, 'SMS Integration' => false, 'Email Integration' => false, 'WhatsApp Integration' => false, 'Website Lead' => false, 'Facebook Lead' => false, 'Campaign' => false, 'Lead Dynamic Fields' => false, 'Department' => false, 'Storage' => '1 GB', 'Greetings' => true, 'WhatsApp Message Credits' => 'buy now']],
    'diamond' => ['name' => 'Diamond', 'INR' => 2500, 'original_INR' => 3125, 'USD' => 40, 'original_USD' => 50, 'features' => ['Number of Staff' => '5', 'Lead' => true, 'Task' => true, 'Reminder' => true, 'Customer' => true, 'Meeting' => true, 'Chat' => true, 'Attendance' => true, 'Invoice' => true, 'Staff Leave' => true, 'Calendar' => true, 'Notes' => true, 'Holidays' => true, 'Templates' => true, 'Automations Rules' => true, 'SMS Integration' => true, 'Email Integration' => true, 'WhatsApp Integration' => true, 'Website Lead' => true, 'Facebook Lead' => true, 'Campaign' => true, 'Lead Dynamic Fields' => true, 'Department' => true, 'Storage' => '1 GB', 'Greetings' => true, 'WhatsApp Message Credits' => 'buy now']],
    'diamond_pro' => ['name' => 'Diamond Pro', 'INR' => 3950, 'original_INR' => 4938, 'USD' => 55, 'original_USD' => 69, 'features' => ['Number of Staff' => '5', 'Lead' => true, 'Task' => true, 'Reminder' => true, 'Customer' => true, 'Meeting' => true, 'Chat' => true, 'Attendance' => true, 'Invoice' => true, 'Staff Leave' => true, 'Calendar' => true, 'Notes' => true, 'Holidays' => true, 'Templates' => true, 'Automations Rules' => true, 'SMS Integration' => true, 'Email Integration' => true, 'WhatsApp Integration' => true, 'Website Lead' => true, 'Facebook Lead' => true, 'Campaign' => true, 'Lead Dynamic Fields' => true, 'Department' => true, 'Storage' => '1 GB', 'Greetings' => true, 'WhatsApp Message Credits' => 'buy now']],
];

$addon_services = [
    'Staff' => ['INR' => 365, 'USD' => 'TBD', 'unit' => '/Staff'],
    'Accounting' => ['INR' => 2500, 'USD' => 'TBD', 'unit' => '/Store'],
    'India Mart' => ['INR' => 1000, 'USD' => 'TBD', 'unit' => '/Year'],
    'Trade India' => ['INR' => 1000, 'USD' => 'TBD', 'unit' => '/Year'],
    'Google Form' => ['INR' => 1000, 'USD' => 'TBD', 'unit' => '/Year'],
    '99 Acres' => ['INR' => 1000, 'USD' => 'TBD', 'unit' => '/Year'],
    'Software Suggest' => ['INR' => 1000, 'USD' => 'TBD', 'unit' => '/Year'],
    'Just Dial' => ['INR' => 1000, 'USD' => 'TBD', 'unit' => '/Year'],
    'Magicbricks' => ['INR' => 1000, 'USD' => 'TBD', 'unit' => '/Year'],
    'Google Ads Lead From Assets' => ['INR' => 1000, 'USD' => 'TBD', 'unit' => '/Year'],
    'WordPress Integration' => ['INR' => 1000, 'USD' => 'TBD', 'unit' => '/Year'],
    'Staff Target' => ['INR' => 1000, 'USD' => 'TBD', 'unit' => '/Year'],
    'Google Calendar Integration' => ['INR' => 1000, 'USD' => 'TBD', 'unit' => '/Year'],
    'Housing' => ['INR' => 1000, 'USD' => 'TBD', 'unit' => '/Year'],
    'Service' => ['INR' => 1000, 'USD' => 'TBD', 'unit' => '/Year'],
    'Lead Attachment' => ['INR' => 1000, 'USD' => 'TBD', 'unit' => '/Year'],
    'Storage' => ['INR' => 500, 'USD' => 'TBD', 'unit' => '1 GB /Year'],
];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pricing - <?php echo SITE_NAME; ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="css/style.css" rel="stylesheet">
</head>
<body class="professional-theme">

<?php include '../includes/navbar.php'; ?>

<div class="container mt-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="d-none d-md-block mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>/public/index.php">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Pricing</li>
        </ol>
    </nav>

    <div class="pricing-header text-center mb-5">
        <span class="badge bg-primary mb-2">PRICING</span>
        <h1 class="display-4 fw-bold">Choose the Right Plan for Your Business</h1>
        <p class="lead text-muted">Affordable pricing for businesses of all sizes. No hidden fees.</p>
        
        <!-- Currency Toggle -->
        <div class="currency-toggle mt-4">
            <div class="btn-group" role="group" aria-label="Currency Toggle">
                <button type="button" class="btn <?php echo $current_currency === 'INR' ? 'btn-primary' : 'btn-outline-primary'; ?>" onclick="setCurrency('INR')"><i class="fas fa-rupee-sign me-1"></i> INR</button>
                <button type="button" class="btn <?php echo $current_currency === 'USD' ? 'btn-primary' : 'btn-outline-primary'; ?>" onclick="setCurrency('USD')"><i class="fas fa-dollar-sign me-1"></i> USD</button>
            </div>
        </div>
    </div>
    <div class="row justify-content-center pricing-cards">
        <?php foreach ($pricing_plans as $plan_key => $plan): ?>
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="pricing-card h-100 <?php echo $plan_key === 'diamond' ? 'popular' : ''; ?>">
                    <?php if ($plan_key === 'diamond'): ?>
                        <div class="popular-badge">Most Popular</div>
                    <?php endif; ?>
                    <div class="pricing-card-header">
                        <h3 class="plan-name"><?php echo $plan['name']; ?></h3>
                    </div>
                    <div class="pricing-card-body">
                        <div class="price-container">
                            <h2 class="price">
                                <span class="currency"><?php echo $current_currency === 'INR' ? '₹' : '$'; ?></span>
                                <span class="price-value" data-inr="<?php echo $plan['INR']; ?>" data-usd="<?php echo $plan['USD']; ?>"><?php echo $current_currency === 'INR' ? $plan['INR'] : $plan['USD']; ?></span>
                                <span class="period">/month</span>
                            </h2>
                            <?php if (isset($plan['original_' . $current_currency])): ?>
                                <p class="original-price">
                                    <del>
                                        <span class="currency-symbol"><?php echo $current_currency === 'INR' ? '₹' : '$'; ?></span>
                                        <span class="original-price-value" data-inr="<?php echo $plan['original_INR']; ?>" data-usd="<?php echo $plan['original_USD']; ?>"><?php echo $plan['original_' . $current_currency]; ?></span>
                                    </del>
                                    <span class="discount-badge">Save 20%</span>
                                </p>
                            <?php endif; ?>
                        </div>
                        
                        <div class="features-container">
                            <ul class="features-list">
                                <?php 
                                // Display the most important features first
                                $key_features = ['Number of Staff', 'Lead', 'Task', 'Customer', 'Meeting', 'Chat', 'Storage'];
                                foreach ($key_features as $feature): 
                                    if (isset($plan['features'][$feature])):
                                        $included = $plan['features'][$feature];
                                ?>
                                    <li class="feature-item">
                                        <?php if (is_bool($included)): ?>
                                            <?php if ($included): ?>
                                                <i class="fas fa-check-circle text-success me-2"></i>
                                            <?php else: ?>
                                                <i class="fas fa-times-circle text-danger me-2"></i>
                                            <?php endif; ?>
                                            <?php echo htmlspecialchars($feature); ?>
                                        <?php else: ?>
                                            <i class="fas fa-check-circle text-success me-2"></i>
                                            <?php echo htmlspecialchars($feature); ?>: <strong><?php echo htmlspecialchars($included); ?></strong>
                                        <?php endif; ?>
                                    </li>
                                <?php 
                                    endif;
                                endforeach; 
                                ?>
                            </ul>
                        </div>
                        
                        <div class="pricing-card-footer">
                            <a href="https://wa.me/919913299890?text=I'm%20interested%20in%20the%20<?php echo urlencode($plan['name']); ?>%20plan" target="_blank" class="btn btn-primary btn-lg w-100">
                                <i class="fab fa-whatsapp me-2"></i>Get Started
                            </a>
                            <a href="register.php" class="btn btn-outline-primary btn-lg w-100 mt-2">
                                <i class="fas fa-user-plus me-2"></i>Sign Up
                            </a>
                        </div>
                        
                        <?php if (count($plan['features']) > count($key_features)): ?>
                            <div class="see-all-features mt-3 text-center">
                                <a data-bs-toggle="collapse" href="#features-<?php echo $plan_key; ?>" role="button" aria-expanded="false" class="see-all-link toggle-features" data-plan="<?php echo $plan_key; ?>">
                                    <span class="show-text">See all features <i class="fas fa-chevron-down ms-1"></i></span>
                                    <span class="hide-text d-none">Hide features <i class="fas fa-chevron-up ms-1"></i></span>
                                </a>
                                <div class="collapse" id="features-<?php echo $plan_key; ?>">
                                    <ul class="features-list mt-3">
                                        <?php 
                                        foreach ($plan['features'] as $feature => $included): 
                                            if (!in_array($feature, $key_features)):
                                        ?>
                                            <li class="feature-item">
                                                <?php if (is_bool($included)): ?>
                                                    <?php if ($included): ?>
                                                        <i class="fas fa-check-circle text-success me-2"></i>
                                                    <?php else: ?>
                                                        <i class="fas fa-times-circle text-danger me-2"></i>
                                                    <?php endif; ?>
                                                    <?php echo htmlspecialchars($feature); ?>
                                                <?php else: ?>
                                                    <i class="fas fa-check-circle text-success me-2"></i>
                                                    <?php echo htmlspecialchars($feature); ?>: <strong><?php echo htmlspecialchars($included); ?></strong>
                                                <?php endif; ?>
                                            </li>
                                        <?php 
                                            endif;
                                        endforeach; 
                                        ?>
                                    </ul>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Add-on Services -->
    <div class="addon-services-section mt-5 pt-5 pb-4">
        <div class="section-header text-center mb-5">
            <span class="badge bg-secondary mb-2">ADD-ONS</span>
            <h2 class="fw-bold">Enhance Your Experience</h2>
            <p class="text-muted">Additional services to boost your productivity</p>
        </div>

        <div class="row justify-content-center g-4">
            <?php foreach ($addon_services as $service_name => $service): ?>
                <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                    <div class="addon-card h-100">
                        <div class="addon-icon mb-3">
                            <i class="<?php echo getAddonIcon($service_name); ?>"></i>
                        </div>
                        <h5 class="addon-title"><?php echo htmlspecialchars($service_name); ?></h5>
                        <div class="addon-price">
                            <span class="currency"><?php echo $current_currency === 'INR' ? '₹' : '$'; ?></span>
                            <span class="price-value" data-inr="<?php echo $service['INR']; ?>" data-usd="<?php echo $service['USD']; ?>"><?php echo $current_currency === 'INR' ? $service['INR'] : $service['USD']; ?></span>
                            <span class="unit"><?php echo htmlspecialchars($service['unit']); ?></span>
                        </div>
                        <a href="https://wa.me/919913299890?text=I'm%20interested%20in%20the%20<?php echo urlencode($service_name); ?>%20add-on" target="_blank" class="btn btn-sm btn-outline-primary mt-3">
                            <i class="fab fa-whatsapp me-1"></i>Buy now
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Note -->
    <div class="disclaimer-section mt-5">
        <div class="alert alert-light border" role="alert">
            <div class="d-flex">
                <div class="me-3">
                    <i class="fas fa-info-circle text-primary fa-2x"></i>
                </div>
                <div>
                    <h5 class="alert-heading">Important Information</h5>
                    <p class="mb-0">If any 3rd party integration is down due to maintenance on their side, <?php echo SITE_NAME; ?> is not responsible for the service. This includes any add-on packages.</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- FAQ Section -->
    <div class="faq-section mt-5 mb-5">
        <div class="section-header text-center mb-4">
            <h2 class="fw-bold">Frequently Asked Questions</h2>
            <p class="text-muted">Find answers to common questions about our pricing</p>
        </div>
        
        <div class="accordion" id="pricingFAQ">
            <div class="accordion-item">
                <h2 class="accordion-header" id="headingOne">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                        Can I upgrade my plan later?
                    </button>
                </h2>
                <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#pricingFAQ">
                    <div class="accordion-body">
                        Yes, you can upgrade your plan at any time. When you upgrade, we'll prorate the remaining days on your current plan and apply them to your new plan.
                    </div>
                </div>
            </div>
            <div class="accordion-item">
                <h2 class="accordion-header" id="headingTwo">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                        Do you offer a free trial?
                    </button>
                </h2>
                <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#pricingFAQ">
                    <div class="accordion-body">
                        Yes, we offer a 7-day free trial on all our plans. You can try out all the features before committing to a subscription.
                    </div>
                </div>
            </div>
            <div class="accordion-item">
                <h2 class="accordion-header" id="headingThree">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                        What payment methods do you accept?
                    </button>
                </h2>
                <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#pricingFAQ">
                    <div class="accordion-body">
                        We accept all major credit cards, debit cards, and online payment methods including PayPal, UPI, and bank transfers.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

<?php
// Helper function to get appropriate icon for add-on services
function getAddonIcon($serviceName) {
    $icons = [
        'Staff' => 'fas fa-users fa-2x text-primary',
        'Accounting' => 'fas fa-calculator fa-2x text-success',
        'India Mart' => 'fas fa-store fa-2x text-warning',
        'Attendance' => 'fas fa-clipboard-check fa-2x text-info',
        'Invoice' => 'fas fa-file-invoice-dollar fa-2x text-danger',
        'WhatsApp' => 'fab fa-whatsapp fa-2x text-success',
        'SMS' => 'fas fa-sms fa-2x text-primary',
        'Email' => 'fas fa-envelope fa-2x text-info',
        'Lead Attachment' => 'fas fa-paperclip fa-2x text-warning',
        'Storage' => 'fas fa-database fa-2x text-primary'
    ];
    
    // Default icon if no match is found
    $defaultIcon = 'fas fa-plus-circle fa-2x text-primary';
    
    // Check for partial matches
    foreach ($icons as $key => $icon) {
        if (stripos($serviceName, $key) !== false) {
            return $icon;
        }
    }
    
    return $defaultIcon;
}
?>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    function setCurrency(currency) {
        document.cookie = "currency=" + currency + "; path=/";
        location.reload();
    }
    
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize tooltips
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
        
        // Add animation to pricing cards on hover
        const pricingCards = document.querySelectorAll('.pricing-card');
        pricingCards.forEach(card => {
            card.addEventListener('mouseenter', function() {
                pricingCards.forEach(c => c.classList.remove('highlight'));
                this.classList.add('highlight');
            });
        });
        
        // Handle the See all features / Hide features toggle
        const toggleFeatures = document.querySelectorAll('.toggle-features');
        toggleFeatures.forEach(toggle => {
            // Initialize collapse elements with event listeners
            const planKey = toggle.getAttribute('data-plan');
            const collapseElement = document.getElementById('features-' + planKey);
            const showText = toggle.querySelector('.show-text');
            const hideText = toggle.querySelector('.hide-text');
            
            // Add event listeners to the collapse element
            collapseElement.addEventListener('show.bs.collapse', function () {
                // When opening
                showText.classList.add('d-none');
                hideText.classList.remove('d-none');
            });
            
            collapseElement.addEventListener('hide.bs.collapse', function () {
                // When closing
                showText.classList.remove('d-none');
                hideText.classList.add('d-none');
            });
        });
    });
</script>
</body>
</html>