<?php
require_once 'config/config.php';
require_once 'config/db.php';
require_once 'config/auth.php';
require_once 'includes/functions.php';

if (isset($_SESSION['user_id'])) {
    log_activity($conn, 'Logout', 'User logged out', $_SESSION['user_id']);
}
session_destroy();
header('Location: ' . SITE_URL . '/login.php?logout=1');
exit();
