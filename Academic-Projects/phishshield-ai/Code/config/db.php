<?php
/**
 * PhishShield AI - Database Connection (PDO)
 * XAMPP MySQL on port 3307
 */
$host   = "localhost";
$port   = "3307";
$dbname = "phishshield_db";
$user   = "root";
$pass   = "machine317";

try {
    $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    die("<h3 style='font-family:sans-serif;color:#b00'>Database connection failed:</h3><pre>" .
        htmlspecialchars($e->getMessage()) .
        "</pre><p>Check that MySQL is running on port <b>3307</b> and that <b>phishshield_db</b> exists.</p>");
}
