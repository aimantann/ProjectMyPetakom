<?php
session_start();

// Prevent caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0"); 
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Fri, 01 Jan 1990 00:00:00 GMT");

// Check if the user is logged in and has a valid session token
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true || 
    !isset($_SESSION['session_token']) || empty($_SESSION['session_token']) ||
    !isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    
    // User's session is invalid or expired
    // Clear any remaining session data
    $_SESSION = array();
    
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 42000, '/');
    }
    
    session_destroy();
    
    // Start a new session for message passing
    session_start();
    $_SESSION['login_required'] = "Your session has expired. Please login again to view the dashboard.";
    
    // Redirect to login page with correct path
    header("Location: ../Student/user-login.php");
    exit;
} else {
    // User has a valid session, redirect them back to the dashboard
    header("Location: student-dashboard.php");
    exit;
}
?>