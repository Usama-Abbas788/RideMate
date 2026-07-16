<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: /ridemate/index.php');
    exit;
}

$phone = $_SESSION['otp_phone'] ?? null;
$purpose = $_SESSION['otp_purpose'] ?? null;
if (!$phone || !$purpose) {
    $_SESSION['error'] = 'Your OTP session expired. Please try again.';
    header('Location: /ridemate/views/auth/login.php');
    exit;
}

$pageTitle = 'Verify OTP';
$metaDesc  = 'Enter the OTP sent to your phone to continue.';
$cooldownAt = intval($_SESSION['otp_resend_at'] ?? 0);
$remainingCooldown = max(0, $cooldownAt - time());
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
  <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🔑</text></svg>" />
</head>
<body>
<main class="auth-page">
  <div class="auth-card animate-fade" style="max-width:480px;">
    <div class="auth-logo">
      <div class="brand">🚗 RideMate</div>
      <div class="tagline">Verify your OTP</div>
    </div>

    <?php if (!empty($_SESSION['error'])): ?>
      <div class="alert alert-error">⚠️ <?= htmlspecialchars($_SESSION['error']) ?></div>
      <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
    <?php if (!empty($_SESSION['success'])): ?>
      <div class="alert alert-success">✅ <?= htmlspecialchars($_SESSION['success']) ?></div>
      <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <p style="color:rgba(255,255,255,0.8); text-align:center; margin-bottom:1rem; line-height:1.6;">
      Enter the 6-digit OTP sent to <strong><?= htmlspecialchars($phone) ?></strong>.
      This code expires in 5 minutes.
    </p>

    <form action="/ridemate/actions/verify_otp.php" method="POST" id="verify-otp-form">
      <div class="form-group" style="margin-bottom:1rem;">
        <label>Phone Number</label>
        <input
          type="tel"
          name="phone"
          class="form-control"
          value="<?= htmlspecialchars($phone) ?>"
          readonly
        />
      </div>
      <div class="form-group" style="margin-bottom:1.5rem;">
        <label>OTP Code</label>
        <input
          type="text"
          name="otp_code"
          class="form-control"
          placeholder="Enter OTP"
          required
          inputmode="numeric"
          maxlength="6"
        />
      </div>
      <button type="submit" class="btn btn-primary w-100 btn-lg" id="verify-otp-btn">
        Verify OTP
      </button>
    </form>

    <div class="timer-container" style="margin:1.5rem 0; padding:1.25rem; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.12); border-radius:14px; text-align:center;">
      <div style="font-size:0.95rem; color:rgba(255,255,255,0.65); margin-bottom:0.4rem;">Resend available in</div>
      <div style="font-size:1.9rem; font-weight:700;" id="otp-timer">00:00</div>
    </div>

    <form action="/ridemate/actions/resend_otp.php" method="POST" id="resend-otp-form">
      <input type="hidden" name="phone" value="<?= htmlspecialchars($phone) ?>" />
      <button type="submit" class="btn btn-secondary w-100 btn-lg" id="resend-otp-btn"<?= $remainingCooldown > 0 ? ' disabled' : '' ?>>
        Resend OTP
      </button>
    </form>

    <div class="auth-footer" style="margin-top:1.3rem;">
      Need to start over? <a href="/ridemate/views/auth/login.php">Go back to login</a>
    </div>
  </div>
</main>

<script src="/ridemate/assets/js/main.js"></script>
<script>
  let remaining = <?= $remainingCooldown ?>;
  const otpTimer = document.getElementById('otp-timer');
  const resendBtn = document.getElementById('resend-otp-btn');

  function updateTimer() {
    if (remaining <= 0) {
      otpTimer.textContent = '00:00';
      resendBtn.disabled = false;
      return;
    }

    const minutes = String(Math.floor(remaining / 60)).padStart(2, '0');
    const seconds = String(remaining % 60).padStart(2, '0');
    otpTimer.textContent = `${minutes}:${seconds}`;
    remaining--;
    setTimeout(updateTimer, 1000);
  }

  updateTimer();

  document.getElementById('verify-otp-form').addEventListener('submit', function() {
    const btn = document.getElementById('verify-otp-btn');
    btn.disabled = true;
    btn.innerHTML = 'Verifying...';
  });
  document.getElementById('resend-otp-form').addEventListener('submit', function() {
    resendBtn.disabled = true;
    resendBtn.innerHTML = 'Sending...';
  });
</script>
</body>
</html>
