<?php
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/config/functions.php';
require_once __DIR__ . '/config/gemini.php';
require_login();
$user = current_user($pdo);

$urlResult = null;
$emailResult = null;
$activeTab = 'url';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Diagnostic: log incoming POSTs to a local debug file to help trace
  // (remove this in production once issue is resolved)
  @file_put_contents(__DIR__ . '/scan-debug.log', date('c') . " " . json_encode(array_keys($_POST)) . PHP_EOL, FILE_APPEND | LOCK_EX);
    // ----- URL SCAN -----
    if (isset($_POST['scan_url'])) {
        $activeTab = 'url';
        $url = trim($_POST['url'] ?? '');
        if ($url === '') {
            $urlResult = ['error' => 'Please enter a URL.'];
        } else {
            $domain = get_domain($url);

            // 1. Blacklist
            $stmt = $pdo->prepare("SELECT 1 FROM blacklist WHERE domain = ?");
            $stmt->execute([$domain]);
            $isBlack = (bool)$stmt->fetchColumn();

            // 2. Whitelist
            $stmt = $pdo->prepare("SELECT 1 FROM whitelist WHERE domain = ?");
            $stmt->execute([$domain]);
            $isWhite = (bool)$stmt->fetchColumn();

            $reasons = [];
            $score = 0;
            $verdict = null;
            $source = 'hybrid';
            $recommendation = '';

            if ($isBlack) {
                $verdict = 'Phishing'; $score = 100; $reasons = ['Domain found in blacklist database.'];
                $source = 'blacklist'; $recommendation = 'Do NOT visit. Report it to your IT team.';
            } elseif ($isWhite) {
                $verdict = 'Safe'; $score = 5; $reasons = ['Domain is in trusted whitelist.'];
                $source = 'whitelist'; $recommendation = 'This domain is verified as safe.';
            } else {
                // 3. Heuristics
                $h = url_heuristics($url);
                $score = $h['score']; $reasons = $h['reasons'];

                // 4. Gemini AI
                $ai = analyzeURL($url);
                if (!empty($ai['verdict'])) {
                    $aiScore = (int)($ai['risk_score'] ?? 0);
                    // Combine: average heuristic + AI, take the higher of verdicts
                    $score = (int)round(($score + $aiScore) / 2);
                    if (!empty($ai['reasons']) && is_array($ai['reasons'])) {
                        $reasons = array_merge($reasons, array_map('strval', $ai['reasons']));
                    }
                    $recommendation = $ai['recommendation'] ?? '';
                    $verdict = $ai['verdict'];
                    $source = 'heuristic+ai';
                } else {
                    $verdict = classify_url($score);
                    $source = 'heuristic';
                    if (!empty($ai['error'])) $reasons[] = 'AI note: ' . $ai['error'];
                }
                if ($recommendation === '') {
                    $recommendation = $verdict === 'Safe'
                        ? 'No major red flags found, but always stay cautious.'
                        : 'Avoid entering credentials. Verify the domain through official channels.';
                }
            }

            $stmt = $pdo->prepare("INSERT INTO url_scans (user_id,url,result,risk_score,reasons,recommendation,source) VALUES (?,?,?,?,?,?,?)");
            $stmt->execute([$user['id'], $url, $verdict, $score, implode("\n", $reasons), $recommendation, $source]);

            $urlResult = compact('url','verdict','score','reasons','recommendation','source');
        }
    }
    // ----- EMAIL SCAN -----
    if (isset($_POST['scan_email'])) {
        $activeTab = 'email';
        $content = trim($_POST['content'] ?? '');
        if ($content === '') {
            $emailResult = ['error' => 'Please paste email content.'];
        } else {
            $h = email_heuristics($content);
            $score = $h['score']; $reasons = $h['reasons'];
            $verdict = null;

            $ai = analyzeEmail($content);
            if (!empty($ai['verdict'])) {
                $aiScore = (int)($ai['risk_score'] ?? 0);
                $score = (int)round(($score + $aiScore) / 2);
                if (!empty($ai['reasons']) && is_array($ai['reasons'])) {
                    $reasons = array_merge($reasons, array_map('strval', $ai['reasons']));
                }
                $verdict = $ai['verdict'];
            } else {
                $verdict = classify_email($score);
                if (!empty($ai['error'])) $reasons[] = 'AI note: ' . $ai['error'];
            }

            $stmt = $pdo->prepare("INSERT INTO email_scans (user_id,content,result,risk_score,reasons) VALUES (?,?,?,?,?)");
            $stmt->execute([$user['id'], $content, $verdict, $score, implode("\n",$reasons)]);

            $emailResult = compact('content','verdict','score','reasons');
        }
    }
}

