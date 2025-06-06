<?php
session_start();
require_once('includes/dbconnection.php');
require_once('includes/header.php');

if (isset($_GET['slot'])) {
    $slotId = $_GET['slot'];
    
    // Validate slot exists and get event information
    $query = "SELECT e.E_name, e.E_description, a.S_Date, a.S_startTime, a.S_endTime, a.S_Location 
              FROM event e 
              JOIN attendanceslot a ON e.E_eventID = a.E_eventID 
              WHERE a.S_slotID = ? AND a.S_Date >= CURDATE()";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $slotId);
    $stmt->execute();
    $result = $stmt->get_result();
    $eventData = $result->fetch_assoc();

    if (!$eventData) {
        die("Invalid or expired event slot");
    }

    // Check if current time is within slot time
    $currentTime = date('H:i:s');
    $startTime = date('H:i:s', strtotime($eventData['S_startTime']));
    $endTime = date('H:i:s', strtotime($eventData['S_endTime']));
    
    if ($currentTime < $startTime || $currentTime > $endTime) {
        die("This attendance slot is not currently active. Available from $startTime to $endTime");
    }
?>
<!DOCTYPE html>
<html>
<head>
    <title>Event Check-in</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { 
            padding: 20px; 
            background-color: #f8f9fa; 
        }
        .container { 
            max-width: 500px; 
            margin: 0 auto; 
        }
        .card { 
            border-radius: 10px; 
            box-shadow: 0 4px 6px rgba(0,0,0,0.1); 
        }
        .event-header { 
            background-color: #f1f8ff; 
            padding: 15px; 
            border-radius: 8px 8px 0 0; 
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="event-header">
                <h3 class="text-center mb-2">Event Check-in</h3>
                <p class="text-center text-muted">Please verify your identity</p>
            </div>
            
            <div class="card-body">
                <div class="mb-4">
                    <h4><?php echo htmlspecialchars($eventData['E_name']); ?></h4>
                    <p class="text-muted"><?php echo htmlspecialchars($eventData['E_description']); ?></p>
                    
                    <div class="d-flex justify-content-between mt-3">
                        <div>
                            <p class="mb-1"><strong>Date</strong></p>
                            <p><?php echo date('F j, Y', strtotime($eventData['S_Date'])); ?></p>
                        </div>
                        <div>
                            <p class="mb-1"><strong>Time</strong></p>
                            <p><?php echo date('h:i A', strtotime($eventData['S_startTime'])) . ' - ' . 
                                  date('h:i A', strtotime($eventData['S_endTime'])); ?></p>
                        </div>
                    </div>
                    
                    <p class="mb-1"><strong>Location</strong></p>
                    <p><?php echo htmlspecialchars($eventData['S_Location']); ?></p>
                </div>

                <form action="validate_attendance.php" method="POST" id="attendanceForm">
                    <input type="hidden" name="slot" value="<?php echo $slotId; ?>">
                    
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" required 
                               placeholder="Enter your email">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" class="form-control" name="password" required 
                               placeholder="Enter your password">
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100 py-2">
                        Check In
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
} else {
    die("Invalid QR code. Please scan a valid attendance QR code.");
}
?>