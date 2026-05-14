<?php
require_once __DIR__ . '/../core/env.php';

if (!isset($_SESSION['user_id'])) die(json_encode(["status" => "unauthorized"]));
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $content = $_POST['content'] ?? '';
    $ext = $_POST['extension'] ?? '.txt';
    
    // Expanded Developer File Extensions
    $allowed = ['.py', '.html', '.txt', '.js', '.ts', '.php', '.css', '.json', '.md', '.sql'];
    
    if (!in_array($ext, $allowed)) {
        die(json_encode(["status" => "error", "message" => "Invalid extension. Not permitted by server rules."]));
    }

    $dir = __DIR__ . '/../storage';
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
        file_put_contents($dir . '/.htaccess', "Options -Indexes\nphp_flag engine off");
    }

    $filename = 'slopara_mod_' . bin2hex(random_bytes(4)) . $ext;
    file_put_contents($dir . '/' . $filename, $content);
    
    echo json_encode(["status" => "success", "url" => 'storage/' . $filename, "filename" => $filename]);
    exit;
}
?>