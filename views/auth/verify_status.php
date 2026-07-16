<?php
session_start();
if (isset($_SESSION['otp_phone']) && isset($_SESSION['otp_purpose'])) {
    header('Location: /ridemate/views/auth/verify_otp.php');
    exit;
}

header('Location: /ridemate/views/auth/login.php');
exit;

$pageTitle = 'Verify Your Email';
$metaDesc  = 'Please verify your email address to active your RideMate account.';
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
  <style>
    .timer-container {
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: var(--radius-md);
        padding: 1.5rem;
        margin: 1.5rem 0;
        text-align: center;
    }
    .timer-display {
        font-family: var(--font-main);
        font-size: 2.2rem;
        font-weight: 700;
        color: var(--green-mid);
        letter-spacing: 1px;
        margin-bottom: 0.5rem;
    }
    .timer-label {
        font-size: 0.85rem;
        color: rgba(255, 255, 255, 0.5);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
  </style>
</head>
<body>
<main class="auth-page">
  <div class="auth-card animate-fade" style="max-width:460px;">
    <div class="auth-logo">
      <div class="brand">🚗 RideMate</div>
      <div class="tagline">Verify your email address</div>
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

    <div style="text-align:center; margin-bottom:1rem;">
        <span style="font-size:3.5rem;">✉️</span>
    </div>

    <p style="color:rgba(255, 255, 255, 0.85); text-align:center; line-height:1.6; margin-bottom:1rem; word-break:break-word;">
      A verification link has been sent to your email:<br>
      <strong style="color:white; font-size:1.05rem; word-break:break-all; display:block; margin-top:0.75rem;"><?= htmlspecialchars($email) ?></strong>
    </p>
    
    <p style="color:rgba(255, 255, 255, 0.6); text-align:center; font-size:0.9rem;">
      Please check your inbox to verify your account. The link expires in 15 minutes.
    </p>

    <!-- Expiry Counter Box -->
    <div class="timer-container">
        <div class="timer-display" id="timer-count">03:00</div>
        <div class="timer-label" id="timer-status">Resend block active</div>
    </div>

    <!-- Resend Link Button Form -->
    <form action="/ridemate/actions/resend_verification.php" method="POST" id="resend-verify-form">
      <input type="hidden" name="email" value="<?= htmlspecialchars($email) ?>" />
      <button type="submit" class="btn btn-primary w-100 btn-lg" id="resend-verify-btn" disabled>
        Resend Verification Email
      </button>
    </form>


    <div class="auth-footer" style="margin-top:1.5rem;">
      <a href="/ridemate/views/auth/login.php">← Back to login</a>
    </div>
  </div>
</main>

<script src="/ridemate/assets/js/main.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const resendButton = document.getElementById('resend-verify-btn');
    const timerCount = document.getElementById('timer-count');
    const timerStatus = document.getElementById('timer-status');
    let remaining = <?= $remainingCooldown ?>;

    function updateTimer() {
        if (remaining <= 0) {
            resendButton.disabled = false;
            timerCount.textContent = "00:00";
            timerCount.style.color = "rgba(255, 255, 255, 0.4)";
            timerStatus.textContent = "You can now resend the email";
            timerStatus.style.color = "var(--green-mid)";
            return;
        }

        resendButton.disabled = true;
        
        // Format time as MM:SS
        const minutes = Math.floor(remaining / 60);
        const seconds = remaining % 60;
        const formattedMinutes = minutes.toString().padStart(2, '0');
        const formattedSeconds = seconds.toString().padStart(2, '0');
        
        timerCount.textContent = `${formattedMinutes}:${formattedSeconds}`;
        
        remaining--;
        setTimeout(updateTimer, 1000);
    }

    updateTimer();

    document.getElementById('resend-verify-form').addEventListener('submit', function() {
        resendButton.disabled = true;
        resendButton.innerHTML = '<span class="spinner" style="width: 18px; height: 18px; margin-right: 8px; vertical-align: middle;"></span> Resending...';
    });
});
</script>
</body>
</html>
