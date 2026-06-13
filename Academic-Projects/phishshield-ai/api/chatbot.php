<?php
header('Content-Type: application/json');
require_once '../config/config.php';
require_once '../config/db.php';
require_once '../config/gemini.php';

session_start();

$message = trim($_POST['message'] ?? '');
if (empty($message)) {
    echo json_encode(['reply' => 'Please send a message.']);
    exit();
}

// Get recent chat history for context
$history = [];
if (isset($_SESSION['user_id'])) {
    $uid = (int)$_SESSION['user_id'];
    $rows = $conn->query("SELECT role, message FROM chat_history WHERE user_id=$uid ORDER BY created_at DESC LIMIT 6");
    if ($rows) {
        $history = array_reverse($rows->fetch_all(MYSQLI_ASSOC));
    }
}

$reply = gemini_chat($message, $history);

// Save to chat history
if (isset($_SESSION['user_id'])) {
    $uid = (int)$_SESSION['user_id'];
    $msg_safe = $conn->real_escape_string($message);
    $rep_safe = $conn->real_escape_string($reply);
    $conn->query("INSERT INTO chat_history (user_id, role, message) VALUES ($uid, 'user', '$msg_safe')");
    $conn->query("INSERT INTO chat_history (user_id, role, message) VALUES ($uid, 'assistant', '$rep_safe')");
}

echo json_encode(['reply' => $reply]);
