<?php
session_start();
include('includes/header.php');
include("includes/dbconnection.php");

// Check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    $_SESSION['login_required'] = "Please login as a student to access this page.";
    header('Location: user-login.php');
    exit();
}

// Get student information
$query = "SELECT U_name, U_email FROM user WHERE U_userID = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();

// Dynamic URL Configuration for your specific path
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'];
$folder = "ProjectMyPetakom/Student";
$merit_url = $protocol . $host . "/{$folder}/qr-student.php?id=" . base64_encode($_SESSION['user_id']);

// Update QR code information in user table
$update_qr_query = "UPDATE user SET U_qrCode = ?, U_qrGeneratedDate = ? WHERE U_userID = ?";
$stmt = $conn->prepare($update_qr_query);
$qr_date = date('Y-m-d H:i:s');
$stmt->bind_param("ssi", $merit_url, $qr_date, $_SESSION['user_id']);
$stmt->execute();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My QR Code - MyPetakom</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.rawgit.com/davidshimjs/qrcodejs/gh-pages/qrcode.min.js"></script>
    <style>
        body {
            background-color: #f8f9fa;
        }
        
        .qr-container {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            text-align: center;
            margin: 20px auto;
            max-width: 500px;
        }

        .student-info {
            margin: 20px 0;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
        }

        #qrcode {
            display: inline-block;
            padding: 20px;
            background: white;
            border-radius: 10px;
            margin: 20px 0;
        }

        .download-btn {
            background-color: #0d6efd;
            color: white;
            border: none;
            padding: 10px 25px;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .download-btn:hover {
            background-color: #0b5ed7;
        }

        .student-info h5 {
            color: #333;
            margin-bottom: 5px;
            font-size: 1.2rem;
        }

        .student-info p {
            color: #6c757d;
            margin: 0;
        }

        .page-title {
            color: #2c3e50;
            font-weight: 600;
            margin-bottom: 1.5rem;
        }

        @media (max-width: 576px) {
            .qr-container {
                margin: 10px;
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="qr-container">
            <h2 class="page-title">My Merit QR Code</h2>
            
            <div class="student-info">
                <h5><?php echo htmlspecialchars($student['U_name']); ?></h5>
                <p><?php echo htmlspecialchars($student['U_email']); ?></p>
            </div>

            <div id="qrcode"></div>

            <button class="download-btn" onclick="downloadQR()">
                <i class="fas fa-download me-2"></i>Download QR Code
            </button>
        </div>
    </div>

    <script>
        // Generate QR Code
        var qrcode = new QRCode(document.getElementById("qrcode"), {
            text: "<?php echo $merit_url; ?>",
            width: 256,
            height: 256,
            colorDark : "#000000",
            colorLight : "#ffffff",
            correctLevel : QRCode.CorrectLevel.H
        });

        // Function to download QR code
        function downloadQR() {
            const canvas = document.querySelector("#qrcode canvas");
            const link = document.createElement('a');
            link.download = 'merit-qr-code.png';
            link.href = canvas.toDataURL();
            link.click();
        }
    </script>

    <?php include('includes/footer.php'); ?>
</body>
</html>