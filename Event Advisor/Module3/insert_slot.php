<?php
include 'db.php';

// Get current date and time
$currentDateTime = "2025-05-31 12:47:32"; // Using the provided UTC time
$currentUser = 'AthirahSN'; // Current logged in user

// Check if the request method is POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get values from POST request
    $slotName = $_POST['S_Name'];
    $slotDate = $_POST['S_Date'];
    $startTime = $_POST['S_startTime'];
    $endTime = $_POST['S_endTime'];
    $location = $_POST['S_Location'];
    $eventId = $_POST['E_eventID'];
    
    // Using prepared statement for security
    $stmt = $conn->prepare("INSERT INTO attendanceslot 
            (S_Name, S_Date, S_startTime, S_endTime, S_Location, S_qrCode, E_eventID) 
            VALUES (?, ?, ?, ?, ?, NULL, ?)");
    
    // Bind parameters
    $stmt->bind_param("sssssi", $slotName, $slotDate, $startTime, $endTime, $location, $eventId);

    if ($stmt->execute()) {
        // Log the successful insertion
        $last_id = $conn->insert_id;
        error_log("[$currentDateTime] User $currentUser created new attendance slot ID: $last_id");
        
        header("Location: view_attendanceslot.php?success=1");
        exit();
    } else {
        error_log("[$currentDateTime] Error: Failed to create attendance slot by user $currentUser - " . $conn->error);
        echo "Error: " . $conn->error;
    }

    $stmt->close();
} else {
    // If not POST request, redirect to the view page
    header("Location: view_attendanceslot.php");
    exit();
}

$conn->close();
?>