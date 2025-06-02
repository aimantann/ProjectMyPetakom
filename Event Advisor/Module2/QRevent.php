<?php
require_once '../includes/dbconnection.php';

if (!isset($_GET['id'])) {
    die("Missing Event ID");
}

$eventId = intval($_GET['id']);
$stmt = $conn->prepare("SELECT * FROM event WHERE E_eventID = ?");
$stmt->bind_param("i", $eventId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Event not found.");
}

$event = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($event['E_name']); ?> - QR Event</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container my-5">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h4><?php echo htmlspecialchars($event['E_name']); ?></h4>
        </div>
        <div class="card-body">
            <p><strong>Description:</strong> <?php echo htmlspecialchars($event['E_description']); ?></p>
            <p><strong>Date:</strong> <?php echo $event['E_startDate']; ?> to <?php echo $event['E_endDate']; ?></p>
            <p><strong>Location:</strong> <?php echo htmlspecialchars($event['E_geoLocation']); ?></p>
            <p><strong>Status:</strong> <?php echo htmlspecialchars($event['E_eventStatus']); ?></p>
            <p><strong>Level:</strong> <?php echo htmlspecialchars($event['E_level']); ?></p>

            <?php if (!empty($event['E_qrCode']) && file_exists("../" . $event['E_qrCode'])): ?>
                <div class="text-center mt-4">
                    <h5>Scan QR to view this page</h5>
                    <img src="../<?php echo $event['E_qrCode']; ?>" alt="QR Code">
                </div>
            <?php else: ?>
                <div class="text-center mt-4">
                    <a href="generate_qr.php?id=<?php echo $eventId; ?>" class="btn btn-success">
                        Generate QR Code
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>
