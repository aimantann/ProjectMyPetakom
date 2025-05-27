<?php
session_start();
require_once '../includes/dbconnection.php';

if (isset($_GET['id'])) {
    $eventId = (int)$_GET['id'];
    
    // Delete from database
    $sql = "DELETE FROM event WHERE E_eventID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $eventId);
    $stmt->execute();
    
    // Set success message
    $_SESSION['message'] = "Event deleted successfully";
    $_SESSION['message_type'] = "success";
}

// Redirect back to event list
header("Location: EventList.php");
exit();
?>