<?php
$host = "";
$user = "";          // default user
$password = "";          // default is blank in XAMPP
$dbname = "";

$conn = new mysqli($host, $user, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

