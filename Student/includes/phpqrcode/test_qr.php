<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Test specific QR code
$qrCodePath = 'qr_codes/slot_9.png';

echo "<h2>QR Code Test</h2>";
echo "<p>Testing path: " . $qrCodePath . "</p>";

// Check if file exists
if (file_exists($qrCodePath)) {
    echo "<p style='color: green;'>File exists!</p>";
    echo "<p>Full path: " . realpath($qrCodePath) . "</p>";
    echo "<p>File permissions: " . decoct(fileperms($qrCodePath) & 0777) . "</p>";
    echo "<p>File size: " . filesize($qrCodePath) . " bytes</p>";
    
    // Try to display the image
    echo "<h3>QR Code Image:</h3>";
    echo "<img src='" . $qrCodePath . "' alt='QR Code' style='max-width: 200px;'>";
} else {
    echo "<p style='color: red;'>File does not exist!</p>";
    
    // Check the directory
    $dir = 'qr_codes';
    echo "<h3>Directory Check:</h3>";
    if (file_exists($dir)) {
        echo "<p>Directory exists</p>";
        echo "<p>Directory permissions: " . decoct(fileperms($dir) & 0777) . "</p>";
        
        // List all files in the directory
        echo "<h4>Files in directory:</h4>";
        $files = scandir($dir);
        echo "<ul>";
        foreach ($files as $file) {
            if ($file != "." && $file != "..") {
                echo "<li>" . $file . "</li>";
            }
        }
        echo "</ul>";
    } else {
        echo "<p>Directory does not exist!</p>";
    }
}

// Check database entry
include 'db.php';
$query = "SELECT S_qrCode, S_qrStatus FROM attendanceslot WHERE S_slotID = 9";
$result = $conn->query($query);
if ($row = $result->fetch_assoc()) {
    echo "<h3>Database Entry:</h3>";
    echo "<p>Stored QR path: " . $row['S_qrCode'] . "</p>";
    echo "<p>QR Status: " . $row['S_qrStatus'] . "</p>";
}
?>