<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) && empty($_SESSION['is_admin'])) {
  http_response_code(401);
  echo json_encode([]);
  exit();
}

require_once 'config.php';
$conn = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
if ($conn->connect_error) {
  http_response_code(500);
  echo json_encode([]);
  exit();
}

$userId = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : 0;


if (isset($_GET['id'])) {
  $scholarshipId = (int) $_GET['id'];
  if ($userId > 0) {
    $sql = "SELECT s.*, (b.id IS NOT NULL) AS bookmarked
            FROM scholarships s
            LEFT JOIN bookmarks b
              ON b.scholarship_id = s.id AND b.user_id = $userId
            WHERE s.id = ?";
  } else {
    $sql = "SELECT * FROM scholarships WHERE id = ?";
  }
  $stmt = $conn->prepare($sql);
  $stmt->bind_param('i', $scholarshipId);
  $stmt->execute();
  $result = $stmt->get_result();
  $scholarship = $result->fetch_assoc();
  $stmt->close();
  $conn->close();
  
  if ($scholarship) {
    if (isset($scholarship['bookmarked'])) { $scholarship['bookmarked'] = (int) $scholarship['bookmarked']; }
    echo json_encode($scholarship);
  } else {
    http_response_code(404);
    echo json_encode(['error' => 'Scholarship not found']);
  }
  exit();
}


$q = trim($_GET['q'] ?? '');

$select = "SELECT s.id, s.title, s.sponsor, s.category, s.image_path";
if ($userId > 0) { $select .= ", (b.id IS NOT NULL) AS bookmarked"; }

$from = " FROM scholarships s";
if ($userId > 0) { $from .= " LEFT JOIN bookmarks b ON b.scholarship_id = s.id AND b.user_id = $userId"; }

$where = '';
if ($q !== '') { $where = " WHERE s.title LIKE ? OR s.sponsor LIKE ? OR s.category LIKE ?"; }

$sql = $select . $from . $where;

if ($q !== '') {
  $stmt = $conn->prepare($sql);
  $like = "%$q%";
  $stmt->bind_param('sss', $like, $like, $like);
  $stmt->execute();
  $res = $stmt->get_result();
} else {
  $res = $conn->query($sql);
}

$items = [];
if ($res) {
  while ($row = $res->fetch_assoc()) {
    if (isset($row['bookmarked'])) { $row['bookmarked'] = (int) $row['bookmarked']; }
    $items[] = $row;
  }
}
if (isset($stmt) && $stmt instanceof mysqli_stmt) { $stmt->close(); }
if (isset($res) && $res instanceof mysqli_result) { $res->free(); }
$conn->close();

echo json_encode($items);
?>
