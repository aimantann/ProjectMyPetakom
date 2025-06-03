<?php
session_start();
$student_id = $_SESSION['student_id'] ?? null;
if (!$student_id) {
    header('Location: login.php');
    exit;
}

include '../db_connect.php';

// Fetch current active slot for student or event (customize as needed)
$query = "SELECT id, event_name FROM attendance_slots WHERE event_date = CURDATE() LIMIT 1";
$result = $conn->query($query);
$slot = $result->fetch_assoc();

if (!$slot) {
    echo "No active attendance slots today.";
    exit;
}

$scan_url = "http://yourdomain.com/student/scan_qr.php?slot_id=" . $slot['id'];

// Generate QR code
require '../phpqrcode/qrlib.php';
ob_start();
QRcode::png($scan_url, null, QR_ECLEVEL_L, 6);
$imageString = base64_encode(ob_get_contents());
ob_end_clean();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Attendance Dashboard</title>
</head>
<body>
<h2>Welcome, Student</h2>

<h3>Scan this QR code to mark attendance for:</h3>
<p><strong><?= htmlspecialchars($slot['event_name']) ?></strong></p>

<img src="data:image/png;base64,<?= $imageString ?>" alt="QR Code">

</body>
</html>
