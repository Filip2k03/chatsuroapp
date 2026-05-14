<?php
// Using __DIR__ ensures absolute pathing regardless of how the Front Controller routes the request. 
// This PERMANENTLY fixes the "Failed to open stream" error.
require_once __DIR__ . '/../core/db.php';
require_once __DIR__ . '/../core/crypto.php';

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

if (!isset($_SESSION['user_id'])) {
    die(json_encode(["status" => "unauthorized"]));
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action === 'send') {
    $text = $_POST['message'] ?? '';
    $type = $_POST['type'] ?? 'text'; // 'text' or 'file_snippet'
    $receiver_id = isset($_POST['receiver_id']) ? (int)$_POST['receiver_id'] : 0;

    if (empty(trim($text)) || $receiver_id === 0) exit;

    // Encrypt BEFORE it touches the database
    $encrypted = encrypt_payload($text);
    
    // V2: Insert with receiver_id for 1-on-1 private messaging
    $stmt = $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, encrypted_payload, message_type) VALUES (?, ?, ?, ?)");
    $stmt->execute([$_SESSION['user_id'], $receiver_id, $encrypted, $type]);
    
    echo json_encode(["status" => "sent"]);
    exit;
}

if ($action === 'fetch') {
    // V2: Delta Fetching Cache & 1-on-1 Filtering
    $last_id = isset($_GET['last_id']) ? (int)$_GET['last_id'] : 0;
    $receiver_id = isset($_GET['receiver_id']) ? (int)$_GET['receiver_id'] : 0;
    $me = $_SESSION['user_id'];
    
    if ($receiver_id === 0) {
        echo json_encode(["status" => "success", "data" => []]);
        exit;
    }
    
    // Fetch only NEW messages securely between YOU and the TARGET USER
    $stmt = $pdo->prepare("
        SELECT m.id, m.encrypted_payload, m.message_type, m.created_at, u.role as sender_role, u.username as sender_name 
        FROM messages m 
        JOIN users u ON m.sender_id = u.id 
        WHERE m.id > ? 
        AND ((m.sender_id = ? AND m.receiver_id = ?) OR (m.sender_id = ? AND m.receiver_id = ?))
        ORDER BY m.id ASC LIMIT 100
    ");
    $stmt->execute([$last_id, $me, $receiver_id, $receiver_id, $me]);
    
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
    // Return ID and Username for 1-on-1 targeting
    $stmt = $pdo->query("
        SELECT id, username, role, 
        IF(TIMESTAMPDIFF(SECOND, last_active, NOW()) < 120, 'online', 'offline') as status 
        FROM users
    ");
    $users = $stmt->fetchAll();
    
    // Role-based visibility
    $myRole = strtolower($_SESSION['role']);
    $filteredUsers = [];
    
    foreach($users as $u) {
        // Skip showing yourself in the directory
        if ($u['id'] == $_SESSION['user_id']) continue; 
        
        // Admin sees everyone. Staff/Finance only see Admins.
        if ($myRole !== 'admin' && strtolower($u['role']) !== 'admin') {
            continue;
        }
        $filteredUsers[] = $u;
    }
    
    echo json_encode(["status" => "success", "data" => $filteredUsers]);
    exit;
}
?>