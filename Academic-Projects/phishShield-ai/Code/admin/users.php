<?php
require_once '../config/config.php';
require_once '../config/db.php';
require_once '../config/auth.php';
require_once '../includes/functions.php';
require_admin_login();

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['user_id'] ?? 0);
    $action = $_POST['action'] ?? '';
    if ($id > 0) {
        if ($action === 'block') $conn->query("UPDATE users SET status='blocked' WHERE id=$id");
        elseif ($action === 'unblock') $conn->query("UPDATE users SET status='active' WHERE id=$id");
        elseif ($action === 'delete') $conn->query("DELETE FROM users WHERE id=$id");
        redirect_with_message(SITE_URL . '/admin/users.php', 'success', 'User updated.');
    }
}

$search = trim($_GET['q'] ?? '');
$where = $search ? "WHERE username LIKE '%{$conn->real_escape_string($search)}%' OR email LIKE '%{$conn->real_escape_string($search)}%'" : '';
$users = $conn->query("SELECT u.*, (SELECT COUNT(*) FROM url_scans WHERE user_id=u.id) url_scans, (SELECT COUNT(*) FROM email_scans WHERE user_id=u.id) email_scans FROM users u $where ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);

$page_title = 'Manage Users';
$extra_css = '<link rel="stylesheet" href="' . SITE_URL . '/assets/css/dashboard.css"><link rel="stylesheet" href="' . SITE_URL . '/assets/css/admin.css">';
?>
<?php include '../includes/header.php'; ?>
<div class="ps-layout">
<?php include 'includes/admin_sidebar.php'; ?>
<div class="ps-main">
    <div class="ps-topbar admin-topbar"><div class="fw-semibold text-white">Users</div></div>
    <div class="ps-content">
        <?php include '../includes/alerts.php'; ?>
        <div class="ps-card">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <h6 class="fw-bold mb-0">All Users (<?= count($users) ?>)</h6>
                <form class="d-flex gap-2">
                    <input type="text" name="q" class="form-control form-control-sm glow-input" style="width:220px;" placeholder="Search users..." value="<?= h($search) ?>">
                    <button class="btn btn-accent btn-sm"><i class="bi bi-search"></i></button>
                </form>
            </div>
            <div class="data-table-wrapper">
            <table class="table">
                <thead><tr><th>User</th><th>Email</th><th>Status</th><th>Scans</th><th>Joined</th><th>Actions</th></tr></thead>
                <tbody>
                <?php foreach ($users as $u): ?>
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <img src="<?= get_avatar($u['avatar'],$u['full_name']?:$u['username']) ?>" class="rounded-circle" width="32" height="32">
                            <div class="text-white small fw-semibold"><?= h($u['full_name']?:$u['username']) ?></div>
                        </div>
                    </td>
                    <td class="text-muted small"><?= h($u['email']) ?></td>
                    <td><?= $u['status']==='active'?'<span class="badge bg-success">Active</span>':'<span class="badge bg-danger">Blocked</span>' ?></td>
                    <td class="text-muted small"><?= $u['url_scans'] ?> URL · <?= $u['email_scans'] ?> Email</td>
                    <td class="text-muted small"><?= date('M d, Y', strtotime($u['created_at'])) ?></td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="<?= SITE_URL ?>/admin/user-edit.php?id=<?= $u['id'] ?>" class="btn btn-sm" style="background:var(--ps-dark3);border:1px solid var(--ps-border);color:var(--ps-muted);"><i class="bi bi-pencil"></i></a>
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                <input type="hidden" name="action" value="<?= $u['status']==='active'?'block':'unblock' ?>">
                                <button class="btn btn-sm <?= $u['status']==='active'?'btn-warning text-dark':'btn-success' ?>" title="<?= $u['status']==='active'?'Block':'Unblock' ?>">
                                    <i class="bi bi-<?= $u['status']==='active'?'lock':'unlock' ?>"></i>
                                </button>
                            </form>
                            <form method="POST" class="d-inline" onsubmit="return confirm('Delete this user?')">
                                <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                <input type="hidden" name="action" value="delete">
                                <button class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </div>
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
<?php include '../includes/footer.php'; ?>
