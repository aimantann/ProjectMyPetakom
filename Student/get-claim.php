<?php
include("includes/dbconnection.php");

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $query = "SELECT * FROM meritclaim WHERE MC_claimID = $id";
    $result = mysqli_query($conn, $query);

    if ($row = mysqli_fetch_assoc($result)) {
        echo json_encode($row);
    } else {
        echo json_encode(["error" => "Claim not found."]);
    }
} else {
    echo json_encode(["error" => "No ID provided."]);
}
?>