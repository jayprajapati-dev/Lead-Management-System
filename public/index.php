<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../includes/config.php';
include '../includes/public-head.php';
?>
<body class="professional-theme">
<!-- Error handling script -->
<script>
    // This will help catch any JavaScript errors
    window.onerror = function(message, source, lineno, colno, error) {
        console.error('JavaScript Error:', message, 'at', source, lineno, colno);
        // Create visible error message for debugging
        if (!document.getElementById('js-error-message')) {
            var errorDiv = document.createElement('div');
            errorDiv.id = 'js-error-message';
            errorDiv.style.position = 'fixed';
            errorDiv.style.top = '0';
            errorDiv.style.left = '0';
            errorDiv.style.right = '0';
            errorDiv.style.padding = '10px';
            errorDiv.style.background = 'rgba(255,0,0,0.8)';
            errorDiv.style.color = 'white';
            errorDiv.style.zIndex = '9999';
            errorDiv.innerHTML = 'JavaScript Error: ' + message;
            document.body.appendChild(errorDiv);
        }
        return false;
    };
</script>

<?php include '../includes/navbar.php'; ?>

<div class="container mt-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="d-none d-md-block mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item active" aria-current="page">Home</li>
        </ol>
    </nav>
    <!-- Hero Section -->
    <div class="row align-items-center hero-section">
        <div class="col-12 col-md-6 text-center text-md-start animate-on-scroll">
            <span class="badge bg-primary-light text-primary mb-3 px-3 py-2">The #1 Lead Management Solution</span>
            <h1>The Easy and Effective 365 Lead Management CRM for Closing Deals</h1>
            <p class="lead mb-4">
                Supercharge your sales with instant lead alerts, lead history tracking, easy follow-ups, and seamless lead management - all from your phone, within seconds.
            </p>
            <div class="d-grid gap-2 d-sm-flex justify-content-sm-center justify-content-md-start mb-4">
                <a href="#" class="btn btn-primary btn-lg px-4 me-sm-3"><i class="fas fa-rocket me-2"></i>Explore now</a>
                <a href="<?php echo SITE_URL; ?>/register.php" class="btn btn-outline-primary btn-lg px-4"><i class="fas fa-user-plus me-2"></i>Free Trial</a>
            </div>
            <div class="d-flex align-items-center justify-content-center justify-content-md-start mt-4 text-muted">
                <div class="me-4">
                    <i class="fas fa-check-circle text-success me-2"></i>No credit card required
                </div>
                <div>
                    <i class="fas fa-check-circle text-success me-2"></i>14-day free trial
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6 mt-5 mt-md-0 text-center animate-on-scroll">
            <!-- Placeholder for a relevant image or illustration -->
            <img src="<?php echo SITE_URL; ?>/public/assets/images/400x400-0bypv1mMjd.jpeg" alt="Lead Management CRM" class="img-fluid rounded shadow-sm">
        </div>
    </div>

    <!-- Why Choose Section -->
    <div class="row mt-5 py-5 bg-light rounded why-choose-section">
        <div class="col-12 text-center mb-4 animate-on-scroll">
            <span class="badge bg-primary-light text-primary mb-3 px-3 py-2">Benefits</span>
            <h2>Why Choose <?php echo SITE_NAME; ?>?</h2>
        </div>
        <div class="col-12 col-md-10 mx-auto text-center animate-on-scroll">
            <p>
                <?php echo SITE_NAME; ?> offers a comprehensive solution to enhance your sales processes by eliminating lead leakage, ensuring faster response times, and boosting task efficiency. It centralizes lead tracking and automates follow-ups, simplifying lead management and reducing missed opportunities.
            </p>
            <p class="mb-4">
                <?php echo SITE_NAME; ?> accelerates funnel movement by streamlining lead nurturing and ensuring timely actions, such as automated reminders and task assignments. Its robust lead management system provides real-time analytics and actionable insights, helping sales teams prioritize leads, improve conversion rates, and optimize workflows. By offering the best lead management software CRM with seamless automation and insightful tools, <?php echo SITE_NAME; ?> drives productivity and overall sales performance.
            </p>
        </div>
        <div class="col-12 mt-5">
            <div class="row text-center justify-content-center">
                <div class="col-12 col-sm-6 col-md-3 mb-4 animate-on-scroll">
                    <div class="stat-card p-4 rounded bg-white shadow-sm">
                        <div class="stat-icon mb-3 text-primary">
                            <i class="fas fa-chart-line fa-2x"></i>
                        </div>
                        <h3>&lt;0.1%</h3>
                        <p class="text-muted">Lead leakage</p>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-md-3 mb-4 animate-on-scroll">
                    <div class="stat-card p-4 rounded bg-white shadow-sm">
                        <div class="stat-icon mb-3 text-primary">
                            <i class="fas fa-bolt fa-2x"></i>
                        </div>
                        <h3>61%</h3>
                        <p class="text-muted">Faster lead response</p>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-md-3 mb-4 animate-on-scroll">
                    <div class="stat-card p-4 rounded bg-white shadow-sm">
                        <div class="stat-icon mb-3 text-primary">
                            <i class="fas fa-tasks fa-2x"></i>
                        </div>
                        <h3>2x</h3>
                        <p class="text-muted">Task efficiency</p>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-md-3 mb-4 animate-on-scroll">
                    <div class="stat-card p-4 rounded bg-white shadow-sm">
                        <div class="stat-icon mb-3 text-primary">
                            <i class="fas fa-tachometer-alt fa-2x"></i>
                        </div>
                        <h3>70%</h3>
                        <p class="text-muted">Faster funnel movement</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Frequently Asked Questions Section -->
    <div class="row mt-5 py-5 faq-section bg-white shadow-sm rounded">
        <div class="col-12 text-center mb-4 animate-on-scroll">
            <span class="badge bg-primary-light text-primary mb-3 px-3 py-2">Support</span>
            <h2>Frequently Asked Questions</h2>
        </div>
        <div class="col-md-8 mx-auto animate-on-scroll">
            <!-- FAQ Accordion -->
            <div class="accordion" id="faqAccordion">
                <div class="accordion-item border-0 mb-3 shadow-sm">
                    <h2 class="accordion-header" id="headingOne">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                            What is <?php echo SITE_NAME; ?> and how does it work?
                        </button>
                    </h2>
                    <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            <?php echo SITE_NAME; ?> is a comprehensive lead management CRM designed to streamline your sales process. It works by centralizing lead data, automating follow-ups, and providing real-time analytics to help you close deals faster and more efficiently.
                        </div>
                    </div>
                </div>
                <div class="accordion-item border-0 mb-3 shadow-sm">
                    <h2 class="accordion-header" id="headingTwo">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                            How much does <?php echo SITE_NAME; ?> cost?
                        </button>
                    </h2>
                    <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            We offer flexible pricing plans to suit businesses of all sizes. Our plans start at $19/month for the Basic plan, with more advanced features available in our Professional and Enterprise plans. Visit our pricing page for detailed information.
                        </div>
                    </div>
                </div>
                <div class="accordion-item border-0 shadow-sm">
                    <h2 class="accordion-header" id="headingThree">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                            Can I integrate <?php echo SITE_NAME; ?> with my existing tools?
                        </button>
                    </h2>
                    <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            Yes! <?php echo SITE_NAME; ?> integrates seamlessly with popular tools like Facebook Lead Ads, Google Forms, WordPress, and many more. Our API also allows for custom integrations with your existing systems.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Our Blog Section -->
    <div class="row mt-5 mb-5 py-5 blog-section bg-white shadow-sm rounded">
        <div class="col-12 text-center mb-4 animate-on-scroll">
            <span class="badge bg-primary-light text-primary mb-3 px-3 py-2">Latest Articles</span>
            <h2>Our Blog</h2>
        </div>
        <div class="col-12 mx-auto">
            <!-- Blog posts preview -->
            <div class="row">
                <div class="col-md-4 mb-4 animate-on-scroll">
                    <div class="card border-0 shadow-sm h-100">
                        <img src="https://via.placeholder.com/350x200" class="card-img-top" alt="Blog post image">
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="badge bg-light text-primary">Lead Management</span>
                                <small class="text-muted">May 25, 2025</small>
                            </div>
                            <h5 class="card-title">7 Ways to Improve Your Lead Conversion Rate</h5>
                            <p class="card-text">Learn the proven strategies that can help you convert more leads into paying customers.</p>
                        </div>
                        <div class="card-footer bg-white border-0">
                            <a href="#" class="btn btn-sm btn-outline-primary">Read More <i class="fas fa-arrow-right ms-1"></i></a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4 animate-on-scroll">
                    <div class="card border-0 shadow-sm h-100">
                        <img src="https://via.placeholder.com/350x200" class="card-img-top" alt="Blog post image">
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="badge bg-light text-primary">CRM Tips</span>
                                <small class="text-muted">May 18, 2025</small>
                            </div>
                            <h5 class="card-title">The Ultimate Guide to CRM Implementation</h5>
                            <p class="card-text">A step-by-step guide to successfully implementing a CRM system in your business.</p>
                        </div>
                        <div class="card-footer bg-white border-0">
                            <a href="#" class="btn btn-sm btn-outline-primary">Read More <i class="fas fa-arrow-right ms-1"></i></a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4 animate-on-scroll">
                    <div class="card border-0 shadow-sm h-100">
                        <img src="https://via.placeholder.com/350x200" class="card-img-top" alt="Blog post image">
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="badge bg-light text-primary">Sales Strategy</span>
                                <small class="text-muted">May 10, 2025</small>
                            </div>
                            <h5 class="card-title">5 Sales Automation Tools Every Team Needs</h5>
                            <p class="card-text">Discover the essential automation tools that can help your sales team work more efficiently.</p>
                        </div>
                        <div class="card-footer bg-white border-0">
                            <a href="#" class="btn btn-sm btn-outline-primary">Read More <i class="fas fa-arrow-right ms-1"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<?php include '../includes/footer.php'; ?>
</body>