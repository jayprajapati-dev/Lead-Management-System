<?php
// includes/header.php
// This file contains the header content and starts the HTML document

// Ensure config.php is included if not already
if (!defined('SITE_URL')) {
    require_once __DIR__ . '/config.php';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS (Optional) -->
    <link href="<?php echo SITE_URL; ?>/public/assets/css/style.css" rel="stylesheet">
    <link href="<?php echo SITE_URL; ?>/public/assets/css/login.css" rel="stylesheet">
</head>
<body>

</body>
</html> 