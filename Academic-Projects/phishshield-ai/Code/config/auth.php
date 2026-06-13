<?php
/**
 * PhishShield AI - Auth helpers
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

function is_logged_in() {
    return isset($_SESSION['user_id']);
}
function is_admin() {
    return isset($_SESSION['admin_id']);
}
function require_login() {
    if (!is_logged_in()) {
        header("Location: " . APP_URL . "/login.php");
        exit;
    }
}
function require_admin() {
    if (!is_admin()) {
        header("Location: " . APP_URL . "/login.php?admin=1");
        exit;
    }
}
function current_user($pdo) {
    if (!is_logged_in()) return null;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}
function log_activity($pdo, $userId, $action) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, action, ip) VALUES (?, ?, ?)");
    $stmt->execute([$userId, $action, $ip]);
}
