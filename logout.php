<?php
require_once 'config.php';

// Destroy session data
session_unset();
session_destroy();

// Start a new session and generate a new CSRF token for any future visits (recommended)
session_start();
csrf_token(); // Ensures CSRF is fresh if user visits login/register again

// Redirect to login page
header('Location: login.php');
exit;
?>
