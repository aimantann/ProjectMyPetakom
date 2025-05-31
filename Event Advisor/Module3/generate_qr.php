<?php
$slot_id = $_GET['id']; // Changed from 'slot_id' to 'id' for consistency
$qr_url = "http://localhost/ProjectMyPetakom/Event Advisor/Module3/scan_qr.php?id=$slot_id"; // Updated URL
header("Content-Type: image/png");
echo file_get_contents("https://chart.googleapis.com/chart?chs=300x300&cht=qr&chl=" . urlencode($qr_url));
?>
