<?php
session_start();
$pageTitle = 'Find Rides';
$metaDesc  = 'Search available rides on RideMate. Filter by origin, destination, date, and vehicle type.';

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../controllers/RideController.php';

$rideController = new RideController($conn);
$rides = $rideController->search();

$filters = [
    'origin'       => trim($_GET['origin']       ?? ''),
    'destination'  => trim($_GET['destination']  ?? ''),
    'date'         => trim($_GET['date']         ?? ''),
    'vehicle_type' => trim($_GET['vehicle_type'] ?? ''),
];

$hasFilter = array_filter($filters, fn($v) => $v !== '');

require_once __DIR__ . '/../../views/layouts/header.php';
?>

<!-- ── Page Header ── -->
<section style="background:var(--surface);padding:3rem 0 2rem;">
  <div class="container">
    <h1 style="color:white;font-family:var(--font-main);font-size:2rem;font-weight:800;margin-bottom:0.5rem;">
      Find Available Rides 🔍
    </h1>
    <p style="color:rgba(255,255,255,0.65);font-size:1rem;">
      Search rides by location, date, and vehicle type
    </p>
  </div>
</section>

<!-- ── Search Filter ── -->
<div style="background:var(--surface);padding:1.5rem 0;border-bottom:1px solid rgba(255,255,255,0.07);">
  <div class="container">
    <form action="/ridemate/views/rides/search.php" method="GET" id="ride_search_form">
      <div class="search-grid" style="grid-template-columns:1fr 1fr 1fr 1fr auto;">
        <div class="form-group">
          <label>From</label>
          <input type="text" name="origin" class="form-control"
                 placeholder="Pickup location"
                 value="<?= htmlspecialchars($filters['origin']) ?>" />
        </div>
        <div class="form-group">
          <label>To</label>
          <input type="text" name="destination" class="form-control"
                 placeholder="Drop-off location"
                 value="<?= htmlspecialchars($filters['destination']) ?>" />
        </div>
        <div class="form-group">
          <label>Date</label>
          <input type="date" name="date" class="form-control"
                 value="<?= htmlspecialchars($filters['date']) ?>" />
        </div>
        <div class="form-group">
          <label>Vehicle</label>
          <select name="vehicle_type" class="form-control">
            <option value="">Any Vehicle</option>
            <option value="car"      <?= $filters['vehicle_type'] === 'car'       ? 'selected' : '' ?>>🚗 Car</option>
            <option value="motorbike"<?= $filters['vehicle_type'] === 'motorbike' ? 'selected' : '' ?>>🏍️ Motorbike</option>
          </select>
        </div>
        <div style="display:flex;gap:0.5rem;align-items:flex-end;">
          <button type="submit" class="btn btn-primary" id="btn-search-rides" style="padding:0.75rem 1.5rem;">
            Search
          </button>
          <?php if ($hasFilter): ?>
            <a href="/ridemate/views/rides/search.php" class="btn btn-outline" style="padding:0.75rem 1rem;">
              ✕
            </a>
          <?php endif; ?>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- ── Results ── -->
<section style="padding:2.5rem 0;background:var(--off-white);min-height:60vh;">
  <div class="container">

    <!-- Results Count -->
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.5rem;flex-wrap:wrap;gap:1rem;">
      <div>
        <span style="font-size:1.1rem;font-weight:700;color:var(--gray-800);">
          <?= count($rides) ?> Ride<?= count($rides) !== 1 ? 's' : '' ?> Available
        </span>
        <?php if ($hasFilter): ?>
          <span style="color:var(--gray-500);font-size:0.88rem;margin-left:0.5rem;">
            (filtered results)
          </span>
        <?php endif; ?>
      </div>
      <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'driver'): ?>
        <a href="/ridemate/views/rides/create.php" class="btn btn-primary btn-sm" id="btn-post-from-search">
          + Post a Ride
        </a>
      <?php endif; ?>
    </div>

    <?php if (empty($rides)): ?>
      <!-- Empty State -->
      <div class="empty-state">
        <div class="empty-state-icon">🚌</div>
        <h3>No rides found</h3>
        <p>
          <?= $hasFilter ? 'Try adjusting your filters or check back later.' : 'No rides are available right now. Check back soon!' ?>
        </p>
        <?php if ($hasFilter): ?>
          <a href="/ridemate/views/rides/search.php" class="btn btn-outline-red" style="margin-top:1.25rem;">
            Clear Filters
          </a>
        <?php endif; ?>
      </div>

    <?php else: ?>
      <div class="rides-grid">
        <?php foreach ($rides as $ride): ?>
          <?php
            $driverInitial = strtoupper(substr($ride['driver_name'], 0, 1));
            $vehicleIcon   = $ride['vehicle_type'] === 'car' ? '🚗' : '🏍️';
            $rideDate      = date('D, M j · g:i A', strtotime($ride['date']));
          ?>
          <div class="ride-card">
            <!-- Card Header with driver info -->
            <div class="ride-card-header">
              <div class="driver-info">
                <div class="driver-avatar-placeholder" style="background:rgba(255,255,255,0.1); border-color:rgba(255,255,255,0.2);">
                  <?= defaultAvatarIcon() ?>
                </div>
                <div>
                  <div class="driver-name"><?= htmlspecialchars($ride['driver_name']) ?></div>
                  <div class="driver-label">Driver</div>
                </div>
              </div>
              <span class="vehicle-badge <?= $ride['vehicle_type'] ?>">
                <?= $vehicleIcon ?> <?= ucfirst($ride['vehicle_type']) ?>
              </span>
            </div>

            <!-- Route Info -->
            <div class="ride-card-body">
              <div class="route-display">
                <div class="route-point">
                  <div class="route-label">From</div>
                  <div class="route-name"><?= htmlspecialchars($ride['origin']) ?></div>
                </div>
                <div class="route-arrow">→</div>
                <div class="route-point">
                  <div class="route-label">To</div>
                  <div class="route-name"><?= htmlspecialchars($ride['destination']) ?></div>
                </div>
              </div>

              <div class="ride-meta">
                <div class="meta-item">📅 <?= $rideDate ?></div>
                <div class="meta-item">💺 <?= $ride['seats'] ?> seat<?= $ride['seats'] != 1 ? 's' : '' ?> left</div>
              </div>

              <div class="ride-price">
                PKR <?= number_format($ride['price'], 0) ?>
                <span>/seat</span>
              </div>
            </div>

            <!-- Footer with CTA -->
            <div class="ride-card-footer">
              <span style="color:var(--gray-500);font-size:0.82rem;">
                Posted <?= date('M j', strtotime($ride['created_at'])) ?>
              </span>
              <a href="/ridemate/views/rides/detail.php?id=<?= $ride['id'] ?>"
                 class="btn btn-primary btn-sm"
                 id="btn-view-ride-<?= $ride['id'] ?>">
                View & Book →
              </a>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>

<?php require_once __DIR__ . '/../../views/layouts/footer.php'; ?>
