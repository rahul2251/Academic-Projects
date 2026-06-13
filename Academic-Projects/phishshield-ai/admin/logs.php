<?php
require_once '../config/config.php';
require_once '../config/db.php';
require_once '../config/auth.php';
require_once '../includes/functions.php';
require_admin_login();

$logs = $conn->query("SELECT al.*, u.username FROM activity_logs al LEFT JOIN users u ON u.id=al.user_id ORDER BY al.created_at DESC LIMIT 200")->fetch_all(MYSQLI_ASSOC);
$page_title = 'Activity Logs';
$extra_css = '<link rel="stylesheet" href="' . SITE_URL . '/assets/css/dashboard.css"><link rel="stylesheet" href="' . SITE_URL . '/assets/css/admin.css">';
?>
<?php include '../includes/header.php'; ?>
<div class="ps-layout">
<?php include 'includes/admin_sidebar.php'; ?>
<div class="ps-main">
    <div class="ps-topbar admin-topbar"><div class="fw-semibold text-white">Activity Logs</div></div>
    <div class="ps-content">
        <div class="ps-card">
            <div class="d-flex justify-content-between mb-3">
                <h6 class="fw-bold mb-0">Activity Logs (<?= count($logs) ?>)</h6>
                <form onsubmit="return confirm('Clear all logs?')" method="POST">
                    <input type="hidden" name="clear_logs" value="1">
                    <button class="btn btn-sm btn-danger"><i class="bi bi-trash me-1"></i>Clear Logs</button>
                </form>
            </div>
            <div class="data-table-wrapper">
            <table class="table table-sm">
                <thead><tr><th>User</th><th>Type</th><th>Action</th><th>Details</th><th>IP</th><th>Time</th></tr></thead>
                <tbody>
                <?php foreach ($logs as $l): ?>
                <tr>
                    <td class="text-muted small"><?= h($l['username'] ?? 'System') ?></td>
                    <td><span class="badge <?= $l['user_type']==='admin'?'bg-danger':'bg-secondary' ?>"><?= ucfirst($l['user_type']) ?></span></td>
                    <td class="text-white small fw-semibold"><?= h($l['action']) ?></td>
                    <td class="text-muted small text-truncate" style="max-width:200px;"><?= h($l['details']) ?></td>
                    <td class="text-muted small" style="font-family:monospace;"><?= h($l['ip_address']) ?></td>
                    <td class="text-muted small"><?= format_date($l['created_at']) ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            </div>
        </div>
    </div>
</div>
</div>
<?php
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['clear_logs'])) {
    $conn->query("TRUNCATE TABLE activity_logs");
    redirect_with_message(SITE_URL.'/admin/logs.php','success','Logs cleared.');
}
?>
<?php include '../includes/footer.php'; ?>
