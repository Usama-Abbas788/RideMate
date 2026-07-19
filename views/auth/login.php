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

// Pick up verification-gate session vars
$needsVerification  = !empty($_SESSION['needs_verification']);
$unverifiedEmail    = htmlspecialchars($_SESSION['unverified_email'] ?? '');
if ($needsVerification) {
    unset($_SESSION['needs_verification']);
    // Keep unverified_email in session so the AJAX call can use it
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
  <style>
    /* ═══════════════════════════════════════════════════════════════
       Modal overlay
    ═══════════════════════════════════════════════════════════════ */
    .vm-overlay {
      position: fixed;
      inset: 0;
      background: rgba(0, 0, 0, 0.65);
      backdrop-filter: blur(6px);
      -webkit-backdrop-filter: blur(6px);
      z-index: 1000;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 1rem;
      opacity: 0;
      transition: opacity 0.25s ease;
    }
    .vm-overlay.vm-open {
      opacity: 1;
    }

    /* ═══════════════════════════════════════════════════════════════
       Modal dialog card
    ═══════════════════════════════════════════════════════════════ */
    .vm-dialog {
      position: relative;
      background: linear-gradient(145deg, #1e1e2e 0%, #181824 100%);
      border: 1px solid var(--green-mid);
      border-radius: 20px;
      padding: 2rem 1.75rem 1.75rem;
      width: 100%;
      max-width: 420px;
      box-shadow:
        0 25px 60px rgba(0,0,0,0.55),
        0 0 0 1px rgba(255,255,255,0.04),
        inset 0 1px 0 rgba(255,255,255,0.06);
      transform: translateY(20px) scale(0.97);
      transition: transform 0.3s cubic-bezier(0.34,1.56,0.64,1), opacity 0.25s ease;
      opacity: 0;
    }
    .vm-overlay.vm-open .vm-dialog {
      transform: translateY(0) scale(1);
      opacity: 1;
    }

    /* ── Accent top bar ── */
    .vm-dialog::before {
      content: '';
      position: absolute;
      top: 0; left: 2rem; right: 2rem;
      height: 3px;
      background: var(--green-mid);
      border-radius: 0 0 4px 4px;
    }

    /* ── Close button ── */
    .vm-close {
      position: absolute;
      top: 1rem;
      right: 1rem;
      width: 32px;
      height: 32px;
      display: flex;
      align-items: center;
      justify-content: center;
      border-radius: 8px;
      background: rgba(255,255,255,0.06);
      border: 1px solid rgba(255,255,255,0.1);
      color: rgba(255,255,255,0.55);
      font-size: 1.1rem;
      cursor: pointer;
      transition: background 0.2s, color 0.2s, transform 0.15s;
      line-height: 1;
    }
    .vm-close:hover {
      background: rgba(255,255,255,0.12);
      color: #fff;
      transform: scale(1.08);
    }
    .vm-close:active { transform: scale(0.95); }

    /* ── Icon area ── */
    .vm-icon-wrap {
      width: 60px;
      height: 60px;
      border-radius: 16px;
      background: linear-gradient(135deg, rgba(245,158,11,0.2), rgba(217,119,6,0.1));
      border: 1px solid var(--green-mid);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.7rem;
      margin: 0 auto 1.25rem;
    }

    /* ── Text content ── */
    .vm-title {
      font-family: var(--font-main, 'Outfit', sans-serif);
      font-size: 1.15rem;
      font-weight: 700;
      color: #fff;
      text-align: center;
      margin: 0 0 0.5rem;
    }
    .vm-body {
      font-size: 0.875rem;
      color: rgba(255,255,255,0.68);
      text-align: center;
      line-height: 1.6;
      margin: 0 0 1.35rem;
    }
    .vm-email {
      display: inline-block;
      color: #fbbf24;
      font-weight: 600;
      word-break: break-all;
      margin-top: 0.25rem;
    }

    /* ── Resend button ── */
    #resend-verify-btn {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.5rem;
      width: 100%;
      padding: 0.75rem 1.25rem;
      font-family: var(--font-main, 'Outfit', sans-serif);
      font-size: 0.925rem;
      font-weight: light;
      border-radius: 12px;
      background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
      color: #fff;
      border: none;
      cursor: pointer;
      transition: opacity 0.2s, transform 0.15s, box-shadow 0.2s;
      box-shadow: 0 4px 18px rgba(245,158,11,0.4);
    }
    #resend-verify-btn:hover:not(:disabled) {
      opacity: 0.9;
      transform: translateY(-1px);
      box-shadow: 0 7px 22px rgba(245,158,11,0.5);
    }
    #resend-verify-btn:active:not(:disabled) { transform: translateY(0); }
    #resend-verify-btn:disabled {
      opacity: 0.5;
      cursor: not-allowed;
      transform: none;
    }

    /* ── Feedback area ── */
    #resend-feedback {
      display: none;
      border-radius: 10px;
      padding: 0.8rem 1rem;
      font-size: 0.875rem;
      font-weight: 500;
      margin-top: 0.9rem;
      line-height: 1.5;
      text-align: center;
    }
    #resend-feedback.feedback--success {
      background: rgba(16,185,129,0.12);
      border: 1px solid rgba(16,185,129,0.35);
      color: #6ee7b7;
    }
    #resend-feedback.feedback--error {
      background: rgba(239,68,68,0.10);
      border: 1px solid rgba(239,68,68,0.30);
      color: #fca5a5;
    }

    /* ── Divider ── */
    .vm-divider {
      border: none;
      border-top: 1px solid rgba(255,255,255,0.07);
      margin: 1.25rem 0;
    }

    /* ── Footer link ── */
    .vm-footer {
      text-align: center;
      font-size: 0.82rem;
      color: rgba(255,255,255,0.4);
      margin-top: 0.5rem;
    }
    .vm-footer a {
      color: rgba(255,255,255,0.6);
      text-decoration: underline;
    }
    .vm-footer a:hover { color: #fff; }

    /* ── Spinner inside button ── */
    .btn-spinner {
      width: 16px;
      height: 16px;
      border: 2px solid rgba(255,255,255,0.35);
      border-top-color: #fff;
      border-radius: 50%;
      animation: vm-spin 0.7s linear infinite;
      flex-shrink: 0;
    }
    @keyframes vm-spin { to { transform: rotate(360deg); } }

    /* ── Pulse ring on icon when modal opens ── */
    @keyframes pulse-ring {
      0%   { box-shadow: 0 0 0 0   rgba(245,158,11,0.4); }
      70%  { box-shadow: 0 0 0 12px rgba(245,158,11,0); }
      100% { box-shadow: 0 0 0 0   rgba(245,158,11,0); }
    }
    .vm-overlay.vm-open .vm-icon-wrap {
      animation: pulse-ring 1.2s ease-out 0.3s 1;
    }
  </style>
</head>
<body>

<main class="auth-page">
  <div class="auth-card animate-fade">

    <!-- Logo -->
    <div class="auth-logo">
      <div class="brand">🚗 RideMate</div>
      <div class="tagline">University Ride Sharing Platform</div>
    </div>

    <!-- ── Standard alerts ───────────────────────────────────── -->
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

    <!-- ── Trigger alert on login form (no box in form) ─────── -->
    <?php if ($needsVerification && $unverifiedEmail): ?>
    <div class="alert alert-warning" id="verify-trigger-alert" style="cursor:pointer;" onclick="openVerifyModal()" role="button" tabindex="0" aria-haspopup="dialog">
      ⚠️ Your account is not verified.
      <strong style="text-decoration:underline;margin-left:0.25rem;">Click to resend verification email →</strong>
    </div>
    <?php endif; ?>

    <div class="auth-title">Welcome back 👋</div>
    <div class="auth-subtitle">Sign in to continue your journey</div>

    <form action="/ridemate/actions/login.php" method="POST" id="login-form">
      <div class="form-group" style="margin-bottom:1.1rem;">
        <label>Email Address</label>
        <input
          type="email"
          name="email"
          id="login-email"
          class="form-control"
          placeholder="Enter your email address"
          required
          autocomplete="email"
          value="<?= $unverifiedEmail ?>"
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
          style="position:absolute;right:12px;bottom:12px;background:none;border:none;color:#666;cursor:pointer;font-size:1rem;"
        >
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

<?php if ($needsVerification && $unverifiedEmail): ?>
<!-- ════════════════════════════════════════════════════════════════
     Verification Modal
════════════════════════════════════════════════════════════════ -->
<div class="vm-overlay" id="verify-modal" role="dialog" aria-modal="true" aria-labelledby="vm-title" aria-describedby="vm-desc">
  <div class="vm-dialog">

    <!-- Close button -->
    <button class="vm-close" id="vm-close-btn" onclick="closeVerifyModal()" aria-label="Close dialog">✕</button>

    <!-- Icon -->
    <div class="vm-icon-wrap" aria-hidden="true">📧</div>

    <!-- Text -->
    <h2 class="vm-title" id="vm-title">Account Not Verified</h2>
    <p class="vm-body" id="vm-desc">
      Your account for<br>
      <span class="vm-email"><?= $unverifiedEmail ?></span><br><br>
      has not been verified yet. Please click the button below to resend
      a verification email to your inbox.
    </p>

    <hr class="vm-divider" />

    <!-- Resend button -->
    <button
      type="button"
      id="resend-verify-btn"
      aria-label="Resend verification email"
      data-email="<?= $unverifiedEmail ?>"
    >
      <span id="resend-btn-icon">✉️</span>
      <span id="resend-btn-label">Resend Verification Email</span>
    </button>

    <!-- Inline feedback -->
    <div id="resend-feedback" role="status" aria-live="polite"></div>

    <!-- Footer -->
    <p class="vm-footer">Already verified? <a href="/ridemate/views/auth/login.php">Refresh &amp; try logging in</a></p>

  </div>
</div>
<?php endif; ?>

<script src="/ridemate/assets/js/main.js"></script>
<script>
/* ──────────────────────────────────────────────────────────────────
   Password toggle
────────────────────────────────────────────────────────────────── */
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

/* ──────────────────────────────────────────────────────────────────
   Login form — loading state on submit
────────────────────────────────────────────────────────────────── */
document.getElementById('login-form').addEventListener('submit', function () {
  const btn = document.getElementById('btn-login-submit');
  btn.disabled = true;
  btn.innerHTML = '<span class="spinner" style="width:18px;height:18px;margin-right:8px;vertical-align:middle;"></span> Signing In...';
});

/* ══════════════════════════════════════════════════════════════════
   Verification Modal — open / close
══════════════════════════════════════════════════════════════════ */
var _verifyModalEl = document.getElementById('verify-modal');

function openVerifyModal() {
  if (!_verifyModalEl) return;
  _verifyModalEl.style.display = 'flex';
  // Next frame so the transition fires
  requestAnimationFrame(function () {
    requestAnimationFrame(function () {
      _verifyModalEl.classList.add('vm-open');
    });
  });
  document.body.style.overflow = 'hidden';
  // Focus the close button for accessibility
  var closeBtn = document.getElementById('vm-close-btn');
  if (closeBtn) setTimeout(function () { closeBtn.focus(); }, 300);
}

function closeVerifyModal() {
  if (!_verifyModalEl) return;
  _verifyModalEl.classList.remove('vm-open');
  document.body.style.overflow = '';
  setTimeout(function () {
    _verifyModalEl.style.display = 'none';
  }, 260);
}

// Close on overlay click (outside dialog)
if (_verifyModalEl) {
  _verifyModalEl.addEventListener('click', function (e) {
    if (e.target === _verifyModalEl) closeVerifyModal();
  });
}

// Close on Escape key
document.addEventListener('keydown', function (e) {
  if (e.key === 'Escape') closeVerifyModal();
});

// Allow keyboard activation of trigger alert
var triggerAlert = document.getElementById('verify-trigger-alert');
if (triggerAlert) {
  triggerAlert.addEventListener('keydown', function (e) {
    if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); openVerifyModal(); }
  });
}

