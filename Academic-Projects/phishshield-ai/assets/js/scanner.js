// PhishShield AI — scanner.js

document.addEventListener('DOMContentLoaded', function () {

    // URL Scanner form
    var urlForm = document.getElementById('urlScanForm');
    if (urlForm) {
        urlForm.addEventListener('submit', function (e) {
            var btn = urlForm.querySelector('[type=submit]');
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Scanning...';
            btn.disabled = true;
        });
    }

    // Email Scanner form
    var emailForm = document.getElementById('emailScanForm');
    if (emailForm) {
        emailForm.addEventListener('submit', function (e) {
            var btn = emailForm.querySelector('[type=submit]');
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Analyzing...';
            btn.disabled = true;
        });
    }

    // Risk bar animation
    document.querySelectorAll('.risk-fill').forEach(function (bar) {
        var target = bar.getAttribute('data-width') || '0';
        bar.style.width = '0%';
        setTimeout(function () {
            bar.style.width = target + '%';
        }, 200);
    });

    // Result section reveal
    var result = document.getElementById('scanResult');
    if (result) {
        result.style.opacity = 0;
        result.style.transform = 'translateY(16px)';
        result.style.transition = 'all .5s ease';
        setTimeout(function () {
            result.style.opacity = 1;
            result.style.transform = 'translateY(0)';
        }, 150);
    }
});
