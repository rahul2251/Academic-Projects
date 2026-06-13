<?php
require_once '../config/config.php';
require_once '../config/db.php';
require_once '../config/auth.php';
require_once '../includes/functions.php';
require_user_login();
$uid = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current = $_POST['current_password'] ?? '';
    $new_pass = $_POST['new_password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    $user = $conn->query("SELECT password FROM users WHERE id=$uid")->fetch_assoc();
    if (!password_verify($current, $user['password'])) {
        redirect_with_message(SITE_URL . '/user/settings.php', 'danger', 'Current password is incorrect.');
    } elseif (strlen($new_pass) < 6) {
        redirect_with_message(SITE_URL . '/user/settings.php', 'danger', 'New password must be at least 6 characters.');
    } elseif ($new_pass !== $confirm) {
        redirect_with_message(SITE_URL . '/user/settings.php', 'danger', 'Passwords do not match.');
    } else {
        $hash = password_hash($new_pass, PASSWORD_BCRYPT);
        $conn->query("UPDATE users SET password='$hash' WHERE id=$uid");
        redirect_with_message(SITE_URL . '/user/settings.php', 'success', 'Password changed successfully!');
    }
}

$page_title = 'Settings';
$extra_css = '<link rel="stylesheet" href="' . SITE_URL . '/assets/css/dashboard.css">';
?>
<?php include '../includes/header.php'; ?>
<div class="ps-layout">
<?php include '../includes/sidebar.php'; ?>
<div class="ps-main">
    <div class="ps-topbar"><div class="fw-semibold text-white">Settings</div></div>
    <div class="ps-content" style="max-width:600px;">
        <?php include '../includes/alerts.php'; ?>
        <div class="ps-card mb-4">
            <h6 class="fw-bold mb-4"><i class="bi bi-lock text-accent me-2"></i>Change Password</h6>
            <form method="POST">
                <div class="mb-3">
                    <label>Current Password</label>
                    <input type="password" name="current_password" class="form-control glow-input" required>
                </div>
                <div class="mb-3">
                    <label>New Password</label>
                    <input type="password" name="new_password" class="form-control glow-input" placeholder="Min. 6 characters" required>
                </div>
                <div class="mb-3">
                    <label>Confirm New Password</label>
                    <input type="password" name="confirm_password" class="form-control glow-input" required>
                </div>
                <button type="submit" name="change_password" class="btn btn-cyber"><i class="bi bi-key me-2"></i>Change Password</button>
            </form>
        </div>
        <div class="ps-card">
            <h6 class="fw-bold mb-3"><i class="bi bi-info-circle text-accent me-2"></i>Account Info</h6>
            <div class="d-flex justify-content-between py-2 border-bottom" style="border-color:var(--ps-border)!important;">
                <span class="text-muted small">Email</span><span class="text-white small"><?= h($_SESSION['user_email']) ?></span>
            </div>
            <div class="d-flex justify-content-between py-2 border-bottom" style="border-color:var(--ps-border)!important;">
                <span class="text-muted small">Role</span><span class="badge bg-success">User</span>
            </div>
            <div class="d-flex justify-content-between py-2">
                <span class="text-muted small">Status</span><span class="badge bg-success">Active</span>
            </div>
            <hr class="divider">
            <div class="text-danger small">
                <strong>Danger Zone</strong><br>
                <a href="<?= SITE_URL ?>/logout.php" class="text-danger">Log out of all sessions →</a>
            </div>
        </div>
    </div>
</div>
</div>
<?php include '../includes/footer.php'; ?>
