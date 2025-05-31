<?php
include 'db.php';

$event_id = $_POST['S_SlotID'];
$event_name = $_POST['S_Name'];
$event_date = $_POST['S_Date'];
$event_time = $_POST['S_Time'];
$event_location = $_POST['S_Location'];

$sql = "INSERT INTO attendanceslot (S_SlotID, S_Name, S_Date, S_Time, S_Location)
        VALUES ('$S_SlotID', '$S_Name', '$S_Date', '$S_Time', '$S_Location')";

if ($conn->query($sql)) {
    header("Location: view_attendanceslot.php");
} else {
    echo "Error: " . $conn->error;
}
?>