<?php
require_once "db.php";
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(['success' => false, 'message' => 'POST required']);
    exit;
}

$token = $_POST['token'] ?? '';
$book_id = (int)($_POST['book_id'] ?? 0);
$date = $_POST['date'] ?? '';
$description = trim($_POST['description'] ?? '');
$amount = abs(floatval($_POST['amount'] ?? 0));
$type = $_POST['type'] ?? 'income';
// Optional check for delete functionality if we want to replace day's entry
$delete_id = (int)($_POST['delete_id'] ?? 0);

if (empty($token)) {
    echo json_encode(['success' => false, 'message' => 'Invalid token']);
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

if ($delete_id > 0) {
    $del = $conn->prepare("DELETE FROM expenses WHERE id = ? AND user_id = ?");
    $del->bind_param("ii", $delete_id, $user_id);
    $del->execute();
}

if (empty($book_id) || empty($date) || empty($description) || $amount <= 0) {
    // If just deleting, we can exit here
    if ($delete_id > 0) {
        doRecalc($conn, $user_id, $book_id);
        echo json_encode(['success' => true, 'message' => 'Entry deleted', 'entry_id' => null]);
        exit;
    }
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

$bk = $conn->prepare("SELECT * FROM books WHERE $id_col = ? AND user_id = ?");
$bk->bind_param("ii", $book_id, $user_id);
$bk->execute();
if ($bk->get_result()->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid book']);
    exit;
}

$cat = "Trading";
$c_check = $conn->prepare("SELECT id FROM categories WHERE categories = ? AND users_id IN(1, ?)");
$c_check->bind_param("si", $cat, $user_id);
$c_check->execute();
if ($c_check->get_result()->num_rows === 0) {
    $c_ins = $conn->prepare("INSERT INTO categories (categories, users_id) VALUES (?, ?)");
    $c_ins->bind_param("si", $cat, $user_id);
    $c_ins->execute();
}

$inc = ($type === 'income') ? $amount : 0;
$exp = ($type === 'expense') ? $amount : 0;

$ins = $conn->prepare("INSERT INTO expenses (user_id, book_id, date, description, category_name, income, expense, balance) VALUES (?, ?, ?, ?, ?, ?, ?, 0)");
$ins->bind_param("iisssdd", $user_id, $book_id, $date, $description, $cat, $inc, $exp);
if ($ins->execute()) {
    $entry_id = $ins->insert_id;
    doRecalc($conn, $user_id, $book_id);
    echo json_encode(['success' => true, 'message' => 'Entry added', 'entry_id' => $entry_id]);
} else {
    echo json_encode(['success' => false, 'message' => 'DB error: ' . $ins->error]);
}

function doRecalc($conn, $user_id, $book_id) {
    if (!$book_id) return;
    $balance = 0;
    $r = $conn->prepare("SELECT id, income, expense FROM expenses WHERE user_id=? AND book_id=? ORDER BY date, id");
    $r->bind_param("ii", $user_id, $book_id);
    $r->execute();
    $result = $r->get_result();
    $u = $conn->prepare("UPDATE expenses SET balance=? WHERE id=?");
    while ($row = $result->fetch_assoc()) {
        $balance += floatval($row['income']) - floatval($row['expense']);
        $u->bind_param("di", $balance, $row['id']);
        $u->execute();
    }
}
