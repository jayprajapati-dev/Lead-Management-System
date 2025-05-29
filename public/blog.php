<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../includes/config.php';
include '../includes/public-head.php';
?>
<body class="professional-theme">

<?php include '../includes/navbar.php'; ?>

<div class="container mt-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="d-none d-md-block mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>/public/index.php">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Blog</li>
        </ol>
    </nav>
    
    <!-- Blog Header -->
    <div class="row mb-5">
        <div class="col-12 text-center">
            <h1 class="display-4 fw-bold">Our Blog</h1>
            <p class="lead">Latest insights, tips, and updates from our team</p>
        </div>
    </div>
    
    <!-- Blog Posts -->
    <div class="row">
        <!-- Blog Post 1 -->
        <div class="col-md-4 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <img src="https://via.placeholder.com/800x450" class="card-img-top" alt="Blog post image">
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="badge bg-light text-primary">Lead Management</span>
                        <small class="text-muted">May 25, 2025</small>
                    </div>
                    <h5 class="card-title">7 Ways to Improve Your Lead Conversion Rate</h5>
                    <p class="card-text">Learn the proven strategies that can help you convert more leads into paying customers. Discover how to optimize your sales funnel and increase your ROI.</p>
                </div>
                <div class="card-footer bg-white border-0">
                    <a href="#" class="btn btn-sm btn-outline-primary">Read More <i class="fas fa-arrow-right ms-1"></i></a>
                </div>
            </div>
        </div>
        
        <!-- Blog Post 2 -->
        <div class="col-md-4 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <img src="https://via.placeholder.com/800x450" class="card-img-top" alt="Blog post image">
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="badge bg-light text-primary">CRM Tips</span>
                        <small class="text-muted">May 18, 2025</small>
                    </div>
                    <h5 class="card-title">The Ultimate Guide to CRM Implementation</h5>
                    <p class="card-text">A step-by-step guide to successfully implementing a CRM system in your business. Learn how to avoid common pitfalls and maximize your ROI.</p>
                </div>
                <div class="card-footer bg-white border-0">
                    <a href="#" class="btn btn-sm btn-outline-primary">Read More <i class="fas fa-arrow-right ms-1"></i></a>
                </div>
            </div>
        </div>
        
        <!-- Blog Post 3 -->
        <div class="col-md-4 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <img src="https://via.placeholder.com/800x450" class="card-img-top" alt="Blog post image">
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="badge bg-light text-primary">Sales Strategy</span>
                        <small class="text-muted">May 10, 2025</small>
                    </div>
                    <h5 class="card-title">5 Sales Automation Tools Every Team Needs</h5>
                    <p class="card-text">Discover the essential automation tools that can help your sales team work more efficiently and close more deals in less time.</p>
                </div>
                <div class="card-footer bg-white border-0">
                    <a href="#" class="btn btn-sm btn-outline-primary">Read More <i class="fas fa-arrow-right ms-1"></i></a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- More Blog Posts Row -->
    <div class="row mt-4">
        <!-- Blog Post 4 -->
        <div class="col-md-4 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <img src="https://via.placeholder.com/800x450" class="card-img-top" alt="Blog post image">
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="badge bg-light text-primary">Customer Success</span>
                        <small class="text-muted">May 5, 2025</small>
                    </div>
                    <h5 class="card-title">Building Strong Customer Relationships in the Digital Age</h5>
                    <p class="card-text">Learn how to maintain personal connections with your customers even when most interactions happen online.</p>
                </div>
                <div class="card-footer bg-white border-0">
                    <a href="#" class="btn btn-sm btn-outline-primary">Read More <i class="fas fa-arrow-right ms-1"></i></a>
                </div>
            </div>
        </div>
        
        <!-- Blog Post 5 -->
        <div class="col-md-4 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <img src="https://via.placeholder.com/800x450" class="card-img-top" alt="Blog post image">
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="badge bg-light text-primary">Industry Trends</span>
                        <small class="text-muted">April 28, 2025</small>
                    </div>
                    <h5 class="card-title">The Future of CRM: AI and Machine Learning</h5>
                    <p class="card-text">Explore how artificial intelligence and machine learning are transforming the CRM landscape and what it means for your business.</p>
                </div>
                <div class="card-footer bg-white border-0">
                    <a href="#" class="btn btn-sm btn-outline-primary">Read More <i class="fas fa-arrow-right ms-1"></i></a>
                </div>
            </div>
        </div>
        
        <!-- Blog Post 6 -->
        <div class="col-md-4 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <img src="https://via.placeholder.com/800x450" class="card-img-top" alt="Blog post image">
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="badge bg-light text-primary">Lead Generation</span>
                        <small class="text-muted">April 20, 2025</small>
                    </div>
                    <h5 class="card-title">10 Proven Lead Generation Strategies for 2025</h5>
                    <p class="card-text">Stay ahead of the competition with these effective lead generation tactics that are working right now.</p>
                </div>
                <div class="card-footer bg-white border-0">
                    <a href="#" class="btn btn-sm btn-outline-primary">Read More <i class="fas fa-arrow-right ms-1"></i></a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Pagination -->
    <div class="row mt-4 mb-5">
        <div class="col-12">
            <nav aria-label="Blog pagination">
                <ul class="pagination justify-content-center">
                    <li class="page-item disabled">
                        <a class="page-link" href="#" tabindex="-1" aria-disabled="true">Previous</a>
                    </li>
                    <li class="page-item active"><a class="page-link" href="#">1</a></li>
                    <li class="page-item"><a class="page-link" href="#">2</a></li>
                    <li class="page-item"><a class="page-link" href="#">3</a></li>
                    <li class="page-item">
                        <a class="page-link" href="#">Next</a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

</body>
</html>
