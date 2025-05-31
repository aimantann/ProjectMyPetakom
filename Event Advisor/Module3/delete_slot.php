<?php
include 'db.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $conn->prepare("DELETE FROM attendanceslot WHERE S_SlotID = ?");
    $stmt->bind_param("s", $id);
    $stmt->execute();
}

header("Location: view_attendanceslot.php");
exit();
?>