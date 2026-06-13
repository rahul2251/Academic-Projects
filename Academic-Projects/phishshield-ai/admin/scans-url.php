<?php
require_once '../config/config.php';
require_once '../config/db.php';
require_once '../config/auth.php';
require_once '../includes/functions.php';
require_admin_login();

$filter = $_GET['filter'] ?? 'all';
$where = $filter !== 'all' ? "WHERE us.result='$filter'" : '';
$scans = $conn->query("SELECT us.*, u.username FROM url_scans us JOIN users u ON u.id=us.user_id $where ORDER BY us.scanned_at DESC LIMIT 100")->fetch_all(MYSQLI_ASSOC);

$page_title = 'URL Scans';
$extra_css = '<link rel="stylesheet" href="' . SITE_URL . '/assets/css/dashboard.css"><link rel="stylesheet" href="' . SITE_URL . '/assets/css/admin.css">';
?>
<?php include '../includes/header.php'; ?>
<div class="ps-layout">
<?php include 'includes/admin_sidebar.php'; ?>
<div class="ps-main">
    <div class="ps-topbar admin-topbar"><div class="fw-semibold text-white">URL Scans</div></div>
    <div class="ps-content">
        <div class="ps-card">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <h6 class="fw-bold mb-0">All URL Scans (<?= count($scans) ?>)</h6>
                <div class="d-flex gap-2">
                    <?php foreach (['all','safe','suspicious','phishing'] as $f): ?>
                    <a href="?filter=<?= $f ?>" class="btn btn-sm <?= $filter===$f?'btn-accent':'btn-outline-accent' ?>"><?= ucfirst($f) ?></a>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="data-table-wrapper">
            <table class="table">
                <thead><tr><th>User</th><th>URL</th><th>Result</th><th>Risk</th><th>Date</th><th>Details</th></tr></thead>
                <tbody>
                <?php foreach ($scans as $s): ?>
                <tr>
                    <td class="text-muted small"><?= h($s['username']) ?></td>
                    <td class="text-white small text-truncate" style="max-width:260px;"><?= h($s['url']) ?></td>
                    <td><?= result_badge($s['result']) ?></td>
                    <td class="fw-bold <?= $s['risk_score']>=80?'text-accent':($s['risk_score']>=50?'':'text-cyan') ?>"><?= $s['risk_score'] ?></td>
                    <td class="text-muted small"><?= format_date($s['scanned_at']) ?></td>
                    <td>
                        <button class="btn btn-sm" style="background:var(--ps-dark3);border:1px solid var(--ps-border);color:var(--ps-muted);"
                            data-bs-toggle="modal" data-bs-target="#detailModal"
                            data-details="<?= h($s['details']) ?>" data-ai="<?= h($s['ai_analysis']??'') ?>">
                            <i class="bi bi-eye"></i>
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            </div>
        </div>
    </div>
</div>
</div>
<div class="modal fade" id="detailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="background:var(--ps-card);border:1px solid var(--ps-border);">
            <div class="modal-header" style="border-color:var(--ps-border);"><h5 class="modal-title text-white">Scan Details</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3"><strong class="text-white">Detection:</strong><p id="modalDetails" class="text-muted small mt-1"></p></div>
                <div><strong class="text-white"><i class="bi bi-robot text-accent me-1"></i>AI Analysis:</strong><p id="modalAI" class="text-muted small mt-1"></p></div>
            </div>
        </div>
    </div>
</div>
<script>document.getElementById('detailModal').addEventListener('show.bs.modal',function(e){document.getElementById('modalDetails').textContent=e.relatedTarget.dataset.details;document.getElementById('modalAI').textContent=e.relatedTarget.dataset.ai||'N/A';});</script>
<?php include '../includes/footer.php'; ?>
