<?php
require_once '../config/config.php';
require_once '../config/db.php';
require_once '../config/auth.php';
require_once '../includes/functions.php';
require_admin_login();
$page_title = 'Admin Settings';
$extra_css = '<link rel="stylesheet" href="' . SITE_URL . '/assets/css/dashboard.css"><link rel="stylesheet" href="' . SITE_URL . '/assets/css/admin.css">';
?>
<?php include '../includes/header.php'; ?>
<div class="ps-layout">
<?php include 'includes/admin_sidebar.php'; ?>
<div class="ps-main">
    <div class="ps-topbar admin-topbar"><div class="fw-semibold text-white">Settings</div></div>
    <div class="ps-content" style="max-width:700px;">
        <?php include '../includes/alerts.php'; ?>
        <div class="ps-card mb-4">
            <h6 class="fw-bold mb-3"><i class="bi bi-robot text-accent me-2"></i>Gemini API Configuration</h6>
            <p class="text-muted small mb-3">Set your Google Gemini API key in <code style="color:var(--ps-accent);">config/config.php</code></p>
            <div class="p-3" style="background:var(--ps-dark3);border-radius:var(--radius-sm);font-family:monospace;font-size:.82rem;color:var(--ps-cyan);">
                define('GEMINI_API_KEY', 'YOUR_GEMINI_API_KEY_HERE');
            </div>
            <p class="text-muted mt-2 small">Get your free API key at <a href="https://aistudio.google.com/" target="_blank" class="text-accent">Google AI Studio →</a></p>
        </div>
        <div class="ps-card mb-4">
            <h6 class="fw-bold mb-3"><i class="bi bi-database text-accent me-2"></i>Database Info</h6>
            <div class="d-flex justify-content-between py-2 border-bottom" style="border-color:var(--ps-border)!important;"><span class="text-muted small">Host</span><span class="text-white small"><?= DB_HOST ?></span></div>
            <div class="d-flex justify-content-between py-2 border-bottom" style="border-color:var(--ps-border)!important;"><span class="text-muted small">Database</span><span class="text-white small"><?= DB_NAME ?></span></div>
            <div class="d-flex justify-content-between py-2"><span class="text-muted small">Status</span><span class="badge bg-success">Connected</span></div>
        </div>
        <div class="ps-card">
            <h6 class="fw-bold mb-3"><i class="bi bi-info-circle text-accent me-2"></i>System Info</h6>
            <div class="d-flex justify-content-between py-2 border-bottom" style="border-color:var(--ps-border)!important;"><span class="text-muted small">PHP Version</span><span class="text-white small"><?= phpversion() ?></span></div>
            <div class="d-flex justify-content-between py-2 border-bottom" style="border-color:var(--ps-border)!important;"><span class="text-muted small">App Version</span><span class="text-white small"><?= SITE_VERSION ?></span></div>
            <div class="d-flex justify-content-between py-2"><span class="text-muted small">Server</span><span class="text-white small"><?= $_SERVER['SERVER_SOFTWARE'] ?? 'Apache' ?></span></div>
        </div>
    </div>
</div>
</div>
<?php include '../includes/footer.php'; ?>
