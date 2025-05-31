<?php
session_start(); // Start the session

// Prevent page caching to avoid back button issues
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Fri, 01 Jan 1990 00:00:00 GMT");

// Check if the user is logged in
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    // Set a session message about needing to login again
    $_SESSION['login_required'] = "Your session has expired. Please login again to view the dashboard.";
    header("Location: ../Admin/user-login.php"); // Redirect to login if not logged in
    exit;
}

// Additional security check - validate user's session token
if (!isset($_SESSION['session_token']) || empty($_SESSION['session_token'])) {
    // If token is missing but user is supposedly logged in, this might be a session issue
    session_start();
    $_SESSION['login_required'] = "For security reasons, please login again to continue.";
    header("Location: ../Admin/user-login.php");
    exit;
}

// Check if the user has the correct role
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    session_start();
    $_SESSION['login_required'] = "You don't have permission to access this page.";
    header("Location: ../Admin/user-login.php");
    exit;
}

// Session timeout check (30 minutes of inactivity)
$timeout = 1800; // 30 minutes in seconds
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout)) {
    // Session has expired
    $_SESSION = array();
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 42000, '/');
    }
    session_destroy();
    
    session_start();
    $_SESSION['login_required'] = "Your session has expired due to inactivity. Please login again.";
    header("Location: ../Student/user-login.php");
    exit;
}

// Update last activity time
$_SESSION['last_activity'] = time();

// Include header file
include('includes/header.php');

// Include database connection file
include('includes/dbconnection.php');

// --- ADDED: Fetch logged-in student's name ---
$user_name = '';
if (isset($_SESSION['email'])) {
    $user_email = $_SESSION['email'];
    $query = "SELECT U_name FROM user WHERE U_email = ? LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('s', $user_email);
    $stmt->execute();
    $stmt->bind_result($db_name);
    if ($stmt->fetch()) {
        $user_name = $db_name;
    }
    $stmt->close();
}

?>

<!-- Add meta tags to prevent back button navigation after logout -->
<meta http-equiv="cache-control" content="no-cache, no-store, must-revalidate">
<meta http-equiv="pragma" content="no-cache">
<meta http-equiv="expires" content="0">

<!-- Dashboard content container -->
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
    </div>
    
    <!-- Dashboard content goes here -->
</div>

<!-- Add JavaScript to handle back button detection -->
<script type="text/javascript">
    // When page loads
    window.onload = function() {
        // When navigating to this page
        window.addEventListener('pageshow', function(event) {
            // If navigated via browser cache/history (like back button)
            if (event.persisted || performance.navigation.type === 2) {
                // Redirect to validation script
                window.location.href = "user-validatesession.php";
            }
        });
    };
    
    // Additional history manipulation for better back button handling
    if (window.history && window.history.pushState) {
        window.history.pushState(null, "", window.location.href);
        window.onpopstate = function() {
            window.location.href = "user-validatesession.php";
        };
    }
</script>

<?php
// Close the database connection
$conn->close();

// Include footer and scripts
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