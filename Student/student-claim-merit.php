<?php
session_start();
date_default_timezone_set('Asia/Kuala_Lumpur');
include('includes/header.php');
include("includes/dbconnection.php");

// Check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    $_SESSION['login_required'] = "Please login as a student to access this page.";
    header('Location: user-login.php');
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $event_id = $_POST['event_id'];
    $role = $_POST['role'];
    $user_id = $_SESSION['user_id'];
    
    // Handle file upload
    $upload_dir = "uploads/merit_claims/";
    $file_path = "";
    
    if (!empty($_FILES['participation_letter']['name'])) {
        // Create directory if it doesn't exist
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_name = time() . "_" . basename($_FILES['participation_letter']['name']);
        $target_file = $upload_dir . $file_name;
        
        // Check file type
        $allowed_types = array('pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png');
        $file_extension = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        
        if (in_array($file_extension, $allowed_types)) {
            if (move_uploaded_file($_FILES['participation_letter']['tmp_name'], $target_file)) {
                $file_path = $target_file;
            } else {
                $error_message = "Error uploading file.";
            }
        } else {
            $error_message = "Invalid file type. Please upload PDF, DOC, DOCX, JPG, JPEG, or PNG files only.";
        }
    }
    
    // Insert into database if no errors
    if (!isset($error_message)) {
        $submit_date = date('Y-m-d H:i:s');
        $claim_status = 'Pending';
        
        // Check if claim already exists
        $check_query = "SELECT MC_claimID FROM meritclaim 
                       WHERE U_userID = ? AND E_eventID = ?";
        $check_stmt = $conn->prepare($check_query);
        $check_stmt->bind_param("ii", $user_id, $event_id);
        $check_stmt->execute();
        $existing_claim = $check_stmt->get_result()->fetch_assoc();
        
        if (!$existing_claim) {
            $sql = "INSERT INTO meritclaim (E_eventID, U_userID, MC_role, MC_documentPath, MC_submitDate, MC_claimStatus) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iissss", $event_id, $user_id, $role, $file_path, $submit_date, $claim_status);
            
            if ($stmt->execute()) {
                echo "<script>
                    window.location.href = 'student-my-merit-claims.php?success=1';
                </script>";
                exit();
            }
            
            $stmt->close();
        } else {
            $error_message = "You have already submitted a claim for this event.";
        }
        $check_stmt->close();
    }
}

// Get available events
$events_query = "SELECT e.E_eventID, e.E_name 
                FROM event e 
                WHERE e.E_eventStatus = 'Active' 
                AND e.E_endDate >= CURDATE()
                ORDER BY e.E_startDate DESC";
$events_result = mysqli_query($conn, $events_query);

