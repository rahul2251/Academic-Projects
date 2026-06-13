<?php
require_once dirname(__FILE__) . '/../config/config.php';

/**
 * Sanitize output to prevent XSS.
 */
function h($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

/**
 * Calculate risk score for a URL based on heuristics.
 */
function calculate_url_risk($url, $conn) {
    $score = 0;
    $details = [];

    $parsed = parse_url($url);
    $host = strtolower($parsed['host'] ?? '');
    $domain = preg_replace('/^www\./', '', $host);

    // Check blacklist
    $stmt = $conn->prepare("SELECT id FROM blacklist WHERE ? LIKE CONCAT('%', domain, '%') OR domain = ? LIMIT 1");
    $stmt->bind_param("ss", $domain, $domain);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        $score += 70;
        $details[] = 'Domain found in blacklist';
    }
    $stmt->close();

    // Check whitelist
    $stmt2 = $conn->prepare("SELECT id FROM whitelist WHERE domain = ? LIMIT 1");
    $stmt2->bind_param("s", $domain);
    $stmt2->execute();
    if ($stmt2->get_result()->num_rows > 0) {
        $score = max(0, $score - 50);
        $details[] = 'Domain is whitelisted';
        $stmt2->close();
        return ['score' => min($score, 5), 'details' => implode('. ', $details)];
    }
    $stmt2->close();

    // Suspicious TLDs
    $sus_tlds = ['.tk', '.ga', '.cf', '.ml', '.xyz', '.top', '.click', '.download', '.zip', '.loan'];
    foreach ($sus_tlds as $tld) {
        if (str_ends_with($domain, $tld)) {
            $score += 25;
            $details[] = 'Suspicious TLD detected (' . $tld . ')';
        }
    }

    // IP address as hostname
    if (filter_var($host, FILTER_VALIDATE_IP)) {
        $score += 30;
        $details[] = 'IP address used instead of domain name';
    }

    // Excessive subdomains
    if (substr_count($host, '.') > 3) {
        $score += 15;
        $details[] = 'Excessive subdomains detected';
    }

    // Long URL
    if (strlen($url) > 200) {
        $score += 10;
        $details[] = 'Unusually long URL';
    }

    // Suspicious keywords
    $keywords = ['login', 'verify', 'update', 'secure', 'account', 'bank', 'paypal', 'ebay', 'amazon', 'microsoft', 'apple', 'password', 'confirm', 'suspend'];
    foreach ($keywords as $kw) {
        if (strpos(strtolower($url), $kw) !== false) {
            $score += 5;
            $details[] = 'Suspicious keyword found: ' . $kw;
            break;
        }
    }

    // @symbol in URL
    if (strpos($url, '@') !== false) {
        $score += 20;
        $details[] = '@ symbol found in URL (common phishing trick)';
    }

    // No HTTPS
    if (($parsed['scheme'] ?? '') !== 'https') {
        $score += 10;
        $details[] = 'Not using HTTPS';
    }

    // Multiple redirects indicator
    if (substr_count($url, 'http') > 1) {
        $score += 20;
        $details[] = 'Possible URL redirection chain';
    }

    $score = min($score, 100);

    if (empty($details)) {
        $details[] = 'No suspicious patterns detected';
    }

    return ['score' => $score, 'details' => implode('. ', $details)];
}

/**
 * Calculate risk score for email.
 */
