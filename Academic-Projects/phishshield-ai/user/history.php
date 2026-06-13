<?php
require_once '../config/config.php';
require_once '../config/db.php';
require_once '../config/auth.php';
require_once '../includes/functions.php';
require_user_login();
$uid = $_SESSION['user_id'];

$tab = $_GET['tab'] ?? 'url';
$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = 15;
$offset = ($page - 1) * $per_page;

if ($tab === 'email') {
    $total = $conn->query("SELECT COUNT(*) c FROM email_scans WHERE user_id=$uid")->fetch_assoc()['c'];
    $scans = $conn->query("SELECT * FROM email_scans WHERE user_id=$uid ORDER BY scanned_at DESC LIMIT $per_page OFFSET $offset")->fetch_all(MYSQLI_ASSOC);
} else {
    $total = $conn->query("SELECT COUNT(*) c FROM url_scans WHERE user_id=$uid")->fetch_assoc()['c'];
    $scans = $conn->query("SELECT * FROM url_scans WHERE user_id=$uid ORDER BY scanned_at DESC LIMIT $per_page OFFSET $offset")->fetch_all(MYSQLI_ASSOC);
}
$pages = ceil($total / $per_page);
$page_title = 'Scan History';
$extra_css = '<link rel="stylesheet" href="' . SITE_URL . '/assets/css/dashboard.css">';
?>
<?php include '../includes/header.php'; ?>
<div class="ps-layout">
<?php include '../includes/sidebar.php'; ?>
<div class="ps-main">
    <div class="ps-topbar">
        <div class="fw-semibold text-white">Scan History</div>
    </div>
    <div class="ps-content">
        <div class="ps-card">
            <ul class="nav nav-tabs mb-4" style="border-color:var(--ps-border);">
                <li class="nav-item"><a class="nav-link <?= $tab==='url'?'active':'' ?>" style="<?= $tab==='url'?'border-color:var(--ps-accent);color:var(--ps-accent);background:rgba(233,69,96,.1);':'color:var(--ps-muted);border-color:transparent;' ?>" href="?tab=url"><i class="bi bi-link-45deg me-1"></i>URL Scans (<?= $tab==='url'?$total:$conn->query("SELECT COUNT(*) c FROM url_scans WHERE user_id=$uid")->fetch_assoc()['c'] ?>)</a></li>
                <li class="nav-item"><a class="nav-link <?= $tab==='email'?'active':'' ?>" style="<?= $tab==='email'?'border-color:var(--ps-accent);color:var(--ps-accent);background:rgba(233,69,96,.1);':'color:var(--ps-muted);border-color:transparent;' ?>" href="?tab=email"><i class="bi bi-envelope me-1"></i>Email Scans (<?= $tab==='email'?$total:$conn->query("SELECT COUNT(*) c FROM email_scans WHERE user_id=$uid")->fetch_assoc()['c'] ?>)</a></li>
            </ul>

            <?php if (empty($scans)): ?>
            <div class="text-center py-5 text-muted">
                <i class="bi bi-inbox fs-1 d-block mb-3 opacity-50"></i>
                No <?= $tab ?> scans yet. <a href="<?= SITE_URL ?>/user/<?= $tab ?>-scanner.php" class="text-accent">Start scanning →</a>
            </div>
            <?php else: ?>
            <div class="data-table-wrapper">
                <table class="table">
                    <thead>
                        <tr>
                            <?php if ($tab==='url'): ?>
                            <th>URL</th>
                            <?php else: ?>
                            <th>Subject</th><th>Sender</th>
                            <?php endif; ?>
                            <th>Result</th>
                            <th>Risk Score</th>
                            <th>Scanned At</th>
                            <th>Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($scans as $s): ?>
                        <tr>
                            <?php if ($tab==='url'): ?>
                            <td class="text-break" style="max-width:280px;"><span class="text-white small"><?= h(substr($s['url'],0,60)) ?><?= strlen($s['url'])>60?'…':'' ?></span></td>
                            <?php else: ?>
                            <td class="text-white small"><?= h($s['subject'] ?: '—') ?></td>
                            <td class="text-muted small"><?= h($s['sender']) ?></td>
                            <?php endif; ?>
                            <td><?= result_badge($s['result']) ?></td>
                            <td>
                                <span class="fw-bold <?= $s['risk_score']>=80?'text-accent':($s['risk_score']>=50?'':'text-cyan') ?>"><?= $s['risk_score'] ?></span>/100
                            </td>
                            <td class="text-muted small"><?= format_date($s['scanned_at']) ?></td>
                            <td>
                                <button class="btn btn-sm" style="background:var(--ps-dark3);border:1px solid var(--ps-border);color:var(--ps-muted);font-size:.75rem;"
                                    data-bs-toggle="modal" data-bs-target="#detailModal"
                                    data-details="<?= h($s['details']) ?>"
                                    data-ai="<?= h($s['ai_analysis'] ?? '') ?>">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <!-- Pagination -->
            <?php if ($pages > 1): ?>
            <nav class="mt-3"><ul class="pagination pagination-sm justify-content-center">
                <?php for ($i=1;$i<=$pages;$i++): ?>
                <li class="page-item <?= $i==$page?'active':'' ?>">
                    <a class="page-link" href="?tab=<?= $tab ?>&page=<?= $i ?>" style="background:<?= $i==$page?'var(--ps-accent)':'var(--ps-dark3)' ?>;border-color:var(--ps-border);color:#fff;"><?= $i ?></a>
                </li>
                <?php endfor; ?>
            </ul></nav>
            <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
</div>

<!-- Detail Modal -->
<div class="modal fade" id="detailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="background:var(--ps-card);border:1px solid var(--ps-border);">
            <div class="modal-header" style="border-color:var(--ps-border);">
                <h5 class="modal-title text-white"><i class="bi bi-info-circle text-accent me-2"></i>Scan Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3"><div class="fw-semibold text-white mb-1">Detection Details</div><p id="modalDetails" class="text-muted small"></p></div>
                <div><div class="fw-semibold text-white mb-1"><i class="bi bi-robot text-accent me-1"></i>AI Analysis</div><p id="modalAI" class="text-muted small"></p></div>
            </div>
        </div>
    </div>
</div>
<script>
document.getElementById('detailModal').addEventListener('show.bs.modal', function(e) {
    document.getElementById('modalDetails').textContent = e.relatedTarget.dataset.details;
    document.getElementById('modalAI').textContent = e.relatedTarget.dataset.ai || 'No AI analysis recorded.';
});
</script>
<?php include '../includes/footer.php'; ?>
