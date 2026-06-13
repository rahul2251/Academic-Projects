<?php
$current = basename($_SERVER['PHP_SELF']);
?>
<aside class="admin-sidebar">
    <div class="sidebar-brand" style="padding:1.25rem 1rem;border-bottom:1px solid var(--ps-border);">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-shield-shaded fs-4 text-accent"></i>
            <span class="fw-bold text-white">PhishShield <span class="text-accent">Admin</span></span>
        </div>
    </div>
    <div class="px-2 py-3 flex-grow-1">
        <div class="sidebar-label">Overview</div>
        <a href="<?= SITE_URL ?>/admin/dashboard.php" class="sidebar-link admin-sidebar-link <?= $current==='dashboard.php'?'active':'' ?>"><i class="bi bi-grid-1x2-fill"></i> Dashboard</a>
        <div class="sidebar-label mt-3">Users</div>
        <a href="<?= SITE_URL ?>/admin/users.php" class="sidebar-link admin-sidebar-link <?= $current==='users.php'?'active':'' ?>"><i class="bi bi-people"></i> Manage Users</a>
        <div class="sidebar-label mt-3">Scans</div>
        <a href="<?= SITE_URL ?>/admin/scans-url.php" class="sidebar-link admin-sidebar-link <?= $current==='scans-url.php'?'active':'' ?>"><i class="bi bi-link-45deg"></i> URL Scans</a>
        <a href="<?= SITE_URL ?>/admin/scans-email.php" class="sidebar-link admin-sidebar-link <?= $current==='scans-email.php'?'active':'' ?>"><i class="bi bi-envelope-exclamation"></i> Email Scans</a>
        <div class="sidebar-label mt-3">Security</div>
        <a href="<?= SITE_URL ?>/admin/blacklist.php" class="sidebar-link admin-sidebar-link <?= $current==='blacklist.php'?'active':'' ?>"><i class="bi bi-slash-circle"></i> Blacklist</a>
        <a href="<?= SITE_URL ?>/admin/whitelist.php" class="sidebar-link admin-sidebar-link <?= $current==='whitelist.php'?'active':'' ?>"><i class="bi bi-check-circle"></i> Whitelist</a>
        <div class="sidebar-label mt-3">Reports</div>
        <a href="<?= SITE_URL ?>/admin/feedback.php" class="sidebar-link admin-sidebar-link <?= $current==='feedback.php'?'active':'' ?>"><i class="bi bi-chat-dots"></i> Feedback</a>
        <a href="<?= SITE_URL ?>/admin/analytics.php" class="sidebar-link admin-sidebar-link <?= $current==='analytics.php'?'active':'' ?>"><i class="bi bi-bar-chart"></i> Analytics</a>
        <a href="<?= SITE_URL ?>/admin/logs.php" class="sidebar-link admin-sidebar-link <?= $current==='logs.php'?'active':'' ?>"><i class="bi bi-journal-text"></i> Activity Logs</a>
        <a href="<?= SITE_URL ?>/admin/settings.php" class="sidebar-link admin-sidebar-link <?= $current==='settings.php'?'active':'' ?>"><i class="bi bi-gear"></i> Settings</a>
    </div>
    <div style="border-top:1px solid var(--ps-border);padding:1rem;">
        <div class="text-muted small mb-1"><?= h($_SESSION['admin_name'] ?? 'Admin') ?></div>
        <a href="<?= SITE_URL ?>/admin/logout.php" class="sidebar-link text-danger" style="padding:.4rem .5rem;"><i class="bi bi-box-arrow-right"></i> Logout</a>
    </div>
</aside>