function badgeFor($v) {
    return match($v){
        'Safe','Legitimate'=>'badge-safe',
        'Suspicious'=>'badge-suspicious',
        'Phishing'=>'badge-phishing',
        default => 'badge-suspicious'
    };
}

$pageTitle = 'Scanner';
include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';
?>
<div class="ps-app">
  <?php include __DIR__ . '/includes/sidebar.php'; ?>
  <main class="ps-main fade-in">
    <div class="ps-page-head"><h1><i class="fa-solid fa-magnifying-glass text-cyan"></i> Phishing Scanner</h1></div>

    <ul class="nav nav-tabs mb-3" role="tablist">
      <li class="nav-item"><button class="nav-link <?= $activeTab==='url'?'active':'' ?>" data-bs-toggle="tab" data-bs-target="#tab-url">URL Scanner</button></li>
      <li class="nav-item"><button class="nav-link <?= $activeTab==='email'?'active':'' ?>" data-bs-toggle="tab" data-bs-target="#tab-email">Email Scanner</button></li>
    </ul>

    <div class="tab-content">
      <!-- URL TAB -->
      <div class="tab-pane fade <?= $activeTab==='url'?'show active':'' ?>" id="tab-url">
        <div class="ps-card mb-3">
          <form id="urlScanForm" method="post">
            <input type="hidden" name="scan_url" value="1">
            <label class="form-label">Enter a suspicious URL</label>
            <div class="input-group">
              <input name="url" class="form-control" placeholder="https://example.com/login" value="<?= e($urlResult['url'] ?? '') ?>" required>
              <button type="submit" name="scan_url" value="1" class="btn btn-grad"><i class="fa-solid fa-shield-halved"></i> Scan URL</button>
            </div>
          </form>
        </div>

        <?php if ($urlResult): ?>
          <?php if (!empty($urlResult['error'])): ?>
            <div class="alert alert-danger"><?= e($urlResult['error']) ?></div>
          <?php else: ?>
            <div class="ps-card fade-in">
              <div class="d-flex justify-content-between align-items-center mb-2">
                <h5 class="mb-0">Result</h5>
                <span class="badge-pill <?= badgeFor($urlResult['verdict']) ?>"><?= e($urlResult['verdict']) ?></span>
              </div>
              <p class="text-muted small mb-1">Source: <?= e($urlResult['source']) ?></p>
              <p class="mb-2"><b>Risk Score:</b> <?= (int)$urlResult['score'] ?>%</p>
              <div class="risk-meter mb-3"><span style="width:<?= (int)$urlResult['score'] ?>%"></span></div>
              <h6>Reasons</h6>
              <ul><?php foreach ($urlResult['reasons'] as $r): ?><li><?= e($r) ?></li><?php endforeach; ?></ul>
              <?php if (!empty($urlResult['recommendation'])): ?>
                <div class="alert alert-info mt-3 mb-0"><i class="fa-solid fa-circle-info"></i> <?= e($urlResult['recommendation']) ?></div>
              <?php endif; ?>
            </div>
          <?php endif; ?>
        <?php endif; ?>
      </div>

      <!-- EMAIL TAB -->
      <div class="tab-pane fade <?= $activeTab==='email'?'show active':'' ?>" id="tab-email">
        <div class="ps-card mb-3">
          <form id="emailScanForm" method="post">
            <input type="hidden" name="scan_email" value="1">
            <label class="form-label">Paste full email content (subject + body)</label>
            <textarea name="content" class="form-control" rows="8" required><?= e($emailResult['content'] ?? '') ?></textarea>
            <button type="submit" name="scan_email" value="1" class="btn btn-grad mt-3"><i class="fa-solid fa-envelope-open-text"></i> Scan Email</button>
          </form>
        </div>

        <?php if ($emailResult): ?>
          <?php if (!empty($emailResult['error'])): ?>
            <div class="alert alert-danger"><?= e($emailResult['error']) ?></div>
          <?php else: ?>
            <div class="ps-card fade-in">
              <div class="d-flex justify-content-between align-items-center mb-2">
                <h5 class="mb-0">Result</h5>
                <span class="badge-pill <?= badgeFor($emailResult['verdict']) ?>"><?= e($emailResult['verdict']) ?></span>
              </div>
              <p class="mb-2"><b>Risk Score:</b> <?= (int)$emailResult['score'] ?>%</p>
              <div class="risk-meter mb-3"><span style="width:<?= (int)$emailResult['score'] ?>%"></span></div>
              <h6>Suspicious Indicators</h6>
              <ul><?php foreach ($emailResult['reasons'] as $r): ?><li><?= e($r) ?></li><?php endforeach; ?></ul>
            </div>
          <?php endif; ?>
        <?php endif; ?>
      </div>
    </div>
  </main>
</div>
<script src="<?= APP_URL ?>/assets/js/scanner.js"></script>
<?php include __DIR__ . '/includes/footer.php'; ?>
