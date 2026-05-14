<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    die(json_encode(["status" => "unauthorized"]));
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $content = $_POST['content'] ?? '';
    $ext = $_POST['extension'] ?? '.txt';
    
    // Strict extension validation to prevent PHP shell uploads
    $allowed_exts = ['.py', '.html', '.txt'];
    if (!in_array($ext, $allowed_exts)) {
        die(json_encode(["status" => "error", "message" => "Security Violation: Extension not permitted."]));
    }

    $dir = __DIR__ . '/storage';
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
        
        // Protect directory from executing scripts
        file_put_contents($dir . '/.htaccess', "Options -Indexes\nRemoveHandler .php .phtml .php3\nRemoveType .php .phtml .php3\nphp_flag engine off");
    }

    // Generate unique, randomized filename
    $filename = 'slopara_' . bin2hex(random_bytes(4)) . $ext;
    $filepath = $dir . '/' . $filename;

    file_put_contents($filepath, $content);
    
    // Return localized URL for the chat frontend to create a download card
    $url = 'storage/' . $filename;

    echo json_encode(["status" => "success", "url" => $url, "filename" => $filename]);
    exit;
}
?>