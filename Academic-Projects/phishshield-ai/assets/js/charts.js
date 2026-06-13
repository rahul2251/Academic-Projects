// PhishShield AI — charts.js
// Chart.js chart builders

var PSCharts = {

    defaults: {
        color: '#c9d1e0',
        gridColor: 'rgba(255,255,255,0.05)',
        fontFamily: "'Inter', sans-serif",
    },

    applyDefaults: function () {
        Chart.defaults.color = this.defaults.color;
        Chart.defaults.font.family = this.defaults.fontFamily;
        Chart.defaults.plugins.legend.labels.usePointStyle = true;
        Chart.defaults.plugins.legend.labels.padding = 20;
    },

    doughnut: function (canvasId, labels, data, colors) {
        this.applyDefaults();
        var ctx = document.getElementById(canvasId);
        if (!ctx) return null;
        return new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{ data: data, backgroundColor: colors, borderWidth: 2, borderColor: '#16213e', hoverOffset: 8 }]
            },
            options: {
                cutout: '70%',
                plugins: { legend: { position: 'bottom' } },
                animation: { animateScale: true }
            }
        });
    },

    bar: function (canvasId, labels, datasets, title) {
        this.applyDefaults();
        var ctx = document.getElementById(canvasId);
        if (!ctx) return null;
        return new Chart(ctx, {
            type: 'bar',
            data: { labels: labels, datasets: datasets },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: { grid: { color: this.defaults.gridColor }, ticks: { color: this.defaults.color } },
                    y: { grid: { color: this.defaults.gridColor }, ticks: { color: this.defaults.color }, beginAtZero: true }
                },
                plugins: {
                    legend: { position: 'top' },
                    title: { display: !!title, text: title, color: '#fff', font: { size: 14, weight: '600' } }
                }
            }
        });
    },

    line: function (canvasId, labels, datasets) {
        this.applyDefaults();
        var ctx = document.getElementById(canvasId);
        if (!ctx) return null;
        datasets.forEach(function (ds) {
            ds.tension = 0.4;
            ds.fill = ds.fill !== undefined ? ds.fill : false;
            ds.pointRadius = 4;
        });
        return new Chart(ctx, {
            type: 'line',
            data: { labels: labels, datasets: datasets },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: { grid: { color: 'rgba(255,255,255,0.04)' } },
                    y: { grid: { color: 'rgba(255,255,255,0.04)' }, beginAtZero: true }
                },
                plugins: { legend: { position: 'top' } }
            }
        });
    },

    gauge: function (canvasId, value, max) {
        this.applyDefaults();
        var ctx = document.getElementById(canvasId);
        if (!ctx) return null;
        var pct = value / max;
        var color = pct >= 0.8 ? '#e94560' : pct >= 0.5 ? '#f5a623' : '#16c79a';
        return new Chart(ctx, {
            type: 'doughnut',
            data: {
                datasets: [{
                    data: [value, max - value],
                    backgroundColor: [color, 'rgba(255,255,255,0.05)'],
                    borderWidth: 0,
                    circumference: 270,
                    rotation: 225
                }]
            },
            options: {
                cutout: '80%',
                plugins: { legend: { display: false }, tooltip: { enabled: false } }
            }
        });
    }
};
