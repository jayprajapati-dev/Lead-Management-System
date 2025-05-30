<?php
// includes/public-head.php
// This file contains the <head> section content for public pages

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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="<?php echo SITE_URL; ?>/public/css/style.css" rel="stylesheet">
    <link href="<?php echo SITE_URL; ?>/public/css/animations.css" rel="stylesheet">
    <!-- Custom JavaScript -->
    <script src="<?php echo SITE_URL; ?>/public/js/theme-toggle.js" defer></script>
    <script src="<?php echo SITE_URL; ?>/public/js/animations.js" defer></script>
</head>
<?php // Close the head tag ?> 