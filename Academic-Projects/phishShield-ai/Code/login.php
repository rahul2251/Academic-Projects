<?php
require_once 'config/config.php';
require_once 'config/db.php';
require_once 'includes/functions.php';


if (function_exists('is_logged_in')) {
    echo "is_logged_in() is loaded!";
} else {
    echo "is_logged_in() is NOT loaded!";
}
exit;


if (is_logged_in()) {
    header('Location: ' . SITE_URL . '/user/dashboard.php');
    exit();
}



$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND status = 'active' LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id']    = $user['id'];
            $_SESSION['user_name']  = $user['full_name'] ?: $user['username'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_avatar']= $user['avatar'];
            $_SESSION['user_role']  = 'user';
            $_SESSION['last_activity'] = time();

            // Update last login
            $conn->query("UPDATE users SET last_login = NOW() WHERE id = " . (int)$user['id']);
            log_activity($conn, 'Login', 'User logged in', $user['id']);

            $redirect = $_GET['redirect'] ?? SITE_URL . '/user/dashboard.php';
            header('Location: ' . $redirect);
            exit();
        } else {
            $error = 'Invalid email or password, or your account is blocked.';
        }
    }
}

$page_title = 'Login';
?>
<?php include 'includes/header.php'; ?>
<div class="auth-page">
    <div class="auth-card">
        <div class="auth-logo">
            <i class="bi bi-shield-shaded text-accent me-2"></i>
            <span class="text-white fw-bold">PhishShield <span class="text-accent">AI</span></span>
        </div>
        <h4 class="text-center fw-bold mb-1">Welcome Back</h4>
        <p class="text-muted text-center small mb-4">Sign in to your account</p>

        <?php if ($error): ?>
        <div class="alert alert-danger d-flex align-items-center gap-2">
            <i class="bi bi-exclamation-circle-fill"></i><?= h($error) ?>
        </div>
        <?php endif; ?>

        <?php if (isset($_GET['expired'])): ?>
        <div class="alert alert-warning">Session expired. Please log in again.</div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-3">
                <label>Email Address</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                    <input type="email" name="email" class="form-control glow-input" placeholder="you@example.com" value="<?= h($_POST['email'] ?? '') ?>" required>
                </div>
            </div>
            <div class="mb-4">
                <div class="d-flex justify-content-between">
                    <label>Password</label>
                    <a href="<?= SITE_URL ?>/forgot-password.php" class="text-accent small">Forgot password?</a>
                </div>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                    <input type="password" name="password" id="pass" class="form-control glow-input" placeholder="••••••••" required>
                    <button type="button" class="input-group-text" onclick="togglePass()"><i class="bi bi-eye" id="eyeIcon"></i></button>
                </div>
            </div>
            <button type="submit" class="btn btn-cyber w-100">
                <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
            </button>
        </form>
        <hr class="divider my-4">
        <p class="text-center text-muted small">Don't have an account? <a href="<?= SITE_URL ?>/register.php" class="text-accent fw-semibold">Register free</a></p>
        <p class="text-center text-muted" style="font-size:.75rem;">Admin? <a href="<?= SITE_URL ?>/admin/login.php" class="text-muted">Admin Login →</a></p>
    </div>
</div>
<script>
function togglePass() {
    var p = document.getElementById('pass');
    var i = document.getElementById('eyeIcon');
    p.type = p.type === 'password' ? 'text' : 'password';
    i.className = p.type === 'password' ? 'bi bi-eye' : 'bi bi-eye-slash';
}
</script>
<?php include 'includes/footer.php'; ?>
