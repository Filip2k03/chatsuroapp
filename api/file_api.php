<?php
require_once __DIR__ . '/../core/api.php';

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

if (!isset($_SESSION['user_id'])) {
    api_response(["status" => "unauthorized", "message" => "Login required.", "data" => []], 401);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payload = api_payload();
    $content = $payload['content'] ?? '';
    $ext = strtolower(trim($payload['extension'] ?? '.txt'));
    $ext = str_starts_with($ext, '.') ? $ext : '.' . $ext;
    
    $allowed = [
        '.txt', '.md', '.csv', '.tsv', '.log',
        '.json', '.xml', '.yaml', '.yml',
        '.html', '.css', '.js', '.ts', '.jsx', '.tsx',
        '.php', '.py', '.rb', '.go', '.rs', '.java', '.c', '.cpp', '.h', '.cs',
        '.sql', '.sh', '.env.example'
    ];
    
    if ($content === '') {
        api_error("Snippet content is required.", 422);
    }

    if (strlen($content) > 200000) {
        api_error("Snippet is too large. Keep it under 200 KB.", 413);
    }

    if (!in_array($ext, $allowed, true)) {
        api_error("Invalid extension. Use a text/code file type.", 422, ["allowed" => $allowed]);
    }

    $dir = __DIR__ . '/../storage';
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
        file_put_contents($dir . '/.htaccess', "Options -Indexes\nphp_flag engine off");
    }

    $filename = 'slopara_mod_' . bin2hex(random_bytes(4)) . $ext;
    file_put_contents($dir . '/' . $filename, $content);
    
    api_response([
        "status" => "success",
        "message" => "Snippet stored.",
        "url" => 'storage/' . $filename,
        "filename" => $filename,
        "data" => ["url" => 'storage/' . $filename, "filename" => $filename],
    ]);
}

api_error("Method not allowed.", 405);
