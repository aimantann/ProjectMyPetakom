<?php
require_once('includes/dbconnection.php');

if (isset($_GET['slot_id'])) {
    $slotId = $_GET['slot_id'];
    $check = $conn->prepare("SELECT COUNT(*) FROM attendance WHERE S_slotID = ?");
    $check->bind_param("i", $slotId);
    $check->execute();
    $check->bind_result($count);
    $check->fetch();
    
    echo json_encode(['count' => $count]);
    exit();
}
?>