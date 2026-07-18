<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: /ridemate/index.php');
    exit;
}
$pageTitle = 'Resend Verification Email';
$metaDesc  = 'Resend your RideMate verification email.';
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
      <div class="tagline">Resend your verification email.</div>
    </div>

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

    <?php $verificationEmail = $_SESSION['verification_email'] ?? null; ?>
    <?php $verificationResendAt = intval($_SESSION['verification_resend_at'] ?? 0); ?>
    <?php if ($verificationEmail): ?>
      <div class="alert alert-info">A verification link was sent to <strong><?= htmlspecialchars($verificationEmail) ?></strong>. You can request another link after the timer expires.</div>
    <?php endif; ?>

    <form action="/ridemate/actions/resend_verification.php" method="POST" id="resend-verification-form">
      <div class="form-group" style="margin-bottom:1rem;">
        <label>Email Address</label>
        <input type="email" name="email" class="form-control" placeholder="Enter your email" required autocomplete="email" value="<?= htmlspecialchars($verificationEmail ?? '') ?>" />
      </div>
      <button type="submit" class="btn btn-primary w-100" id="resend-verification-submit">Resend Verification Email</button>
    </form>

    <div class="auth-footer" style="margin-top:1rem;">
      Already verified? <a href="/ridemate/views/auth/login.php">Login</a>
    </div>

    <?php if ($verificationResendAt > time()): ?>
      <script>
        const resendButton = document.getElementById('resend-verification-submit');
        const cooldownAt = <?= $verificationResendAt ?>;
        const timer = setInterval(() => {
          const remaining = cooldownAt - Math.floor(Date.now() / 1000);
          if (remaining <= 0) {
            resendButton.disabled = false;
            resendButton.textContent = 'Resend Verification Email';
            clearInterval(timer);
            return;
          }
          resendButton.disabled = true;
          resendButton.textContent = 'Please wait ' + new Date(remaining * 1000).toISOString().substr(14, 5) + ' to resend';
        }, 500);
      </script>
    <?php endif; ?>
  </div>
</main>
</body>
</html>
