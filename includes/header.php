<?php
$isLoggedIn = isset($_SESSION['user_id']);
$userRole = $_SESSION['role'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars(env('APP_NAME', 'Slopara'), ENT_QUOTES, 'UTF-8') ?> Secure Enterprise</title>
    
    <!-- PWA Implementation -->
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#0f172a">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Slopara">
    <meta name="mobile-web-app-capable" content="yes">
    <link rel="apple-touch-icon" href="https://placehold.co/192x192/0f172a/00f0ff?text=S">
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('sw.js?v=3')
                    .catch(() => {});
            });
        }
    </script>

    <!-- Dynamically injecting the separated CSS file -->
    <?php require_once __DIR__ . '/styles.php'; ?>
</head>
<body class="dark-mode">
