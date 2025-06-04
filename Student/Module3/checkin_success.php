<?php
include '../includes/dbconnection.php';
session_start();

if (!isset($_GET['slot'])) {
    header("Location: index.php");
    exit();
}

$slot_id = $_GET['slot'];

// Get event details
$query = "SELECT e.E_name, a.S_Date, a.S_startTime, a.S_endTime, a.S_Location 
          FROM event e 
          JOIN attendanceslot a ON e.E_eventID = a.E_eventID 
          WHERE a.S_slotID = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $slot_id);
$stmt->execute();
$result = $stmt->get_result();
$eventData = $result->fetch_assoc();

if (!$eventData) {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Check-in Successful</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #f8f9fa;
            padding: 20px;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .success-card {
            width: 100%;
            max-width: 500px;
            margin: 20px auto;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 6px 15px rgba(0,0,0,0.1);
        }
        .success-header {
            background: linear-gradient(135deg, #198754, #20c997);
            color: white;
            padding: 30px 20px;
            text-align: center;
        }
        .location-btn {
            background-color: #0d6efd;
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 5px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }
        .location-btn:hover {
            background-color: #0b5ed7;
            color: white;
            transform: translateY(-2px);
        }
        .success-icon {
            font-size: 48px;
            color: white;
            margin-bottom: 10px;
        }
        .info-block {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 15px;
        }
        .info-label {
            font-weight: 600;
            color: #6c757d;
            margin-bottom: 5px;
        }
        .info-value {
            font-size: 1.1rem;
            color: #212529;
        }
        @media (max-width: 768px) {
            .success-card {
                margin: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="success-card card">
        <div class="success-header">
            <i class="bi bi-check-circle success-icon"></i>
            <h3 class="mt-2">Check-in Successful!</h3>
            <p class="mb-0">You have successfully checked in to this event.</p>
        </div>
        
        <div class="card-body">
            <h4 class="card-title mb-4"><?= htmlspecialchars($eventData['E_name']) ?></h4>
            
            <div class="info-block">
                <div class="row">
                    <div class="col-md-6">
                        <div class="info-label">Date</div>
                        <div class="info-value">
                            <?= date('F j, Y', strtotime($eventData['S_Date'])) ?>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-label">Time</div>
                        <div class="info-value">
                            <?= date('h:i A', strtotime($eventData['S_startTime'])) ?> - 
                            <?= date('h:i A', strtotime($eventData['S_endTime'])) ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="info-block">
                <div class="info-label">Location</div>
                <div class="info-value mb-3">
                    <?= htmlspecialchars($eventData['S_Location']) ?>
                </div>
                <?php
                $mapsUrl = "https://www.google.com/maps/search/" . urlencode($eventData['S_Location']);
                ?>
                <a href="<?= $mapsUrl ?>" target="_blank" class="location-btn">
                    <i class="bi bi-geo-alt-fill"></i>
                    Get Directions
                </a>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>