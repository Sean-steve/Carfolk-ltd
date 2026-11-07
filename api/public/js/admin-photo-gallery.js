/* Carfolk O7.4: accessible gallery ordering and browser-optimized photo replacement. */
(() => {
  'use strict';

  const gallery = document.querySelector('[data-gallery-sort]');
  const orderForm = document.querySelector('[data-photo-order]');
  const optimizer = window.CarfolkImageOptimizer;

  if (gallery && orderForm) {
    const cards = () => [...gallery.querySelectorAll('[data-image-id]')];
    const inputs = orderForm.querySelector('[data-order-inputs]');
    let dragging = null;

    const syncOrder = () => {
      const items = cards();
      inputs.replaceChildren(...items.map((card, index) => {
        const field = document.createElement('input');
        field.type = 'hidden';
        field.name = 'order[]';
        field.value = card.dataset.imageId;
        card.querySelector('.vehicle-image__position').textContent = `Photo ${index + 1}`;
        return field;
      }));
    };

    gallery.addEventListener('dragstart', event => {
      const card = event.target.closest('[data-image-id]');
      if (!card || event.target.closest('input,button,textarea,select')) {
        event.preventDefault();
        return;
      }
      dragging = card;
      event.dataTransfer.effectAllowed = 'move';
      event.dataTransfer.setData('text/plain', card.dataset.imageId);
      card.classList.add('vehicle-image--dragging');
    });
    gallery.addEventListener('dragover', event => {
      if (!dragging) return;
      const other = event.target.closest('[data-image-id]');
      if (!other || other === dragging) return;
      event.preventDefault();
      event.dataTransfer.dropEffect = 'move';
      const box = other.getBoundingClientRect();
      const before = event.clientX < box.left + box.width / 2;
      gallery.insertBefore(dragging, before ? other : other.nextElementSibling);
      syncOrder();
      orderForm.classList.add('vehicle-photo-order-form--changed');
    });
    gallery.addEventListener('drop', event => {
      if (dragging) event.preventDefault();
    });
    gallery.addEventListener('dragend', () => {
      dragging?.classList.remove('vehicle-image--dragging');
      dragging = null;
    });
    syncOrder();
  }

  const parseBytes = text => {
    const m = String(text || '').trim().match(/^(\d+(?:\.\d+)?)\s*([KMG])?B?$/i);
    if (!m) return NaN;
    const unit = (m[2] || '').toUpperCase();
    return Number(m[1]) * ({ K: 1024, M: 1024 * 1024, G: 1024 ** 3 }[unit] || 1);
  };

  document.querySelectorAll('[data-photo-replace]').forEach(form => {
    const input = form.querySelector('input[type=file]');
    const submit = form.querySelector('button[type=submit]');
    const status = form.querySelector('[data-replace-status]');
    if (!input || !submit || !status) return;

    let preparing = false;
    let ready = false;
    let generation = 0;
    const fileLimit = Math.min(8 * 1024 * 1024, parseBytes(form.dataset.serverFileLimit) || 8 * 1024 * 1024);
    input.addEventListener('change', async () => {
      const current = ++generation;
      ready = false;
      const original = input.files?.[0];
      if (!original) return;
      if (!optimizer) {
        ready = true;
        status.textContent = 'Browser optimization unavailable. Photos must be within the server limit.';
        return;
      }
      preparing = true;
      submit.disabled = true;
      status.textContent = 'Optimizing replacement photo…';
      try {
        const result = await optimizer.optimize(original, { maxBytes: fileLimit });
        if (current !== generation) return;
        if (typeof DataTransfer !== 'function') throw new Error('This browser does not support setting the optimized photo in the upload field.');
        const selection = new DataTransfer();
        selection.items.add(result.file);
        input.files = selection.files;
        ready = true;
        status.textContent = result.optimized
          ? `Ready · ${(original.size / 1048576).toFixed(2)} MB → ${(result.outputBytes / 1048576).toFixed(2)} MB. Original remains on your device.`
          : 'Ready · Original photo already meets upload requirements.';
      } catch (error) {
        status.textContent = error.message || 'This photo cannot be prepared. Choose another photo.';
        ready = false;
      } finally {
        if (current === generation) {
          preparing = false;
          submit.disabled = !ready;
        }
      }
    });
    form.addEventListener('submit', event => {
      if (preparing || !ready && optimizer) {
        event.preventDefault();
        status.textContent = 'Select a valid replacement and finish optimization before uploading.';
      }
    });
  });
})();
