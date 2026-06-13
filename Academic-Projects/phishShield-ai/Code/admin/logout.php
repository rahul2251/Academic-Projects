<?php
require_once '../config/config.php';
require_once '../config/db.php';
require_once '../config/auth.php';
require_once '../includes/functions.php';
if (isset($_SESSION['admin_id'])) {
    log_activity($conn, 'Admin Logout', 'Admin logged out', $_SESSION['admin_id'], 'admin');
}
session_destroy();
header('Location: ' . SITE_URL . '/admin/login.php');
exit();
