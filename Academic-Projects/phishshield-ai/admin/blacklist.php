<?php
require_once '../config/config.php';
require_once '../config/db.php';
require_once '../config/auth.php';
require_once '../includes/functions.php';
require_admin_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'add') {
        $domain = $conn->real_escape_string(strtolower(trim($_POST['domain'] ?? '')));
        $reason = $conn->real_escape_string(trim($_POST['reason'] ?? ''));
        if ($domain) {
            $conn->query("INSERT IGNORE INTO blacklist (domain, reason, added_by) VALUES ('$domain', '$reason', {$_SESSION['admin_id']})");
            redirect_with_message(SITE_URL.'/admin/blacklist.php','success',"Domain '$domain' added to blacklist.");
        }
    } elseif ($action === 'delete') {
        $id = (int)$_POST['id'];
        $conn->query("DELETE FROM blacklist WHERE id=$id");
        redirect_with_message(SITE_URL.'/admin/blacklist.php','success','Domain removed from blacklist.');
    }
}

$list = $conn->query("SELECT * FROM blacklist ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);
$page_title = 'Blacklist Manager';
$extra_css = '<link rel="stylesheet" href="' . SITE_URL . '/assets/css/dashboard.css"><link rel="stylesheet" href="' . SITE_URL . '/assets/css/admin.css">';
?>
<?php include '../includes/header.php'; ?>
<div class="ps-layout">
<?php include 'includes/admin_sidebar.php'; ?>
<div class="ps-main">
    <div class="ps-topbar admin-topbar"><div class="fw-semibold text-white">Blacklist Manager</div></div>
    <div class="ps-content">
        <?php include '../includes/alerts.php'; ?>
        <div class="row g-4">
            <div class="col-lg-4">
                <div class="ps-card">
                    <h6 class="fw-bold mb-3"><i class="bi bi-plus-circle text-accent me-2"></i>Add Domain</h6>
                    <form method="POST">
                        <input type="hidden" name="action" value="add">
                        <div class="mb-3"><label>Domain</label><input type="text" name="domain" class="form-control glow-input" placeholder="phishing-site.com" required></div>
                        <div class="mb-3"><label>Reason</label><input type="text" name="reason" class="form-control glow-input" placeholder="Why is this blocked?"></div>
                        <button type="submit" class="btn btn-cyber w-100"><i class="bi bi-slash-circle me-2"></i>Add to Blacklist</button>
                    </form>
                </div>
            </div>
            <div class="col-lg-8">
                <div class="ps-card">
                    <h6 class="fw-bold mb-3">Blacklisted Domains (<?= count($list) ?>)</h6>
                    <div class="data-table-wrapper">
                    <table class="table">
                        <thead><tr><th>Domain</th><th>Reason</th><th>Added</th><th>Action</th></tr></thead>
                        <tbody>
                        <?php foreach ($list as $l): ?>
                        <tr>
                            <td class="text-accent fw-semibold small"><?= h($l['domain']) ?></td>
                            <td class="text-muted small"><?= h($l['reason']?:'—') ?></td>
                            <td class="text-muted small"><?= date('M d, Y', strtotime($l['created_at'])) ?></td>
                            <td>
                                <form method="POST" class="d-inline" onsubmit="return confirm('Remove this domain?')">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $l['id'] ?>">
                                    <button class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></button>
                                </form>
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
</div>
</div>
<?php include '../includes/footer.php'; ?>
