<?php
/**
 * PhishShield AI - Shared utility functions
 */

function e($s) { return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }

function flash_set($key, $msg) { $_SESSION['flash'][$key] = $msg; }
function flash_get($key) {
    if (isset($_SESSION['flash'][$key])) {
        $m = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);
        return $m;
    }
    return null;
}

function get_domain($url) {
    $url = trim($url);
    if (!preg_match('#^https?://#i', $url)) $url = 'http://' . $url;
    $host = parse_url($url, PHP_URL_HOST);
    return $host ? strtolower($host) : '';
}

/**
 * PHP heuristic phishing checks for a URL.
 * Returns ['score' => int 0-100, 'reasons' => string[]]
 */
function url_heuristics($url) {
    $reasons = [];
    $score   = 0;
    $u = trim($url);

    if (strpos($u, '@') !== false)            { $score += 20; $reasons[] = 'Contains "@" symbol (often used to disguise URLs).'; }
    if (strlen($u) > 75)                      { $score += 10; $reasons[] = 'Unusually long URL (' . strlen($u) . ' chars).'; }
    if (substr_count($u, '.') > 4)            { $score += 10; $reasons[] = 'Too many dots in the URL.'; }
    if (preg_match('#https?://\d{1,3}(\.\d{1,3}){3}#', $u)) { $score += 25; $reasons[] = 'URL uses a raw IP address instead of a domain.'; }
    if (stripos($u, 'https://') === false)    { $score += 15; $reasons[] = 'URL does not use HTTPS.'; }

    $shorts = ['bit.ly','tinyurl.com','t.co','goo.gl','ow.ly','is.gd','buff.ly','adf.ly','rebrand.ly'];
    foreach ($shorts as $s) if (stripos($u, $s) !== false) { $score += 15; $reasons[] = "URL uses a shortener ($s)."; break; }

    $keywords = ['login','verify','update','secure','account','bank','confirm','password','wallet','signin','webscr'];
    foreach ($keywords as $k) if (stripos($u, $k) !== false) { $score += 8; $reasons[] = "Suspicious keyword: \"$k\"."; }

    if (preg_match('/-{2,}/', $u))            { $score += 5;  $reasons[] = 'Multiple consecutive dashes in URL.'; }
    if (preg_match('/(xn--)/i', $u))          { $score += 10; $reasons[] = 'Punycode domain detected (possible homograph attack).'; }

    if ($score > 100) $score = 100;
    return ['score' => $score, 'reasons' => $reasons];
}

/**
 * Email content heuristic checks.
 */
function email_heuristics($text) {
    $reasons = [];
    $score = 0;
    $t = strtolower($text);

    $urgent = ['urgent','immediately','act now','within 24 hours','suspended','verify your account','final notice'];
    foreach ($urgent as $w) if (strpos($t, $w) !== false) { $score += 10; $reasons[] = "Urgency phrase: \"$w\"."; }

    $offers = ['you have won','congratulations','free gift','lottery','prize','claim now','million dollars'];
    foreach ($offers as $w) if (strpos($t, $w) !== false) { $score += 12; $reasons[] = "Fake offer phrase: \"$w\"."; }

    $cred = ['password','social security','credit card','cvv','pin code','bank login','verify identity'];
    foreach ($cred as $w) if (strpos($t, $w) !== false) { $score += 15; $reasons[] = "Requests sensitive info: \"$w\"."; }

    if (preg_match_all('#https?://\S+#', $text, $m)) {
        $count = count($m[0]);
        if ($count > 0) { $score += min(20, $count * 5); $reasons[] = "Contains $count link(s)."; }
        foreach ($m[0] as $link) {
            $h = url_heuristics($link);
            if ($h['score'] >= 30) { $score += 10; $reasons[] = "Suspicious link detected: $link"; break; }
        }
    }

    if (preg_match('/dear (customer|user|client|valued)/i', $text)) { $score += 5; $reasons[] = 'Generic greeting (e.g. "Dear Customer").'; }

    if ($score > 100) $score = 100;
    return ['score' => $score, 'reasons' => $reasons];
}

function classify_url($score) {
    if ($score >= 60) return 'Phishing';
    if ($score >= 30) return 'Suspicious';
    return 'Safe';
}
function classify_email($score) {
    if ($score >= 60) return 'Phishing';
    if ($score >= 30) return 'Suspicious';
    return 'Legitimate';
}
