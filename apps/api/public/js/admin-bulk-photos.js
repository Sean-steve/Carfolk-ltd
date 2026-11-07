/* Carfolk O7.2: up to 20 photos selected once; sequential, retry-safe small requests. */
(() => {
  'use strict';

  const form = document.querySelector('[data-photo-upload]');
  if (!form || typeof fetch !== 'function' || !window.crypto?.getRandomValues) return;

  const input = form.querySelector('input[type="file"][name="photos[]"]');
  const submit = form.querySelector('button[type="submit"]');
  const error = form.querySelector('[data-upload-error]');
  const selection = form.querySelector('[data-upload-selection]');
  const status = form.querySelector('[data-bulk-status]');
  const progress = form.querySelector('[data-bulk-progress]');
  const progressText = form.querySelector('[data-bulk-progress-text]');
  const retry = form.querySelector('[data-bulk-retry]');
  const csrf = form.querySelector('input[name="_token"]');
  if (![input, submit, error, selection, status, progress, progressText, retry, csrf].every(Boolean)) return;

  const MiB = 1024 * 1024;
  const maxPerVehicle = 20;
  const maxPerRequest = 5; // Preserve Laravel's existing per-request limit.
  const overhead = 256 * 1024; // Multipart and CSRF overhead safety margin.
  const parseBytes = text => {
    const match = String(text ?? '').trim().match(/^(\d+(?:\.\d+)?)\s*([KMG])?B?$/i);
    if (!match) return NaN;
    return Number(match[1]) * ({ K: 1024, M: MiB, G: MiB * 1024 }[String(match[2] || '').toUpperCase()] || 1);
  };
  const configuredFileLimit = parseBytes(form.dataset.serverFileLimit);
  const configuredPostLimit = parseBytes(form.dataset.serverPostLimit);
  const fileLimit = Number.isFinite(configuredFileLimit) ? Math.min(8 * MiB, configuredFileLimit) : 8 * MiB;
  const postLimit = configuredPostLimit === 0 ? Infinity : configuredPostLimit - overhead;
  const existing = Math.max(0, Math.min(maxPerVehicle, Number(form.dataset.existingPhotos) || 0));
  const remainingSlots = maxPerVehicle - existing;
  const allowed = new Set(['image/jpeg', 'image/png', 'image/webp']);
  const originalLabel = submit.textContent;
  let batches = [];
  let completed = 0;
  let busy = false;
  let started = false;

  const newBatchId = () => {
    if (typeof window.crypto.randomUUID === 'function') return window.crypto.randomUUID();
    const bytes = window.crypto.getRandomValues(new Uint8Array(16));
    bytes[6] = (bytes[6] & 15) | 64;
    bytes[8] = (bytes[8] & 63) | 128;
    const hex = [...bytes].map(value => value.toString(16).padStart(2, '0')).join('');
    return [hex.slice(0, 8), hex.slice(8, 12), hex.slice(12, 16), hex.slice(16, 20), hex.slice(20)].join('-');
  };

  const displayError = message => {
    error.textContent = message;
    error.hidden = false;
    error.focus();
  };
  const clearError = () => {
    error.textContent = '';
    error.hidden = true;
  };
  const isImage = file => allowed.has(file.type) ||
    (!file.type && /\.(?:jpe?g|png|webp)$/i.test(file.name));

  const planBatches = files => {
    if (!files.length) throw new Error('Choose at least one photo.');
    if (files.length > 20) throw new Error('Select up to 20 photos at once.');
    if (files.length > remainingSlots) {
      throw new Error(`Only ${remainingSlots} photo slots remain for this vehicle.`);
    }
    const plans = [];
    let current = [];
    let bytes = 0;
    for (const file of files) {
      if (!isImage(file)) throw new Error('Only JPEG, PNG and WebP photos are supported.');
      if (!file.size || file.size > fileLimit) {
        throw new Error(`Each photo must be nonempty and no larger than ${(fileLimit / MiB).toFixed(1)} MB. Automatic compression is planned for the next phase.`);
      }
      if (!(postLimit > file.size)) {
        throw new Error('A selected photo exceeds the PHP request limit including multipart overhead. Reduce its size or increase the host limit.');
      }
      if (current.length === maxPerRequest || bytes + file.size >= postLimit) {
        plans.push({ id: newBatchId(), files: current });
        current = [];
        bytes = 0;
      }
      current.push(file);
      bytes += file.size;
    }
    if (current.length) plans.push({ id: newBatchId(), files: current });
    return plans;
  };

  const selectedCount = () => [...input.files].length;
  const showSelection = () => {
    if (started) return;
    const count = selectedCount();
    selection.textContent = count
      ? `${count} selected · ${remainingSlots} available slots · up to ${maxPerRequest} photos per request automatically`
      : `Select up to ${remainingSlots} photos at once; Carfolk will upload them in small batches.`;
  };
  input.addEventListener('change', () => {
    clearError();
    showSelection();
  });
  if (remainingSlots === 0) {
    input.disabled = true;
    submit.disabled = true;
    selection.textContent = 'This vehicle already has 20 photos. Remove one before uploading more.';
    return;
  }
  showSelection();

  const updateProgress = () => {
    const selected = completed + batches.reduce((total, batch) => total + batch.files.length, 0);
    progress.max = selected || 1;
    progress.value = completed;
    progressText.textContent = `${completed} of ${selected} photos uploaded. ${batches.length} batch${batches.length === 1 ? '' : 'es'} remaining.`;
  };

  const send = async batch => {
    const body = new FormData();
    body.set('_token', csrf.value);
    body.set('client_batch_id', batch.id);
    const alt = form.querySelector('input[name="alt_text"]')?.value ?? '';
    body.set('alt_text', alt);
    batch.files.forEach(file => body.append('photos[]', file, file.name));

    const response = await fetch(form.action, {
      method: 'POST',
      credentials: 'same-origin',
      headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
      body,
    });
    let payload = null;
    if ((response.headers.get('content-type') || '').includes('application/json')) {
      payload = await response.json().catch(() => null);
    }
    if (!response.ok) {
      if (response.status === 413) throw new Error('Host rejected this batch (HTTP 413). Smaller images or hosting limits are needed before retrying.');
      if ([401, 403, 419].includes(response.status)) throw new Error('Your admin session expired or access was denied. Reload and sign in before trying again.');
      if (response.status === 422) {
        const first = payload?.errors && Object.values(payload.errors).flat()[0];
        throw new Error(typeof first === 'string' ? first : 'The batch failed validation. Check the vehicle photo limit and file types.');
      }
      throw new Error(`Batch could not be confirmed (HTTP ${response.status}). Retry uses the same batch reference to avoid duplicate photos.`);
    }
    if (!payload?.ok || payload.client_batch_id !== batch.id ||
        !Number.isInteger(payload.uploaded) || payload.uploaded !== batch.files.length) {
      throw new Error('The upload response could not be verified. Retry safely using the original batch reference.');
    }
  };

  const run = async () => {
    if (busy || !batches.length) return;
    busy = true;
    retry.hidden = true;
    submit.disabled = true;
    input.disabled = true;
    status.hidden = false;
    clearError();
    submit.textContent = 'Uploading photos…';
    updateProgress();
    try {
      while (batches.length) {
        const current = batches[0];
        await send(current); // Keep this same token if a network response is lost.
        completed += current.files.length;
        batches.shift();
        updateProgress();
      }
      busy = false;
      progressText.textContent = `All ${completed} photos uploaded. Refreshing your gallery…`;
      window.history.replaceState(null, '', window.location.pathname + window.location.search + '#vehicle-photos');
      window.location.reload();
    } catch (exception) {
      busy = false;
      submit.textContent = 'Upload paused';
      retry.hidden = false;
      displayError(`${exception.message || 'Upload paused.'} Already uploaded batches are saved. Choose Retry remaining photos, or reload to review the gallery.`);
    }
  };

  form.addEventListener('submit', event => {
    event.preventDefault();
    if (started || busy) return;
    clearError();
    const files = [...input.files];
    try {
      batches = planBatches(files);
    } catch (exception) {
      displayError(exception.message);
      return;
    }
    started = true;
    completed = 0;
    run();
  });
  retry.addEventListener('click', run);
  window.addEventListener('beforeunload', event => {
    if (!busy) return;
    event.preventDefault();
    event.returnValue = '';
  });
})();
