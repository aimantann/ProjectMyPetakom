<?php
session_start();

// Prevent caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Fri, 01 Jan 1990 00:00:00 GMT");

// Only run this on protected pages (DASHBOARDS, etc.), NEVER on user-login.php or user-logout.php!

// Check if the user is logged in and has a valid session token
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true ||
    !isset($_SESSION['session_token']) || empty($_SESSION['session_token'])) {

    // Clear any remaining session data
    $_SESSION = array();
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 42000, '/');
    }
    session_destroy();

    // Start a new session for message passing
    session_start();
    $_SESSION['login_required'] = "Your session has expired. Please login again to view the dashboard.";

    // Redirect to login page
    header("Location: user-login.php");
    exit;
}

// If the code reaches here, the session is valid and the user can stay on the protected page.
?>