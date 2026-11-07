# Carfolk O7.2 — 20-photo selection with safe bulk upload

## Scope

- One selection can include up to **20** JPEG/PNG/WebP images, subject to available slots on the vehicle.
- Browser uploads those images in **sequential requests of up to five** photos each, splitting sooner when the *actual web PHP* `post_max_size` would be exceeded. The existing 8 MiB per-photo and 20-photo per-vehicle limits remain authoritative in Laravel.
- Progress shows completed photos and remaining batches. If a request fails, upload pauses and the operator can retry **remaining photos**.
- Each batch has a browser-generated UUID, submitted as `client_batch_id` and persisted transactionally in `vehicle_photo_upload_batches`. Retrying the same token returns the previously saved images instead of creating duplicates, including if the prior HTTP response was lost.
- Existing non-JavaScript forms retain their 1–5-photo submission and server validation.
- Database migration, feature and real PostgreSQL/browser integration tests are included.
- The image form is provided by O7.1; this phase is stacked on its branch.

## Request contract

Existing `POST /manage/vehicles/{vehicle}/photos` supports:

- Multipart `photos[]` (1–5 files), optional `alt_text`, session CSRF `_token`, optional `client_batch_id` (UUID).
- Normal HTML submission: redirect plus flash.
- JSON/`Accept: application/json`: HTTP 201 with `{ok,uploaded,total,remaining,client_batch_id}`. Every idempotent replay returns the same existing images, not duplicates.

Uploads of more than five photos **in one HTTP request** are still rejected. Browsers submit 6–20 photos through multiple requests.

## Limits and recovery

- Browser preflights: file count, MIME/extension, nonzero file size, max of 8 MiB or effective web `upload_max_filesize` (whichever is lower), and `post_max_size` less multipart reserve.
- Sequential uploads protect shared-hosting resources; no parallel image bursts.
- A failed request preserves the unconfirmed batch and its ID, so pressing **Retry remaining photos** cannot generate duplicates when the original response was lost.
- Previously confirmed batches persist even if a later batch fails. This is **partial job completion**, not all-or-nothing atomicity across multiple requests.
- If the browser is closed mid-upload, staff should reload and check gallery count before selecting any remaining images. The pending local selection is not persisted across page reloads.
- Host-side Apache/WAF/proxy 413 responses cannot be eliminated by Laravel. For actual HostPinnacle upload-limit changes, use the O6.1 System Health page and hosting controls.
- **No client-side compression, resizing, drag-and-drop photo sorting, gallery reordering, or full vehicle listing preview** in this phase. O7.3/O7.4 cover those.

## Operator acceptance

After merging O7.1 and O7.2 in order, run migrations on the **authorized local/staging** Carfolk database. Do not use `migrate:fresh` on a populated environment.

```bash
cd apps/api
php artisan migrate
php artisan test --filter=BulkPhotoUploadTest
```

As an authenticated admin, open any eligible vehicle's `Edit → Vehicle photos`; select seven 1 MB images in one file picker and submit. Expect two requests (5 + 2), progress from 0 to 7, page reload, seven gallery photos and exactly one primary.

Repeat with 20 selected on a vehicle with no previous photos. Confirm a batch of six uploaded directly via Postman still fails. Check that selection beyond remaining capacity is blocked client-side and server-side.

To test safe retry, briefly interrupt a network response in a controlled local test environment only, then select **Retry remaining photos**. Confirm all resulting images are present exactly once and no extra photo rows were created.

**HostPinnacle:** The release must include `public/js/admin-bulk-photos.js`, a migration backup, verified `APP_DEBUG=false`, effective PHP limits, and real authorized post-deployment tests. GitHub CI passing alone is not proof of production activation.
