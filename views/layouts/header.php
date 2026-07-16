<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$base = '/ridemate';
$isLoggedIn = isset($_SESSION['user_id']);
$userRole   = $_SESSION['user_role'] ?? '';
$userName   = $_SESSION['user_name'] ?? '';
$isAdmin    = $userRole === 'admin';

function defaultAvatarIcon() {
    return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" style="width:50%;height:50%;color:rgba(255,255,255,0.8);"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 4c1.93 0 3.5 1.57 3.5 3.5S13.93 13 12 13s-3.5-1.57-3.5-3.5S10.07 6 12 6zm0 14c-2.03 0-4.43-.82-6.14-2.88a7.04 7.04 0 0 1 12.28 0C16.43 19.18 14.03 20 12 20z"/></svg>';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="<?= $metaDesc ?? 'RideMate — Smart ride sharing for university students. Find rides, save money, and travel together.' ?>" />
  <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' | RideMate' : 'RideMate — University Ride Sharing' ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="<?= $base ?>/assets/css/style.css" />
  <!-- Favicon inline SVG -->
  <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🚗</text></svg>" />
</head>
<body>

<!-- ── NAVBAR ── -->
<header class="navbar" role="banner">
  <div class="inner" style="display: grid; grid-template-columns: 1fr auto 1fr; width: 100%; align-items: center;">
    <div style="display: flex; justify-content: flex-start;">
      <a href="<?= $base ?>/index.php" class="brand">Ride<span>Mate</span></a>
    </div>

    <nav aria-label="Main navigation" style="display: flex; gap: 1rem; justify-content: center;">
      <?php if (!$isAdmin): ?>
        <a href="<?= $base ?>/index.php">Home</a>
        <a href="<?= $base ?>/index.php#about">About</a>
        <a href="<?= $base ?>/index.php#how-it-works">How It Works</a>
        <a href="<?= $base ?>/index.php#features">Features</a>
        <a href="<?= $base ?>/index.php#contact">Contact</a>

        <?php if ($isLoggedIn): ?>
          <?php if ($userRole === 'driver'): ?>
            <a href="<?= $base ?>/driver/dashboard.php">Driver Dashboard</a>
            <a href="<?= $base ?>/views/rides/create.php">Post Ride</a>
          <?php elseif ($userRole === 'passenger'): ?>
            <a href="<?= $base ?>/passenger/dashboard.php">Passenger Dashboard</a>
            <a href="<?= $base ?>/views/rides/search.php">Find Rides</a>
          <?php endif; ?>
        <?php else: ?>
          <a href="<?= $base ?>/views/rides/search.php">Find Rides</a>
          <a href="<?= $base ?>/views/rides/create.php">Post Ride</a>
        <?php endif; ?>
      <?php endif; ?>
    </nav>

    <div class="d-flex align-center gap-2" style="justify-content: flex-end;">
      <?php if ($isLoggedIn): ?>
        <?php
          require_once __DIR__ . '/../../controllers/NotificationController.php';
          $notificationController = new NotificationController($conn);
          $unreadCount = $notificationController->countUnread($_SESSION['user_id']);
        ?>
        <a href="<?= $base ?>/views/notifications.php" class="notification-bell" style="position:relative;color:white;text-decoration:none;font-size:1.25rem;">
          🔔
          <?php if ($unreadCount > 0): ?>
            <span class="notification-badge"><?= $unreadCount ?></span>
          <?php endif; ?>
        </a>
        <div class="nav-user">
          <div style="width:36px;height:36px;border-radius:50%;background:rgba(255,255,255,0.1);display:flex;align-items:center;justify-content:center;border:1px solid rgba(255,255,255,0.2);">
            <?= defaultAvatarIcon() ?>
          </div>
          <span><?= htmlspecialchars($userName) ?></span>
        </div>
        <a href="<?= $base ?>/actions/logout.php" class="btn-nav-logout btn" id="btn-logout">Logout</a>
      <?php else: ?>
        <a href="<?= $base ?>/views/auth/login.php" class="btn btn-outline" id="btn-login">Login</a>
        <a href="<?= $base ?>/views/auth/register.php" class="btn btn-primary" id="btn-register">Join Free</a>
      <?php endif; ?>
    </div>
  </div>
</header>
