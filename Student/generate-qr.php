<?php
session_start();
require_once('includes/dbconnection.php');
require_once('phpqrcode/qrlib.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    exit('Unauthorized access');
}

// Get student data for QR code
$query = "SELECT U_name, U_phoneNum, U_email FROM user WHERE U_userID = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$student_data = $result->fetch_assoc();

// Create QR code data
$qr_data = json_encode([
    'name' => $student_data['U_name'],
    'phone' => $student_data['U_phoneNum'],
    'email' => $student_data['U_email'],
    'role' => 'Student',
    'timestamp' => time()
]);

// Generate unique filename for the QR code
$qr_filename = 'temp/qr_' . $_SESSION['user_id'] . '_' . time() . '.png';

// Ensure temp directory exists
if (!file_exists('temp')) {
    mkdir('temp', 0777, true);
}

// Generate QR code
QRcode::png($qr_data, $qr_filename, QR_ECLEVEL_L, 10);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Code</title>
    <style>
        body {
            margin: 0;
            padding: 20px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-color: white;
            font-family: Arial, sans-serif;
        }
        .qr-container {
            text-align: center;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .close-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 24px;
            cursor: pointer;
            color: #666;
            background: none;
            border: none;
            padding: 5px;
        }
        .close-btn:hover {
            color: #333;
        }
        img {
            max-width: 300px;
            height: auto;
        }
    </style>
</head>
<body>
    <button class="close-btn" onclick="window.close()">&times;</button>
    <div class="qr-container">
        <img src="<?php echo $qr_filename; ?>" alt="QR Code">
    </div>
</body>
</html>

<?php
// Clean up: Delete the QR code file after a delay
register_shutdown_function(function() use ($qr_filename) {
    sleep(1); // Wait for the image to be loaded
    if (file_exists($qr_filename)) {
        unlink($qr_filename);
    }
});
?>