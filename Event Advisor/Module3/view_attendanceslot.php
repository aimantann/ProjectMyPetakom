<?php 
include 'db.php';

// Set current user and datetime
$currentDateTime = "2025-06-02 16:08:21";
$currentUser = "AthirahSN";

// Log page access
error_log("[$currentDateTime] User $currentUser accessed view_attendanceslot.php");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Attendance Slot</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
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
        .qr-modal-image {
            max-width: 300px;
            height: auto;
            margin: 0 auto;
            display: block;
            border: 1px solid #ddd;
            padding: 10px;
            background: white;
            border-radius: 4px;
        }
        .qr-info {
            margin-top: 15px;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 4px;
        }
        .qr-actions {
            margin-top: 15px;
            display: flex;
            gap: 10px;
            justify-content: center;
        }
    </style>
</head>
<body class="bg-light">

<!-- QR Code Modal -->
<div class="modal fade" id="qrModal" tabindex="-1" aria-labelledby="qrModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="qrModalLabel">QR Code</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <div id="qrModalContent"></div>
            </div>
        </div>
    </div>
</div>

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
            switch ($_GET['success']) {
                case 1: echo "Attendance slot created successfully!"; break;
                case 2: echo "Attendance slot updated successfully!"; break;
                case 3: echo "Attendance slot deleted successfully!"; break;
                case 4: echo "QR code generated successfully!"; break;
            }
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php
    $query = "SELECT attendanceslot.*, event.E_name 
              FROM attendanceslot 
              JOIN event ON attendanceslot.E_eventID = event.E_eventID 
              ORDER BY attendanceslot.S_slotID DESC";
    $result = $conn->query($query);

    if ($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $startTime = date('h:i A', strtotime($row['S_startTime']));
            $endTime = date('h:i A', strtotime($row['S_endTime']));
            $slotDate = date('Y-m-d', strtotime($row['S_Date']));
            $slotName = htmlspecialchars($row['S_Name']);
            $slotLocation = htmlspecialchars($row['S_Location']);
            $qrPath = "qr_codes/slot_" . $row['S_slotID'] . ".png";

            echo "<div class='card slot-card mb-3'>
                    <div class='card-body'>
                        <div class='row'>
                            <div class='col-md-8'>
                                <h5 class='card-title mb-1'>Slot ID: {$row['S_slotID']}</h5>
                                <p class='mb-1'><strong>Slot Name:</strong> $slotName</p>
                                <p class='mb-1'><strong>Location:</strong> $slotLocation</p>
                                <p class='mb-1'><strong>Date:</strong> $slotDate</p>
                                <p class='card-text mb-1'>
                                    <strong>Event:</strong> {$row['E_name']} (ID: {$row['E_eventID']})<br>
                                    <strong>Time:</strong> {$startTime} - {$endTime}<br>
                                    <strong>QR Code Status:</strong> ";
                                    
            echo ($row['S_qrStatus'] === 'Generated') 
                ? '<span class="badge bg-success">Generated</span>' 
                : '<span class="badge bg-warning text-dark">Not Generated</span>';
            
            echo "          </p>
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
                                    </a>";
                                    
            if ($row['S_qrStatus'] === 'Generated') {
                echo "<button type='button' 
                             class='btn btn-info btn-sm' 
                             onclick='showQRCode(\"{$qrPath}\", \"{$row['E_name']}\", \"$slotDate\", \"$startTime\", \"$endTime\", \"$slotLocation\", \"{$row['S_slotID']}\")'>
                        <i class='bi bi-qr-code'></i> View QR
                      </button>";
            } else {
                echo "<a href='generate_qr.php?slot_id={$row['S_slotID']}' class='btn btn-secondary btn-sm'>
                        <i class='bi bi-qr-code'></i> Generate QR
                      </a>";
            }

            echo "          </div>
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
    setTimeout(function() {
        const alerts = document.getElementsByClassName('alert');
        for(let alert of alerts) {
            bootstrap.Alert.getOrCreateInstance(alert).close();
        }
    }, 5000);
});

function showQRCode(qrPath, eventName, date, startTime, endTime, location, slotId) {
    const validationUrl = window.location.origin + '/validate_attendance.php?slot=' + slotId;
    const modalContent = `
        <img src="${qrPath}" class="qr-modal-image" alt="QR Code">
        <div class="qr-info">
            <h6>${eventName}</h6>
            <p class="mb-1"><strong>Date:</strong> ${date}</p>
            <p class="mb-1"><strong>Time:</strong> ${startTime} - ${endTime}</p>
            <p class="mb-0"><strong>Location:</strong> ${location}</p>
            <hr>
            <p class="mb-1"><strong>Direct URL:</strong></p>
            <input type="text" class="form-control mb-2" value="${validationUrl}" readonly>
            <div class="qr-actions">
                <button class="btn btn-sm btn-outline-secondary" onclick="copyUrl('${validationUrl}')">
                    <i class="bi bi-clipboard"></i> Copy URL
                </button>
                <a href="${qrPath}" download="QR_${eventName}_${date}.png" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-download"></i> Download QR
                </a>
            </div>
        </div>
    `;
    document.getElementById('qrModalContent').innerHTML = modalContent;
    new bootstrap.Modal(document.getElementById('qrModal')).show();
}

function copyUrl(url) {
    navigator.clipboard.writeText(url);
    alert('URL copied to clipboard!');
}
</script>
</body>
</html>