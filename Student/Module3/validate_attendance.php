<?php
include '../includes/dbconnection.php';
session_start();

// Handle GET request when accessed directly or through QR code
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $slotId = isset($_GET['slot']) ? $_GET['slot'] : null;
    
    if (!$slotId) {
        die("Invalid attendance slot");
    }
    
    // Get slot details
    $slotQuery = "SELECT e.E_name, a.S_Date, a.S_startTime, a.S_endTime, a.S_Location 
                 FROM event e JOIN attendanceslot a ON e.E_eventID = a.E_eventID 
                 WHERE a.S_slotID = ?";
    $stmt = $conn->prepare($slotQuery);
    $stmt->bind_param("i", $slotId);
    $stmt->execute();
    $eventData = $stmt->get_result()->fetch_assoc();

    if (!$eventData) {
        die("Invalid attendance slot");
    }

    // Get current UTC datetime
    $currentDateTime = gmdate('Y-m-d H:i:s');
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
        .container {
            max-width: 100%;
            padding: 15px;
        }
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin: 0 auto;
            max-width: 500px;
            background: white;
        }
        .card-header {
            background: linear-gradient(135deg, #0d6efd, #0dcaf0);
            color: white;
            padding: 20px;
            border-radius: 10px 10px 0 0;
            text-align: center;
        }
        .card-body {
            padding: 20px;
        }
        .event-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .info-label {
            color: #6c757d;
            font-weight: 600;
            margin-bottom: 5px;
        }
        .info-value {
            font-size: 1.1rem;
            margin-bottom: 15px;
            color: #333;
        }
        .form-control {
            height: 45px;
            border-radius: 8px;
            border: 2px solid #e9ecef;
            padding: 10px 15px;
        }
        .form-control:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
        }
        .btn-check-in {
            height: 45px;
            font-size: 1.1rem;
            font-weight: 500;
        }
        .datetime-info {
            text-align: center;
            padding: 10px;
            background: #e9ecef;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.9rem;
        }
        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }
            .card {
                margin: 10px;
            }
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
                <div class="datetime-info">
                    <div>Current Date and Time (UTC):</div>
                    <strong><?= $currentDateTime ?></strong>
                    <?php if(isset($_SESSION['login_id'])): ?>
                    <div class="mt-2">Logged in as: <?= htmlspecialchars($_SESSION['login_id']) ?></div>
                    <?php endif; ?>
                </div>
                
                <div class="event-info">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-label">Date</div>
                            <div class="info-value">
                                <?= date('F j, Y', strtotime($eventData['S_Date'])) ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-label">Time</div>
                            <div class="info-value">
                                <?= date('h:i A', strtotime($eventData['S_startTime'])) ?> - 
                                <?= date('h:i A', strtotime($eventData['S_endTime'])) ?>
                            </div>
                        </div>
                    </div>
                    <div class="info-label">Location</div>
                    <div class="info-value">
                        <?= htmlspecialchars($eventData['S_Location']) ?>
                    </div>
                </div>

                <form action="<?php echo $_SERVER['PHP_SELF']; ?>?slot=<?php echo $slotId; ?>" method="POST">
                    <input type="hidden" name="slot" value="<?= $slotId ?>">
                    
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" 
                               required placeholder="Enter your email">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" class="form-control" name="password" 
                               required placeholder="Enter your password">
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100 btn-check-in">
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
    exit();
}

// Handle POST request for form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $slotId = $_POST['slot'];
    
    // First get the user ID using email
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
    
    // Verify password using password_verify
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
                window.location.href = 'index.php';
            </script>";
            exit();
        }

        // Get current UTC datetime
        $currentDateTime = gmdate('Y-m-d H:i:s');

        // Get slot details for location
        $slotQuery = "SELECT S_Location FROM attendanceslot WHERE S_slotID = ?";
        $slotStmt = $conn->prepare($slotQuery);
        $slotStmt->bind_param("i", $slotId);
        $slotStmt->execute();
        $slotData = $slotStmt->get_result()->fetch_assoc();

        // Insert attendance record with hashed password
        $insertQuery = "INSERT INTO attendance (A_checkinTime, A_location, A_password, U_userID, S_slotID) 
                       VALUES (?, ?, ?, ?, ?)";
        $insertStmt = $conn->prepare($insertQuery);
        $insertStmt->bind_param("sssii", $currentDateTime, $slotData['S_Location'], $userData['U_password'], $userId, $slotId);
        
        if ($insertStmt->execute()) {
            $_SESSION['login_id'] = $userId;
            header("Location: checkin_success.php?slot=" . $slotId);
            exit();
        } else {
            echo "<script>
                alert('Error recording attendance: " . $conn->error . "');
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