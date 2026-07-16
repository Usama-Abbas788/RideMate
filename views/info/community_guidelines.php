<?php
session_start();
$pageTitle = 'Community Guidelines';
$metaDesc  = 'RideMate community guidelines for respectful, friendly ride sharing on university routes.';
require_once __DIR__ . '/../../views/layouts/header.php';
?>

<section class="page-section" style="padding:3rem 0;">
  <div class="container">
    <h1>Community Guidelines</h1>
    <p class="section-desc">RideMate is a shared ride space for students. These guidelines help everyone enjoy safe, respectful, and stress-free journeys.</p>

    <div class="info-card">
      <h2>Respect fellow riders</h2>
      <p>Be courteous to drivers and passengers. Keep conversations polite, avoid loud music without permission, and leave the vehicle clean after your ride.</p>
    </div>

    <div class="info-card">
      <h2>Arrive on time</h2>
      <p>Timely arrival keeps rides running smoothly. If you need to cancel, do it early so the driver and other riders can adjust.</p>
    </div>

    <div class="info-card">
      <h2>Use RideMate responsibly</h2>
      <p>Post accurate trip details and choose routes that match your needs. Honest communication helps everyone trust the platform.</p>
    </div>

    <div class="info-card">
      <h2>Follow university policies</h2>
      <p>RideMate complements campus safety standards. Respect any rules your university has for shared transportation and parking.</p>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/../../views/layouts/footer.php'; ?>
