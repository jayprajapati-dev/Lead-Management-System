<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}

require_once '../includes/config.php';

$error = '';
$success = '';
$formData = [
    'first_name' => '',
    'last_name' => '',
    'email' => '',
    'phone' => '',
    'timezone' => '(GMT+05:30) Kolkata',
    'company_name' => '',
    'gst_number' => '',
    'package' => '',
    'terms' => false,
    'is_trial' => false // Ensure is_trial is always initialized
];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get and sanitize form data
    $formData = [
        'first_name' => trim($_POST['first_name'] ?? ''),
        'last_name' => trim($_POST['last_name'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'phone' => trim($_POST['phone'] ?? ''),
        'password' => $_POST['password'] ?? '',
        'confirm_password' => $_POST['confirm_password'] ?? '',
        'timezone' => $_POST['timezone'] ?? '(GMT+05:30) Kolkata',
        'company_name' => trim($_POST['company_name'] ?? ''),
        'gst_number' => trim($_POST['gst_number'] ?? ''),
        'package' => $_POST['package'] ?? '',
        'terms' => isset($_POST['terms']),
        'is_trial' => ($_POST['action'] ?? '') === 'try_for_free'
    ];
    
    // Different validation for trial vs paid registration
    if ($formData['is_trial']) {
        // For trial, only validate essential fields
        if (empty($formData['first_name']) || empty($formData['email']) || empty($formData['password']) || empty($formData['confirm_password'])) {
            $error = 'Please fill in all required fields for trial registration (First Name, Email, and Password).';
        } elseif (!filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address.';
        } elseif (strlen($formData['password']) < 8) {
            $error = 'Password must be at least 8 characters long.';
        } elseif ($formData['password'] !== $formData['confirm_password']) {
            $error = 'Passwords do not match.';
        } elseif (!$formData['terms']) {
            $error = 'You must agree to the Privacy Policy and Terms.';
        }
    } else {
        // For paid registration, validate all required fields
        if (empty($formData['first_name']) || empty($formData['last_name']) || empty($formData['email']) || 
            empty($formData['phone']) || empty($formData['password']) || empty($formData['confirm_password']) || 
            empty($formData['package'])) {
            $error = 'Please fill in all required fields for paid registration.';
        } elseif (!filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address.';
        } elseif (strlen($formData['password']) < 8) {
            $error = 'Password must be at least 8 characters long.';
        } elseif ($formData['password'] !== $formData['confirm_password']) {
            $error = 'Passwords do not match.';
        } elseif (!$formData['terms']) {
            $error = 'You must agree to the Privacy Policy and Terms.';
        }
    }

    if (empty($error)) {
        try {
            // Check if email already exists
            $stmt = executeQuery(
                "SELECT id FROM users WHERE email = ?",
                [$formData['email']]
            );
            
            if ($stmt->get_result()->num_rows > 0) {
                $error = 'This email address is already registered.';
            } else {
                // Set trial period end date if it's a trial registration
                $trial_end_date = $formData['is_trial'] ? date('Y-m-d H:i:s', strtotime('+7 days')) : null;
                $status = $formData['is_trial'] ? 'trial' : 'active';
                
                // Create new user
                $hashed_password = password_hash($formData['password'], PASSWORD_DEFAULT);
                
                $insert_stmt = executeQuery(
                    "INSERT INTO users (first_name, last_name, email, phone, password, timezone, company_name, gst_number, package, status, trial_end_date, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())",
                    [
                        $formData['first_name'],
                        $formData['last_name'],
                        $formData['email'],
                        $formData['phone'],
                        $hashed_password,
                        $formData['timezone'],
                        $formData['company_name'],
                        $formData['gst_number'],
                        $formData['is_trial'] ? 'basic' : $formData['package'],
                        $status,
                        $trial_end_date
                    ]
                );
                
                if ($insert_stmt->affected_rows > 0) {
                    if ($formData['is_trial']) {
                        $success = 'Trial registration successful! You have 7 days of free access. After the trial period, you will need to select and pay for a package to continue using the service.';
                    } else {
                        $success = 'Registration successful! You have registered for the ' . ucfirst($formData['package']) . ' package.';
                    }
                    
                    // Clear form data after successful registration, retain is_trial
                    $formData = [
                        'first_name' => '',
                        'last_name' => '',
                        'email' => '',
                        'phone' => '',
                        'timezone' => '(GMT+05:30) Kolkata',
                        'company_name' => '',
                        'gst_number' => '',
                        'package' => '',
                        'terms' => false,
                        'is_trial' => $formData['is_trial'] // Retain the is_trial value
                    ];
                } else {
                    $error = 'Failed to create account. Please try again.';
                }
            }
        } catch (Exception $e) {
            $error = 'An error occurred. Please try again later.';
            error_log("Registration error: " . $e->getMessage());
        }
    }
}

// Timezones for dropdown
$timezones = [
    '(GMT-12:00) International Date Line West',
    '(GMT-11:00) Midway Island, Samoa',
    '(GMT-10:00) Hawaii',
    '(GMT-09:00) Alaska',
    '(GMT-08:00) Pacific Time (US & Canada)',
    '(GMT-07:00) Mountain Time (US & Canada)',
    '(GMT-06:00) Central Time (US & Canada), Mexico City',
    '(GMT-05:00) Eastern Time (US & Canada), Bogota, Lima',
    '(GMT-04:00) Atlantic Time (Canada), Caracas, La Paz',
    '(GMT-03:30) Newfoundland',
    '(GMT-03:00) Brasilia, Buenos Aires, Georgetown',
    '(GMT-02:00) Mid-Atlantic',
    '(GMT-01:00) Azores, Cape Verde Islands',
    '(GMT+00:00) Western Europe Time, London, Lisbon, Casablanca',
    '(GMT+01:00) Brussels, Copenhagen, Madrid, Paris',
    '(GMT+02:00) Kaliningrad, South Africa',
    '(GMT+03:00) Baghdad, Riyadh, Moscow, St. Petersburg',
    '(GMT+03:30) Tehran',
    '(GMT+04:00) Abu Dhabi, Muscat, Baku, Tbilisi',
    '(GMT+04:30) Kabul',
    '(GMT+05:00) Ekaterinburg, Islamabad, Karachi, Tashkent',
    '(GMT+05:30) Chennai, Kolkata, Mumbai, New Delhi',
    '(GMT+05:45) Kathmandu',
    '(GMT+06:00) Almaty, Dhaka, Colombo',
    '(GMT+06:30) Yangon (Rangoon)',
    '(GMT+07:00) Bangkok, Hanoi, Jakarta',
    '(GMT+08:00) Beijing, Perth, Singapore, Hong Kong',
    '(GMT+08:45) Eucla',
    '(GMT+09:00) Tokyo, Seoul, Osaka, Sapporo, Yakutsk',
    '(GMT+09:30) Adelaide, Darwin',
    '(GMT+10:00) Eastern Australia, Guam, Vladivostok',
    '(GMT+10:30) Lord Howe Island',
    '(GMT+11:00) Magadan, Solomon Islands, New Caledonia',
    '(GMT+11:30) Norfolk Island',
    '(GMT+12:00) Auckland, Wellington, Fiji, Kamchatka',
    '(GMT+12:45) Chatham Islands',
    '(GMT+13:00) Apia, Nuku\'alofa',
    '(GMT+14:00) Line Islands, Tokelau'
];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration - <?php echo SITE_NAME; ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="css/style.css" rel="stylesheet">
    <link href="assets/css/login.css" rel="stylesheet">
    <link href="assets/css/register.css" rel="stylesheet">
</head>
<body class="professional-theme">
    <div class="register-wrapper">
        <div class="register-container shadow-lg">
            <div class="register-header text-center mb-4">
                <a href="index.php" class="logo-link">
                    <h1 class="site-name"><?php echo SITE_NAME; ?></h1>
                </a>
                <h2 class="fw-bold">Create Your Account</h2>
                <p class="text-muted">Get started with our lead management solution</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?php echo htmlspecialchars($error); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    <?php echo htmlspecialchars($success); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="needs-validation" novalidate>
                <input type="hidden" name="selected_package" id="selected_package" value="<?php echo htmlspecialchars($formData['package']); ?>">
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="first_name" class="form-label fw-medium">First Name</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="fas fa-user text-primary"></i>
                            </span>
                            <input type="text" class="form-control border-start-0" id="first_name" name="first_name" value="<?php echo htmlspecialchars($formData['first_name']); ?>" placeholder="Enter first name" required>
                        </div>
                        <div class="invalid-feedback">Please enter your first name.</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="last_name" class="form-label fw-medium">Last Name</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="fas fa-user text-primary"></i>
                            </span>
                            <input type="text" class="form-control border-start-0" id="last_name" name="last_name" value="<?php echo htmlspecialchars($formData['last_name']); ?>" placeholder="Enter last name" required>
                        </div>
                        <div class="invalid-feedback">Please enter your last name.</div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="email" class="form-label fw-medium">Email Address</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="fas fa-envelope text-primary"></i>
                        </span>
                        <input type="email" class="form-control border-start-0" id="email" name="email" value="<?php echo htmlspecialchars($formData['email']); ?>" placeholder="Enter email address" required>
                    </div>
                    <div class="invalid-feedback">Please enter a valid email address.</div>
                </div>
                
                <div class="mb-3">
                    <label for="phone" class="form-label fw-medium">Phone Number</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="fas fa-phone text-primary"></i>
                        </span>
                        <input type="tel" class="form-control border-start-0" id="phone" name="phone" value="<?php echo htmlspecialchars($formData['phone']); ?>" placeholder="Enter phone number" required>
                    </div>
                    <div class="invalid-feedback">Please enter your phone number.</div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="password" class="form-label fw-medium">Password</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="fas fa-lock text-primary"></i>
                            </span>
                            <input type="password" class="form-control border-start-0" id="password" name="password" placeholder="Enter password (min. 8 chars)" required>
                            <span class="toggle-password" onclick="togglePassword('password')" title="Toggle password visibility">
                                <i class="fas fa-eye"></i>
                            </span>
                        </div>
                        <div class="invalid-feedback">Please enter a password (min. 8 characters).</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="confirm_password" class="form-label fw-medium">Confirm Password</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="fas fa-lock text-primary"></i>
                            </span>
                            <input type="password" class="form-control border-start-0" id="confirm_password" name="confirm_password" placeholder="Confirm your password" required>
                            <span class="toggle-password" onclick="togglePassword('confirm_password')" title="Toggle password visibility">
                                <i class="fas fa-eye"></i>
                            </span>
                        </div>
                        <div class="invalid-feedback">Please confirm your password.</div>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="timezone" class="form-label fw-medium">Timezone</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="fas fa-globe text-primary"></i>
                        </span>
                        <select class="form-select border-start-0" id="timezone" name="timezone">
                            <?php foreach ($timezones as $timezone): ?>
                                <option value="<?php echo htmlspecialchars($timezone); ?>" <?php echo $formData['timezone'] === $timezone ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($timezone); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="company_name" class="form-label fw-medium">Company Name</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="fas fa-building text-primary"></i>
                        </span>
                        <input type="text" class="form-control border-start-0" id="company_name" name="company_name" value="<?php echo htmlspecialchars($formData['company_name']); ?>" placeholder="Enter company name">
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="gst_number" class="form-label fw-medium">GST Number (Optional)</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="fas fa-receipt text-primary"></i>
                        </span>
                        <input type="text" class="form-control border-start-0" id="gst_number" name="gst_number" value="<?php echo htmlspecialchars($formData['gst_number']); ?>" placeholder="Enter GST number if applicable">
                    </div>
                </div>
                
                <div class="plan-section mt-5 mb-4">
                    <div class="section-header text-center mb-4">
                        <span class="badge bg-primary mb-2">SUBSCRIPTION</span>
                        <h3 class="fw-bold">Choose Your Plan</h3>
                        <p class="text-muted">Select the plan that best fits your business needs</p>
                    </div>
                    
                    <div class="row mb-4">
                        <!-- Basic Package -->
                        <div class="col-md-4 mb-3">
                            <div class="package-card" data-package-id="basic">
                                <div class="package-badge">BASIC</div>
                                <div class="package-header">
                                    <h4>Starter</h4>
                                    <div class="price">₹999<span>/month</span></div>
                                </div>
                                <div class="package-features">
                                    <ul>
                                        <li><i class="fas fa-check-circle text-success me-2"></i>Up to 500 Leads</li>
                                        <li><i class="fas fa-check-circle text-success me-2"></i>Email Support</li>
                                        <li><i class="fas fa-check-circle text-success me-2"></i>Basic Analytics</li>
                                        <li><i class="fas fa-check-circle text-success me-2"></i>1 User Account</li>
                                    </ul>
                                </div>
                                <div class="package-footer">
                                    <span class="select-plan-text">Select Plan</span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Standard Package -->
                        <div class="col-md-4 mb-3">
                            <div class="package-card popular" data-package-id="standard">
                                <div class="package-badge">POPULAR</div>
                                <div class="package-header">
                                    <h4>Standard</h4>
                                    <div class="price">₹1999<span>/month</span></div>
                                </div>
                                <div class="package-features">
                                    <ul>
                                        <li><i class="fas fa-check-circle text-success me-2"></i>Up to 2000 Leads</li>
                                        <li><i class="fas fa-check-circle text-success me-2"></i>Email & Phone Support</li>
                                        <li><i class="fas fa-check-circle text-success me-2"></i>Advanced Analytics</li>
                                        <li><i class="fas fa-check-circle text-success me-2"></i>5 User Accounts</li>
                                        <li><i class="fas fa-check-circle text-success me-2"></i>Custom Fields</li>
                                    </ul>
                                </div>
                                <div class="package-footer">
                                    <span class="select-plan-text">Select Plan</span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Premium Package -->
                        <div class="col-md-4 mb-3">
                            <div class="package-card" data-package-id="premium">
                                <div class="package-badge">PREMIUM</div>
                                <div class="package-header">
                                    <h4>Enterprise</h4>
                                    <div class="price">₹3999<span>/month</span></div>
                                </div>
                                <div class="package-features">
                                    <ul>
                                        <li><i class="fas fa-check-circle text-success me-2"></i>Unlimited Leads</li>
                                        <li><i class="fas fa-check-circle text-success me-2"></i>Priority Support</li>
                                        <li><i class="fas fa-check-circle text-success me-2"></i>Advanced Analytics & Reports</li>
                                        <li><i class="fas fa-check-circle text-success me-2"></i>Unlimited User Accounts</li>
                                        <li><i class="fas fa-check-circle text-success me-2"></i>Custom Fields & Workflows</li>
                                        <li><i class="fas fa-check-circle text-success me-2"></i>API Access</li>
                                    </ul>
                                </div>
                                <div class="package-footer">
                                    <span class="select-plan-text">Select Plan</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="mb-4 form-check">
                    <input type="checkbox" class="form-check-input" id="terms" name="terms" <?php echo $formData['terms'] ? 'checked' : ''; ?> required>
                    <label class="form-check-label" for="terms">
                        I agree to the <a href="privacy.php" target="_blank" class="text-primary">Privacy Policy</a>
                    </label>
                    <div class="invalid-feedback">You must agree to the privacy policy.</div>
                </div>
                
                <div class="d-grid gap-3 d-md-flex justify-content-md-center mt-4">
                    <button type="submit" name="action" value="try_for_free" class="btn btn-outline-primary btn-lg px-4" id="try_for_free_btn">
                        <i class="fas fa-rocket me-2"></i>Try For Free
                    </button>
                    <button type="submit" name="action" value="pay_now" class="btn btn-primary btn-lg px-4" id="pay_now_btn">
                        <i class="fas fa-credit-card me-2"></i>Pay Now
                    </button>
                </div>
                
            </form>
            
            <div class="text-center mt-4">
                <p class="mb-2">Already have an account?</p>
                <a href="login.php" class="btn btn-outline-secondary">
                    <i class="fas fa-sign-in-alt me-2"></i>Sign In
                </a>
            </div>
            
            <div class="text-center mt-4">
                <a href="index.php" class="btn btn-link text-muted">
                    <i class="fas fa-arrow-left me-1"></i> Back to Home
                </a>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Custom Script -->
    <script>
        // Password Toggle Script
        function togglePassword(inputId) {
            const passwordInput = document.getElementById(inputId);
            const toggleIcon = passwordInput.parentElement.querySelector('.toggle-password i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }
        
        // Form validation
        (function () {
            'use strict';
            var forms = document.querySelectorAll('.needs-validation');
            Array.prototype.slice.call(forms).forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    // Get the button that was clicked
                    const clickedButton = document.activeElement;
                    const isPayNow = clickedButton && clickedButton.value === 'pay_now';

                    // Only check package selection for Pay Now button
                    if (isPayNow) {
                        const selectedPackage = document.getElementById('selected_package').value;
                        if (!selectedPackage) {
                            alert('Please select a pricing package to proceed with payment.');
                            event.preventDefault();
                            event.stopPropagation();
                            return;
                        }
                    } else {
                        // For Try For Free, set the package to 'basic' automatically
                        document.getElementById('selected_package').value = 'basic';
                    }

                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        })();

        // Package Selection Logic
        document.addEventListener('DOMContentLoaded', function() {
            const packageCards = document.querySelectorAll('.package-card');
            const selectedPackageInput = document.getElementById('selected_package');
            const tryForFreeBtn = document.getElementById('try_for_free_btn');
            const payNowBtn = document.getElementById('pay_now_btn');

            // Both buttons always visible
            tryForFreeBtn.style.display = 'block';
            payNowBtn.style.display = 'block';

            // Add click handler for Pay Now button
            payNowBtn.addEventListener('click', function() {
                if (!selectedPackageInput.value) {
                    alert('Please select a package to proceed with payment.');
                    return false;
                }
            });

            packageCards.forEach(card => {
                card.addEventListener('click', function() {
                    // Remove 'selected' class from all cards
                    packageCards.forEach(c => c.classList.remove('selected'));
                    // Add 'selected' class to the clicked card
                    this.classList.add('selected');
                    // Set the value of the hidden input
                    const packageId = this.getAttribute('data-package-id');
                    selectedPackageInput.value = packageId;
                });

                // Ensure the correct card is marked as selected on page load if a package is already in formData
                if (selectedPackageInput.value && document.querySelector('[data-package-id="' + selectedPackageInput.value + '"]')) {
                     document.querySelector('[data-package-id="' + selectedPackageInput.value + '"]').classList.add('selected');
                }
            });
        });

    </script>
</body>
</html>