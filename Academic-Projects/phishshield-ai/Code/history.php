<?php
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/config/functions.php';
require_login();
$user = current_user($pdo);
$uid  = $user['id'];

// Delete
if (isset($_GET['del'], $_GET['type'])) {
    $id = (int)$_GET['del'];
    $tbl = $_GET['type'] === 'email' ? 'email_scans' : 'url_scans';
    $stmt = $pdo->prepare("DELETE FROM $tbl WHERE id=? AND user_id=?");
    $stmt->execute([$id, $uid]);
    header("Location: history.php"); exit;
}

$filter = $_GET['filter'] ?? 'all';   // all|url|email
$search = trim($_GET['q'] ?? '');

$rows = [];

$urlSql = "SELECT id, 'URL' AS type, url AS input, result, risk_score, created_at FROM url_scans WHERE user_id=?";
$emlSql = "SELECT id, 'Email' AS type, LEFT(content,120) AS input, result, risk_score, created_at FROM email_scans WHERE user_id=?";

$queries = [];
if ($filter === 'url' || $filter === 'all')   $queries[] = $urlSql;
if ($filter === 'email' || $filter === 'all') $queries[] = $emlSql;

$sql = "SELECT * FROM (" . implode(" UNION ALL ", $queries) . ") t";
$params = array_fill(0, count($queries), $uid);
if ($search !== '') {
    $sql .= " WHERE input LIKE ?";
    $params[] = "%$search%";
}
$sql .= " ORDER BY created_at DESC LIMIT 200";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll();

$pageTitle = 'History';
include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';
?>
<div class="ps-app">
  <?php include __DIR__ . '/includes/sidebar.php'; ?>
  <main class="ps-main fade-in">
    <div class="ps-page-head">
      <h1><i class="fa-solid fa-clock-rotate-left text-cyan"></i> Scan History</h1>
    </div>

    <form class="ps-card mb-3" method="get">
      <div class="row g-2 align-items-end">
        <div class="col-sm-3">
          <label class="form-label small">Type</label>
          <select name="filter" class="form-select">
            <option value="all"   <?= $filter==='all'  ?'selected':'' ?>>All</option>
            <option value="url"   <?= $filter==='url'  ?'selected':'' ?>>URL</option>
            <option value="email" <?= $filter==='email'?'selected':'' ?>>Email</option>
          </select>
        </div>
        <div class="col-sm-7">
          <label class="form-label small">Search</label>
          <input name="q" class="form-control" value="<?= e($search) ?>" placeholder="Search input...">
        </div>
        <div class="col-sm-2"><button class="btn btn-grad w-100">Filter</button></div>
      </div>
    </form>

    <div class="ps-card">
      <div class="table-responsive">
        <table class="table align-middle">
          <thead><tr><th>Date</th><th>Type</th><th>Input</th><th>Result</th><th>Risk</th><th></th></tr></thead>
          <tbody>
          <?php if (!$rows): ?>
            <tr><td colspan="6" class="text-center text-muted py-4">No matching records.</td></tr>
          <?php else: foreach ($rows as $r):
            $cls = match($r['result']){
              'Safe','Legitimate'=>'badge-safe',
              'Suspicious'=>'badge-suspicious',
              default=>'badge-phishing'
            };
          ?>
            <tr>
              <td class="small text-muted"><?= e($r['created_at']) ?></td>
              <td><?= e($r['type']) ?></td>
              <td class="text-truncate" style="max-width:380px;"><?= e($r['input']) ?></td>
              <td><span class="badge-pill <?= $cls ?>"><?= e($r['result']) ?></span></td>
              <td style="min-width:140px;"><div class="risk-meter"><span style="width:<?= (int)$r['risk_score'] ?>%"></span></div><small class="text-muted"><?= (int)$r['risk_score'] ?>%</small></td>
              <td><a class="btn btn-sm btn-outline-danger" href="?del=<?= (int)$r['id'] ?>&type=<?= strtolower($r['type'])==='email'?'email':'url' ?>" onclick="return confirm('Delete this record?')"><i class="fa-solid fa-trash"></i></a></td>
            </tr>
          <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </main>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
