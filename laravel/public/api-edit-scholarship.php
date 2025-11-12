<?php
session_start();
header('Content-Type: application/json');


if (empty($_SESSION['is_admin'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Admin access required']);
    exit();
}

require_once 'config.php';
$conn = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    $scholarshipId = (int) ($_GET['id'] ?? 0);
    
    if ($scholarshipId <= 0) {
        echo json_encode(['error' => 'Invalid scholarship ID']);
        exit();
    }
    
    $sql = "SELECT * FROM scholarships WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $scholarshipId);
    $stmt->execute();
    $result = $stmt->get_result();
    $scholarship = $result->fetch_assoc();
    $stmt->close();
    
    if ($scholarship) {
        echo json_encode($scholarship);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Scholarship not found']);
    }
    
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action = $_POST['action'] ?? '';

    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['success' => false, 'error' => 'Invalid scholarship ID']);
            exit();
        }

        $stmt = $conn->prepare("DELETE FROM scholarships WHERE id = ?");
        $stmt->bind_param('i', $id);
        $success = $stmt->execute();
        $stmt->close();

        echo json_encode(['success' => $success]);
        exit();
    }

   
    $scholarshipId = (int) ($_POST['id'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $sponsor = trim($_POST['sponsor'] ?? '');
    $category = $_POST['category'] ?? '';
    $start_date = $_POST['start_date'] ?? null;
    $end_date = $_POST['end_date'] ?? null;
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    
    $errors = [];
    if ($scholarshipId <= 0) { $errors[] = 'Invalid scholarship ID.'; }
    if ($title === '') { $errors[] = 'Scholarship title is required.'; }
    if ($description === '') { $errors[] = 'Description is required.'; }
    if (!in_array($category, ['Company','School','Organization','Government','Foundation','Non-Profit','Individual','Other'], true)) { $errors[] = 'Category is required.'; }
    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) { $errors[] = 'Invalid email format.'; }
    if ($start_date && $end_date && strtotime($end_date) < strtotime($start_date)) { $errors[] = 'End date cannot be before start date.'; }
    
   
    $imagePath = null;
    if (!empty($_FILES['image']['name'])) {
        $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png'];
        $mime = mime_content_type($_FILES['image']['tmp_name']);
        if (!isset($allowed[$mime])) {
            $errors[] = 'Only JPG and PNG images are allowed.';
        } else {
            $ext = $allowed[$mime];
            $uploadsDir = __DIR__ . '/uploads';
            if (!is_dir($uploadsDir)) {
                mkdir($uploadsDir, 0777, true);
            }
            $safeName = preg_replace('/[^A-Za-z0-9-_]/', '_', pathinfo($_FILES['image']['name'], PATHINFO_FILENAME));
            $fileName = $safeName . '_' . time() . '.' . $ext;
            $dest = $uploadsDir . '/' . $fileName;
            if (!move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
                $errors[] = 'Failed to upload image.';
            } else {
                $imagePath = 'uploads/' . $fileName;
            }
        }
    }

    if (!empty($errors)) {
        echo json_encode(['success' => false, 'errors' => $errors]);
        exit();
    }
    
    
    if ($imagePath) {
        $sql = "UPDATE scholarships 
                SET title = ?, description = ?, sponsor = ?, category = ?, start_date = ?, end_date = ?, phone = ?, email = ?, image_path = ?
                WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('sssssssssi', $title, $description, $sponsor, $category, $start_date, $end_date, $phone, $email, $imagePath, $scholarshipId);
    } else {
        $sql = "UPDATE scholarships 
                SET title = ?, description = ?, sponsor = ?, category = ?, start_date = ?, end_date = ?, phone = ?, email = ?
                WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ssssssssi', $title, $description, $sponsor, $category, $start_date, $end_date, $phone, $email, $scholarshipId);
    }
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Scholarship updated successfully']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to update scholarship: ' . $stmt->error]);
    }
    
    $stmt->close();
}

$conn->close();
?>