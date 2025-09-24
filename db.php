<?php
// db.php
// Database connection file. Include this in other PHP files.
 
$host = 'localhost';  // Adjust if using remote DB, e.g., Supabase/Postgres would need PDO
$dbname = 'db7s97fuoglcty';
$username = 'uhpdlnsnj1voi';
$password = 'rowrmxvbu3z5';
 
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
 
// Function to generate TOTP (simple implementation for 2FA without external libs)
function generateTOTP($secret, $timeStep = 30, $codeLength = 6) {
    $time = floor(time() / $timeStep);
    $timeHex = str_pad(dechex($time), 16, '0', STR_PAD_LEFT);
    $hmac = hash_hmac('sha1', hex2bin($timeHex), base32_decode($secret), true);
    $offset = ord($hmac[19]) & 0xF;
    $code = ((ord($hmac[$offset]) & 0x7F) << 24) |
            ((ord($hmac[$offset + 1]) & 0xFF) << 16) |
            ((ord($hmac[$offset + 2]) & 0xFF) << 8) |
            (ord($hmac[$offset + 3]) & 0xFF);
    $code = $code % pow(10, $codeLength);
    return str_pad($code, $codeLength, '0', STR_PAD_LEFT);
}
 
// Base32 decode function (for TOTP)
function base32_decode($b32) {
    $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    $bits = '';
    $decoded = '';
    for ($i = 0; $i < strlen($b32); $i++) {
        $bits .= str_pad(decbin(strpos($alphabet, $b32[$i])), 5, '0', STR_PAD_LEFT);
    }
    for ($i = 0; $i + 8 <= strlen($bits); $i += 8) {
        $decoded .= chr(bindec(substr($bits, $i, 8)));
    }
    return $decoded;
}
?>