function calculate_email_risk($subject, $sender, $body) {
    $score = 0;
    $details = [];

    $text = strtolower($subject . ' ' . $sender . ' ' . $body);

    // Urgency keywords
    $urgent = ['urgent', 'immediate', 'alert', 'action required', 'verify now', 'suspended', 'expire', 'limited time', 'act now'];
    foreach ($urgent as $kw) {
        if (strpos($text, $kw) !== false) {
            $score += 15;
            $details[] = 'Urgency language detected';
            break;
        }
    }

    // Suspicious sender patterns
    if (preg_match('/\d{4,}/', $sender) || strpos($sender, 'noreply@') !== false) {
        $score += 10;
        $details[] = 'Suspicious sender pattern';
    }

    // Lookalike domains in sender
    $lookalike = ['paypa1', 'g00gle', 'arnazon', 'micr0soft', 'facebo0k', 'app1e'];
    foreach ($lookalike as $fake) {
        if (strpos(strtolower($sender), $fake) !== false) {
            $score += 40;
            $details[] = 'Lookalike domain in sender email';
            break;
        }
    }

    // Suspicious links
    if (preg_match('/http[s]?:\/\/[^\s]+/i', $body, $matches)) {
        $link = $matches[0];
        $sus_tlds = ['.tk', '.ga', '.cf', '.ml', '.xyz'];
        foreach ($sus_tlds as $tld) {
            if (strpos($link, $tld) !== false) {
                $score += 25;
                $details[] = 'Suspicious link with bad TLD found';
                break;
            }
        }
        if (strpos($link, 'bit.ly') !== false || strpos($link, 'tinyurl') !== false) {
            $score += 15;
            $details[] = 'Shortened URL detected in body';
        }
    }

    // Phishing phrases
    $phrases = ['click here', 'verify your account', 'confirm your', 'update your information', 'your account has been', 'reset your password', 'we noticed', 'unusual activity'];
    foreach ($phrases as $ph) {
        if (strpos($text, $ph) !== false) {
            $score += 10;
            $details[] = 'Phishing phrase: "' . $ph . '"';
            break;
        }
    }

    // Excessive exclamation marks
    if (substr_count($text, '!') > 3) {
        $score += 5;
        $details[] = 'Excessive punctuation';
    }

    $score = min($score, 100);

    if (empty($details)) {
        $details[] = 'No suspicious patterns detected';
    }

    return ['score' => $score, 'details' => implode('. ', $details)];
}

/**
 * Get result label from score.
 */
function score_to_result($score) {
    if ($score >= RISK_PHISHING) return 'phishing';
    if ($score >= RISK_SUSPICIOUS) return 'suspicious';
    if ($score >= RISK_SAFE) return 'suspicious';
    return 'safe';
}

/**
 * Get badge HTML for a result.
 */
function result_badge($result) {
    $badges = [
        'safe'       => '<span class="badge bg-success"><i class="bi bi-shield-check"></i> Safe</span>',
        'suspicious' => '<span class="badge bg-warning text-dark"><i class="bi bi-exclamation-triangle"></i> Suspicious</span>',
        'phishing'   => '<span class="badge bg-danger"><i class="bi bi-shield-x"></i> Phishing</span>',
        'unknown'    => '<span class="badge bg-secondary"><i class="bi bi-question-circle"></i> Unknown</span>',
    ];
    return $badges[$result] ?? $badges['unknown'];
}

/**
 * Format a timestamp for display.
 */
function format_date($ts) {
    return date('M d, Y h:i A', strtotime($ts));
}

/**
 * Get user avatar URL.
 */
function get_avatar($avatar, $name = 'User') {
    if (!empty($avatar) && file_exists(ROOT_PATH . '/assets/uploads/profile/' . $avatar)) {
        return SITE_URL . '/assets/uploads/profile/' . $avatar;
    }
    return 'https://ui-avatars.com/api/?name=' . urlencode($name) . '&background=0f3460&color=e94560&size=64';
}

/**
 * Redirect with a flash message.
 */
function redirect_with_message($url, $type, $msg) {
    $_SESSION['flash_type'] = $type;
    $_SESSION['flash_msg']  = $msg;
    header('Location: ' . $url);
    exit();
}

/**
 * Check if a user is currently logged in.
 */
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

/**
 * Log activity to the database (Updated for your schema).
 */
function log_activity($conn, $type, $action, $user_id = null) {
    // Matches your table: activity_logs
    // Matches your columns: user_id, action, details
    $stmt = $conn->prepare("INSERT INTO activity_logs (user_id, action, details) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $user_id, $type, $action);
    $result = $stmt->execute();
    $stmt->close();
    return $result;
}


/**
 * Check if user is logged in.
 */
function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Check if current user is admin.
 */
function is_admin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}
