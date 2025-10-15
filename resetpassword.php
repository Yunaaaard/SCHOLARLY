<?php
require_once 'config.php';
$conn = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$token = $_GET['token'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'];
    $newPassword = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if ($newPassword !== $confirm) {
        echo "<script>alert('Passwords do not match.'); window.history.back();</script>";
        exit();
    }

    // Check token validity
    $stmt = $conn->prepare("SELECT user_id FROM password_resets WHERE token = ? AND expires_at > NOW()");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo "<script>alert('Invalid or expired token.'); window.location.href = 'forgot.html';</script>";
        exit();
    }

    $user = $result->fetch_assoc();
    $userId = $user['user_id'];

    // Update password
    $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
    $stmt2 = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt2->bind_param("si", $hashed, $userId);
    $stmt2->execute();

    // Delete token
    $conn->query("DELETE FROM password_resets WHERE token = '$token'");

    echo "<script>alert('Password reset successful! Please login again.'); window.location.href = 'login.html';</script>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Reset Password</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="d-flex justify-content-center align-items-center vh-100 bg-light">
  <form method="POST" class="bg-white p-4 rounded shadow" style="width: 400px;">
    <h4 class="mb-3 text-center">Reset Password</h4>
    <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
    <div class="mb-3">
      <input type="password" name="password" class="form-control" placeholder="New Password" required>
    </div>
    <div class="mb-3">
      <input type="password" name="confirm_password" class="form-control" placeholder="Confirm Password" required>
    </div>
    <button type="submit" class="btn btn-primary w-100">Reset Password</button>
  </form>
</body>
</html>
