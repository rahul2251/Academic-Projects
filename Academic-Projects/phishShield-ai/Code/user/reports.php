<?php
require_once '../config/config.php';
require_once '../config/db.php';
require_once '../config/auth.php';
require_once '../includes/functions.php';
require_user_login();
$uid = $_SESSION['user_id'];

$url_total   = $conn->query("SELECT COUNT(*) c FROM url_scans WHERE user_id=$uid")->fetch_assoc()['c'];
$email_total = $conn->query("SELECT COUNT(*) c FROM email_scans WHERE user_id=$uid")->fetch_assoc()['c'];
$phishing    = $conn->query("SELECT COUNT(*) c FROM url_scans WHERE user_id=$uid AND result='phishing'")->fetch_assoc()['c'];
$safe        = $conn->query("SELECT COUNT(*) c FROM url_scans WHERE user_id=$uid AND result='safe'")->fetch_assoc()['c'];

$page_title = 'Reports';
$extra_css = '<link rel="stylesheet" href="' . SITE_URL . '/assets/css/dashboard.css">';
?>
<?php include '../includes/header.php'; ?>
<div class="ps-layout">
<?php include '../includes/sidebar.php'; ?>
<div class="ps-main">
    <div class="ps-topbar"><div class="fw-semibold text-white">Reports</div></div>
    <div class="ps-content">
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="ps-card mb-4">
                    <h5 class="fw-bold mb-3"><i class="bi bi-file-earmark-bar-graph text-accent me-2"></i>Your Scan Summary</h5>
                    <div class="row g-3 mb-4">
                        <div class="col-6 col-md-3"><div class="text-center"><div class="fw-bold fs-3 text-white"><?= $url_total ?></div><div class="text-muted small">URL Scans</div></div></div>
                        <div class="col-6 col-md-3"><div class="text-center"><div class="fw-bold fs-3 text-white"><?= $email_total ?></div><div class="text-muted small">Email Scans</div></div></div>
                        <div class="col-6 col-md-3"><div class="text-center"><div class="fw-bold fs-3 text-accent"><?= $phishing ?></div><div class="text-muted small">Phishing Found</div></div></div>
                        <div class="col-6 col-md-3"><div class="text-center"><div class="fw-bold fs-3 text-cyan"><?= $safe ?></div><div class="text-muted small">Safe URLs</div></div></div>
                    </div>
                    <hr class="divider">
                    <h6 class="fw-bold mb-3">Recent Activity</h6>
                    <?php
                    $recent = $conn->query("SELECT 'url' AS type, url AS target, result, risk_score, scanned_at FROM url_scans WHERE user_id=$uid
                        UNION ALL SELECT 'email', CONCAT('Email: ', subject), result, risk_score, scanned_at FROM email_scans WHERE user_id=$uid
                        ORDER BY scanned_at DESC LIMIT 10")->fetch_all(MYSQLI_ASSOC);
                    ?>
                    <div class="data-table-wrapper">
                    <table class="table table-sm">
                        <thead><tr><th>Type</th><th>Target</th><th>Result</th><th>Score</th><th>Date</th></tr></thead>
                        <tbody>
                        <?php foreach ($recent as $r): ?>
                        <tr>
                            <td><span class="badge" style="background:var(--ps-dark3);color:var(--ps-muted);"><?= strtoupper($r['type']) ?></span></td>
                            <td class="text-white small text-truncate" style="max-width:200px;"><?= h($r['target']) ?></td>
                            <td><?= result_badge($r['result']) ?></td>
                            <td class="fw-bold <?= $r['risk_score']>=80?'text-accent':'' ?>"><?= $r['risk_score'] ?></td>
                            <td class="text-muted small"><?= format_date($r['scanned_at']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="ps-card mb-3">
                    <h6 class="fw-bold mb-3"><i class="bi bi-download text-accent me-2"></i>Export Report</h6>
                    <p class="text-muted small">Download your scan history as a printable report.</p>
                    <a href="<?= SITE_URL ?>/api/stats.php?export=pdf&uid=<?= $uid ?>" class="btn btn-cyber w-100 mb-2">
                        <i class="bi bi-file-pdf me-2"></i>Download PDF Report
                    </a>
                    <a href="<?= SITE_URL ?>/api/stats.php?export=csv&uid=<?= $uid ?>" class="btn btn-outline-accent w-100">
                        <i class="bi bi-file-csv me-2"></i>Export CSV
                    </a>
                    <p class="text-muted mt-2 mb-0" style="font-size:.75rem;">Reports contain all scan results, risk scores, and AI analysis.</p>
                </div>
                <div class="ps-card">
                    <h6 class="fw-bold mb-3">Threat Distribution</h6>
                    <div class="d-flex flex-column gap-2">
                        <div>
                            <div class="d-flex justify-content-between small mb-1"><span class="text-muted">Phishing</span><span class="text-accent fw-bold"><?= $url_total > 0 ? round($phishing/$url_total*100) : 0 ?>%</span></div>
                            <div class="risk-bar"><div class="risk-fill phishing" data-width="<?= $url_total > 0 ? round($phishing/$url_total*100) : 0 ?>"></div></div>
                        </div>
                        <div>
                            <div class="d-flex justify-content-between small mb-1"><span class="text-muted">Safe</span><span class="text-cyan fw-bold"><?= $url_total > 0 ? round($safe/$url_total*100) : 0 ?>%</span></div>
                            <div class="risk-bar"><div class="risk-fill safe" data-width="<?= $url_total > 0 ? round($safe/$url_total*100) : 0 ?>"></div></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
<script src="<?= SITE_URL ?>/assets/js/scanner.js"></script>
<?php include '../includes/footer.php'; ?>
