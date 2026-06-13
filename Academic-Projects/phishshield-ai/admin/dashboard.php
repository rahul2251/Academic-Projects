<?php
require_once '../config/config.php';
require_once '../config/db.php';
require_once '../config/auth.php';
require_once '../includes/functions.php';
require_admin_login();

$total_users  = $conn->query("SELECT COUNT(*) c FROM users")->fetch_assoc()['c'];
$total_url    = $conn->query("SELECT COUNT(*) c FROM url_scans")->fetch_assoc()['c'];
$total_email  = $conn->query("SELECT COUNT(*) c FROM email_scans")->fetch_assoc()['c'];
$total_phish  = $conn->query("SELECT COUNT(*) c FROM url_scans WHERE result='phishing'")->fetch_assoc()['c'];
$total_phish += $conn->query("SELECT COUNT(*) c FROM email_scans WHERE result='phishing'")->fetch_assoc()['c'];
$blacklist_count = $conn->query("SELECT COUNT(*) c FROM blacklist")->fetch_assoc()['c'];
$feedback_pending= $conn->query("SELECT COUNT(*) c FROM feedback WHERE status='pending'")->fetch_assoc()['c'];

// Recent scans
$recent = $conn->query("SELECT u.username, us.url, us.result, us.scanned_at FROM url_scans us JOIN users u ON u.id=us.user_id ORDER BY us.scanned_at DESC LIMIT 8")->fetch_all(MYSQLI_ASSOC);

// Last 7 days
$days=[]; $scan_counts=[];
for($i=6;$i>=0;$i--) {
    $d=date('Y-m-d',strtotime("-$i days"));
    $days[]=date('D',strtotime($d));
    $scan_counts[]=$conn->query("SELECT COUNT(*) c FROM url_scans WHERE DATE(scanned_at)='$d'")->fetch_assoc()['c'];
}

$page_title = 'Admin Dashboard';
$extra_css = '<link rel="stylesheet" href="' . SITE_URL . '/assets/css/dashboard.css">
<link rel="stylesheet" href="' . SITE_URL . '/assets/css/admin.css">';
$extra_js = '<script src="' . SITE_URL . '/assets/js/charts.js"></script>';
?>
<?php include '../includes/header.php'; ?>
<div class="ps-layout">
<?php include 'includes/admin_sidebar.php'; ?>
<div class="ps-main">
    <div class="ps-topbar admin-topbar">
        <div class="fw-semibold text-white">Admin Dashboard</div>
        <div class="text-muted small">Logged in as: <?= h($_SESSION['admin_name']) ?></div>
    </div>
    <div class="ps-content">
        <!-- Stats -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-xl-2">
                <div class="ps-card stat-card"><div class="stat-icon info mx-auto"><i class="bi bi-people"></i></div><div class="stat-value"><?= $total_users ?></div><div class="stat-label">Users</div></div>
            </div>
            <div class="col-6 col-xl-2">
                <div class="ps-card stat-card"><div class="stat-icon info mx-auto"><i class="bi bi-link-45deg"></i></div><div class="stat-value"><?= $total_url ?></div><div class="stat-label">URL Scans</div></div>
            </div>
            <div class="col-6 col-xl-2">
                <div class="ps-card stat-card"><div class="stat-icon info mx-auto"><i class="bi bi-envelope"></i></div><div class="stat-value"><?= $total_email ?></div><div class="stat-label">Email Scans</div></div>
            </div>
            <div class="col-6 col-xl-2">
                <div class="ps-card stat-card"><div class="stat-icon danger mx-auto"><i class="bi bi-shield-x"></i></div><div class="stat-value"><?= $total_phish ?></div><div class="stat-label">Phishing</div></div>
            </div>
            <div class="col-6 col-xl-2">
                <div class="ps-card stat-card"><div class="stat-icon warning mx-auto"><i class="bi bi-slash-circle"></i></div><div class="stat-value"><?= $blacklist_count ?></div><div class="stat-label">Blacklist</div></div>
            </div>
            <div class="col-6 col-xl-2">
                <div class="ps-card stat-card"><div class="stat-icon warning mx-auto"><i class="bi bi-chat-dots"></i></div><div class="stat-value"><?= $feedback_pending ?></div><div class="stat-label">Pending Feedback</div></div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-lg-8">
                <div class="ps-card h-100">
                    <h6 class="fw-bold mb-3">URL Scans — Last 7 Days</h6>
                    <div class="chart-container">
                        <canvas id="adminChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="ps-card h-100">
                    <h6 class="fw-bold mb-3">Quick Actions</h6>
                    <div class="d-flex flex-column gap-2">
                        <a href="<?= SITE_URL ?>/admin/users.php" class="btn btn-outline-accent btn-sm text-start"><i class="bi bi-person-plus me-2"></i>Manage Users</a>
                        <a href="<?= SITE_URL ?>/admin/blacklist.php" class="btn btn-outline-accent btn-sm text-start"><i class="bi bi-plus-circle me-2"></i>Add to Blacklist</a>
                        <a href="<?= SITE_URL ?>/admin/feedback.php" class="btn btn-outline-accent btn-sm text-start"><i class="bi bi-chat-dots me-2"></i>Review Feedback <?= $feedback_pending>0?"<span class='badge bg-danger ms-1'>$feedback_pending</span>":'' ?></a>
                        <a href="<?= SITE_URL ?>/admin/logs.php" class="btn btn-outline-accent btn-sm text-start"><i class="bi bi-journal-text me-2"></i>Activity Logs</a>
                        <a href="<?= SITE_URL ?>/admin/analytics.php" class="btn btn-outline-accent btn-sm text-start"><i class="bi bi-bar-chart me-2"></i>Analytics</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="ps-card">
            <h6 class="fw-bold mb-3">Recent URL Scans</h6>
            <div class="data-table-wrapper">
            <table class="table table-sm">
                <thead><tr><th>User</th><th>URL</th><th>Result</th><th>Date</th></tr></thead>
                <tbody>
                <?php foreach ($recent as $r): ?>
                <tr>
                    <td class="text-muted small"><?= h($r['username']) ?></td>
                    <td class="text-white small text-truncate" style="max-width:280px;"><?= h($r['url']) ?></td>
                    <td><?= result_badge($r['result']) ?></td>
                    <td class="text-muted small"><?= format_date($r['scanned_at']) ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            </div>
        </div>
    </div>
</div>
</div>
<script>
document.addEventListener('DOMContentLoaded',function(){
    PSCharts.bar('adminChart', <?= json_encode($days) ?>,
        [{ label:'URL Scans', data: <?= json_encode($scan_counts) ?>, backgroundColor:'rgba(233,69,96,.7)', borderColor:'#e94560', borderWidth:1 }]
    );
});
</script>
<?php include '../includes/footer.php'; ?>
