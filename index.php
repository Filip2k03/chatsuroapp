<?php
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.cookie_samesite', 'Strict');
    session_start();
}

// Load Environment Engine
require_once __DIR__ . '/core/env.php';
load_env(__DIR__ . '/.env');

// Simple MVC Router
$route = $_GET['route'] ?? '/chat';

switch ($route) {
    case '/api/auth':
        require_once __DIR__ . '/api/auth.php';
        break;
    case '/api/chat':
        require_once __DIR__ . '/api/chat_api.php';
        break;
    case '/api/file':
        require_once __DIR__ . '/api/file_api.php';
        break;
    case '/chat':
    default:
        require_once __DIR__ . '/views/chat.php';
        break;
}
?>
