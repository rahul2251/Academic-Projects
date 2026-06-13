// PhishShield AI — main.js

document.addEventListener('DOMContentLoaded', function () {
    // Auto-dismiss alerts after 5 seconds
    document.querySelectorAll('.alert').forEach(function (alert) {
        setTimeout(function () {
            var bs = bootstrap.Alert.getOrCreateInstance(alert);
            bs.close();
        }, 5000);
    });

    // Activate tooltips
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (el) {
        new bootstrap.Tooltip(el);
    });

    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(function (a) {
        a.addEventListener('click', function (e) {
            var id = this.getAttribute('href');
            var el = document.querySelector(id);
            if (el) {
                e.preventDefault();
                el.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    });

    // Copy-to-clipboard buttons
    document.querySelectorAll('[data-copy]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var text = this.getAttribute('data-copy');
            navigator.clipboard.writeText(text).then(function () {
                btn.innerHTML = '<i class="bi bi-check-lg"></i> Copied!';
                setTimeout(function () {
                    btn.innerHTML = '<i class="bi bi-clipboard"></i> Copy';
                }, 2000);
            });
        });
    });

    // Animate stat numbers on scroll
    var counters = document.querySelectorAll('.stat-value[data-target]');
    if ('IntersectionObserver' in window && counters.length) {
        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    animateCounter(entry.target);
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.5 });
        counters.forEach(function (el) { observer.observe(el); });
    }

    function animateCounter(el) {
        var target = parseInt(el.getAttribute('data-target'), 10);
        var duration = 1200;
        var step = target / (duration / 16);
        var current = 0;
        var timer = setInterval(function () {
            current += step;
            if (current >= target) { current = target; clearInterval(timer); }
            el.textContent = Math.floor(current).toLocaleString();
        }, 16);
    }
});
