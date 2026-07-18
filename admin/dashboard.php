<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header('Location: /ridemate/admin/login.php');
  exit;
}
if ($_SESSION['user_role'] !== 'admin') {
  header('HTTP/1.1 403 Forbidden');
  echo "<h1 style='color:#16a34a;font-family:sans-serif;text-align:center;margin-top:5rem;'>Access Denied. Admins Only.</h1>";
  exit;
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../controllers/AdminController.php';
require_once __DIR__ . '/../controllers/NotificationController.php';

$adminController = new AdminController($conn);
$notificationController = new NotificationController($conn);
$data = $adminController->getDashboardData();

$totalUsers      = $data['totalUsers'];
$totalDrivers    = $data['totalDrivers'];
$totalPassengers = $data['totalPassengers'];
$totalRides      = $data['totalRides'];
$totalBookings   = $data['totalBookings'];
$pendingBookings = $data['pendingBookings'];

$weeklyReport   = $data['weeklyReport'];
$monthlyReport  = $data['monthlyReport'];

$allUsers    = $data['allUsers'];
$allRides    = $data['allRides'];
$allBookings = $data['allBookings'];
$latestAdminNotifications = $notificationController->fetchForUser($_SESSION['user_id']);

$pageTitle = 'Admin Dashboard';
$metaDesc  = 'RideMate Admin — Manage users, rides, and bookings.';

require_once __DIR__ . '/../views/layouts/header.php';
?>

<div class="dashboard-layout">

  <!-- ── Sidebar ── -->
  <aside class="sidebar">
    <div class="sidebar-user">
      <div class="sidebar-avatar-placeholder" style="background:rgba(255,255,255,0.1); border-color:rgba(255,255,255,0.2);">
        <?= defaultAvatarIcon() ?>
      </div>
      <div class="sidebar-name"><?= htmlspecialchars($_SESSION['user_name']) ?></div>
      <span class="badge badge-admin">⚙️ Admin</span>
    </div>

    <nav class="sidebar-nav">
      <a href="#overview" class="active"><span class="nav-icon">📊</span> Overview</a>
      <a href="#users"> <span class="nav-icon">👥</span> Users</a>
      <a href="#rides"> <span class="nav-icon">🚗</span> Rides</a>
      <a href="#bookings"> <span class="nav-icon">📋</span> Bookings</a>
      <div class="divider"></div>
      <a href="/ridemate/actions/logout.php"><span class="nav-icon">🚪</span> Logout</a>
    </nav>
  </aside>

  <!-- ── Main Content ── -->
  <main class="main-content">

    <!-- Alerts -->
    <?php if (!empty($_SESSION['error'])): ?>
      <div class="alert alert-error">⚠️ <?= htmlspecialchars($_SESSION['error']) ?></div>
      <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
    <?php if (!empty($_SESSION['success'])): ?>
      <div class="alert alert-success">✅ <?= htmlspecialchars($_SESSION['success']) ?></div>
      <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <!-- Page Header -->
    <div class="page-header" id="overview">
      <h1 class="page-title">Admin Dashboard ⚙️</h1>
      <p class="page-subtitle">Complete overview of RideMate platform</p>
    </div>

    <!-- Stats Grid -->
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:1.25rem;margin-bottom:2.5rem;">
      <div class="stat-card">
        <div class="stat-icon red">👥</div>
        <div>
          <div class="stat-value counter-animate"><?= $totalUsers ?></div>
          <div class="stat-label">Total Users</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon navy">🚗</div>
        <div>
          <div class="stat-value counter-animate"><?= $totalRides ?></div>
          <div class="stat-label">Total Rides</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon green">📋</div>
        <div>
          <div class="stat-value counter-animate"><?= $totalBookings ?></div>
          <div class="stat-label">Total Bookings</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon yellow">🚗</div>
        <div>
          <div class="stat-value"><?= $totalDrivers ?></div>
          <div class="stat-label">Drivers</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon red">🎒</div>
        <div>
          <div class="stat-value"><?= $totalPassengers ?></div>
          <div class="stat-label">Passengers</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon yellow">⏳</div>
        <div>
          <div class="stat-value"><?= $pendingBookings ?></div>
          <div class="stat-label">Pending Bookings</div>
        </div>
      </div>
    </div>

    <!-- ── REPORTS SUMMARY ── -->
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:1.25rem;margin-bottom:2.5rem;">
      <div class="stat-card">
        <div class="stat-icon green">🗓️</div>
        <div>
          <div class="stat-value"><?= $weeklyReport['total_bookings'] ?></div>
          <div class="stat-label">Bookings This Week</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon green">💰</div>
        <div>
          <div class="stat-value">PKR <?= number_format($weeklyReport['revenue'], 0) ?></div>
          <div class="stat-label">Revenue This Week</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon green">📅</div>
        <div>
          <div class="stat-value"><?= $monthlyReport['total_bookings'] ?></div>
          <div class="stat-label">Bookings This Month</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon green">📈</div>
        <div>
          <div class="stat-value">PKR <?= number_format($monthlyReport['revenue'], 0) ?></div>
          <div class="stat-label">Revenue This Month</div>
        </div>
      </div>
    </div>

    <!-- ── PDF REPORT DOWNLOAD ── -->
    <div style="display:flex;gap:1rem;margin-bottom:2.5rem;">
      <a href="/ridemate/actions/admin/export_pdf.php?period=weekly" class="btn btn-primary" id="btn-download-weekly-pdf" style="display:flex;align-items:center;gap:0.5rem;">
        📄 Download Weekly Report (PDF)
      </a>
      <a href="/ridemate/actions/admin/export_pdf.php?period=monthly" class="btn btn-warning" id="btn-download-monthly-pdf" style="display:flex;align-items:center;gap:0.5rem;">
        📅 Download Monthly Report (PDF)
      </a>
    </div>

    <!-- <div id="notifications" style="margin-bottom:2.5rem;">
      <div class="page-header">
        <h2 class="page-title" style="font-size:1.35rem;">Recent Admin Notifications</h2>
        <p style="margin:0.5rem 0 0; color:var(--gray-600);">Latest ride and booking events for admin review.</p>
      </div>
      <div class="table-container">
        <table class="data-table">
          <thead>
            <tr>
              <th>#</th>
              <th>Notification</th>
              <th>Date</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!empty($latestAdminNotifications)): ?>
              <?php foreach ($latestAdminNotifications as $idx => $note): ?>
                <tr>
                  <td style="color:var(--gray-500);font-size:0.82rem;">#<?= $idx + 1 ?></td>
                  <td style="font-weight:600; width:60%;"><?= htmlspecialchars($note['message']) ?></td>
                  <td style="color:var(--gray-500);font-size:0.85rem;"><?= date('M j, Y · g:i A', strtotime($note['created_at'])) ?></td>
                  <td><span class="badge badge-<?= $note['is_read'] ? 'success' : 'warning' ?>"><?= $note['is_read'] ? 'Read' : 'Unread' ?></span></td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="4" style="text-align:center; color:var(--gray-600);">No admin notifications yet.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div> -->

    <div id="users" style="margin-bottom:2.5rem;">
      <div class="page-header">
        <h2 class="page-title" style="font-size:1.35rem;">All Users (<?= $totalUsers ?>)</h2>
      </div>
      <div class="table-container">
        <table class="data-table">
          <thead>
            <tr>
              <th>#</th>
              <th>User</th>
              <th>Phone</th>
              <th>Role</th>
              <th>Joined</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($allUsers as $u): ?>
              <tr>
                <td style="color:var(--gray-500);font-size:0.82rem;">#<?= $u['id'] ?></td>
                <td>
                  <div style="display:flex;align-items:center;gap:0.6rem;">
                    <div style="width:36px;height:36px;border-radius:50%;background:var(--green);display:flex;align-items:center;justify-content:center;color:white;font-weight:700;font-size:0.9rem;flex-shrink:0;">
                      <?= strtoupper(substr($u['name'], 0, 1)) ?>
                    </div>
                    <span style="font-weight:600;"><?= htmlspecialchars($u['name']) ?></span>
                  </div>
                </td>
                <td><?= htmlspecialchars($u['phone']) ?></td>
                <td><span class="badge badge-<?= $u['role'] ?>"><?= ucfirst($u['role']) ?></span></td>
                <td style="color:var(--gray-500);font-size:0.85rem;"><?= date('M j, Y', strtotime($u['created_at'])) ?></td>
                <td>
                  <?php if ($u['role'] !== 'admin'): ?>
                    <form action="/ridemate/actions/admin_delete_user.php" method="POST" style="margin:0;" onsubmit="return confirm('Delete this user?');">
                      <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                      <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                    </form>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- ── RIDES TABLE ── -->
    <div id="rides" style="margin-bottom:2.5rem;">
      <div class="page-header">
        <h2 class="page-title" style="font-size:1.35rem;">All Rides (<?= $totalRides ?>)</h2>
      </div>
      <div class="table-container">
        <table class="data-table">
          <thead>
            <tr>
              <th>#</th>
              <th>Driver</th>
              <th>Route</th>
              <th>Date</th>
              <th>Seats</th>
              <th>Price</th>
              <th>Vehicle</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($allRides as $r): ?>
              <tr>
                <td style="color:var(--gray-500);font-size:0.82rem;">#<?= $r['id'] ?></td>
                <td>
                  <div style="font-weight:600;"><?= htmlspecialchars($r['driver_name']) ?></div>
                  <div style="color:var(--gray-500);font-size:0.85rem;"><?= htmlspecialchars($r['driver_phone']) ?></div>
                </td>
                <td>
                  <div style="font-weight:600;"><?= htmlspecialchars($r['origin']) ?></div>
                  <div style="color:var(--gray-500);font-size:0.82rem;">→ <?= htmlspecialchars($r['destination']) ?></div>
                </td>
                <td style="font-size:0.85rem;"><?= date('M j, Y · g:i A', strtotime($r['date'])) ?></td>
                <td><?= $r['seats'] ?></td>
                <td style="font-weight:700;color:var(--green);">PKR <?= number_format($r['price'], 0) ?></td>
                <td>
                  <span class="vehicle-badge <?= $r['vehicle_type'] ?>">
                    <?= $r['vehicle_type'] === 'car' ? '🚗' : '🏍️' ?> <?= ucfirst($r['vehicle_type']) ?>
                  </span>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- ── BOOKINGS TABLE ── -->
    <div id="bookings">
      <div class="page-header">
        <h2 class="page-title" style="font-size:1.35rem;">All Bookings (<?= $totalBookings ?>)</h2>
      </div>
      <div class="table-container">
        <table class="data-table">
          <thead>
            <tr>
              <th>#</th>
              <th>Passenger</th>
              <th>Driver</th>
              <th>Route</th>
              <th>Date</th>
              <th>Status</th>
              <th>Booked</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($allBookings as $b): ?>
              <tr>
                <td style="color:var(--gray-500);font-size:0.82rem;">#<?= $b['id'] ?></td>
                <td>
                  <div style="font-weight:600;"><?= htmlspecialchars($b['passenger_name']) ?></div>
                  <div style="color:var(--gray-500);font-size:0.85rem;"><?= htmlspecialchars($b['passenger_phone']) ?></div>
                </td>
                <td>
                  <div style="font-weight:600;"><?= htmlspecialchars($b['driver_name']) ?></div>
                  <div style="color:var(--gray-500);font-size:0.85rem;"><?= htmlspecialchars($b['driver_phone']) ?></div>
                </td>
                <td>
                  <div><?= htmlspecialchars($b['origin']) ?></div>
                  <div style="color:var(--gray-500);font-size:0.82rem;">→ <?= htmlspecialchars($b['destination']) ?></div>
                </td>
                <?php
                  $displayStatus = $b['status'];
                  if (strtotime($b['date']) < time() && !in_array($b['status'], ['cancelled', 'closed', 'rejected'])) {
                      $displayStatus = 'expired';
                  }
                ?>
                <td style="font-size:0.85rem;"><?= date('M j, Y', strtotime($b['date'])) ?></td>
                <td><span class="badge badge-<?= $displayStatus ?>"><?= ucfirst($displayStatus) ?></span></td>
                <td style="color:var(--gray-500);font-size:0.85rem;"><?= date('M j, Y', strtotime($b['created_at'])) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

  </main>
</div>

<?php require_once __DIR__ . '/../views/layouts/footer.php'; ?>