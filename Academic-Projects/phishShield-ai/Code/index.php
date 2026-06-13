<?php
require_once 'config/config.php';
require_once 'includes/functions.php';
$page_title = 'Home';
?>
<?php include 'includes/header.php'; ?>
<?php include 'includes/navbar.php'; ?>

<!-- Hero Section -->
<section class="ps-hero">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-6">
                <div class="hero-badge">
                    <i class="bi bi-lightning-charge-fill"></i> AI-Powered Protection
                </div>
                <h1 class="hero-title">
                    Stop <span class="text-accent">Phishing</span><br>Before It Strikes
                </h1>
                <p class="hero-subtitle mt-3 mb-4">
                    PhishShield AI uses advanced machine learning and Google Gemini to detect phishing URLs, suspicious emails, and social engineering attacks in real time.
                </p>
                <div class="d-flex gap-3 flex-wrap">
                    <a href="<?= SITE_URL ?>/register.php" class="btn btn-cyber btn-lg px-4">
                        <i class="bi bi-shield-check me-2"></i>Start Free Scan
                    </a>
                    <a href="<?= SITE_URL ?>/features.php" class="btn btn-outline-light btn-lg px-4">
                        <i class="bi bi-info-circle me-2"></i>Learn More
                    </a>
                </div>
                <div class="d-flex gap-4 mt-4">
                    <div><div class="fw-bold text-white fs-5">10K+</div><div class="text-muted small">Scans Done</div></div>
                    <div class="vr opacity-25"></div>
                    <div><div class="fw-bold text-white fs-5">99.2%</div><div class="text-muted small">Accuracy</div></div>
                    <div class="vr opacity-25"></div>
                    <div><div class="fw-bold text-white fs-5">500+</div><div class="text-muted small">Threats Blocked</div></div>
                </div>
            </div>
            <div class="col-lg-6 text-center d-none d-lg-block">
                <div style="position:relative; display:inline-block;">
                    <div style="width:340px;height:340px;background:radial-gradient(circle,rgba(233,69,96,.15) 0%,transparent 70%);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:auto;">
                        <div style="font-size:9rem;animation:pulse 3s infinite;">🛡️</div>
                    </div>
                    <!-- Floating cards -->
                    <div class="ps-card position-absolute" style="top:20px;right:-20px;padding:.75rem 1rem;min-width:180px;">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-danger p-2"><i class="bi bi-shield-x fs-6"></i></span>
                            <div><div class="small text-white fw-bold">Phishing Detected!</div><div class="small text-muted">fake-paypal.xyz</div></div>
                        </div>
                    </div>
                    <div class="ps-card position-absolute" style="bottom:40px;left:-30px;padding:.75rem 1rem;min-width:180px;">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-success p-2"><i class="bi bi-shield-check fs-6"></i></span>
                            <div><div class="small text-white fw-bold">URL is Safe</div><div class="small text-muted">google.com</div></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="py-6" style="padding:5rem 0;">
    <div class="container">
        <div class="text-center mb-5">
            <div class="hero-badge mx-auto d-inline-flex mb-3"><i class="bi bi-stars"></i> Features</div>
            <h2 class="section-title">Everything You Need to Stay Safe</h2>
            <p class="text-muted">Comprehensive protection against modern phishing threats</p>
        </div>
        <div class="row g-4">
            <?php
            $features = [
                ['icon'=>'link-45deg','color'=>'rgba(233,69,96,.15)','icolor'=>'var(--ps-accent)','title'=>'URL Scanner','desc'=>'Scan any URL instantly. Our AI cross-references blacklists, checks TLS certificates, detects lookalike domains, and analyzes suspicious patterns.'],
                ['icon'=>'envelope-exclamation','color'=>'rgba(22,199,154,.15)','icolor'=>'var(--ps-cyan)','title'=>'Email Analyzer','desc'=>'Paste suspicious email content and let AI identify phishing tactics, urgency language, spoofed senders, and malicious links.'],
                ['icon'=>'robot','color'=>'rgba(77,168,255,.15)','icolor'=>'#4da8ff','title'=>'AI Chatbot','desc'=>'Chat with our Gemini-powered assistant for cybersecurity advice, phishing education, and real-time threat analysis.'],
                ['icon'=>'bar-chart-line','color'=>'rgba(245,166,35,.15)','icolor'=>'var(--ps-yellow)','title'=>'Analytics Dashboard','desc'=>'Visualize your scan history, risk trends, and threat statistics with interactive Chart.js graphs.'],
                ['icon'=>'shield-lock','color'=>'rgba(233,69,96,.15)','icolor'=>'var(--ps-accent)','title'=>'Blacklist Manager','desc'=>'Admins can manage domain blacklists and whitelists to fine-tune the detection engine for your organization.'],
                ['icon'=>'file-earmark-pdf','color'=>'rgba(22,199,154,.15)','icolor'=>'var(--ps-cyan)','title'=>'Reports','desc'=>'Generate and download PDF reports of your scan history for compliance, auditing, or sharing with your IT team.'],
            ];
            foreach ($features as $f): ?>
            <div class="col-md-6 col-lg-4">
                <div class="ps-card h-100">
                    <div class="feature-icon" style="background:<?= $f['color'] ?>;color:<?= $f['icolor'] ?>;">
                        <i class="bi bi-<?= $f['icon'] ?>"></i>
                    </div>
                    <h5 class="fw-bold mb-2"><?= $f['title'] ?></h5>
                    <p class="text-muted small mb-0"><?= $f['desc'] ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-5" style="background:linear-gradient(135deg, var(--ps-blue), var(--ps-dark));">
    <div class="container text-center py-4">
        <h2 class="section-title mb-3">Start Protecting Yourself Today</h2>
        <p class="text-muted mb-4">Create a free account and run unlimited scans powered by Google Gemini AI.</p>
        <a href="<?= SITE_URL ?>/register.php" class="btn btn-cyber btn-lg px-5">
            <i class="bi bi-person-plus me-2"></i>Create Free Account
        </a>
        <p class="text-muted mt-3 small">No credit card required · Instant access</p>
    </div>
