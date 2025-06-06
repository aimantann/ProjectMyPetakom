<?php
require_once('includes/dbconnection.php');

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $conn->prepare("DELETE FROM attendanceslot WHERE S_slotID = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        header("Location: view_attendanceslot.php?success=3");
    } else {
        header("Location: view_attendanceslot.php?error=1");
    }
    exit();
} else {
    header("Location: view_attendanceslot.php");
    exit();
}
?>