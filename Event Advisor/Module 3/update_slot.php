<?php
include 'db.php';

$slot_id = $_POST['slot_id'];
$event_id = $_POST['event_id'];
$event_name = $_POST['event_name'];
$event_date = $_POST['event_date'];
$event_time = $_POST['event_time'];
$event_location = $_POST['event_location'];

$sql = "UPDATE attendance_slot SET 
        event_id='$event_id', event_name='$event_name', 
        event_date='$event_date', event_time='$event_time', 
        event_location='$event_location' 
        WHERE slot_id=$slot_id";

if ($conn->query($sql)) {
    header("Location: index.php");
} else {
    echo "Error: " . $conn->error;
}
?>
