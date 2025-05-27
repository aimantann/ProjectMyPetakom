<?php include 'db.php'; ?>
<!DOCTYPE html>
<html>
<head>
  <title>Manage Attendance Slot</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
  <h2 class="mb-4">Attendance Slot List</h2>
  <a href="../advisor-dashboard.php" class="btn btn-outline-primary mb-3">← Back to Dashboard</a>
  <a href="create_slot.php" class="btn btn-success mb-3">+ Create Attendance Slot</a>

  <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      Attendance slot created successfully!
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  <?php endif; ?>

  <?php
  $result = $conn->query("SELECT * FROM attendanceslot");

  if ($result->num_rows > 0) {
      while($row = $result->fetch_assoc()) {
          echo "<div class='border p-3 mb-3 rounded bg-white shadow-sm'>
          <p><strong>Event ID:</strong> {$row['S_SlotID']}<br>
             <strong>Name:</strong> {$row['S_Name']}<br>
             <strong>Date:</strong> {$row['S_Date']}<br>
             <strong>Time:</strong> {$row['S_Time']}<br>
             <strong>Location:</strong> {$row['S_Location']}</p>
          <div class='d-flex gap-2'>
            <a href='edit_slot.php?id={$row['S_SlotID']}' class='btn btn-primary btn-sm'>Edit</a>
            <a href='delete_slot.php?id={$row['S_SlotID']}' class='btn btn-danger btn-sm'>Delete</a>
            <a href='generate_qr.php?id={$row['S_SlotID']}' class='btn btn-secondary btn-sm'>Create QR</a>
          </div>
        </div>";
      }
  } else {
      echo "<div class='alert alert-info'>No attendance slots created yet.</div>";
  }
  ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
