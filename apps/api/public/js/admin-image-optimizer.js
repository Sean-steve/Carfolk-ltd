/* O7.3 — Device-side photo resizing before HTTP submission (no remote processing). */
(() => {
  'use strict';

  const MiB = 1024 * 1024;
  const DEFAULT_TARGET = Math.floor(1.5 * MiB);
  const MAX_SOURCE_BYTES = 60 * MiB;
  const MAX_SOURCE_PIXELS = 64 * 1000 * 1000;
  const MAX_LONG_EDGE = 2560;
  const valid = new Set(['image/jpeg', 'image/png', 'image/webp']);

  const loadImageFallback = file => new Promise((resolve, reject) => {
    const url = URL.createObjectURL(file);
    const image = new Image();
    image.onload = () => resolve({ image, url });
    image.onerror = () => {
      URL.revokeObjectURL(url);
      reject(new Error('The image cannot be read. Please choose a valid JPEG, PNG or WebP photo.'));
    };
    image.src = url;
  });

  const encode = (canvas, quality) => new Promise((resolve, reject) => {
    canvas.toBlob(blob => {
      if (!blob || !blob.size || !valid.has(blob.type)) {
        reject(new Error('Your browser could not compress this photo. Try another browser or reduce the image manually.'));
      } else resolve(blob);
    }, 'image/webp', quality);
  });

  async function optimize(file, options = {}) {
    if (!(file instanceof Blob) || !valid.has(file.type) || file.size === 0) {
      throw new Error('Choose a valid, nonempty JPEG, PNG or WebP photograph.');
    }
    if (file.size > MAX_SOURCE_BYTES) {
      throw new Error('This original image is larger than 60 MB. Reduce it before selecting it.');
    }
    const maxBytes = Number.isFinite(options.maxBytes) ? Math.min(options.maxBytes, 8 * MiB) : 8 * MiB;
    const targetBytes = Math.min(DEFAULT_TARGET, Math.floor(maxBytes * 0.75));
    if (maxBytes <= 0 || targetBytes <= 0) throw new Error('This server does not permit photo uploads at its current PHP limit.');
    // Small images already under the target stay unchanged, including transparent PNGs.
    if (file.size <= targetBytes) {
      return { file, originalBytes: file.size, outputBytes: file.size, optimized: false, dimensions: null };
    }

    let bitmap = null;
    let loaded = null;
    try {
      if (typeof createImageBitmap === 'function') {
        try {
          // Modern browsers honor EXIF orientation here, before drawing to canvas.
          bitmap = await createImageBitmap(file, { imageOrientation: 'from-image' });
        } catch (_) {
          loaded = await loadImageFallback(file);
        }
      } else {
        loaded = await loadImageFallback(file);
      }
      const image = bitmap || loaded.image;
      const width = bitmap ? bitmap.width : image.naturalWidth;
      const height = bitmap ? bitmap.height : image.naturalHeight;
      if (!width || !height || width * height > MAX_SOURCE_PIXELS) {
        throw new Error('This image resolution is too large to process safely on this device (maximum 64 megapixels).');
      }
      const ratio = Math.min(1, MAX_LONG_EDGE / Math.max(width, height));
      let edgeW = Math.max(1, Math.round(width * ratio));
      let edgeH = Math.max(1, Math.round(height * ratio));
      const canvas = document.createElement('canvas');
      const ctx = canvas.getContext('2d', { alpha: true });
      if (!ctx) throw new Error('Your browser does not support image compression.');
      ctx.imageSmoothingEnabled = true;
      ctx.imageSmoothingQuality = 'high';
      let best = null;
      let bestWidth = edgeW;
      let bestHeight = edgeH;

      // WebP retains transparency and removes original EXIF/GPS metadata.
      for (let scaleAttempt = 0; scaleAttempt < 3; scaleAttempt++) {
        canvas.width = edgeW;
        canvas.height = edgeH;
        ctx.clearRect(0, 0, edgeW, edgeH);
        ctx.drawImage(image, 0, 0, edgeW, edgeH);
        for (const quality of [0.84, 0.72, 0.58]) {
          const blob = await encode(canvas, quality);
          if (!best || blob.size < best.size) {
            best = blob;
            bestWidth = edgeW;
            bestHeight = edgeH;
          }
          if (blob.size <= targetBytes) break;
        }
        if (best && best.size <= targetBytes) break;
        edgeW = Math.max(1, Math.round(edgeW * 0.8));
        edgeH = Math.max(1, Math.round(edgeH * 0.8));
      }
      canvas.width = 0;
      canvas.height = 0;
      if (!best || best.size > maxBytes) {
        throw new Error('This photo remains too large after optimization. Reduce it manually before uploading.');
      }
      const changedResolution = Math.max(width, height) > MAX_LONG_EDGE;
      const pickOptimized = file.size > maxBytes || best.size < file.size || changedResolution && best.size <= file.size * 1.25;
      if (!pickOptimized) {
        return { file, originalBytes: file.size, outputBytes: file.size, optimized: false, dimensions: { width, height } };
      }
      const baseName = (file.name || 'vehicle-photo').replace(/\.[^.]+$/, '');
      const output = new File([best], baseName + '.webp', { type: best.type, lastModified: Date.now() });
      return {
        file: output,
        originalBytes: file.size,
        outputBytes: output.size,
        optimized: true,
        dimensions: { width: bestWidth, height: bestHeight },
      };
    } finally {
      bitmap?.close();
      if (loaded) URL.revokeObjectURL(loaded.url);
    }
  }

  window.CarfolkImageOptimizer = Object.freeze({
    optimize,
    config: Object.freeze({
      maxLongEdge: MAX_LONG_EDGE,
      targetBytes: DEFAULT_TARGET,
      maxSourceBytes: MAX_SOURCE_BYTES,
      maxSourcePixels: MAX_SOURCE_PIXELS,
    }),
  });
})();
