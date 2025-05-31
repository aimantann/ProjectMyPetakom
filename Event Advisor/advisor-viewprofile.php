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

// Get advisor data from unified user table
$query = "SELECT U_name, U_phoneNum, U_email FROM user WHERE U_email = ? AND U_usertype = 'event_advisor'";
$stmt = $conn->prepare($query);
$stmt->bind_param('s', $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $advisor_data = $result->fetch_assoc();
} else {
    // Handle case where advisor data is not found
    header("Location: user-login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($advisor_data['U_name']); ?> - Event Advisor Profile</title>
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
            background: #28a745;
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
            background: #007bff;
            border: none;
            padding: 12px 30px;
            border-radius: 25px;
            font-weight: bold;
        }
        .btn-edit:hover {
            background: #0056b3;
        }
        .avatar {
            width: 100px;
            height: 100px;
            background: #28a745;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 36px;
            color: white;
            font-weight: bold;
        }
        .success-message {
            margin-bottom: 20px;
        }
        .my-profile-label {
            font-weight: bold;
            color: #155724;
            font-size: 18px;
            margin-bottom: 5px;
            letter-spacing: 1px;
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
                <a href="advisor-dashboard.php" class="btn btn-light">Back to Dashboard</a>
                <a href="user-logout.php" class="btn btn-danger">Logout</a>
            </div>
        </div>
    </div>
</div>

<div class="container-main">
    <div class="profile-container">
        <?php
        if (isset($_SESSION['success_message'])) {
            echo "<div class='alert alert-success success-message'>" . $_SESSION['success_message'] . "</div>";
            unset($_SESSION['success_message']);
        }
        ?>
        
        <div class="profile-header">
            <div class="my-profile-label">My Profile</div>
            <div class="avatar">
                <?php echo strtoupper(substr($advisor_data['U_name'], 0, 1)); ?>
            </div>
            <h2 class="profile-title"><?php echo htmlspecialchars($advisor_data['U_name']); ?></h2>
            <span class="role-badge">Event Advisor</span>
        </div>
        
        <div class="profile-info">
            <div class="info-row">
                <div class="info-label">Full Name:</div>
                <div class="info-value"><?php echo htmlspecialchars($advisor_data['U_name']); ?></div>
            </div>
            
            <div class="info-row">
                <div class="info-label">Phone Number:</div>
                <div class="info-value"><?php echo htmlspecialchars($advisor_data['U_phoneNum']); ?></div>
            </div>
            
            <div class="info-row">
                <div class="info-label">Email Address:</div>
                <div class="info-value"><?php echo htmlspecialchars($advisor_data['U_email']); ?></div>
            </div>
            
            <div class="info-row">
                <div class="info-label">Role:</div>
                <div class="info-value">Event Advisor</div>
            </div>
        </div>
    </div>
</div>

</body>
</html>