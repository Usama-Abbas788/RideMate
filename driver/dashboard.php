<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'driver') {
    header('Location: /ridemate/views/auth/login.php');
    exit;
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../controllers/RideController.php';
require_once __DIR__ . '/../controllers/BookingController.php';
require_once __DIR__ . '/../models/User.php';

$rideController    = new RideController($conn);
$bookingController = new BookingController($conn);
$userModel         = new User($conn);

$userId    = $_SESSION['user_id'];
$user      = $userModel->findById($userId);
$rides     = $rideController->getDriverRides($userId);

$pageTitle = 'Driver Dashboard';
$metaDesc  = 'Manage your rides and booking requests on RideMate.';

require_once __DIR__ . '/../views/layouts/header.php';
?>

<div class="dashboard-layout">

  <!-- ── Sidebar ── -->
  <aside class="sidebar">
    <div class="sidebar-user">
      <div class="sidebar-avatar-placeholder" style="background:rgba(255,255,255,0.1); border-color:rgba(255,255,255,0.2);">
        <?= defaultAvatarIcon() ?>
      </div>
      <div class="sidebar-name"><?= htmlspecialchars($user['name']) ?></div>
      <span class="badge badge-driver">🚗 Driver</span>
    </div>

    <nav class="sidebar-nav">
      <a href="/ridemate/driver/dashboard.php" class="active">
        <span class="nav-icon">📊</span> Dashboard
      </a>
      <a href="/ridemate/views/rides/create.php">
        <span class="nav-icon">➕</span> Post a Ride
      </a>
      <a href="/ridemate/views/rides/search.php">
        <span class="nav-icon">🔍</span> Browse Rides
      </a>
      <div class="divider"></div>
      <a href="/ridemate/actions/logout.php">
        <span class="nav-icon">🚪</span> Logout
      </a>
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
    <div class="page-header" style="display:flex;align-items:center;justify-content:space-between;">
      <div>
        <h1 class="page-title">Driver Dashboard</h1>
        <p class="page-subtitle">Manage your rides and passenger requests</p>
      </div>
      <a href="/ridemate/views/rides/create.php" class="btn btn-primary" id="btn-post-new-ride">
        + Post New Ride
      </a>
    </div>

    <!-- Stats Row -->
    <?php
      $totalRides    = count($rides);
      $totalBookings = 0;
      $pendingCount  = 0;
      foreach ($rides as $ride) {
          $totalBookings += ($ride['accepted_count'] + $ride['pending_count']);
          $pendingCount  += $ride['pending_count'];
      }
    ?>
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:1.25rem;margin-bottom:2rem;">
      <div class="stat-card">
        <div class="stat-icon red">🚗</div>
        <div>
          <div class="stat-value"><?= $totalRides ?></div>
          <div class="stat-label">Total Rides</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon green">✅</div>
        <div>
          <div class="stat-value"><?= $totalBookings ?></div>
          <div class="stat-label">Total Bookings</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon yellow">⏳</div>
        <div>
          <div class="stat-value"><?= $pendingCount ?></div>
          <div class="stat-label">Pending Requests</div>
        </div>
      </div>
    </div>

    <!-- Rides Section -->
    <div class="page-header">
      <h2 class="page-title" style="font-size:1.35rem;">My Rides</h2>
    </div>

    <?php if (empty($rides)): ?>
      <div class="empty-state card">
        <div class="empty-state-icon">🚗</div>
        <h3>No rides posted yet</h3>
        <p>Share your route and start earning while helping fellow students.</p>
        <a href="/ridemate/views/rides/create.php" class="btn btn-primary" style="margin-top:1.25rem;">
          Post Your First Ride
        </a>
      </div>

    <?php else: ?>
      <?php foreach ($rides as $ride): ?>
        <?php
          $vehicleIcon = $ride['vehicle_type'] === 'car' ? '🚗' : '🏍️';
          $bookings    = $bookingController->getRideBookings($ride['id']);
        ?>
        <div class="card" style="margin-bottom:1.5rem;">

          <!-- Ride Header -->
          <div class="card-header" style="background:var(--surface-2);">
            <div>
              <div style="color:white;font-weight:700;font-size:1.05rem;font-family:var(--font-main);">
                <?= htmlspecialchars($ride['origin']) ?> → <?= htmlspecialchars($ride['destination']) ?>
              </div>
              <div style="color:rgba(255,255,255,0.6);font-size:0.82rem;margin-top:0.2rem;">
                📅 <?= date('D, M j, Y · g:i A', strtotime($ride['date'])) ?>
              </div>
            </div>
            <div style="display:flex;align-items:center;gap:0.75rem;">
              <span class="vehicle-badge <?= $ride['vehicle_type'] ?>"><?= $vehicleIcon ?> <?= ucfirst($ride['vehicle_type']) ?></span>
              <span style="color:white;font-family:var(--font-main);font-weight:700;font-size:1.1rem;">
                PKR <?= number_format($ride['price'], 0) ?>
              </span>
              <!-- Delete Ride -->
              <form action="/ridemate/actions/delete_ride.php" method="POST"
                    style="margin:0;"
                    onsubmit="return confirm('Delete this ride? All bookings will be lost.');">
                <input type="hidden" name="ride_id" value="<?= $ride['id'] ?>" />
                <button type="submit" class="btn btn-danger btn-sm" id="btn-delete-ride-<?= $ride['id'] ?>">🗑</button>
              </form>
            </div>
          </div>

          <!-- Ride Meta -->
          <div style="padding:1rem 1.5rem;display:flex;gap:2rem;border-bottom:1px solid var(--gray-200);background:var(--gray-100);">
            <div class="meta-item">💺 <?= $ride['seats'] ?> seats left</div>
            <div class="meta-item">✅ <?= $ride['accepted_count'] ?> accepted</div>
            <div class="meta-item">⏳ <?= $ride['pending_count'] ?> pending</div>
          </div>

          <!-- Booking Requests -->
          <div class="card-body">
            <h4 style="font-size:0.9rem;font-weight:700;color:var(--gray-700);margin-bottom:1rem;text-transform:uppercase;letter-spacing:0.4px;">
              Booking Requests (<?= count($bookings) ?>)
            </h4>

            <?php if (empty($bookings)): ?>
              <p style="color:var(--gray-400);font-size:0.9rem;text-align:center;padding:1.5rem 0;">
                No booking requests yet.
              </p>
            <?php else: ?>
              <?php foreach ($bookings as $booking): ?>
                <div class="booking-card">
                  <div class="booking-card-header">
                    <div class="booking-passenger-info">
                      <div class="passenger-avatar-placeholder" style="background:rgba(255,255,255,0.1); border-color:rgba(255,255,255,0.2);">
                        <?= defaultAvatarIcon() ?>
                      </div>
                      <div>
                        <div style="color:white;font-weight:700;font-size:0.92rem;"><?= htmlspecialchars($booking['passenger_name']) ?></div>
                        <div style="color:rgba(255,255,255,0.55);font-size:0.78rem;"><?= htmlspecialchars($booking['passenger_phone'] ?? '') ?></div>
                      </div>
                    </div>
                    <div style="display:flex;align-items:center;gap:0.75rem;">
                      <span class="badge badge-<?= $booking['status'] ?>"><?= ucfirst($booking['status']) ?></span>
                      <span style="color:rgba(255,255,255,0.4);font-size:0.78rem;"><?= date('M j', strtotime($booking['created_at'])) ?></span>
                    </div>
                  </div>

                  <?php if ($booking['status'] === 'pending'): ?>
                  <div class="booking-card-body" style="display:flex;gap:0.75rem;justify-content:flex-end;">
                    <form action="/ridemate/actions/update_booking.php" method="POST" style="margin:0;">
                      <input type="hidden" name="booking_id" value="<?= $booking['id'] ?>" />
                      <input type="hidden" name="status" value="accepted" />
                      <button type="submit" class="btn btn-success btn-sm" id="btn-accept-<?= $booking['id'] ?>">
                        ✅ Accept
                      </button>
                    </form>
                    <form action="/ridemate/actions/update_booking.php" method="POST" style="margin:0;">
                      <input type="hidden" name="booking_id" value="<?= $booking['id'] ?>" />
                      <input type="hidden" name="status" value="rejected" />
                      <button type="submit" class="btn btn-danger btn-sm" id="btn-reject-<?= $booking['id'] ?>">
                        ✕ Reject
                      </button>
                    </form>
                  </div>
                  <?php endif; ?>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>

  </main>
</div>

<?php require_once __DIR__ . '/../views/layouts/footer.php'; ?>
