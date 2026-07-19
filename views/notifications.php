<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = 'Please login to view notifications.';
    header('Location: /ridemate/views/auth/login.php');
    exit;
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../controllers/NotificationController.php';

$notificationController = new NotificationController($conn);
$notifications = $notificationController->fetchForUser($_SESSION['user_id']);

$pageTitle = 'Notifications';
$metaDesc  = 'View your RideMate notifications.';
require_once __DIR__ . '/layouts/header.php';
?>
<div class="dashboard-layout">
  <aside class="sidebar">
    <div class="sidebar-user">
      <div class="sidebar-avatar-placeholder" style="background:rgba(255,255,255,0.1); border-color:rgba(255,255,255,0.2);">
        <?= defaultAvatarIcon() ?>
      </div>
      <div class="sidebar-name"><?= htmlspecialchars($_SESSION['user_name']) ?></div>
      <span class="badge badge-info">🔔 Notifications</span>
    </div>

    <nav class="sidebar-nav">
      <a href="/ridemate/index.php">Home</a>
      <a href="/ridemate/actions/logout.php">Logout</a>
    </nav>
  </aside>

  <main class="main-content">
    <div class="page-header" style="display:flex;align-items:center;justify-content:space-between;">
      <div>
        <h1 class="page-title">Notifications</h1>
        <p class="page-subtitle">Manage your recent RideMate alerts.</p>
      </div>
      <form action="/ridemate/actions/notification_mark_all.php" method="POST" style="margin:0; margin-right:1rem;">
        <button type="submit" class="btn btn-danger fixed-clear-btn" data-confirm="Clear all notifications for this account?">Clear All</button>
      </form>
    </div>

    <?php if (!empty($notifications)): ?>
      <div class="notification-list">
        <?php foreach ($notifications as $notification): ?>
          <div class="notification-card <?= $notification['is_read'] ? 'read' : 'unread' ?>">
            <div>
              <div style="font-weight:600; color:var(--gray-900);"><?= htmlspecialchars($notification['message']) ?></div>
              <div style="font-size:0.85rem; color:var(--gray-600); margin-top:0.25rem;"><?= date('M j, Y · g:i A', strtotime($notification['created_at'])) ?></div>
            </div>
            <?php if (!$notification['is_read']): ?>
              <form action="/ridemate/actions/notification_mark_read.php" method="POST" style="margin:0;">
                <input type="hidden" name="notification_id" value="<?= $notification['id'] ?>">
                <button type="submit" class="btn btn-sm btn-primary">Mark as Read</button>
              </form>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <div class="empty-state card" style="text-align:center; padding:2rem;">
        <div class="empty-state-icon">🔔</div>
        <h3>No notifications yet</h3>
        <p>You will see notifications here when passengers book rides or drivers update bookings.</p>
      </div>
    <?php endif; ?>
  </main>
</div>

<?php require_once __DIR__ . '/layouts/footer.php'; ?>
