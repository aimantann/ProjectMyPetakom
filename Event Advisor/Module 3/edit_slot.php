<?php
include 'db.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    $stmt = $conn->prepare("SELECT * FROM attendanceslot WHERE S_SlotID = ?");
    $stmt->bind_param("s", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $slot = $result->fetch_assoc();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['slot_id'];
    $name = $_POST['name'];
    $date = $_POST['date'];
    $time = $_POST['time'];
    $location = $_POST['location'];

    $stmt = $conn->prepare("UPDATE attendanceslot SET S_Name = ?, S_Date = ?, S_Time = ?, S_Location = ? WHERE S_SlotID = ?");
    $stmt->bind_param("sssss", $name, $date, $time, $location, $id);
    $stmt->execute();

    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Edit Attendance Slot</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
  <h2>Edit Attendance Slot</h2>
  <form method="POST" class="mt-4">
    <input type="hidden" name="slot_id" value="<?= $slot['S_SlotID'] ?>">
    <div class="mb-3">
      <label class="form-label">Event Name</label>
      <input type="text" name="name" class="form-control" value="<?= $slot['S_Name'] ?>" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Event Date</label>
      <input type="date" name="date" class="form-control" value="<?= $slot['S_Date'] ?>" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Event Time</label>
      <input type="time" name="time" class="form-control" value="<?= $slot['S_Time'] ?>" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Location</label>
      <input type="text" name="location" class="form-control" value="<?= $slot['S_Location'] ?>" required>
    </div>
    <button type="submit" class="btn btn-primary">Update Slot</button>
    <a href="index.php" class="btn btn-secondary">Cancel</a>
  </form>
</div>

</body>
</html>
