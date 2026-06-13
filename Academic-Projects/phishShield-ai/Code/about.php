<?php
require_once 'config/config.php';
require_once 'includes/functions.php';
$page_title = 'About';
?>
<?php include 'includes/header.php'; ?>
<?php include 'includes/navbar.php'; ?>
<div style="padding:5rem 0;">
<div class="container" style="max-width:860px;">
    <div class="text-center mb-5">
        <h1 class="section-title">About PhishShield AI</h1>
        <p class="text-muted">Protecting users from phishing threats using artificial intelligence</p>
    </div>
    <div class="ps-card mb-4">
        <h5 class="fw-bold mb-3"><i class="bi bi-shield-check text-accent me-2"></i>Our Mission</h5>
        <p class="text-muted">PhishShield AI was built to combat the growing threat of phishing attacks. Phishing is responsible for over 90% of data breaches. By combining AI analysis with real-time blacklists and heuristic detection, we make enterprise-level protection accessible to everyone.</p>
    </div>
    <div class="ps-card mb-4">
        <h5 class="fw-bold mb-3"><i class="bi bi-cpu text-accent me-2"></i>Technology</h5>
        <ul class="text-muted list-unstyled">
            <li class="mb-2"><i class="bi bi-check2-circle text-cyan me-2"></i>Google Gemini API for AI-powered analysis</li>
            <li class="mb-2"><i class="bi bi-check2-circle text-cyan me-2"></i>PHP 8 + MySQL for the backend</li>
            <li class="mb-2"><i class="bi bi-check2-circle text-cyan me-2"></i>Chart.js for interactive data visualization</li>
            <li class="mb-2"><i class="bi bi-check2-circle text-cyan me-2"></i>Bootstrap 5 for responsive UI</li>
            <li class="mb-2"><i class="bi bi-check2-circle text-cyan me-2"></i>Heuristic + blacklist-based threat detection</li>
        </ul>
    </div>
    <div class="ps-card">
        <h5 class="fw-bold mb-3"><i class="bi bi-exclamation-triangle text-accent me-2"></i>Disclaimer</h5>
        <p class="text-muted mb-0">PhishShield AI is a demonstration/educational project. While it uses real detection algorithms and AI, it should not be the sole tool for security decisions. Always follow your organization's security policies.</p>
    </div>
</div>
</div>
<footer class="ps-footer"><div class="container text-center text-muted small">&copy; <?= date('Y') ?> PhishShield AI</div></footer>
<?php include 'includes/footer.php'; ?>
