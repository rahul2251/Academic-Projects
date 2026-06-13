<?php
require_once '../config/config.php';
require_once '../config/db.php';
require_once '../config/auth.php';
require_once '../includes/functions.php';

session_start();

// CSV export
if (isset($_GET['export']) && $_GET['export'] === 'csv' && isset($_SESSION['user_id'])) {
    $uid = (int)$_SESSION['user_id'];
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="phishshield_scans_' . date('Y-m-d') . '.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['Type', 'Target', 'Result', 'Risk Score', 'Date']);
    $scans = $conn->query("SELECT 'URL' AS type, url AS target, result, risk_score, scanned_at FROM url_scans WHERE user_id=$uid UNION ALL SELECT 'Email', subject, result, risk_score, scanned_at FROM email_scans WHERE user_id=$uid ORDER BY scanned_at DESC");
    while ($r = $scans->fetch_assoc()) {
        fputcsv($out, [$r['type'], $r['target'], $r['result'], $r['risk_score'], $r['scanned_at']]);
    }
    fclose($out);
    exit();
}

// PDF export (simple HTML print version)
if (isset($_GET['export']) && $_GET['export'] === 'pdf' && isset($_SESSION['user_id'])) {
    $uid = (int)$_SESSION['user_id'];
    $user = $conn->query("SELECT * FROM users WHERE id=$uid")->fetch_assoc();
    $scans = $conn->query("SELECT 'URL' AS type, url AS target, result, risk_score, scanned_at FROM url_scans WHERE user_id=$uid UNION ALL SELECT 'Email', subject, result, risk_score, scanned_at FROM email_scans WHERE user_id=$uid ORDER BY scanned_at DESC")->fetch_all(MYSQLI_ASSOC);
    ?>
    <!DOCTYPE html><html><head><title>PhishShield Report</title>
    <style>body{font-family:sans-serif;margin:2rem;}h1{color:#e94560;}table{width:100%;border-collapse:collapse;}th,td{border:1px solid #ddd;padding:8px;text-align:left;font-size:13px;}th{background:#0f3460;color:#fff;}.safe{color:green;}.phishing{color:red;}.suspicious{color:orange;}</style>
    </head><body onload="window.print()">
    <h1>🛡️ PhishShield AI — Scan Report</h1>
    <p>User: <?= h($user['full_name']?:$user['username']) ?> | Generated: <?= date('Y-m-d H:i') ?></p>
    <table><thead><tr><th>Type</th><th>Target</th><th>Result</th><th>Risk Score</th><th>Date</th></tr></thead><tbody>
    <?php foreach ($scans as $s): ?>
    <tr><td><?= h($s['type']) ?></td><td><?= h(substr($s['target'],0,60)) ?></td><td class="<?= $s['result'] ?>"><?= strtoupper($s['result']) ?></td><td><?= $s['risk_score'] ?>/100</td><td><?= $s['scanned_at'] ?></td></tr>
    <?php endforeach; ?>
    </tbody></table></body></html>
    <?php exit();
}

// JSON stats
header('Content-Type: application/json');
if (!isset($_SESSION['user_id']) && !isset($_SESSION['admin_id'])) {
    echo json_encode(['error' => 'Unauthorized']); exit();
}

$uid = (int)($_SESSION['user_id'] ?? 0);
echo json_encode([
    'url_total'   => (int)$conn->query("SELECT COUNT(*) c FROM url_scans WHERE user_id=$uid")->fetch_assoc()['c'],
    'email_total' => (int)$conn->query("SELECT COUNT(*) c FROM email_scans WHERE user_id=$uid")->fetch_assoc()['c'],
    'phishing'    => (int)$conn->query("SELECT COUNT(*) c FROM url_scans WHERE user_id=$uid AND result='phishing'")->fetch_assoc()['c'],
    'safe'        => (int)$conn->query("SELECT COUNT(*) c FROM url_scans WHERE user_id=$uid AND result='safe'")->fetch_assoc()['c'],
]);
