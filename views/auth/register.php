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
$pageTitle = 'Register';
$metaDesc  = 'Create your RideMate account and start sharing rides with university students.';
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
  <div class="auth-card animate-fade" style="max-width:500px;">

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

    <div class="auth-title">Join RideMate 🎓</div>
    <div class="auth-subtitle">Create your account and start your journey</div>

    <form action="/ridemate/actions/register.php" method="POST" id="register-form">

      <div class="form-group" style="margin-bottom:1rem;">
        <label>Full Name</label>
        <input
          type="text"
          name="name"
          id="reg-name"
          class="form-control"
          placeholder="Enter your full name"
          required
          autocomplete="name"
        />
      </div>

      <div class="form-group" style="margin-bottom:1rem;">
        <label>Phone Number</label>
        <input
          type="tel"
          name="phone"
          id="reg-phone"
          class="form-control"
          placeholder="Enter your phone number"
          required
          autocomplete="tel"
        />
      </div>

      <div class="form-group" style="margin-bottom:1rem;position:relative;">
        <label>Password</label>
        <input
          type="password"
          name="password"
          id="reg-password"
          class="form-control"
          placeholder="Min. 6 characters"
          required
          minlength="6"
          autocomplete="new-password"
        />
        <button
          type="button"
          onclick="togglePassword('reg-password', this)"
          style="position:absolute;right:12px;bottom:12px;background:none;border:none;color:rgba(255,255,255,0.5);cursor:pointer;font-size:1rem;">
          👁
        </button>
      </div>

      <div class="form-group" style="margin-bottom:1.5rem;">
        <label>I want to join as</label>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.75rem;margin-top:0.5rem;">
          <label id="role-passenger-label" class="role-option" style="
            display:flex;align-items:center;gap:0.6rem;
            background:rgba(34,195,96,0.12);border:2px solid var(--green);
            padding:0.85rem;border-radius:12px;cursor:pointer;
            color:white;font-weight:600;font-size:0.92rem;
            transition:all 0.2s;
          ">
            <input type="radio" name="role" value="passenger" checked style="display:none;" id="role-passenger">
            🎒 Passenger
          </label>
          <label id="role-driver-label" class="role-option" style="
            display:flex;align-items:center;gap:0.6rem;
            background:rgba(255,255,255,0.05);border:2px solid rgba(255,255,255,0.15);
            padding:0.85rem;border-radius:12px;cursor:pointer;
            color:rgba(255,255,255,0.6);font-weight:600;font-size:0.92rem;
            transition:all 0.2s;
          ">
            <input type="radio" name="role" value="driver" style="display:none;" id="role-driver">
            🚗 Driver
          </label>
        </div>
      </div>

      <button type="submit" class="btn btn-primary w-100 btn-lg" id="btn-register-submit">
        Create My Account
      </button>

    </form>

    <div class="auth-footer">
      Already have an account? <a href="/ridemate/views/auth/login.php">Sign in →</a>
    </div>

  </div>
</main>

<script src="/ridemate/assets/js/main.js"></script>
<script>
function togglePassword(inputId, btn) {
  const input = document.getElementById(inputId);
  if (input.type === 'password') { input.type = 'text'; btn.textContent = '🙈'; }
  else { input.type = 'password'; btn.textContent = '👁'; }
}

// Role selector visual toggle
const passengerLabel = document.getElementById('role-passenger-label');
const driverLabel    = document.getElementById('role-driver-label');
const passengerInput = document.getElementById('role-passenger');
const driverInput    = document.getElementById('role-driver');

const activeStyle   = { background: 'rgba(34,195,96,0.12)', border: '2px solid var(--green)', color: 'white' };
const inactiveStyle = { background: 'rgba(255,255,255,0.05)', border: '2px solid rgba(255,255,255,0.15)', color: 'rgba(255,255,255,0.6)' };

function applyStyle(el, styles) {
  Object.assign(el.style, styles);
}

passengerLabel.addEventListener('click', () => {
  passengerInput.checked = true;
  applyStyle(passengerLabel, activeStyle);
  applyStyle(driverLabel, inactiveStyle);
});

driverLabel.addEventListener('click', () => {
  driverInput.checked = true;
  applyStyle(driverLabel, activeStyle);
  applyStyle(passengerLabel, inactiveStyle);
});
</script>
</body>
</html>
