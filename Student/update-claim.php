<?php
include("includes/dbconnection.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id'])) {
    $id = intval($_POST['id']);
    $role = mysqli_real_escape_string($conn, $_POST['role']);
    
    // Check if claim exists and is still pending
    $check_query = "SELECT MC_claimStatus FROM meritclaim WHERE MC_claimID = $id";
    $check_result = mysqli_query($conn, $check_query);
    
    if ($check_row = mysqli_fetch_assoc($check_result)) {
        if (strtolower($check_row['MC_claimStatus']) !== 'pending') {
            echo "error: Can only edit pending claims";
            exit;
        }
        
        // Update only the role since we're not using event_id anymore
        $update_query = "UPDATE meritclaim 
                        SET MC_role = '$role' 
                        WHERE MC_claimID = $id";
        
        if (mysqli_query($conn, $update_query)) {
            echo "success";
        } else {
            echo "error: " . mysqli_error($conn);
        }
    } else {
        echo "error: Claim not found";
    }
} else {
    echo "error: Invalid request";
}
?>