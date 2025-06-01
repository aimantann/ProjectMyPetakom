<?php
session_start();
include('includes/dbconnection.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['membership_id'], $_POST['action'])) {
    $membership_id = intval($_POST['membership_id']);
    $action = $_POST['action'];

    if ($action === 'approve') {
        $status = 'Approved';
        $msg = 'Membership application approved.';
    } elseif ($action === 'reject') {
        $status = 'Rejected';
        $msg = 'Membership application rejected.';
    } else {
        $_SESSION['approve_error'] = "Invalid action.";
        header("Location: admin-approvemembership.php");
        exit;
    }

    $stmt = $conn->prepare("UPDATE membership SET M_status = ? WHERE M_membershipID = ?");
    $stmt->bind_param('si', $status, $membership_id);
    if ($stmt->execute()) {
        $_SESSION['approve_success'] = $msg;
    } else {
        $_SESSION['approve_error'] = "Failed to update application status.";
    }
    $stmt->close();
} else {
    $_SESSION['approve_error'] = "Invalid request.";
}

header("Location: admin-approvemembership.php");
exit;
?>