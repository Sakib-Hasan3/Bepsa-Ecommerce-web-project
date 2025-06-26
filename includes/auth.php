<?php
// Ensure session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect to login if user not authenticated
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header('Location: /bepsa-ecommerce/login.php');
    exit;
}

// Helper function to require a specific role
function require_role($role) {
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== $role) {
        header('Location: /bepsa-ecommerce/logout.php');
        exit;
    }
}
?>
