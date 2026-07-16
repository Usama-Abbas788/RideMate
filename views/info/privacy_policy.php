<?php
session_start();
$pageTitle = 'Privacy Policy';
$metaDesc  = 'How RideMate collects and uses student data for safe ride sharing and service improvement.';
require_once __DIR__ . '/../../views/layouts/header.php';
?>

<section class="page-section" style="padding:3rem 0;">
  <div class="container">
    <h1>Privacy Policy</h1>
    <p class="section-desc">RideMate respects student privacy. This policy explains how we collect, use, and protect the information needed to deliver ride sharing on campus.</p>

    <div class="info-card">
      <h2>What information we collect</h2>
      <p>We collect registration details like name, phone number, optional email (for notifications), role, and university affiliation so rides remain within the student community. We also store ride postings and booking details to operate the service.</p>
    </div>

    <div class="info-card">
      <h2>How we use your data</h2>
      <p>Your information helps match riders with drivers, confirm bookings, and provide a record of ride activity. We never sell personal data to outside parties.</p>
    </div>

    <div class="info-card">
      <h2>Keeping data safe</h2>
      <p>RideMate uses standard security practices to protect data in the database and on the server. Access to account data is limited to authenticated users and application operations only.</p>
    </div>

    <div class="info-card">
      <h2>Your choices</h2>
      <p>You can update your profile information from your dashboard and log out at any time. If you decide to leave RideMate, please contact support so we can remove your account safely.</p>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/../../views/layouts/footer.php'; ?>
