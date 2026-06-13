<?php require_once dirname(__FILE__) . '/../config/config.php'; ?>
<nav class="navbar navbar-expand-lg navbar-dark ps-navbar">
    <div class="container">
        <a class="navbar-brand" href="<?= SITE_URL ?>">
            <i class="bi bi-shield-shaded me-2 text-accent"></i>
            <span class="brand-text">PhishShield <span class="text-accent">AI</span></span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMain">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navMain">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link" href="<?= SITE_URL ?>"><i class="bi bi-house-door me-1"></i>Home</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= SITE_URL ?>/features.php"><i class="bi bi-stars me-1"></i>Features</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= SITE_URL ?>/about.php"><i class="bi bi-info-circle me-1"></i>About</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= SITE_URL ?>/contact.php"><i class="bi bi-envelope me-1"></i>Contact</a></li>
            </ul>
            <div class="d-flex gap-2">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="<?= SITE_URL ?>/user/dashboard.php" class="btn btn-outline-accent btn-sm">
                        <i class="bi bi-grid-1x2 me-1"></i>Dashboard
                    </a>
                    <a href="<?= SITE_URL ?>/logout.php" class="btn btn-danger btn-sm">
                        <i class="bi bi-box-arrow-right me-1"></i>Logout
                    </a>
                <?php else: ?>
                    <a href="<?= SITE_URL ?>/login.php" class="btn btn-outline-light btn-sm">
                        <i class="bi bi-box-arrow-in-right me-1"></i>Login
                    </a>
                    <a href="<?= SITE_URL ?>/register.php" class="btn btn-accent btn-sm">
                        <i class="bi bi-person-plus me-1"></i>Register
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>
