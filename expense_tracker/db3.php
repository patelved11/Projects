<?php
$host = "sql300.infinityfree.com";
$user = "if0_39648501";          // default user
$password = "patelved1";          // default is blank in XAMPP
$dbname = "if0_39648501_expense_tracker";

$conn = new mysqli($host, $user, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
