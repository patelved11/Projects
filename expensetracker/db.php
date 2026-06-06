<?php
$host = "";
$user = "";          // default user
$password = "";          // default is blank in XAMPP
$dbname = "";



$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    // For API calls - return JSON error instead of HTML
    header('Content-Type: application/json');
    die(json_encode([
        'success' => false,
        'message' => 'DB Error: ' . $conn->connect_error
    ]));
}
$conn->set_charset("utf8mb4");


