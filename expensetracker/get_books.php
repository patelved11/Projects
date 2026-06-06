<?php
require_once "db.php";
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

$token = $_GET['token'] ?? '';
if (empty($token)) {
    echo json_encode(['success' => false, 'message' => 'Token required']);
    exit;
}

$stmt = $conn->prepare("SELECT user_id FROM users WHERE api_token = ?");
$stmt->bind_param("s", $token);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid token']);
    exit;
}
$user = $res->fetch_assoc();
$user_id = $user['user_id'];

$col_check = $conn->query("SHOW COLUMNS FROM books LIKE 'book_id'");
$id_col = ($col_check && $col_check->num_rows > 0) ? 'book_id' : 'id';

$bk = $conn->prepare("SELECT $id_col AS id, book_name AS name FROM books WHERE user_id = ?");
$bk->bind_param("i", $user_id);
$bk->execute();
$books_res = $bk->get_result();
$books = [];
while ($row = $books_res->fetch_assoc()) {
    $books[] = ['id' => $row['id'], 'name' => $row['name']];
}

echo json_encode(['success' => true, 'books' => $books]);
