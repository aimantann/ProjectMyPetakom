<?php
session_start();

// Prevent caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Fri, 01 Jan 1990 00:00:00 GMT");

// Destroy all session variables and the session cookie
$_SESSION = array();
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 42000, '/');
}
session_destroy();

// Start a new session just to pass the message
session_start();
$_SESSION['login_required'] = "You have been logged out successfully.";

// Redirect to login page
header("Location: user-login.php");
exit();
?>