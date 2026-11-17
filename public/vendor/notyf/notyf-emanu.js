window.emanuToast = new Notyf({
  duration: 2800,
  position: { x: 'right', y: 'top' },
  ripple: false,
  types: [
    { type: 'success', background: '#1f1c19', icon: false },
    { type: 'error', background: '#a8501f', icon: false, duration: 4000, dismissible: true },
    { type: 'warning', background: '#D4A373', icon: false, duration: 4000, dismissible: true, className: 'notyf-warn' },
  ],
});
window.emanuNotify = (msg, ok = true) => (ok ? emanuToast.success(msg) : emanuToast.error(msg));
window.emanuWarn = msg => emanuToast.open({ type: 'warning', message: msg });