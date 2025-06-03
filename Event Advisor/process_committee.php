<?php
$conn = new mysqli("localhost", "root", "", "mypetakom_db", "3307");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$E_eventID = $_POST['E_eventID'];
$U_userID = $_POST['U_userID'];
$C_position = $_POST['C_position'];

$sql = "INSERT INTO eventcommittee (C_position, U_userID, E_eventID) 
        VALUES (?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("sii", $C_position, $U_userID, $E_eventID);

if ($stmt->execute()) {
    echo "<script>alert('Committee member assigned successfully.');window.location.href='CommitteeEvent.php';</script>";
} else {
    echo "Error: " . $conn->error;
}

$stmt->close();
$conn->close();
?>