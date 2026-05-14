<?php
require_once 'core/db.php';
header('Content-Type: application/json');

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
        echo json_encode(["status" => "success", "role" => $userData['role']]);
    } else {
        echo json_encode(["status" => "error", "message" => "Invalid credentials."]);
    }
    exit;
}

if (!isset($_SESSION['user_id'])) die(json_encode(["status" => "unauthorized"]));

if ($action === 'heartbeat') {
    $pdo->prepare("UPDATE users SET last_active = NOW() WHERE id = ?")->execute([$_SESSION['user_id']]);
    echo json_encode(["status" => "alive"]);
    exit;
}

if ($action === 'create_user' && $_SESSION['role'] === 'Admin') {
    $u = trim($_POST['new_username'] ?? '');
    $p = $_POST['new_password'] ?? '';
    $r = $_POST['new_role'] ?? 'Staff';
    
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$u]);
    if ($stmt->fetch()) die(json_encode(["status" => "error", "message" => "Username taken."]));
    
    $hash = password_hash($p, PASSWORD_DEFAULT);
    $pdo->prepare("INSERT INTO users (username, password_hash, role) VALUES (?, ?, ?)")->execute([$u, $hash, $r]);
    echo json_encode(["status" => "success", "message" => "User Provisioned"]);
    exit;
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
        echo json_encode(["status" => "success", "message" => "Password Rotated."]);
    } else {
        echo json_encode(["status" => "error", "message" => "Current password incorrect."]);
    }
    exit;
}

if ($action === 'logout') {
    session_destroy();
    echo json_encode(["status" => "logged_out"]);
    exit;
}
?>