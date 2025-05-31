<?php
include("includes/dbconnection.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    // First check if the claim exists
    $check_query = "SELECT MC_documentPath FROM meritclaim WHERE MC_claimID = $id";
    $check_result = mysqli_query($conn, $check_query);
    
    if ($check_row = mysqli_fetch_assoc($check_result)) {
        // Delete the claim from database
        $delete_query = "DELETE FROM meritclaim WHERE MC_claimID = $id";
        
        if (mysqli_query($conn, $delete_query)) {
            // Also delete the uploaded file if it exists
            $file_path = $check_row['MC_documentPath'];
            if ($file_path && file_exists($file_path)) {
                unlink($file_path);
            }
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