<?php
function encrypt_payload($string) {
    $cipher = "aes-256-cbc";
    $app_key = env('APP_KEY', 'SloparaFallbackKey2026!@#$');
    $iv_length = openssl_cipher_iv_length($cipher);
    
    // Generate secure random Initialization Vector (IV)
    $iv = openssl_random_pseudo_bytes($iv_length);
    
    // Encrypt the string
    $encrypted = openssl_encrypt($string, $cipher, APP_KEY, 0, $iv);
    
    // Combine IV and Ciphertext, then base64 encode for safe database storage
    return base64_encode($encrypted . '::' . $iv);
}

function decrypt_payload($string) {
    $cipher = "aes-256-cbc";
    $app_key = env('APP_KEY', 'SloparaFallbackKey2026!@#$');
    
    // Decode and split IV from Ciphertext
    list($encrypted_data, $iv) = explode('::', base64_decode($string), 2);
    
    // Validate IV length to prevent malformed data errors
    if(strlen($iv) !== openssl_cipher_iv_length($cipher)) {
        return "[Encrypted Payload Corrupted]";
    }
    
    // Decrypt and return original text
    return openssl_decrypt($encrypted_data, $cipher, $app_key, 0, $iv);
}
?>