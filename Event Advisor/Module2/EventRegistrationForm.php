<?php
session_start();

// Include DB connection
require_once '../includes/dbconnection.php'; 

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['message'] = "Please log in first.";
    $_SESSION['message_type'] = "warning";
    header("Location: ../login.php");
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
    $event_level = $_POST['event_level'];
    $user_id = $_SESSION['user_id']; // Get user ID from session

    $upload_dir = 'uploads/approval_letters/';
    $approval_letter = '';

    // Create upload directory if it doesn't exist
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    // Handle file upload for approval letter
    if (isset($_FILES['approval_letter']) && $_FILES['approval_letter']['error'] == 0) {
        $file_extension = pathinfo($_FILES['approval_letter']['name'], PATHINFO_EXTENSION);
        $new_filename = 'approval_' . time() . '.' . $file_extension;
        $upload_path = $upload_dir . $new_filename;

        // Validate file type
        $allowed_types = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];
        if (in_array(strtolower($file_extension), $allowed_types)) {
            if (move_uploaded_file($_FILES['approval_letter']['tmp_name'], $upload_path)) {
                $approval_letter = $upload_path;
            } else {
                $_SESSION['message'] = "Failed to upload approval letter.";
                $_SESSION['message_type'] = "danger";
            }
        } else {
            $_SESSION['message'] = "Invalid file type for approval letter.";
            $_SESSION['message_type'] = "danger";
        }
    }

    // Insert event into database
    if (empty($_SESSION['message'])) {
        $sql = "INSERT INTO event (E_name, E_description, E_startDate, E_endDate, E_geoLocation, E_eventStatus, E_approvalLetter, E_level, U_userID) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssssi", $event_name, $description, $start_date, $end_date, $geo_location, $event_status, $approval_letter, $event_level, $user_id);

        if ($stmt->execute()) {
            $_SESSION['message'] = "Event registered successfully!";
            $_SESSION['message_type'] = "success";
            header("Location: EventList.php");
            exit();
        } else {
            $_SESSION['message'] = "Error: " . $stmt->error;
            $_SESSION['message_type'] = "danger";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Registration</title>
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
            background: linear-gradient(135deg,rgba(0, 60, 255, 0.63),rgba(0, 0, 0, 0.45));
            color: white;
            padding: 1.5rem;
            margin: -2rem -2rem 2rem -2rem;
            border-radius: 15px 15px 0 0;
        }
        .required {
            color: #dc3545;
        }
        .form-control:focus {
            border-color:rgba(255, 0, 0, 0.05);
            box-shadow: 0 0 0 0.2rem rgba(255, 0, 0, 0.12);
        }
    </style>
</head>
<body class="bg-light">
    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="form-container">
                    <div class="form-header text-center">
                        <h2><i class="fas fa-plus-circle me-2"></i>Event Registration</h2>
                        <p class="mb-0">Register a new event</p>
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

                    <form method="POST" enctype="multipart/form-data" id="eventForm">
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="event_name" class="form-label">
                                    <i class="fas fa-tag me-1"></i>Event Name <span class="required">*</span>
                                </label>
                                <input type="text" class="form-control" id="event_name" name="event_name" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">
                                <i class="fas fa-align-left me-1"></i>Event Description <span class="required">*</span>
                            </label>
                            <textarea class="form-control" id="description" name="description" rows="4" required></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="start_date" class="form-label">
                                    <i class="fas fa-calendar-plus me-1"></i>Start Date <span class="required">*</span>
                                </label>
                                <input type="date" class="form-control" id="start_date" name="start_date" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="end_date" class="form-label">
                                    <i class="fas fa-calendar-minus me-1"></i>End Date <span class="required">*</span>
                                </label>
                                <input type="date" class="form-control" id="end_date" name="end_date" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="geo_location" class="form-label">
                                <i class="fas fa-map-marker-alt me-1"></i>Event Location <span class="required">*</span>
                            </label>
                            <input type="text" class="form-control" id="geo_location" name="geo_location" 
                                   placeholder="e.g., Main Hall" required>
                        </div>

                        <div class="mb-3">
                            <label for="event_status" class="form-label">
                                <i class="fas fa-flag me-1"></i>Event Status
                            </label>
                            <select class="form-select" id="event_status" name="event_status">
                                <option value="Active">Active</option>
                                <option value="Postponed">Postponed</option>
                                <option value="Cancelled">Cancelled</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="event_level" class="form-label">
                                <i class="fas fa-layer-group me-1"></i>Event Level <span class="required">*</span>
                            </label>
                            <select class="form-select" id="event_level" name="event_level" required>
                                <option value="">-- Select Level --</option>
                                <option value="International">International</option>
                                <option value="National">National</option>
                                <option value="State">State</option>
                                <option value="District">District</option>
                                <option value="UMPSA">UMPSA</option>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label for="approval_letter" class="form-label">
                                <i class="fas fa-file-upload me-1"></i>Approval Letter <span class="required">*</span>
                            </label>
                            <input type="file" class="form-control" id="approval_letter" name="approval_letter" 
                                   accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" required>
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1"></i>
                                Accepted formats: PDF, DOC, DOCX, JPG, JPEG, PNG (Max size: 5MB)
                            </div>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="../advisor-dashboard.php" class="btn btn-secondary me-md-2">
                                <i class="fas fa-times me-1"></i>Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>Register Event
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Form validation
        document.getElementById('eventForm').addEventListener('submit', function(e) {
            const startDate = new Date(document.getElementById('start_date').value);
            const endDate = new Date(document.getElementById('end_date').value);
            
            if (endDate < startDate) {
                e.preventDefault();
                alert('End date must be after start date');
                return false;
            }
            
            // Check file size
            const fileInput = document.getElementById('approval_letter');
            if (fileInput.files.length > 0) {
                const fileSize = fileInput.files[0].size / 1024 / 1024; // Convert to MB
                if (fileSize > 5) {
                    e.preventDefault();
                    alert('File size must be less than 5MB');
                    return false;
                }
            }
        });

        // Set minimum date to today
        document.getElementById('start_date').min = new Date().toISOString().split('T')[0];
        
        // Update end date minimum when start date changes
        document.getElementById('start_date').addEventListener('change', function() {
            document.getElementById('end_date').min = this.value;
        });
    </script>
</body>
</html>