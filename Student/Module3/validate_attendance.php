<?php
include '../includes/dbconnection.php';
session_start();

$currentDateTime = "2025-06-02 17:40:50";
$currentUser = "AthirahSN";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $slotId = $_POST['slot_id'];
    $studentId = $_POST['student_id'];
    $password = $_POST['password'];

    // First check if attendance already exists
    $checkQuery = "SELECT * FROM attendance WHERE S_slotID = ? AND U_userID = ?";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param("ii", $slotId, $studentId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Already checked in
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <title>Attendance Status</title>
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
            <style>
                body { padding: 20px; }
                .container { max-width: 500px; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="alert alert-warning">
                    <h4>Already Checked In</h4>
                    <p>You have already checked in for this event.</p>
                    <button class="btn btn-primary mt-3" onclick="window.close()">Close</button>
                </div>
            </div>
        </body>
        </html>
        <?php
    } else {
        // Get event details for the record
        $eventQuery = "SELECT e.*, a.* 
                      FROM event e 
                      JOIN attendanceslot a ON e.E_eventID = a.E_eventID 
                      WHERE a.S_slotID = ?";
        $stmt = $conn->prepare($eventQuery);
        $stmt->bind_param("i", $slotId);
        $stmt->execute();
        $eventResult = $stmt->get_result();
        $eventData = $eventResult->fetch_assoc();

        // Insert attendance record
        $insertQuery = "INSERT INTO attendance 
                       (A_checkinTime, U_userID, S_slotID, A_password, A_location) 
                       VALUES (?, ?, ?, ?, ?)";
              
        $stmt = $conn->prepare($insertQuery);
        $checkinTime = date('H:i:s', strtotime($currentDateTime));
        $location = $eventData['S_Location'];
        $stmt->bind_param("siiss", $checkinTime, $studentId, $slotId, $password, $location);
        
        if ($stmt->execute()) {
            ?>
            <!DOCTYPE html>
            <html>
            <head>
                <title>Attendance Status</title>
                <meta name="viewport" content="width=device-width, initial-scale=1">
                <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
                <style>
                    body { padding: 20px; }
                    .container { max-width: 500px; }
                </style>
            </head>
            <body>
                <div class="container">
                    <div class="card">
                        <div class="card-body">
                            <div class="alert alert-success">
                                <h4>Success!</h4>
                                <p>Your attendance has been recorded successfully.</p>
                            </div>
                            
                            <div class="mt-3">
                                <h5>Check-in Details:</h5>
                                <p><strong>Event:</strong> <?php echo htmlspecialchars($eventData['E_name']); ?></p>
                                <p><strong>Date:</strong> <?php echo $eventData['S_Date']; ?></p>
                                <p><strong>Time:</strong> <?php echo $checkinTime; ?></p>
                                <p><strong>Location:</strong> <?php echo htmlspecialchars($location); ?></p>
                            </div>

                            <button class="btn btn-primary mt-3" onclick="window.close()">Close</button>
                        </div>
                    </div>
                </div>
            </body>
            </html>
            <?php
        } else {
            ?>
            <!DOCTYPE html>
            <html>
            <head>
                <title>Attendance Status</title>
                <meta name="viewport" content="width=device-width, initial-scale=1">
                <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
                <style>
                    body { padding: 20px; }
                    .container { max-width: 500px; }
                </style>
            </head>
            <body>
                <div class="container">
                    <div class="alert alert-danger">
                        <h4>Error</h4>
                        <p>Failed to record attendance. Please try again.</p>
                        <button class="btn btn-primary mt-3" onclick="history.back()">Go Back</button>
                    </div>
                </div>
            </body>
            </html>
            <?php
        }
    }
}
?>