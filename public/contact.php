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
            <li class="breadcrumb-item active" aria-current="page">Contact Us</li>
        </ol>
    </nav>
    
    <!-- Contact Header -->
    <div class="row mb-5">
        <div class="col-12 text-center">
            <h1 class="display-4 fw-bold">Contact Us</h1>
            <p class="lead">We'd love to hear from you. Get in touch with our team.</p>
        </div>
    </div>
    
    <div class="row">
        <!-- Contact Information -->
        <div class="col-md-5 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <h3 class="card-title mb-4">Get In Touch</h3>
                    
                    <div class="d-flex align-items-start mb-4">
                        <div class="bg-primary text-white rounded-circle p-3 me-3">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div>
                            <h5>Our Office</h5>
                            <p class="text-muted mb-0">2047, Silver Business Point, Near VIP Circle,<br>Uttran, Surat - 394105</p>
                        </div>
                    </div>
                    
                    <div class="d-flex align-items-start mb-4">
                        <div class="bg-primary text-white rounded-circle p-3 me-3">
                            <i class="fas fa-phone"></i>
                        </div>
                        <div>
                            <h5>Phone</h5>
                            <p class="text-muted mb-0">+91 9913299890<br>+91 9913299865</p>
                        </div>
                    </div>
                    
                    <div class="d-flex align-items-start mb-4">
                        <div class="bg-primary text-white rounded-circle p-3 me-3">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div>
                            <h5>Email</h5>
                            <p class="text-muted mb-0">contact@365leadmanagement.com<br>support@365leadmanagement.com</p>
                        </div>
                    </div>
                    
                    <div class="d-flex align-items-start">
                        <div class="bg-primary text-white rounded-circle p-3 me-3">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div>
                            <h5>Working Hours</h5>
                            <p class="text-muted mb-0">Monday - Friday: 9:00 AM - 6:00 PM<br>Saturday: 9:00 AM - 1:00 PM</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Contact Form -->
        <div class="col-md-7 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <h3 class="card-title mb-4">Send Us a Message</h3>
                    
                    <form id="contactForm">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Your Name</label>
                                <input type="text" class="form-control" id="name" placeholder="Enter your name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="email" placeholder="Enter your email" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" id="phone" placeholder="Enter your phone number">
                        </div>
                        
                        <div class="mb-3">
                            <label for="subject" class="form-label">Subject</label>
                            <input type="text" class="form-control" id="subject" placeholder="Enter subject">
                        </div>
                        
                        <div class="mb-4">
                            <label for="message" class="form-label">Message</label>
                            <textarea class="form-control" id="message" rows="5" placeholder="Enter your message" required></textarea>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary py-3">
                                <i class="fas fa-paper-plane me-2"></i>Send Message
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Google Map -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-0">
                    <div class="ratio ratio-21x9">
                        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3720.2978132333707!2d72.8361!3d21.1924!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zMjHCsDExJzMyLjYiTiA3MsKwNTAnMTAuMCJF!5e0!3m2!1sen!2sin!4v1622018926548!5m2!1sen!2sin" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

<!-- Form validation script -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('contactForm');
        
        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Simple form validation
                const name = document.getElementById('name').value;
                const email = document.getElementById('email').value;
                const message = document.getElementById('message').value;
                
                if (name && email && message) {
                    // Show success message (in a real application, you would send the form data to the server)
                    const formControls = form.querySelectorAll('input, textarea, button');
                    formControls.forEach(control => control.disabled = true);
                    
                    // Create success alert
                    const successAlert = document.createElement('div');
                    successAlert.className = 'alert alert-success mt-3';
                    successAlert.innerHTML = '<i class="fas fa-check-circle me-2"></i>Thank you for your message! We\'ll get back to you soon.';
                    form.appendChild(successAlert);
                    
                    // Reset form after 3 seconds
                    setTimeout(() => {
                        form.reset();
                        successAlert.remove();
                        formControls.forEach(control => control.disabled = false);
                    }, 3000);
                }
            });
        }
    });
</script>

</body>
</html>