// Store events in array
$events = [];
while ($row = mysqli_fetch_assoc($events_result)) {
    $events[] = $row;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Claim Merit - MyPetakom</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            padding-top: 20px;
        }

        .card {
            border: none;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            border-radius: 15px;
        }

        .card-header {
            background-color: #fff;
            border-bottom: 1px solid #eee;
            border-radius: 15px 15px 0 0 !important;
        }

        .btn-primary {
            background-color: #0d6efd;
            border: none;
            padding: 8px 20px;
        }

        .btn-primary:hover {
            background-color: #0b5ed7;
        }

        .role-options label {
            border: 2px solid #dee2e6;
            border-radius: 10px;
            padding: 15px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }

        .role-options input[type="radio"]:checked + label {
            background-color: #0d6efd;
            color: white;
            border-color: #0d6efd;
        }

        .file-upload {
            border: 2px dashed #dee2e6;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }

        .file-upload:hover {
            border-color: #0d6efd;
            background-color: #f8f9fa;
        }

        .required {
            color: red;
        }

        .alert {
            opacity: 1;
            transition: opacity 5s ease-out;
        }

        .alert.fade-out {
            opacity: 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card mb-4">
            <div class="card-header p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-0">Claim Merit</h4>
                    </div>
                </div>
            </div>
            <div class="card-body p-4">
                <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger" role="alert" id="errorAlert">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <?php echo $error_message; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data" id="claimForm">
                    <!-- Event Selection -->
                    <div class="mb-4">
                        <label for="event_id" class="form-label fw-bold">
                            Select Event <span class="required">*</span>
                        </label>
                        <select name="event_id" id="event_id" class="form-select" required>
                            <option value="">-- Choose an Event --</option>
                            <?php if (!empty($events)): ?>
                                <?php foreach ($events as $event): ?>
                                    <option value="<?php echo $event['E_eventID']; ?>">
                                        <?php echo htmlspecialchars($event['E_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <option value="" disabled>No active events available</option>
                            <?php endif; ?>
                        </select>
                        <?php if (empty($events)): ?>
                            <div class="form-text text-danger">
                                <i class="fas fa-info-circle"></i> 
                                There are currently no active events available for merit claims.
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Role Selection -->
                    <div class="mb-4">
                        <label class="form-label fw-bold">
                            Your Role in the Event <span class="required">*</span>
                        </label>
                        <div class="role-options row g-3">
                            <div class="col-md-4">
                                <input type="radio" class="btn-check" name="role" id="participant" 
                                       value="Participant" required>
                                <label class="btn w-100 h-100" for="participant">
                                    <i class="fas fa-user mb-2"></i>
                                    <div>Participant</div>
                                </label>
                            </div>
                            <div class="col-md-4">
                                <input type="radio" class="btn-check" name="role" id="committee" 
                                       value="Committee" required>
                                <label class="btn w-100 h-100" for="committee">
                                    <i class="fas fa-users mb-2"></i>
                                    <div>Committee</div>
                                </label>
                            </div>
                            <div class="col-md-4">
                                <input type="radio" class="btn-check" name="role" id="main_committee" 
                                       value="Main Committee" required>
                                <label class="btn w-100 h-100" for="main_committee">
                                    <i class="fas fa-crown mb-2"></i>
                                    <div>Main Committee</div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- File Upload -->
                    <div class="mb-4">
                        <label class="form-label fw-bold">
                            Official Letter <span class="required">*</span>
                        </label>
                        <div class="file-upload">
                            <input type="file" class="form-control" name="participation_letter" 
                                   id="participation_letter" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" required>
                        </div>
                        <div class="form-text">
                            Accepted formats: PDF, DOC, DOCX, JPG, JPEG, PNG (Max size: 5MB)
                        </div>
                        <div id="file-info" class="alert alert-success mt-2" style="display: none;"></div>
                    </div>

                    <!-- Submit Button -->
                    <div class="text-end">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-paper-plane me-2"></i>
                            Submit Claim for Approval
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
    
    <script>
    // File upload handler
    document.getElementById('participation_letter').addEventListener('change', function(e) {
        const fileInfo = document.getElementById('file-info');
        const file = e.target.files[0];
        
        if (file) {
            const fileSize = (file.size / 1024 / 1024).toFixed(2);
            fileInfo.innerHTML = `
                <i class="fas fa-file me-2"></i>
                Selected: ${file.name} (${fileSize} MB)
            `;
            fileInfo.style.display = 'block';
            
            if (file.size > 5 * 1024 * 1024) {
                alert('File size must be less than 5MB');
                e.target.value = '';
                fileInfo.style.display = 'none';
            }
        } else {
            fileInfo.style.display = 'none';
        }
    });

    // Form validation
    document.getElementById('claimForm').addEventListener('submit', function(e) {
        const eventId = document.getElementById('event_id').value;
        const role = document.querySelector('input[name="role"]:checked');
        const file = document.getElementById('participation_letter').files[0];

        if (!eventId || !role || !file) {
            e.preventDefault();
            alert('Please fill in all required fields');
            return false;
        }

        const submitBtn = this.querySelector('button[type="submit"]');
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Submitting...';
        submitBtn.disabled = true;
    });

    // Add fade out effect for error message
    const errorAlert = document.getElementById('errorAlert');
    if (errorAlert) {
        setTimeout(() => {
            errorAlert.classList.add('fade-out');
            setTimeout(() => {
                errorAlert.style.display = 'none';
            }, 5000); // Wait for fade out animation to complete
        }, 100);
    }
</script>
<?php
include('includes/footer.php');
?>
</body>
</html>