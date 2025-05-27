<?php
include("includes/dbconnection.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $query = "DELETE FROM student_claim_merit WHERE id = $id";

    if (mysqli_query($conn, $query)) {
        echo "success";
    } else {
        echo "error";
    }
}
?>
