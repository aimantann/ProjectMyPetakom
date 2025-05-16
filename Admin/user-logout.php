<?php
session_start(); // Start the session

// Send cache control headers before destroying the session
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Fri, 01 Jan 1990 00:00:00 GMT");

// Store the logout message before destroying the session
$logoutMessage = "You have been logged out successfully";

// Destroy all session variables and session itself
$_SESSION = array();

// If a session cookie is used, destroy it too
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 42000, '/');
}

session_destroy();

// Start a new session for the logout message
session_start();
$_SESSION['logout_message'] = "Logout successfully";

// Redirect to the login page
header("Location: user-login.php");
exit();
?>