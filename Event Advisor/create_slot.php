<?php
require_once('user-validatesession.php');

require_once('includes/dbconnection.php');

// Handle AJAX requests first
if (isset($_GET['fetch_event']) && isset($_GET['E_eventID'])) {
    $eventId = intval($_GET['E_eventID']);
    $sql = "SELECT E_name, E_date, E_location FROM event WHERE E_eventID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $eventId);
    $stmt->execute();
    $result = $stmt->get_result();
    $event = $result->fetch_assoc();
    header('Content-Type: application/json');
    echo json_encode($event);
    exit();
}

// Initialize error/success messages
$error_message = '';
$success_message = '';

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get POST data safely
    $slotName = $_POST['S_Name'] ?? '';
    $slotDate = $_POST['S_Date'] ?? '';
    $startTime = $_POST['S_startTime'] ?? '';
    $endTime = $_POST['S_endTime'] ?? '';
    $location = $_POST['S_Location'] ?? '';
    $eventId = $_POST['E_eventID'] ?? '';

    // Basic validation
    if ($slotName && $slotDate && $startTime && $endTime && $location && $eventId) {
        // Prepare the insert statement
        $stmt = $conn->prepare("INSERT INTO attendanceslot 
            (S_Name, S_Date, S_startTime, S_endTime, S_Location, E_eventID) 
            VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssi", $slotName, $slotDate, $startTime, $endTime, $location, $eventId);

        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Attendance slot created successfully!";
            header("Location: view_attendanceslot.php?success=1");
            exit();
        } else {
            $error_message = "Error executing query: " . $stmt->error;
        }
    } else {
        $error_message = "Please fill in all fields.";
    }
}

// Fetch existing events
$eventQuery = "SELECT E_eventID, E_name FROM event ORDER BY E_name ASC";
$eventResult = $conn->query($eventQuery);

// Now include header and other visual elements
require_once('includes/header.php');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Create Attendance Slot</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <h2>Create Attendance Slot</h2>
        <a href="advisor-dashboard.php" class="btn btn-outline-primary mb-4">← Back to Dashboard</a>

        <?php if ($error_message): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <form method="POST" class="mt-4" id="attendanceSlotForm">
            <div class="mb-3">
                <label class="form-label">Select Event</label>
                <select name="E_eventID" class="form-control" id="eventSelect" required>
                    <option value="">-- Select Event --</option>
                    <?php 
                    $eventResult->data_seek(0);
                    while ($row = $eventResult->fetch_assoc()): ?>
                        <option value="<?= htmlspecialchars($row['E_eventID']) ?>">
                            <?= htmlspecialchars($row['E_name']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label">Slot Name</label>
                <input type="text" name="S_Name" class="form-control" id="slotName" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Date</label>
                <input type="date" name="S_Date" class="form-control" id="slotDate" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Start Time</label>
                <input type="time" name="S_startTime" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">End Time</label>
                <input type="time" name="S_endTime" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Location</label>
                <input type="text" name="S_Location" class="form-control" id="slotLocation" required>
            </div>

            <button type="submit" class="btn btn-success">Create Slot</button>
            <a href="view_attendanceslot.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>

    <script>
    document.getElementById('eventSelect').addEventListener('change', function() {
        var eventId = this.value;
        if (eventId) {
            fetch('create_slot.php?fetch_event=1&E_eventID=' + eventId)
            .then(response => response.json())
            .then(data => {
                if (data) {
                    document.getElementById('slotName').value = data.E_name || '';
                    document.getElementById('slotDate').value = data.E_date || '';
                    document.getElementById('slotLocation').value = data.E_location || '';
                }
            });
        } else {
            document.getElementById('slotName').value = '';
            document.getElementById('slotDate').value = '';
            document.getElementById('slotLocation').value = '';
        }
    });
    </script>

    <?php include('includes/footer.php'); ?>
</body>
</html>