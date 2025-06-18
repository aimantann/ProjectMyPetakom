<?php
session_start();
include('includes/dbconnection.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $eventId = $_POST['E_eventID'];
    $userId = $_POST['U_userID'];
    $position = $_POST['C_position'];
    $semester = $_POST['ES_semester'];
    $currentDate = '2025-06-18 15:00:09'; // Specified UTC format
    $currentUser = 'izzrashzleen21';
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // First, check if this event-semester combination exists
        $checkSemesterQuery = "SELECT ES_id FROM eventsemester WHERE E_eventID = ? AND ES_semester = ?";
        $stmt = $conn->prepare($checkSemesterQuery);
        $stmt->bind_param("is", $eventId, $semester);
        $stmt->execute();
        $semesterResult = $stmt->get_result();
        
        if ($semesterResult->num_rows == 0) {
            // If not exists, insert new event semester
            $semesterQuery = "INSERT INTO eventsemester (ES_semester, E_eventID) VALUES (?, ?)";
            $stmt = $conn->prepare($semesterQuery);
            $stmt->bind_param("si", $semester, $eventId);
            $stmt->execute();
        }

        // Then, check if student is already assigned to this event
        $checkCommitteeQuery = "SELECT C_position FROM eventcommittee WHERE U_userID = ? AND E_eventID = ?";
        $stmt = $conn->prepare($checkCommitteeQuery);
        $stmt->bind_param("ii", $userId, $eventId);
        $stmt->execute();
        $committeeResult = $stmt->get_result();
        
        if ($committeeResult->num_rows > 0) {
            throw new Exception("Student is already assigned to this event");
        }

        // Insert into eventcommittee table
        $committeeQuery = "INSERT INTO eventcommittee (U_userID, E_eventID, C_position) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($committeeQuery);
        $stmt->bind_param("iis", $userId, $eventId, $position);
        
        if ($stmt->execute()) {
            // Get event level for merit calculation
            $eventLevelQuery = "SELECT E_level FROM event WHERE E_eventID = ?";
            $stmt = $conn->prepare($eventLevelQuery);
            $stmt->bind_param("i", $eventId);
            $stmt->execute();
            $result = $stmt->get_result();
            $event = $result->fetch_assoc();
            
            if (!$event) {
                throw new Exception("Event not found");
            }
            
            // Calculate merit points based on level and position
            $meritPoints = calculateMeritPoints($event['E_level'], $position);
            
            // Insert into meritclaim table with auto-approved status for committee roles
            $meritClaimQuery = "INSERT INTO meritclaim (U_userID, E_eventID, MC_role, MC_claimStatus) 
                               VALUES (?, ?, ?, 'Approved')";
            $stmt = $conn->prepare($meritClaimQuery);
            $stmt->bind_param("iis", $userId, $eventId, $position);
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to insert merit claim");
            }
            
            // Insert into meritawarded table
            $meritAwardedQuery = "INSERT INTO meritawarded (MD_meritPoint, MD_awardedDate, U_userID, E_eventID) 
                                 VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($meritAwardedQuery);
            $stmt->bind_param("isis", $meritPoints, $currentDate, $userId, $eventId);
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to insert merit points");
            }
            
            // Log the merit points award
            $logMessage = sprintf(
                "Merit points awarded by %s - Student: %d, Event: %d, Points: %d, Position: %s, Date: %s",
                $currentUser,
                $userId,
                $eventId,
                $meritPoints,
                $position,
                $currentDate
            );
            error_log($logMessage);
            
        } else {
            throw new Exception("Failed to assign committee member");
        }

        // Store the user ID for verification in success message
        $_SESSION['last_awarded_user'] = $userId;
        $_SESSION['last_merit_points'] = $meritPoints;
        $_SESSION['last_position'] = $position;

        // If everything is successful, commit the transaction
        $conn->commit();
        
        header("Location: CommitteeEvent.php?success=1");
        exit();
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        
        // Log the error with details
        $errorMessage = sprintf(
            "Error in committee assignment by %s - Error: %s, Student: %d, Event: %d, Position: %s, Date: %s",
            $currentUser,
            $e->getMessage(),
            $userId,
            $eventId,
            $position,
            $currentDate
        );
        error_log($errorMessage);
        
        // Redirect with appropriate error message
        if (strpos($e->getMessage(), "already assigned") !== false) {
            header("Location: CommitteeEvent.php?error=duplicate");
        } else {
            header("Location: CommitteeEvent.php?error=committee");
        }
        exit();
    }
}

function calculateMeritPoints($eventLevel, $position) {
    $meritPoints = [
        'International' => ['Main Committee' => 100, 'Committee' => 70],
        'National' => ['Main Committee' => 80, 'Committee' => 50],
        'State' => ['Main Committee' => 60, 'Committee' => 40],
        'District' => ['Main Committee' => 40, 'Committee' => 30],
        'UMPSA' => ['Main Committee' => 30, 'Committee' => 20]
    ];
    
    return $meritPoints[$eventLevel][$position] ?? 0;
}
?>