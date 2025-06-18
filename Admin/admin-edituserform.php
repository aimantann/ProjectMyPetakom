<?php
require_once('user-validatesession.php');

ob_start();

include("includes/dbconnection.php");
include("includes/header.php");

if (!isset($_GET['id'])) {
    header("Location: admin-edituserlist.php");
    exit();
}

$user_id = $_GET['id'];
$user_data = array();
$error = "";
$success = "";

// Get current user data
$query = "SELECT u.*, COALESCE(sp.SP_Role, u.U_usertype) as role_type FROM user u
          LEFT JOIN staff s ON u.U_userID = s.U_userID
          LEFT JOIN staffposition sp ON s.SP_ID = sp.SP_ID
          WHERE u.U_userID = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $user_id);
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
    if ($new_email != $user_data['U_email']) {
        $check_query = "SELECT U_userID FROM user WHERE U_email = ? AND U_userID != ?";
        $check_stmt = $conn->prepare($check_query);
        $check_stmt->bind_param('si', $new_email, $user_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        if ($check_result->num_rows > 0) {
            $error = "Email already exists!";
        }
    }
    
    if (!$error) {
        // Update user data
        $update_query = "UPDATE user SET U_name = ?, U_phoneNum = ?, U_email = ? WHERE U_userID = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param('sssi', $new_name, $new_phone, $new_email, $user_id);
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
    
<div class="container-main">
    <div class="form-container">
        <h2 class="page-title">Edit User Details</h2>
        
        <div class="role-display">
            <strong>Role: </strong>
            <?php 
            if ($user_data['role_type'] == 'event_advisor') echo 'Event Advisor';
            elseif ($user_data['role_type'] == 'student') echo 'Student';
            elseif ($user_data['role_type'] == 'admin') echo 'Administrator';
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
            <?php if ($user_data['role_type'] != 'admin'): ?>
            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($user_data['U_name'] ?? ''); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="phone">Phone Number</label>
                <input type="text" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($user_data['U_phoneNum'] ?? ''); ?>" required>
            </div>
            <?php else: ?>
            <input type="hidden" name="name" value="">
            <input type="hidden" name="phone" value="">
            <?php endif; ?>
            
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user_data['U_email']); ?>" required>
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

<?php
include('includes/footer.php');

ob_end_flush();
?>

</body>
</html>