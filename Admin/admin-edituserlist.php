<?php
session_start();
include("includes/dbconnection.php");

// Handle delete action
if (isset($_GET['delete']) && isset($_GET['id'])) {
    $user_id = $_GET['id'];

    // Delete from specific tables first to preserve referential integrity
    $delete_staff = "DELETE FROM staff WHERE U_userID = ?";
    $stmt = $conn->prepare($delete_staff);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();

    $delete_student = "DELETE FROM student WHERE U_userID = ?";
    $stmt = $conn->prepare($delete_student);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();

    // Delete from user table
    $delete_user = "DELETE FROM user WHERE U_userID = ?";
    $stmt = $conn->prepare($delete_user);
    $stmt->bind_param('i', $user_id);

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
                    // Get all users with their roles
                    $query = "SELECT u.*, COALESCE(sp.SP_Role, u.U_usertype) as role_type
                              FROM user u
                              LEFT JOIN staff s ON u.U_userID = s.U_userID
                              LEFT JOIN staffposition sp ON s.SP_ID = sp.SP_ID
                              WHERE u.U_userID != ?
                              ORDER BY role_type, u.U_name";
                    $stmt = $conn->prepare($query);
                    $stmt->bind_param('i', $_SESSION['user_id']);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $role_display = '';
                            $role_class = '';
                            if ($row['role_type'] == 'admin') {
                                $role_display = 'Administrator';
                                $role_class = 'role-admin';
                            } elseif ($row['role_type'] == 'event_advisor') {
                                $role_display = 'Event Advisor';
                                $role_class = 'role-advisor';
                            } else {
                                $role_display = 'Student';
                                $role_class = 'role-student';
                            }

                            echo "<tr>";
                            echo "<td>" . $counter++ . "</td>";
                            echo "<td>" . htmlspecialchars($row['U_name'] ?? 'N/A') . "</td>";
                            echo "<td>" . htmlspecialchars($row['U_phoneNum'] ?? 'N/A') . "</td>";
                            echo "<td>" . htmlspecialchars($row['U_email']) . "</td>";
                            echo "<td><span class='role-badge $role_class'>" . $role_display . "</span></td>";
                            echo "<td class='action-buttons'>";
                            echo "<a href='admin-edituserform.php?id=" . urlencode($row['U_userID']) . "' class='btn btn-warning btn-sm'>Edit</a>";
                            echo "<a href='admin-edituserlist.php?delete=1&id=" . urlencode($row['U_userID']) . "' class='btn btn-danger btn-sm' onclick='return confirm(\"Are you sure you want to delete this user?\")'>Delete</a>";
                            echo "</td>";
                            echo "</tr>";
                        }
                    } else {
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