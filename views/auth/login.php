<?php
session_start();
if (isset($_SESSION['user_id'])) {
    switch ($_SESSION['user_role']) {
        case 'admin':
            header('Location: /ridemate/admin/dashboard.php');
            break;
        default:
            header('Location: /ridemate/index.php');
            break;
    }
    exit;
}
$pageTitle = 'Login';
$metaDesc  = 'Login to RideMate — Your university ride sharing platform.';
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
  <div class="auth-card animate-fade">

    <!-- Logo -->
    <div class="auth-logo">
      <div class="brand">🚗 RideMate</div>
      <div class="tagline">University Ride Sharing Platform</div>
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

    <div class="auth-title">Welcome back 👋</div>
    <div class="auth-subtitle">Sign in to continue your journey</div>

    <form action="/ridemate/actions/login.php" method="POST" id="login-form">
      <div class="form-group" style="margin-bottom:1.1rem;">
        <label>Phone Number</label>
        <input
          type="tel"
          name="phone"
          id="login-phone"
          class="form-control"
          placeholder="Enter your phone number"
          required
          autocomplete="tel"
        />
      </div>

      <div class="form-group" style="margin-bottom:1.5rem;position:relative;">
        <label>Password</label>
        <input
          type="password"
          name="password"
          id="login-password"
          class="form-control"
          placeholder="Enter your password"
          required
          autocomplete="current-password"
        />
        <button
          type="button"
          id="toggle-password"
          onclick="togglePassword('login-password', this)"
          style="position:absolute;right:12px;bottom:12px;background:none;border:none;color:#666;cursor:pointer;font-size:1rem;">
          👁
        </button>
      </div>

      <button type="submit" class="btn btn-primary w-100 btn-lg" id="btn-login-submit">
        Sign In to RideMate
      </button>
    </form>

    <div style="display:flex;justify-content:center;align-items:center;margin-top:0.75rem;font-size:0.95rem;">
      <a href="/ridemate/views/auth/forgot_password.php" style="color:rgba(255,255,255,0.85);">Forgot Password?</a>
    </div>

    <div class="auth-footer">
      Don't have an account? <a href="/ridemate/views/auth/register.php">Create one free →</a>
    </div>

  </div>
</main>

<script src="/ridemate/assets/js/main.js"></script>
<script>
function togglePassword(inputId, btn) {
  const input = document.getElementById(inputId);
  if (input.type === 'password') {
    input.type = 'text';
    btn.textContent = '🙈';
  } else {
    input.type = 'password';
    btn.textContent = '👁';
  }
}

document.getElementById('login-form').addEventListener('submit', function() {
  const btn = document.getElementById('btn-login-submit');
  btn.disabled = true;
  btn.innerHTML = '<span class="spinner" style="width: 18px; height: 18px; margin-right: 8px; vertical-align: middle;"></span> Signing In...';
});
</script>
</body>
</html>
