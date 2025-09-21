<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
  http_response_code(401);
  echo json_encode(['error' => 'Unauthorized']);
  exit();
}

require_once 'config.php';
$conn = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
if ($conn->connect_error) {
  http_response_code(500);
  echo json_encode(['error' => 'Database connection failed']);
  exit();
}

// Get user's applications with scholarship details
$sql = "SELECT a.*, s.title as scholarship_title, s.sponsor as scholarship_sponsor
        FROM applications a
        JOIN scholarships s ON a.scholarship_id = s.id
        WHERE a.user_id = ?
        ORDER BY a.applied_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

$notifications = [];
while ($row = $result->fetch_assoc()) {
  $notifications[] = $row;
}

$stmt->close();
$conn->close();

echo json_encode($notifications);
?>
