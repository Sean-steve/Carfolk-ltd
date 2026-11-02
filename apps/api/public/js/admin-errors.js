/* O6: progressive enhancements; every form still works without JavaScript. */
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

  const form = document.querySelector('[data-photo-upload]');
  if (!form) return;

  const input = form.querySelector('input[type=file]');
  const alert = form.querySelector('[data-upload-error]');
  const submit = form.querySelector('button[type=submit]');
  if (!input || !alert || !submit) return;

  const mib = 1024 * 1024;
  const parseBytes = text => {
    const match = String(text || '').trim().match(/^(\d+(?:\.\d+)?)\s*([KMG])?B?$/i);
    if (!match) return Infinity;
    const unit = String(match[2] || '').toUpperCase();
    return Number(match[1]) * ({ K: 1024, M: mib, G: 1024 * mib }[unit] || 1);
  };
  const serverFileLimit = parseBytes(form.dataset.serverFileLimit);
  const serverPostLimit = parseBytes(form.dataset.serverPostLimit);
  const fileLimit = Math.min(8 * mib, serverFileLimit);
  // Leave a margin for multipart boundaries and the CSRF token.
  const postLimit = serverPostLimit - 256 * 1024;
  const availableSlots = 20 - Number(form.dataset.existingPhotos || 0);
  const validTypes = new Set(['image/jpeg', 'image/png', 'image/webp']);
  const originalLabel = submit.textContent;

  const showError = message => {
    alert.textContent = message;
    alert.hidden = false;
    alert.focus();
  };
  const clearError = () => {
    alert.textContent = '';
    alert.hidden = true;
  };
  input.addEventListener('change', clearError);
  form.addEventListener('submit', event => {
    clearError();
    const photos = [...(input.files || [])];
    if (photos.length < 1 || photos.length > 5) {
      event.preventDefault();
      return showError('Select between 1 and 5 photos.');
    }
    if (photos.length > availableSlots) {
      event.preventDefault();
      return showError(`Only ${Math.max(0, availableSlots)} photo slots remain for this vehicle.`);
    }
    if (photos.some(photo => !validTypes.has(photo.type))) {
      event.preventDefault();
      return showError('Only JPEG, PNG and WebP photos are supported.');
    }
    if (photos.some(photo => photo.size > fileLimit)) {
      event.preventDefault();
      return showError(`Each photo must be no larger than ${(fileLimit / mib).toFixed(1)} MB on this server.`);
    }
    if (photos.reduce((size, photo) => size + photo.size, 0) > postLimit) {
      event.preventDefault();
      return showError('The selected photos exceed the server request limit. Upload fewer photos at a time or reduce their size.');
    }
    submit.disabled = true;
    submit.textContent = 'Uploading photos…';
  });
  // Restore buttons if users return from a rejected request using browser history.
  window.addEventListener('pageshow', () => {
    submit.disabled = false;
    submit.textContent = originalLabel;
  });
})();
