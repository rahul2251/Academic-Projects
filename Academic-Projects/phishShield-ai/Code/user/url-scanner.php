<?php
require_once '../config/config.php';
require_once '../config/db.php';
require_once '../config/auth.php';
require_once '../config/gemini.php';
require_once '../includes/functions.php';
require_user_login();

$uid    = $_SESSION['user_id'];
$result = null;
$error  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $url = trim($_POST['url'] ?? '');
    if (empty($url)) {
        $error = 'Please enter a URL.';
    } elseif (!filter_var($url, FILTER_VALIDATE_URL)) {
        $error = 'Please enter a valid URL (include http:// or https://).';
    } else {
        $risk = calculate_url_risk($url, $conn);
        $score = $risk['score'];
        $details = $risk['details'];
        $res_label = score_to_result($score);
        $ai_analysis = gemini_analyze_url($url);

        // Save to DB
        $stmt = $conn->prepare("INSERT INTO url_scans (user_id, url, result, risk_score, details, ai_analysis) VALUES (?,?,?,?,?,?)");
        $stmt->bind_param("ississs", $uid, $url, $res_label, $score, $details, $ai_analysis);
        $stmt->execute();
        $scan_id = $stmt->insert_id;
        $stmt->close();

        log_activity($conn, 'URL Scan', "Scanned: $url | Result: $res_label", $uid);

        $result = compact('url','score','res_label','details','ai_analysis','scan_id');
    }
}

$page_title = 'URL Scanner';
$extra_css = '<link rel="stylesheet" href="' . SITE_URL . '/assets/css/dashboard.css">';
$extra_js = '<script src="' . SITE_URL . '/assets/js/scanner.js"></script>';
?>
<?php include '../includes/header.php'; ?>
<div class="ps-layout">
<?php include '../includes/sidebar.php'; ?>
<div class="ps-main">
    <div class="ps-topbar">
        <div>
            <div class="fw-semibold text-white">URL Scanner</div>
            <div class="text-muted small">Scan any URL for phishing threats</div>
        </div>
    </div>
    <div class="ps-content" style="max-width:800px;">
        <?php include '../includes/alerts.php'; ?>

        <div class="ps-card mb-4">
            <h5 class="fw-bold mb-1"><i class="bi bi-link-45deg text-accent me-2"></i>Enter URL to Scan</h5>
            <p class="text-muted small mb-3">Enter a full URL including http:// or https://</p>

            <?php if ($error): ?>
            <div class="alert alert-danger"><?= h($error) ?></div>
            <?php endif; ?>

            <form id="urlScanForm" method="POST">
                <div class="d-flex gap-2">
                    <div class="input-group flex-grow-1">
                        <span class="input-group-text"><i class="bi bi-globe"></i></span>
                        <input type="url" name="url" class="form-control glow-input"
                               placeholder="https://example.com/page"
                               value="<?= h($_POST['url'] ?? '') ?>" required>
                    </div>
                    <button type="submit" class="btn btn-cyber scan-pulse px-4">
                        <i class="bi bi-search me-2"></i>Scan
                    </button>
                </div>
            </form>
        </div>

        <?php if ($result): ?>
        <div id="scanResult">
            <!-- Result Box -->
            <div class="result-box <?= $result['res_label'] ?> mb-4">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div style="font-size:2.5rem;">
                        <?= $result['res_label']==='safe' ? '✅' : ($result['res_label']==='phishing' ? '🚨' : '⚠️') ?>
                    </div>
                    <div>
                        <h4 class="mb-1 fw-bold">
                            <?= $result['res_label']==='safe' ? 'URL is Safe' : ($result['res_label']==='phishing' ? 'Phishing Detected!' : 'Suspicious URL') ?>
                        </h4>
                        <div class="text-muted small text-break"><?= h($result['url']) ?></div>
                    </div>
                    <div class="ms-auto text-end">
                        <?= result_badge($result['res_label']) ?>
                    </div>
                </div>

                <!-- Risk Score -->
                <div class="mb-3">
                    <div class="d-flex justify-content-between small mb-1">
                        <span class="text-muted">Risk Score</span>
                        <span class="fw-bold <?= $result['score']>=80?'text-accent':($result['score']>=50?'':'text-cyan') ?>"><?= $result['score'] ?>/100</span>
                    </div>
                    <div class="risk-bar">
                        <div class="risk-fill <?= $result['res_label'] ?>" data-width="<?= $result['score'] ?>"></div>
                    </div>
                </div>

                <!-- Details -->
                <div class="mb-3">
                    <div class="small fw-semibold text-white mb-1">Detection Details:</div>
                    <div class="text-muted small"><?= h($result['details']) ?></div>
                </div>
            </div>

            <!-- AI Analysis -->
            <div class="ps-card mb-4">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <i class="bi bi-robot text-accent fs-5"></i>
                    <h6 class="fw-bold mb-0">Gemini AI Analysis</h6>
                </div>
                <p class="text-muted small mb-0" style="line-height:1.7;"><?= nl2br(h($result['ai_analysis'])) ?></p>
            </div>

            <div class="d-flex gap-2">
                <a href="<?= SITE_URL ?>/user/history.php" class="btn btn-outline-accent btn-sm">
                    <i class="bi bi-clock-history me-1"></i>View History
                </a>
                <a href="?" class="btn btn-cyber btn-sm">
                    <i class="bi bi-arrow-repeat me-1"></i>Scan Another
                </a>
            </div>
        </div>
        <?php endif; ?>

        <!-- Tips -->
        <?php if (!$result): ?>
        <div class="ps-card">
            <h6 class="fw-bold mb-3"><i class="bi bi-lightbulb text-accent me-2"></i>What We Check</h6>
            <div class="row g-3">
                <?php $checks = [['Domain Blacklist','We cross-reference thousands of known phishing domains'],['SSL Certificate','Valid HTTPS certificates indicate legitimacy'],['TLD Risk','Suspicious TLDs like .tk, .xyz are flagged'],['URL Patterns','We detect lookalike domains and URL tricks'],['Keyword Analysis','Suspicious phishing keywords in URLs are flagged'],['AI Analysis','Google Gemini provides deep threat analysis']]; ?>
                <?php foreach ($checks as $c): ?>
                <div class="col-md-6">
                    <div class="d-flex gap-2">
                        <i class="bi bi-check-circle-fill text-cyan mt-1 flex-shrink-0"></i>
                        <div><div class="small fw-semibold text-white"><?= $c[0] ?></div><div class="text-muted" style="font-size:.78rem;"><?= $c[1] ?></div></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
</div>
<?php include '../includes/footer.php'; ?>
