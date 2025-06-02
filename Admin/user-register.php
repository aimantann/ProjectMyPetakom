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

    // Map form role values to U_usertype values
    if ($role == "event_advisor") $role_db = "event_advisor";
    elseif ($role == "admin" || $role == "petakom_coordinator") $role_db = "admin";
    elseif ($role == "student") $role_db = "student";
    else $role_db = "";

    if ($password != $confirm_password) {
        $error = "Passwords do not match.";
    } elseif ($role_db == "") {
        $error = "Please select a role.";
    } else {
        // Check for existing email
        $query = "SELECT * FROM user WHERE U_email=?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error = "Email is already registered.";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Insert new user into user table
            $insert_query = "INSERT INTO user (U_name, U_phoneNum, U_email, U_password, U_usertype) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($insert_query);
            $stmt->bind_param('sssss', $name, $phoneNum, $email, $hashedPassword, $role_db);
            $stmt->execute();
            $new_user_id = $stmt->insert_id;

            // If staff, insert into staff and staffposition if needed
            if ($role_db == "admin" || $role_db == "event_advisor") {
                // Insert staffposition if SP_Role does not exist
                $sp_query = "SELECT SP_ID FROM staffposition WHERE SP_Role=?";
                $stmt = $conn->prepare($sp_query);
                $stmt->bind_param('s', $role_db);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($row = $result->fetch_assoc()) {
                    $sp_id = $row['SP_ID'];
                } else {
                    $insert_sp = "INSERT INTO staffposition (SP_Role) VALUES (?)";
                    $stmt = $conn->prepare($insert_sp);
                    $stmt->bind_param('s', $role_db);
                    $stmt->execute();
                    $sp_id = $stmt->insert_id;
                }
                // Insert staff
                $insert_staff = "INSERT INTO staff (U_userID, SP_ID) VALUES (?, ?)";
                $stmt = $conn->prepare($insert_staff);
                $stmt->bind_param('ii', $new_user_id, $sp_id);
                $stmt->execute();
            }
            // If student, insert into student table
            if ($role_db == "student") {
                $today = date('Y-m-d');
                $insert_student = "INSERT INTO student (U_userID, STU_registrationDate) VALUES (?, ?)";
                $stmt = $conn->prepare($insert_student);
                $stmt->bind_param('is', $new_user_id, $today);
                $stmt->execute();
            }

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