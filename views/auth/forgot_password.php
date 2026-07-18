<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: /ridemate/index.php');
    exit;
}
$pageTitle = 'Forgot Password';
$metaDesc  = 'Reset your RideMate password with email.';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="<?= $metaDesc ?>" />
  <title><?= $pageTitle ?> | RideMate</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="/ridemate/assets/css/style.css" />
  <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🚗</text></svg>" />
</head>
<body>
<main class="auth-page">
  <div class="auth-card animate-fade" style="max-width:440px;">
    <div class="auth-logo">
      <div class="brand">🚗 RideMate</div>
      <div class="tagline">Reset your password</div>
    </div>

    <!-- Alerts -->
    <?php if (!empty($_SESSION['error'])): ?>
      <div class="alert alert-error">⚠️ <?= htmlspecialchars($_SESSION['error']) ?></div>
      <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
    <?php if (!empty($_SESSION['warning'])): ?>
      <div class="alert alert-warning">⚠️ <?= htmlspecialchars($_SESSION['warning']) ?></div>
      <?php unset($_SESSION['warning']); ?>
    <?php endif; ?>
    <?php if (!empty($_SESSION['success'])): ?>
      <div class="alert alert-success">✅ <?= htmlspecialchars($_SESSION['success']) ?></div>
      <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <p style="color:rgba(255,255,255,0.7); text-align:center; margin-bottom:1.5rem; font-size:0.95rem;">
      Enter your registered email address and we'll send you a password reset link.
    </p>

    <form action="/ridemate/actions/forgot_password.php" method="POST" id="forgot-password-form">
      <div class="form-group" style="margin-bottom:1.5rem;">
        <label>Email Address</label>
        <input 
          type="email" 
          name="email" 
          id="forgot-email"
          class="form-control" 
          placeholder="Enter your email address" 
          required 
          autocomplete="email" 
        />
      </div>
      <button type="submit" class="btn btn-primary w-100 btn-lg" id="forgot-submit-btn">
        Send Reset Link
      </button>
    </form>

    <div class="auth-footer" style="margin-top:1.5rem;">
      Remembered your password? <a href="/ridemate/views/auth/login.php">Go back to login</a>
    </div>
  </div>
</main>

<script src="/ridemate/assets/js/main.js"></script>
<script>
document.getElementById('forgot-password-form').addEventListener('submit', function() {
  const btn = document.getElementById('forgot-submit-btn');
  btn.disabled = true;
  btn.innerHTML = '<span class="spinner" style="width: 18px; height: 18px; margin-right: 8px; vertical-align: middle;"></span> Sending Link...';
});
</script>
</body>
</html>
