<?php
require_once('includes/dbconnection.php');

// Validate input
if (!isset($_GET['slot_id']) || !is_numeric($_GET['slot_id'])) {
    header("Location: view_attendanceslot.php?error=invalid_id");
    exit();
}

$slot_id = (int)$_GET['slot_id'];

// Fetch slot data
$query = "SELECT a.*, e.E_name 
          FROM attendanceslot a
          JOIN event e ON a.E_eventID = e.E_eventID 
          WHERE a.S_slotID = ?";
$stmt = $conn->prepare($query);

if (!$stmt) {
    header("Location: view_attendanceslot.php?error=db_error");
    exit();
}

$stmt->bind_param("i", $slot_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: view_attendanceslot.php?error=not_found");
    exit();
}

$slot = $result->fetch_assoc();

// Check if QR already exists
if ($slot['S_qrStatus'] === 'Generated') {
    header("Location: view_attendanceslot.php?success=4");
    exit();
}

// Include QR library
require_once 'phpqrcode/qrlib.php';

// Prepare QR directory
$qrDir = 'qr_codes';
if (!file_exists($qrDir)) {
    if (!mkdir($qrDir, 0755, true)) {
        header("Location: view_attendanceslot.php?error=directory_error");
        exit();
    }
}

// Prepare QR content
$validationUrl = "http://localhost/ProjectMyPetakom/Student/validate_attendance.php?slot=" . $slot_id;
$validationUrl = str_replace(" ", "%20", $validationUrl);

// Generate QR code
$qrFile = $qrDir . '/slot_' . $slot_id . '.png';

try {
    QRcode::png($validationUrl, $qrFile, QR_ECLEVEL_H, 8, 2);
    
    if (!file_exists($qrFile)) {
        throw new Exception("QR file not created");
    }

    // Update database
    $update = $conn->prepare("UPDATE attendanceslot SET S_qrStatus = 'Generated' WHERE S_slotID = ?");
    $update->bind_param("i", $slot_id);
    $update->execute();

    header("Location: view_attendanceslot.php?success=4");
    exit();

} catch (Exception $e) {
    if (file_exists($qrFile)) {
        unlink($qrFile);
    }
    header("Location: view_attendanceslot.php?error=qr_failed");
    exit();
}
?>