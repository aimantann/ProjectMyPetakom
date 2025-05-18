<?php
session_start(); // Start the session

// Send cache control headers before destroying the session
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Fri, 01 Jan 1990 00:00:00 GMT");

// Destroy all session variables and session itself
$_SESSION = array();

// If a session cookie is used, destroy it too
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 42000, '/');
}

session_destroy();

header("Location: user-login.php");

exit();
?>