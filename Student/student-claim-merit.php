<?php
session_start();
include("includes/dbconnection.php");

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $event_id = $_POST['event_id'];
    $role = $_POST['role'];

    
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
        
        $sql = "INSERT INTO meritclaim (E_eventID, MC_role, MC_documentPath, MC_submitDate, MC_claimStatus) 
                VALUES (?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issss", $event_id, $role, $file_path, $submit_date, $claim_status);
        
        if ($stmt->execute()) {
            header('Location: student-my-merit-claims.php?success=1');
            exit();
        } else {
            $error_message = "Error submitting claim: " . $conn->error;
        }
        
        $stmt->close();
    }
}

// Dummy events data (replace with actual database query)
$dummy_events = [
    ['id' => 1, 'name' => 'Programming Competition 2024'],
    ['id' => 2, 'name' => 'Tech Talk: AI in Web Development'],
    ['id' => 3, 'name' => 'Hackathon 2024'],
    ['id' => 4, 'name' => 'Web Design Workshop'],
    ['id' => 5, 'name' => 'Cybersecurity Seminar'],
    ['id' => 6, 'name' => 'Mobile App Development Course'],
    ['id' => 7, 'name' => 'Database Management Workshop'],
    ['id' => 8, 'name' => 'UI/UX Design Competition'],
    ['id' => 9, 'name' => 'Cloud Computing Seminar'],
    ['id' => 10, 'name' => 'Data Science Workshop']
];

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Claim Merit - MyPetakom</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
            color: #333;
            line-height: 1.6;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            display: flex;
            align-items: center;
            margin-bottom: 30px;
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .back-btn {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: flex;
            align-items: center;
            margin-right: 20px;
            transition: background-color 0.3s;
        }

        .back-btn:hover {
            background-color: #0056b3;
            text-decoration: none;
            color: white;
        }

        .back-btn i {
            margin-right: 5px;
        }

        .page-title {
            font-size: 28px;
            color: #333;
            font-weight: 600;
        }

        .form-container {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
            font-size: 14px;
        }

        .required {
            color: #dc3545;
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s, box-shadow 0.3s;
        }

        .form-control:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }

        select.form-control {
            cursor: pointer;
        }

        textarea.form-control {
            resize: vertical;
            min-height: 100px;
        }

        .file-upload {
            position: relative;
            display: inline-block;
            width: 100%;
        }

        .file-upload input[type="file"] {
            position: absolute;
            left: -9999px;
        }

        .file-upload-label {
            display: block;
            padding: 12px 15px;
            border: 2px dashed #007bff;
            border-radius: 8px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            background-color: #f8f9fa;
        }

        .file-upload-label:hover {
            background-color: #e9ecef;
            border-color: #0056b3;
        }

        .file-upload-label i {
            margin-right: 8px;
            color: #007bff;
        }

        .file-info {
            margin-top: 10px;
            padding: 8px 12px;
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            border-radius: 5px;
            font-size: 14px;
            color: #155724;
        }

        .btn-submit {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            border: none;
            padding: 15px 30px;
            font-size: 16px;
            font-weight: 600;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
            width: 100%;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            font-weight: 500;
        }

        .alert-danger {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }

        .help-text {
            font-size: 12px;
            color: #6c757d;
            margin-top: 5px;
        }

        .role-options {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 10px;
            margin-top: 8px;
        }

        .role-option {
            display: none;
        }

        .role-option + label {
            display: block;
            padding: 12px 15px;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            font-weight: 500;
        }

        .role-option:checked + label {
            background-color: #007bff;
            color: white;
            border-color: #007bff;
        }

        .role-option + label:hover {
            border-color: #007bff;
            background-color: #f8f9fa;
        }

        .role-option:checked + label:hover {
            background-color: #0056b3;
        }

        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }

            .header {
                flex-direction: column;
                align-items: flex-start;
            }

            .back-btn {
                margin-right: 0;
                margin-bottom: 15px;
            }

            .form-container {
                padding: 20px;
            }

            .role-options {
                grid-template-columns: 1fr;
            }
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <!-- Header with Back Button and Title -->
        <div class="header">
            <a href="student-dashboard.php" class="back-btn">
                <i class="fas fa-arrow-left"></i>
                Back
            </a>
            <h1 class="page-title">Claim Merit</h1>
        </div>

        <!-- Claim Form -->
        <div class="form-container">
            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data" id="claimForm">
                <!-- Event Selection -->
                <div class="form-group">
                    <label for="event_id" class="form-label">
                        Select Event <span class="required">*</span>
                    </label>
                    <select name="event_id" id="event_id" class="form-control" required>
                        <option value="">-- Choose an Event --</option>
                        <?php foreach ($dummy_events as $event): ?>
                            <option value="<?php echo $event['id']; ?>">
                                <?php echo htmlspecialchars($event['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Role Selection -->
                <div class="form-group">
                    <label class="form-label">
                        Your Role in the Event <span class="required">*</span>
                    </label>
                    <div class="role-options">
                        <input type="radio" name="role" id="participant" value="Participant" class="role-option" required>
                        <label for="participant">
                            <i class="fas fa-user"></i>
                            Participant
                        </label>

                        <input type="radio" name="role" id="committee" value="Committee" class="role-option" required>
                        <label for="committee">
                            <i class="fas fa-users"></i>
                            Committee
                        </label>

                        <input type="radio" name="role" id="main_committee" value="Main Committee" class="role-option" required>
                        <label for="main_committee">
                            <i class="fas fa-crown"></i>
                            Main Committee
                        </label>
                    </div>
                </div>



                <!-- File Upload -->
                <div class="form-group">
                    <label class="form-label">
                        Official Participation Letter <span class="required">*</span>
                    </label>
                    <div class="file-upload">
                        <input type="file" name="participation_letter" id="participation_letter" 
                               accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" required>
                        <label for="participation_letter" class="file-upload-label">
                            <i class="fas fa-cloud-upload-alt"></i>
                            Click to upload or drag and drop
                        </label>
                    </div>
                    <div class="help-text">
                        Accepted formats: PDF, DOC, DOCX, JPG, JPEG, PNG (Max size: 5MB)
                    </div>
                    <div id="file-info" class="file-info" style="display: none;"></div>
                </div>

                <!-- Submit Button -->
                <div class="form-group">
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-paper-plane"></i>
                        Submit Claim for Approval
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // File upload handler
        document.getElementById('participation_letter').addEventListener('change', function(e) {
            const fileInfo = document.getElementById('file-info');
            const file = e.target.files[0];
            
            if (file) {
                const fileSize = (file.size / 1024 / 1024).toFixed(2);
                fileInfo.innerHTML = `
                    <i class="fas fa-file"></i>
                    Selected: ${file.name} (${fileSize} MB)
                `;
                fileInfo.style.display = 'block';
                
                // Check file size (5MB limit)
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

            // Show loading state
            const submitBtn = document.querySelector('.btn-submit');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';
            submitBtn.disabled = true;
        });
    </script>
</body>
</html>