<?php
require_once '../config/config.php';
require_once '../config/db.php';
require_once '../config/auth.php';
require_once '../includes/functions.php';
require_admin_login();

// Monthly scan data
$months=[]; $url_m=[]; $email_m=[];
for($i=5;$i>=0;$i--) {
    $m = date('Y-m', strtotime("-$i months"));
    $months[] = date('M Y', strtotime("-$i months"));
    $url_m[]   = $conn->query("SELECT COUNT(*) c FROM url_scans WHERE DATE_FORMAT(scanned_at,'%Y-%m')='$m'")->fetch_assoc()['c'];
    $email_m[] = $conn->query("SELECT COUNT(*) c FROM email_scans WHERE DATE_FORMAT(scanned_at,'%Y-%m')='$m'")->fetch_assoc()['c'];
}

// Result breakdown
$url_safe  = $conn->query("SELECT COUNT(*) c FROM url_scans WHERE result='safe'")->fetch_assoc()['c'];
$url_susp  = $conn->query("SELECT COUNT(*) c FROM url_scans WHERE result='suspicious'")->fetch_assoc()['c'];
$url_phish = $conn->query("SELECT COUNT(*) c FROM url_scans WHERE result='phishing'")->fetch_assoc()['c'];

// Top phishing domains
$top_phish = $conn->query("SELECT url, COUNT(*) cnt FROM url_scans WHERE result='phishing' GROUP BY url ORDER BY cnt DESC LIMIT 5")->fetch_all(MYSQLI_ASSOC);

$page_title = 'Analytics';
$extra_css = '<link rel="stylesheet" href="' . SITE_URL . '/assets/css/dashboard.css"><link rel="stylesheet" href="' . SITE_URL . '/assets/css/admin.css">';
$extra_js = '<script src="' . SITE_URL . '/assets/js/charts.js"></script>';
?>
<?php include '../includes/header.php'; ?>
<div class="ps-layout">
<?php include 'includes/admin_sidebar.php'; ?>
<div class="ps-main">
    <div class="ps-topbar admin-topbar"><div class="fw-semibold text-white">Analytics</div></div>
    <div class="ps-content">
        <div class="row g-4 mb-4">
            <div class="col-lg-8">
                <div class="ps-card h-100">
                    <h6 class="fw-bold mb-3">Scans Per Month (Last 6 Months)</h6>
                    <div class="chart-container"><canvas id="monthlyChart"></canvas></div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="ps-card h-100">
                    <h6 class="fw-bold mb-3">URL Result Distribution</h6>
                    <div class="chart-container" style="height:220px;"><canvas id="distChart"></canvas></div>
                </div>
            </div>
        </div>
        <div class="ps-card">
            <h6 class="fw-bold mb-3">Top Phishing Targets</h6>
            <?php if (empty($top_phish)): ?>
            <p class="text-muted small">No phishing scans yet.</p>
            <?php else: ?>
            <div class="data-table-wrapper">
            <table class="table table-sm">
                <thead><tr><th>URL</th><th>Times Scanned</th></tr></thead>
                <tbody>
                <?php foreach ($top_phish as $t): ?>
                <tr><td class="text-accent small text-truncate" style="max-width:400px;"><?= h($t['url']) ?></td><td class="fw-bold text-white"><?= $t['cnt'] ?></td></tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
</div>
<script>
document.addEventListener('DOMContentLoaded',function(){
    PSCharts.line('monthlyChart',<?= json_encode($months) ?>,[
        {label:'URL Scans',data:<?= json_encode($url_m) ?>,borderColor:'#e94560',backgroundColor:'rgba(233,69,96,.1)',fill:true},
        {label:'Email Scans',data:<?= json_encode($email_m) ?>,borderColor:'#16c79a',backgroundColor:'rgba(22,199,154,.1)',fill:true}
    ]);
    PSCharts.doughnut('distChart',['Safe','Suspicious','Phishing'],[<?= $url_safe ?>,<?= $url_susp ?>,<?= $url_phish ?>],['#16c79a','#f5a623','#e94560']);
});
</script>
<?php include '../includes/footer.php'; ?>
