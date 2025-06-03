<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $slotId = $_POST['slot_id'];
    $studentId = $_POST['student_id'];
    $name = $_POST['name'];
    $currentTime = date('Y-m-d H:i:s');

    // First check if this student has already marked attendance
    $checkQuery = "SELECT * FROM attendance WHERE S_slotID = ? AND student_id = ?";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param("is", $slotId, $studentId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Student has already marked attendance
        $message = "You have already marked your attendance for this event.";
        $status = "warning";
    } else {
        // Insert new attendance record
        $insertQuery = "INSERT INTO attendance (S_slotID, student_id, student_name, attendance_time) 
                       VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($insertQuery);
        $stmt->bind_param("isss", $slotId, $studentId, $name, $currentTime);
        
        if ($stmt->execute()) {
            $message = "Attendance marked successfully!";
            $status = "success";
        } else {
            $message = "Error marking attendance. Please try again.";
            $status = "danger";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Attendance Status</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { 
            background-color: #f8f9fa;
            padding: 20px;
        }
        .container { 
            max-width: 600px; 
            margin: 20px auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="card-body text-center">
                <div class="alert alert-<?php echo $status; ?>" role="alert">
                    <?php echo $message; ?>
                </div>
                <a href="#" class="btn btn-primary" onclick="window.close()">Close</a>
            </div>
        </div>
    </div>
</body>
</html>