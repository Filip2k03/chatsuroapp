<?php
// In a true production environment, load this from an .env file
define('APP_KEY', 'SloparaSuperSecretEnterpriseKey2026!@#$');

function encrypt_payload($string) {
    $cipher = "aes-256-cbc";
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
    
    // Decode and split IV from Ciphertext
    list($encrypted_data, $iv) = explode('::', base64_decode($string), 2);
    
    // Validate IV length to prevent malformed data errors
    if(strlen($iv) !== openssl_cipher_iv_length($cipher)) {
        return "[Encrypted Payload Corrupted]";
    }
    
    // Decrypt and return original text
    return openssl_decrypt($encrypted_data, $cipher, APP_KEY, 0, $iv);
}
?>