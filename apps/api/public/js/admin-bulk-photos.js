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
  const optimizationStatus = form.querySelector('[data-optimization-status]');
  const previews = form.querySelector('[data-optimized-previews]');
  const optimizer = window.CarfolkImageOptimizer;
  if (![input, submit, error, selection, status, progress, progressText, retry, csrf, optimizationStatus, previews].every(Boolean)) return;

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
  const postLimit = Number.isFinite(configuredPostLimit)
    ? (configuredPostLimit === 0 ? Infinity : Math.max(0, configuredPostLimit - overhead))
    : 8 * MiB; // Conservative fallback when PHP does not report its limit.
  const existing = Math.max(0, Math.min(maxPerVehicle, Number(form.dataset.existingPhotos) || 0));
  const remainingSlots = maxPerVehicle - existing;
  const allowed = new Set(['image/jpeg', 'image/png', 'image/webp']);
  const originalLabel = submit.textContent;
  let batches = [];
  let completed = 0;
  let busy = false;
  let started = false;
  let preparing = false;
  let prepared = [];
  let selectionGeneration = 0;
  let previewUrls = [];

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
        throw new Error(`This photo could not be brought under the ${(fileLimit / MiB).toFixed(1)} MB server limit. Please reduce it manually.`);
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

  const clearPreviews = () => {
    previewUrls.forEach(url => URL.revokeObjectURL(url));
    previewUrls = [];
    previews.replaceChildren();
    previews.hidden = true;
  };

  const renderPreview = (originalFile, result) => {
    const card = document.createElement('div');
    card.className = 'bulk-photo-preview';
    const picture = document.createElement('img');
    const url = URL.createObjectURL(result.file);
    previewUrls.push(url);
    picture.src = url;
    picture.alt = `Optimized upload preview for ${originalFile.name}`;
    picture.loading = 'lazy';
    const details = document.createElement('div');
    details.className = 'bulk-photo-preview__details';
    const title = document.createElement('strong');
    title.textContent = originalFile.name;
    const info = document.createElement('span');
    const size = bytes => (bytes / MiB).toFixed(2) + ' MB';
    const resolution = result.dimensions ? ` · ${result.dimensions.width} × ${result.dimensions.height}px` : '';
    info.textContent = result.optimized
      ? `${size(result.originalBytes)} → ${size(result.outputBytes)}${resolution}`
      : `${size(result.outputBytes)} · Original kept`;
    details.append(title, info);
    card.append(picture, details);
    previews.append(card);
    previews.hidden = false;
  };

  const prepareSelection = async () => {
    if (started || busy) return;
    const generation = ++selectionGeneration;
    prepared = [];
    clearPreviews();
    clearError();
    showSelection();
    const files = [...input.files];
    if (!files.length) {
      preparing = false;
      submit.disabled = false;
      optimizationStatus.hidden = true;
      return;
    }
    preparing = true;
    submit.disabled = true;
    optimizationStatus.hidden = false;
    try {
      if (files.length > maxPerVehicle || files.length > remainingSlots) {
        throw new Error(`Select no more than ${remainingSlots} photo${remainingSlots === 1 ? '' : 's'} for this vehicle.`);
      }
      for (const [index, original] of files.entries()) {
        if (!isImage(original)) throw new Error('Only JPEG, PNG and WebP photos are supported.');
        optimizationStatus.textContent = `Preparing photo ${index + 1} of ${files.length}…`;
        const result = optimizer
          ? await optimizer.optimize(original, { maxBytes: fileLimit })
          : { file: original, originalBytes: original.size, outputBytes: original.size, optimized: false, dimensions: null };
        if (generation !== selectionGeneration) return; // Selection changed while encoding.
        prepared.push(result.file);
        renderPreview(original, result);
      }
      planBatches(prepared);
      if (generation !== selectionGeneration) return;
      const originalBytes = files.reduce((total, file) => total + file.size, 0);
      const resultBytes = prepared.reduce((total, file) => total + file.size, 0);
      const savedPercent = originalBytes > 0 ? Math.max(0, Math.round(100 * (1 - resultBytes / originalBytes))) : 0;
      optimizationStatus.textContent = `${files.length} photos ready · ${savedPercent}% less data · Preview below, then upload.`;
      submit.disabled = false;
    } catch (exception) {
      if (generation !== selectionGeneration) return;
      prepared = [];
      optimizationStatus.textContent = 'Photo preparation failed. No images were uploaded.';
      displayError(exception.message || 'One of the selected photos could not be prepared.');
    } finally {
      if (generation === selectionGeneration) preparing = false;
    }
  };
  input.addEventListener('change', () => { void prepareSelection(); });
  if (remainingSlots === 0) {
    input.disabled = true;
    submit.disabled = true;
    selection.textContent = 'This vehicle already has 20 photos. Remove one before uploading more.';
    optimizationStatus.hidden = true;
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
    if (preparing) {
      displayError('Photo optimization is still running. Upload will be available when previews are ready.');
      return;
    }
    try {
      if (!prepared.length) throw new Error('Choose photos and review the optimized previews first.');
      batches = planBatches(prepared);
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
    if (busy) {
      event.preventDefault();
      event.returnValue = '';
    }
    if (!busy) clearPreviews();
  });
})();
