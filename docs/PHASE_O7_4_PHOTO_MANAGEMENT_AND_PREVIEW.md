# Carfolk Phase O7.4 — Advanced Vehicle Photo Management & Listing Preview

## Delivered

### Advanced photo editor
- Admin-only **Set as main photo** action. The original photo identity remains intact and exactly one photo is flagged primary.
- Persisted **photo ordering**. Desktop can drag cards; operator clicks **Save photo order** to commit. Each card also has **Move up** and **Move down** actions that work without JavaScript and on mobile/keyboard.
- **Replace photo** per existing image without changing its record ID, display position, caption, or primary flag. With JavaScript and a supported browser, replacements use O7.3's local WebP resizing/compression before uploading. Otherwise the normal PHP file limits apply.
- Editable **photo description / alt text** and existing **Remove photo** action.
- Admin mutations check that the target photo belongs to that vehicle, refuse archived vehicles and use a vehicle row lock and transactions. Reordering requires the exact set of current image IDs and rejects stale/malformed submissions.
- Changes are logged through the existing Carfolk audit event system. Photo replacements clean up the old file after the database commit; a failed database update cleans up the newly uploaded file.

### Full saved listing preview
- Admin-only `GET /manage/vehicles/{vehicle}/preview`, available from **Vehicle Inventory** and **Edit Vehicle**.
- Buyer-style **full listing preview** shows saved make/model/year/trim, KES price, status, location, featured gallery/thumbnail strip, description, full available specifications and features.
- The primary image is shown as cover; all uploaded gallery photos are displayed in saved order.
- Buyer actions are visibly disabled (no real enquiry/viewing submission from a preview).
- Unpublished drafts can be inspected without publishing them. Public API, catalogue and SEO continue to exclude drafts.
- Preview is protected by the existing authenticated active-admin middleware with `Cache-Control: private, no-store` and `X-Robots-Tag: noindex,nofollow,noarchive`. **The preview page itself is private.** Existing public storage URLs for image files are not transformed into private image URLs; a future storage-security requirement would require a separate asset authorization design.
- The preview reproduces saved buyer-facing **content** and the principal listing structure in a Carfolk-styled Blade view. It is **not guaranteed pixel-for-pixel identical** to the React buyer page or representative of unsaved form edits. Changes must be saved before previewing.

### Scope and constraints
- The earlier O7.1 visible-photo workflow, O7.2 batch selection and O7.3 browser-side optimization are inherited because O7.4 is stacked on O7.3.
- Existing per-photo server limit (8 MiB), limit of five per HTTP request and 20 photos per vehicle remain enforced.
- O7.4 introduces no new database migrations beyond O7.2's `vehicle_photo_upload_batches` receipts.
- HostPinnacle PHP/Apache/ModSecurity HTTP 413 limits may still require configuration and actual live photo testing.

## Verification

Automated checks:
- Laravel/PostgreSQL feature suite including `AdvancedPhotoManagementTest` checks RBAC, draft preview no-index/no-store, public API isolation, reorder, moving, cover selection, 20-photo invariants, safe replacement, alt text and cross-vehicle rejection.
- Desktop browser against PostgreSQL checks 20 selected photos, changing the primary image and opening a complete read-only draft preview. Mobile browser suite checks existing admin and showroom responsiveness.
- Frontend and backend CI must pass before review approval.

Local test (make sure PostgreSQL is reachable and `carfolk` DB credentials are configured first):

```bash
cd ~/Sites/Carfolk-ltd
git fetch origin
git switch phase/o7-4-photo-management-preview
cd apps/api
php artisan migrate
php artisan test --filter=AdvancedPhotoManagementTest
php artisan serve --host=127.0.0.1 --port=8000
```

Sign in at `http://localhost:8000/manage/login`. Go to Vehicle Inventory → Edit → Vehicle photos and test move, drag then save, set main, replace and caption. Click **Preview complete listing** and verify the updated primary and photo order, price, details and disabled buyer CTAs. Confirm the same unpublished vehicle returns 404 from the public vehicle API.

## Deployment gate

PR dependencies must be merged in this order: **O7.1 #26 → O7.2 #27 → O7.3 #28 → O7.4 #29**, each reviewed and verified against updated `main`. Back up PostgreSQL and uploaded photos before enabling the existing cPanel release process. Run `php artisan migrate` on the correct host **only after** backup; never run `migrate:fresh` against real customer inventory. Deploy and verify the new PHP, Blade, CSS and JS assets; check authenticated preview, production images, file replacements and request limits.

Until production activation and live checks are carried out, **do not claim deployment to carfolkltd.com**.
