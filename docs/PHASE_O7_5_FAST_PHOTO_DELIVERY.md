# Carfolk O7.5 — Fast Photo Delivery / Existing Photo Optimization

## Why gallery images were slow

As of the November 7 live launch, existing vehicle photos are stored at their original upload resolution. In the customer UI, the tiny gallery thumbnail previously loaded the very same full-size photo as the main gallery. A known existing JPG file is roughly 2.8 MB and the host contained 70 original vehicle photo files. The O7.3 uploader only optimizes newly selected browser uploads, not earlier photos.

## Implemented

- Generate *three separate static WebP files* from existing approved vehicle JPEG/PNG/WebP originals using PHP GD:
  - **thumb**: max long edge **320px**, quality 72.
  - **card**: max long edge **640px**, quality 74.
  - **detail**: max long edge **1600px**, quality 78.
- Preserve original files byte-for-byte, their original URLs, and database image records.
- Each generated file is placed in the same vehicle's `vehicles/{id}/variants/` directory beside the original photos, so the existing public-storage symlink and known image extension allowlist remain valid.
- Public API returns **optional** `thumbnail_url`, `card_url`, `detail_url` per existing image; if a variant has not been created, the frontend falls back to `url`.
- Listing cards and comparisons use **card** variants, vehicle-gallery thumbnails use **thumb** variants, vehicle-gallery main image uses the **detail** variant. Lightbox loads the original only when opened.
- Off-screen gallery photos remain lazy-loaded. Photo images use asynchronous decode.
- New uploaded or replacement images receive variants on a best-effort basis *after* their database save. A variant failure never deletes or rolls back successfully uploaded originals. Removed/replaced images have their corresponding generated variants removed.
- Legacy JPEG EXIF orientation 2, 3, 4, 6 and 8 is respected where EXIF is available. The uncommon orientations 5 and 7 intentionally keep the original rather than generating incorrectly rotated derivatives.
- Large images above **20 MP**, images not in a supported format, and images without GD WebP support fall back to originals. They are not deleted or changed.

## Backfill safely on HostPinnacle

**Prerequisites:** deployment to `/home2/carfolkl/carfolk-api` completed from approved commit; existing database/photo backups verified; PHP 8.4 GD WebP encoder available; server confirms storage symlink and LiteSpeed access.

First check GD:

```bash
/opt/cpanel/ea-php84/root/usr/bin/php -r 'echo function_exists("imagewebp") ? "GD WEBP READY\n" : "GD WEBP MISSING\n";'
```

Check the original inventory; no changes yet:

```bash
cd /home2/carfolkl/carfolk-api
/opt/cpanel/ea-php84/root/usr/bin/php artisan carfolk:images:build --dry-run
```

Generate variants for a small canary subset (no original file is rewritten):

```bash
/opt/cpanel/ea-php84/root/usr/bin/php artisan carfolk:images:build --limit=5
```

Open a vehicle listing and confirm its thumbnail WebP URLs return `200 image/webp` before building the rest:

```bash
/opt/cpanel/ea-php84/root/usr/bin/php artisan carfolk:images:build
```

Dry-run output estimates how many derivatives are missing; command output reports generated variants and processing errors. If a photo is unreadable, unsupported or above safe decoding limit, the original remains available through the existing `url`.

**Do not run `--force` by default**; it overwrites optimized derivatives, and can make caches temporarily stale. Use only when a rebuild is deliberately approved. Never delete originals or run `artisan storage:link` against a working link unnecessarily.

## Verification

- Laravel/PostgreSQL tests cover legacy dry-run/backfill, preserving hash of the original, URL fallbacks, thumbnail sizes, replacement and deletion safety, and CLI limit validation.
- Frontend CI verifies typed optional fields and responsive routes.
- Browser CI checks buyer-to-admin journey and image display compatibility.
- After production deploy: compare browser Network panel for old JPG (~2.8MB) vs generated thumbnail WebP (expected far smaller, actual bytes vary by image), waterfall and page load time on a throttled mobile connection.
- Confirm actual 200 for the `thumbnail_url` and `detail_url` from the API after backfill.

## Safety and limits

No new database migration. Generated photos are **additional public web files**; available disk space matters. Shared hosting resources may cause individual encodes to be skipped. Uploads can take slightly longer because derivatives are generated after each successful batch. If HostPinnacle CPU/memory limits are reached, consider moving derivative generation to a cPanel cron instead of the upload request. The preexisting 20-photo and request-size limits remain unchanged.

The code release does not modify production automatically; the deployment gate and live operator verification remain required.
