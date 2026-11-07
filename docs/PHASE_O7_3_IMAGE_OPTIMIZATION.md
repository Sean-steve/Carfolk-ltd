# Phase O7.3 — Automatic Vehicle Photo Optimization and Compression

## Implemented

1. Selecting vehicle photographs (up to the remaining capacity of 20) immediately begins **local, sequential** image preparation in the administration browser. Originals remain on the operator's own device.
2. JPEG/PNG/WebP images up to **60 MiB original file size** and **64 megapixels** are inspected. Small files already under the target size and under the resolution cap are retained unmodified.
3. Files exceeding the target size, or with a long edge over **2560 pixels**, are redrawn on canvas and encoded to WebP where supported. Progressive quality/size attempts target **approximately 1.5 MiB per photo**; this is a best-effort size, not a quality or fixed-output guarantee.
4. Modern browsers use EXIF-aware image decoding for correct orientation. Canvas re-encoding strips the original metadata, including GPS EXIF, from **re-encoded** images. Originals that are small enough to remain unchanged are not guaranteed to have metadata stripped. The original files are never uploaded when compression produces a replacement.
5. Operators see a *preview of each selected upload*, plus per-photo before/after file size and a summary of saved data, **before clicking Upload**.
6. Only the prepared files go to the existing O7.2 sequential 1–5-photo batches; the server's actual `upload_max_filesize`, `post_max_size`, and 8 MiB application cap remain enforced. A 413 produced by Apache, ModSecurity or a proxy still needs hosting configuration.
7. Server-side `PhotoImageGuard` checks readable dimensions and a **64-megapixel maximum**, without decoding full-size bitmaps in shared-hosting PHP. The existing Laravel MIME/type, size, role and 20-photo restrictions remain.
8. Uploaded images continue to use PostgreSQL records, existing rollback behavior and idempotent batch receipt references.

## Scope boundaries

- **This is device-side image optimization**. If JavaScript is disabled/unavailable, the existing form remains functional only for files already within the server's upload constraints (up to five per HTTP request). HostPinnacle may reject oversize requests before Laravel can compress them, so optimizing solely on the server would not fix HTTP 413.
- Generated WebP transparency is preserved in current browsers that support WebP canvas encoding. If encoding is unsupported, browsers may use PNG fallback; exceptionally large outputs are rejected with a readable message rather than uploading a broken file.
- 60 MiB is the original **input cap**, **not** the HTTP upload limit: files must be optimized below the effective server upload cap before sending.
- Photos above 64 megapixels, undecodable images, unsupported HEIC files, files that cannot fit the server limit after optimization and resource-constrained browsers receive a clear error rather than unsafe best-effort upload.
- The preview in O7.3 is an **upload preview**, not the full unpublished vehicle listing preview (O7.4).
- The existing public gallery, deletion, ordering and primary-photo semantics remain unchanged.

## Automated certification

- Browser integration checks use Chromium on real PostgreSQL, including 20-image select/preview/upload and an in-browser deliberately >8 MiB high-resolution source compressed to an image under 8 MiB with a maximum long edge of 2560.
- Backend feature tests cover image header validation, the pixel cap, invalid images, existing photo permissions and upload invariants.
- Production readiness still requires **mobile Safari**, modern Android Chrome, transparency and EXIF-rotation checks, upload of representative real vehicle photos, storage verification, host limit verification and testing for interrupted uploads.

## Local test (O7.1 → O7.2 → O7.3 stacked)

```bash
cd ~/Sites/Carfolk-ltd
git fetch origin
git switch phase/o7-3-image-optimization
cd apps/api
php artisan migrate        # new O7.2 receipt table; do not migrate:fresh on real data
php artisan serve --host=127.0.0.1 --port=8000
```

Sign into `http://localhost:8000/manage/login`, open a vehicle, then choose photos in the visible photo workspace. Select one >8 MiB JPEG/PNG, inspect the optimized size and preview, click Upload, and confirm the stored photograph appears and loads in the public listing. For a complete flow, select 20 varied JPEG/PNG/WebP images (within the 60 MiB original cap each), confirm preview count and upload progress, and check exactly 20 images/one primary image after completion.

If the site's PHP limits are smaller than the optimized output, correct MultiPHP INI settings or upload fewer/smaller files as the UI advises. The **effective web PHP** limit is visible on `/manage/system-health`. Do not upload real customer information or unapproved copyrighted imagery.

## Hosting and release gate

This branch builds upon pending O7.1 PR #26 and O7.2 PR #27 and **must not be deployed out of order**. After each upstream change is approved, merge in order to `main`, run GitHub CI, ensure a restorable backup, deploy the new JS assets and Laravel code via the existing HostPinnacle release process, then certify the live upload outcomes. Merging to GitHub alone does not activate the PHP host changes.
