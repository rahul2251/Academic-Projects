<?php
/**
 * PhishShield AI - Global Config
 */
if (session_status() === PHP_SESSION_NONE) session_start();

define('APP_NAME', 'PhishShield AI');
define('APP_URL',  'http://localhost/phishshield-ai');

// =====================================================
// PASTE YOUR GEMINI API KEY HERE
// Get one at: https://aistudio.google.com/app/apikey
// =====================================================
$gemini_api_key = "AIzaSyBx9Wv_GHidhdnbiac58RehJEjvcSjcpqY";

// Gemini model + endpoint (Gemini 2.5 Flash)
define('GEMINI_MODEL', 'gemini-2.5-flash');
define('GEMINI_URL',   'https://generativelanguage.googleapis.com/v1beta/models/' . GEMINI_MODEL . ':generateContent');

// Timezone
date_default_timezone_set('UTC');
