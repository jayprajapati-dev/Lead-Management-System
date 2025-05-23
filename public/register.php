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
    <title>Registration For 365 Lead Management</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="assets/css/register.css" rel="stylesheet">
</head>
<body>
    <div class="register-wrapper">
        <div class="register-container">
            <div class="register-header text-center mb-4">
                 <h2>Registration For 365 Lead Management</h2>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="needs-validation" novalidate>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="first_name" class="form-label">First Name: *</label>
                        <input type="text" 
                               class="form-control" 
                               id="first_name" 
                               name="first_name" 
                               value="<?php echo htmlspecialchars($formData['first_name']); ?>"
                               placeholder="Enter First Name"
                               required>
                    </div>
                    
                    <div class="col-md-6">
                        <label for="last_name" class="form-label">Last Name: *</label>
                         <input type="text" 
                               class="form-control" 
                               id="last_name" 
                               name="last_name" 
                               value="<?php echo htmlspecialchars($formData['last_name']); ?>"
                               placeholder="Enter Last Name"
                               required>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="email" class="form-label">Email: *</label>
                    <input type="email" 
                           class="form-control" 
                           id="email" 
                           name="email" 
                           value="<?php echo htmlspecialchars($formData['email']); ?>"
                           placeholder="Enter Email"
                           required>
                </div>
                
                <div class="mb-3">
                    <label for="phone" class="form-label">Phone: *</label>
                     <div class="input-group">
                        <span class="input-group-text" id="basic-addon1">
                            🇮🇳 +91 
                        </span>
                        <input type="text" 
                               class="form-control" 
                               id="phone" 
                               name="phone" 
                               value="<?php echo htmlspecialchars($formData['phone']); ?>"
                               placeholder="Enter Phone Number"
                               aria-label="Phone Number" 
                               aria-describedby="basic-addon1"
                               required>
                    </div>
                </div>
                
                <div class="mb-3 password-toggle">
                    <label for="password" class="form-label">Password: *</label>
                    <input type="password" 
                           class="form-control" 
                           id="password" 
                           name="password" 
                           placeholder="Enter Password"
                           required 
                           minlength="8">
                    <span class="toggle-password" onclick="togglePassword('password')">
                        <i class="fas fa-eye"></i>
                    </span>
                </div>
                
                <div class="mb-3 password-toggle">
                    <label for="confirm_password" class="form-label">Confirm Password: *</label>
                    <input type="password" 
                           class="form-control" 
                           id="confirm_password" 
                           name="confirm_password" 
                           placeholder="Enter Confirm Password"
                           required>
                    <span class="toggle-password" onclick="togglePassword('confirm_password')">
                        <i class="fas fa-eye"></i>
                    </span>
                </div>

                 <div class="mb-3">
                    <label for="timezone" class="form-label">Select TimeZone:</label>
                    <select class="form-select" id="timezone" name="timezone">
                        <?php foreach ($timezones as $tz): ?>
                            <option value="<?php echo htmlspecialchars($tz); ?>" <?php echo $formData['timezone'] === $tz ? 'selected' : ''; ?>><?php echo htmlspecialchars($tz); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="company_name" class="form-label">Company Name: (Optional)</label>
                    <input type="text" 
                           class="form-control" 
                           id="company_name" 
                           name="company_name" 
                           value="<?php echo htmlspecialchars($formData['company_name']); ?>"
                           placeholder="Enter Company Name">
                </div>

                <div class="mb-3">
                    <label for="gst_number" class="form-label">GST Number: (Optional)</label>
                    <input type="text" 
                           class="form-control" 
                           id="gst_number" 
                           name="gst_number" 
                           value="<?php echo htmlspecialchars($formData['gst_number']); ?>"
                           placeholder="Enter GST Number">
                </div>
                
                <div class="coupon-section mb-3">
                     <a href="#couponCode" class="coupon-link" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="couponCode">
                        Apply Coupon Code
                    </a>
                    <div class="collapse coupon-input-container" id="couponCode">
                        <div class="mt-2">
                             <label for="coupon_code" class="form-label">Coupon Code:</label>
                             <input type="text" 
                                    class="form-control" 
                                    id="coupon_code" 
                                    name="coupon_code" 
                                    placeholder="Enter Coupon Code">
                        </div>
                    </div>
                </div>

                <input type="hidden" name="package" id="selected_package" value="<?php echo htmlspecialchars($formData['package']); ?>">

                <div class="pricing-packages row">
                    <div class="col-md-4">
                        <div class="package-card" data-package-id="basic">
                            <h4>Basic</h4>
                            <p class="price">₹365.00</p>
                            <p class="users">Users:- 1</p>
                            <p class="trial">7 Days Trial</p>
                        </div>
                    </div>
                     <div class="col-md-4">
                        <div class="package-card" data-package-id="silver">
                            <h4>Silver</h4>
                            <p class="price">₹1000.00</p>
                            <p class="users">Users:- 2</p>
                            <p class="trial">7 Days Trial</p>
                        </div>
                    </div>
                     <div class="col-md-4">
                        <div class="package-card" data-package-id="gold">
                            <h4>Gold</h4>
                            <p class="price">₹1500.00</p>
                            <p class="users">Users:- 3</p>
                            <p class="trial">7 Days Trial</p>
                        </div>
                    </div>
                     <div class="col-md-4">
                        <div class="package-card" data-package-id="platinum">
                            <h4>Platinum</h4>
                            <p class="price">₹2000.00</p>
                            <p class="users">Users:- 4</p>
                            <p class="trial">7 Days Trial</p>
                        </div>
                    </div>
                     <div class="col-md-4">
                        <div class="package-card" data-package-id="diamond">
                            <h4>Diamond</h4>
                            <p class="price">₹2500.00</p>
                            <p class="users">Users:- 6</p>
                            <p class="trial">7 Days Trial</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="package-card" data-package-id="diamond_pro">
                            <h4>Diamond Pro</h4>
                            <p class="price">₹3950.00</p>
                            <p class="users">Users:- 6</p>
                            <p class="trial">7 Days Trial</p>
                        </div>
                    </div>
                </div>

                <div class="form-check mb-4">
                    <input class="form-check-input" type="checkbox" id="terms" name="terms" <?php echo $formData['terms'] ? 'checked' : ''; ?> required>
                    <label class="form-check-label" for="terms">
                        I agree to the <a href="privacy_policy.php" target="_blank">Privacy Policy and Terms</a>: *
                    </label>
                    <div class="invalid-feedback">
                        You must agree to the Privacy Policy and Terms before registering.
                    </div>
                </div>

                <div class="button-group mb-3 text-center">
                     <button type="submit" class="btn btn-primary me-2" name="action" value="try_for_free" id="try_for_free_btn">Try For Free</button>
                     <button type="submit" class="btn btn-outline-primary" name="action" value="pay_now" id="pay_now_btn">Pay Now</button>
                </div>

                <div class="text-center mt-3">
                    Already have an account? <a href="login.php">Login here</a>
                </div>
            </form>
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