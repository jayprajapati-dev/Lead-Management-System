<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../includes/config.php';
require_once '../includes/trial_functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: " . SITE_URL . "/public/login.php");
    exit;
}

// Get user information from database
$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Set page title
$page_title = "My Profile";

// Create uploads directory if it doesn't exist
$upload_dir = "../uploads/profile_images/";
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// Handle form submission for profile update
$success_message = "";
$error_message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        // Get form data
        $first_name = trim($_POST['first_name'] ?? '');
        $last_name = trim($_POST['last_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $company_name = trim($_POST['company_name'] ?? '');
        $gst_number = trim($_POST['gst_number'] ?? '');
        $timezone = $_POST['timezone'] ?? $user['timezone'];
        
        // Validate data
        if (empty($first_name)) {
            $error_message = "First name is required";
        } else {
            // Update user profile
            $update_sql = "UPDATE users SET first_name = ?, last_name = ?, phone = ?, company_name = ?, gst_number = ?, timezone = ? WHERE id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("ssssssi", $first_name, $last_name, $phone, $company_name, $gst_number, $timezone, $user_id);
            
            if ($update_stmt->execute()) {
                $success_message = "Profile updated successfully";
                
                // Refresh user data
                $stmt->execute();
                $result = $stmt->get_result();
                $user = $result->fetch_assoc();
            } else {
                $error_message = "Failed to update profile: " . $conn->error;
            }
        }
    } elseif (isset($_POST['change_password'])) {
        // Get password data
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        // Validate passwords
        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            $error_message = "All password fields are required";
        } elseif ($new_password !== $confirm_password) {
            $error_message = "New passwords do not match";
        } elseif (strlen($new_password) < 8) {
            $error_message = "New password must be at least 8 characters long";
        } else {
            // Verify current password
            if (password_verify($current_password, $user['password'])) {
                // Hash new password
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                
                // Update password
                $update_sql = "UPDATE users SET password = ? WHERE id = ?";
                $update_stmt = $conn->prepare($update_sql);
                $update_stmt->bind_param("si", $hashed_password, $user_id);
                
                if ($update_stmt->execute()) {
                    $success_message = "Password changed successfully";
                } else {
                    $error_message = "Failed to change password: " . $conn->error;
                }
            } else {
                $error_message = "Current password is incorrect";
            }
        }
    } elseif (isset($_POST['upload_profile_image'])) {
        // Handle profile image upload
        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['profile_image'];
            
            // Validate file type
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $file_type = mime_content_type($file['tmp_name']);
            
            if (!in_array($file_type, $allowed_types)) {
                $error_message = "Invalid file type. Only JPG, PNG, GIF, and WEBP images are allowed.";
            } elseif ($file['size'] > 2 * 1024 * 1024) { // 2MB limit
                $error_message = "File size exceeds the limit of 2MB.";
            } else {
                // Generate a unique filename
                $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                $new_filename = 'profile_' . $user_id . '_' . time() . '.' . $file_extension;
                $target_path = $upload_dir . $new_filename;
                
                // Move the uploaded file
                if (move_uploaded_file($file['tmp_name'], $target_path)) {
                    // Update user profile with new image path
                    // Store the path relative to the site root for consistent access
                    $image_path = 'uploads/profile_images/' . $new_filename;
                    
                    // Make sure the path is stored consistently without leading slash
                    $image_path = ltrim($image_path, '/');
                    
                    $update_sql = "UPDATE users SET profile_image = ?, profile_image_updated_at = NOW() WHERE id = ?";
                    $update_stmt = $conn->prepare($update_sql);
                    $update_stmt->bind_param("si", $image_path, $user_id);
                    
                    if ($update_stmt->execute()) {
                        $success_message = "Profile image updated successfully";
                        
                        // Refresh user data
                        $stmt->execute();
                        $result = $stmt->get_result();
                        $user = $result->fetch_assoc();
                    } else {
                        $error_message = "Failed to update profile image in database: " . $conn->error;
                    }
                } else {
                    $error_message = "Failed to upload image. Please try again.";
                }
            }
        } else {
            $error_message = "No image file uploaded or an error occurred during upload.";
        }
    }
}

