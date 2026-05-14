<?php
// Secure Session Configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'core/db.php'; // Or 'db.php' depending on your folder layout
require_once 'core/crypto.php';

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

if (!isset($_SESSION['user_id'])) {
    die(json_encode(["status" => "unauthorized"]));
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action === 'send') {
    $text = $_POST['message'] ?? '';
    $type = $_POST['type'] ?? 'text'; // 'text' or 'file_snippet'

    if (empty(trim($text))) exit;

    // Encrypt BEFORE it touches the database
    $encrypted = encrypt_payload($text);
    
    $stmt = $pdo->prepare("INSERT INTO messages (sender_id, encrypted_payload, message_type) VALUES (?, ?, ?)");
    $stmt->execute([$_SESSION['user_id'], $encrypted, $type]);
    
    echo json_encode(["status" => "sent"]);
    exit;
}

if ($action === 'fetch') {
    // Fetch recent messages securely
    $stmt = $pdo->query("
        SELECT m.id, m.encrypted_payload, m.message_type, m.created_at, u.role as sender_role 
        FROM messages m 
        JOIN users u ON m.sender_id = u.id 
        ORDER BY m.id ASC LIMIT 100
    ");
    
    $messages = [];
    while ($row = $stmt->fetch()) {
        // Decrypt payload on the fly for the authenticated user
        $row['text'] = decrypt_payload($row['encrypted_payload']);
        unset($row['encrypted_payload']); // Strip ciphertext before sending to frontend
        $messages[] = $row;
    }
    echo json_encode(["status" => "success", "data" => $messages]);
    exit;
}

if ($action === 'users') {
    // SQL calculates if last_active is older than 120 seconds
    $stmt = $pdo->query("
        SELECT role, 
        IF(TIMESTAMPDIFF(SECOND, last_active, NOW()) < 120, 'online', 'offline') as status 
        FROM users
    ");
    $users = $stmt->fetchAll();
    echo json_encode(["status" => "success", "data" => $users]);
    exit;
}
?>