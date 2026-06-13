<?php
require_once '../config/config.php';
require_once '../config/db.php';
require_once '../config/auth.php';
require_once '../includes/functions.php';
require_admin_login();

$filter = $_GET['filter'] ?? 'all';
$where = $filter !== 'all' ? "WHERE es.result='$filter'" : '';
$scans = $conn->query("SELECT es.*, u.username FROM email_scans es JOIN users u ON u.id=es.user_id $where ORDER BY es.scanned_at DESC LIMIT 100")->fetch_all(MYSQLI_ASSOC);

$page_title = 'Email Scans';
$extra_css = '<link rel="stylesheet" href="' . SITE_URL . '/assets/css/dashboard.css"><link rel="stylesheet" href="' . SITE_URL . '/assets/css/admin.css">';
?>
<?php include '../includes/header.php'; ?>
<div class="ps-layout">
<?php include 'includes/admin_sidebar.php'; ?>
<div class="ps-main">
    <div class="ps-topbar admin-topbar"><div class="fw-semibold text-white">Email Scans</div></div>
    <div class="ps-content">
        <div class="ps-card">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <h6 class="fw-bold mb-0">All Email Scans (<?= count($scans) ?>)</h6>
                <div class="d-flex gap-2">
                    <?php foreach (['all','safe','suspicious','phishing'] as $f): ?>
                    <a href="?filter=<?= $f ?>" class="btn btn-sm <?= $filter===$f?'btn-accent':'btn-outline-accent' ?>"><?= ucfirst($f) ?></a>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="data-table-wrapper">
            <table class="table">
                <thead><tr><th>User</th><th>Subject</th><th>Sender</th><th>Result</th><th>Risk</th><th>Date</th></tr></thead>
                <tbody>
                <?php foreach ($scans as $s): ?>
                <tr>
                    <td class="text-muted small"><?= h($s['username']) ?></td>
                    <td class="text-white small"><?= h($s['subject']?:'—') ?></td>
                    <td class="text-muted small"><?= h($s['sender']) ?></td>
                    <td><?= result_badge($s['result']) ?></td>
                    <td class="fw-bold <?= $s['risk_score']>=80?'text-accent':'' ?>"><?= $s['risk_score'] ?></td>
                    <td class="text-muted small"><?= format_date($s['scanned_at']) ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            </div>
        </div>
    </div>
</div>
</div>
<?php include '../includes/footer.php'; ?>
