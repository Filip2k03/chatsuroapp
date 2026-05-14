<?php
$isLoggedIn = isset($_SESSION['user_id']);
$userRole = $_SESSION['role'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= env('APP_NAME', 'Slopara') ?> Secure Enterprise</title>
    
    <!-- Dynamically injecting the separated CSS file -->
    <?php require_once 'includes/styles.php'; ?>
</head>
<body class="dark-mode">