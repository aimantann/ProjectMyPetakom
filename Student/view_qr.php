<?php
session_start();
require_once('includes/dbconnection.php');

// Get the slot ID from URL
$slotId = isset($_GET['id']) ? $_GET['id'] : null;

if (!$slotId) {
    header("Location: student-viewevents.php");
    exit();
}

// Get event details
$query = "SELECT e.E_name, a.S_Date, a.S_startTime, a.S_endTime 
          FROM event e 
          JOIN attendanceslot a ON e.E_eventID = a.E_eventID 
          WHERE a.S_slotID = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $slotId);
$stmt->execute();
$result = $stmt->get_result();
$eventData = $result->fetch_assoc();


// Updated QR code path with correct directory
$qrCodePath = "../Event Advisor/qr_codes/slot_" . $slotId . ".png";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View QR Code - <?php echo htmlspecialchars($eventData['E_name']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #f8f9fa;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .header {
            background-color: #fff;
            padding: 1rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .qr-container {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 2rem;
        }
        .qr-code-img {
            max-width: 80%;
            height: auto;
            padding: 20px;
            background: white;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            border-radius: 10px;
        }
        .datetime-info {
            font-size: 0.9rem;
            color: #6c757d;
        }
        .back-button {
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 1000;
        }
        @media (max-width: 768px) {
            .qr-code-img {
                max-width: 100%;
            }
        }
        .event-info {
            margin-top: 1rem;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="back-button">
        <a href="javascript:history.back()" class="btn btn-outline-primary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>

    <div class="header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col">
                    <h4 class="mb-0"><?php echo htmlspecialchars($eventData['E_name']); ?></h4>
                    <div class="event-info">
                        <?php echo date('F j, Y', strtotime($eventData['S_Date'])); ?> | 
                        <?php echo date('h:i A', strtotime($eventData['S_startTime'])) . ' - ' . 
                              date('h:i A', strtotime($eventData['S_endTime'])); ?>
                    </div>
                </div>
                <div class="col-auto">
                    <div class="datetime-info text-end">

                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="qr-container">
        <div class="text-center">
            <img src="<?php echo $qrCodePath; ?>" 
                 class="qr-code-img mb-3" 
                 alt="QR Code for <?php echo htmlspecialchars($eventData['E_name']); ?>">
            
            <div class="mt-3">
                <a href="<?php echo $qrCodePath; ?>" 
                   class="btn btn-primary"
                   download="event_<?php echo $slotId; ?>_qr.png">
                    <i class="bi bi-download"></i> Download QR Code
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>