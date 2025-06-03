<?php
include 'includes/dbconnection.php';

// Set current user and datetime
$currentDateTime = date('Y-m-d H:i:s');
$currentUser = "AthirahSN";

// Get all active events with their QR codes
$query = "SELECT e.*, a.S_qrCode, a.S_slotID, a.S_qrStatus, a.S_startTime, a.S_endTime, a.S_Date, a.S_Location 
          FROM event e 
          JOIN attendanceslot a ON e.E_eventID = a.E_eventID 
          WHERE a.S_qrStatus = 'Generated' 
          ORDER BY a.S_Date DESC, a.S_startTime ASC";

$result = $conn->query($query);

// Function to get QR code path
function getQRCodePath($slotId) {
    // Using the absolute path from your project root
    $qrPath = "../Event Advisor/Module3/qr_codes/slot_" . $slotId . ".png";
    
    // For debugging
    error_log("Trying to access QR code at: " . $qrPath);
    error_log("File exists: " . (file_exists($qrPath) ? "Yes" : "No"));
    
    return $qrPath;
}

// Function to check if QR code exists
function qrCodeExists($slotId) {
    $qrPath = "../Event Advisor/Module3/qr_codes/slot_" . $slotId . ".png";
    return file_exists($qrPath);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Available Events</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
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
    </style>
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Available Events</h2>
        <div class="text-muted">
            <small>Last Updated: <?php echo $currentDateTime; ?> UTC</small>
        </div>
    </div>

    <!-- Back to Dashboard Button -->
    <div class="mb-4">
        <a href="student-dashboard.php" class="btn btn-outline-primary">← Back to Dashboard</a>
    </div>

    <!-- Event List -->
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
                                <strong>Date:</strong> <?php echo $row['S_Date']; ?><br>
                                <strong>Time:</strong> <?php echo date('h:i A', strtotime($row['S_startTime'])) . ' - ' . date('h:i A', strtotime($row['S_endTime'])); ?>
                            </p>
                        </div>

                        <!-- QR Code Display -->
                        <div class="text-center">
                            <?php if ($hasQRCode): ?>
                                <img src="<?php echo $qrCodePath; ?>" 
                                     class="qr-code-img" 
                                     alt="QR Code for <?php echo htmlspecialchars($row['E_name']); ?>"
                                     onerror="this.onerror=null; this.src='placeholder-qr.png';">
                            <?php else: ?>
                                <div class="alert alert-info">
                                    <strong>Slot ID:</strong> <?php echo $row['S_slotID']; ?><br>
                                    <?php if ($row['S_slotID'] <= 8): ?>
                                        Please generate QR code for this slot.
                                    <?php else: ?>
                                        Looking for QR code at: <?php echo htmlspecialchars($qrCodePath); ?>
                                    <?php endif; ?>
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

<?php if (isset($_GET['debug'])): ?>
<div class="container mt-3">
    <div class="alert alert-secondary">
        <h5>Debug Information</h5>
        <pre>
Script Location: <?php echo __FILE__; ?>
Document Root: <?php echo $_SERVER['DOCUMENT_ROOT']; ?>
QR Codes Directory: <?php echo realpath("../Event Advisor/Module3/qr_codes/"); ?>
Available QR Codes: 
<?php 
$qrDir = "../Event Advisor/Module3/qr_codes/";
if (is_dir($qrDir)) {
    $files = scandir($qrDir);
    echo implode("\n", array_filter($files, function($f) { return strpos($f, 'slot_') === 0; }));
} else {
    echo "Directory not found: $qrDir";
}
?>
        </pre>
    </div>
</div>
<?php endif; ?>

</body>
</html>