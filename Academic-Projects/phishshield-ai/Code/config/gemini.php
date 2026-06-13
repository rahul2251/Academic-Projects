<?php
/**
 * PhishShield AI - Gemini 2.5 Flash API integration
 */
require_once __DIR__ . '/config.php';

function gemini_call($prompt) {
    global $gemini_api_key;
    if (empty($gemini_api_key) || $gemini_api_key === 'PASTE_YOUR_GEMINI_API_KEY_HERE') {
        return ['ok' => false, 'error' => 'Gemini API key not configured in config/config.php'];
    }

    $payload = json_encode([
        'contents' => [[ 'parts' => [[ 'text' => $prompt ]] ]],
        'generationConfig' => [
            'temperature' => 0.3,
            'maxOutputTokens' => 800,
        ],
    ]);

    $ch = curl_init(GEMINI_URL . '?key=' . urlencode($gemini_api_key));
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_SSL_VERIFYPEER => false,
    ]);
    $resp = curl_exec($ch);
    $err  = curl_error($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($err)         return ['ok' => false, 'error' => "cURL error: $err"];
    if ($code !== 200) return ['ok' => false, 'error' => "HTTP $code: " . substr($resp, 0, 300)];

    $data = json_decode($resp, true);
    // Defensive: if structure isn't as expected, try to log and return raw body
    $text = '';
    if (is_array($data) && isset($data['candidates'][0]['content']['parts'][0]['text'])) {
        $text = $data['candidates'][0]['content']['parts'][0]['text'];
    } else {
        // Log unexpected response for debugging
        @file_put_contents(__DIR__ . '/../scan-debug.log', date('c') . " GEMINI-UNEXPECTED: " . substr($resp,0,2000) . PHP_EOL, FILE_APPEND | LOCK_EX);
        // Fallback to treating whole response body as text
        $text = is_string($resp) ? $resp : '';
    }
    return ['ok' => true, 'text' => trim($text)];
}

/** Try to extract a JSON object from Gemini's reply. */
function gemini_extract_json($text) {
    // 1) Try if the entire text is valid JSON
    $trim = trim($text);
    $j = json_decode($trim, true);
    if (is_array($j)) return $j;

    // 2) Try to extract JSON inside a fenced code block (```json or ```)
    if (preg_match('/```(?:json)?\s*([\s\S]*?)\s*```/i', $text, $m)) {
        $cand = trim($m[1]);
        $j = json_decode($cand, true);
        if (is_array($j)) return $j;
    }

    // 3) Find all brace-balanced {...} fragments and try decoding each (handles extra text)
    if (preg_match_all('/\{(?:[^{}]|(?R))*\}/s', $text, $matches)) {
        foreach ($matches[0] as $frag) {
            $j = json_decode($frag, true);
            if (is_array($j)) return $j;
        }
    }

    // 4) Last resort: try to locate a JSON-like substring using a simpler brace search
    if (preg_match('/(\{[\s\S]*\})/s', $text, $m2)) {
        $j = json_decode($m2[1], true);
        if (is_array($j)) return $j;
    }

    return null;
}

function analyzeURL($url) {
    $prompt = "You are a phishing detection expert. Analyze this URL: \"$url\"\n" .
              "Reply ONLY with a strict JSON object with these keys: " .
              "verdict (one of: Safe, Suspicious, Phishing), risk_score (0-100 integer), " .
              "reasons (array of short strings), recommendation (one short string).";
    $r = gemini_call($prompt);
    if (!$r['ok']) return ['verdict' => null, 'error' => $r['error']];
    $j = gemini_extract_json($r['text']);
    if (!$j) return ['verdict' => null, 'error' => 'Could not parse Gemini response.', 'raw' => $r['text']];
    return $j;
}

function analyzeEmail($text) {
    $clean = mb_substr($text, 0, 4000);
    $prompt = "You are a phishing email detection expert. Analyze this email content:\n\n\"\"\"$clean\"\"\"\n\n" .
              "Reply ONLY with a strict JSON object with keys: " .
              "verdict (one of: Legitimate, Suspicious, Phishing), risk_score (0-100 integer), " .
              "reasons (array of short strings highlighting suspicious indicators).";
    $r = gemini_call($prompt);
    if (!$r['ok']) return ['verdict' => null, 'error' => $r['error']];
    $j = gemini_extract_json($r['text']);
    if (!$j) return ['verdict' => null, 'error' => 'Could not parse Gemini response.', 'raw' => $r['text']];
    return $j;
}

function chatBotReply($message) {
    $prompt = "You are PhishShield AI, a friendly cybersecurity assistant. " .
              "Help the user with phishing, scams, safe browsing and email safety. " .
              "Keep replies concise and practical.\n\nUser: $message\nAssistant:";
    $r = gemini_call($prompt);
    return $r['ok'] ? $r['text'] : ('Error: ' . $r['error']);
}
