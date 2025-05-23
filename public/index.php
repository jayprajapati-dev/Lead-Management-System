<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../includes/config.php';
include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container mt-5">
    <!-- Hero Section -->
    <div class="row align-items-center">
        <div class="col-12 col-md-6 text-center text-md-start">
            <h1 class="display-4">The Easy and Effective 365 Lead Management CRM for Closing Deals</h1>
            <p class="lead mb-4">
                Supercharge your sales with instant lead alerts, lead history tracking, easy follow-ups, and seamless lead management - all from your phone, within seconds.
            </p>
            <div class="d-grid gap-2 d-sm-flex justify-content-sm-center justify-content-md-start mb-4">
                <a href="#" class="btn btn-primary btn-lg">Explore now</a>
                <a href="<?php echo SITE_URL; ?>/register.php" class="btn btn-outline-primary btn-lg">Free Trial</a>
            </div>
        </div>
        <div class="col-12 col-md-6 mt-4 mt-md-0 text-center">
            <!-- Placeholder for a relevant image or illustration -->
            <img src="<?php echo SITE_URL; ?>/public/assets/images/hero-image.png" alt="Lead Management CRM" class="img-fluid">
        </div>
    </div>

    <!-- Why Choose Section -->
    <div class="row mt-5 py-5 bg-light rounded">
        <div class="col-12 text-center mb-4">
            <h2>Why Choose <?php echo SITE_NAME; ?> ?</h2>
        </div>
        <div class="col-12 text-center">
            <p>
                <?php echo SITE_NAME; ?> offers a comprehensive solution to enhance your sales processes by eliminating lead leakage, ensuring faster response times, and boosting task efficiency. It centralizes lead tracking and automates follow-ups, simplifying lead management and reducing missed opportunities.
            </p>
             <p class="mb-4">
                <?php echo SITE_NAME; ?> accelerates funnel movement by streamlining lead nurturing and ensuring timely actions, such as automated reminders and task assignments. Its robust lead management system provides real-time analytics and actionable insights, helping sales teams prioritize leads, improve conversion rates, and optimize workflows. By offering the best lead management software CRM with seamless automation and insightful tools, <?php echo SITE_NAME; ?> drives productivity and overall sales performance.
            </p>
        </div>
        <div class="col-12 mt-4">
            <div class="row text-center justify-content-center">
                <div class="col-12 col-sm-6 col-md-3 mb-3">
                    <h3>&lt;0.1%</h3>
                    <p class="text-muted">Lead leakage</p>
                </div>
                <div class="col-12 col-sm-6 col-md-3 mb-3">
                    <h3>61%</h3>
                    <p class="text-muted">Faster lead response</p>
                </div>
                <div class="col-12 col-sm-6 col-md-3 mb-3">
                    <h3>2x</h3>
                    <p class="text-muted">Task efficiency</p>
                </div>
                <div class="col-12 col-sm-6 col-md-3 mb-3">
                    <h3>70%</h3>
                    <p class="text-muted">Faster funnel movement</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Frequently Asked Questions Section (Placeholder) -->
    <div class="row mt-5">
        <div class="col-12 text-center mb-4">
            <h2>Frequently Asked Questions</h2>
        </div>
        <div class="col-md-8 mx-auto">
            <!-- FAQ Accordion or list goes here -->
            <p class="text-muted text-center">[ FAQ content will be added here ]</p>
        </div>
    </div>

    <!-- Our Blog Section (Placeholder) -->
    <div class="row mt-5 mb-5">
        <div class="col-12 text-center mb-4">
            <h2>Our Blog</h2>
        </div>
        <div class="col-md-8 mx-auto">
            <!-- Blog posts preview goes here -->
             <p class="text-muted text-center">[ Blog content will be added here ]</p>
        </div>
    </div>

</div>

<?php include '../includes/footer.php'; ?>