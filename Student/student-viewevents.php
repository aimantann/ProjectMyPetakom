<?php
session_start();
require_once('includes/dbconnection.php');
require_once('includes/header.php');

// Get all active events with their QR codes
$query = "SELECT e.*, a.S_qrCode, a.S_slotID, a.S_qrStatus, a.S_startTime, a.S_endTime, a.S_Date, a.S_Location 
          FROM event e 
          JOIN attendanceslot a ON e.E_eventID = a.E_eventID 
          WHERE a.S_qrStatus = 'Generated' 
          ORDER BY a.S_Date DESC, a.S_startTime ASC";

$result = $conn->query($query);

// Function to get QR code path - Updated with correct path
function getQRCodePath($slotId) {
    return "../Event Advisor/qr_codes/slot_" . $slotId . ".png";
}

// Function to check if QR code exists
function qrCodeExists($slotId) {
    $qrPath = "../Event Advisor/qr_codes/slot_" . $slotId . ".png";
    return file_exists($qrPath);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Available Events</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        .event-card {
            transition: all 0.3s ease;
            margin-bottom: 20px;
        }
        .event-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .qr-code-img {
            max-width: 150px;
            height: auto;
            margin: 10px auto;
            border: 1px solid #ddd;
            padding: 5px;
            background-color: white;
        }
        .event-details {
            margin-bottom: 15px;
        }
        .qr-actions {
            margin-top: 10px;
            display: flex;
            justify-content: center;
            gap: 10px;
        }
        .btn-download {
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
    </style>
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Available Events</h2>
    </div>

    <div class="mb-4">
        <a href="student-dashboard.php" class="btn btn-outline-primary">← Back to Dashboard</a>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-8">
            <?php 
            if ($result && $result->num_rows > 0): 
                while($row = $result->fetch_assoc()): 
                    $qrCodePath = getQRCodePath($row['S_slotID']);
                    $hasQRCode = qrCodeExists($row['S_slotID']);
            ?>
                <div class="card event-card">
                    <div class="card-body">
                        <div class="event-details">
                            <h5 class="card-title"><?php echo htmlspecialchars($row['E_name']); ?></h5>
                            <p class="card-text">
                                <strong>Description:</strong> <?php echo htmlspecialchars($row['E_description']); ?><br>
                                <strong>Location:</strong> <?php echo htmlspecialchars($row['S_Location']); ?><br>
                                <strong>Date:</strong> <?php echo date('F j, Y', strtotime($row['S_Date'])); ?><br>
                                <strong>Time:</strong> <?php echo date('h:i A', strtotime($row['S_startTime'])) . ' - ' . 
                                                           date('h:i A', strtotime($row['S_endTime'])); ?>
                            </p>
                        </div>

                        <div class="text-center">
                            <?php if ($hasQRCode): ?>
                                <img src="<?php echo $qrCodePath; ?>" 
                                     class="qr-code-img" 
                                     alt="QR Code for <?php echo htmlspecialchars($row['E_name']); ?>"
                                     onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                                
                                <div class="qr-actions">
                                    <a href="<?php echo $qrCodePath; ?>" 
                                       class="btn btn-primary btn-download" 
                                       download="event_<?php echo $row['S_slotID']; ?>_qr.png">
                                        <i class="bi bi-download"></i> Download QR Code
                                    </a>
                                    <a href="view_qr.php?id=<?php echo $row['S_slotID']; ?>" 
                                       class="btn btn-outline-secondary">
                                        <i class="bi bi-arrows-fullscreen"></i> View Full Size
                                    </a>
                                </div>
                                
                                <div class="mt-2">
                                    <small class="text-muted">Scan QR code to check in</small>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-info">
                                    <p class="mb-0">QR code not available for this event.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php 
                endwhile; 
            else: 
            ?>
                <div class="alert alert-info">No events available at the moment.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>