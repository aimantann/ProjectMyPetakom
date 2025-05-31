<?php
session_start();
include('includes/dbconnection.php');

// Make sure user is logged in and has email
if (!isset($_SESSION['email'])) {
    header("Location: user-login.php");
    exit;
}

// Get user ID (primary key, not email)
$user_email = $_SESSION['email'];
$user_id = null;
$stmt = $conn->prepare("SELECT U_userID FROM user WHERE U_email = ? LIMIT 1");
$stmt->bind_param('s', $user_email);
$stmt->execute();
$stmt->bind_result($user_id);
$stmt->fetch();
$stmt->close();

if (!$user_id) {
    $_SESSION['error'] = "User not found.";
    header("Location: student-applymembership.php");
    exit;
}

// Handle file upload
$upload_dir = "uploads/studentcards/";
if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

if (isset($_FILES['studentCard']) && $_FILES['studentCard']['error'] == UPLOAD_ERR_OK) {
    $allowed = ['jpg', 'jpeg', 'png', 'pdf'];
    $ext = strtolower(pathinfo($_FILES['studentCard']['name'], PATHINFO_EXTENSION));

    if (!in_array($ext, $allowed)) {
        $_SESSION['error'] = "Invalid file type. Only JPG, PNG, or PDF allowed.";
        header("Location: student-applymembership.php");
        exit;
    }
    $newname = 'studentcard_' . $user_id . '_' . time() . '.' . $ext;
    $target = $upload_dir . $newname;
    if (!move_uploaded_file($_FILES['studentCard']['tmp_name'], $target)) {
        $_SESSION['error'] = "Failed to upload file.";
        header("Location: student-applymembership.php");
        exit;
    }
} else {
    $_SESSION['error'] = "Please upload your student card.";
    header("Location: student-applymembership.php");
    exit;
}

// Insert membership application (or update if exists)
$date = date('Y-m-d');
$status = 'Pending';
$studentCardPath = $target;

// Check if user already has a membership application
$stmt = $conn->prepare("SELECT M_membershipID FROM membership WHERE U_userID = ?");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    // Update existing application
    $stmt->close();
    $stmt2 = $conn->prepare("UPDATE membership SET M_applicationDate=?, M_status=?, M_studentCard=? WHERE U_userID=?");
    $stmt2->bind_param('sssi', $date, $status, $studentCardPath, $user_id);
    if ($stmt2->execute()) {
        $_SESSION['success'] = "Membership application updated successfully.";
        $_SESSION['show_success_modal'] = true;
    } else {
        $_SESSION['error'] = "Failed to update application.";
    }
    $stmt2->close();
} else {
    // Insert new application
    $stmt->close();
    $stmt2 = $conn->prepare("INSERT INTO membership (M_applicationDate, M_status, U_userID, M_studentCard) VALUES (?, ?, ?, ?)");
    $stmt2->bind_param('ssis', $date, $status, $user_id, $studentCardPath);
    if ($stmt2->execute()) {
        $_SESSION['success'] = "Membership application submitted successfully.";
        $_SESSION['show_success_modal'] = true;
    } else {
        $_SESSION['error'] = "Failed to submit application.";
    }
    $stmt2->close();
}

// Redirect to membership application page after submit
header("Location: student-applymembership.php");
exit;
?>