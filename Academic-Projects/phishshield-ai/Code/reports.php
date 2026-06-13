<?php
/**
 * PhishShield AI - CSV Reports Export (admin only)
 */
require_once __DIR__ . '/config/auth.php';
require_admin();

$type = $_GET['type'] ?? '';
$allowed = ['url_scans','email_scans','users','feedback'];
if (!in_array($type, $allowed)) { http_response_code(400); exit('Invalid report type.'); }

$file = "phishshield_{$type}_" . date('Ymd_His') . ".csv";
header("Content-Type: text/csv; charset=utf-8");
header("Content-Disposition: attachment; filename=\"$file\"");

$out = fopen('php://output', 'w');

switch ($type) {
    case 'url_scans':
        fputcsv($out, ['ID','User ID','URL','Result','Risk','Source','Reasons','Recommendation','Date']);
        foreach ($pdo->query("SELECT * FROM url_scans ORDER BY id") as $r)
            fputcsv($out, [$r['id'],$r['user_id'],$r['url'],$r['result'],$r['risk_score'],$r['source'],$r['reasons'],$r['recommendation'],$r['created_at']]);
        break;
    case 'email_scans':
        fputcsv($out, ['ID','User ID','Content','Result','Risk','Reasons','Date']);
        foreach ($pdo->query("SELECT * FROM email_scans ORDER BY id") as $r)
            fputcsv($out, [$r['id'],$r['user_id'],$r['content'],$r['result'],$r['risk_score'],$r['reasons'],$r['created_at']]);
        break;
    case 'users':
        fputcsv($out, ['ID','Name','Email','Blocked','Created']);
        foreach ($pdo->query("SELECT id,name,email,is_blocked,created_at FROM users ORDER BY id") as $r)
            fputcsv($out, [$r['id'],$r['name'],$r['email'],$r['is_blocked'],$r['created_at']]);
        break;
    case 'feedback':
        fputcsv($out, ['ID','User ID','Rating','Message','Date']);
        foreach ($pdo->query("SELECT * FROM feedback ORDER BY id") as $r)
            fputcsv($out, [$r['id'],$r['user_id'],$r['rating'],$r['message'],$r['created_at']]);
        break;
}
fclose($out);
