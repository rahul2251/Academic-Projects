<?php
require_once '../config/config.php';
require_once '../config/db.php';
require_once '../config/auth.php';
require_once '../includes/functions.php';
require_admin_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)$_POST['id'];
    $reply = $conn->real_escape_string(trim($_POST['reply'] ?? ''));
    if ($reply) {
        $conn->query("UPDATE feedback SET admin_reply='$reply', status='replied' WHERE id=$id");
    } else {
        $conn->query("UPDATE feedback SET status='read' WHERE id=$id");
    }
    redirect_with_message(SITE_URL.'/admin/feedback.php','success','Feedback updated.');
}

$list = $conn->query("SELECT f.*, u.username, u.email FROM feedback f JOIN users u ON u.id=f.user_id ORDER BY f.created_at DESC")->fetch_all(MYSQLI_ASSOC);
$page_title = 'Feedback';
$extra_css = '<link rel="stylesheet" href="' . SITE_URL . '/assets/css/dashboard.css"><link rel="stylesheet" href="' . SITE_URL . '/assets/css/admin.css">';
?>
<?php include '../includes/header.php'; ?>
<div class="ps-layout">
<?php include 'includes/admin_sidebar.php'; ?>
<div class="ps-main">
    <div class="ps-topbar admin-topbar"><div class="fw-semibold text-white">User Feedback</div></div>
    <div class="ps-content">
        <?php include '../includes/alerts.php'; ?>
        <?php if (empty($list)): ?>
        <div class="ps-card text-center py-5 text-muted"><i class="bi bi-chat-dots fs-1 d-block mb-2 opacity-50"></i>No feedback yet.</div>
        <?php else: ?>
        <div class="d-flex flex-column gap-3">
        <?php foreach ($list as $fb): ?>
        <div class="ps-card">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <div class="fw-bold text-white"><?= h($fb['subject']) ?></div>
                    <div class="text-muted small">From: <?= h($fb['username']) ?> (<?= h($fb['email']) ?>) · <?= format_date($fb['created_at']) ?></div>
                </div>
                <span class="badge <?= $fb['status']==='replied'?'bg-success':($fb['status']==='read'?'bg-warning text-dark':'bg-secondary') ?>"><?= ucfirst($fb['status']) ?></span>
            </div>
            <div class="text-muted small p-3 mb-3" style="background:var(--ps-dark3);border-radius:var(--radius-sm);"><?= h($fb['message']) ?></div>
            <?php if ($fb['admin_reply']): ?>
            <div class="p-2 mb-3" style="background:rgba(22,199,154,.08);border:1px solid rgba(22,199,154,.2);border-radius:var(--radius-sm);">
                <div class="small text-cyan fw-bold mb-1">Admin Reply:</div>
                <div class="text-muted small"><?= h($fb['admin_reply']) ?></div>
            </div>
            <?php endif; ?>
            <form method="POST" class="d-flex gap-2">
                <input type="hidden" name="id" value="<?= $fb['id'] ?>">
                <input type="text" name="reply" class="form-control glow-input form-control-sm" placeholder="Reply to user...">
                <button type="submit" class="btn btn-accent btn-sm">Reply</button>
                <?php if ($fb['status']==='pending'): ?>
                <button type="submit" name="mark_read" class="btn btn-sm btn-outline-accent">Mark Read</button>
                <?php endif; ?>
            </form>
        </div>
        <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>
</div>
<?php include '../includes/footer.php'; ?>
