<?php
include("includes/dbconnection.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = intval($_POST['id']);
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $points = intval($_POST['points']);

    $query = "UPDATE student_claim_merit SET 
                title = '$title',
                description = '$description',
                points = $points 
              WHERE id = $id";

    if (mysqli_query($conn, $query)) {
        header("Location: student-my-merit-claims.php");
    } else {
        echo "Update failed: " . mysqli_error($conn);
    }
}
?>
