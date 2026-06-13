<?php
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/config/functions.php';

if (is_logged_in()) { header("Location: dashboard.php"); exit; }

$err = null;
$isAdmin = isset($_GET['admin']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $pw    = $_POST['password'] ?? '';
    $loginAdmin = isset($_POST['as_admin']);

    if ($email === '' || $pw === '') {
        $err = 'Please fill in all fields.';
    } elseif ($loginAdmin) {
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE email = ?");
        $stmt->execute([$email]);
        $a = $stmt->fetch();
        if ($a && password_verify($pw, $a['password'])) {
            $_SESSION['admin_id'] = $a['id'];
            $_SESSION['admin_name'] = $a['name'];
            header("Location: admin.php"); exit;
        }
        $err = 'Invalid admin credentials.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $u = $stmt->fetch();
        if ($u && password_verify($pw, $u['password'])) {
            if ($u['is_blocked']) {
                $err = 'Your account has been blocked. Contact admin.';
            } else {
                $_SESSION['user_id'] = $u['id'];
                log_activity($pdo, $u['id'], 'login');
                header("Location: dashboard.php"); exit;
            }
        } else {
            $err = 'Invalid email or password.';
        }
    }
}
$pageTitle = $isAdmin ? 'Admin Login' : 'Login';
include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';
?>
<form method="post" class="ps-form fade-in">
  <h2><i class="fa-solid fa-right-to-bracket text-cyan"></i> <?= $isAdmin ? 'Admin Login' : 'Sign In' ?></h2>
  <?php if ($err): ?><div class="alert alert-danger py-2"><?= e($err) ?></div><?php endif; ?>
    <div class="mb-3"><label class="form-label">Email</label><input type="email" name="email" class="form-control" placeholder="name@example.com" required></div>
    <div class="mb-3"><label class="form-label">Password</label><input type="password" name="password" class="form-control" placeholder="Enter your password" required></div>
  <?php if ($isAdmin): ?><input type="hidden" name="as_admin" value="1"><?php endif; ?>
  <button class="btn btn-grad w-100">Login</button>
  <p class="text-center mt-3 small text-muted">
    <?php if ($isAdmin): ?>
      <a href="login.php" class="text-cyan">User login</a>
    <?php else: ?>
      No account? <a href="register.php" class="text-cyan">Register</a> ·
      <a href="login.php?admin=1" class="text-cyan">Admin login</a>
    <?php endif; ?>
  </p>
</form>
<?php include __DIR__ . '/includes/footer.php'; ?>
