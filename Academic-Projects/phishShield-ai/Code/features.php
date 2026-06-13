<?php
require_once 'config/config.php';
require_once 'includes/functions.php';
$page_title = 'Features';
?>
<?php include 'includes/header.php'; ?>
<?php include 'includes/navbar.php'; ?>

<div style="background:linear-gradient(180deg,var(--ps-dark3),var(--ps-dark)); padding:5rem 0 3rem;">
<div class="container">
    <div class="text-center mb-5">
        <div class="hero-badge mx-auto d-inline-flex mb-3"><i class="bi bi-stars"></i> All Features</div>
        <h1 class="section-title">Powerful Phishing Protection</h1>
        <p class="text-muted">Everything you need to detect and prevent phishing attacks</p>
    </div>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="ps-card h-100">
                <div class="d-flex gap-3 mb-3">
                    <div class="feature-icon" style="background:rgba(233,69,96,.15);color:var(--ps-accent);flex-shrink:0;">
                        <i class="bi bi-link-45deg fs-4"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-1">URL Scanner</h5>
                        <p class="text-muted small mb-0">Paste any URL and get instant threat analysis. Our system checks domain reputation, SSL certificates, TLD risk, lookalike patterns, and cross-references a real-time blacklist. Powered by Google Gemini for AI-driven insights.</p>
                    </div>
                </div>
                <ul class="list-unstyled small text-muted mt-2 ps-2">
                    <li class="mb-1"><i class="bi bi-check-circle text-cyan me-2"></i>Domain blacklist check</li>
                    <li class="mb-1"><i class="bi bi-check-circle text-cyan me-2"></i>Lookalike domain detection</li>
                    <li class="mb-1"><i class="bi bi-check-circle text-cyan me-2"></i>Risk scoring (0–100)</li>
                    <li class="mb-1"><i class="bi bi-check-circle text-cyan me-2"></i>Gemini AI analysis</li>
                </ul>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="ps-card h-100">
                <div class="d-flex gap-3 mb-3">
                    <div class="feature-icon" style="background:rgba(22,199,154,.15);color:var(--ps-cyan);flex-shrink:0;">
                        <i class="bi bi-envelope-exclamation fs-4"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-1">Email Analyzer</h5>
                        <p class="text-muted small mb-0">Submit suspicious email content — subject, sender, and body. PhishShield AI analyzes urgency language, spoofed senders, embedded links, and social engineering tactics.</p>
                    </div>
                </div>
                <ul class="list-unstyled small text-muted mt-2 ps-2">
                    <li class="mb-1"><i class="bi bi-check-circle text-cyan me-2"></i>Sender spoofing detection</li>
                    <li class="mb-1"><i class="bi bi-check-circle text-cyan me-2"></i>Urgency language analysis</li>
                    <li class="mb-1"><i class="bi bi-check-circle text-cyan me-2"></i>Link extraction & check</li>
                    <li class="mb-1"><i class="bi bi-check-circle text-cyan me-2"></i>Phishing phrase detection</li>
                </ul>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="ps-card h-100">
                <div class="feature-icon" style="background:rgba(77,168,255,.15);color:#4da8ff;">
                    <i class="bi bi-robot fs-4"></i>
                </div>
                <h5 class="fw-bold mb-2">AI Chatbot</h5>
                <p class="text-muted small">Ask anything about cybersecurity. Our Gemini-powered chatbot can analyze threats, explain phishing tactics, and give personalized advice.</p>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="ps-card h-100">
                <div class="feature-icon" style="background:rgba(245,166,35,.15);color:var(--ps-yellow);">
                    <i class="bi bi-bar-chart-line fs-4"></i>
                </div>
                <h5 class="fw-bold mb-2">Analytics Dashboard</h5>
                <p class="text-muted small">Interactive charts showing scan history, risk trends over time, threat type breakdown, and your personal security score.</p>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="ps-card h-100">
                <div class="feature-icon" style="background:rgba(233,69,96,.15);color:var(--ps-accent);">
                    <i class="bi bi-people fs-4"></i>
                </div>
                <h5 class="fw-bold mb-2">Admin Panel</h5>
                <p class="text-muted small">Full admin dashboard to manage users, view all scans, manage blacklists/whitelists, read feedback, and monitor system activity logs.</p>
            </div>
        </div>
    </div>
</div>
</div>

<!-- Footer -->
<footer class="ps-footer">
    <div class="container text-center text-muted small">
        &copy; <?= date('Y') ?> PhishShield AI — <a href="<?= SITE_URL ?>" class="text-accent">Home</a>
    </div>
</footer>
<?php include 'includes/footer.php'; ?>
