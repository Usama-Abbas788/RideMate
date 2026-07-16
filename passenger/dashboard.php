<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'passenger') {
    header('Location: /ridemate/views/auth/login.php');
    exit;
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../controllers/BookingController.php';
require_once __DIR__ . '/../models/User.php';

$bookingController = new BookingController($conn);
$userModel         = new User($conn);

$userId   = $_SESSION['user_id'];
$user     = $userModel->findById($userId);
$bookings = $bookingController->getPassengerBookings($userId);

$pageTitle = 'My Bookings';
$metaDesc  = 'View and manage your ride bookings on RideMate.';

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
      <span class="badge badge-passenger">🎒 Passenger</span>
    </div>

    <nav class="sidebar-nav">
      <a href="/ridemate/passenger/dashboard.php" class="active">
        <span class="nav-icon">📋</span> My Bookings
      </a>
      <a href="/ridemate/views/rides/search.php">
        <span class="nav-icon">🔍</span> Find Rides
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
        <h1 class="page-title">My Bookings 🎒</h1>
        <p class="page-subtitle">Track all your ride requests and statuses</p>
      </div>
      <a href="/ridemate/views/rides/search.php" class="btn btn-primary" id="btn-find-ride">
        🔍 Find a Ride
      </a>
    </div>

    <!-- Stats -->
    <?php
      $pendingCount   = 0;
      $acceptedCount  = 0;
      $rejectedCount  = 0;
      $cancelledCount = 0;
      foreach ($bookings as $b) {
          switch($b['status']) {
              case 'pending':   $pendingCount++; break;
              case 'accepted':  $acceptedCount++; break;
              case 'rejected':  $rejectedCount++; break;
              case 'cancelled': $cancelledCount++; break;
          }
      }
    ?>
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:1rem;margin-bottom:2rem;">
      <div class="stat-card">
        <div class="stat-icon yellow">⏳</div>
        <div>
          <div class="stat-value"><?= $pendingCount ?></div>
          <div class="stat-label">Pending</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon green">✅</div>
        <div>
          <div class="stat-value"><?= $acceptedCount ?></div>
          <div class="stat-label">Accepted</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon red">❌</div>
        <div>
          <div class="stat-value"><?= $rejectedCount ?></div>
          <div class="stat-label">Rejected</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon navy">🚫</div>
        <div>
          <div class="stat-value"><?= $cancelledCount ?></div>
          <div class="stat-label">Cancelled</div>
        </div>
      </div>
    </div>

    <!-- Bookings List -->
    <?php if (empty($bookings)): ?>
      <div class="empty-state card">
        <div class="empty-state-icon">🎒</div>
        <h3>No bookings yet</h3>
        <p>Search for available rides and book your first seat today!</p>
        <a href="/ridemate/views/rides/search.php" class="btn btn-primary" style="margin-top:1.25rem;">
          Browse Rides
        </a>
      </div>

    <?php else: ?>
      <?php foreach ($bookings as $booking): ?>
        <?php
          $vehicleIcon  = $booking['vehicle_type'] === 'car' ? '🚗' : '🏍️';
          $driverInitial = strtoupper(substr($booking['driver_name'], 0, 1));
        ?>
        <div class="booking-card" style="margin-bottom:1.25rem;">

          <!-- Booking Header -->
          <div class="booking-card-header">
            <div>
              <div style="color:white;font-weight:700;font-size:1rem;font-family:var(--font-main);">
                <?= htmlspecialchars($booking['origin']) ?> → <?= htmlspecialchars($booking['destination']) ?>
              </div>
              <div style="color:rgba(255,255,255,0.55);font-size:0.8rem;margin-top:0.2rem;">
                <?= $vehicleIcon ?> <?= ucfirst($booking['vehicle_type']) ?> &nbsp;·&nbsp;
                📅 <?= date('D, M j, Y · g:i A', strtotime($booking['date'])) ?>
              </div>
            </div>
            <span class="badge badge-<?= $booking['status'] ?>"><?= ucfirst($booking['status']) ?></span>
          </div>

          <!-- Booking Body -->
          <div class="booking-card-body">
            <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1rem;">

              <!-- Driver Info -->
              <div style="display:flex;align-items:center;gap:0.75rem;">
                <div style="width:44px;height:44px;border-radius:50%;background:var(--green);display:flex;align-items:center;justify-content:center;color:white;font-weight:700;font-size:1rem;flex-shrink:0;">
                  <?= $driverInitial ?>
                </div>
                <div>
                  <div style="font-weight:600;color:var(--gray-800);"><?= htmlspecialchars($booking['driver_name']) ?></div>
                  <div style="color:var(--gray-500);font-size:0.8rem;">Driver</div>
                </div>
              </div>

              <!-- Price + Actions -->
              <div style="display:flex;align-items:center;gap:1.25rem;">
                <div style="font-family:var(--font-main);font-size:1.35rem;font-weight:800;color:var(--green);">
                  PKR <?= number_format($booking['price'], 0) ?>
                  <span style="font-size:0.8rem;color:var(--gray-400);font-weight:400;font-family:var(--font-body);">/seat</span>
                </div>

                <?php if ($booking['status'] === 'pending' || $booking['status'] === 'accepted'): ?>
                  <form action="/ridemate/actions/cancel_booking.php" method="POST"
                        onsubmit="return confirm('Cancel this booking?');">
                    <input type="hidden" name="booking_id" value="<?= $booking['id'] ?>" />
                    <button type="submit" class="btn btn-danger btn-sm" id="btn-cancel-booking-<?= $booking['id'] ?>">
                      Cancel
                    </button>
                  </form>
                <?php endif; ?>

                <a href="/ridemate/views/rides/detail.php?id=<?= $booking['ride_id'] ?>"
                   class="btn btn-outline-red btn-sm">View Ride</a>
              </div>

            </div>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>

  </main>
</div>

<?php require_once __DIR__ . '/../views/layouts/footer.php'; ?>
