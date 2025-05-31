<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $slotName = $_POST['slot_name'];
    $slotDate = $_POST['slot_date'];
    $startTime = $_POST['start_time'];
    $endTime = $_POST['end_time'];
    $location = $_POST['location'];
    $eventId = $_POST['event_id'];
    
    // QR code will be generated later, setting it as NULL for now
    $stmt = $conn->prepare("INSERT INTO attendanceslot 
            (S_Name, S_Date, S_startTime, S_endTime, S_Location, S_qrCode, E_eventID) 
            VALUES (?, ?, ?, ?, ?, NULL, ?)");
    $stmt->bind_param("sssssi", $slotName, $slotDate, $startTime, $endTime, $location, $eventId);
    
    if ($stmt->execute()) {
        header("Location: view_attendanceslot.php?success=1");
    } else {
        echo "Error: " . $conn->error;
    }
    exit();
}
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
  <a href="../advisor-dashboard.php" class="btn btn-outline-primary mb-4">← Back to Dashboard</a>
  <form method="POST" class="mt-4">
    <div class="mb-3">
      <label class="form-label">Event ID</label>
      <input type="number" name="event_id" class="form-control" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Slot Name</label>
      <input type="text" name="slot_name" class="form-control" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Date</label>
      <input type="date" name="slot_date" class="form-control" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Start Time</label>
      <input type="time" name="start_time" class="form-control" required>
    </div>
    <div class="mb-3">
      <label class="form-label">End Time</label>
      <input type="time" name="end_time" class="form-control" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Location</label>
      <input type="text" name="location" class="form-control" required>
    </div>
    <button type="submit" class="btn btn-success">Create Slot</button>
    <a href="view_attendanceslot.php" class="btn btn-secondary">Cancel</a>
  </form>
</div>
</body>
</html>