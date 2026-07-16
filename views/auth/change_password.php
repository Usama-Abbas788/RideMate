<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /ridemate/views/auth/login.php');
    exit;
}
$pageTitle = 'Change Password';
$metaDesc  = 'Change your RideMate password securely.';
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
      <div class="tagline">Change your account password.</div>
    </div>

    <?php if (!empty($_SESSION['error'])): ?>
      <div class="alert alert-error">⚠️ <?= htmlspecialchars($_SESSION['error']) ?></div>
      <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
    <?php if (!empty($_SESSION['success'])): ?>
      <div class="alert alert-success">✅ <?= htmlspecialchars($_SESSION['success']) ?></div>
      <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <form action="/ridemate/actions/change_password.php" method="POST">
      <div class="form-group" style="margin-bottom:1rem;">
        <label>Current Password</label>
        <input type="password" name="current_password" class="form-control" placeholder="Current password" required autocomplete="current-password" />
      </div>
      <div class="form-group" style="margin-bottom:1rem;">
        <label>New Password</label>
        <input type="password" name="password" class="form-control" placeholder="New password" required minlength="6" autocomplete="new-password" />
      </div>
      <div class="form-group" style="margin-bottom:1.5rem;">
        <label>Confirm New Password</label>
        <input type="password" name="password_confirm" class="form-control" placeholder="Confirm new password" required minlength="6" />
      </div>
      <button type="submit" class="btn btn-primary w-100">Update Password</button>
    </form>

    <div class="auth-footer" style="margin-top:1rem;">
      Back to <a href="/ridemate/index.php">Dashboard</a>
    </div>
  </div>
</main>
</body>
</html>
