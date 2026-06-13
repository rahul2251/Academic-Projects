<?php
require_once 'config/config.php';
require_once 'includes/functions.php';
$page_title = 'Contact';
$success = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $success = true; // Demo: just show success
}
?>
<?php include 'includes/header.php'; ?>
<?php include 'includes/navbar.php'; ?>
<div style="padding:5rem 0; min-height:80vh;">
<div class="container" style="max-width:700px;">
    <div class="text-center mb-5">
        <h1 class="section-title">Contact Us</h1>
        <p class="text-muted">Have a question or want to report an issue?</p>
    </div>
    <?php if ($success): ?>
    <div class="alert alert-success text-center">
        <i class="bi bi-check-circle-fill me-2"></i>Message sent! We'll get back to you soon.
    </div>
    <?php else: ?>
    <div class="ps-card">
        <form method="POST">
            <div class="row g-3">
                <div class="col-md-6">
                    <label>Your Name</label>
                    <input type="text" name="name" class="form-control glow-input" placeholder="John Doe" required>
                </div>
                <div class="col-md-6">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control glow-input" placeholder="you@example.com" required>
                </div>
                <div class="col-12">
                    <label>Subject</label>
                    <input type="text" name="subject" class="form-control glow-input" placeholder="How can we help?" required>
                </div>
                <div class="col-12">
                    <label>Message</label>
                    <textarea name="message" class="form-control glow-input" rows="5" placeholder="Describe your issue or question..." required></textarea>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-cyber w-100">
                        <i class="bi bi-send me-2"></i>Send Message
                    </button>
                </div>
            </div>
        </form>
    </div>
    <?php endif; ?>
</div>
</div>
<footer class="ps-footer"><div class="container text-center text-muted small">&copy; <?= date('Y') ?> PhishShield AI</div></footer>
<?php include 'includes/footer.php'; ?>
