<?php
// Site Configuration
define('SITE_NAME', 'PhishShield AI');
define('SITE_URL', 'http://localhost/phishshield-ai');
define('SITE_VERSION', '1.0.0');

// Paths
define('ROOT_PATH', dirname(dirname(__FILE__)));
define('ASSETS_URL', SITE_URL . '/assets');

// Session timeout (seconds)
define('SESSION_TIMEOUT', 3600);

// File upload settings
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024); // 5MB
define('UPLOAD_PATH', ROOT_PATH . '/assets/uploads/profile/');

// Gemini API (set your API key here)
define('GEMINI_API_KEY', 'YOUR_GEMINI_API_KEY_HERE');
define('GEMINI_API_URL', 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent');

// Risk score thresholds
define('RISK_SAFE', 30);
define('RISK_SUSPICIOUS', 60);
define('RISK_PHISHING', 80);

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
