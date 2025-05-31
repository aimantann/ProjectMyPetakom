<?php 
include 'db.php';

// Set current user and datetime
$currentDateTime = "2025-05-31 12:57:34";
$currentUser = "AthirahSN";

// Log page access
error_log("[$currentDateTime] User $currentUser accessed view_attendanceslot.php");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Attendance Slot</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .slot-card {
            transition: all 0.3s ease;
        }
        .slot-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .datetime-info {
            font-size: 0.8rem;
            color: #6c757d;
        }
    </style>
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Attendance Slot List</h2>
        <div class="datetime-info">
            Last Updated: <?php echo $currentDateTime; ?> UTC
        </div>
    </div>

    <div class="row mb-4">
        <div class="col">
            <a href="../advisor-dashboard.php" class="btn btn-outline-primary">← Back to Dashboard</a>
            <a href="create_slot.php" class="btn btn-success ms-2">+ Create Attendance Slot</a>
        </div>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php 
            if ($_GET['success'] == 1) {
                echo "Attendance slot created successfully!";
            } elseif ($_GET['success'] == 2) {
                echo "Attendance slot updated successfully!";
            } elseif ($_GET['success'] == 3) {
                echo "Attendance slot deleted successfully!";
            }
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php
    // Fetch all attendance slots ordered by start time
    $query = "SELECT * FROM attendanceslot ORDER BY S_startTime ASC";
    $result = $conn->query($query);

    if ($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $startTime = date('h:i A', strtotime($row['S_startTime']));
            $endTime = date('h:i A', strtotime($row['S_endTime']));
            
            echo "<div class='card slot-card mb-3'>
                    <div class='card-body'>
                        <div class='row'>
                            <div class='col-md-8'>
                                <h5 class='card-title'>Slot ID: {$row['S_slotID']}</h5>
                                <p class='card-text'>
                                    <strong>Event ID:</strong> {$row['E_eventID']}<br>
                                    <strong>Time:</strong> {$startTime} - {$endTime}<br>
                                    <strong>QR Code Status:</strong> " . 
                                    ($row['S_qrCode'] ? 
                                        '<span class="badge bg-success">Generated</span>' : 
                                        '<span class="badge bg-warning text-dark">Not Generated</span>') . "
                                </p>
                            </div>
                            <div class='col-md-4 text-md-end'>
                                <div class='btn-group' role='group'>
                                    <a href='edit_slot.php?id={$row['S_slotID']}' class='btn btn-primary btn-sm'>
                                        <i class='bi bi-pencil'></i> Edit
                                    </a>
                                    <a href='delete_slot.php?id={$row['S_slotID']}' 
                                       class='btn btn-danger btn-sm'
                                       onclick='return confirm(\"Are you sure you want to delete this slot?\");'>
                                        <i class='bi bi-trash'></i> Delete
                                    </a>
                                    " . (!$row['S_qrCode'] ? 
                                    "<a href='generate_qr.php?id={$row['S_slotID']}' class='btn btn-secondary btn-sm'>
                                        <i class='bi bi-qr-code'></i> Generate QR
                                    </a>" : "") . "
                                </div>
                            </div>
                        </div>
                    </div>
                </div>";
        }
    } else {
        echo "<div class='alert alert-info'>No attendance slots have been created yet.</div>";
    }
    ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        const alerts = document.getElementsByClassName('alert');
        for(let alert of alerts) {
            bootstrap.Alert.getOrCreateInstance(alert).close();
        }
    }, 5000);
});
</script>

</body>
</html>