<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: /ridemate/index.php');
    exit;
}

$token = trim($_GET['token'] ?? '');
if (empty($token)) {
    $_SESSION['error'] = 'Invalid password reset link.';
    header('Location: /ridemate/views/auth/forgot_password.php');
    exit;
}

$pageTitle = 'Reset Password';
$metaDesc  = 'Set a new password for your RideMate account.';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="<?= $metaDesc ?>" />
  <title><?= $pageTitle ?> | RideMate</title>
  <link rel="stylesheet" href="/ridemate/assets/css/style.css" />
</head>
<body>
<main class="auth-page">
  <div class="auth-card animate-fade" style="max-width:440px;">
    <div class="auth-logo">
      <div class="brand">🚗 RideMate</div>
      <div class="tagline">Choose a new password.</div>
    </div>

    <?php if (!empty($_SESSION['error'])): ?>
      <div class="alert alert-error">⚠️ <?= htmlspecialchars($_SESSION['error']) ?></div>
      <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
    <?php if (!empty($_SESSION['success'])): ?>
      <div class="alert alert-success">✅ <?= htmlspecialchars($_SESSION['success']) ?></div>
      <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <p style="color:rgba(255,255,255,0.75); text-align:center; margin-bottom:1.2rem;">
      Enter a new password for your RideMate account.
    </p>
    <form action="/ridemate/actions/reset_password.php" method="POST">
      <input type="hidden" name="reset_token" value="<?= htmlspecialchars($token, ENT_QUOTES) ?>" />
      <div class="form-group" style="margin-bottom:1rem;">
        <label>New Password</label>
        <input type="password" name="password" class="form-control" placeholder="Enter new password" required minlength="6" />
      </div>
      <div class="form-group" style="margin-bottom:1.5rem;">
        <label>Confirm Password</label>
        <input type="password" name="password_confirm" class="form-control" placeholder="Confirm password" required minlength="6" />
      </div>
      <button type="submit" class="btn btn-primary w-100">Reset Password</button>
    </form>

    <div class="auth-footer" style="margin-top:1rem;">
      Remembered your password? <a href="/ridemate/views/auth/login.php">Login</a>
    </div>
  </div>
</main>
</body>
</html>
