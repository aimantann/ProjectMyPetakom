<?php
require_once('user-validatesession.php');

// Debug: Check what session variables exist (remove this after debugging)
// echo "<pre>"; print_r($_SESSION); echo "</pre>"; exit();

// Check if user is logged in - be flexible with session variable names
if (!isset($_SESSION['U_userID']) && !isset($_SESSION['userID']) && !isset($_SESSION['user_id'])) {
    echo "<script>alert('Please login first.'); window.location.href='../login.php';</script>";
    exit();
}

// Get user ID from whatever session variable is set
$currentUserID = $_SESSION['U_userID'] ?? $_SESSION['userID'] ?? $_SESSION['user_id'] ?? null;

// Check if user type is student (if this session variable exists)
if (isset($_SESSION['U_usertype']) && $_SESSION['U_usertype'] !== 'Student') {
    echo "<script>alert('Access denied. Students only.'); window.location.href='../login.php';</script>";
    exit();
}

// DB connection
include('includes/header.php');
include('includes/dbconnection.php');

$currentUserID = $_SESSION['U_userID'] ?? $_SESSION['userID'] ?? $_SESSION['user_id'] ?? null;

// Fetch events where current user is assigned as committee member
$eventQuery = "SELECT 
    e.E_eventID,
    e.E_name,
    e.E_description,
    e.E_startDate,
    e.E_endDate,
    e.E_geoLocation,
    e.E_eventStatus,
    e.E_approvalLetter,
    e.E_qrCode,
    e.E_level,
    ec.C_committeeID,
    ec.C_position,
    u.U_name as advisor_name
FROM event e
INNER JOIN eventcommittee ec ON e.E_eventID = ec.E_eventID
LEFT JOIN user u ON e.U_userID = u.U_userID
WHERE ec.U_userID = ? AND e.E_eventStatus = 'Active'
ORDER BY e.E_startDate ASC";

$stmt = $conn->prepare($eventQuery);
$stmt->bind_param("i", $currentUserID);
$stmt->execute();
$eventResult = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Committee Events | MyPetakom</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .event-card {
            transition: transform 0.2s;
            border-left: 4px solid #0d6efd;
        }
        .event-card:hover {
            transform: translateY(-2px);
        }
        .position-badge {
            font-size: 0.8em;
        }
        .date-badge {
            background: linear-gradient(45deg, #667eea 0%, #764ba2 100%);
        }
        .status-active {
            background-color: #28a745;
        }
        .committee-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
    </style>
</head>
<body>

<div class="container py-4">
    <div class="row">
        <div class="col-12">
            <div class="committee-header text-white p-4 rounded mb-4">
                <h2 class="mb-1"><i class="fas fa-users me-2"></i>My Committee Events</h2>
                <p class="mb-0">Events where you are assigned as committee member</p>
            </div>
        </div>
    </div>

    <div class="row">
        <?php if ($eventResult->num_rows > 0): ?>
            <?php while ($event = $eventResult->fetch_assoc()): ?>
                <div class="col-lg-6 col-xl-4 mb-4">
                    <div class="card event-card h-100 shadow-sm">
                        <div class="card-header bg-light d-flex justify-content-between align-items-center">
                            <span class="badge position-badge <?= $event['C_position'] == 'Main Committee' ? 'bg-primary' : 'bg-success' ?>">
                                <i class="fas fa-star me-1"></i><?= htmlspecialchars($event['C_position']) ?>
                            </span>
                            <span class="badge status-active text-white">
                                <i class="fas fa-check-circle me-1"></i><?= htmlspecialchars($event['E_eventStatus']) ?>
                            </span>
                        </div>
                        
                        <div class="card-body">
                            <h5 class="card-title text-primary mb-3">
                                <?= htmlspecialchars($event['E_name']) ?>
                            </h5>
                            
                            <p class="card-text text-muted mb-3">
                                <?= htmlspecialchars(substr($event['E_description'], 0, 100)) ?>
                                <?= strlen($event['E_description']) > 100 ? '...' : '' ?>
                            </p>
                            
                            <div class="event-details">
                                <div class="mb-2">
                                    <i class="fas fa-calendar-alt text-primary me-2"></i>
                                    <strong>Date:</strong>
                                    <?= date('M d, Y', strtotime($event['E_startDate'])) ?> 
                                    to <?= date('M d, Y', strtotime($event['E_endDate'])) ?>
                                </div>
                                <div class="mb-2">
                                    <i class="fas fa-map-marker-alt text-danger me-2"></i>
                                    <strong>Location:</strong> <?= htmlspecialchars($event['E_geoLocation']) ?>
                                </div>
                            </div>
                        </div>

                        <div class="card-footer bg-white d-flex justify-content-end">
                            <a href="QRevent.php?id=<?= $event['E_eventID'] ?>" class="btn btn-outline-dark btn-sm">
                                <i class="fas fa-qrcode"></i> QR Code
                            </a>
                        </div>

                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12 text-center mt-5">
                <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                <h4 class="text-muted">You are not assigned to any active events</h4>
            </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
