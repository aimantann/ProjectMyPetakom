<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .center-container {
            height: 100vh;
            display: grid;
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
            max-width: 400px;
            width: 150%;
            text-align: center;
            margin-right: 170px;
            margin-left: 100px;
        }
        .login-title {
            font-family: 'Arial', sans-serif; 
            font-size: 30px; 
            font-weight: bold; 
            color: #333;
            margin-bottom: 20px;
        }
        .logo {
            max-width: 250px;
            margin-bottom: 20px;
            margin-top: 60px;
            margin-left: 175px;
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
include("includes/dbconnection.php");
$error = "";

if (isset($_POST['submit'])) {
    $name = $_POST['name'];
    $phoneNum = $_POST['phoneNum'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = $_POST['role'];

    if ($password != $confirm_password) {
        $error = "Passwords do not match.";
    } elseif ($role == "") {
        $error = "Please select a role.";
    } else {
        // Check for existing email
        if ($role == "event_advisor") {
            $query = "SELECT * FROM advisor WHERE advEmail=?";
        } elseif ($role == "petakom_coordinator" || $role == "admin") {
            $query = "SELECT * FROM admin WHERE adminEmail=?";
        } elseif ($role == "student") {
            $query = "SELECT * FROM student WHERE stuEmail=?";
        }

        $stmt = $conn->prepare($query);
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error = "Email is already registered.";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert new user into the appropriate table
            if ($role == "event_advisor") {
                $insert_query = "INSERT INTO advisor (advName, advPhoneNum, advEmail, advPassword) VALUES (?, ?, ?, ?)";
                $stmt = $conn->prepare($insert_query);
                $stmt->bind_param('ssss', $name, $phoneNum, $email, $hashedPassword);
            } elseif ($role == "petakom_coordinator" || $role == "admin") {
                $insert_query = "INSERT INTO admin (adminEmail, adminPassword) VALUES (?, ?)";
                $stmt = $conn->prepare($insert_query);
                $stmt->bind_param('ss', $email, $hashedPassword);
            } elseif ($role == "student") {
                $insert_query = "INSERT INTO student (stuName, stuPhoneNum, stuEmail, stuPassword) VALUES (?, ?, ?, ?)";
                $stmt = $conn->prepare($insert_query);
                $stmt->bind_param('ssss', $name, $phoneNum, $email, $hashedPassword);
            }

            $stmt->execute();

            $_SESSION['success_message'] = "Account successfully created!";
            header("Location: user-login.php");
            exit();
        }
        $stmt->close();
    }
    $conn->close();
}
?>

<div class="center-container">
    <img src="images/MyPetakom Logo.png" alt="PETAKOM Logo" class="logo">
    <div>
        <div class="login-container">
            <h1 class="login-title">CREATE ACCOUNT</h1>
            <?php
            if ($error != "") {
                echo "<div class='error-message'>$error</div>";
            }
            ?>
            <form method="post" action="" id="registerForm">
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text" class="form-control" id="name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="phoneNum">Phone Number</label>
                    <input type="text" class="form-control" id="phoneNum" name="phoneNum" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                </div>
                <div class="form-group">
                    <label for="role">Select Role</label>
                    <select class="form-control" id="role" name="role" required>
                        <option value="">---</option>
                        <option value="event_advisor">Event Advisor</option>
                        <option value="student">Student</option>
                        <option value="admin">Administrator</option>
                    </select>
                </div>

                <div class="form-group">
                    <button type="submit" name="submit" class="btn btn-primary btn-block">Register</button>
                </div>
                <div class="form-group text-center">
                    <a href="../Admin/user-login.php">Already have an account? Login</a>
                </div>
            </form>
        </div>
    </div>
</div>

</body>
</html>