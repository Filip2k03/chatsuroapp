<?php
// Secure Session Configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_samesite', 'Strict');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'db.php';

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action === 'login') {
    $user = trim($_POST['username'] ?? '');
    $pass = $_POST['password'] ?? '';

    // Prevent SQL injection via prepared statements
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$user]);
    $userData = $stmt->fetch();

    // Verify hashed password
    if ($userData && password_verify($pass, $userData['password_hash'])) {
        $_SESSION['user_id'] = $userData['id'];
        $_SESSION['role'] = $userData['role'];
        
        // Snap status to active on login
        $pdo->prepare("UPDATE users SET last_active = NOW() WHERE id = ?")->execute([$userData['id']]);
        
        echo json_encode(["status" => "success", "role" => $userData['role']]);
    } else {
        echo json_encode(["status" => "error", "message" => "Invalid credentials. Unauthorized access logged."]);
    }
    exit;
}

if ($action === 'create_user') {
    // 1. Strict Authorization Check
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Admin') {
        die(json_encode(["status" => "error", "message" => "Security Violation: Admin clearance required."]));
    }

    $new_user = trim($_POST['new_username'] ?? '');
    $new_pass = $_POST['new_password'] ?? '';
    $new_role = $_POST['new_role'] ?? 'Staff';

    if (empty($new_user) || empty($new_pass)) {
        die(json_encode(["status" => "error", "message" => "Username and Password cannot be empty."]));
    }

    // 2. Check for duplicate usernames
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$new_user]);
    if ($stmt->fetch()) {
        die(json_encode(["status" => "error", "message" => "Username already allocated."]));
    }

    // 3. Hash password securely (Argon2 / Bcrypt)
    $hashed_pass = password_hash($new_pass, PASSWORD_DEFAULT);

    // 4. Insert into database
    $stmt = $pdo->prepare("INSERT INTO users (username, password_hash, role) VALUES (?, ?, ?)");
    if ($stmt->execute([$new_user, $hashed_pass, $new_role])) {
        echo json_encode(["status" => "success", "message" => "User $new_user successfully provisioned."]);
    } else {
        echo json_encode(["status" => "error", "message" => "Database failure during provisioning."]);
    }
    exit;
}

if ($action === 'logout') {
    session_destroy();
    echo json_encode(["status" => "logged_out"]);
    exit;
}
?>