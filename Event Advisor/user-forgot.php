<?php
session_start();
include("includes/dbconnection.php");

$error = "";
$success = "";

if (isset($_POST['submit'])) {
    $email = $_POST['email'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Check if passwords match
    if ($new_password !== $confirm_password) {
        $error = "Passwords do not match.";
    } elseif (strlen($new_password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } else {
        // Check if the email exists in the selected role
        $query = "SELECT * FROM advisor WHERE advEmail=?";  // Check for Event Advisor
        $stmt = $conn->prepare($query);
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user_type = 'advisor';

        if ($result->num_rows == 0) {
            // If no advisor found, check the other tables
            $query = "SELECT * FROM admin WHERE adminEmail=?";  // Check for Petakom Coordinator (Admin)
            $stmt = $conn->prepare($query);
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $result = $stmt->get_result();
            $user_type = 'admin';
        }

        if ($result->num_rows == 0) {
            // If no admin found, check student table
            $query = "SELECT * FROM student WHERE stuEmail=?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $result = $stmt->get_result();
            $user_type = 'student';
        }

        if ($result->num_rows > 0) {
            // User exists, now reset password with encryption
            // Hash the new password using password_hash()
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            
            // Verify the hash was created successfully
            if ($hashed_password === false) {
                $error = "Error creating password hash.";
            } else {
                // Update password based on user type
                if ($user_type == 'advisor') {
                    $update_query = "UPDATE advisor SET advPassword=? WHERE advEmail=?";
                } elseif ($user_type == 'admin') {
                    $update_query = "UPDATE admin SET adminPassword=? WHERE adminEmail=?";
                } else {
                    $update_query = "UPDATE student SET stuPassword=? WHERE stuEmail=?";
                }

                $stmt = $conn->prepare($update_query);
                if ($stmt === false) {
                    $error = "Error preparing statement: " . $conn->error;
                } else {
                    $stmt->bind_param('ss', $hashed_password, $email);
                    
                    if ($stmt->execute()) {
                        if ($stmt->affected_rows > 0) {
                            // Set success message
                            //$_SESSION['success_message'] = "Password reset successfully";
                            // Redirect to login page
                            header("Location: user-login.php");
                            exit();
                        } else {
                            $error = "No rows affected. Password may not have been updated.";
                        }
                    } else {
                        $error = "Error updating password: " . $stmt->error;
                    }
                }
            }
        } else {
            $error = "Email does not exist.";
        }

        if (isset($stmt)) {
            $stmt->close();
        }
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .center-container {
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            margin-top: -50px;
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
        .error-message {
            color: red;
            margin-bottom: 15px;
        }
    </style>
</head>

<body class="bg-light">

<?php
// Check if the session variable for success message is set and display it
if (isset($_SESSION['success_message'])) {
    echo "<script type='text/javascript'>alert('" . $_SESSION['success_message'] . "');</script>";
    // Unset the session variable after displaying the message
    unset($_SESSION['success_message']);
}
?>

<div class="center-container">
    <img src="images/MyPetakom Logo.png" alt="PETAKOM Logo" class="logo">
    <div>
        <div class="login-container">
            <h1 class="login-title">Reset Password</h1>
            <?php
            if ($error != "") {
                echo "<div class='error-message'>$error</div>";
            }
            ?>
            <form method="post" action="user-forgot.php">
                <div class="form-group">
                    <label for="email">Enter your Email</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <input type="password" class="form-control" id="new_password" name="new_password" required minlength="6">
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm New Password</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                </div>

                <div class="form-group">
                    <button type="submit" name="submit" class="btn btn-primary btn-block">Reset Password</button>
                </div>
                <div class="form-group text-center">
                    <a href="../Admin/user-login.php">Back to Login</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Client-side password confirmation validation
document.querySelector('form').addEventListener('submit', function(e) {
    var password = document.getElementById('new_password').value;
    var confirmPassword = document.getElementById('confirm_password').value;
    
    if (password !== confirmPassword) {
        e.preventDefault();
        alert('Passwords do not match!');
        return false;
    }
    
    if (password.length < 3) {
        e.preventDefault();
        alert('Password must be at least 3 characters long!');
        return false;
    }
});
</script>

</body>
</html>