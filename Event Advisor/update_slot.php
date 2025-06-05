<?php
include 'dbconnection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $slot_id = $_POST['slot_id'];
    $slotName = $_POST['slot_name'];
    $slotDate = $_POST['slot_date'];
    $startTime = $_POST['start_time'];
    $endTime = $_POST['end_time'];
    $location = $_POST['location'];
    $eventId = $_POST['event_id'];

    // Using prepared statement for security
    $stmt = $conn->prepare("UPDATE attendanceslot SET 
            S_Name = ?,
            S_Date = ?,
            S_startTime = ?, 
            S_endTime = ?, 
            S_Location = ?,
            E_eventID = ? 
            WHERE S_slotID = ?");
    
    // Bind parameters
    $stmt->bind_param("sssssii", $slotName, $slotDate, $startTime, $endTime, $location, $eventId, $slot_id);

    if ($stmt->execute()) {
        header("Location: view_attendanceslot.php?success=2"); // 2 indicates successful update
        exit();
    } else {
        echo "Error updating record: " . $conn->error;
    }
    
    $stmt->close();
} else {
    header("Location: view_attendanceslot.php");
    exit();
}
?>