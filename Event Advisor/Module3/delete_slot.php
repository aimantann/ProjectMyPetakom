<?php
include 'db.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $conn->prepare("DELETE FROM attendanceslot WHERE S_slotID = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
}

header("Location: view_attendanceslot.php");
exit();
?>