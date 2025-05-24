<?php
require_once '../includes/config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('index.php');
}

// You can add PHP logic here for fetching and displaying user profile information
// For now, using placeholder data
$userFirstName = "Kavan";
$userLastName = "Patel";
$userEmail = "uddan1771@gmail.com";
$userPhone = "919427415370";
$userGST = "";
$userBirthday = "";
$userStoreStatus = "uddan1771@gmail.com";
$userAvatar = "https://via.placeholder.com/100"; // Placeholder avatar image

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
                                <button class="nav-link active" id="personal-info-tab" data-bs-toggle="tab" data-bs-target="#personal-info" type="button" role="tab" aria-controls="personal-info" aria-selected="true">Personal Info</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="change-password-tab" data-bs-toggle="tab" data-bs-target="#change-password" type="button" role="tab" aria-controls="change-password" aria-selected="false">Change Password</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="store-authentication-tab" data-bs-toggle="tab" data-bs-target="#store-authentication" type="button" role="tab" aria-controls="store-authentication" aria-selected="false">Change Store Authentication</button>
                            </li>
                        </ul>
                        <div class="tab-content" id="profileTabsContent">
                            <div class="tab-pane fade show active" id="personal-info" role="tabpanel" aria-labelledby="personal-info-tab">
                                <div class="row mt-4">
                                    <div class="col-md-4 text-center">
                                        <div class="avatar-upload-area">
                                            <img src="<?php echo $userAvatar; ?>" alt="User Avatar" class="rounded-circle mb-2" width="100" height="100">
                                            <button class="btn btn-primary btn-sm">Upload</button>
                                        </div>
                                    </div>
                                    <div class="col-md-8">
                                        <form>
                                            <div class="row mb-3">
                                                <div class="col-md-6">
                                                    <label for="firstName" class="form-label">First Name</label>
                                                    <input type="text" class="form-control" id="firstName" value="<?php echo htmlspecialchars($userFirstName); ?>">
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="lastName" class="form-label">Last Name</label>
                                                    <input type="text" class="form-control" id="lastName" value="<?php echo htmlspecialchars($userLastName); ?>">
                                                </div>
                                            </div>
                                            <div class="row mb-3">
                                                <div class="col-md-6">
                                                    <label for="email" class="form-label">Email</label>
                                                    <input type="email" class="form-control" id="email" value="<?php echo htmlspecialchars($userEmail); ?>" disabled>
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="phone" class="form-label">Phone</label>
                                                    <input type="text" class="form-control" id="phone" value="<?php echo htmlspecialchars($userPhone); ?>">
                                                </div>
                                            </div>
                                            <div class="row mb-3">
                                                <div class="col-md-6">
                                                    <label for="gstNo" class="form-label">GST No</label>
                                                    <input type="text" class="form-control" id="gstNo" value="<?php echo htmlspecialchars($userGST); ?>">
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="birthdayDate" class="form-label">Birthday Date</label>
                                                    <input type="text" class="form-control" id="birthdayDate" placeholder="dd-MM-yyyy" value="<?php echo htmlspecialchars($userBirthday); ?>">
                                                </div>
                                            </div>
                                            <div class="row mb-3">
                                                <div class="col-md-6">
                                                    <label for="storeStatus" class="form-label">Store Status</label>
                                                    <input type="text" class="form-control" id="storeStatus" value="<?php echo htmlspecialchars($userStoreStatus); ?>" disabled>
                                                </div>
                                                 <div class="col-md-6">
                                                    <!-- Placeholder for another field or empty space -->
                                                 </div>
                                            </div>
                                            <div class="row mt-4">
                                                 <div class="col text-end">
                                                    <button type="submit" class="btn btn-primary">Submit</button>
                                                 </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="change-password" role="tabpanel" aria-labelledby="change-password-tab">
                                <div class="row mt-4">
                                    <div class="col-md-8 offset-md-2">
                                        <form>
                                            <div class="mb-3">
                                                <label for="currentPassword" class="form-label">Current Password</label>
                                                <input type="password" class="form-control" id="currentPassword">
                                            </div>
                                            <div class="mb-3">
                                                <label for="newPassword" class="form-label">New Password</label>
                                                <input type="password" class="form-control" id="newPassword">
                                            </div>
                                            <div class="mb-3">
                                                <label for="confirmPassword" class="form-label">Confirm Password</label>
                                                <input type="password" class="form-control" id="confirmPassword">
                                            </div>
                                            <div class="text-end">
                                                <button type="submit" class="btn btn-primary">Submit</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="store-authentication" role="tabpanel" aria-labelledby="store-authentication-tab">
                                <div class="row mt-4">
                                     <div class="col-md-8 offset-md-2">
                                        <form>
                                            <div class="row mb-3">
                                                <div class="col-md-6">
                                                    <label for="authEmail" class="form-label">Email</label>
                                                    <input type="email" class="form-control" id="authEmail" value="<?php echo htmlspecialchars($userEmail); ?>" disabled>
                                                </div>
                                                <div class="col-md-6">
                                                    <label for="authPhone" class="form-label">Phone</label>
                                                    <input type="text" class="form-control" id="authPhone" value="<?php echo htmlspecialchars($userPhone); ?>">
                                                </div>
                                            </div>
                                             <div class="row mt-4">
                                                 <div class="col text-end">
                                                    <button type="button" class="btn btn-primary">Send OTP</button>
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
    <!-- Bootstrap JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
<?php include '../includes/dashboard-footer.php'; ?>
</body>
</html> 