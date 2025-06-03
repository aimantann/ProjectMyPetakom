<?php
include '../includes/dbconnection.php';

if (isset($_GET['slot'])) {
    $slotId = $_GET['slot'];
    
    // Get event information
    $query = "SELECT e.*, a.* 
              FROM event e 
              JOIN attendanceslot a ON e.E_eventID = a.E_eventID 
              WHERE a.S_slotID = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $slotId);
    $stmt->execute();
    $result = $stmt->get_result();
    $eventData = $result->fetch_assoc();

    if (!$eventData) {
        die("Invalid event slot");
    }
?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Event Check-in</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
        <style>
            body { padding: 20px; }
            .container { max-width: 500px; margin: 0 auto; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="card">
                <div class="card-body">
                    <h3 class="text-center mb-4">Event Check-in</h3>
                    
                    <div class="mb-4">
                        <h5><?php echo htmlspecialchars($eventData['E_name']); ?></h5>
                        <p><strong>Date:</strong> <?php echo $eventData['S_Date']; ?></p>
                        <p><strong>Time:</strong> 
                            <?php echo date('h:i A', strtotime($eventData['S_startTime'])) . ' - ' . 
                                  date('h:i A', strtotime($eventData['S_endTime'])); ?>
                        </p>
                        <p><strong>Location:</strong> <?php echo htmlspecialchars($eventData['S_Location']); ?></p>
                    </div>

                    <form action="validate_attendance.php" method="POST">
                        <input type="hidden" name="slot_id" value="<?php echo $slotId; ?>">
                        
                        <div class="mb-3">
                            <label class="form-label">Student ID</label>
                            <input type="text" class="form-control" name="student_id" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" class="form-control" name="password" required>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100">Check In</button>
                    </form>
                </div>
            </div>
        </div>
    </body>
    </html>
<?php
}
?>