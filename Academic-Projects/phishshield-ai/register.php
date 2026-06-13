<?php
require_once 'config/config.php';
require_once 'config/db.php';
require_once 'includes/functions.php';

/*
if (is_logged_in()) {
    header('Location: ' . SITE_URL . '/user/dashboard.php');
    exit();
}
*/


$error = ''; $success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username  = trim($_POST['username'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $full_name = trim($_POST['full_name'] ?? '');
    $password  = $_POST['password'] ?? '';
    $confirm   = $_POST['confirm_password'] ?? '';

    if (empty($username) || empty($email) || empty($password)) {
        $error = 'All fields are required.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email address.';
    } else {
        // Check unique
        $stmt = $conn->prepare("SELECT id FROM users WHERE email=? OR username=? LIMIT 1");
        $stmt->bind_param("ss", $email, $username);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $error = 'Email or username already taken.';
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $stmt2 = $conn->prepare("INSERT INTO users (username,email,password,full_name) VALUES (?,?,?,?)");
            $stmt2->bind_param("ssss", $username, $email, $hash, $full_name);
            if ($stmt2->execute()) {
                $new_id = $stmt2->insert_id;
                log_activity($conn, 'Register', 'New user registered', $new_id);
                redirect_with_message(SITE_URL . '/login.php', 'success', 'Account created! Please log in.');
            } else {
                $error = 'Registration failed. Please try again.';
            }
            $stmt2->close();
        }
        $stmt->close();
    }
}
$page_title = 'Register';
?>
<?php include 'includes/header.php'; ?>
<div class="auth-page">
    <div class="auth-card" style="max-width:480px;">
        <div class="auth-logo">
            <i class="bi bi-shield-shaded text-accent me-2"></i>
            <span class="text-white fw-bold">PhishShield <span class="text-accent">AI</span></span>
        </div>
        <h4 class="text-center fw-bold mb-1">Create Account</h4>
        <p class="text-muted text-center small mb-4">Join PhishShield AI for free</p>

        <?php if ($error): ?><div class="alert alert-danger"><?= h($error) ?></div><?php endif; ?>

        <form method="POST">
            <div class="row g-3">
                <div class="col-md-6">
                    <label>Full Name</label>
                    <input type="text" name="full_name" class="form-control glow-input" placeholder="John Doe" value="<?= h($_POST['full_name'] ?? '') ?>">
                </div>
                <div class="col-md-6">
                    <label>Username <span class="text-accent">*</span></label>
                    <input type="text" name="username" class="form-control glow-input" placeholder="johndoe" value="<?= h($_POST['username'] ?? '') ?>" required>
                </div>
                <div class="col-12">
                    <label>Email Address <span class="text-accent">*</span></label>
                    <input type="email" name="email" class="form-control glow-input" placeholder="you@example.com" value="<?= h($_POST['email'] ?? '') ?>" required>
                </div>
                <div class="col-md-6">
                    <label>Password <span class="text-accent">*</span></label>
                    <input type="password" name="password" class="form-control glow-input" placeholder="Min. 6 characters" required>
                </div>
                <div class="col-md-6">
                    <label>Confirm Password <span class="text-accent">*</span></label>
                    <input type="password" name="confirm_password" class="form-control glow-input" placeholder="Repeat password" required>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-cyber w-100 mt-2">
                        <i class="bi bi-person-plus me-2"></i>Create Account
                    </button>
                </div>
            </div>
        </form>
        <hr class="divider my-4">
        <p class="text-center text-muted small">Already have an account? <a href="<?= SITE_URL ?>/login.php" class="text-accent fw-semibold">Sign In</a></p>
    </div>
</div>
<?php include 'includes/footer.php'; ?>
