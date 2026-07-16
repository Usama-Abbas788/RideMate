<?php
session_start();
$pageTitle = 'Terms of Service';
$metaDesc  = 'Terms of service for RideMate users, drivers, and passengers in the university ride sharing community.';
require_once __DIR__ . '/../../views/layouts/header.php';
?>

<section class="page-section" style="padding:3rem 0;">
  <div class="container">
    <h1>Terms of Service</h1>
    <p class="section-desc">These terms govern your use of RideMate. By using the platform, you agree to follow the rules that keep the student ride sharing experience safe, fair, and reliable.</p>

    <div class="info-card">
      <h2>Account eligibility</h2>
      <p>RideMate is intended for university students and verified community members. You must provide accurate registration information when creating an account.</p>
    </div>

    <div class="info-card">
      <h2>Booking and ride conduct</h2>
      <p>Passengers and drivers should honor confirmed bookings and communicate clearly if plans change. Unprofessional behavior, unsafe driving, or misuse of the platform may result in account suspension.</p>
    </div>

    <div class="info-card">
      <h2>Content and behavior</h2>
      <p>Users must not post misleading ride details or engage in harassment. The community guidelines and safety tips are part of our shared expectation for respectful behavior.</p>
    </div>

    <div class="info-card">
      <h2>Limitations</h2>
      <p>RideMate provides ride matching and booking tools, but it is not a transportation provider. We do not guarantee ride availability or assume liability for travel in privately arranged rides.</p>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/../../views/layouts/footer.php'; ?>
