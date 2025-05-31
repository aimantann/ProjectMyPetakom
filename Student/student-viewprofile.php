<?php
session_start();
include("includes/dbconnection.php");

// Check if user is student
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    header("Location: user-login.php");
    exit();
}

$email = $_SESSION['email'];
$student_data = array();

// Get student data from unified user table
$query = "SELECT U_name, U_phoneNum, U_email FROM user WHERE U_email = ? AND U_usertype = 'student'";
$stmt = $conn->prepare($query);
$stmt->bind_param('s', $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $student_data = $result->fetch_assoc();
} else {
    // Handle case where student data is not found
    header("Location: user-login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Profile - Student</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .header {
            background: #007bff;
            color: white;
            padding: 20px 0;
            margin-bottom: 30px;
        }
        .logo {
            max-width: 150px;
        }
        .container-main {
            margin: 20px auto;
            max-width: 800px;
        }
        .profile-container {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }
        .profile-header {
            text-align: center;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e9ecef;
        }
        .profile-title {
            color: #333;
            margin-bottom: 10px;
        }
        .role-badge {
            background: #007bff;
            color: white;
            padding: 8px 20px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: bold;
        }
        .profile-info {
            margin: 30px 0;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #f8f9fa;
        }
        .info-label {
            font-weight: bold;
            color: #666;
            width: 150px;
        }
        .info-value {
            flex: 1;
            color: #333;
            font-size: 16px;
        }
        .btn-edit {
            background: #28a745;
            border: none;
            padding: 12px 30px;
            border-radius: 25px;
            font-weight: bold;
        }
        .btn-edit:hover {
            background: #218838;
        }
        .avatar {
            width: 100px;
            height: 100px;
            background: #007bff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 36px;
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
                <a href="student-dashboard.php" class="btn btn-light">Back to Dashboard</a>
                <a href="user-logout.php" class="btn btn-danger">Logout</a>
            </div>
        </div>
    </div>
</div>

<div class="container-main">
    <div class="profile-container">
        <div class="profile-header">
            <div class="avatar">
                <?php echo strtoupper(substr($student_data['U_name'], 0, 1)); ?>
            </div>
            <h2 class="profile-title">My Profile</h2>
            <span class="role-badge">Student</span>
        </div>
        
        <div class="profile-info">
            <div class="info-row">
                <div class="info-label">Full Name:</div>
                <div class="info-value"><?php echo htmlspecialchars($student_data['U_name']); ?></div>
            </div>
            
            <div class="info-row">
                <div class="info-label">Phone Number:</div>
                <div class="info-value"><?php echo htmlspecialchars($student_data['U_phoneNum']); ?></div>
            </div>
            
            <div class="info-row">
                <div class="info-label">Email Address:</div>
                <div class="info-value"><?php echo htmlspecialchars($student_data['U_email']); ?></div>
            </div>
            
            <div class="info-row">
                <div class="info-label">Role:</div>
                <div class="info-value">Student</div>
            </div>
        </div>
    </div>
</div>

</body>
</html>