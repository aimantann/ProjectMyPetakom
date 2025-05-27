<?php
session_start();
include("includes/dbconnection.php");


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Registered Users List</title>
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
            max-width: 1200px;
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
        <h2 class="page-title">Registered Users List</h2>
        
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead class="thead-dark">
                    <tr>
                        <th>No.</th>
                        <th>Full Name</th>
                        <th>Phone Number</th>
                        <th>Email</th>
                        <th>Role</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $counter = 1;
                    
                    // Get all advisors
                    $advisor_query = "SELECT advName as name, advPhoneNum as phone, advEmail as email, 'Event Advisor' as role FROM advisor";
                    $advisor_result = $conn->query($advisor_query);
                    
                    if ($advisor_result->num_rows > 0) {
                        while ($row = $advisor_result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . $counter++ . "</td>";
                            echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['phone']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                            echo "<td><span class='role-badge role-advisor'>" . $row['role'] . "</span></td>";
                            echo "</tr>";
                        }
                    }
                    
                    // Get all students
                    $student_query = "SELECT stuName as name, stuPhoneNum as phone, stuEmail as email, 'Student' as role FROM student";
                    $student_result = $conn->query($student_query);
                    
                    if ($student_result->num_rows > 0) {
                        while ($row = $student_result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . $counter++ . "</td>";
                            echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['phone']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                            echo "<td><span class='role-badge role-student'>" . $row['role'] . "</span></td>";
                            echo "</tr>";
                        }
                    }
                    
                    // Get all admins
                    $admin_query = "SELECT adminEmail as email, 'Administrator' as role FROM admin";
                    $admin_result = $conn->query($admin_query);
                    
                    if ($admin_result->num_rows > 0) {
                        while ($row = $admin_result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . $counter++ . "</td>";
                            echo "<td>N/A</td>";
                            echo "<td>N/A</td>";
                            echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                            echo "<td><span class='role-badge role-admin'>" . $row['role'] . "</span></td>";
                            echo "</tr>";
                        }
                    }
                    
                    if ($counter == 1) {
                        echo "<tr><td colspan='5' class='text-center'>No users found</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>