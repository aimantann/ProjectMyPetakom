<?php
require_once('includes/dbconnection.php');

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    try {
        // Start transaction
        $conn->begin_transaction();
        
        // First delete related attendance records
        $deleteAttendance = $conn->prepare("DELETE FROM attendance WHERE S_slotID = ?");
        $deleteAttendance->bind_param("i", $id);
        $deleteAttendance->execute();
        
        // Then delete the slot
        $deleteSlot = $conn->prepare("DELETE FROM attendanceslot WHERE S_slotID = ?");
        $deleteSlot->bind_param("i", $id);
        $deleteSlot->execute();
        
        // Commit transaction
        $conn->commit();
        
        header("Location: view_attendanceslot.php?success=3");
    } catch (Exception $e) {
        // Rollback if error occurs
        $conn->rollback();
        header("Location: view_attendanceslot.php?error=1&message=" . urlencode($e->getMessage()));
    }
    exit();
} else {
    header("Location: view_attendanceslot.php");
    exit();
}
?>