<?php
session_start();

if (isset($_SESSION['user_id'])) {
    if ($_SESSION['user_role'] === 'admin') {
        header('Location: /ridemate/admin/dashboard.php');
        exit;
    }
} else {
    header('Location: /ridemate/views/auth/login.php');
    exit;
}

$pageTitle = 'Home';
$metaDesc  = 'RideMate — Smart ride sharing for university students. Find affordable rides, post your route, and connect with fellow students.';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/models/Ride.php';

$rideModel = new Ride($conn);
$totalRides = $rideModel->getTotalCount();
?>
<?php require_once __DIR__ . '/views/layouts/header.php'; ?>

<!-- ── HERO ── -->
<section class="hero">
  <div class="container">
    <div class="row align-items-center">
      <!-- Left Content -->
      <div class="col-lg-7 col-md-12">
        <div class="hero-content" style="max-width: 100%;">
          <h1>
            Your Campus Commute,<br>
            <span class="highlight">Reimagined.</span>
          </h1>
          <p>
            Connect with fellow students for safe, affordable rides. Split costs,
            reduce carbon footprint, and never miss class again.
          </p>
          <div class="hero-btns">
            <a href="/ridemate/views/rides/search.php" class="btn btn-primary btn-xl" id="hero-find-ride">
              🔍 Find a Ride
            </a>
            <?php if (!isset($_SESSION['user_id'])): ?>
              <a href="/ridemate/views/auth/register.php" class="btn btn-outline btn-xl" id="hero-register">
                Join as Driver
              </a>
            <?php else: ?>
              <?php if ($_SESSION['user_role'] === 'passenger'): ?>
                <a href="#" class="btn btn-outline btn-xl" id="hero-post-ride-blocked"
                   onclick="showDialog('Only drivers can post rides. If you want to offer rides, please register as a driver.', 'Action Not Allowed'); return false;">
                  🚗 Post a Ride
                </a>
              <?php else: ?>
                <a href="/ridemate/views/rides/create.php" class="btn btn-outline btn-xl" id="hero-post-ride">
                  🚗 Post a Ride
                </a>
              <?php endif; ?>
            <?php endif; ?>
          </div>

          <!-- Quick Search Box -->
          <div class="search-box">
            <div class="search-title">🔍 Quick Ride Search</div>
            <form action="/ridemate/views/rides/search.php" method="GET" class="search-grid" id="ride_search_form">
              <div class="form-group">
                <label>From</label>
                <input type="text" name="origin" class="form-control" placeholder="Pickup location" />
              </div>
              <div class="form-group">
                <label>To</label>
                <input type="text" name="destination" class="form-control" placeholder="Drop-off location" />
              </div>
              <div class="form-group">
                <label>Date</label>
                <input type="date" name="date" class="form-control" />
              </div>
              <div class="form-group">
                <label>Vehicle</label>
                <select name="vehicle_type" class="form-control">
                  <option value="">Any</option>
                  <option value="car">🚗 Car</option>
                  <option value="motorbike">🏍️ Motorbike</option>
                </select>
              </div>
              <button type="submit" class="btn btn-primary" id="btn-quick-search" style="padding:0.75rem 1.5rem;">
                Search
              </button>
            </form>
          </div>

          <div class="hero-stats">
            <div class="hero-stat">
              <strong class="counter-animate" data-suffix="+"><?= max($totalRides, 120) ?></strong>
              <span>Active Rides</span>
            </div>
            <div class="hero-stat">
              <strong class="counter-animate" data-suffix="+">850</strong>
              <span>Students Connected</span>
            </div>
            <div class="hero-stat">
              <strong class="counter-animate" data-suffix="%">98</strong>
              <span>Satisfaction Rate</span>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Right Image -->
      <div class="col-lg-5 col-md-12 text-center mt-5 mt-lg-0">
        <div style="display: inline-block; padding: 10px; background: rgba(255, 255, 255, 0.05); border-radius: 50%; box-shadow: 0 20px 40px rgba(0,0,0,0.3); border: 2px solid rgba(255, 255, 255, 0.1);">
          <img src="/ridemate/assets/images/hero.png" alt="RideMate Logo and Vehicles" class="img-fluid" style="width: 380px; height: 380px; border-radius: 50%; object-fit: cover; aspect-ratio: 1/1; display: block;" />
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ── ABOUT ── -->
<section id="about" class="section" style="background:white;">
  <div class="container text-center">
    <div class="section-header">
      <div class="section-tag">About Us</div>
      <h2 class="section-title">Built for Students, by Students</h2>
      <p class="section-desc" style="margin:0 auto;">RideMate was created to solve the daily commuting struggles of university students. We believe in affordable, safe, and eco-friendly travel.</p>
    </div>
  </div>
</section>

