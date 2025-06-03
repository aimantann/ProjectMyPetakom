<?php
$host = "localhost";
$user = "root";
$password = "";
$dbname = "mypetakom_db";
$port = "3307";




$conn = new mysqli($host, $user, $password, $dbname, $port);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

