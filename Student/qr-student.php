<?php
session_start();
require_once('../includes/dbconnection.php');

// Get data from QR code
$qr_data = json_decode($_GET['data'], true);

if (!$qr_data) {
    exit('Invalid QR code data');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Information</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .info-container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .info-item {
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        .info-label {
            font-size: 14px;
            color: #666;
            margin-bottom: 5px;
        }
        .info-value {
            font-size: 16px;
            color: #333;
            font-weight: 500;
        }
        .merit-total {
            font-size: 24px;
            color: #007bff;
            font-weight: bold;
            text-align: center;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="info-container">
        <div class="info-item">
            <div class="info-label">Full Name</div>
            <div class="info-value"><?php echo htmlspecialchars($qr_data['name']); ?></div>
        </div>
        
        <div class="info-item">
            <div class="info-label">Phone Number</div>
            <div class="info-value"><?php echo htmlspecialchars($qr_data['phone']); ?></div>
        </div>
        
        <div class="info-item">
            <div class="info-label">Email Address</div>
            <div class="info-value"><?php echo htmlspecialchars($qr_data['email']); ?></div>
        </div>
        
        <div class="info-item">
            <div class="info-label">Role</div>
            <div class="info-value"><?php echo htmlspecialchars($qr_data['role']); ?></div>
        </div>
    </div>
</body>
</html>