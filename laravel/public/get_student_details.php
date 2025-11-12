<?php
require_once 'config.php';
header('Content-Type: application/json');

if (empty($_GET['id'])) {
    echo json_encode(['error' => 'Missing student ID']);
    exit;
}

$conn = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
if ($conn->connect_error) {
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

$id = intval($_GET['id']);

$stmt = $conn->prepare("SELECT id, username, email, contact FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['error' => 'Student not found']);
    exit;
}

$student = $result->fetch_assoc();

$schStmt = $conn->prepare("
    SELECT s.title 
    FROM scholarships s
    JOIN applications a ON s.id = a.scholarship_id
    WHERE a.user_id = ?
");
$schStmt->bind_param("i", $id);
$schStmt->execute();
$schResult = $schStmt->get_result();

$scholarships = [];
while ($row = $schResult->fetch_assoc()) {
    $scholarships[] = $row['title'];
}

$student['scholarships'] = $scholarships;

echo json_encode($student);

$schStmt->close();
$stmt->close();
$conn->close();
?>