<?php
session_start();
if (empty($_SESSION['is_admin'])) {
    header('Location: login.html');
    exit;
}

require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = intval($_POST['id']);

    $conn = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
    if ($conn->connect_error) {
        die('Database connection failed: ' . $conn->connect_error);
    }

    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param('i', $id);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Student deleted successfully.";
    } else {
        $_SESSION['message'] = "Error deleting student.";
    }

    $stmt->close();
    $conn->close();

    header("Location: studentmanagementboard.php");
    exit;
} else {
    header("Location: studentmanagementboard.php");
    exit;
}
?>