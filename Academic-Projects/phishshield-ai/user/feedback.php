<?php
require_once '../config/config.php';
require_once '../config/db.php';
require_once '../config/auth.php';
require_once '../includes/functions.php';
require_user_login();
$uid = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');
    if ($subject && $message) {
        $stmt = $conn->prepare("INSERT INTO feedback (user_id, subject, message) VALUES (?,?,?)");
        $stmt->bind_param("iss", $uid, $subject, $message);
        $stmt->execute();
        $stmt->close();
        redirect_with_message(SITE_URL . '/user/feedback.php', 'success', 'Feedback submitted! We\'ll review it soon.');
    }
}

$my_feedback = $conn->query("SELECT * FROM feedback WHERE user_id=$uid ORDER BY created_at DESC LIMIT 10")->fetch_all(MYSQLI_ASSOC);
$page_title = 'Feedback';
$extra_css = '<link rel="stylesheet" href="' . SITE_URL . '/assets/css/dashboard.css">';
?>
<?php include '../includes/header.php'; ?>
<div class="ps-layout">
<?php include '../includes/sidebar.php'; ?>
<div class="ps-main">
    <div class="ps-topbar"><div class="fw-semibold text-white">Feedback</div></div>
    <div class="ps-content" style="max-width:800px;">
        <?php include '../includes/alerts.php'; ?>
        <div class="ps-card mb-4">
            <h5 class="fw-bold mb-3"><i class="bi bi-chat-dots text-accent me-2"></i>Send Feedback</h5>
            <form method="POST">
                <div class="mb-3">
                    <label>Subject</label>
                    <input type="text" name="subject" class="form-control glow-input" placeholder="What's your feedback about?" required>
                </div>
                <div class="mb-3">
                    <label>Message</label>
                    <textarea name="message" class="form-control glow-input" rows="5" placeholder="Describe your feedback, bug report, or suggestion..." required></textarea>
                </div>
                <button type="submit" class="btn btn-cyber"><i class="bi bi-send me-2"></i>Submit Feedback</button>
            </form>
        </div>

        <?php if (!empty($my_feedback)): ?>
        <div class="ps-card">
            <h6 class="fw-bold mb-3">Your Previous Feedback</h6>
            <?php foreach ($my_feedback as $fb): ?>
            <div class="p-3 mb-3" style="background:var(--ps-dark3);border-radius:var(--radius-sm);border:1px solid var(--ps-border);">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div class="fw-semibold text-white small"><?= h($fb['subject']) ?></div>
                    <span class="badge <?= $fb['status']==='replied'?'bg-success':($fb['status']==='read'?'bg-warning text-dark':'bg-secondary') ?>"><?= ucfirst($fb['status']) ?></span>
                </div>
                <div class="text-muted small mb-2"><?= h($fb['message']) ?></div>
                <?php if ($fb['admin_reply']): ?>
                <div class="mt-2 p-2" style="background:rgba(22,199,154,.08);border-radius:var(--radius-sm);border:1px solid rgba(22,199,154,.2);">
                    <div class="small fw-bold text-cyan mb-1"><i class="bi bi-reply me-1"></i>Admin Reply:</div>
                    <div class="text-muted small"><?= h($fb['admin_reply']) ?></div>
                </div>
                <?php endif; ?>
                <div class="text-muted mt-2" style="font-size:.73rem;"><?= format_date($fb['created_at']) ?></div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>
</div>
<?php include '../includes/footer.php'; ?>
