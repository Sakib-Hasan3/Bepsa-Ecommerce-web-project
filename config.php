<?php
// Start session if not already started
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

// Database connection
$host = 'localhost';
$user = 'root'; // Set your db username
$pass = '';     // Set your db password
$db   = 'bepsa'; // Set your db name

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die('Database connection failed: ' . $conn->connect_error);
}

// XSS output escape function
function esc($str) {
    return htmlspecialchars($str, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

// --- CSRF Protection --- //

// Generate and store CSRF token for this session
function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Verify a submitted CSRF token
function verify_csrf($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
?>
