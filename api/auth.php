<?php
require_once __DIR__ . '/../core/api.php';
require_once __DIR__ . '/../core/db.php';

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

$payload = api_payload();
$action = $payload['action'] ?? $_GET['action'] ?? '';

if ($action === 'login') {
    $user = trim($payload['username'] ?? '');
    $pass = $payload['password'] ?? '';
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$user]);
    $userData = $stmt->fetch();

    if ($userData && password_verify($pass, $userData['password_hash'])) {
        $_SESSION['user_id'] = $userData['id'];
        $_SESSION['role'] = $userData['role'];
        $pdo->prepare("UPDATE users SET last_active = NOW() WHERE id = ?")->execute([$userData['id']]);
        api_response(["status" => "success", "message" => "Logged in.", "role" => $userData['role'], "data" => ["role" => $userData['role']]]);
    } else {
        api_error("Invalid credentials.", 401);
    }
}

if (!isset($_SESSION['user_id'])) {
    api_response(["status" => "unauthorized", "message" => "Login required.", "data" => []], 401);
}

if ($action === 'me') {
    api_response([
        "status" => "success",
        "message" => "OK",
        "data" => [
            "id" => (int)$_SESSION['user_id'],
            "role" => $_SESSION['role'] ?? '',
        ],
    ]);
}

if ($action === 'heartbeat') {
    $pdo->prepare("UPDATE users SET last_active = NOW() WHERE id = ?")->execute([$_SESSION['user_id']]);
    api_response(["status" => "alive", "message" => "OK", "data" => []]);
}

// FIX: Case-insensitive Admin check
if ($action === 'create_user' && strtolower($_SESSION['role']) === 'admin') {
    $u = trim($payload['new_username'] ?? $payload['username'] ?? '');
    $p = $payload['new_password'] ?? $payload['password'] ?? '';
    $r = $payload['new_role'] ?? $payload['role'] ?? 'Staff';

    if ($u === '' || $p === '') {
        api_error("Username and password are required.", 422);
    }

    if (!preg_match('/^[A-Za-z0-9_.-]{3,50}$/', $u)) {
        api_error("Username must be 3-50 letters, numbers, dots, dashes, or underscores.", 422);
    }

    if (strlen($p) < 8) {
        api_error("Password must be at least 8 characters.", 422);
    }

    if (!in_array($r, ['Admin', 'Staff', 'Finance'], true)) {
        api_error("Invalid role selected.", 422);
    }
    
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$u]);
    if ($stmt->fetch()) {
        api_error("Username taken.", 409);
    }
    
    $hash = password_hash($p, PASSWORD_DEFAULT);
    $pdo->prepare("INSERT INTO users (username, password_hash, role) VALUES (?, ?, ?)")->execute([$u, $hash, $r]);
    api_response([
        "status" => "success",
        "message" => "User provisioned.",
        "data" => ["id" => (int)$pdo->lastInsertId(), "username" => $u, "role" => $r],
    ]);
}

if ($action === 'create_user') {
    api_error("Admin access required.", 403);
}

if ($action === 'change_password') {
    $old = $payload['old_password'] ?? '';
    $new = $payload['new_password'] ?? '';

    if (strlen($new) < 8) {
        api_error("New password must be at least 8 characters.", 422);
    }
    
    $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $hash = $stmt->fetchColumn();
    
    if(password_verify($old, $hash)) {
        $new_hash = password_hash($new, PASSWORD_DEFAULT);
        $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?")->execute([$new_hash, $_SESSION['user_id']]);
        api_success([], "Password rotated.");
    } else {
        api_error("Current password incorrect.", 403);
    }
}

if ($action === 'logout') {
    session_destroy();
    api_response(["status" => "logged_out", "message" => "Logged out.", "data" => []]);
}

api_error("Unknown auth action.", 404);
