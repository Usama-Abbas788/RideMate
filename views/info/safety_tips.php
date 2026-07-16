<?php
session_start();
$pageTitle = 'Safety Tips';
$metaDesc  = 'Practical safety tips for RideMate users to stay secure while sharing rides on campus.';
require_once __DIR__ . '/../../views/layouts/header.php';
?>

<section class="page-section" style="padding:3rem 0;">
  <div class="container">
    <h1>Safety Tips</h1>
    <p class="section-desc">RideMate is built for students who want a safer, smarter way to share rides across campus and beyond. Follow these best practices to make every trip comfortable and secure.</p>

    <div class="info-card">
      <h2>1. Confirm ride details before boarding</h2>
      <p>Verify the driver name, route, and vehicle details you see in the app before you get in. Match the license plate and driver name so you always join the correct ride.</p>
    </div>

    <div class="info-card">
      <h2>2. Share your trip with friends</h2>
      <p>Let a friend or roommate know when you leave and when you arrive. This simple step keeps your campus commute safer and gives your network peace of mind.</p>
    </div>

    <div class="info-card">
      <h2>3. Ride with verified student drivers</h2>
      <p>RideMate is designed for university communities. Only registered students and verified drivers can create or book rides, so you can travel with people who share your campus environment.</p>
    </div>

    <div class="info-card">
      <h2>4. Trust your instincts</h2>
      <p>If a ride doesn’t feel right, cancel and choose another option. Safety comes first, and RideMate supports responsible commuting by letting you pick the best match.</p>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/../../views/layouts/footer.php'; ?>
