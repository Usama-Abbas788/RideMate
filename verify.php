<?php
session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/models/User.php';

$token       = trim($_GET['token'] ?? '');
$success     = false;
$message     = '';
$messageType = 'error';

if (empty($token)) {
    $message = 'Invalid verification link. Please check the link in your email and try again.';
} else {
    $userModel = new User($conn);
    if ($userModel->verifyEmail($token)) {
        $success     = true;
        $message     = 'Your email has been verified! You can now sign in to RideMate.';
        $messageType = 'success';
        // Clean up session leftover from registration
        unset($_SESSION['verification_email'], $_SESSION['verification_resend_at']);
    } else {
        $message = 'Verification failed. The link may have expired or already been used. Please request a new verification email.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="Email verification status for your RideMate account." />
  <title>Email Verification | RideMate</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="/ridemate/assets/css/style.css" />
  <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🚗</text></svg>" />
  <style>
    .verify-result-card {
      text-align: center;
    }
    .verify-result-icon {
      font-size: 4rem;
      margin-bottom: 1rem;
      display: block;
      animation: pop 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
    }
    @keyframes pop {
      from { opacity: 0; transform: scale(0.5); }
      to   { opacity: 1; transform: scale(1); }
    }
    .verify-result-title {
      font-family: var(--font-main);
      font-size: 1.4rem;
      font-weight: 700;
      margin-bottom: 0.6rem;
      color: #fff;
    }
    .verify-result-body {
      font-size: 0.925rem;
      color: rgba(255, 255, 255, 0.72);
      line-height: 1.6;
      margin-bottom: 1.6rem;
      max-width: 340px;
      margin-left: auto;
      margin-right: auto;
    }
    .verify-result-actions {
      display: flex;
      flex-direction: column;
      gap: 0.75rem;
      align-items: center;
    }
    .verify-result-actions .btn {
      width: 100%;
      max-width: 280px;
    }
    .status-badge {
      display: inline-flex;
      align-items: center;
      gap: 0.4rem;
      padding: 0.35rem 0.85rem;
      border-radius: 999px;
      font-size: 0.8rem;
      font-weight: 600;
      letter-spacing: 0.4px;
      margin-bottom: 1.2rem;
    }
    .status-badge--success {
      background: rgba(16, 185, 129, 0.15);
      border: 1px solid rgba(16, 185, 129, 0.4);
      color: #6ee7b7;
    }
    .status-badge--error {
      background: rgba(239, 68, 68, 0.12);
      border: 1px solid rgba(239, 68, 68, 0.35);
      color: #fca5a5;
    }
    .divider {
      border: none;
      border-top: 1px solid rgba(255,255,255,0.08);
      margin: 1.5rem 0;
    }
  </style>
</head>
<body>
<main class="auth-page">
  <div class="auth-card animate-fade verify-result-card" style="max-width:480px;">

    <div class="auth-logo">
      <div class="brand">🚗 RideMate</div>
    </div>

    <hr class="divider" />

    <!-- Status icon -->
    <span class="verify-result-icon" aria-hidden="true">
      <?= $success ? '✅' : '⚠️' ?>
    </span>

    <!-- Badge -->
    <span class="status-badge status-badge--<?= $messageType ?>">
      <?= $success ? '✓ Verified' : '✕ Verification Failed' ?>
    </span>

    <!-- Title -->
    <p class="verify-result-title">
      <?= $success ? 'Email Verified!' : 'Verification Failed' ?>
    </p>

    <!-- Body -->
    <p class="verify-result-body">
      <?= htmlspecialchars($message) ?>
    </p>

    <!-- Actions -->
    <div class="verify-result-actions">
      <?php if ($success): ?>
        <a href="/ridemate/views/auth/login.php" class="btn btn-primary" id="btn-go-login">
          Sign In to RideMate →
        </a>
      <?php else: ?>
        <a href="/ridemate/views/auth/login.php" class="btn btn-primary" id="btn-go-login">
          Back to Login
        </a>
        <a href="/ridemate/views/auth/register.php" class="btn btn-secondary" id="btn-go-register">
          Create New Account
        </a>
      <?php endif; ?>
    </div>

  </div>
</main>
</body>
</html>
