<?php
session_start();
include '../includes/dbconnection.php';

// Make sure advisor is logged in and has user_id
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

$advisorId = $_SESSION['user_id'];

// Apply for merit
if (isset($_GET['apply_merit']) && isset($_GET['event_id'])) {
    $eventId = intval($_GET['event_id']);

    // Verify advisor owns this event
    $stmt = $conn->prepare("SELECT E_eventID FROM event WHERE E_eventID = ? AND U_userID = ? LIMIT 1");
    $stmt->bind_param('ii', $eventId, $advisorId);
    $stmt->execute();
    $stmt->bind_result($verified_event_id);
    $stmt->fetch();
    $stmt->close();

    if (!$verified_event_id) {
        $message = "You are not authorized to apply merit for this event.";
    } else {
        // Insert merit application (or update if exists)
        $date = date('Y-m-d');
        $status = 'Pending';

        // Check if advisor already has a merit application for this event
        $stmt = $conn->prepare("SELECT MA_applicationID FROM meritapplication WHERE E_eventID = ? AND MA_appliedBy = ?");
        $stmt->bind_param('ii', $eventId, $advisorId);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            // Update existing application
            $stmt->close();
            $stmt2 = $conn->prepare("UPDATE meritapplication SET MA_meritAppStatus=? WHERE E_eventID=? AND MA_appliedBy=?");
            $stmt2->bind_param('sii', $status, $eventId, $advisorId);
            if ($stmt2->execute()) {
                $message = "Merit application updated successfully.";
            } else {
                $message = "Failed to update merit application.";
            }
            $stmt2->close();
        } else {
            // First, create a default merit record if none exists
            $stmt->close();
            $meritStmt = $conn->prepare("SELECT MR_meritID FROM merit LIMIT 1");
            $meritStmt->execute();
            $meritStmt->bind_result($meritId);
            $meritStmt->fetch();
            $meritStmt->close();
            
            if (!$meritId) {
                // Create a default merit record
                $defaultMeritName = "Event Merit";
                $defaultMeritDesc = "Merit for event participation";
                $defaultMeritPoints = 10;
                
                $createMeritStmt = $conn->prepare("INSERT INTO merit (MR_description, MR_score) VALUES (?, ?)");
                $createMeritStmt->bind_param('si', $defaultMeritDesc, $defaultMeritPoints);
                $createMeritStmt->execute();
                $meritId = $conn->insert_id;
                $createMeritStmt->close();
            }
            
            if ($meritId) {
                // Insert new application with valid MR_meritID
                $stmt2 = $conn->prepare("INSERT INTO meritapplication (MA_meritAppStatus, MA_appliedBy, E_eventID, U_userID, MR_meritID) VALUES (?, ?, ?, ?, ?)");
                $stmt2->bind_param('siiii', $status, $advisorId, $eventId, $advisorId, $meritId);
                if ($stmt2->execute()) {
                    $message = "Merit application submitted successfully.";
                } else {
                    $message = "Failed to submit merit application.";
                }
                $stmt2->close();
            } else {
                $message = "Failed to create merit record. Please contact administrator.";
            }
        }
    }
}

// Get advisor's events
$sql = "SELECT E_eventID, E_name, E_startDate, E_endDate FROM event WHERE U_userID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $advisorId);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Advisor - Apply for Event Merit</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-5">
    <div class="card shadow">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h4 class="mb-0">My Events – Merit Application</h4>
            <a href="../advisor-dashboard.php" class="btn btn-light btn-sm">Dashboard</a>
        </div>
        <div class="card-body">

            <?php if (isset($message)): ?>
                <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>

            <table class="table table-bordered table-hover">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>Event Name</th>
                        <th>Date</th>
                        <th>Merit Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $i = 1;
                while ($row = $result->fetch_assoc()):
                    $eventId = $row['E_eventID'];

                    // Check merit status
                    $checkStatus = "SELECT MA_meritAppStatus FROM meritapplication WHERE E_eventID = ? AND MA_appliedBy = ?";
                    $stmtStatus = $conn->prepare($checkStatus);
                    $stmtStatus->bind_param("ii", $eventId, $advisorId);
                    $stmtStatus->execute();
                    $resStatus = $stmtStatus->get_result();
                    $statusRow = $resStatus->fetch_assoc();
                    $status = $statusRow ? $statusRow['MA_meritAppStatus'] : 'Not Applied';
                ?>
                <tr>
                    <td><?= $i++ ?></td>
                    <td><?= htmlspecialchars($row['E_name']) ?></td>
                    <td><?= htmlspecialchars($row['E_startDate']) ?> to <?= htmlspecialchars($row['E_endDate']) ?></td>
                    <td>
                        <span class="badge <?= $status === 'Not Applied' ? 'bg-secondary' : 'bg-success' ?>">
                            <?= htmlspecialchars($status) ?>
                        </span>
                    </td>
                    <td>
                        <?php if ($status === 'Not Applied'): ?>
                            <a href="?apply_merit=1&event_id=<?= $eventId ?>" 
                               class="btn btn-sm btn-outline-primary"
                               onclick="return confirm('Apply merit for this event?')">
                               Apply for Merit
                            </a>
                        <?php else: ?>
                            <span class="text-muted">Already Applied</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
                </tbody>
            </table>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>