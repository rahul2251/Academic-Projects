<?php
require_once 'config/config.php';
require_once 'config/db.php';
require_once 'includes/functions.php';
$page_title = 'Forgot Password';
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $msg = ['type'=>'danger','text'=>'Invalid email address.'];
    } else {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        // Always show success to prevent email enumeration
        $msg = ['type'=>'success','text'=>'If that email exists, a reset link has been sent. (Demo mode — check with admin to reset password manually.)'];
        $stmt->close();
    }
}
?>
<?php include 'includes/header.php'; ?>
<div class="auth-page">
    <div class="auth-card">
        <div class="auth-logo">
            <i class="bi bi-shield-shaded text-accent me-2"></i>
            <span class="text-white fw-bold">PhishShield <span class="text-accent">AI</span></span>
        </div>
        <h4 class="text-center fw-bold mb-1">Reset Password</h4>
        <p class="text-muted text-center small mb-4">Enter your email to get a reset link</p>

        <?php if ($msg): ?><div class="alert alert-<?= $msg['type'] ?>"><?= h($msg['text']) ?></div><?php endif; ?>

        <form method="POST">
            <div class="mb-4">
                <label>Email Address</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                    <input type="email" name="email" class="form-control glow-input" placeholder="you@example.com" required>
                </div>
            </div>
            <button type="submit" class="btn btn-cyber w-100">
                <i class="bi bi-send me-2"></i>Send Reset Link
            </button>
        </form>
        <hr class="divider my-4">
        <p class="text-center text-muted small"><a href="<?= SITE_URL ?>/login.php" class="text-accent">← Back to Login</a></p>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
