// Scanner page interactions
document.addEventListener('DOMContentLoaded', () => {
  const urlForm = document.getElementById('urlScanForm');
  const emailForm = document.getElementById('emailScanForm');

  function setLoading(btn, loading) {
    if (!btn) return;
    btn.disabled = loading;
    btn.innerHTML = loading
      ? '<span class="spinner-border spinner-border-sm me-2"></span> Scanning…'
      : btn.dataset.label;
  }

  [urlForm, emailForm].forEach(f => {
    if (!f) return;
    const btn = f.querySelector('button[type=submit]');
    if (btn) btn.dataset.label = btn.innerHTML;
    f.addEventListener('submit', () => setLoading(btn, true));
  });
});
