<?php
$current = basename($_SERVER['PHP_SELF']);
$dir = basename(dirname($_SERVER['PHP_SELF']));
?>
<aside class="ps-sidebar d-flex flex-column">
    <div class="sidebar-brand">
        <a href="<?= SITE_URL ?>" class="d-flex align-items-center text-decoration-none">
            <i class="bi bi-shield-shaded fs-4 text-accent me-2"></i>
            <span class="fw-bold text-white">PhishShield <span class="text-accent">AI</span></span>
        </a>
    </div>

    <div class="sidebar-user px-3 py-3">
        <div class="d-flex align-items-center gap-2">
            <img src="<?= get_avatar($_SESSION['user_avatar'] ?? '', $_SESSION['user_name'] ?? 'User') ?>"
                 class="rounded-circle" width="38" height="38" alt="avatar">
            <div class="overflow-hidden">
                <div class="text-white fw-semibold text-truncate small"><?= h($_SESSION['user_name'] ?? 'User') ?></div>
                <div class="text-muted" style="font-size:.72rem;"><?= h($_SESSION['user_email'] ?? '') ?></div>
            </div>
        </div>
    </div>

    <nav class="sidebar-nav flex-grow-1 px-2">
        <div class="sidebar-label">Main</div>
        <a href="<?= SITE_URL ?>/user/dashboard.php" class="sidebar-link <?= $current==='dashboard.php'?'active':'' ?>">
            <i class="bi bi-grid-1x2-fill"></i> Dashboard
        </a>
        <div class="sidebar-label mt-3">Scan Tools</div>
        <a href="<?= SITE_URL ?>/user/url-scanner.php" class="sidebar-link <?= $current==='url-scanner.php'?'active':'' ?>">
            <i class="bi bi-link-45deg"></i> URL Scanner
        </a>
        <a href="<?= SITE_URL ?>/user/email-scanner.php" class="sidebar-link <?= $current==='email-scanner.php'?'active':'' ?>">
            <i class="bi bi-envelope-exclamation"></i> Email Scanner
        </a>
        <a href="<?= SITE_URL ?>/chatbot.php" class="sidebar-link <?= $current==='chatbot.php'?'active':'' ?>">
            <i class="bi bi-robot"></i> AI Chatbot
        </a>
        <div class="sidebar-label mt-3">Reports</div>
        <a href="<?= SITE_URL ?>/user/history.php" class="sidebar-link <?= $current==='history.php'?'active':'' ?>">
            <i class="bi bi-clock-history"></i> Scan History
        </a>
        <a href="<?= SITE_URL ?>/user/reports.php" class="sidebar-link <?= $current==='reports.php'?'active':'' ?>">
            <i class="bi bi-file-earmark-bar-graph"></i> Reports
        </a>
        <div class="sidebar-label mt-3">Account</div>
        <a href="<?= SITE_URL ?>/user/feedback.php" class="sidebar-link <?= $current==='feedback.php'?'active':'' ?>">
            <i class="bi bi-chat-dots"></i> Feedback
        </a>
        <a href="<?= SITE_URL ?>/user/profile.php" class="sidebar-link <?= $current==='profile.php'?'active':'' ?>">
            <i class="bi bi-person-circle"></i> Profile
        </a>
        <a href="<?= SITE_URL ?>/user/settings.php" class="sidebar-link <?= $current==='settings.php'?'active':'' ?>">
            <i class="bi bi-gear"></i> Settings
        </a>
    </nav>

    <div class="sidebar-footer px-3 py-3">
        <a href="<?= SITE_URL ?>/logout.php" class="sidebar-link text-danger">
            <i class="bi bi-box-arrow-right"></i> Logout
        </a>
    </div>
</aside>