<!-- ── HOW IT WORKS ── -->
<section id="how-it-works" class="section" style="background:#f8f9fa;">
  <div class="container">
    <div class="section-header text-center">
      <div class="section-tag">Simple Process</div>
      <h2 class="section-title">How RideMate Works</h2>
      <p class="section-desc" style="margin:0 auto;">Get started in minutes. No complicated setup required.</p>
    </div>
    <div class="steps-grid">
      <div class="step-card">
        <div class="step-num">1</div>
        <div class="step-title">Create Account</div>
        <p class="step-desc">Sign up as a driver or passenger with your phone number in seconds.</p>
      </div>
      <div class="step-card">
        <div class="step-num">2</div>
        <div class="step-title">Find or Post Ride</div>
        <p class="step-desc">Search available rides by location, date, and vehicle type — or post your own.</p>
      </div>
      <div class="step-card">
        <div class="step-num">3</div>
        <div class="step-title">Book & Connect</div>
        <p class="step-desc">Send a booking request. Drivers review and confirm your seat instantly.</p>
      </div>
      <div class="step-card">
        <div class="step-num">4</div>
        <div class="step-title">Travel Together</div>
        <p class="step-desc">Meet your ride partner, share the journey, and split the cost fairly.</p>
      </div>
    </div>
  </div>
</section>

<!-- ── FEATURES ── -->
<section id="features" class="section" style="background:white;">
  <div class="container">
    <div class="section-header">
      <div class="section-tag">Why RideMate?</div>
      <h2 class="section-title">Everything You Need for Safe Campus Travel</h2>
      <p class="section-desc">Designed specifically for university life with features that matter.</p>
    </div>
    <div class="features-grid">
      <div class="feature-card">
        <div class="feature-icon">🛡️</div>
        <div class="feature-title">Verified Students Only</div>
        <p class="feature-desc">All users are university students, creating a trusted community for safer rides.</p>
      </div>
      <div class="feature-card">
        <div class="feature-icon">💰</div>
        <div class="feature-title">Affordable Pricing</div>
        <p class="feature-desc">Split fuel costs with fellow students. Pay only your fair share of the journey.</p>
      </div>
      <div class="feature-card">
        <div class="feature-icon">🚗</div>
        <div class="feature-title">Car & Motorbike</div>
        <p class="feature-desc">Choose between cars and motorbikes based on your comfort and budget.</p>
      </div>
      <div class="feature-card">
        <div class="feature-icon">📍</div>
        <div class="feature-title">Flexible Routes</div>
        <p class="feature-desc">Search by origin, destination, date, and vehicle type to find your perfect match.</p>
      </div>
      <div class="feature-card">
        <div class="feature-icon">⚡</div>
        <div class="feature-title">Instant Booking</div>
        <p class="feature-desc">Book a seat instantly and get notified when the driver accepts your request.</p>
      </div>
      <div class="feature-card">
        <div class="feature-icon">🎓</div>
        <div class="feature-title">Student Community</div>
        <p class="feature-desc">Build connections, make friends, and create a greener campus commute together.</p>
      </div>
    </div>
  </div>
</section>

<!-- ── CTA ── -->
<section class="cta-section">
  <div class="container" style="position:relative;z-index:2;">
    <h2 style="color: var(--gray-300);">Ready to Ride Smarter?</h2>
    <p>Join thousands of university students saving money and time with RideMate.</p>
    <div class="d-flex gap-2" style="justify-content:center;flex-wrap:wrap;">
      <?php if (!isset($_SESSION['user_id'])): ?>
        <a href="/ridemate/views/auth/register.php" class="btn btn-primary btn-xl" id="cta-register">
          🎓 Get Started Free
        </a>
        <a href="/ridemate/views/rides/search.php" class="btn btn-outline btn-xl" id="cta-search">
          Browse Rides
        </a>
      <?php else: ?>
        <a href="/ridemate/views/rides/search.php" class="btn btn-primary btn-xl" id="cta-find">
          🔍 Find a Ride Now
        </a>
        <?php if ($_SESSION['user_role'] === 'passenger'): ?>
          <a href="#" class="btn btn-outline btn-xl" id="cta-post-blocked"
             onclick="showDialog('Only drivers can post rides. If you want to offer rides, please register as a driver.', 'Action Not Allowed'); return false;">
            🚗 Post a Ride
          </a>
        <?php elseif ($_SESSION['user_role'] === 'driver'): ?>
          <a href="/ridemate/views/rides/create.php" class="btn btn-outline btn-xl" id="cta-post">
            🚗 Post a Ride
          </a>
        <?php endif; ?>
      <?php endif; ?>
    </div>
  </div>
</section>

<!-- ── CONTACT ── -->
<section id="contact" class="section" style="background:white; border-top:1px solid #eee;">
  <div class="container text-center">
    <div class="section-header">
      <div class="section-tag">Contact Us</div>
      <h2 class="section-title">Get in Touch</h2>
      <p class="section-desc" style="margin:0 auto;">Have questions or need support? Reach out to us at <a href="mailto:support@ridemate.com" style="color:var(--green);">mixtology422@gmail.com</a></p>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/views/layouts/footer.php'; ?>
