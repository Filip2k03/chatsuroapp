<?php
require_once 'core/db.php';
require_once 'core/crypto.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) die(json_encode(["status" => "unauthorized"]));

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action === 'send') {
    $text = $_POST['message'] ?? '';
    $type = $_POST['type'] ?? 'text';
    if (!empty(trim($text))) {
        $enc = encrypt_payload($text);
        $pdo->prepare("INSERT INTO messages (sender_id, encrypted_payload, message_type) VALUES (?, ?, ?)")
            ->execute([$_SESSION['user_id'], $enc, $type]);
    }
    echo json_encode(["status" => "sent"]);
    exit;
}

if ($action === 'fetch') {
    $stmt = $pdo->query("SELECT m.id, m.encrypted_payload, u.role as sender_role FROM messages m JOIN users u ON m.sender_id = u.id ORDER BY m.id ASC LIMIT 100");
    $messages = [];
    while ($row = $stmt->fetch()) {
        $row['text'] = decrypt_payload($row['encrypted_payload']);
        unset($row['encrypted_payload']);
        $messages[] = $row;
    }
    echo json_encode(["status" => "success", "data" => $messages]);
    exit;
}

if ($action === 'users') {
    // V1 Visibility Rules: Staff & Finance only see Admin. Admins see everyone.
    $myRole = $_SESSION['role'];
    $sql = "SELECT role, IF(TIMESTAMPDIFF(SECOND, last_active, NOW()) < 120, 'online', 'offline') as status FROM users";
    
    if ($myRole !== 'Admin') {
        $sql .= " WHERE role = 'Admin'";
    }
    
    $users = $pdo->query($sql)->fetchAll();
    echo json_encode(["status" => "success", "data" => $users]);
    exit;
}
?>