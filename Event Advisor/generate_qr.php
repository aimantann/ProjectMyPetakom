<?php
require_once '../includes/dbconnection.php';
require_once '../libs/phpqrcode/qrlib.php'; // Ensure phpqrcode is installed

if (!isset($_GET['id'])) {
    exit('Event ID missing.');
}

$eventId = intval($_GET['id']);
$eventUrl = "http://localhost/ProjectMyPetakom/Event%20Advisor/Module2/QRevent.php?id=" . $eventId;

// Define path to save QR code
$qrDir = "../uploads/qrcodes/";
if (!file_exists($qrDir)) {
    mkdir($qrDir, 0777, true);
}
$qrFile = $qrDir . "event_" . $eventId . ".png";

// Generate QR Code
QRcode::png($eventUrl, $qrFile, QR_ECLEVEL_H, 6);

// Save path to DB
$qrPathInDb = "uploads/qrcodes/event_" . $eventId . ".png";
$stmt = $conn->prepare("UPDATE event SET E_qrCode = ? WHERE E_eventID = ?");
$stmt->bind_param("si", $qrPathInDb, $eventId);
if ($stmt->execute()) {
    echo "QR Code generated and saved successfully.";
} else {
    echo "Failed to save QR path.";
}
