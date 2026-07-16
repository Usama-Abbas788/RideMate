<?php
session_start();
$rideId = intval($_GET['id'] ?? 0);
if (!$rideId) {
    header('Location: /ridemate/views/rides/search.php');
    exit;
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../controllers/RideController.php';
require_once __DIR__ . '/../../controllers/BookingController.php';

$rideController    = new RideController($conn);
$bookingController = new BookingController($conn);

$ride = $rideController->getDetail($rideId);
if (!$ride) {
    header('Location: /ridemate/views/rides/search.php');
    exit;
}

$isLoggedIn    = isset($_SESSION['user_id']);
$userId        = $_SESSION['user_id']   ?? null;
$userRole      = $_SESSION['user_role'] ?? '';
$isDriver      = ($ride['driver_id'] == $userId);
$alreadyBooked = $isLoggedIn ? $bookingController->getRideBookings($rideId) : [];

// Check if current user already booked
$userBooked = false;
if ($isLoggedIn && $userRole === 'passenger') {
    require_once __DIR__ . '/../../models/Booking.php';
    $bookingModel = new Booking($conn);
    $userBooked   = $bookingModel->alreadyBooked($rideId, $userId);
}

$pageTitle    = htmlspecialchars($ride['origin']) . ' → ' . htmlspecialchars($ride['destination']);
$metaDesc     = 'Ride from ' . $ride['origin'] . ' to ' . $ride['destination'] . ' on ' . date('M j, Y', strtotime($ride['date']));
$vehicleIcon  = $ride['vehicle_type'] === 'car' ? '🚗' : '🏍️';
$driverInitial = strtoupper(substr($ride['driver_name'], 0, 1));

require_once __DIR__ . '/../../views/layouts/header.php';
?>

<section style="background:var(--off-white);min-height:calc(100vh - 70px);padding:2.5rem 0;">
  <div class="container" style="max-width:860px;">

    <a href="/ridemate/views/rides/search.php"
       style="color:var(--gray-500);font-size:0.88rem;display:inline-flex;align-items:center;gap:0.35rem;margin-bottom:1.5rem;">
      ← Back to Rides
    </a>

    <!-- Alerts -->
    <?php if (!empty($_SESSION['error'])): ?>
      <div class="alert alert-error">⚠️ <?= htmlspecialchars($_SESSION['error']) ?></div>
      <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
    <?php if (!empty($_SESSION['success'])): ?>
      <div class="alert alert-success">✅ <?= htmlspecialchars($_SESSION['success']) ?></div>
      <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <!-- Route Hero -->
    <div class="detail-hero">
      <div class="detail-route">
        <div class="detail-location">
          <span style="color:rgba(255,255,255,0.5);font-size:0.78rem;text-transform:uppercase;letter-spacing:0.5px;">From</span>
          <h2><?= htmlspecialchars($ride['origin']) ?></h2>
        </div>
        <div class="detail-arrow">→</div>
        <div class="detail-location">
          <span style="color:rgba(255,255,255,0.5);font-size:0.78rem;text-transform:uppercase;letter-spacing:0.5px;">To</span>
          <h2><?= htmlspecialchars($ride['destination']) ?></h2>
        </div>
      </div>
      <div class="detail-price">
        <span style="color:rgba(255,255,255,0.5);font-size:0.78rem;">Per Seat</span>
        <div class="amount">PKR <?= number_format($ride['price'], 0) ?></div>
        <span class="vehicle-badge <?= $ride['vehicle_type'] ?>" style="margin-top:0.5rem;display:inline-flex;">
          <?= $vehicleIcon ?> <?= ucfirst($ride['vehicle_type']) ?>
        </span>
      </div>
    </div>

    <div style="display:grid;grid-template-columns:2fr 1fr;gap:1.5rem;align-items:start;">

      <!-- Left: Details -->
      <div>

        <!-- Ride Info Card -->
        <div class="card" style="margin-bottom:1.5rem;">
          <div class="card-header">
            <h3 style="font-family:var(--font-main);font-size:1rem;font-weight:700;">Ride Information</h3>
          </div>
          <div class="card-body">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;">
              <div>
                <div style="font-size:0.78rem;color:var(--gray-500);text-transform:uppercase;letter-spacing:0.4px;margin-bottom:0.25rem;">Date & Time</div>
                <div style="font-weight:600;color:var(--gray-800);">📅 <?= date('D, M j, Y', strtotime($ride['date'])) ?></div>
                <div style="font-weight:600;color:var(--gray-800);">🕐 <?= date('g:i A', strtotime($ride['date'])) ?></div>
              </div>
              <div>
                <div style="font-size:0.78rem;color:var(--gray-500);text-transform:uppercase;letter-spacing:0.4px;margin-bottom:0.25rem;">Seats Left</div>
                <div style="font-weight:700;color:<?= $ride['seats'] > 0 ? 'var(--green)' : 'var(--black)' ?>;font-size:1.25rem;">
                  💺 <?= $ride['seats'] ?> <?= $ride['seats'] > 0 ? 'available' : '(full)' ?>
                </div>
              </div>
              <div>
                <div style="font-size:0.78rem;color:var(--gray-500);text-transform:uppercase;letter-spacing:0.4px;margin-bottom:0.25rem;">Vehicle Type</div>
                <div style="font-weight:600;color:var(--gray-800);"><?= $vehicleIcon ?> <?= ucfirst($ride['vehicle_type']) ?></div>
              </div>
              <div>
                <div style="font-size:0.78rem;color:var(--gray-500);text-transform:uppercase;letter-spacing:0.4px;margin-bottom:0.25rem;">Price</div>
                <div style="font-weight:700;color:var(--black);font-size:1.25rem;">
                  PKR <?= number_format($ride['price'], 2) ?> <span style="font-size:0.85rem;color:var(--gray-500);font-weight:400;">/seat</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Driver Card -->
        <div class="card">
          <div class="card-header">
            <h3 style="font-family:var(--font-main);font-size:1rem;font-weight:700;">About the Driver</h3>
          </div>
          <div class="card-body">
            <div style="display:flex;align-items:center;gap:1rem;">
              <div style="width:70px;height:70px;border-radius:50%;background:rgba(255,255,255,0.1);display:flex;align-items:center;justify-content:center;border:1px solid rgba(255,255,255,0.2);flex-shrink:0;">
                <?= defaultAvatarIcon() ?>
              </div>
              <div>
                <div style="font-weight:700;font-size:1.1rem;color:var(--gray-800);"><?= htmlspecialchars($ride['driver_name']) ?></div>
                <div style="color:var(--gray-500);font-size:0.88rem;margin-top:0.2rem;"><?= htmlspecialchars($ride['driver_phone'] ?? '') ?></div>
                <span class="badge badge-driver" style="margin-top:0.5rem;">🚗 Driver</span>
              </div>
            </div>
          </div>
        </div>

      </div>

      <!-- Right: Booking CTA -->
      <div>
        <div class="card" style="position:sticky;top:90px;">
          <div class="card-header" style="background:var(--surface-2);">
            <h3 style="color:white;font-family:var(--font-main);font-size:1rem;font-weight:700;">Book This Ride</h3>
          </div>
          <div class="card-body" style="text-align:center;">
            <div style="font-family:var(--font-main);font-size:2rem;font-weight:800;color:var(--green);margin-bottom:0.25rem;">
              PKR <?= number_format($ride['price'], 0) ?>
            </div>
            <div style="color:var(--gray-500);font-size:0.85rem;margin-bottom:1.5rem;">per seat</div>

            <?php if (!$isLoggedIn): ?>
              <a href="/ridemate/views/auth/login.php" class="btn btn-primary w-100 btn-lg" id="btn-login-to-book">
                Login to Book
              </a>
              <div style="color:var(--gray-400);font-size:0.8rem;margin-top:0.75rem;">
                Need an account? <a href="/ridemate/views/auth/register.php" style="color:var(--green);">Register free</a>
              </div>

            <?php elseif ($isDriver): ?>
              <div class="badge badge-driver" style="padding:0.6rem 1rem;font-size:0.88rem;">
                This is your ride
              </div>

            <?php elseif ($userBooked): ?>
              <div class="badge badge-accepted" style="padding:0.6rem 1rem;font-size:0.88rem;display:block;text-align:center;">
                ✅ Already Booked
              </div>
              <div style="color:var(--gray-400);font-size:0.8rem;margin-top:0.75rem;">
                Check your dashboard for status.
              </div>

            <?php elseif ($ride['seats'] <= 0): ?>
              <div class="badge badge-rejected" style="padding:0.6rem 1rem;font-size:0.88rem;display:block;text-align:center;">
                😔 Fully Booked
              </div>

            <?php else: ?>
              <form action="/ridemate/actions/book_ride.php" method="POST" id="booking-form-<?= $ride['id'] ?>">
                <input type="hidden" name="ride_id" value="<?= $ride['id'] ?>" />
                <?php if ($userRole === 'driver'): ?>
                  <button type="button" class="btn btn-primary w-100 btn-lg" onclick="showDialog('Drivers cannot book rides. Please log in as a passenger to book rides.', 'Action Not Allowed');">
                    🎒 Request Booking
                  </button>
                <?php else: ?>
                  <button type="submit" class="btn btn-primary w-100 btn-lg" id="btn-book-ride-<?= $ride['id'] ?>">
                    🎒 Request Booking
                  </button>
                <?php endif; ?>
              </form>
              <div style="color:var(--gray-400);font-size:0.8rem;margin-top:0.75rem;">
                Driver will confirm your request
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>

    </div>
  </div>
</section>

<?php require_once __DIR__ . '/../../views/layouts/footer.php'; ?>
