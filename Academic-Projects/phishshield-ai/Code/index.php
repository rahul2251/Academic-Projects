<?php
require_once __DIR__ . '/config/auth.php';
$pageTitle = 'Welcome';
include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';
?>
<section class="ps-hero fade-in">
  <div>
    <span class="badge bg-dark border border-secondary mb-3"><i class="fa-solid fa-bolt text-cyan"></i> Powered by Gemini 2.5 Flash</span>
    <h1>Detect Phishing Before It Hits You.</h1>
    <p>PhishShield AI combines heuristic analysis, blacklist intelligence, and Google Gemini AI to instantly classify suspicious URLs and emails — protecting users in real time.</p>
    <div class="d-flex justify-content-center gap-2 mt-4">
      <?php if (is_logged_in()): ?>
        <a href="<?= APP_URL ?>/scanner.php" class="btn btn-grad btn-lg"><i class="fa-solid fa-magnifying-glass"></i> Open Scanner</a>
        <a href="<?= APP_URL ?>/dashboard.php" class="btn btn-outline-light btn-lg">Dashboard</a>
      <?php else: ?>
        <a href="<?= APP_URL ?>/register.php" class="btn btn-grad btn-lg">Get Started Free</a>
        <a href="<?= APP_URL ?>/login.php" class="btn btn-outline-light btn-lg">Sign In</a>
      <?php endif; ?>
    </div>

    <div class="row g-3 mt-5 text-start" style="max-width:980px; margin-inline:auto;">
      <div class="col-md-4"><div class="ps-card"><h5><i class="fa-solid fa-link text-cyan"></i> URL Scanner</h5><p class="text-muted small mb-0">Heuristic + blacklist + AI classification with risk scoring.</p></div></div>
      <div class="col-md-4"><div class="ps-card"><h5><i class="fa-solid fa-envelope text-cyan"></i> Email Scanner</h5><p class="text-muted small mb-0">Detect urgency, fake offers, credential requests, and suspicious links.</p></div></div>
      <div class="col-md-4"><div class="ps-card"><h5><i class="fa-solid fa-robot text-cyan"></i> AI Chatbot</h5><p class="text-muted small mb-0">Ask cybersecurity questions and get instant guidance.</p></div></div>
    </div>
  </div>
</section>
<footer class="ps-footer">© <?= date('Y') ?> PhishShield AI · Built for academic & demo purposes.</footer>
<?php include __DIR__ . '/includes/footer.php'; ?>
