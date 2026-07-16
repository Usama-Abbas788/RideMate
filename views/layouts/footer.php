<?php $base = '/ridemate'; ?>
<!-- ── FOOTER ── -->
<footer class="footer" role="contentinfo">
  <div class="container">
    <div class="footer-grid">
      <div>
        <div class="footer-brand">RideMate</div>
        <p class="footer-desc">
          Smart ride sharing for university students. Save money, reduce traffic,
          and make new friends on your daily commute.
        </p>
      </div>
      <div>
        <h4>Quick Links</h4>
        <ul>
          <li><a href="<?= $base ?>/index.php">Home</a></li>
          <li><a href="<?= $base ?>/views/rides/search.php">Find Rides</a></li>
          <li><a href="<?= $base ?>/views/auth/register.php">Register</a></li>
          <li><a href="<?= $base ?>/views/auth/login.php">Login</a></li>
        </ul>
      </div>
      <div>
        <h4>Support</h4>
        <ul>
          <li><a href="<?= $base ?>/views/info/safety_tips.php">Safety Tips</a></li>
          <li><a href="<?= $base ?>/views/info/community_guidelines.php">Community Guidelines</a></li>
          <li><a href="<?= $base ?>/views/info/privacy_policy.php">Privacy Policy</a></li>
          <li><a href="<?= $base ?>/views/info/terms_of_service.php">Terms of Service</a></li>
        </ul>
      </div>
    </div>

    <div class="footer-bottom">
      <p>&copy; <?= date('Y') ?> RideMate. Built for University Students. All rights reserved.</p>
    </div>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= $base ?>/assets/js/main.js"></script>
</body>
</html>
