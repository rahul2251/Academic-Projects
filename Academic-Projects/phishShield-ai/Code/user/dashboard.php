<?php
require_once '../config/config.php';
require_once '../config/db.php';
require_once '../config/auth.php';
require_once '../includes/functions.php';
require_user_login();

$uid = $_SESSION['user_id'];

// Stats
$url_total   = $conn->query("SELECT COUNT(*) c FROM url_scans WHERE user_id=$uid")->fetch_assoc()['c'];
$email_total = $conn->query("SELECT COUNT(*) c FROM email_scans WHERE user_id=$uid")->fetch_assoc()['c'];
$phish_found = $conn->query("SELECT COUNT(*) c FROM url_scans WHERE user_id=$uid AND result='phishing'")->fetch_assoc()['c'];
$phish_found+= $conn->query("SELECT COUNT(*) c FROM email_scans WHERE user_id=$uid AND result='phishing'")->fetch_assoc()['c'];

/* Recent scans
$recent_url   = $conn->query("SELECT url, result, risk_score, scanned_at FROM url_scans WHERE user_id=$uid ORDER BY scanned_at DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC);
$recent_email = $conn->query("SELECT subject, result, risk_score, scanned_at FROM email_scans WHERE user_id=$uid ORDER BY scanned_at DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC);
*/

// Replace your existing lines 16-17 with this:
$res_url = $conn->query("SELECT url, result, risk_score, scanned_at FROM url_scans WHERE user_id=$uid ORDER BY scanned_at DESC LIMIT 5");
$recent_url = [];
if ($res_url) {
    while($row = $res_url->fetch_assoc()) {
        $recent_url[] = $row;
    }
}

$res_email = $conn->query("SELECT subject, result, risk_score, scanned_at FROM email_scans WHERE user_id=$uid ORDER BY scanned_at DESC LIMIT 5");
$recent_email = [];
if ($res_email) {
    while($row = $res_email->fetch_assoc()) {
        $recent_email[] = $row;
    }
}

// Chart data — scans per day (last 7 days)
$days = []; $url_counts = []; $email_counts = [];
for ($i = 6; $i >= 0; $i--) {
    $d = date('Y-m-d', strtotime("-$i days"));
    $days[] = date('D', strtotime($d));
    $url_counts[]   = $conn->query("SELECT COUNT(*) c FROM url_scans WHERE user_id=$uid AND DATE(scanned_at)='$d'")->fetch_assoc()['c'];
    $email_counts[] = $conn->query("SELECT COUNT(*) c FROM email_scans WHERE user_id=$uid AND DATE(scanned_at)='$d'")->fetch_assoc()['c'];
}

// Result breakdown
$url_safe    = $conn->query("SELECT COUNT(*) c FROM url_scans WHERE user_id=$uid AND result='safe'")->fetch_assoc()['c'];
$url_susp    = $conn->query("SELECT COUNT(*) c FROM url_scans WHERE user_id=$uid AND result='suspicious'")->fetch_assoc()['c'];
$url_phish   = $conn->query("SELECT COUNT(*) c FROM url_scans WHERE user_id=$uid AND result='phishing'")->fetch_assoc()['c'];

