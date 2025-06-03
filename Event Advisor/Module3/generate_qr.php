<?php
include 'db.php';

// 1. Validate input
if (!isset($_GET['slot_id']) || !is_numeric($_GET['slot_id'])) {
    die("Invalid slot ID");
}

$slot_id = (int)$_GET['slot_id'];

// 2. Fetch slot data
$query = "SELECT a.*, e.E_name 
          FROM attendanceslot a
          JOIN event e ON a.E_eventID = e.E_eventID 
          WHERE a.S_slotID = ?";
$stmt = $conn->prepare($query);

if (!$stmt) {
    die("Database error: " . $conn->error);
}

$stmt->bind_param("i", $slot_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Slot not found");
}

$slot = $result->fetch_assoc();

// 3. Check if QR already exists
if ($slot['S_qrStatus'] === 'Generated') {
    header("Location: view_attendanceslot.php?success=4");
    exit();
}

// 4. Include QR library
require_once 'phpqrcode/qrlib.php';

// 5. Prepare QR directory
$qrDir = 'qr_codes';
if (!file_exists($qrDir)) {
    if (!mkdir($qrDir, 0755, true)) {
        die("Failed to create QR directory");
    }
}

// 6. Prepare QR content
$qrContent = "ATTENDANCE SLOT\n";
$qrContent .= "Event: " . htmlspecialchars($slot['E_name']) . "\n";
$qrContent .= "Slot: " . htmlspecialchars($slot['S_Name']) . "\n";
$qrContent .= "Date: " . date('d/m/Y', strtotime($slot['S_Date'])) . "\n";
$qrContent .= "Time: " . date('h:i A', strtotime($slot['S_startTime'])) . " - " . 
               date('h:i A', strtotime($slot['S_endTime'])) . "\n";
$qrContent .= "Location: " . htmlspecialchars($slot['S_Location']) . "\n";
$qrContent .= "ID: " . $slot_id . "\n";
$qrContent .= "Verification: " . substr(md5($slot_id . $slot['E_eventID'] . $slot['S_Date']), 0, 8);

// 7. Generate QR code
$qrFile = $qrDir . '/slot_' . $slot_id . '.png';

try {
    // Generate QR code with error correction
    QRcode::png($qrContent, $qrFile, QR_ECLEVEL_H, 8, 2);
    
    // Verify QR was created
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
    // Clean up if QR generation failed
    if (file_exists($qrFile)) {
        unlink($qrFile);
    }
    header("Location: view_attendanceslot.php?error=qr_failed&msg=" . urlencode($e->getMessage()));
    exit();
}
?>