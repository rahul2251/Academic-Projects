<?php
require_once '../config/config.php';
require_once '../config/db.php';
require_once '../config/auth.php';
require_once '../includes/functions.php';
require_admin_login();

$id = (int)($_GET['id'] ?? 0);
$user = $conn->query("SELECT * FROM users WHERE id=$id")->fetch_assoc();
if (!$user) { redirect_with_message(SITE_URL.'/admin/users.php','danger','User not found.'); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = $conn->real_escape_string(trim($_POST['full_name']??''));
    $email     = $conn->real_escape_string(trim($_POST['email']??''));
    $status    = in_array($_POST['status'],['active','blocked','pending'])?$_POST['status']:'active';
    $conn->query("UPDATE users SET full_name='$full_name', email='$email', status='$status' WHERE id=$id");
    if (!empty($_POST['new_password'])) {
        $hash = password_hash($_POST['new_password'], PASSWORD_BCRYPT);
        $conn->query("UPDATE users SET password='$hash' WHERE id=$id");
    }
    redirect_with_message(SITE_URL.'/admin/users.php','success','User updated.');
}

$page_title = 'Edit User';
$extra_css = '<link rel="stylesheet" href="' . SITE_URL . '/assets/css/dashboard.css"><link rel="stylesheet" href="' . SITE_URL . '/assets/css/admin.css">';
?>
<?php include '../includes/header.php'; ?>
<div class="ps-layout">
<?php include 'includes/admin_sidebar.php'; ?>
<div class="ps-main">
    <div class="ps-topbar admin-topbar"><div class="fw-semibold text-white">Edit User</div></div>
    <div class="ps-content" style="max-width:600px;">
        <?php include '../includes/alerts.php'; ?>
        <div class="ps-card">
            <div class="d-flex align-items-center gap-3 mb-4">
                <img src="<?= get_avatar($user['avatar'],$user['full_name']?:$user['username']) ?>" class="rounded-circle" width="56" height="56">
                <div><div class="text-white fw-bold"><?= h($user['username']) ?></div><div class="text-muted small"><?= h($user['email']) ?></div></div>
            </div>
            <form method="POST">
                <div class="mb-3"><label>Full Name</label><input type="text" name="full_name" class="form-control glow-input" value="<?= h($user['full_name']) ?>"></div>
                <div class="mb-3"><label>Email</label><input type="email" name="email" class="form-control glow-input" value="<?= h($user['email']) ?>" required></div>
                <div class="mb-3"><label>Status</label>
                    <select name="status" class="form-select glow-input">
                        <option value="active" <?= $user['status']==='active'?'selected':'' ?>>Active</option>
                        <option value="blocked" <?= $user['status']==='blocked'?'selected':'' ?>>Blocked</option>
                        <option value="pending" <?= $user['status']==='pending'?'selected':'' ?>>Pending</option>
                    </select>
                </div>
                <div class="mb-4"><label>New Password <span class="text-muted">(leave blank to keep current)</span></label><input type="password" name="new_password" class="form-control glow-input" placeholder="Leave blank to keep unchanged"></div>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-cyber"><i class="bi bi-save me-2"></i>Save Changes</button>
                    <a href="<?= SITE_URL ?>/admin/users.php" class="btn btn-outline-accent">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
</div>
<?php include '../includes/footer.php'; ?>
