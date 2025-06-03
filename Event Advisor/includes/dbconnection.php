<?php
$servername = "localhost";
$username = "root"; // database username
$password = ""; // database password
$dbname = "mypetakom_db";
$port = "3307";// database port

// Create connection
$conn = mysqli_connect($servername, $username, $password, $dbname, $port);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
