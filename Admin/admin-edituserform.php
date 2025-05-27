<?php
session_start();
include("includes/dbconnection.php");



if (!isset($_GET['email']) || !isset($_GET['role'])) {
    header("Location: admin-edituserlist.php");
    exit();
}

$email = $_GET['email'];
$role = $_GET['role'];
$user_data = array();
$error = "";
$success = "";

// Get current user data
if ($role == 'advisor') {
    $query = "SELECT advName as name, advPhoneNum as phone, advEmail as email FROM advisor WHERE advEmail = ?";
} elseif ($role == 'student') {
    $query = "SELECT stuName as name, stuPhoneNum as phone, stuEmail as email FROM student WHERE stuEmail = ?";
} elseif ($role == 'admin') {
    $query = "SELECT adminEmail as email FROM admin WHERE adminEmail = ?";
}

$stmt = $conn->prepare($query);
$stmt->bind_param('s', $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header("Location: admin-edituserlist.php");
    exit();
}

$user_data = $result->fetch_assoc();

// Handle form submission
if (isset($_POST['update'])) {
    $new_name = $_POST['name'] ?? '';
    $new_phone = $_POST['phone'] ?? '';
    $new_email = $_POST['email'];
    
    // Check if new email already exists (if email is being changed)
    if ($new_email != $email) {
        $check_queries = array();
        $check_queries[] = "SELECT advEmail FROM advisor WHERE advEmail = ?";
        $check_queries[] = "SELECT stuEmail FROM student WHERE stuEmail = ?";
        $check_queries[] = "SELECT adminEmail FROM admin WHERE adminEmail = ?";
        
        $email_exists = false;
        foreach ($check_queries as $check_query) {
            $check_stmt = $conn->prepare($check_query);
            $check_stmt->bind_param('s', $new_email);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();
            if ($check_result->num_rows > 0) {
                $email_exists = true;
                break;
            }
        }
        
        if ($email_exists) {
            $error = "Email already exists!";
        }
    }
    
    if (!$error) {
        // Update user data
        if ($role == 'advisor') {
            $update_query = "UPDATE advisor SET advName = ?, advPhoneNum = ?, advEmail = ? WHERE advEmail = ?";
            $stmt = $conn->prepare($update_query);
            $stmt->bind_param('ssss', $new_name, $new_phone, $new_email, $email);
        } elseif ($role == 'student') {
            $update_query = "UPDATE student SET stuName = ?, stuPhoneNum = ?, stuEmail = ? WHERE stuEmail = ?";
            $stmt = $conn->prepare($update_query);
            $stmt->bind_param('ssss', $new_name, $new_phone, $new_email, $email);
        } elseif ($role == 'admin') {
            $update_query = "UPDATE admin SET adminEmail = ? WHERE adminEmail = ?";
            $stmt = $conn->prepare($update_query);
            $stmt->bind_param('ss', $new_email, $email);
        }
        
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "User updated successfully!";
            header("Location: admin-edituserlist.php");
            exit();
        } else {
            $error = "Error updating user!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit User</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .header {
            background: #f8f9fa;
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
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .page-title {
            color: #333;
            margin-bottom: 30px;
            text-align: center;
        }
        .form-group label {
            font-weight: bold;
        }
        .role-display {
            background: #e9ecef;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
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
                <a href="edit-users-list.php" class="btn btn-secondary">Back to Users List</a>
                <a href="user-logout.php" class="btn btn-danger">Logout</a>
            </div>
        </div>
    </div>
</div>

<div class="container-main">
    <div class="form-container">
        <h2 class="page-title">Edit User Details</h2>
        
        <div class="role-display">
            <strong>Role: </strong>
            <?php 
            if ($role == 'advisor') echo 'Event Advisor';
            elseif ($role == 'student') echo 'Student';
            elseif ($role == 'admin') echo 'Administrator';
            ?>
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
            <?php if ($role != 'admin'): ?>
            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($user_data['name'] ?? ''); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="phone">Phone Number</label>
                <input type="text" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($user_data['phone'] ?? ''); ?>" required>
            </div>
            <?php else: ?>
            <input type="hidden" name="name" value="">
            <input type="hidden" name="phone" value="">
            <?php endif; ?>
            
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user_data['email']); ?>" required>
            </div>
            
            <div class="form-group">
                <button type="submit" name="update" class="btn btn-primary btn-block">Save Changes</button>
            </div>
            
            <div class="form-group text-center">
                <a href="admin-edituserlist.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

</body>
</html>