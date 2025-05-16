<?php
session_start();

// Prevent caching of the page to prevent the back button showing the dashboard after logout
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Fri, 01 Jan 1990 00:00:00 GMT");

// Check if the user is logged in
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    // Set a session message about needing to login again
    $_SESSION['login_required'] = "Your session has expired. Please login again to view the dashboard.";
    header("Location: user-login.php"); // Redirect to login if not logged in
    exit;
}

// Additional security check - validate user's session token if available
if (!isset($_SESSION['session_token']) || empty($_SESSION['session_token'])) {
    // Generate a new token if it doesn't exist
    $_SESSION['session_token'] = bin2hex(random_bytes(32));
    
    // If token is missing but user is supposedly logged in, this might be a session issue
    if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true) {
        session_start();
        $_SESSION['login_required'] = "For security reasons, please login again to continue.";
        header("Location: user-login.php");
        exit;
    }
}

include('includes/header.php');
include('includes/dbconnection.php');
?>

<!-- Add a meta tag to prevent back button navigation after logout -->
<meta http-equiv="cache-control" content="no-cache, no-store, must-revalidate">
<meta http-equiv="pragma" content="no-cache">
<meta http-equiv="expires" content="0">

<!-- Add your dashboard content here -->
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
    </div>
    
    <!-- Your dashboard content goes here -->
</div>

<!-- Add JavaScript to handle back button detection -->
<script type="text/javascript">
    // When page loads
    window.onload = function() {
        // When navigating to this page with back button
        window.addEventListener('pageshow', function(event) {
            // If navigated via browser history (back button)
            if (event.persisted || performance.navigation.type === 2) {
                // Instead of just reloading, redirect to a validation script
                window.location.href = "validate-session.php";
            }
        });
    };
</script>

<?php
include('includes/footer.php');
include('includes/scripts.php');
?>

<!-- Custom CSS -->
<style>
    .card-title {
        font-size: 1.2em;
        font-weight: bold;
    }
    .card-count {
        font-size: 1.2em;
    }
    .card-footer {
        font-size: 1em;
    }
</style>