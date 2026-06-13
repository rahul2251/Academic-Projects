<?php
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/config/functions.php';

if (is_logged_in()) { header("Location: dashboard.php"); exit; }

$err = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name  = trim($_POST['name']  ?? '');
    $email = trim($_POST['email'] ?? '');
    $pw    = $_POST['password']   ?? '';

    if ($name==='' || $email==='' || $pw==='') {
        $err = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $err = 'Invalid email address.';
    } elseif (strlen($pw) < 6) {
        $err = 'Password must be at least 6 characters.';
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $err = 'Email already registered.';
        } else {
            $hash = password_hash($pw, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("INSERT INTO users (name,email,password) VALUES (?,?,?)");
            $stmt->execute([$name,$email,$hash]);
            $_SESSION['user_id'] = (int)$pdo->lastInsertId();
            log_activity($pdo, $_SESSION['user_id'], 'register');
            header("Location: dashboard.php"); exit;
        }
    }
}
$pageTitle = 'Register';
include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';
?>
<form method="post" class="ps-form fade-in">
  <h2><i class="fa-solid fa-user-plus text-cyan"></i> Create Account</h2>
  <?php if ($err): ?><div class="alert alert-danger py-2"><?= e($err) ?></div><?php endif; ?>
    <div class="mb-3"><label class="form-label">Full Name</label><input name="name" class="form-control" placeholder="Rahul Nishad" required></div>
    <div class="mb-3"><label class="form-label">Email</label><input type="email" name="email" class="form-control" placeholder="name@example.com" required></div>
  <div class="mb-3"><label class="form-label">Password</label><input type="password" name="password" class="form-control" required></div>
  <button class="btn btn-grad w-100">Register</button>
  <p class="text-center mt-3 small text-muted">Already have an account? <a href="login.php" class="text-cyan">Login</a></p>
</form>
<?php include __DIR__ . '/includes/footer.php'; ?>
