// Chart.js helpers (loaded on dashboard / analytics pages)
function psPieChart(ctx, labels, data, colors) {
  return new Chart(ctx, {
    type: 'doughnut',
    data: { labels, datasets: [{ data, backgroundColor: colors, borderWidth: 0 }] },
    options: { plugins: { legend: { labels: { color: '#cbd5e1' } } }, cutout: '65%' }
  });
}
function psLineChart(ctx, labels, data, label) {
  return new Chart(ctx, {
    type: 'line',
    data: { labels, datasets: [{
      label, data, fill:true,
      borderColor:'#22d3ee',
      backgroundColor:'rgba(34,211,238,0.15)',
      tension:0.35, pointRadius:3
    }]},
    options: {
      plugins: { legend: { labels: { color:'#cbd5e1' } } },
      scales: {
        x: { ticks:{ color:'#94a3b8' }, grid:{ color:'rgba(255,255,255,0.05)' } },
        y: { ticks:{ color:'#94a3b8' }, grid:{ color:'rgba(255,255,255,0.05)' } }
      }
    }
  });
}
