<nav class="navbar navbar-expand-lg ps-navbar">
  <div class="container-fluid">
    <a class="navbar-brand fw-bold" href="<?= APP_URL ?>/index.php">
      <i class="fa-solid fa-shield-halved text-cyan"></i> PhishShield <span class="text-cyan">AI</span>
    </a>
    <div class="ms-auto d-flex align-items-center gap-2">
      <?php if (is_logged_in()): ?>
        <span class="text-muted small d-none d-md-inline">Hi, <?= e($user['name'] ?? 'User') ?></span>
        <a href="<?= APP_URL ?>/dashboard.php" class="btn btn-sm btn-outline-light">Dashboard</a>
        <a href="<?= APP_URL ?>/logout.php" class="btn btn-sm btn-danger">Logout</a>
      <?php elseif (is_admin()): ?>
        <a href="<?= APP_URL ?>/admin.php" class="btn btn-sm btn-outline-light">Admin Panel</a>
        <a href="<?= APP_URL ?>/logout.php" class="btn btn-sm btn-danger">Logout</a>
      <?php else: ?>
        <a href="<?= APP_URL ?>/login.php" class="btn btn-sm btn-outline-light">Login</a>
        <a href="<?= APP_URL ?>/register.php" class="btn btn-sm btn-cyan">Register</a>
      <?php endif; ?>
    </div>
  </div>
</nav>