</section>

<!-- Footer -->
<footer class="ps-footer">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-4">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <i class="bi bi-shield-shaded fs-4 text-accent"></i>
                    <span class="fw-bold fs-5 text-white">PhishShield AI</span>
                </div>
                <p class="text-muted small">AI-powered phishing detection and prevention for everyone.</p>
            </div>
            <div class="col-lg-2">
                <div class="fw-semibold text-white mb-3">Links</div>
                <div class="d-flex flex-column gap-2">
                    <a href="<?= SITE_URL ?>">Home</a>
                    <a href="<?= SITE_URL ?>/features.php">Features</a>
                    <a href="<?= SITE_URL ?>/about.php">About</a>
                    <a href="<?= SITE_URL ?>/contact.php">Contact</a>
                </div>
            </div>
            <div class="col-lg-2">
                <div class="fw-semibold text-white mb-3">Account</div>
                <div class="d-flex flex-column gap-2">
                    <a href="<?= SITE_URL ?>/login.php">Login</a>
                    <a href="<?= SITE_URL ?>/register.php">Register</a>
                    <a href="<?= SITE_URL ?>/user/dashboard.php">Dashboard</a>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="fw-semibold text-white mb-3">Quick Scan</div>
                <p class="text-muted small">Have a suspicious URL? <a href="<?= SITE_URL ?>/login.php" class="text-accent">Login to scan it instantly.</a></p>
            </div>
        </div>
        <hr class="divider mt-4">
        <div class="text-center text-muted small">
            &copy; <?= date('Y') ?> PhishShield AI. Built for cybersecurity education.
        </div>
    </div>
</footer>

<style>
@keyframes pulse { 0%,100%{transform:scale(1)} 50%{transform:scale(1.05)} }
</style>
<?php include 'includes/footer.php'; ?>
