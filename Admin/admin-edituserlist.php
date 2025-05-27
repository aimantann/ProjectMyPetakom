<?php
session_start();
include("includes/dbconnection.php");


// Handle delete action
if (isset($_GET['delete']) && isset($_GET['role']) && isset($_GET['email'])) {
    $email = $_GET['email'];
    $role = $_GET['role'];
    
    if ($role == 'advisor') {
        $delete_query = "DELETE FROM advisor WHERE advEmail = ?";
    } elseif ($role == 'student') {
        $delete_query = "DELETE FROM student WHERE stuEmail = ?";
    } elseif ($role == 'admin') {
        $delete_query = "DELETE FROM admin WHERE adminEmail = ?";
    }
    
    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param('s', $email);
    
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "User deleted successfully!";
    } else {
        $_SESSION['error_message'] = "Error deleting user.";
    }
    
    header("Location: admin-edituserlist.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Registered Users</title>
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
            max-width: 1400px;
        }
        .table-container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .page-title {
            color: #333;
            margin-bottom: 30px;
            text-align: center;
        }
        .role-badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: bold;
        }
        .role-admin { background: #dc3545; color: white; }
        .role-advisor { background: #28a745; color: white; }
        .role-student { background: #007bff; color: white; }
        .action-buttons {
            white-space: nowrap;
        }
        .btn-sm {
            margin: 2px;
        }
        .alert {
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
                <a href="admin-dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
                <a href="user-logout.php" class="btn btn-danger">Logout</a>
            </div>
        </div>
    </div>
</div>

<div class="container-main">
    <div class="table-container">
        <h2 class="page-title">Edit Registered Users</h2>
        
        <?php
        if (isset($_SESSION['success_message'])) {
            echo "<div class='alert alert-success'>" . $_SESSION['success_message'] . "</div>";
            unset($_SESSION['success_message']);
        }
        if (isset($_SESSION['error_message'])) {
            echo "<div class='alert alert-danger'>" . $_SESSION['error_message'] . "</div>";
            unset($_SESSION['error_message']);
        }
        ?>
        
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead class="thead-dark">
                    <tr>
                        <th>No.</th>
                        <th>Full Name</th>
                        <th>Phone Number</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $counter = 1;
                    
                    // Get all advisors
                    $advisor_query = "SELECT advName as name, advPhoneNum as phone, advEmail as email, 'advisor' as role_type, 'Event Advisor' as role_display FROM advisor";
                    $advisor_result = $conn->query($advisor_query);
                    
                    if ($advisor_result->num_rows > 0) {
                        while ($row = $advisor_result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . $counter++ . "</td>";
                            echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['phone']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                            echo "<td><span class='role-badge role-advisor'>" . $row['role_display'] . "</span></td>";
                            echo "<td class='action-buttons'>";
                            echo "<a href='admin-edituserform.php?email=" . urlencode($row['email']) . "&role=" . $row['role_type'] . "' class='btn btn-warning btn-sm'>Edit</a>";
                            echo "<a href='admin-edituserlist.php?delete=1&email=" . urlencode($row['email']) . "&role=" . $row['role_type'] . "' class='btn btn-danger btn-sm' onclick='return confirm(\"Are you sure you want to delete this user?\")'>Delete</a>";
                            echo "</td>";
                            echo "</tr>";
                        }
                    }
                    
                    // Get all students
                    $student_query = "SELECT stuName as name, stuPhoneNum as phone, stuEmail as email, 'student' as role_type, 'Student' as role_display FROM student";
                    $student_result = $conn->query($student_query);
                    
                    if ($student_result->num_rows > 0) {
                        while ($row = $student_result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . $counter++ . "</td>";
                            echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['phone']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                            echo "<td><span class='role-badge role-student'>" . $row['role_display'] . "</span></td>";
                            echo "<td class='action-buttons'>";
                            echo "<a href='admin-edituserform.php?email=" . urlencode($row['email']) . "&role=" . $row['role_type'] . "' class='btn btn-warning btn-sm'>Edit</a>";
                            echo "<a href='admin-edituserlist.php?delete=1&email=" . urlencode($row['email']) . "&role=" . $row['role_type'] . "' class='btn btn-danger btn-sm' onclick='return confirm(\"Are you sure you want to delete this user?\")'>Delete</a>";
                            echo "</td>";
                            echo "</tr>";
                        }
                    }
                    
                    // Get all admins (excluding current admin)
                    $admin_query = "SELECT adminEmail as email, 'admin' as role_type, 'Administrator' as role_display FROM admin WHERE adminEmail != ?";
                    $stmt = $conn->prepare($admin_query);
                    $stmt->bind_param('s', $_SESSION['email']);
                    $stmt->execute();
                    $admin_result = $stmt->get_result();
                    
                    if ($admin_result->num_rows > 0) {
                        while ($row = $admin_result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . $counter++ . "</td>";
                            echo "<td>N/A</td>";
                            echo "<td>N/A</td>";
                            echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                            echo "<td><span class='role-badge role-admin'>" . $row['role_display'] . "</span></td>";
                            echo "<td class='action-buttons'>";
                            echo "<a href='admin-edituserform.php?email=" . urlencode($row['email']) . "&role=" . $row['role_type'] . "' class='btn btn-warning btn-sm'>Edit</a>";
                            echo "<a href='admin-edituserlist.php?delete=1&email=" . urlencode($row['email']) . "&role=" . $row['role_type'] . "' class='btn btn-danger btn-sm' onclick='return confirm(\"Are you sure you want to delete this user?\")'>Delete</a>";
                            echo "</td>";
                            echo "</tr>";
                        }
                    }
                    
                    if ($counter == 1) {
                        echo "<tr><td colspan='6' class='text-center'>No users found</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>