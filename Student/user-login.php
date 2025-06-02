<?php
session_start();  // Start the session to handle session variables
include("includes/dbconnection.php");

$error = "";

// Prevent caching of the page to prevent the back button showing the dashboard after logout
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0"); 
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Fri, 01 Jan 1990 00:00:00 GMT");

// Store messages to display after page is rendered
$messages = array();

// Check if login required message exists and capture it
if (isset($_SESSION['login_required'])) {
    $messages[] = $_SESSION['login_required'];
    // Unset the session variable after capturing the message
    unset($_SESSION['login_required']);
}

if (isset($_POST['submit'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $role = $_POST['role'];  // Capture role from the form

    // Map form role values to U_usertype values
    if ($role == "event_advisor") $role_db = "event_advisor";
    elseif ($role == "petakom_coordinator") $role_db = "admin";
    elseif ($role == "student") $role_db = "student";
    else $role_db = "";

    // Check if inputs are not empty
    if (empty($email) || empty($password) || empty($role)) {
        $error = "All fields are required.";
    } else {
        // Unified query for user table
        $query = "SELECT * FROM user WHERE U_email=? AND U_usertype=?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('ss', $email, $role_db);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        if ($row) {
            $hashedPassword = $row['U_password'];

            if (password_verify($password, $hashedPassword)) {
                // Generate a new session ID to prevent session fixation attacks
                session_regenerate_id(true);
                
                $_SESSION['user_logged_in'] = true; // Mark the user as logged in
                $_SESSION['email'] = $email; 
                $_SESSION['role'] = $role_db;
                $_SESSION['U_userID'] = $row['U_userID'];
                
                // Create a session token for added security
                $_SESSION['session_token'] = bin2hex(random_bytes(32));
                
                // Set the last activity time for session timeout handling
                $_SESSION['last_activity'] = time();

                // Redirect to the corresponding dashboard based on the role
                if ($role == "event_advisor") {
                    header("Location: ../Event Advisor/advisor-dashboard.php");
                } elseif ($role == "petakom_coordinator") {
                    header("Location: ../Admin/admin-dashboard.php");
                } elseif ($role == "student") {
                    header("Location: student-dashboard.php");
                }
                exit();
            } else {
                $error = "Incorrect Username or Password.";
            }
        } else {
            $error = "Incorrect Username or Password.";
        }
        $stmt->close();
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <!-- Add meta tags to prevent caching -->
    <meta http-equiv="cache-control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="pragma" content="no-cache">
    <meta http-equiv="expires" content="0">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .center-container {
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            margin-top: -50px; /* Adjust as needed */
        }
        .login-container {
            background: #ffffff;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            max-width: 500px;
            width: 100%;
            text-align: center;
        }
        .login-title {
            font-family: 'Arial', sans-serif; 
            font-size: 30px; 
            font-weight: bold; 
            color: #333;
            margin-bottom: 20px;
        }
        .logo {
            max-width: 200px;
            margin-bottom: 20px;
        }
        /* Set text alignment to left for labels */
        .login-container label {
            text-align: left;
            display: block;
            margin-bottom: 5px;
        }
        .error-message {
            color: red;
            margin-bottom: 15px;
        }
        .info-message {
            color: #0056b3;
            background-color: #e6f0ff;
            border: 1px solid #b3d7ff;
            border-radius: 5px;
            padding: 10px;
            margin-bottom: 15px;
            text-align: center;
        }
    </style>
</head>

<body class="bg-light">

<div class="center-container">
    <img src="images/MyPetakom Logo.png" alt="PETAKOM Logo" class="logo">
    <div>
        <div class="login-container">
            <h1 class="login-title">LOGIN</h1>
            
            <?php
            // Display any captured messages
            if (!empty($messages)) {
                foreach ($messages as $message) {
                    echo "<div class='info-message'>" . htmlspecialchars($message) . "</div>";
                }
                
                // Also show as an alert for better visibility
                echo "<script type='text/javascript'>";
                echo "alert('" . addslashes(implode("\\n", $messages)) . "');";
                echo "</script>";
            }
            
            if ($error != "") {
                echo "<div class='error-message'>" . htmlspecialchars($error) . "</div>";
            }
            ?>
            <form method="post" action="user-login.php">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <div class="form-group">
                    <label for="role">Role</label>
                    <select class="form-control" id="role" name="role" required>
                        <option value="petakom_coordinator">Petakom Coordinator (Admin)</option>
                        <option value="event_advisor">Event Advisor</option>
                        <option value="student">Student</option>
                    </select>
                </div>

                <div class="form-group">
                    <button type="submit" name="submit" class="btn btn-primary btn-block">Login</button>
                </div>
                <div class="form-group text-center">
                    <a href="../Admin/user-register.php">Register New Account</a> | <a href="../Admin/user-forgot.php">Forgot Password?</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<!-- Add JavaScript to handle page reloads and back button -->
<script type="text/javascript">
    // When page loads
    window.onload = function() {
        // When navigating to this page
        window.addEventListener('pageshow', function(event) {
            // If navigated via browser cache/history (like back button)
            if (event.persisted || performance.navigation.type === 2) {
                // Force a page refresh to ensure newest state
                window.location.reload();
            }
        });
        
        // Clear browser form autofill
        document.getElementById('email').value = '';
        document.getElementById('password').value = '';
        
        // Set focus to the email field
        document.getElementById('email').focus();
    };
    
    // History manipulation for better back button handling
    if (window.history && window.history.pushState) {
        // When user navigates to this page
        window.history.pushState('login', null, '');
        
        // When user presses back button
        window.addEventListener('popstate', function() {
            // Push another state to prevent going back to protected pages
            window.history.pushState('login', null, '');
            
            // Show login prompt
            alert("Please log in to continue.");
        });
    }
</script>

</body>
</html>