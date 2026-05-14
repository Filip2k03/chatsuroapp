<?php
require_once __DIR__ . '/env.php';

function encrypt_payload($string) {
    $cipher = "aes-256-cbc";
    $app_key = env('APP_KEY', '');
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($cipher));
    
    $encrypted = openssl_encrypt($string, $cipher, $app_key, 0, $iv);
    return base64_encode($encrypted . '::' . $iv);
}

function decrypt_payload($string) {
    $cipher = "aes-256-cbc";
    $app_key = env('APP_KEY', '');
    
    $parts = explode('::', base64_decode($string), 2);
    if(count($parts) !== 2) return "[Corrupt Payload]";
    
    list($encrypted_data, $iv) = $parts;
    
    if(strlen($iv) !== openssl_cipher_iv_length($cipher)) return "[Corrupt IV]";
    
    return openssl_decrypt($encrypted_data, $cipher, $app_key, 0, $iv);
}
?>