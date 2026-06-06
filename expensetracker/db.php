<?php
$host = "sql300.infinityfree.com";
$user = "if0_39648501";          // default user
$password = "patelved1";          // default is blank in XAMPP
$dbname = "if0_39648501_expense_tracker";



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


