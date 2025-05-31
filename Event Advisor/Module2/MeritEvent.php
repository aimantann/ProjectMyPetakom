<?php
session_start();
include '../includes/dbconnection.php';

// Ensure advisor is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$advisorId = $_SESSION['user_id'];

// Apply for merit
if (isset($_GET['apply_merit']) && isset($_GET['event_id'])) {
    $eventId = intval($_GET['event_id']);

    // Check if merit already applied
    $checkSql = "SELECT * FROM meritapplication WHERE E_eventID = ? AND MA_appliedBy = ?";
    $stmtCheck = $conn->prepare($checkSql);
    $stmtCheck->bind_param("ii", $eventId, $advisorId);
    $stmtCheck->execute();
    $checkResult = $stmtCheck->get_result();

    if ($checkResult->num_rows == 0) {
        // Insert new merit application
        $insertSql = "INSERT INTO meritapplication (MA_meritAppStatus, MA_approvedBy, MA_appliedBy, E_eventID, U_userID, MR_meritID)
                      VALUES ('Pending', NULL, ?, ?, NULL, NULL)";
        $stmtInsert = $conn->prepare($insertSql);
        $stmtInsert->bind_param("ii", $advisorId, $eventId);
        $stmtInsert->execute();

        if ($stmtInsert->affected_rows > 0) {
            $message = "Merit application submitted.";
        } else {
            $message = "Failed to apply for merit.";
        }
    } else {
        $message = "Merit already applied for this event.";
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