// Get user data for display
$userFirstName = htmlspecialchars($user['first_name'] ?? '');
$userLastName = htmlspecialchars($user['last_name'] ?? '');
$userEmail = htmlspecialchars($user['email'] ?? '');
$userPhone = htmlspecialchars($user['phone'] ?? '');
$userGST = htmlspecialchars($user['gst_number'] ?? '');
$userCompany = htmlspecialchars($user['company_name'] ?? '');
$userTimezone = $user['timezone'] ?? '(GMT+05:30) Kolkata';
$userPackage = ucfirst($user['package'] ?? 'basic');
$userStatus = ucfirst($user['status'] ?? 'trial');

// Delete the separate profile update SQL file since it's now included in the main SQL file
if (file_exists('../database/profile_update.sql')) {
    unlink('../database/profile_update.sql');
}

// Delete the trial_update.sql file since it's redundant
if (file_exists('../database/trial_update.sql')) {
    unlink('../database/trial_update.sql');
}

// Get user's profile image
$userProfileImage = '';
if (isset($user['profile_image']) && !empty($user['profile_image'])) {
    // Get the site root path for consistent image references
    $site_root = SITE_URL;
    
    // Remove any leading slashes from the profile image path
    $user_image = ltrim($user['profile_image'], '/');
    
    // Create the full URL to the profile image
    $userProfileImage = $site_root . '/' . $user_image;
}

// If no profile image, use generated avatar
if (empty($userProfileImage)) {
    $userAvatar = "https://ui-avatars.com/api/?name=" . urlencode($userFirstName . " " . $userLastName) . "&background=4f46e5&color=fff&size=128";
} else {
    $userAvatar = $userProfileImage;
}

// Common timezones for dropdown
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
    '(GMT-03:00) Brazil, Buenos Aires, Georgetown',
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
    '(GMT+05:30) Kolkata, Chennai, Mumbai, New Delhi',
    '(GMT+05:45) Kathmandu',
    '(GMT+06:00) Almaty, Dhaka, Colombo',
    '(GMT+07:00) Bangkok, Hanoi, Jakarta',
    '(GMT+08:00) Beijing, Perth, Singapore, Hong Kong',
    '(GMT+09:00) Tokyo, Seoul, Osaka, Sapporo, Yakutsk',
    '(GMT+09:30) Adelaide, Darwin',
    '(GMT+10:00) Eastern Australia, Guam, Vladivostok',
    '(GMT+11:00) Magadan, Solomon Islands, New Caledonia',
    '(GMT+12:00) Auckland, Wellington, Fiji, Kamchatka'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Link to your custom CSS file -->
    <link rel="stylesheet" href="css/dashboard_style.css">
    <style>
        html, body {
            height: 100%;
        }
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        .dashboard-container {
            flex: 1 0 auto;
            display: flex;
            flex-direction: column;
        }
        .main-content-area {
            display: flex;
            flex-direction: column;
            flex: 1;
        }
        .dashboard-body {
            flex: 1 0 auto;
            padding-bottom: 30px;
        }
        .footer {
            flex-shrink: 0;
            margin-top: auto;
            clear: both;
            position: relative;
            width: 100%;
        }
    </style>
</head>
<body>
    <div class="dashboard-container container-fluid">
        <div class="row">
            <!-- Mobile Toggle Button -->
            <button class="sidebar-toggle d-md-none" id="sidebarToggle">
                <i class="fas fa-bars"></i>
            </button>

            <!-- Mobile Overlay -->
            <div class="sidebar-overlay" id="sidebarOverlay"></div>

            <div class="col-md-3 col-lg-2 sidebar" id="sidebarMenu">
<?php include '../includes/sidebar.php'; ?>
            </div>
            <div class="col-md-9 col-lg-10 main-content-area">
