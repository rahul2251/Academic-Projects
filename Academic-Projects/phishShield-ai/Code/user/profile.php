<?php
require_once '../config/config.php';
require_once '../config/db.php';
require_once '../config/auth.php';
require_once '../includes/functions.php';
require_user_login();
$uid = $_SESSION['user_id'];
$user = $conn->query("SELECT * FROM users WHERE id=$uid")->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $full_name = trim($_POST['full_name'] ?? '');
    $username  = trim($_POST['username'] ?? '');
    $avatar_name = $user['avatar'];

    if (!empty($_FILES['avatar']['name'])) {
        $ext = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg','jpeg','png','gif','webp'])) {
            $avatar_name = 'user_' . $uid . '_' . time() . '.' . $ext;
            move_uploaded_file($_FILES['avatar']['tmp_name'], ROOT_PATH . '/assets/uploads/profile/' . $avatar_name);
        }
    }

    $stmt = $conn->prepare("UPDATE users SET full_name=?, username=?, avatar=? WHERE id=?");
    $stmt->bind_param("sssi", $full_name, $username, $avatar_name, $uid);
    $stmt->execute();
    $_SESSION['user_name']  = $full_name;
    $_SESSION['user_avatar']= $avatar_name;
    redirect_with_message(SITE_URL . '/user/profile.php', 'success', 'Profile updated!');
}

$page_title = 'Profile';
$extra_css = '<link rel="stylesheet" href="' . SITE_URL . '/assets/css/dashboard.css">';
?>
<?php include '../includes/header.php'; ?>
<div class="ps-layout">
<?php include '../includes/sidebar.php'; ?>
<div class="ps-main">
    <div class="ps-topbar"><div class="fw-semibold text-white">My Profile</div></div>
    <div class="ps-content" style="max-width:700px;">
        <?php include '../includes/alerts.php'; ?>
        <div class="ps-card mb-4 text-center">
            <img src="<?= get_avatar($user['avatar'], $user['full_name'] ?: $user['username']) ?>"
                 class="rounded-circle mb-3" width="90" height="90" alt="Avatar">
            <h5 class="fw-bold text-white"><?= h($user['full_name'] ?: $user['username']) ?></h5>
            <div class="text-muted small"><?= h($user['email']) ?></div>
            <span class="badge bg-success mt-2">Active</span>
        </div>
        <div class="ps-card">
            <h6 class="fw-bold mb-4">Edit Profile</h6>
            <form method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label>Profile Photo</label>
                    <input type="file" name="avatar" class="form-control glow-input" accept="image/*">
                </div>
                <div class="mb-3">
                    <label>Full Name</label>
                    <input type="text" name="full_name" class="form-control glow-input" value="<?= h($user['full_name']) ?>">
                </div>
                <div class="mb-3">
                    <label>Username</label>
                    <input type="text" name="username" class="form-control glow-input" value="<?= h($user['username']) ?>" required>
                </div>
                <div class="mb-3">
                    <label>Email Address</label>
                    <input type="email" class="form-control" value="<?= h($user['email']) ?>" disabled style="background:var(--ps-dark3);color:var(--ps-muted);border-color:var(--ps-border);">
                    <div class="text-muted" style="font-size:.75rem;">Contact admin to change email</div>
                </div>
                <button type="submit" name="update_profile" class="btn btn-cyber"><i class="bi bi-save me-2"></i>Save Changes</button>
            </form>
        </div>
    </div>
</div>
</div>
<?php include '../includes/footer.php'; ?>
