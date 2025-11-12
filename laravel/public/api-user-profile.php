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
  echo json_encode(['error' => 'DB connection failed']);
  exit();
}

// Ensure profile_picture column exists
$conn->query("ALTER TABLE users ADD COLUMN IF NOT EXISTS profile_picture VARCHAR(255) DEFAULT NULL");

$userId = (int) $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $action = $_POST['action'] ?? '';
  $username = trim($_POST['username'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $contact = trim($_POST['contact'] ?? '');
  $password = trim($_POST['password'] ?? '');
  $profilePicture = null;

  $errors = [];
  
  // Handle password-only change
  if ($action === 'change_password') {
    if ($password === '') {
      $errors[] = 'Password is required.';
    } elseif (strlen($password) < 6) {
      $errors[] = 'Password must be at least 6 characters long.';
    }
    
    if (empty($errors)) {
      $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
      $stmt = $conn->prepare('UPDATE users SET password = ? WHERE id = ?');
      $stmt->bind_param('si', $hashedPassword, $userId);
      
      if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Password updated successfully']);
      } else {
        echo json_encode(['success' => false, 'error' => 'Failed to update password']);
      }
      $stmt->close();
      $conn->close();
      exit();
    } else {
      http_response_code(422);
      echo json_encode(['success' => false, 'error' => implode("\n", $errors)]);
      $conn->close();
      exit();
    }
  }
  
  // Handle profile update
  if ($username === '') { $errors[] = 'Username is required.'; }
  if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) { $errors[] = 'Valid email is required.'; }
  if ($contact === '') { $errors[] = 'Contact is required.'; }

  // Handle profile picture upload
  if (isset($_FILES['profilePic']) && $_FILES['profilePic']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = 'uploads/';
    if (!is_dir($uploadDir)) {
      mkdir($uploadDir, 0755, true);
    }
    
    $fileExtension = strtolower(pathinfo($_FILES['profilePic']['name'], PATHINFO_EXTENSION));
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
    
    if (!in_array($fileExtension, $allowedExtensions)) {
      $errors[] = 'Invalid file type. Only JPG, PNG, and GIF are allowed.';
    } elseif ($_FILES['profilePic']['size'] > 5 * 1024 * 1024) { // 5MB limit
      $errors[] = 'File too large. Maximum size is 5MB.';
    } else {
      $fileName = uniqid() . '_' . time() . '.' . $fileExtension;
      $filePath = $uploadDir . $fileName;
      
      if (move_uploaded_file($_FILES['profilePic']['tmp_name'], $filePath)) {
        $profilePicture = $filePath;
      } else {
        $errors[] = 'Failed to upload profile picture.';
      }
    }
  }

  if (!empty($errors)) {
    http_response_code(422);
    echo json_encode(['error' => implode("\n", $errors)]);
    $conn->close();
    exit();
  }

  // Update profile with or without new picture and password
  if ($password !== '') {
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    if ($profilePicture) {
      $stmt = $conn->prepare('UPDATE users SET username = ?, email = ?, contact = ?, password = ?, profile_picture = ? WHERE id = ?');
      $stmt->bind_param('sssssi', $username, $email, $contact, $hashedPassword, $profilePicture, $userId);
    } else {
      $stmt = $conn->prepare('UPDATE users SET username = ?, email = ?, contact = ?, password = ? WHERE id = ?');
      $stmt->bind_param('ssssi', $username, $email, $contact, $hashedPassword, $userId);
    }
  } else {
    if ($profilePicture) {
      $stmt = $conn->prepare('UPDATE users SET username = ?, email = ?, contact = ?, profile_picture = ? WHERE id = ?');
      $stmt->bind_param('ssssi', $username, $email, $contact, $profilePicture, $userId);
    } else {
      $stmt = $conn->prepare('UPDATE users SET username = ?, email = ?, contact = ? WHERE id = ?');
      $stmt->bind_param('sssi', $username, $email, $contact, $userId);
    }
  }
  
  if ($stmt->execute()) {
    $_SESSION['username'] = $username;
    $_SESSION['email'] = $email;
    echo json_encode(['success' => true]);
  } else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to update profile']);
  }
  $stmt->close();
  $conn->close();
  exit();
}

$stmt = $conn->prepare('SELECT username, email, contact, profile_picture FROM users WHERE id = ?');
$stmt->bind_param('i', $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();
$conn->close();

echo json_encode([
  'username' => $user['username'] ?? '',
  'email' => $user['email'] ?? '',
  'contact' => $user['contact'] ?? '',
  'profile_picture' => $user['profile_picture'] ?? ''
]);
?>


