<?php
// reorder.php — AJAX endpoint: saves book sort order for the logged-in user
session_name("ExpenseTracker"); session_start();
include "db.php";

header('Content-Type: application/json');

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo json_encode(['ok' => false, 'msg' => 'Not logged in']); exit;
}

$user_id = $_SESSION['user_id'];
$input   = json_decode(file_get_contents('php://input'), true);
$order   = $input['order'] ?? [];   // array of book IDs in new order

if (empty($order) || !is_array($order)) {
    echo json_encode(['ok' => false, 'msg' => 'No order received']); exit;
}

// Ensure sort_order column exists (safe to run every time — fails silently if already exists)
$conn->query("ALTER TABLE books ADD COLUMN sort_order INT DEFAULT 0");

$stmt = $conn->prepare("UPDATE books SET sort_order = ? WHERE book_id = ? AND user_id = ?");
if (!$stmt) {
    // fallback: try with 'id' column name
    $stmt = $conn->prepare("UPDATE books SET sort_order = ? WHERE id = ? AND user_id = ?");
}

foreach ($order as $position => $book_id) {
    $book_id  = (int)$book_id;
    $position = (int)$position;
    $stmt->bind_param("iii", $position, $book_id, $user_id);
    $stmt->execute();
}

echo json_encode(['ok' => true]);
?>
