<?php
require_once __DIR__ . '/config/auth.php';
require_once __DIR__ . '/config/functions.php';
require_admin();

$page = $_GET['page'] ?? 'dashboard';

// ---------- ACTIONS ----------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_blacklist'])) {
        $d = strtolower(trim($_POST['domain']));
        $r = trim($_POST['reason'] ?? '');
        if ($d) { $stmt = $pdo->prepare("INSERT IGNORE INTO blacklist (domain,reason) VALUES (?,?)"); $stmt->execute([$d,$r]); }
        header("Location: admin.php?page=blacklist"); exit;
    }
    if (isset($_POST['add_whitelist'])) {
        $d = strtolower(trim($_POST['domain']));
        if ($d) { $stmt = $pdo->prepare("INSERT IGNORE INTO whitelist (domain) VALUES (?)"); $stmt->execute([$d]); }
        header("Location: admin.php?page=whitelist"); exit;
    }
}
if (isset($_GET['del_user']))      { $pdo->prepare("DELETE FROM users WHERE id=?")->execute([(int)$_GET['del_user']]); header("Location: admin.php?page=users"); exit; }
if (isset($_GET['toggle_block'])) {
    $stmt = $pdo->prepare("UPDATE users SET is_blocked = 1 - is_blocked WHERE id=?");
    $stmt->execute([(int)$_GET['toggle_block']]);
    header("Location: admin.php?page=users"); exit;
}
if (isset($_GET['del_bl'])) { $pdo->prepare("DELETE FROM blacklist WHERE id=?")->execute([(int)$_GET['del_bl']]); header("Location: admin.php?page=blacklist"); exit; }
if (isset($_GET['del_wl'])) { $pdo->prepare("DELETE FROM whitelist WHERE id=?")->execute([(int)$_GET['del_wl']]); header("Location: admin.php?page=whitelist"); exit; }
if (isset($_GET['del_fb'])) { $pdo->prepare("DELETE FROM feedback WHERE id=?")->execute([(int)$_GET['del_fb']]); header("Location: admin.php?page=feedback"); exit; }

$pageTitle = 'Admin · ' . ucfirst($page);
include __DIR__ . '/includes/header.php';
?>
<nav class="navbar ps-navbar">
  <div class="container-fluid">
    <a class="navbar-brand fw-bold" href="admin.php"><i class="fa-solid fa-user-shield text-cyan"></i> PhishShield <span class="text-cyan">Admin</span></a>
    <div class="ms-auto"><a href="logout.php" class="btn btn-sm btn-danger">Logout</a></div>
  </div>
</nav>

