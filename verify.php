<?php
session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/models/User.php';

$token = trim($_GET['token'] ?? '');
$message = '';
$messageType = 'error';

if (empty($token)) {
    $message = 'Invalid verification link.';
} else {
    $userModel = new User($conn);
    if ($userModel->verifyEmail($token)) {
        $message = 'Email verified successfully. You may now login.';
        $messageType = 'success';
    } else {
        $message = 'Verification failed. The link may have expired or already been used.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Email Verification | RideMate</title>
  <link rel="stylesheet" href="/ridemate/assets/css/style.css" />
</head>
<body>
<main class="auth-page">
  <div class="auth-card animate-fade" style="max-width:460px;">
    <div class="auth-logo">
      <div class="brand">🚗 RideMate</div>
      <div class="tagline">Email verification status</div>
    </div>

    <div class="alert alert-<?= $messageType ?>">
      <?= $messageType === 'success' ? '✅' : '⚠️' ?> <?= htmlspecialchars($message) ?>
    </div>

    <div style="margin-top:1.5rem;text-align:center;">
      <a href="/ridemate/views/auth/login.php" class="btn btn-primary">Go to Login</a>
    </div>
  </div>
</main>
</body>
</html>
