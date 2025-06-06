<?php
require_once 'includes/dbconnection.php';
require_once 'includes/phpqrcode/qrlib.php';

// Check if event ID is provided and valid
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid event ID");
}

$eventId = intval($_GET['id']);

// Fetch event details
$sql = "SELECT * FROM event WHERE E_eventID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $eventId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Event not found");
}

$event = $result->fetch_assoc();

// Create properly formatted QR code content
$qrContent = "BEGIN:VEVENT\n";
$qrContent .= "SUMMARY:" . $event['E_name'] . "\n";
$qrContent .= "DTSTART:" . date('Ymd', strtotime($event['E_startDate'])) . "\n";
if ($event['E_startDate'] != $event['E_endDate']) {
    $qrContent .= "DTEND:" . date('Ymd', strtotime($event['E_endDate'])) . "\n";
}
$qrContent .= "LOCATION:" . $event['E_geoLocation'] . "\n";
$qrContent .= "DESCRIPTION:" . str_replace("\n", "\\n", $event['E_description']) . "\n";
$qrContent .= "STATUS:" . $event['E_eventStatus'] . "\n";
$qrContent .= "END:VEVENT";

// Generate QR code
if (!function_exists('imagecreate')) {
    // Output as text QR code if GD not available
    $qrCodeText = QRcode::text($qrContent);
    echo '<pre style="font-family: monospace; line-height: 1; font-size: 10px;">'.implode("\n", $qrCodeText).'</pre>';
    echo '<p>Scan this text QR code with your device</p>';
} else {
    // Output as PNG image
    QRcode::png($qrContent, false, QR_ECLEVEL_H, 10, 2);
}

// Close connection
$stmt->close();
$conn->close();
exit();
?>