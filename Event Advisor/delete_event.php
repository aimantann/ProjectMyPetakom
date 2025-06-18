<?php
require_once('user-validatesession.php');

include('includes/dbconnection.php');

if (isset($_GET['id'])) {
    $eventId = (int)$_GET['id'];
    
    try {
        // Start transaction
        $conn->begin_transaction();
        
        // First delete related records in eventcommittee
        $sql1 = "DELETE FROM eventcommittee WHERE E_eventID = ?";
        $stmt1 = $conn->prepare($sql1);
        $stmt1->bind_param("i", $eventId);
        $stmt1->execute();
        
        // Then delete the event
        $sql2 = "DELETE FROM event WHERE E_eventID = ?";
        $stmt2 = $conn->prepare($sql2);
        $stmt2->bind_param("i", $eventId);
        $stmt2->execute();
        
        // Commit transaction
        $conn->commit();
        
        $_SESSION['message'] = "Event deleted successfully";
        $_SESSION['message_type'] = "success";
    } catch (Exception $e) {
        // Rollback on error
        $conn->rollback();
        $_SESSION['message'] = "Error deleting event: " . $e->getMessage();
        $_SESSION['message_type'] = "danger";
    }
}

header("Location: EventList.php");
exit();
?>