<?php
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/config/functions.php';
require_login();
$user = current_user($pdo);
$ok = null; $err = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rating = max(1, min(5, (int)($_POST['rating'] ?? 5)));
    $msg    = trim($_POST['message'] ?? '');
    if ($msg === '') $err = 'Please write a message.';
    else {
        $stmt = $pdo->prepare("INSERT INTO feedback (user_id,rating,message) VALUES (?,?,?)");
        $stmt->execute([$user['id'],$rating,$msg]);
        $ok = 'Thank you for your feedback!';
    }
}

$mine = $pdo->prepare("SELECT * FROM feedback WHERE user_id=? ORDER BY id DESC LIMIT 10");
$mine->execute([$user['id']]);
$mine = $mine->fetchAll();

$pageTitle = 'Feedback';
include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';
?>
<div class="ps-app">
  <?php include __DIR__ . '/includes/sidebar.php'; ?>
  <main class="ps-main fade-in">
    <div class="ps-page-head"><h1><i class="fa-solid fa-comment-dots text-cyan"></i> Feedback</h1></div>

    <?php if ($ok):  ?><div class="alert alert-success"><?= e($ok) ?></div><?php endif; ?>
    <?php if ($err): ?><div class="alert alert-danger"><?= e($err) ?></div><?php endif; ?>

    <div class="ps-card mb-4">
      <form method="post">
        <label class="form-label">Rating</label>
        <select name="rating" class="form-select mb-3">
          <?php for ($i=5;$i>=1;$i--): ?><option value="<?= $i ?>"><?= str_repeat('⭐',$i) ?> (<?= $i ?>)</option><?php endfor; ?>
        </select>
        <label class="form-label">Your message</label>
        <textarea name="message" rows="4" class="form-control" required></textarea>
        <button class="btn btn-grad mt-3">Submit Feedback</button>
      </form>
    </div>

    <div class="ps-card">
      <h6 class="mb-3">Your previous feedback</h6>
      <?php if (!$mine): ?><p class="text-muted small">No feedback yet.</p>
      <?php else: foreach ($mine as $f): ?>
        <div class="border-bottom border-secondary py-2">
          <div class="d-flex justify-content-between"><b><?= str_repeat('⭐',(int)$f['rating']) ?></b><small class="text-muted"><?= e($f['created_at']) ?></small></div>
          <div><?= nl2br(e($f['message'])) ?></div>
        </div>
      <?php endforeach; endif; ?>
    </div>
  </main>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
