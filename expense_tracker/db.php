<?php
$host = "localhost";
$user = "root";          // default user
$password = "";          // default is blank in XAMPP
$dbname = "expense_tracker";

$conn = new mysqli($host, $user, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
