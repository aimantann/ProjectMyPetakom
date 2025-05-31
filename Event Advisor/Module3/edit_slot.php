<?php
include 'db.php';

// Set current user and datetime
$currentDateTime = "2025-05-31 12:52:16";
$currentUser = "AthirahSN";

// Initialize variables
$error_message = '';
$success_message = '';
$slot = null;

// Fetch slot data if ID is provided
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    $stmt = $conn->prepare("SELECT * FROM attendanceslot WHERE S_slotID = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $slot = $result->fetch_assoc();
        // Log access
        error_log("[$currentDateTime] User $currentUser accessed edit_slot.php for slot ID: $id");
    } else {
        $error_message = "Slot not found.";
        error_log("[$currentDateTime] Error: User $currentUser attempted to edit non-existent slot ID: $id");
    }
    $stmt->close();
}

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $slot_id = $_POST['slot_id'];
    $slotName = $_POST['slot_name'];
    $slotDate = $_POST['slot_date'];
    $startTime = $_POST['start_time'];
    $endTime = $_POST['end_time'];
    $location = $_POST['location'];
    $eventId = $_POST['event_id'];

    // Validate inputs
    if (strtotime($endTime) <= strtotime($startTime)) {
        $error_message = "End time must be after start time.";
    } else {
        $stmt = $conn->prepare("UPDATE attendanceslot SET 
                S_Name = ?, 
                S_Date = ?, 
                S_startTime = ?, 
                S_endTime = ?, 
                S_Location = ?, 
                E_eventID = ? 
                WHERE S_slotID = ?");
        
        $stmt->bind_param("sssssii", 
            $slotName, 
            $slotDate, 
            $startTime, 
            $endTime, 
            $location, 
            $eventId, 
            $slot_id
        );

        if ($stmt->execute()) {
            // Log successful update
            error_log("[$currentDateTime] User $currentUser successfully updated slot ID: $slot_id");
            header("Location: view_attendanceslot.php?success=2");
            exit();
        } else {
            $error_message = "Error updating record: " . $conn->error;
            error_log("[$currentDateTime] Error: User $currentUser failed to update slot ID: $slot_id - " . $conn->error);
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Attendance Slot</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .form-container {
            max-width: 800px;
            margin: 0 auto;
        }
        .datetime-info {
            font-size: 0.8rem;
            color: #6c757d;
        }
    </style>
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="form-container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Edit Attendance Slot</h2>
            <div class="datetime-info">
                Last Updated: <?php echo $currentDateTime; ?> UTC
            </div>
        </div>

        <?php if ($error_message): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $error_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if ($slot): ?>
            <form method="POST" class="needs-validation" novalidate>
                <input type="hidden" name="slot_id" value="<?php echo $slot['S_slotID']; ?>">
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Event ID</label>
                        <input type="number" name="event_id" class="form-control" 
                               value="<?php echo $slot['E_eventID']; ?>" required>
                        <div class="invalid-feedback">Please provide an Event ID.</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Slot Name</label>
                        <input type="text" name="slot_name" class="form-control" 
                               value="<?php echo $slot['S_Name']; ?>" required>
                        <div class="invalid-feedback">Please provide a Slot Name.</div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Date</label>
                        <input type="date" name="slot_date" class="form-control" 
                               value="<?php echo $slot['S_Date']; ?>" required>
                        <div class="invalid-feedback">Please select a date.</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Start Time</label>
                        <input type="time" name="start_time" class="form-control" 
                               value="<?php echo $slot['S_startTime']; ?>" required>
                        <div class="invalid-feedback">Please select a start time.</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">End Time</label>
                        <input type="time" name="end_time" class="form-control" 
                               value="<?php echo $slot['S_endTime']; ?>" required>
                        <div class="invalid-feedback">Please select an end time.</div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Location</label>
                    <input type="text" name="location" class="form-control" 
                           value="<?php echo $slot['S_Location']; ?>" required>
                    <div class="invalid-feedback">Please provide a location.</div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Update Slot</button>
                    <a href="view_attendanceslot.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        <?php else: ?>
            <div class="alert alert-danger">
                Slot not found or invalid ID provided.
                <br>
                <a href="view_attendanceslot.php" class="btn btn-primary mt-3">Back to Slot List</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Form validation
(function () {
    'use strict'
    var forms = document.querySelectorAll('.needs-validation')
    Array.prototype.slice.call(forms)
        .forEach(function (form) {
            form.addEventListener('submit', function (event) {
                if (!form.checkValidity()) {
                    event.preventDefault()
                    event.stopPropagation()
                }
                form.classList.add('was-validated')
            }, false)
        })
})()

// Time validation
document.querySelector('form').addEventListener('submit', function(e) {
    const startTime = document.querySelector('input[name="start_time"]').value;
    const endTime = document.querySelector('input[name="end_time"]').value;
    
    if (startTime >= endTime) {
        e.preventDefault();
        alert('End time must be after start time');
    }
});
</script>

</body>
</html>