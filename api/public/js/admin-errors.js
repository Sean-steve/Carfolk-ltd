/* Carfolk admin notices: automatically dismiss with an accessible close button. */
(() => {
  const scheduleDismiss = (notice, delay) => {
    if (!notice || notice.dataset.dismissScheduled === '1') return;
    notice.dataset.dismissScheduled = '1';
    const close = () => {
      notice.classList.add('flash--closing');
      window.setTimeout(() => notice.remove(), 260);
    };
    notice.querySelector('[data-dismiss-flash]')?.addEventListener('click', close);
    window.setTimeout(close, delay);
  };
  document.querySelectorAll('[data-flash]').forEach(notice => {
    scheduleDismiss(notice, notice.classList.contains('flash-ok') ? 6000 : 12000);
  });
})();
