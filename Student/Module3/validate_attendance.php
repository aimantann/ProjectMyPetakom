<?php
include('../config/dbconnection.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $student_id = $_POST['student_id'];
    $password = $_POST['password'];
    $slot_id = $_POST['slot_id'];
    $geolocation = "Lat: 3.123, Long: 101.456"; // Mock geolocation (use Geolocation API in real case)

    // Verify student (Module 1 Integration)
    $sql = "SELECT * FROM student WHERE student_id = ? AND password = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $student_id, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Record attendance
        $insert = "INSERT INTO attendance (slot_id, student_id, geolocation) VALUES (?, ?, ?)";
        $stmt2 = $conn->prepare($insert);
        $stmt2->bind_param("iss", $slot_id, $student_id, $geolocation);
        $stmt2->execute();

        header("Location: attendance_dashboard.php?success=1");
    } else {
        header("Location: scan_qr.php?id=$slot_id&error=1");
    }
}
?>
