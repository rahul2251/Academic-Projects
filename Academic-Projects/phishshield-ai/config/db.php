<?php
// Database Configuration
define('DB_HOST', '127.0.0.1'); // Changed from localhost to 127.0.0.1
define('DB_PORT', 3307);        // Separate the port
define('DB_USER', 'root');
define('DB_PASS', 'machine317');
define('DB_NAME', 'phishshield_db');

// New connection string format
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

//$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$conn = new mysqli('127.0.0.1', DB_USER, DB_PASS, DB_NAME, 3307);

if ($conn->connect_error) {
    die('<div style="font-family:sans-serif;background:#1a1a2e;color:#e94560;padding:40px;text-align:center;">
        <h2>⚠️ Database Connection Failed</h2>
        <p>' . $conn->connect_error . '</p>
        <p>Make sure MySQL is running in XAMPP and the database is imported.</p>
    </div>');
}

$conn->set_charset("utf8mb4");
