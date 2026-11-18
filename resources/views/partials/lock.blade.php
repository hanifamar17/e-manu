<script>
// data-lock: sekali klik -> terkunci (cegah klik ganda). aria-busy + pointer-events-none, BUKAN disabled
// (disabled sebelum FormData bikin field tidak ikut terkirim). Dibuka lagi saat pageshow (Back).
document.addEventListener('DOMContentLoaded', () => {
  const LOCK = ['pointer-events-none', 'opacity-60'];
  const lock = el => { el.setAttribute('aria-busy', 'true'); el.classList.add(...LOCK); };
  const unlockAll = () => document.querySelectorAll('[aria-busy="true"]').forEach(el => {
    el.removeAttribute('aria-busy'); el.classList.remove(...LOCK);
  });

  document.querySelectorAll('a[data-lock]').forEach(a => a.addEventListener('click', e => {
    if (e.button !== 0 || e.ctrlKey || e.metaKey || e.shiftKey || a.target === '_blank') return;
    if (a.hasAttribute('aria-busy')) { e.preventDefault(); return; }
    lock(a);
  }));

  document.querySelectorAll('form[data-lock]').forEach(f => f.addEventListener('submit', e => {
    const b = f.querySelector('button[type="submit"]');
    if (b && b.hasAttribute('aria-busy')) { e.preventDefault(); return; }
    if (b) lock(b);
  }));

  window.addEventListener('pageshow', unlockAll);
});
</script>
