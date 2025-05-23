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

<?php include '../includes/header.php'; ?>

<div class="container mt-5">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Pricing</li>
        </ol>
    </nav>

    <h1 class="text-center mb-4">Our Pricing</h1>
    <p class="text-center mb-5">Pricing for 365 Lead Management CRM That is Affordable for Anyone.</p>

    <!-- Currency Toggle -->
    <div class="text-center mb-5">
        <div class="btn-group" role="group" aria-label="Currency toggle">
            <button type="button" class="btn btn-outline-primary <?php echo $current_currency === 'INR' ? 'active' : ''; ?>" onclick="setCurrency('INR')">INR</button>
            <button type="button" class="btn btn-outline-primary <?php echo $current_currency === 'USD' ? 'active' : ''; ?>" onclick="setCurrency('USD')">USD</button>
        </div>
    </div>

    <!-- Popular Pricing Plans -->
    <h2 class="text-center mb-4">Popular Pricing Plans</h2>
    <div class="row justify-content-center">
        <?php foreach ($pricing_plans as $plan_key => $plan): ?>
            <div class="col-md-4 col-sm-6 mb-4">
                <div class="card h-100 text-center">
                    <div class="card-header">
                        <h4 class="my-0 font-weight-normal"><?php echo $plan['name']; ?></h4>
                    </div>
                    <div class="card-body">
                        <h1 class="card-title pricing-card-title">
                            <span class="currency-symbol"><?php echo $current_currency === 'INR' ? '₹' : '$'; ?></span>
                            <span class="price-value" data-inr="<?php echo $plan['INR']; ?>" data-usd="<?php echo $plan['USD']; ?>"><?php echo $current_currency === 'INR' ? $plan['INR'] : $plan['USD']; ?></span>
                            <small class="text-muted">/Year</small>
                        </h1>
                        <?php if (isset($plan['original_INR'])): ?>
                            <p class="text-muted"><del>
                                <span class="currency-symbol"><?php echo $current_currency === 'INR' ? '₹' : '$'; ?></span>
                                <span class="original-price-value" data-inr="<?php echo $plan['original_INR']; ?>" data-usd="<?php echo $plan['original_USD']; ?>"><?php echo $current_currency === 'INR' ? $plan['original_INR'] : $plan['original_USD']; ?></span>
                            </del></p>
                        <?php endif; ?>
                        <ul class="list-unstyled mt-3 mb-4">
                            <?php foreach ($plan['features'] as $feature => $included): ?>
                                <li>
                                    <?php if ($included === true): ?>
                                        <i class="fas fa-check text-success me-2"></i>
                                    <?php elseif ($included === false): ?>
                                        <i class="fas fa-times text-danger me-2"></i>
                                    <?php else: ?>
                                        <?php echo htmlspecialchars($included); ?>
                                    <?php endif; ?>
                                    <?php echo htmlspecialchars($feature); ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <button type="button" class="btn btn-lg btn-block btn-outline-primary">Click To WhatsApp</button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Add-on Services -->
    <h2 class="text-center mt-5 mb-4">Add-on Services</h2>
     <p class="text-center mb-5">Pricing for 365 Lead Management CRM That is Affordable for Anyone.</p>

    <div class="row justify-content-center">
        <?php foreach ($addon_services as $service_name => $service): ?>
            <div class="col-md-4 col-sm-6 mb-4">
                <div class="card h-100">
                    <div class="card-body text-center">
                        <h5 class="card-title"><?php echo htmlspecialchars($service_name); ?></h5>
                         <h1 class="card-title pricing-card-title">
                            <span class="currency-symbol"><?php echo $current_currency === 'INR' ? '₹' : '$'; ?></span>
                            <span class="price-value" data-inr="<?php echo $service['INR']; ?>" data-usd="<?php echo $service['USD']; ?>"><?php echo $current_currency === 'INR' ? $service['INR'] : $service['USD']; ?></span>
                            <small class="text-muted"><?php echo htmlspecialchars($service['unit']); ?></small>
                        </h1>
                        <a href="#" class="btn btn-primary mt-3">Buy now</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Note -->
    <div class="alert alert-info mt-4" role="alert">
        <strong>Note:</strong> If any 3rd party integration is down due to maintenance on their side, <?php echo SITE_NAME; ?> is not responsible for the service. This includes any add-on packages.
    </div>

</div>

<?php include '../includes/footer.php'; ?>

<script>
    function setCurrency(currency) {
        document.cookie = "currency=" + currency + "; path=/";
        location.reload();
    }

    // Optional: Update prices dynamically without full reload (requires more advanced JS)
    // document.addEventListener('DOMContentLoaded', function() {
    //     const currencyToggle = document.querySelectorAll('.btn-group .btn');
    //     currencyToggle.forEach(button => {
    //         button.addEventListener('click', function() {
    //             const selectedCurrency = this.innerText;
    //             const priceElements = document.querySelectorAll('.price-value');
    //             priceElements.forEach(priceElement => {
    //                 const inrPrice = priceElement.getAttribute('data-inr');
    //                 const usdPrice = priceElement.getAttribute('data-usd');
    //                 if (selectedCurrency === 'INR') {
    //                     priceElement.innerText = inrPrice;
    //                     priceElement.previousElementSibling.innerText = '₹';
    //                 } else {
    //                     priceElement.innerText = usdPrice;
    //                     priceElement.previousElementSibling.innerText = '$';
    //                 }
    //             });
    //              document.querySelectorAll('.original-price-value').forEach(priceElement => {
    //                 const inrPrice = priceElement.getAttribute('data-inr');
    //                 const usdPrice = priceElement.getAttribute('data-usd');
    //                 if (selectedCurrency === 'INR') {
    //                     priceElement.innerText = inrPrice;
    //                     priceElement.previousElementSibling.innerText = '₹';
    //                 } else {
    //                     priceElement.innerText = usdPrice;
    //                     priceElement.previousElementSibling.innerText = '$';
    //                 }
    //             });
    //             currencyToggle.forEach(btn => btn.classList.remove('active'));
    //             this.classList.add('active');
    //         });
    //     });
    // });
</script> 