<?php
header('Content-Type: application/json');
require_once '../config/config.php';
require_once '../config/db.php';
require_once '../config/gemini.php';
require_once '../includes/functions.php';

session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']); exit();
}

$uid = $_SESSION['user_id'];
$url = trim($_POST['url'] ?? '');

if (empty($url) || !filter_var($url, FILTER_VALIDATE_URL)) {
    echo json_encode(['error' => 'Invalid URL']); exit();
}

$risk = calculate_url_risk($url, $conn);
$score = $risk['score'];
$details = $risk['details'];
$result = score_to_result($score);
$ai = gemini_analyze_url($url);

$stmt = $conn->prepare("INSERT INTO url_scans (user_id, url, result, risk_score, details, ai_analysis) VALUES (?,?,?,?,?,?)");
$stmt->bind_param("ississs", $uid, $url, $result, $score, $details, $ai);
$stmt->execute();
$stmt->close();

echo json_encode([
    'url' => $url,
    'result' => $result,
    'risk_score' => $score,
    'details' => $details,
    'ai_analysis' => $ai
]);
