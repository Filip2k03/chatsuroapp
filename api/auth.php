<?php
require_once __DIR__ . '/../core/db.php';

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

function auth_json_response(array $payload, int $statusCode = 200): void
{
    http_response_code($statusCode);
    echo json_encode($payload);
    exit;
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action === 'login') {
    $user = trim($_POST['username'] ?? '');
    $pass = $_POST['password'] ?? '';
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$user]);
    $userData = $stmt->fetch();

    if ($userData && password_verify($pass, $userData['password_hash'])) {
        $_SESSION['user_id'] = $userData['id'];
        $_SESSION['role'] = $userData['role'];
        $pdo->prepare("UPDATE users SET last_active = NOW() WHERE id = ?")->execute([$userData['id']]);
        auth_json_response(["status" => "success", "role" => $userData['role']]);
    } else {
        auth_json_response(["status" => "error", "message" => "Invalid credentials."], 401);
    }
}

if (!isset($_SESSION['user_id'])) {
    auth_json_response(["status" => "unauthorized"], 401);
}

if ($action === 'heartbeat') {
    $pdo->prepare("UPDATE users SET last_active = NOW() WHERE id = ?")->execute([$_SESSION['user_id']]);
    auth_json_response(["status" => "alive"]);
}

// FIX: Case-insensitive Admin check
if ($action === 'create_user' && strtolower($_SESSION['role']) === 'admin') {
    $u = trim($_POST['new_username'] ?? '');
    $p = $_POST['new_password'] ?? '';
    $r = $_POST['new_role'] ?? 'Staff';

    if ($u === '' || $p === '') {
        auth_json_response(["status" => "error", "message" => "Username and password are required."], 422);
    }

    if (!preg_match('/^[A-Za-z0-9_.-]{3,50}$/', $u)) {
        auth_json_response(["status" => "error", "message" => "Username must be 3-50 letters, numbers, dots, dashes, or underscores."], 422);
    }

    if (strlen($p) < 8) {
        auth_json_response(["status" => "error", "message" => "Password must be at least 8 characters."], 422);
    }

    if (!in_array($r, ['Admin', 'Staff', 'Finance'], true)) {
        auth_json_response(["status" => "error", "message" => "Invalid role selected."], 422);
    }
    
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$u]);
    if ($stmt->fetch()) {
        auth_json_response(["status" => "error", "message" => "Username taken."], 409);
    }
    
    $hash = password_hash($p, PASSWORD_DEFAULT);
    $pdo->prepare("INSERT INTO users (username, password_hash, role) VALUES (?, ?, ?)")->execute([$u, $hash, $r]);
    auth_json_response(["status" => "success", "message" => "User provisioned.", "user" => ["username" => $u, "role" => $r]]);
}

if ($action === 'create_user') {
    auth_json_response(["status" => "error", "message" => "Admin access required."], 403);
}

if ($action === 'change_password') {
    $old = $_POST['old_password'] ?? '';
    $new = $_POST['new_password'] ?? '';
    
    $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $hash = $stmt->fetchColumn();
    
    if(password_verify($old, $hash)) {
        $new_hash = password_hash($new, PASSWORD_DEFAULT);
        $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?")->execute([$new_hash, $_SESSION['user_id']]);
        auth_json_response(["status" => "success", "message" => "Password rotated."]);
    } else {
        auth_json_response(["status" => "error", "message" => "Current password incorrect."], 403);
    }
}

if ($action === 'logout') {
    session_destroy();
    auth_json_response(["status" => "logged_out"]);
}

auth_json_response(["status" => "error", "message" => "Unknown auth action."], 404);
