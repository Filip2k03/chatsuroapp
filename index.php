<?php
// Secure Session Configuration (Must be before session_start)
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_samesite', 'Strict');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'core/env.php';
load_env(__DIR__ . '/.env');

require_once 'core/router.php';
$router = new Router();

// Frontend Views
$router->add('GET', '/chat', 'views/chat.php');

// Secure API Endpoints - Updated paths to include the /api/ directory
$router->add('POST', '/api/auth', 'api/auth.php');
$router->add('GET', '/api/auth', 'api/auth.php'); // Used for logout & heartbeat
$router->add('POST', '/api/chat', 'api/chat_api.php');
$router->add('GET', '/api/chat', 'api/chat_api.php');
$router->add('POST', '/api/file', 'api/file_api.php');

$router->dispatch($_SERVER['REQUEST_METHOD']);
?>