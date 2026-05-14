<?php
require_once __DIR__ . '/env.php';

try {
    $dsn = "mysql:host=" . env('DB_HOST', 'localhost') . ";dbname=" . env('DB_NAME', 'slopara_chat') . ";charset=utf8mb4";
    $pdo = new PDO($dsn, env('DB_USER', 'root'), env('DB_PASS', ''));
    
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die(json_encode(["status" => "error", "message" => "Database Connection Failed. Check .env file."]));
}
?>