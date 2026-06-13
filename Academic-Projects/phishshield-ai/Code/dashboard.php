<?php
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/config/functions.php';
require_login();
$user = current_user($pdo);

// Stats
$uid = $user['id'];
$total   = $pdo->prepare("SELECT (SELECT COUNT(*) FROM url_scans WHERE user_id=?) + (SELECT COUNT(*) FROM email_scans WHERE user_id=?) AS c");
$total->execute([$uid,$uid]); $total = (int)$total->fetch()['c'];

$safe = $pdo->prepare("SELECT (SELECT COUNT(*) FROM url_scans WHERE user_id=? AND result='Safe') + (SELECT COUNT(*) FROM email_scans WHERE user_id=? AND result='Legitimate') AS c");
$safe->execute([$uid,$uid]); $safe = (int)$safe->fetch()['c'];

$phish = $pdo->prepare("SELECT (SELECT COUNT(*) FROM url_scans WHERE user_id=? AND result='Phishing') + (SELECT COUNT(*) FROM email_scans WHERE user_id=? AND result='Phishing') AS c");
$phish->execute([$uid,$uid]); $phish = (int)$phish->fetch()['c'];

$susp = max(0, $total - $safe - $phish);

// Monthly trend (last 6 months) for url_scans + email_scans
$months = [];
for ($i=5; $i>=0; $i--) {
    $months[date('Y-m', strtotime("-$i months"))] = 0;
}
$stmt = $pdo->prepare("
  SELECT DATE_FORMAT(created_at,'%Y-%m') ym, COUNT(*) c FROM (
    SELECT created_at FROM url_scans WHERE user_id=?
    UNION ALL
    SELECT created_at FROM email_scans WHERE user_id=?
  ) t GROUP BY ym
");
$stmt->execute([$uid,$uid]);
foreach ($stmt->fetchAll() as $r) if (isset($months[$r['ym']])) $months[$r['ym']] = (int)$r['c'];

// Recent activity
$recent = $pdo->prepare("
  SELECT * FROM (
    SELECT id, 'URL' AS type, url AS input, result, risk_score, created_at FROM url_scans WHERE user_id=?
    UNION ALL
    SELECT id, 'Email' AS type, LEFT(content,80) AS input, result, risk_score, created_at FROM email_scans WHERE user_id=?
  ) r ORDER BY created_at DESC LIMIT 8
");
$recent->execute([$uid,$uid]);
$recent = $recent->fetchAll();

$pageTitle = 'Dashboard';
include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';
?>
<div class="ps-app">
  <?php include __DIR__ . '/includes/sidebar.php'; ?>
  <main class="ps-main fade-in">
    <div class="ps-page-head">
      <div>
        <h1>Welcome back, <?= e($user['name']) ?> 👋</h1>
        <p class="text-muted mb-0 small">Here's a snapshot of your phishing protection.</p>
      </div>
      <a href="scanner.php" class="btn btn-grad"><i class="fa-solid fa-magnifying-glass"></i> New Scan</a>
    </div>

    <div class="row g-3 mb-4">
      <div class="col-sm-6 col-lg-3"><div class="ps-card ps-stat"><div class="ico"><i class="fa-solid fa-chart-simple"></i></div><div><div class="num"><?= $total ?></div><div class="lbl">Total Scans</div></div></div></div>
      <div class="col-sm-6 col-lg-3"><div class="ps-card ps-stat"><div class="ico" style="background:#22c55e"><i class="fa-solid fa-shield"></i></div><div><div class="num"><?= $safe ?></div><div class="lbl">Safe</div></div></div></div>
      <div class="col-sm-6 col-lg-3"><div class="ps-card ps-stat"><div class="ico" style="background:#f59e0b"><i class="fa-solid fa-triangle-exclamation"></i></div><div><div class="num"><?= $susp ?></div><div class="lbl">Suspicious</div></div></div></div>
      <div class="col-sm-6 col-lg-3"><div class="ps-card ps-stat"><div class="ico" style="background:#ef4444"><i class="fa-solid fa-skull-crossbones"></i></div><div><div class="num"><?= $phish ?></div><div class="lbl">Phishing</div></div></div></div>
    </div>

    <div class="row g-3 mb-4">
      <div class="col-lg-5"><div class="ps-card"><h6 class="mb-3">Detection Breakdown</h6><canvas id="pieChart" height="220"></canvas></div></div>
      <div class="col-lg-7"><div class="ps-card"><h6 class="mb-3">Monthly Scan Trend</h6><canvas id="lineChart" height="220"></canvas></div></div>
    </div>

    <div class="ps-card">
      <h6 class="mb-3">Recent Activity</h6>
      <div class="table-responsive">
        <table class="table align-middle">
          <thead><tr><th>Date</th><th>Type</th><th>Input</th><th>Result</th><th>Risk</th></tr></thead>
          <tbody>
          <?php if (!$recent): ?>
            <tr><td colspan="5" class="text-center text-muted py-4">No scans yet — try the <a href="scanner.php" class="text-cyan">scanner</a>.</td></tr>
          <?php else: foreach ($recent as $r):
            $cls = match($r['result']){
              'Safe','Legitimate'=>'badge-safe',
              'Suspicious'=>'badge-suspicious',
              default=>'badge-phishing'
            };
          ?>
            <tr>
              <td class="small text-muted"><?= e($r['created_at']) ?></td>
              <td><?= e($r['type']) ?></td>
              <td class="text-truncate" style="max-width:340px;"><?= e($r['input']) ?></td>
              <td><span class="badge-pill <?= $cls ?>"><?= e($r['result']) ?></span></td>
              <td style="min-width:140px;"><div class="risk-meter"><span style="width:<?= (int)$r['risk_score'] ?>%"></span></div><small class="text-muted"><?= (int)$r['risk_score'] ?>%</small></td>
            </tr>
          <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </main>
</div>

<script src="<?= APP_URL ?>/assets/js/charts.js"></script>
<script>
psPieChart(document.getElementById('pieChart'),
  ['Safe','Suspicious','Phishing'],
  [<?= $safe ?>,<?= $susp ?>,<?= $phish ?>],
  ['#22c55e','#f59e0b','#ef4444']);
psLineChart(document.getElementById('lineChart'),
  <?= json_encode(array_keys($months)) ?>,
  <?= json_encode(array_values($months)) ?>,
  'Scans');
</script>
<?php include __DIR__ . '/includes/footer.php'; ?>
