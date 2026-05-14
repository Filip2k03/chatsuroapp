<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'core/env.php';
load_env(__DIR__ . '/.env');

require_once 'core/router.php';
$router = new Router();

// Frontend Views
$router->add('GET', '/chat', 'views/chat.php');

// Secure API Endpoints
$router->add('POST', '/api/auth', 'auth.php');
$router->add('GET', '/api/auth', 'auth.php'); // Used for logout & heartbeat
$router->add('POST', '/api/chat', 'chat_api.php');
$router->add('GET', '/api/chat', 'chat_api.php');
$router->add('POST', '/api/file', 'file_api.php');

$router->dispatch($_SERVER['REQUEST_METHOD']);
?>