<?php
require_once dirname(__FILE__) . '/config.php';

/**
 * Send a prompt to Gemini AI and return the response text.
 */
function gemini_ask($prompt) {
    $api_key = GEMINI_API_KEY;
    $url = GEMINI_API_URL . '?key=' . $api_key;

    $data = [
        'contents' => [
            ['parts' => [['text' => $prompt]]]
        ],
        'generationConfig' => [
            'temperature' => 0.3,
            'maxOutputTokens' => 1024
        ]
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $response = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);

    if ($err) {
        return 'Error connecting to AI: ' . $err;
    }

    $decoded = json_decode($response, true);

    if (isset($decoded['candidates'][0]['content']['parts'][0]['text'])) {
        return $decoded['candidates'][0]['content']['parts'][0]['text'];
    }

    if (isset($decoded['error']['message'])) {
        return 'AI Error: ' . $decoded['error']['message'];
    }

    return 'Unable to get AI response. Please check your Gemini API key in config/config.php.';
}

/**
 * Analyze a URL for phishing indicators using Gemini.
 */
function gemini_analyze_url($url) {
    $prompt = "You are a cybersecurity expert specializing in phishing detection. Analyze this URL for phishing indicators: {$url}\n\nProvide a concise analysis (max 150 words) covering:\n1. Whether it appears safe, suspicious, or a phishing attempt\n2. Key indicators you found (domain age, SSL, lookalike domains, suspicious patterns)\n3. Risk level (Low/Medium/High)\n4. Recommendation for the user\n\nBe direct and clear.";
    return gemini_ask($prompt);
}

/**
 * Analyze an email for phishing indicators using Gemini.
 */
function gemini_analyze_email($subject, $sender, $body) {
    $prompt = "You are a cybersecurity expert. Analyze this email for phishing indicators:\n\nSubject: {$subject}\nFrom: {$sender}\nBody: {$body}\n\nProvide a concise analysis (max 150 words) covering:\n1. Whether it's safe, suspicious, or a phishing attempt\n2. Key red flags found (urgency, suspicious links, spoofed sender, grammar errors)\n3. Risk level (Low/Medium/High)\n4. What the user should do\n\nBe direct and clear.";
    return gemini_ask($prompt);
}

/**
 * Chat with the PhishShield AI assistant.
 */
function gemini_chat($user_message, $history = []) {
    $system = "You are PhishShield AI Assistant, an expert in cybersecurity and phishing prevention. Help users understand phishing threats, analyze suspicious content, and stay safe online. Be helpful, concise, and professional.";

    $context = $system . "\n\n";
    foreach ($history as $h) {
        $context .= ucfirst($h['role']) . ': ' . $h['message'] . "\n";
    }
    $context .= "User: " . $user_message;

    return gemini_ask($context);
}
