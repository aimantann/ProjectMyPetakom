<?php
session_start();
require_once('includes/dbconnection.php');

// Handle GET request when accessed directly or through QR code
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $slotId = isset($_GET['slot']) ? $_GET['slot'] : null;
    
    if (!$slotId) {
        die("Invalid attendance slot");
    }
    
    // Get slot details
    $slotQuery = "SELECT e.E_name, a.S_Date, a.S_startTime, a.S_endTime, a.S_Location, e.E_description 
                 FROM event e 
                 JOIN attendanceslot a ON e.E_eventID = a.E_eventID 
                 WHERE a.S_slotID = ?";
    $stmt = $conn->prepare($slotQuery);
    $stmt->bind_param("i", $slotId);
    $stmt->execute();
    $eventData = $stmt->get_result()->fetch_assoc();

    if (!$eventData) {
        die("Invalid attendance slot");
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Event Check-in</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f5f5f5;
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 20px;
        }
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin: 0 auto;
            max-width: 500px;
        }
        .card-header {
            background: linear-gradient(135deg, #0d6efd, #0dcaf0);
            color: white;
            padding: 20px;
            border-radius: 10px 10px 0 0;
            text-align: center;
        }
        .event-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0"><?= htmlspecialchars($eventData['E_name']) ?></h4>
            </div>
            <div class="card-body">
                <div class="event-info">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Date:</strong><br>
                            <?= date('F j, Y', strtotime($eventData['S_Date'])) ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Time:</strong><br>
                            <?= date('h:i A', strtotime($eventData['S_startTime'])) ?> - 
                            <?= date('h:i A', strtotime($eventData['S_endTime'])) ?></p>
                        </div>
                    </div>
                    <p><strong>Location:</strong><br>
                    <?= htmlspecialchars($eventData['S_Location']) ?></p>
                    <?php if (!empty($eventData['E_description'])): ?>
                    <p><strong>Description:</strong><br>
                    <?= htmlspecialchars($eventData['E_description']) ?></p>
                    <?php endif; ?>
                </div>

                <form action="<?php echo $_SERVER['PHP_SELF']; ?>?slot=<?php echo $slotId; ?>" method="POST">
                    <input type="hidden" name="slot" value="<?= $slotId ?>">
                    
                    <div class="mb-3">
                        <label class="form-label">ID</label>
                        <input type="email" class="form-control" name="email" required 
                               placeholder="Enter your email">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" class="form-control" name="password" required 
                               placeholder="Enter your password">
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100">Check In</button>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
    exit();
}

// Handle POST request for form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $slotId = $_POST['slot'];
    
    // Get user data using email
    $userQuery = "SELECT * FROM user WHERE U_email = ?";
    $userStmt = $conn->prepare($userQuery);
    $userStmt->bind_param("s", $email);
    $userStmt->execute();
    $userResult = $userStmt->get_result();
    
    if ($userResult->num_rows === 0) {
        echo "<script>
            alert('Email not found. Please try again.');
            window.history.back();
        </script>";
        exit();
    }
    
    $userData = $userResult->fetch_assoc();
    
    // Verify password using password_verify()
    if (password_verify($password, $userData['U_password'])) {
        $userId = $userData['U_userID'];
        
        // Check if already checked in
        $checkQuery = "SELECT * FROM attendance WHERE S_slotID = ? AND U_userID = ?";
        $checkStmt = $conn->prepare($checkQuery);
        $checkStmt->bind_param("ii", $slotId, $userId);
        $checkStmt->execute();
        
        if ($checkStmt->get_result()->num_rows > 0) {
            echo "<script>
                alert('You have already checked in for this event.');
                window.location.href = 'student-dashboard.php';
            </script>";
            exit();
        }

        // Insert attendance record
        $insertQuery = "INSERT INTO attendance (A_checkinTime, A_location, A_password, U_userID, S_slotID) 
                       SELECT NOW(), S_Location, ?, ?, ? 
                       FROM attendanceslot 
                       WHERE S_slotID = ?";
        $insertStmt = $conn->prepare($insertQuery);
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $insertStmt->bind_param("siii", $hashedPassword, $userId, $slotId, $slotId);
        
        if ($insertStmt->execute()) {
            // Redirect to student checkin success page
            header("Location: checkin_success.php?slot=" . $slotId);
            exit();
        } else {
            echo "<script>
                alert('Error recording attendance. Please try again.');
                window.history.back();
            </script>";
            exit();
        }
    } else {
        echo "<script>
            alert('Invalid password. Please try again.');
            window.history.back();
        </script>";
        exit();
    }
}
?>