<?php
require_once '../config/config.php';
require_once '../config/db.php';
require_once '../config/auth.php';
require_once '../includes/functions.php';

if (is_admin_logged_in()) {
    header('Location: ' . SITE_URL . '/admin/dashboard.php');
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $stmt = $conn->prepare("SELECT * FROM admins WHERE email = ? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $admin = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION['admin_id']   = $admin['id'];
        $_SESSION['admin_name'] = $admin['full_name'];
        $_SESSION['user_role']  = 'admin';
        $_SESSION['last_activity'] = time();
        $conn->query("UPDATE admins SET last_login=NOW() WHERE id=" . (int)$admin['id']);
        log_activity($conn, 'Admin Login', 'Admin logged in', $admin['id'], 'admin');
        header('Location: ' . SITE_URL . '/admin/dashboard.php');
        exit();
    } else {
        $error = 'Invalid admin credentials.';
    }
}
$page_title = 'Admin Login';
?>
<?php include '../includes/header.php'; ?>
<style>
body { background: #0a0a18 !important; }
</style>
<div class="auth-page">
    <div class="auth-card" style="border-color:rgba(233,69,96,.3);">
        <div class="auth-logo">
            <i class="bi bi-shield-lock-fill text-accent fs-2 d-block mb-2"></i>
            <span class="text-white fw-bold">Admin Panel</span>
        </div>
        <h5 class="text-center fw-bold mb-1">Administrator Access</h5>
        <p class="text-muted text-center small mb-4">Restricted area — authorised personnel only</p>
        <?php if ($error): ?><div class="alert alert-danger"><?= h($error) ?></div><?php endif; ?>
        <?php if (isset($_GET['expired'])): ?><div class="alert alert-warning">Session expired.</div><?php endif; ?>
        <form method="POST">
            <div class="mb-3">
                <label>Admin Email</label>
                <div class="input-group"><span class="input-group-text"><i class="bi bi-envelope"></i></span>
                <input type="email" name="email" class="form-control glow-input" required placeholder="admin@phishshield.ai"></div>
            </div>
            <div class="mb-4">
                <label>Password</label>
                <div class="input-group"><span class="input-group-text"><i class="bi bi-lock"></i></span>
                <input type="password" name="password" class="form-control glow-input" required placeholder="••••••••"></div>
            </div>
            <button type="submit" class="btn btn-cyber w-100"><i class="bi bi-shield-lock me-2"></i>Admin Login</button>
        </form>
        <hr class="divider my-3">
        <p class="text-center text-muted small"><a href="<?= SITE_URL ?>/login.php" class="text-muted">← User Login</a></p>
        <div class="text-center text-muted mt-2" style="font-size:.72rem;">
            Default: admin@phishshield.ai / admin123
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>
