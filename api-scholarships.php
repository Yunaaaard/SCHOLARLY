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

// Handle individual scholarship detail request
if (isset($_GET['id'])) {
  $scholarshipId = (int) $_GET['id'];
  $sql = "SELECT * FROM scholarships WHERE id = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param('i', $scholarshipId);
  $stmt->execute();
  $result = $stmt->get_result();
  $scholarship = $result->fetch_assoc();
  $stmt->close();
  $conn->close();
  
  if ($scholarship) {
    echo json_encode($scholarship);
  } else {
    http_response_code(404);
    echo json_encode(['error' => 'Scholarship not found']);
  }
  exit();
}

// Handle list request
$q = trim($_GET['q'] ?? '');
$sql = "SELECT id, title, sponsor, category, image_path FROM scholarships";
if ($q !== '') {
  $sql .= " WHERE title LIKE ? OR sponsor LIKE ? OR category LIKE ?";
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
  while ($row = $res->fetch_assoc()) { $items[] = $row; }
}
if (isset($stmt) && $stmt instanceof mysqli_stmt) { $stmt->close(); }
if (isset($res) && $res instanceof mysqli_result) { $res->free(); }
$conn->close();

echo json_encode($items);
?>


