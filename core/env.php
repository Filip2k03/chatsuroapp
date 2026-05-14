<?php
function load_env($filePath) {
    if (!file_exists($filePath)) return;
    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    
    foreach ($lines as $line) {
        $line = trim($line);
        
        // Skip comments and lines that are purely whitespace
        if ($line === '' || strpos($line, '#') === 0) continue;
        
        // Fix Warning & Deprecated Error: Make sure an '=' exists before exploding
        if (strpos($line, '=') !== false) {
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);
            
            if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
                // Fix Fatal Error: Check if the server has disabled putenv() for security
                if (function_exists('putenv')) {
                    putenv(sprintf('%s=%s', $name, $value));
                }
                
                // Always populate $_ENV and $_SERVER as reliable fallbacks
                $_ENV[$name] = $value;
                $_SERVER[$name] = $value;
            }
        }
    }
}

function env($key, $default = null) {
    return $_ENV[$key] ?? $default;
}
?>