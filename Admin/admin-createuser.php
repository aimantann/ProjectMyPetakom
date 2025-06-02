<?php
session_start();
include('includes/header.php');
include('includes/dbconnection.php');

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = $_POST['role'];
    
    // Validation
    $errors = [];
    
    if (empty($name)) {
        $errors[] = "Full name is required.";
    }
    
    if (empty($phone)) {
        $errors[] = "Phone number is required.";
    }
    
    if (empty($email)) {
        $errors[] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }
    
    if (empty($password)) {
        $errors[] = "Password is required.";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters long.";
    }
    
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }
    
    if (empty($role)) {
        $errors[] = "Role is required.";
    }
    
    // Check if email already exists
    if (empty($errors)) {
        $check_email = "SELECT U_userID FROM user WHERE U_email = ?";
        $stmt = $conn->prepare($check_email);
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $errors[] = "Email already exists.";
        }
    }
    
    // If no errors, create the user
    if (empty($errors)) {
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Insert into user table
            $insert_user = "INSERT INTO user (U_name, U_phoneNum, U_email, U_password, U_usertype) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($insert_user);
            $stmt->bind_param('sssss', $name, $phone, $email, $hashed_password, $role);
            $stmt->execute();
            
            $user_id = $conn->insert_id;
            
            // Insert into role-specific table
            if ($role == 'admin' || $role == 'event_advisor') {
                // Get the correct SP_ID from the staffposition table based on role
                $get_sp_id = "SELECT SP_ID FROM staffposition WHERE SP_Role = ?";
                $stmt = $conn->prepare($get_sp_id);
                $stmt->bind_param('s', $role);
                $stmt->execute();
                $sp_result = $stmt->get_result();
                
                if ($sp_result->num_rows > 0) {
                    $sp_row = $sp_result->fetch_assoc();
                    $sp_id = $sp_row['SP_ID'];
                } else {
                    // Fallback based on your actual database structure
                    $sp_id = ($role == 'admin') ? 2 : 3; // 2 for admin, 3 for event_advisor
                }
                
                // Insert into staff table
                $insert_staff = "INSERT INTO staff (U_userID, SP_ID) VALUES (?, ?)";
                $stmt = $conn->prepare($insert_staff);
                $stmt->bind_param('ii', $user_id, $sp_id);
                $stmt->execute();
            } else {
                // Insert into student table with registration date
                $today = date('Y-m-d');
                $insert_student = "INSERT INTO student (U_userID, STU_registrationDate) VALUES (?, ?)";
                $stmt = $conn->prepare($insert_student);
                $stmt->bind_param('is', $user_id, $today);
                $stmt->execute();
            }
            
            $conn->commit();
            $_SESSION['success_message'] = "User created successfully!";
            header("Location: admin-edituserlist.php");
            exit();
            
        } catch (Exception $e) {
            $conn->rollback();
            $errors[] = "Error creating user: " . $e->getMessage();
        }
    }
}

// Get staff positions for dropdown
$positions_query = "SELECT SP_ID, SP_Role FROM staffposition";
$positions_result = $conn->query($positions_query);
$positions = [];
if ($positions_result->num_rows > 0) {
    while ($row = $positions_result->fetch_assoc()) {
        $positions[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create New User</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .header {
            background: #f8f9fa;
            padding: 20px 0;
            margin-bottom: 30px;
        }
        .logo {
            max-width: 150px;
        }
        .container-main {
            margin: 20px auto;
            max-width: 800px;
        }
        .form-container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .page-title {
            color: #333;
            margin-bottom: 30px;
            text-align: center;
        }
        .form-group label {
            font-weight: bold;
            color: #555;
        }
        .btn-create {
            background: #28a745;
            border-color: #28a745;
            padding: 10px 30px;
        }
        .btn-create:hover {
            background: #218838;
            border-color: #1e7e34;
        }
        .alert {
            margin-bottom: 20px;
        }
        .required {
            color: #dc3545;
        }
    </style>
</head>

<body class="bg-light">

<div class="container-main">
    <div class="form-container">
        <h2 class="page-title">Create New User</h2>
        
        <?php
        if (!empty($errors)) {
            echo "<div class='alert alert-danger'>";
            echo "<ul class='mb-0'>";
            foreach ($errors as $error) {
                echo "<li>" . htmlspecialchars($error) . "</li>";
            }
            echo "</ul>";
            echo "</div>";
        }
        ?>
        
        <form method="POST" action="">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="name">Full Name <span class="required">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" 
                               value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" 
                               required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="phone">Phone Number <span class="required">*</span></label>
                        <input type="tel" class="form-control" id="phone" name="phone" 
                               value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>" 
                               required>
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <label for="email">Email Address <span class="required">*</span></label>
                <input type="email" class="form-control" id="email" name="email" 
                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" 
                       required>
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="password">Password <span class="required">*</span></label>
                        <input type="password" class="form-control" id="password" name="password" 
                               minlength="6" required>
                        <small class="form-text text-muted">Minimum 6 characters</small>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="confirm_password">Confirm Password <span class="required">*</span></label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                               minlength="6" required>
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <label for="role">User Role <span class="required">*</span></label>
                <select class="form-control" id="role" name="role" required>
                    <option value="">Select Role</option>
                    <option value="admin" <?php echo (isset($_POST['role']) && $_POST['role'] == 'admin') ? 'selected' : ''; ?>>Administrator</option>
                    <option value="event_advisor" <?php echo (isset($_POST['role']) && $_POST['role'] == 'event_advisor') ? 'selected' : ''; ?>>Event Advisor</option>
                    <option value="student" <?php echo (isset($_POST['role']) && $_POST['role'] == 'student') ? 'selected' : ''; ?>>Student</option>
                </select>
            </div>
            
            <div class="text-center mt-4">
                <button type="submit" class="btn btn-success btn-create">Create User</button>
                <a href="admin-edituserlist.php" class="btn btn-secondary ml-3">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
// Client-side password confirmation validation
document.getElementById('confirm_password').addEventListener('input', function() {
    const password = document.getElementById('password').value;
    const confirmPassword = this.value;
    
    if (password !== confirmPassword) {
        this.setCustomValidity('Passwords do not match');
    } else {
        this.setCustomValidity('');
    }
});

document.getElementById('password').addEventListener('input', function() {
    const confirmPassword = document.getElementById('confirm_password').value;
    const confirmPasswordField = document.getElementById('confirm_password');
    
    if (confirmPassword && this.value !== confirmPassword) {
        confirmPasswordField.setCustomValidity('Passwords do not match');
    } else {
        confirmPasswordField.setCustomValidity('');
    }
});
</script>

<?php
include('includes/footer.php');
?>

</body>
</html>