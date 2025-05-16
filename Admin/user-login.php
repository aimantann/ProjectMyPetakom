<?php
session_start();
include("includes/dbconnection.php");

$error = "";

if (isset($_POST['submit'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $role = $_POST['role'];

    if ($role == "event_advisor") {
        $query = "SELECT * FROM advisor WHERE advEmail=?";
        $email_field = 'advEmail';
        $password_field = 'advPassword';
    } elseif ($role == "petakom_coordinator") {
        $query = "SELECT * FROM admin WHERE adminEmail=?";
        $email_field = 'adminEmail';
        $password_field = 'adminPassword';
    } elseif ($role == "student") {
        $query = "SELECT * FROM student WHERE stuEmail=?";
        $email_field = 'stuEmail';
        $password_field = 'stuPassword';
    } else {
        $error = "Invalid role selected.";
    }

    if (isset($query)) {
        $stmt = $conn->prepare($query);
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        if ($row && password_verify($password, $row[$password_field])) {
            $_SESSION['email'] = $row[$email_field];
            $_SESSION['role'] = $role;

            if ($role == "event_advisor") {
                header("Location: ../Event Advisor/advisor-dashboard.php");
            } elseif ($role == "petakom_coordinator") {
                header("Location: admin-dashboard.php");
            } elseif ($role == "student") {
                header("Location: ../Student/student-dashboard.php");
            }
            exit();
        } else {
            $error = "Incorrect email or password.";
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
        .login-container label {
            text-align: left;
            display: block;
            margin-bottom: 5px;
        }
        .error-message {
            color: red;
            margin-bottom: 15px;
        }
    </style>
</head>

<body class="bg-light">

<?php
if (isset($_SESSION['success_message'])) {
    echo "<script type='text/javascript'>alert('" . $_SESSION['success_message'] . "');</script>";
    unset($_SESSION['success_message']);
}
?>

<div class="center-container">
    <img src="images/MyPetakom Logo.png" alt="PETAKOM Logo" class="logo">
    <div>
        <div class="login-container">
            <h1 class="login-title">LOGIN</h1>
            <?php
            if ($error != "") {
                echo "<div class='error-message'>$error</div>";
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
                    <a href="../Admin/user-register.php">Register New Account</a> |
                    <a href="../Admin/user-forgot.php">Forgot Password?</a>
                </div>
            </form>
        </div>
    </div>
</div>

</body>
</html>