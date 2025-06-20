<?php
require_once('user-validatesession.php');

include('includes/header.php');
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
                    // Get all users with their roles
                    $query = "SELECT u.*, COALESCE(sp.SP_Role, u.U_usertype) as role_type
                              FROM user u
                              LEFT JOIN staff s ON u.U_userID = s.U_userID
                              LEFT JOIN staffposition sp ON s.SP_ID = sp.SP_ID
                              ORDER BY role_type, u.U_name";
                    $result = $conn->query($query);

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
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='5' class='text-center'>No users found</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
include('includes/footer.php');
?>

</body>
</html>