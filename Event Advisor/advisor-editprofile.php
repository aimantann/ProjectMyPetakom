<?php
session_start();
include("includes/dbconnection.php");

// Check if user is event advisor
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'event_advisor') {
    header("Location: user-login.php");
    exit();
}

$email = $_SESSION['email'];
$advisor_data = array();
$error = "";
$success = "";

// Get current advisor data from unified user table
$query = "SELECT U_userID, U_name, U_phoneNum, U_email FROM user WHERE U_email = ? AND U_usertype = 'event_advisor'";
$stmt = $conn->prepare($query);
$stmt->bind_param('s', $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $advisor_data = $result->fetch_assoc();
} else {
    header("Location: user-login.php");
    exit();
}

// Handle form submission
if (isset($_POST['update'])) {
    $new_name = $_POST['name'];
    $new_phone = $_POST['phone'];
    $new_email = $_POST['email'];

    // Check if new email already exists (if email is being changed)
    if ($new_email != $email) {
        $check_query = "SELECT U_userID FROM user WHERE U_email = ? AND U_userID != ?";
        $check_stmt = $conn->prepare($check_query);
        $check_stmt->bind_param('si', $new_email, $advisor_data['U_userID']);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        if ($check_result->num_rows > 0) {
            $error = "Email already exists!";
        }
    }

    if (!$error) {
        // Update advisor data in user table
        $update_query = "UPDATE user SET U_name = ?, U_phoneNum = ?, U_email = ? WHERE U_userID = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param('sssi', $new_name, $new_phone, $new_email, $advisor_data['U_userID']);

        if ($stmt->execute()) {
            // Update session email if changed
            if ($new_email != $email) {
                $_SESSION['email'] = $new_email;
            }
            $_SESSION['success_message'] = "Profile updated successfully!";
            header("Location: advisor-viewprofile.php");
            exit();
        } else {
            $error = "Error updating profile!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Profile - Event Advisor</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .header {
            background: #28a745;
            color: white;
            padding: 20px 0;
            margin-bottom: 30px;
        }
        .logo {
            max-width: 150px;
        }
        .container-main {
            margin: 20px auto;
            max-width: 600px;
        }
        .form-container {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }
        .page-title {
            color: #333;
            margin-bottom: 30px;
            text-align: center;
        }
        .form-group label {
            font-weight: bold;
            color: #666;
        }
        .role-display {
            background: #e8f5e8;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 25px;
            text-align: center;
        }
        .role-badge {
            background: #28a745;
            color: white;
            padding: 8px 20px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: bold;
        }
        .btn-save {
            background: #007bff;
            border: none;
            padding: 12px 30px;
            border-radius: 25px;
            font-weight: bold;
        }
        .btn-save:hover {
            background: #0056b3;
        }
        .avatar {
            width: 60px;
            height: 60px;
            background: #28a745;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-size: 24px;
            color: white;
            font-weight: bold;
        }
    </style>
</head>

<body class="bg-light">

<div class="header">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6">
                <img src="images/MyPetakom Logo.png" alt="PETAKOM Logo" class="logo">
            </div>
            <div class="col-md-6 text-right">
                <a href="advisor-viewprofile.php" class="btn btn-light">Back to Profile</a>
                <a href="user-logout.php" class="btn btn-danger">Logout</a>
            </div>
        </div>
    </div>
</div>

<div class="container-main">
    <div class="form-container">
        <div class="avatar">
            <?php echo strtoupper(substr($advisor_data['U_name'], 0, 1)); ?>
        </div>
        <h2 class="page-title">Edit My Profile</h2>
        
        <div class="role-display">
            <span class="role-badge">Event Advisor</span>
        </div>
        
        <?php
        if ($error) {
            echo "<div class='alert alert-danger'>$error</div>";
        }
        if ($success) {
            echo "<div class='alert alert-success'>$success</div>";
        }
        ?>
        
        <form method="post" action="">
            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($advisor_data['U_name']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="phone">Phone Number</label>
                <input type="text" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($advisor_data['U_phoneNum']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($advisor_data['U_email']); ?>" required>
            </div>
            
            <div class="form-group text-center">
                <button type="submit" name="update" class="btn btn-primary btn-save">Save Changes</button>
            </div>
            
            <div class="form-group text-center">
                <a href="advisor-viewprofile.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

</body>
</html>