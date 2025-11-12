<?php
date_default_timezone_set('Asia/Manila');
require_once 'config.php';

$conn = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$email = trim($_POST['email'] ?? '');

if (empty($email)) {
    echo "<script>alert('Please enter your email address.'); window.history.back();</script>";
    exit();
}

// Check if email exists
$sql = "SELECT id FROM users WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<script>alert('Email not found. Please check again.'); window.history.back();</script>";
    exit();
}

$user = $result->fetch_assoc();
$userId = $user['id'];

// Generate token
$token = bin2hex(random_bytes(16));
$expiry = date("Y-m-d H:i:s", strtotime("+15 minutes"));

// Store token in database (create table if not exists)
$conn->query("CREATE TABLE IF NOT EXISTS password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(255) NOT NULL,
    expires_at DATETIME NOT NULL
)");

$stmt2 = $conn->prepare("INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)");
$stmt2->bind_param("iss", $userId, $token, $expiry);
$stmt2->execute();

// Build reset link
$resetLink = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/resetpassword.php?token=" . $token;

// Send email (for now, just display link for local testing)
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/PHPMailer.php';
require 'phpmailer/SMTP.php';
require 'phpmailer/Exception.php';

$mail = new PHPMailer(true);

try {
    // Server settings
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';       // Gmail SMTP server
    $mail->SMTPAuth = true;
    $mail->Username = 'scholarlys2@gmail.com'; 
    $mail->Password = 'avuu xpqo qfez qkob';    
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    // Recipients
    $mail->setFrom('scholarlys2@gmail.com', 'Scholarly Support');
    $mail->addAddress($email);

    // Email content
    $mail->isHTML(true);
    $mail->Subject = 'Password Reset Request';
    $mail->Body = "
        <p>Hello,</p>
        <p>You requested a password reset for your Scholarly account.</p>
        <p>Click the link below to reset your password:</p>
        <p><a href='$resetLink'>$resetLink</a></p>
        <p>This link expires in 15 minutes.</p>
    ";

    $mail->send();
    echo "<script>
        alert('A password reset link has been sent to your email.');
        window.location.href = 'login.html';
    </script>";
} catch (Exception $e) {
    echo "Email could not be sent. Error: {$mail->ErrorInfo}";
}

$stmt->close();
$stmt2->close();
$conn->close();
?>
