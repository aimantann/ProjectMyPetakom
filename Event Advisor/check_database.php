<?php
include 'dbconnection.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Database Check</h2>";

$query = "SELECT a.S_slotID, a.S_qrCode, a.S_qrStatus, e.E_name 
          FROM attendanceslot a 
          JOIN event e ON a.E_eventID = e.E_eventID 
          WHERE a.S_slotID = 9";  // Replace with your slot ID

$result = $conn->query($query);

if ($result && $row = $result->fetch_assoc()) {
    echo "<pre>";
    print_r($row);
    echo "</pre>";
} else {
    echo "No data found";
}
?>