// Auto-open on page load if server flagged needs_verification
<?php if ($needsVerification && $unverifiedEmail): ?>
document.addEventListener('DOMContentLoaded', function () { openVerifyModal(); });
<?php endif; ?>

/* ══════════════════════════════════════════════════════════════════
   AJAX Resend Verification
══════════════════════════════════════════════════════════════════ */
(function () {
  var resendBtn = document.getElementById('resend-verify-btn');
  if (!resendBtn) return;

  var feedback  = document.getElementById('resend-feedback');
  var btnIcon   = document.getElementById('resend-btn-icon');
  var btnLabel  = document.getElementById('resend-btn-label');
  var email     = resendBtn.dataset.email;

  function setLoading(on) {
    resendBtn.disabled = on;
    if (on) {
      btnIcon.innerHTML    = '<span class="btn-spinner"></span>';
      btnLabel.textContent = 'Sending…';
    } else {
      btnIcon.textContent  = '✉️';
      btnLabel.textContent = 'Resend Verification Email';
    }
  }

  function showFeedback(ok, msg) {
    feedback.textContent = (ok ? '✅  ' : '⚠️  ') + msg;
    feedback.className   = 'feedback--' + (ok ? 'success' : 'error');
    feedback.style.display = 'block';
    // Re-trigger animation
    feedback.style.animation = 'none';
    void feedback.offsetHeight;
    feedback.style.animation = '';
  }

  resendBtn.addEventListener('click', function () {
    setLoading(true);
    feedback.style.display = 'none';

    var formData = new FormData();
    formData.append('email', email);

    fetch('/ridemate/actions/resend_verification_ajax.php', {
      method: 'POST',
      body: formData,
      credentials: 'same-origin'
    })
    .then(function (res) {
      if (!res.ok) throw new Error('HTTP ' + res.status);
      return res.json();
    })
    .then(function (data) {
      showFeedback(data.success, data.message);
      if (data.success) {
        resendBtn.style.display = 'none'; // hide after success
      } else {
        setLoading(false);
      }
    })
    .catch(function () {
      showFeedback(false, 'Something went wrong. Please try again.');
      setLoading(false);
    });
  });
})();
</script>
</body>
</html>
