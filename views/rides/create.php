<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'driver') {
    $_SESSION['error'] = 'Only drivers can post rides. Please login as a driver.';
    header('Location: /ridemate/views/auth/login.php');
    exit;
}
$pageTitle = 'Post a Ride';
$metaDesc  = 'Post your ride on RideMate and help fellow students commute affordably.';
require_once __DIR__ . '/../../views/layouts/header.php';
?>

<section style="background:var(--off-white);min-height:calc(100vh - 70px);padding:3rem 0;">
  <div class="container" style="max-width:700px;">

    <!-- Page Header -->
    <div style="margin-bottom:2rem;">
      <a href="/ridemate/driver/dashboard.php"
         style="color:var(--gray-500);font-size:0.88rem;display:inline-flex;align-items:center;gap:0.35rem;margin-bottom:1rem;">
        ← Back to Dashboard
      </a>
      <h1 class="page-title">Post a New Ride 🚗</h1>
      <p class="page-subtitle">Share your route and help students travel affordably together.</p>
    </div>

    <!-- Alerts -->
    <?php if (!empty($_SESSION['error'])): ?>
      <div class="alert alert-error">⚠️ <?= htmlspecialchars($_SESSION['error']) ?></div>
      <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- Form Card -->
    <div class="card">
      <div class="card-header" style="background:var(--surface-2);">
        <h3 style="color:white;font-family:var(--font-main);font-size:1.1rem;font-weight:700;">Ride Details</h3>
        <span class="vehicle-badge car" style="background:rgba(255,255,255,0.15);color:white;">New Posting</span>
      </div>
      <div class="card-body">
        <form action="/ridemate/actions/create_ride.php" method="POST" id="create-ride-form">

          <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;margin-bottom:1.25rem;">
            <div class="form-group">
              <label class="dark-label">📍 Origin / Pickup Location *</label>
              <input
                type="text"
                name="origin"
                id="ride-origin"
                class="form-control dark"
                placeholder="e.g., City Center"
                required
                maxlength="100"
              />
            </div>
            <div class="form-group">
              <label class="dark-label">🏁 Destination *</label>
              <input
                type="text"
                name="destination"
                id="ride-destination"
                class="form-control dark"
                placeholder="e.g., University Campus"
                required
                maxlength="100"
              />
            </div>
          </div>

          <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;margin-bottom:1.25rem;">
            <div class="form-group">
              <label class="dark-label">📅 Date & Time *</label>
              <input
                type="datetime-local"
                name="date"
                id="ride_date"
                class="form-control dark"
                required
              />
            </div>
            <div class="form-group">
              <label class="dark-label">🚗 Vehicle Type *</label>
              <select name="vehicle_type" id="ride-vehicle" class="form-control dark" required>
                <option value="">-- Select Vehicle --</option>
                <option value="car">🚗 Car</option>
                <option value="motorbike">🏍️ Motorbike</option>
              </select>
            </div>
          </div>

          <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.25rem;margin-bottom:2rem;">
            <div class="form-group">
              <label class="dark-label">💺 Available Seats *</label>
              <input
                type="number"
                name="seats"
                id="ride-seats"
                class="form-control dark"
                placeholder="e.g., 3"
                min="1"
                max="10"
                required
              />
            </div>
            <div class="form-group">
              <label class="dark-label">💰 Price per Seat (PKR) *</label>
              <input
                type="number"
                name="price"
                id="ride-price"
                class="form-control dark"
                placeholder="e.g., 150"
                min="0"
                step="0.01"
                required
              />
            </div>
          </div>

          <div style="display:flex;gap:1rem;justify-content:flex-end;">
            <a href="/ridemate/driver/dashboard.php" class="btn btn-outline-red">Cancel</a>
            <button type="submit" class="btn btn-primary btn-lg" id="btn-post-ride">
              🚗 Post This Ride
            </button>
          </div>

        </form>
      </div>
    </div>

    <!-- Tips -->
    <div style="margin-top:1.5rem;background:rgba(0,0,0,0.04);border:1px solid rgba(0,0,0,0.08);border-radius:14px;padding:1.25rem;">
      <h4 style="color:var(--red-dark);font-size:0.9rem;font-weight:700;margin-bottom:0.75rem;">💡 Tips for a Great Ride</h4>
      <ul style="display:flex;flex-direction:column;gap:0.4rem;list-style:disc;padding-left:1.25rem;color:var(--gray-600);font-size:0.88rem;">
        <li>Set a fair price — around 20-30 PKR per km is common.</li>
        <li>Be specific about your pickup location so passengers know where to meet.</li>
        <li>Always confirm bookings promptly so passengers can plan ahead.</li>
      </ul>
    </div>

  </div>
</section>

<?php require_once __DIR__ . '/../../views/layouts/footer.php'; ?>