<div class="ps-app">
  <aside class="ps-sidebar">
    <div class="ps-side-brand"><i class="fa-solid fa-user-shield"></i><span>Admin</span></div>
    <ul class="ps-side-menu">
      <?php
      $tabs = [
        'dashboard'=>['fa-gauge','Dashboard'],
        'users'=>['fa-users','Users'],
        'url_logs'=>['fa-link','URL Logs'],
        'email_logs'=>['fa-envelope','Email Logs'],
        'blacklist'=>['fa-ban','Blacklist'],
        'whitelist'=>['fa-circle-check','Whitelist'],
        'feedback'=>['fa-comment-dots','Feedback'],
        'analytics'=>['fa-chart-line','Analytics'],
        'reports'=>['fa-file-export','Reports'],
      ];
      foreach ($tabs as $k=>$t): ?>
        <li><a href="?page=<?= $k ?>" class="<?= $page===$k?'active':'' ?>"><i class="fa-solid <?= $t[0] ?>"></i> <span><?= $t[1] ?></span></a></li>
      <?php endforeach; ?>
    </ul>
  </aside>

  <main class="ps-main fade-in">
  <?php
  if ($page === 'dashboard') {
      $totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
      $totalScans = $pdo->query("SELECT (SELECT COUNT(*) FROM url_scans)+(SELECT COUNT(*) FROM email_scans)")->fetchColumn();
      $phish      = $pdo->query("SELECT (SELECT COUNT(*) FROM url_scans WHERE result='Phishing')+(SELECT COUNT(*) FROM email_scans WHERE result='Phishing')")->fetchColumn();
      $safe       = $pdo->query("SELECT (SELECT COUNT(*) FROM url_scans WHERE result='Safe')+(SELECT COUNT(*) FROM email_scans WHERE result='Legitimate')")->fetchColumn();
      $accuracy   = $totalScans > 0 ? round((($safe+$phish)/$totalScans)*100, 1) : 0;
      ?>
      <h1 class="mb-4">Admin Overview</h1>
      <div class="row g-3 mb-4">
        <div class="col-sm-6 col-lg-3"><div class="ps-card ps-stat"><div class="ico"><i class="fa-solid fa-users"></i></div><div><div class="num"><?= (int)$totalUsers ?></div><div class="lbl">Total Users</div></div></div></div>
        <div class="col-sm-6 col-lg-3"><div class="ps-card ps-stat"><div class="ico" style="background:#3b82f6"><i class="fa-solid fa-chart-simple"></i></div><div><div class="num"><?= (int)$totalScans ?></div><div class="lbl">Total Scans</div></div></div></div>
        <div class="col-sm-6 col-lg-3"><div class="ps-card ps-stat"><div class="ico" style="background:#ef4444"><i class="fa-solid fa-skull-crossbones"></i></div><div><div class="num"><?= (int)$phish ?></div><div class="lbl">Phishing Detected</div></div></div></div>
        <div class="col-sm-6 col-lg-3"><div class="ps-card ps-stat"><div class="ico" style="background:#22c55e"><i class="fa-solid fa-bullseye"></i></div><div><div class="num"><?= $accuracy ?>%</div><div class="lbl">Decisive Verdicts</div></div></div></div>
      </div>
      <div class="row g-3"><div class="col-lg-12"><div class="ps-card"><h6>Detection ratio</h6><canvas id="adminPie" height="120"></canvas></div></div></div>
      <script src="<?= APP_URL ?>/assets/js/charts.js"></script>
      <script>psPieChart(document.getElementById('adminPie'),['Safe','Phishing','Other'],
        [<?= (int)$safe ?>,<?= (int)$phish ?>,<?= (int)$totalScans-(int)$safe-(int)$phish ?>],
        ['#22c55e','#ef4444','#f59e0b']);</script>
      <?php
  }

  elseif ($page === 'users') {
      $users = $pdo->query("SELECT * FROM users ORDER BY id DESC")->fetchAll();
      ?>
      <h1>Users</h1>
      <div class="ps-card mt-3"><div class="table-responsive"><table class="table align-middle">
        <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Status</th><th>Joined</th><th></th></tr></thead><tbody>
        <?php foreach ($users as $u): ?>
          <tr>
            <td><?= (int)$u['id'] ?></td>
            <td><?= e($u['name']) ?></td>
            <td><?= e($u['email']) ?></td>
            <td><?= $u['is_blocked'] ? '<span class="badge-pill badge-phishing">Blocked</span>' : '<span class="badge-pill badge-safe">Active</span>' ?></td>
            <td class="small text-muted"><?= e($u['created_at']) ?></td>
            <td>
              <a href="?page=users&toggle_block=<?= (int)$u['id'] ?>" class="btn btn-sm btn-outline-warning"><?= $u['is_blocked']?'Unblock':'Block' ?></a>
              <a href="?page=users&del_user=<?= (int)$u['id'] ?>" onclick="return confirm('Delete this user?')" class="btn btn-sm btn-outline-danger">Delete</a>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody></table></div></div>
      <?php
  }

  elseif ($page === 'url_logs' || $page === 'email_logs') {
      $isUrl = $page === 'url_logs';
      $tbl = $isUrl ? 'url_scans' : 'email_scans';
      $col = $isUrl ? 'url' : 'content';
      $rows = $pdo->query("SELECT s.*, u.name FROM $tbl s LEFT JOIN users u ON u.id=s.user_id ORDER BY s.id DESC LIMIT 200")->fetchAll();
      ?>
      <h1><?= $isUrl ? 'URL Scan Logs' : 'Email Scan Logs' ?></h1>
      <div class="ps-card mt-3"><div class="table-responsive"><table class="table align-middle">
        <thead><tr><th>Date</th><th>User</th><th><?= $isUrl?'URL':'Content' ?></th><th>Result</th><th>Risk</th></tr></thead><tbody>
        <?php foreach ($rows as $r):
          $cls = match($r['result']){'Safe','Legitimate'=>'badge-safe','Suspicious'=>'badge-suspicious',default=>'badge-phishing'};
        ?>
          <tr>
            <td class="small text-muted"><?= e($r['created_at']) ?></td>
            <td><?= e($r['name'] ?? '—') ?></td>
            <td class="text-truncate" style="max-width:380px;"><?= e(mb_substr($r[$col],0,180)) ?></td>
            <td><span class="badge-pill <?= $cls ?>"><?= e($r['result']) ?></span></td>
            <td><?= (int)$r['risk_score'] ?>%</td>
          </tr>
        <?php endforeach; ?>
        </tbody></table></div></div>
      <?php
  }

  elseif ($page === 'blacklist') {
      $rows = $pdo->query("SELECT * FROM blacklist ORDER BY id DESC")->fetchAll();
      ?>
      <h1>Blacklist</h1>
      <form method="post" class="ps-card my-3">
        <div class="row g-2">
          <div class="col-md-5"><input class="form-control" name="domain" placeholder="malicious.com" required></div>
          <div class="col-md-5"><input class="form-control" name="reason" placeholder="Reason"></div>
          <div class="col-md-2"><button name="add_blacklist" value="1" class="btn btn-grad w-100">Add</button></div>
        </div>
      </form>
      <div class="ps-card"><div class="table-responsive"><table class="table align-middle">
        <thead><tr><th>Domain</th><th>Reason</th><th>Added</th><th></th></tr></thead><tbody>
        <?php foreach ($rows as $r): ?>
          <tr><td><?= e($r['domain']) ?></td><td><?= e($r['reason']) ?></td><td class="small text-muted"><?= e($r['created_at']) ?></td>
              <td><a href="?page=blacklist&del_bl=<?= (int)$r['id'] ?>" class="btn btn-sm btn-outline-danger">Delete</a></td></tr>
        <?php endforeach; ?>
        </tbody></table></div></div>
      <?php
  }

  elseif ($page === 'whitelist') {
      $rows = $pdo->query("SELECT * FROM whitelist ORDER BY id DESC")->fetchAll();
      ?>
      <h1>Whitelist</h1>
      <form method="post" class="ps-card my-3">
        <div class="row g-2">
          <div class="col-md-10"><input class="form-control" name="domain" placeholder="trusted.com" required></div>
          <div class="col-md-2"><button name="add_whitelist" value="1" class="btn btn-grad w-100">Add</button></div>
        </div>
      </form>
      <div class="ps-card"><div class="table-responsive"><table class="table align-middle">
        <thead><tr><th>Domain</th><th>Added</th><th></th></tr></thead><tbody>
        <?php foreach ($rows as $r): ?>
          <tr><td><?= e($r['domain']) ?></td><td class="small text-muted"><?= e($r['created_at']) ?></td>
              <td><a href="?page=whitelist&del_wl=<?= (int)$r['id'] ?>" class="btn btn-sm btn-outline-danger">Delete</a></td></tr>
        <?php endforeach; ?>
        </tbody></table></div></div>
      <?php
  }

  elseif ($page === 'feedback') {
      $rows = $pdo->query("SELECT f.*, u.name, u.email FROM feedback f LEFT JOIN users u ON u.id=f.user_id ORDER BY f.id DESC")->fetchAll();
      ?>
      <h1>User Feedback</h1>
      <div class="ps-card mt-3">
      <?php if (!$rows): ?><p class="text-muted">No feedback yet.</p><?php else: foreach ($rows as $f): ?>
        <div class="border-bottom border-secondary py-2">
          <div class="d-flex justify-content-between">
            <b><?= e($f['name'] ?? '—') ?> <small class="text-muted">&lt;<?= e($f['email'] ?? '') ?>&gt;</small></b>
            <span><?= str_repeat('⭐',(int)$f['rating']) ?>
              <a href="?page=feedback&del_fb=<?= (int)$f['id'] ?>" class="btn btn-sm btn-outline-danger ms-2">×</a>
            </span>
          </div>
          <div class="small text-muted"><?= e($f['created_at']) ?></div>
          <div><?= nl2br(e($f['message'])) ?></div>
        </div>
      <?php endforeach; endif; ?>
      </div>
      <?php
  }

  elseif ($page === 'analytics') {
      $months = [];
      for ($i=5; $i>=0; $i--) $months[date('Y-m', strtotime("-$i months"))] = 0;
      $stmt = $pdo->query("
        SELECT DATE_FORMAT(created_at,'%Y-%m') ym, COUNT(*) c FROM (
          SELECT created_at FROM url_scans
          UNION ALL
          SELECT created_at FROM email_scans
        ) t GROUP BY ym
      ");
      foreach ($stmt as $r) if (isset($months[$r['ym']])) $months[$r['ym']] = (int)$r['c'];
      $safe  = (int)$pdo->query("SELECT (SELECT COUNT(*) FROM url_scans WHERE result='Safe')+(SELECT COUNT(*) FROM email_scans WHERE result='Legitimate')")->fetchColumn();
      $susp  = (int)$pdo->query("SELECT (SELECT COUNT(*) FROM url_scans WHERE result='Suspicious')+(SELECT COUNT(*) FROM email_scans WHERE result='Suspicious')")->fetchColumn();
      $phish = (int)$pdo->query("SELECT (SELECT COUNT(*) FROM url_scans WHERE result='Phishing')+(SELECT COUNT(*) FROM email_scans WHERE result='Phishing')")->fetchColumn();
      ?>
      <h1>Analytics</h1>
      <div class="row g-3 mt-2">
        <div class="col-lg-7"><div class="ps-card"><h6>Monthly Trend</h6><canvas id="trend" height="220"></canvas></div></div>
        <div class="col-lg-5"><div class="ps-card"><h6>Detection Ratio</h6><canvas id="ratio" height="220"></canvas></div></div>
      </div>
      <script src="<?= APP_URL ?>/assets/js/charts.js"></script>
      <script>
        psLineChart(document.getElementById('trend'), <?= json_encode(array_keys($months)) ?>, <?= json_encode(array_values($months)) ?>, 'Scans');
        psPieChart(document.getElementById('ratio'), ['Safe','Suspicious','Phishing'], [<?= $safe ?>,<?= $susp ?>,<?= $phish ?>], ['#22c55e','#f59e0b','#ef4444']);
      </script>
      <?php
  }

  elseif ($page === 'reports') {
      ?>
      <h1>Reports</h1>
      <p class="text-muted">Export scan data for offline analysis.</p>
      <div class="ps-card">
        <div class="d-flex flex-wrap gap-2">
          <a href="reports.php?type=url_scans"   class="btn btn-grad"><i class="fa-solid fa-file-csv"></i> URL Scans CSV</a>
          <a href="reports.php?type=email_scans" class="btn btn-grad"><i class="fa-solid fa-file-csv"></i> Email Scans CSV</a>
          <a href="reports.php?type=users"       class="btn btn-grad"><i class="fa-solid fa-file-csv"></i> Users CSV</a>
          <a href="reports.php?type=feedback"    class="btn btn-grad"><i class="fa-solid fa-file-csv"></i> Feedback CSV</a>
        </div>
      </div>
      <?php
  }
  ?>
  </main>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
