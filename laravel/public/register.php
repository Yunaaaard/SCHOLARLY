<?php
// Prevent direct access
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: register.html');
    exit();
}

session_start(); // Start session for auto login

// Include database configuration
require_once 'config.php';

// Connect to MySQL
$conn = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD);
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

// Ensure database exists and select it
$conn->query("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "`");
$conn->select_db(DB_NAME);

// Ensure users table exists
$createTableSql = "CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    contact VARCHAR(30) NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
$conn->query($createTableSql);

// Get and validate form values
$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$contact = trim($_POST['contact'] ?? '');
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';
$termsAccepted = isset($_POST['terms']);

$errors = [];

// Validation
if (empty($username)) {
    $errors[] = "Username is required.";
} elseif (strlen($username) < 3) {
    $errors[] = "Username must be at least 3 characters long.";
}

if (empty($email)) {
    $errors[] = "Email is required.";
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Invalid email format.";
}

if (empty($contact)) {
    $errors[] = "Contact number is required.";
} elseif (!preg_match('/^[0-9+\-\s()]+$/', $contact)) {
    $errors[] = "Invalid contact number format.";
}

if (empty($password)) {
    $errors[] = "Password is required.";
} elseif (strlen($password) < 6) {
    $errors[] = "Password must be at least 6 characters long.";
}

if ($password !== $confirm_password) {
    $errors[] = "Passwords do not match.";
}

if (!$termsAccepted) {
    $errors[] = "You must agree to the Terms and Conditions before registering.";
}

// If there are validation errors, show them
if (!empty($errors)) {
    echo "<script>
        alert('" . implode('\\n', $errors) . "');
        window.history.back();
    </script>";
    exit();
}

// Check if username or email already exists
$checkSql = "SELECT id FROM users WHERE username = ? OR email = ?";
$checkStmt = $conn->prepare($checkSql);
$checkStmt->bind_param('ss', $username, $email);
$checkStmt->execute();
$checkStmt->store_result();

if ($checkStmt->num_rows > 0) {
    echo "<script>
        alert('Username or email already exists. Please choose different credentials.');
        window.history.back();
    </script>";
    $checkStmt->close();
    $conn->close();
    exit();
}
$checkStmt->close();

// Hash the password for security
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Insert new user
$insertSql = "INSERT INTO users (username, email, contact, password) VALUES (?, ?, ?, ?)";
$insertStmt = $conn->prepare($insertSql);
$insertStmt->bind_param('ssss', $username, $email, $contact, $hashed_password);

if ($insertStmt->execute()) {
    // Automatically log in the user
    $_SESSION['user_id'] = $insertStmt->insert_id;
    $_SESSION['username'] = $username;
    $_SESSION['email'] = $email;

    echo "<script>
        alert('Registration successful! Redirecting to your dashboard...');
        window.location.href = 'dashboard.php';
    </script>";
} else {
    echo "<script>
        alert('Registration failed: " . addslashes($insertStmt->error) . "');
        window.history.back();
    </script>";
}

$insertStmt->close();
$conn->close();
?>