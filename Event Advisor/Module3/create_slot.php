<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['slot_id'];
    $name = $_POST['name'];
    $date = $_POST['date'];
    $time = $_POST['time'];
    $location = $_POST['location'];

    $stmt = $conn->prepare("INSERT INTO attendanceslot (S_SlotID, S_Name, S_Date, S_Time, S_Location) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $id, $name, $date, $time, $location);
    $stmt->execute();

    // Redirect with success message
    header("Location: view_attendanceslot.php?success=1");
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
      <input type="text" name="slot_id" class="form-control" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Event Name</label>
      <input type="text" name="name" class="form-control" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Event Date</label>
      <input type="date" name="date" class="form-control" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Event Time</label>
      <input type="time" name="time" class="form-control" required>
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