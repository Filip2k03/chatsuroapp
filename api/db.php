<?php
try {
    // Establish secure PDO connection using ENV variables
    $pdo = new PDO("mysql:host=" . env('DB_HOST', 'localhost') . ";dbname=" . env('DB_NAME', 'slopara_chat') . ";charset=utf8mb4", env('DB_USER', 'root'), env('DB_PASS', ''));
    
    // Set strict error mode and default fetch format
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Return structured JSON error for frontend handling
    die(json_encode(["status" => "error", "message" => "Database Connection Failed"]));
}
?>