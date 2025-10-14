<?php
session_start();
header('Content-Type: application/json');


require_once 'config.php';
$conn = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
if ($conn->connect_error) {
  http_response_code(500);
  echo json_encode(['error' => 'Database connection failed']);
  exit();
}

$createTableSql = "CREATE TABLE IF NOT EXISTS applications (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  scholarship_id INT UNSIGNED NOT NULL,
  status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
  applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY unique_application (user_id, scholarship_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
$conn->query($createTableSql);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (isset($_POST['action']) && $_POST['action'] === 'update_status') {
    if (empty($_SESSION['is_admin'])) {
      echo json_encode(['success' => false, 'error' => 'Admin access required']);
      exit();
    }
    
    $applicationId = (int) ($_POST['application_id'] ?? 0);
    $status = $_POST['status'] ?? '';
    
    if (!in_array($status, ['pending', 'approved', 'rejected'])) {
      echo json_encode(['success' => false, 'error' => 'Invalid status']);
      exit();
    }
    
    $updateSql = "UPDATE applications SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($updateSql);
    $stmt->bind_param('si', $status, $applicationId);
    
    if ($stmt->execute()) {
      echo json_encode(['success' => true, 'message' => 'Application status updated successfully']);
    } else {
      echo json_encode(['success' => false, 'error' => 'Failed to update status']);
    }
    
    $stmt->close();
  } else {
    $scholarshipId = (int) ($_POST['scholarship_id'] ?? 0);
    
    if ($scholarshipId <= 0) {
      echo json_encode(['success' => false, 'error' => 'Invalid scholarship ID']);
      exit();
    }
    
    $checkSql = "SELECT id FROM applications WHERE user_id = ? AND scholarship_id = ?";
    $stmt = $conn->prepare($checkSql);
    $stmt->bind_param('ii', $_SESSION['user_id'], $scholarshipId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
      echo json_encode(['success' => false, 'error' => 'You have already applied for this scholarship']);
      exit();
    }
    
    $insertSql = "INSERT INTO applications (user_id, scholarship_id) VALUES (?, ?)";
    $stmt = $conn->prepare($insertSql);
    $stmt->bind_param('ii', $_SESSION['user_id'], $scholarshipId);
    
    if ($stmt->execute()) {
      echo json_encode(['success' => true, 'message' => 'Application submitted successfully!']);
    } else {
      echo json_encode(['success' => false, 'error' => 'Failed to submit application']);
    }
    
    $stmt->close();
  }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
  $scholarshipId = (int) ($_GET['scholarship_id'] ?? 0);
  
  if ($scholarshipId > 0) {
    try {
      // Ensure bookmarks table exists
      $conn->query("CREATE TABLE IF NOT EXISTS bookmarks (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id INT UNSIGNED NOT NULL,
        scholarship_id INT UNSIGNED NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_user_scholarship (user_id, scholarship_id)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
      
      $sql = "SELECT b.*, u.username, u.email, u.contact 
              FROM bookmarks b 
              LEFT JOIN users u ON b.user_id = u.id 
              WHERE b.scholarship_id = ? 
              ORDER BY b.created_at DESC";
      $stmt = $conn->prepare($sql);
      $stmt->bind_param('i', $scholarshipId);
      $stmt->execute();
      $result = $stmt->get_result();
      
      $applications = [];
      while ($row = $result->fetch_assoc()) {
        // Do not fabricate placeholder data; leave missing fields empty so UI can show N/A
        if (!isset($row['username']) || $row['username'] === null) { $row['username'] = ''; }
        if (!isset($row['email']) || $row['email'] === null) { $row['email'] = ''; }
        if (empty($row['contact'])) { $row['contact'] = ''; }
        // Rename fields to match expected format
        $row['applied_at'] = $row['created_at'];
        $row['status'] = 'bookmarked';
        $applications[] = $row;
      }
      
      $stmt->close();
      
      if ($conn->error) {
        echo json_encode(['error' => 'Database error: ' . $conn->error]);
      } else {
        echo json_encode($applications ?: []);
      }
    } catch (Exception $e) {
      echo json_encode(['error' => 'Exception: ' . $e->getMessage()]);
    }
  } else {
    echo json_encode([]);
  }
}

$conn->close();
?>
