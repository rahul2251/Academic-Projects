<?php
require_once '../config/config.php';
require_once '../config/db.php';
require_once '../config/auth.php';
require_once '../config/gemini.php';
require_once '../includes/functions.php';
require_user_login();

$uid = $_SESSION['user_id'];
$result = null;
$error  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject = trim($_POST['subject'] ?? '');
    $sender  = trim($_POST['sender']  ?? '');
    $body    = trim($_POST['body']    ?? '');

    if (empty($sender) || empty($body)) {
        $error = 'Sender and email body are required.';
    } else {
        $risk = calculate_email_risk($subject, $sender, $body);
        $score = $risk['score'];
        $details = $risk['details'];
        $res_label = score_to_result($score);
        $ai_analysis = gemini_analyze_email($subject, $sender, $body);

        $stmt = $conn->prepare("INSERT INTO email_scans (user_id, subject, sender, email_body, result, risk_score, details, ai_analysis) VALUES (?,?,?,?,?,?,?,?)");
        $stmt->bind_param("isssssiss", $uid, $subject, $sender, $body, $res_label, $score, $details, $ai_analysis);
        $stmt->execute();
        $stmt->close();

        log_activity($conn, 'Email Scan', "Scanned email from: $sender | Result: $res_label", $uid);

        $result = compact('subject','sender','body','score','res_label','details','ai_analysis');
    }
}

$page_title = 'Email Scanner';
$extra_css = '<link rel="stylesheet" href="' . SITE_URL . '/assets/css/dashboard.css">';
$extra_js = '<script src="' . SITE_URL . '/assets/js/scanner.js"></script>';
?>
<?php include '../includes/header.php'; ?>
<div class="ps-layout">
<?php include '../includes/sidebar.php'; ?>
<div class="ps-main">
    <div class="ps-topbar">
        <div><div class="fw-semibold text-white">Email Scanner</div><div class="text-muted small">Analyze suspicious emails for phishing</div></div>
    </div>
    <div class="ps-content" style="max-width:800px;">
        <?php include '../includes/alerts.php'; ?>
        <div class="ps-card mb-4">
            <h5 class="fw-bold mb-1"><i class="bi bi-envelope-exclamation text-accent me-2"></i>Email Analysis</h5>
            <p class="text-muted small mb-3">Paste the email details below for AI-powered phishing analysis</p>
            <?php if ($error): ?><div class="alert alert-danger"><?= h($error) ?></div><?php endif; ?>
            <form id="emailScanForm" method="POST">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label>Subject Line</label>
                        <input type="text" name="subject" class="form-control glow-input" placeholder="Email subject..." value="<?= h($_POST['subject'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label>Sender Email <span class="text-accent">*</span></label>
                        <input type="text" name="sender" class="form-control glow-input" placeholder="sender@domain.com" value="<?= h($_POST['sender'] ?? '') ?>" required>
                    </div>
                    <div class="col-12">
                        <label>Email Body <span class="text-accent">*</span></label>
                        <textarea name="body" class="form-control glow-input" rows="7" placeholder="Paste the full email body here..." required><?= h($_POST['body'] ?? '') ?></textarea>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-cyber">
                            <i class="bi bi-search me-2"></i>Analyze Email
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <?php if ($result): ?>
        <div id="scanResult">
            <div class="result-box <?= $result['res_label'] ?> mb-4">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div style="font-size:2.5rem;">
                        <?= $result['res_label']==='safe' ? '✅' : ($result['res_label']==='phishing' ? '🚨' : '⚠️') ?>
                    </div>
                    <div>
                        <h4 class="mb-1 fw-bold">
                            <?= $result['res_label']==='safe' ? 'Email Looks Safe' : ($result['res_label']==='phishing' ? 'Phishing Email Detected!' : 'Suspicious Email') ?>
                        </h4>
                        <div class="text-muted small">From: <?= h($result['sender']) ?></div>
                    </div>
                    <div class="ms-auto"><?= result_badge($result['res_label']) ?></div>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between small mb-1">
                        <span class="text-muted">Risk Score</span>
                        <span class="fw-bold"><?= $result['score'] ?>/100</span>
                    </div>
                    <div class="risk-bar">
                        <div class="risk-fill <?= $result['res_label'] ?>" data-width="<?= $result['score'] ?>"></div>
                    </div>
                </div>
                <div class="small fw-semibold text-white mb-1">Detection Details:</div>
                <div class="text-muted small"><?= h($result['details']) ?></div>
            </div>
            <div class="ps-card mb-4">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <i class="bi bi-robot text-accent fs-5"></i>
                    <h6 class="fw-bold mb-0">Gemini AI Analysis</h6>
                </div>
                <p class="text-muted small mb-0" style="line-height:1.7;"><?= nl2br(h($result['ai_analysis'])) ?></p>
            </div>
            <div class="d-flex gap-2">
                <a href="?" class="btn btn-cyber btn-sm"><i class="bi bi-arrow-repeat me-1"></i>Scan Another</a>
                <a href="<?= SITE_URL ?>/user/history.php" class="btn btn-outline-accent btn-sm"><i class="bi bi-clock-history me-1"></i>View History</a>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
</div>
<?php include '../includes/footer.php'; ?>
