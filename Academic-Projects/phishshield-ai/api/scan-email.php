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

$uid     = $_SESSION['user_id'];
$subject = trim($_POST['subject'] ?? '');
$sender  = trim($_POST['sender']  ?? '');
$body    = trim($_POST['body']    ?? '');

if (empty($sender) || empty($body)) {
    echo json_encode(['error' => 'Missing required fields']); exit();
}

$risk = calculate_email_risk($subject, $sender, $body);
$score = $risk['score'];
$details = $risk['details'];
$result = score_to_result($score);
$ai = gemini_analyze_email($subject, $sender, $body);

$stmt = $conn->prepare("INSERT INTO email_scans (user_id, subject, sender, email_body, result, risk_score, details, ai_analysis) VALUES (?,?,?,?,?,?,?,?)");
$stmt->bind_param("isssssiss", $uid, $subject, $sender, $body, $result, $score, $details, $ai);
$stmt->execute();
$stmt->close();

echo json_encode([
    'result' => $result,
    'risk_score' => $score,
    'details' => $details,
    'ai_analysis' => $ai
]);
