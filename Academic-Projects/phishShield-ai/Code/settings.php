<?php
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/config/functions.php';
require_login();
$user = current_user($pdo);
$ok = null; $err = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['profile'])) {
        $name  = trim($_POST['name']  ?? '');
        $email = trim($_POST['email'] ?? '');
        $dark  = isset($_POST['dark_mode']) ? 1 : 0;
        if ($name==='' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $err = 'Invalid name or email.';
        } else {
            $imgPath = $user['profile_image'];
            if (!empty($_FILES['avatar']['name'])) {
                $f = $_FILES['avatar'];
                $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg','jpeg','png','gif','webp']) && $f['size'] < 2*1024*1024) {
                    $dest = "assets/uploads/u{$user['id']}_" . time() . ".$ext";
                    if (move_uploaded_file($f['tmp_name'], __DIR__ . "/$dest")) $imgPath = $dest;
                }
            }
            $stmt = $pdo->prepare("UPDATE users SET name=?, email=?, dark_mode=?, profile_image=? WHERE id=?");
            $stmt->execute([$name,$email,$dark,$imgPath,$user['id']]);
            $ok = 'Profile updated.';
            $user = current_user($pdo);
        }
    }
    if (isset($_POST['password'])) {
        $cur = $_POST['current']  ?? '';
        $new = $_POST['new']      ?? '';
        if (!password_verify($cur, $user['password'])) $err = 'Current password incorrect.';
        elseif (strlen($new) < 6)                      $err = 'New password too short.';
        else {
            $h = password_hash($new, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("UPDATE users SET password=? WHERE id=?");
            $stmt->execute([$h,$user['id']]);
            $ok = 'Password changed.';
        }
    }
}

$pageTitle = 'Settings';
include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';
?>
<div class="ps-app">
  <?php include __DIR__ . '/includes/sidebar.php'; ?>
  <main class="ps-main fade-in">
    <div class="ps-page-head"><h1><i class="fa-solid fa-gear text-cyan"></i> Settings</h1></div>

    <?php if ($ok):  ?><div class="alert alert-success"><?= e($ok) ?></div><?php endif; ?>
    <?php if ($err): ?><div class="alert alert-danger"><?= e($err) ?></div><?php endif; ?>

    <div class="row g-3">
      <div class="col-lg-6">
        <div class="ps-card">
          <h6>Profile</h6>
          <form method="post" enctype="multipart/form-data">
            <div class="mb-2"><label class="form-label">Name</label><input class="form-control" name="name" value="<?= e($user['name']) ?>" required></div>
            <div class="mb-2"><label class="form-label">Email</label><input class="form-control" type="email" name="email" value="<?= e($user['email']) ?>" required></div>
            <div class="mb-2"><label class="form-label">Profile image</label><input class="form-control" type="file" name="avatar" accept="image/*"></div>
            <?php if (!empty($user['profile_image'])): ?><img src="<?= APP_URL ?>/<?= e($user['profile_image']) ?>" style="height:60px;border-radius:8px" class="mb-2"><br><?php endif; ?>
            <div class="form-check form-switch mb-3">
              <input class="form-check-input" type="checkbox" name="dark_mode" id="dm" <?= $user['dark_mode']?'checked':'' ?>>
              <label for="dm" class="form-check-label">Dark mode</label>
            </div>
            <button name="profile" value="1" class="btn btn-grad">Save Profile</button>
          </form>
        </div>
      </div>
      <div class="col-lg-6">
        <div class="ps-card">
          <h6>Change Password</h6>
          <form method="post">
            <div class="mb-2"><label class="form-label">Current Password</label><input class="form-control" type="password" name="current" required></div>
            <div class="mb-2"><label class="form-label">New Password</label><input class="form-control" type="password" name="new" required></div>
            <button name="password" value="1" class="btn btn-grad">Update Password</button>
          </form>
        </div>
      </div>
    </div>
  </main>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
