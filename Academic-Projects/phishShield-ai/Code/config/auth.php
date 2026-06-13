<?php
require_once __DIR__ . '/config.php';

/**
 * Require user login. Redirect to login if not logged in.
 */
function require_user_login() {
    if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'user') {
        header('Location: ' . SITE_URL . '/login.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
        exit();
    }
    // Session timeout check
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > SESSION_TIMEOUT) {
        session_destroy();
        header('Location: ' . SITE_URL . '/login.php?expired=1');
        exit();
    }
    $_SESSION['last_activity'] = time();
}

/**
 * Require admin login. Redirect to admin login if not logged in.
 */
function require_admin_login() {
    if (!isset($_SESSION['admin_id']) || $_SESSION['user_role'] !== 'admin') {
        header('Location: ' . SITE_URL . '/admin/login.php');
        exit();
    }
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > SESSION_TIMEOUT) {
        session_destroy();
        header('Location: ' . SITE_URL . '/admin/login.php?expired=1');
        exit();
    }
    $_SESSION['last_activity'] = time();
}

/**
 * Check if user is logged in.
 */
function is_logged_in() {
    return isset($_SESSION['user_id']) && $_SESSION['user_role'] === 'user';
}

/**
 * Check if admin is logged in.
 */
function is_admin_logged_in() {
    return isset($_SESSION['admin_id']) && $_SESSION['user_role'] === 'admin';
}

/**
 * Log an activity.
 */
function log_activity($conn, $action, $details = '', $user_id = null, $user_type = 'user') {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    $user_id = $user_id ?? ($_SESSION['user_id'] ?? null);
    $stmt = $conn->prepare("INSERT INTO activity_logs (user_id, user_type, action, details, ip_address) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $user_id, $user_type, $action, $details, $ip);
    $stmt->execute();
    $stmt->close();
}