<?php include '../includes/dashboard-header.php'; ?>
                <div class="dashboard-body">
                    <div class="profile-page-container">
                        <h2>Profile Account</h2>

                        <ul class="nav nav-tabs" id="profileTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link <?php echo !isset($_GET['tab']) || $_GET['tab'] === 'personal-info' ? 'active' : ''; ?>" id="personal-info-tab" data-bs-toggle="tab" data-bs-target="#personal-info" type="button" role="tab" aria-controls="personal-info" aria-selected="<?php echo !isset($_GET['tab']) || $_GET['tab'] === 'personal-info' ? 'true' : 'false'; ?>">Personal Info</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link <?php echo isset($_GET['tab']) && $_GET['tab'] === 'change-password' ? 'active' : ''; ?>" id="change-password-tab" data-bs-toggle="tab" data-bs-target="#change-password" type="button" role="tab" aria-controls="change-password" aria-selected="<?php echo isset($_GET['tab']) && $_GET['tab'] === 'change-password' ? 'true' : 'false'; ?>">Change Password</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link <?php echo isset($_GET['tab']) && $_GET['tab'] === 'store-authentication' ? 'active' : ''; ?>" id="store-authentication-tab" data-bs-toggle="tab" data-bs-target="#store-authentication" type="button" role="tab" aria-controls="store-authentication" aria-selected="<?php echo isset($_GET['tab']) && $_GET['tab'] === 'store-authentication' ? 'true' : 'false'; ?>">Change Store Authentication</button>
                            </li>
                        </ul>
                        <?php if (!empty($success_message)): ?>
                            <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                                <?php echo $success_message; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($error_message)): ?>
                            <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                                <?php echo $error_message; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>
                        
                        <div class="tab-content" id="profileTabsContent">
                            <div class="tab-pane fade <?php echo !isset($_GET['tab']) || $_GET['tab'] === 'personal-info' ? 'show active' : ''; ?>" id="personal-info" role="tabpanel" aria-labelledby="personal-info-tab">
                                <div class="row mt-4">
                                    <div class="col-md-4 text-center">
                                        <div class="profile-card p-4 bg-light rounded shadow-sm">
                                            <div class="avatar-upload-area mb-3">
                                                <img src="<?php echo $userAvatar; ?>" alt="User Avatar" class="rounded-circle mb-3 shadow" width="120" height="120" id="profile-image">
                                                <form method="post" action="" enctype="multipart/form-data" id="profile-image-form">
                                                    <div class="d-grid gap-2">
                                                        <label for="profile-photo-upload" class="btn btn-outline-primary btn-sm"><i class="fas fa-camera me-2"></i>Change Photo</label>
                                                        <input type="file" id="profile-photo-upload" name="profile_image" class="d-none" accept="image/jpeg,image/png,image/gif,image/webp" onchange="document.getElementById('profile-image-form').submit();">
                                                        <input type="hidden" name="upload_profile_image" value="1">
                                                    </div>
                                                </form>
                                                <div class="mt-2 small text-muted">Max size: 2MB. Formats: JPG, PNG, GIF, WEBP</div>
                                            </div>
                                            <div class="user-info text-center">
                                                <h5 class="mb-1"><?php echo $userFirstName . ' ' . $userLastName; ?></h5>
                                                <p class="text-muted mb-2"><?php echo $userEmail; ?></p>
                                                <div class="badge bg-primary mb-2"><?php echo $userPackage; ?> Plan</div>
                                                <p class="small text-muted">Account Status: <?php echo $userStatus; ?></p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-8">
                                        <div class="card shadow-sm">
                                            <div class="card-header bg-white">
                                                <h5 class="card-title mb-0"><i class="fas fa-user-edit me-2 text-primary"></i>Personal Information</h5>
                                            </div>
                                            <div class="card-body">
                                                <form method="post" action="">
                                                    <div class="row mb-3">
                                                        <div class="col-md-6">
                                                            <label for="first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                                                            <input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo $userFirstName; ?>" required>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label for="last_name" class="form-label">Last Name</label>
                                                            <input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo $userLastName; ?>">
                                                        </div>
                                                    </div>
                                                    <div class="row mb-3">
                                                        <div class="col-md-6">
                                                            <label for="email" class="form-label">Email</label>
                                                            <div class="input-group">
                                                                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                                                <input type="email" class="form-control" id="email" value="<?php echo $userEmail; ?>" disabled>
                                                            </div>
                                                            <div class="form-text">Email cannot be changed</div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label for="phone" class="form-label">Phone</label>
                                                            <div class="input-group">
                                                                <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                                                <input type="text" class="form-control" id="phone" name="phone" value="<?php echo $userPhone; ?>">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="row mb-3">
                                                        <div class="col-md-6">
                                                            <label for="company_name" class="form-label">Company Name</label>
                                                            <div class="input-group">
                                                                <span class="input-group-text"><i class="fas fa-building"></i></span>
                                                                <input type="text" class="form-control" id="company_name" name="company_name" value="<?php echo $userCompany; ?>">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label for="gst_number" class="form-label">GST Number</label>
                                                            <div class="input-group">
                                                                <span class="input-group-text"><i class="fas fa-receipt"></i></span>
                                                                <input type="text" class="form-control" id="gst_number" name="gst_number" value="<?php echo $userGST; ?>">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="row mb-3">
                                                        <div class="col-md-6">
                                                            <label for="timezone" class="form-label">Timezone</label>
                                                            <select class="form-select" id="timezone" name="timezone">
                                                                <?php foreach ($timezones as $tz): ?>
                                                                    <option value="<?php echo htmlspecialchars($tz); ?>" <?php echo ($userTimezone === $tz) ? 'selected' : ''; ?>>
                                                                        <?php echo htmlspecialchars($tz); ?>
                                                                    </option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label">Account Type</label>
                                                            <input type="text" class="form-control" value="<?php echo $userPackage; ?> (<?php echo $userStatus; ?>)" disabled>
                                                        </div>
                                                    </div>
                                                    <div class="row mt-4">
                                                        <div class="col text-end">
                                                            <button type="submit" name="update_profile" class="btn btn-primary"><i class="fas fa-save me-2"></i>Save Changes</button>
                                                        </div>
                                                    </div>
                                                </form>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade <?php echo isset($_GET['tab']) && $_GET['tab'] === 'change-password' ? 'show active' : ''; ?>" id="change-password" role="tabpanel" aria-labelledby="change-password-tab">
                                <div class="row mt-4">
                                    <div class="col-md-8 offset-md-2">
                                        <div class="card shadow-sm">
                                            <div class="card-header bg-white">
                                                <h5 class="card-title mb-0"><i class="fas fa-lock me-2 text-primary"></i>Change Password</h5>
                                            </div>
                                            <div class="card-body">
                                                <form method="post" action="" id="password-change-form">
                                                    <div class="mb-3">
                                                        <label for="current_password" class="form-label">Current Password <span class="text-danger">*</span></label>
                                                        <div class="input-group">
                                                            <span class="input-group-text"><i class="fas fa-key"></i></span>
                                                            <input type="password" class="form-control" id="current_password" name="current_password" required>
                                                            <button class="btn btn-outline-secondary toggle-password" type="button" data-target="current_password">
                                                                <i class="fas fa-eye"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="new_password" class="form-label">New Password <span class="text-danger">*</span></label>
                                                        <div class="input-group">
                                                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                                            <input type="password" class="form-control" id="new_password" name="new_password" required minlength="8">
                                                            <button class="btn btn-outline-secondary toggle-password" type="button" data-target="new_password">
                                                                <i class="fas fa-eye"></i>
                                                            </button>
                                                        </div>
                                                        <div class="form-text">Password must be at least 8 characters long</div>
                                                        <div class="password-strength mt-2 d-none" id="password-strength">
                                                            <div class="progress" style="height: 5px;">
                                                                <div class="progress-bar" role="progressbar" style="width: 0%" id="password-strength-bar"></div>
                                                            </div>
                                                            <small class="text-muted" id="password-strength-text">Password strength</small>
                                                        </div>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="confirm_password" class="form-label">Confirm Password <span class="text-danger">*</span></label>
                                                        <div class="input-group">
                                                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                                            <button class="btn btn-outline-secondary toggle-password" type="button" data-target="confirm_password">
                                                                <i class="fas fa-eye"></i>
                                                            </button>
                                                        </div>
                                                        <div class="invalid-feedback" id="password-match-feedback">Passwords do not match</div>
                                                    </div>
                                                    <div class="text-end">
                                                        <button type="submit" name="change_password" class="btn btn-primary" id="update-password-btn">
                                                            <i class="fas fa-save me-2"></i>Update Password
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade <?php echo isset($_GET['tab']) && $_GET['tab'] === 'store-authentication' ? 'show active' : ''; ?>" id="store-authentication" role="tabpanel" aria-labelledby="store-authentication-tab">
                                <div class="row mt-4">
                                    <div class="col-md-8 offset-md-2">
                                        <div class="card shadow-sm">
                                            <div class="card-header bg-white">
                                                <h5 class="card-title mb-0"><i class="fas fa-shield-alt me-2 text-primary"></i>Store Authentication</h5>
                                            </div>
                                            <div class="card-body">
                                                <div class="alert alert-info">
                                                    <i class="fas fa-info-circle me-2"></i> Store authentication allows you to securely connect your store to our platform.
                                                </div>
                                                <form method="post" action="">
                                                    <div class="row mb-3">
                                                        <div class="col-md-6">
                                                            <label for="auth_email" class="form-label">Email</label>
                                                            <div class="input-group">
                                                                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                                                <input type="email" class="form-control" id="auth_email" value="<?php echo $userEmail; ?>" disabled>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label for="auth_phone" class="form-label">Phone</label>
                                                            <div class="input-group">
                                                                <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                                                <input type="text" class="form-control" id="auth_phone" name="auth_phone" value="<?php echo $userPhone; ?>">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="mb-3 d-none" id="otp-field">
                                                        <label for="otp" class="form-label">Enter OTP <span class="text-danger">*</span></label>
                                                        <div class="input-group">
                                                            <span class="input-group-text"><i class="fas fa-key"></i></span>
                                                            <input type="text" class="form-control" id="otp" name="otp" placeholder="Enter the OTP sent to your phone">
                                                        </div>
                                                    </div>
                                                    <div class="row mt-4">
                                                        <div class="col text-end">
                                                            <button type="button" id="send-otp-btn" class="btn btn-primary"><i class="fas fa-paper-plane me-2"></i>Send OTP</button>
                                                            <button type="submit" id="verify-otp-btn" class="btn btn-success d-none"><i class="fas fa-check-circle me-2"></i>Verify OTP</button>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Bootstrap JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
    
    <!-- Custom JavaScript for Profile Page -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Handle tab navigation from URL parameters
            const urlParams = new URLSearchParams(window.location.search);
            const tabParam = urlParams.get('tab');
            
            if (tabParam) {
                // Activate the correct tab based on URL parameter
                const tabElement = document.getElementById(tabParam + '-tab');
                if (tabElement) {
                    const tab = new bootstrap.Tab(tabElement);
                    tab.show();
                }
            }
            
            // Update URL when tabs are clicked
            const tabLinks = document.querySelectorAll('button[data-bs-toggle="tab"]');
            tabLinks.forEach(tabLink => {
                tabLink.addEventListener('shown.bs.tab', function(event) {
                    const id = event.target.getAttribute('id').replace('-tab', '');
                    const url = new URL(window.location.href);
                    url.searchParams.set('tab', id);
                    window.history.replaceState(null, '', url.toString());
                });
            });
            // Password toggle functionality
            const togglePasswordButtons = document.querySelectorAll('.toggle-password');
            if (togglePasswordButtons) {
                togglePasswordButtons.forEach(button => {
                    button.addEventListener('click', function() {
                        const targetId = this.getAttribute('data-target');
                        const passwordInput = document.getElementById(targetId);
                        const icon = this.querySelector('i');
                        
                        // Toggle password visibility
                        if (passwordInput.type === 'password') {
                            passwordInput.type = 'text';
                            icon.classList.remove('fa-eye');
                            icon.classList.add('fa-eye-slash');
                        } else {
                            passwordInput.type = 'password';
                            icon.classList.remove('fa-eye-slash');
                            icon.classList.add('fa-eye');
                        }
                    });
                });
            }
            
            // Password strength meter
            const newPasswordField = document.getElementById('new_password');
            const confirmPasswordField = document.getElementById('confirm_password');
            const passwordStrength = document.getElementById('password-strength');
            const passwordStrengthBar = document.getElementById('password-strength-bar');
            const passwordStrengthText = document.getElementById('password-strength-text');
            const passwordMatchFeedback = document.getElementById('password-match-feedback');
            const updatePasswordBtn = document.getElementById('update-password-btn');
            
            if (newPasswordField && confirmPasswordField) {
                // Show password strength meter when input is focused
                newPasswordField.addEventListener('focus', function() {
                    passwordStrength.classList.remove('d-none');
                });
                
                // Check password match
                confirmPasswordField.addEventListener('input', function() {
                    if (this.value === '') {
                        this.classList.remove('is-invalid');
                        this.classList.remove('is-valid');
                        passwordMatchFeedback.style.display = 'none';
                    } else if (this.value === newPasswordField.value) {
                        this.classList.remove('is-invalid');
                        this.classList.add('is-valid');
                        passwordMatchFeedback.style.display = 'none';
                    } else {
                        this.classList.remove('is-valid');
                        this.classList.add('is-invalid');
                        passwordMatchFeedback.style.display = 'block';
                    }
                });
                
                // Check password strength
                newPasswordField.addEventListener('input', function() {
                    const value = this.value;
                    let strength = 0;
                    let feedback = '';
                    
                    // Show password strength meter
                    passwordStrength.classList.remove('d-none');
                    
                    // Calculate password strength
                    if (value.length >= 8) strength += 25;
                    if (value.match(/[a-z]+/)) strength += 25;
                    if (value.match(/[A-Z]+/)) strength += 25;
                    if (value.match(/[0-9]+/)) strength += 12.5;
                    if (value.match(/[^a-zA-Z0-9]+/)) strength += 12.5;
                    
                    // Update strength bar
                    passwordStrengthBar.style.width = strength + '%';
                    
                    // Update color based on strength
                    if (strength < 30) {
                        passwordStrengthBar.className = 'progress-bar bg-danger';
                        feedback = 'Weak password';
                        this.classList.add('is-invalid');
                        this.classList.remove('is-valid');
                    } else if (strength < 60) {
                        passwordStrengthBar.className = 'progress-bar bg-warning';
                        feedback = 'Moderate password';
                        this.classList.remove('is-invalid');
                        this.classList.remove('is-valid');
                    } else if (strength < 80) {
                        passwordStrengthBar.className = 'progress-bar bg-info';
                        feedback = 'Good password';
                        this.classList.remove('is-invalid');
                        this.classList.add('is-valid');
                    } else {
                        passwordStrengthBar.className = 'progress-bar bg-success';
                        feedback = 'Strong password';
                        this.classList.remove('is-invalid');
                        this.classList.add('is-valid');
                    }
                    
                    // Update feedback text
                    passwordStrengthText.textContent = feedback;
                    
                    // Also check confirm password
                    if (confirmPasswordField.value) {
                        if (confirmPasswordField.value === value) {
                            confirmPasswordField.classList.remove('is-invalid');
                            confirmPasswordField.classList.add('is-valid');
                            passwordMatchFeedback.style.display = 'none';
                        } else {
                            confirmPasswordField.classList.remove('is-valid');
                            confirmPasswordField.classList.add('is-invalid');
                            passwordMatchFeedback.style.display = 'block';
                        }
                    }
                });
            }
            
            // Show/hide OTP field when Send OTP button is clicked
            const sendOtpBtn = document.getElementById('send-otp-btn');
            const verifyOtpBtn = document.getElementById('verify-otp-btn');
            const otpField = document.getElementById('otp-field');
            
            if (sendOtpBtn) {
                sendOtpBtn.addEventListener('click', function() {
                    // In a real application, you would send an AJAX request to send an OTP
                    // For demo purposes, we'll just show the OTP field
                    otpField.classList.remove('d-none');
                    verifyOtpBtn.classList.remove('d-none');
                    
                    // Show a success message
                    const alertDiv = document.createElement('div');
                    alertDiv.className = 'alert alert-success alert-dismissible fade show mt-3';
                    alertDiv.innerHTML = `
                        <i class="fas fa-check-circle me-2"></i> OTP sent successfully to your phone.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    `;
                    
                    // Insert the alert before the form
                    const formElement = sendOtpBtn.closest('form');
                    formElement.parentNode.insertBefore(alertDiv, formElement);
                    
                    // Change button text
                    sendOtpBtn.innerHTML = '<i class="fas fa-paper-plane me-2"></i>Resend OTP';
                });
            }
            
            // Initialize tooltips
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
    </script>
    
    <style>
        /* Additional styles for footer positioning */
        .dashboard-container {
            min-height: calc(100vh - 60px); /* Adjust based on footer height */
            position: relative;
            padding-bottom: 60px; /* Footer height */
        }
        
        .footer {
            position: absolute;
            bottom: 0;
            width: 100%;
            height: 60px; /* Set footer height */
            line-height: 60px; /* Vertically center the text */
            background-color: #f8f9fa;
            border-top: 1px solid #dee2e6;
        }
        
        /* Password toggle button styling */
        .toggle-password {
            cursor: pointer;
        }
        
        /* Password strength meter styling */
        .password-strength {
            margin-top: 5px;
        }
        
        .password-strength .progress {
            height: 5px;
            margin-bottom: 5px;
        }
    </style>
<?php include '../includes/dashboard-footer.php'; ?>
</body>
</html> 