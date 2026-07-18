<?php
session_start();

if (isset($_SESSION['user_id']) && ($_SESSION['user_role'] ?? '') === 'admin') {
    header('Location: /ridemate/admin/dashboard.php');
    exit;
}

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/../config/database.php';
    require_once __DIR__ . '/../config/helpers.php';
    require_once __DIR__ . '/../models/User.php';

    $email    = sanitizeEmail(trim($_POST['email'] ?? ''));
    $password = trim($_POST['password'] ?? '');

    if ($email === '' || $password === '') {
        $error = 'Please enter both email and password.';
    } elseif (!isValidEmail($email)) {
        $error = 'Please enter a valid email address.';
    } else {
        $userModel = new User($conn);
        $user = $userModel->findByEmailAndPassword($email, $password);

        if (!$user || $user['role'] !== 'admin') {
            $error = 'Invalid credentials or access denied.';
        } else {
            unset(
                $_SESSION['otp_phone'],
                $_SESSION['otp_purpose'],
                $_SESSION['otp_sent_at'],
                $_SESSION['otp_resend_at'],
                $_SESSION['otp_dev_code']
            );
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];
            header('Location: /ridemate/admin/dashboard.php');
            exit;
        }
    }
}

if (!empty($_SESSION['error'])) {
    $error = $_SESSION['error'];
    unset($_SESSION['error']);
}
if (!empty($_SESSION['success'])) {
    $success = $_SESSION['success'];
    unset($_SESSION['success']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="RideMate Admin Login — Secure administrator access." />
  <title>Admin Login | RideMate</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="/ridemate/assets/css/style.css" />
  <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>⚙️</text></svg>" />
</head>
<body>
<main class="auth-page">
  <div class="auth-card animate-fade">
    <div class="auth-logo">
      <div class="brand">Ride<span>Mate</span></div>
      <div class="tagline">Administration Panel</div>
    </div>

    <div class="admin-access-badge">Restricted access</div>

    <div class="auth-title">Admin sign in</div>
    <div class="auth-subtitle">Enter your administrator email and password to continue.</div>

    <?php if ($error): ?>
      <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
      <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form action="/ridemate/admin/login.php" method="POST" novalidate>
      <div class="form-group">
        <label for="admin-email">Email Address</label>
        <input
          type="email"
          id="admin-email"
          name="email"
          class="form-control"
          placeholder="admin@example.com"
          value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
          required
          autocomplete="email"
        />
      </div>

      <div class="form-group form-group-password">
        <label for="admin-password">Password</label>
        <div class="password-field">
          <input
            type="password"
            id="admin-password"
            name="password"
            class="form-control"
            placeholder="••••••••"
            required
            autocomplete="current-password"
          />
          <button type="button" class="password-toggle" data-toggle-password="admin-password" aria-label="Show password">Show</button>
        </div>
      </div>

      <button type="submit" class="btn btn-primary w-100 btn-lg">Sign in to dashboard</button>
    </form>

    <div class="auth-footer">
      Not an admin? <a href="/ridemate/views/auth/login.php">Regular login</a>
    </div>
  </div>
</main>
<script src="/ridemate/assets/js/main.js"></script>
</body>
</html>
