<?php
require_once('user-validatesession.php');

ob_start(); // Start output buffering

include('includes/header.php');
include('includes/dbconnection.php');

$event_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$event_id) {
    $_SESSION['message'] = "Invalid event ID.";
    $_SESSION['message_type'] = "danger";
    header("Location: EventList.php");
    exit();
}

// Fetch event details
try {
    $sql = "SELECT * FROM event WHERE E_eventID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $event_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        $_SESSION['message'] = "Event not found.";
        $_SESSION['message_type'] = "danger";
        header("Location: EventList.php");
        exit();
    }

    $event = $result->fetch_assoc();
} catch (Exception $e) {
    $_SESSION['message'] = "Database error: " . $e->getMessage();
    $_SESSION['message_type'] = "danger";
    header("Location: EventList.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $event_name = trim($_POST['event_name']);
    $description = trim($_POST['description']);
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $geo_location = trim($_POST['geo_location']);
    $event_status = $_POST['event_status'];

    $upload_dir = 'uploads/approval_letters/';
    $approval_letter = $event['E_approvalLetter'];

    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    if (isset($_FILES['approval_letter']) && $_FILES['approval_letter']['error'] == 0) {
        $file_extension = pathinfo($_FILES['approval_letter']['name'], PATHINFO_EXTENSION);
        $new_filename = 'approval_' . $event_id . '_' . time() . '.' . $file_extension;
        $upload_path = $upload_dir . $new_filename;

        $allowed_types = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];
        if (in_array(strtolower($file_extension), $allowed_types)) {
            if (move_uploaded_file($_FILES['approval_letter']['tmp_name'], $upload_path)) {
                if (!empty($event['E_approvalLetter']) && file_exists($event['E_approvalLetter'])) {
                    unlink($event['E_approvalLetter']);
                }
                $approval_letter = $upload_path;
            } else {
                $_SESSION['message'] = "Failed to upload new approval letter.";
                $_SESSION['message_type'] = "danger";
            }
        } else {
            $_SESSION['message'] = "Invalid file type for approval letter.";
            $_SESSION['message_type'] = "danger";
        }
    }

    if (empty($_SESSION['message'])) {
        try {
            $sql = "UPDATE event SET 
                        E_name = ?, 
                        E_description = ?, 
                        E_startDate = ?, 
                        E_endDate = ?, 
                        E_geoLocation = ?, 
                        E_eventStatus = ?, 
                        E_approvalLetter = ? 
                    WHERE E_eventID = ?";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssssi",
                $event_name,
                $description,
                $start_date,
                $end_date,
                $geo_location,
                $event_status,
                $approval_letter,
                $event_id
            );

            if ($stmt->execute()) {
                $_SESSION['message'] = "Event updated successfully!";
                $_SESSION['message_type'] = "success";
                ob_end_clean(); // Clear buffer
                header("Location: EventList.php"); // Adjust path if needed
                exit();
            }
        } catch (Exception $e) {
            $_SESSION['message'] = "Error updating event: " . $e->getMessage();
            $_SESSION['message_type'] = "danger";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Event - MyPetakom</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .form-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            padding: 2rem;
        }
        .form-header {
            background: linear-gradient(135deg, #ffc107, #e0a800);
            color: white;
            padding: 1.5rem;
            margin: -2rem -2rem 2rem -2rem;
            border-radius: 15px 15px 0 0;
        }
        .required {
            color: #dc3545;
        }
        .form-control:focus {
            border-color: #ffc107;
            box-shadow: 0 0 0 0.2rem rgba(255, 193, 7, 0.25);
        }
        .current-file {
            background: #f8f9fa;
            padding: 0.75rem;
            border-radius: 0.375rem;
            border: 1px solid #dee2e6;
        }
    </style>
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand" href="#"><i class="fas fa-calendar-alt me-2"></i>Event System</a>
        <div class="navbar-nav ms-auto">
            <?php if (isset($_SESSION['username'])): ?>
                <span class="navbar-text me-3">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
            <?php endif; ?>
        </div>
    </div>
</nav>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="form-container">
                <div class="form-header text-center">
                    <h2><i class="fas fa-edit me-2"></i>Edit Event</h2>
                    <p class="mb-0">Update event information</p>
                </div>

                <?php if (isset($_SESSION['message'])): ?>
                    <div class="alert alert-<?php echo $_SESSION['message_type']; ?> alert-dismissible fade show">
                        <?php
                        echo $_SESSION['message'];
                        unset($_SESSION['message'], $_SESSION['message_type']);
                        ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if ($event): ?>
                    <form method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="event_name" class="form-label">Event Name <span class="required">*</span></label>
                            <input type="text" class="form-control" id="event_name" name="event_name"
                                   value="<?php echo htmlspecialchars($event['E_name']); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description <span class="required">*</span></label>
                            <textarea class="form-control" id="description" name="description" rows="4" required><?php
                                echo htmlspecialchars($event['E_description']);
                                ?></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="start_date" class="form-label">Start Date <span class="required">*</span></label>
                                <input type="date" class="form-control" id="start_date" name="start_date"
                                       value="<?php echo htmlspecialchars($event['E_startDate']); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="end_date" class="form-label">End Date <span class="required">*</span></label>
                                <input type="date" class="form-control" id="end_date" name="end_date"
                                       value="<?php echo htmlspecialchars($event['E_endDate']); ?>" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="geo_location" class="form-label">Location <span class="required">*</span></label>
                            <input type="text" class="form-control" id="geo_location" name="geo_location"
                                   value="<?php echo htmlspecialchars($event['E_geoLocation']); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="event_status" class="form-label">Status</label>
                            <select class="form-select" id="event_status" name="event_status">
                                <option value="Active" <?php echo $event['E_eventStatus'] == 'Active' ? 'selected' : ''; ?>>Active</option>
                                <option value="Postponed" <?php echo $event['E_eventStatus'] == 'Postponed' ? 'selected' : ''; ?>>Postponed</option>
                                <option value="Cancelled" <?php echo $event['E_eventStatus'] == 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label for="approval_letter" class="form-label">Approval Letter</label>
                            <input type="file" class="form-control" id="approval_letter" name="approval_letter"
                                   accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                            <div class="form-text">
                                Accepted formats: PDF, DOC, DOCX, JPG, JPEG, PNG (Max size: 5MB)
                            </div>
                            <?php if (!empty($event['E_approvalLetter'])): ?>
                                <div class="current-file mt-2">
                                    <i class="fas fa-paperclip me-2"></i>
                                    Current file: <?php echo basename($event['E_approvalLetter']); ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="EventList.php" class="btn btn-secondary me-md-2">Cancel</a>
                            <button type="submit" class="btn btn-primary">Update Event</button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const startInput = document.getElementById('start_date');
    const endInput = document.getElementById('end_date');

    const today = new Date().toISOString().split('T')[0];
    startInput.min = today;

    startInput.addEventListener('change', function () {
        endInput.min = this.value;
    });
</script>

<?php
include('includes/footer.php');
ob_end_flush(); // Send output
?>
</body>
</html>