$page_title = 'Dashboard';
$extra_css = '<link rel="stylesheet" href="' . SITE_URL . '/assets/css/dashboard.css">';
$extra_js = '<script src="' . SITE_URL . '/assets/js/charts.js"></script>';
?>
<?php include '../includes/header.php'; ?>
<div class="ps-layout">
<?php include '../includes/sidebar.php'; ?>
<div class="ps-main">
    <div class="ps-topbar">
        <div>
            <div class="fw-semibold text-white">Dashboard</div>
            <div class="text-muted small">Welcome back, <?= h($_SESSION['user_name']) ?>!</div>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= SITE_URL ?>/user/url-scanner.php" class="btn btn-accent btn-sm"><i class="bi bi-link-45deg me-1"></i>Scan URL</a>
            <a href="<?= SITE_URL ?>/user/email-scanner.php" class="btn btn-outline-accent btn-sm"><i class="bi bi-envelope-exclamation me-1"></i>Scan Email</a>
        </div>
    </div>

    <div class="ps-content">
        <?php include '../includes/alerts.php'; ?>

        <!-- Stats Row -->
        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-xl-3">
                <div class="ps-card stat-card">
                    <div class="stat-icon info mx-auto"><i class="bi bi-link-45deg"></i></div>
                    <div class="stat-value" data-target="<?= $url_total ?>"><?= $url_total ?></div>
                    <div class="stat-label">URL Scans</div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="ps-card stat-card">
                    <div class="stat-icon info mx-auto"><i class="bi bi-envelope"></i></div>
                    <div class="stat-value" data-target="<?= $email_total ?>"><?= $email_total ?></div>
                    <div class="stat-label">Email Scans</div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="ps-card stat-card">
                    <div class="stat-icon danger mx-auto"><i class="bi bi-shield-x"></i></div>
                    <div class="stat-value" data-target="<?= $phish_found ?>"><?= $phish_found ?></div>
                    <div class="stat-label">Phishing Detected</div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="ps-card stat-card">
                    <div class="stat-icon safe mx-auto"><i class="bi bi-shield-check"></i></div>
                    <div class="stat-value" data-target="<?= $url_safe ?>"><?= $url_safe ?></div>
                    <div class="stat-label">Safe URLs</div>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="row g-3 mb-4">
            <div class="col-lg-8">
                <div class="ps-card h-100">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold mb-0">Scan Activity (Last 7 Days)</h6>
                    </div>
                    <div class="chart-container">
                        <canvas id="activityChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="ps-card h-100">
                    <h6 class="fw-bold mb-3">URL Results Breakdown</h6>
                    <div class="chart-container" style="height:200px;">
                        <canvas id="breakdownChart"></canvas>
                    </div>
                    <div class="mt-3 d-flex flex-column gap-2">
                        <div class="d-flex justify-content-between small"><span class="text-muted">Safe</span><span class="fw-bold text-cyan"><?= $url_safe ?></span></div>
                        <div class="d-flex justify-content-between small"><span class="text-muted">Suspicious</span><span class="fw-bold" style="color:var(--ps-yellow)"><?= $url_susp ?></span></div>
                        <div class="d-flex justify-content-between small"><span class="text-muted">Phishing</span><span class="fw-bold text-accent"><?= $url_phish ?></span></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Scans -->
        <div class="row g-3">
            <div class="col-lg-6">
                <div class="ps-card">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold mb-0">Recent URL Scans</h6>
                        <a href="<?= SITE_URL ?>/user/history.php" class="btn btn-sm btn-outline-accent">View All</a>
                    </div>
                    <?php if (empty($recent_url)): ?>
                        <p class="text-muted small text-center py-3">No scans yet. <a href="<?= SITE_URL ?>/user/url-scanner.php" class="text-accent">Scan a URL</a></p>
                    <?php else: ?>
                    <div class="scan-timeline">
                        <?php foreach ($recent_url as $s): ?>
                        <div class="timeline-item <?= $s['result'] ?>">
                            <div class="text-white small fw-semibold text-truncate" style="max-width:280px;"><?= h($s['url']) ?></div>
                            <div class="d-flex align-items-center gap-2 mt-1">
                                <?= result_badge($s['result']) ?>
                                <span class="text-muted" style="font-size:.75rem;"><?= format_date($s['scanned_at']) ?></span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="ps-card">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold mb-0">Recent Email Scans</h6>
                        <a href="<?= SITE_URL ?>/user/history.php" class="btn btn-sm btn-outline-accent">View All</a>
                    </div>
                    <?php if (empty($recent_email)): ?>
                        <p class="text-muted small text-center py-3">No scans yet. <a href="<?= SITE_URL ?>/user/email-scanner.php" class="text-accent">Scan an Email</a></p>
                    <?php else: ?>
                    <div class="scan-timeline">
                        <?php foreach ($recent_email as $s): ?>
                        <div class="timeline-item <?= $s['result'] ?>">
                            <div class="text-white small fw-semibold"><?= h($s['subject'] ?: '(No subject)') ?></div>
                            <div class="d-flex align-items-center gap-2 mt-1">
                                <?= result_badge($s['result']) ?>
                                <span class="text-muted" style="font-size:.75rem;"><?= format_date($s['scanned_at']) ?></span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    PSCharts.line('activityChart',
        <?= json_encode($days) ?>,
        [
            { label: 'URL Scans', data: <?= json_encode($url_counts) ?>, borderColor: '#e94560', backgroundColor: 'rgba(233,69,96,.1)', fill: true },
            { label: 'Email Scans', data: <?= json_encode($email_counts) ?>, borderColor: '#16c79a', backgroundColor: 'rgba(22,199,154,.1)', fill: true }
        ]
    );
    PSCharts.doughnut('breakdownChart',
        ['Safe', 'Suspicious', 'Phishing'],
        [<?= $url_safe ?>, <?= $url_susp ?>, <?= $url_phish ?>],
        ['#16c79a', '#f5a623', '#e94560']
    );
});
</script>
<?php include '../includes/footer.php'; ?>
