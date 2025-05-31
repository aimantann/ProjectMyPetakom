<?php
include('../config/dbconnection.php');

if (isset($_GET['id'])) {
    $slot_id = $_GET['id'];

    // Fetch event details
    $stmt = $conn->prepare("SELECT * FROM attendanceslot WHERE S_SlotID = ?");
    $stmt->bind_param("s", $slot_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $event = $result->fetch_assoc();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Event Check-In</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <h2>Event Check-In</h2>
    <div class="card">
        <div class="card-body">
            <h5><strong>Event Name:</strong> <?= $event['S_Name'] ?></h5>
            <p><strong>Date:</strong> <?= $event['S_Date'] ?></p>
            <p><strong>Time:</strong> <?= $event['S_Time'] ?></p>
            <p><strong>Location:</strong> <?= $event['S_Location'] ?></p>
            <p><strong>Geolocation:</strong> Allow access to verify your location.</p>

            <!-- Manual Check-in Form -->
            <form action="validate_attendance.php" method="POST">
                <input type="hidden" name="slot_id" value="<?= $slot_id ?>">
                <div class="mb-3">
                    <label>Student ID</label>
                    <input type="text" name="student_id" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-success">Check In</button>
            </form>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
