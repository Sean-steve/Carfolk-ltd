# Phase 4 — Laravel Backend, PostgreSQL Database & Dealership Administration

## Scope delivered
A Laravel 13/PHP 8.3+ backend at `apps/api` with a real PostgreSQL schema, one dealership's administration workspace, media storage, public endpoints and guarded administration API.

### Public endpoints (JSON)
- `GET /api/v1/vehicles`: published/unarchived listings, pagination, make/model/body/fuel/transmission/status, price/year ranges, keyword and sorting.
- `GET /api/v1/vehicles/{slug}`: one published non-archived vehicle, ordered photo URLs.
- `GET /api/v1/site`: client-configured business name, tagline, verified contact details and about copy.
- `POST /api/v1/enquiries`: general or vehicle enquiry, explicit consent, optional vehicle reference, 5/min rate limit.
- `POST /api/v1/test-drives`: published available vehicle, preferred date/time in Africa/Nairobi, explicit consent, 5/min rate limit. Creation means **requested**, not confirmed.

### Dealership admin (server-rendered)
- `/manage/login`: credential-only, session protected, throttled; provision admin interactively using `php artisan carfolk:admin`; no public registration.
- `/manage`: truthful DB counts for stock, published vehicles, new enquiries, requested viewings.
- `/manage/vehicles`: create/edit/archive cars; price and stock ref validation, publish toggle, available/reserved/sold and featured flags.
- Vehicle photo upload/delete (JPEG/PNG/WebP, 8 MB maximum, up to 20 per vehicle).
- `/manage/enquiries`, `/manage/viewings`: protected lead/request inboxes, status updates.
- `/manage/settings`: approved business contact details, location, opening hours and about.
- `/api/v1/admin/*`: versioned CRUD, photo, lead and site endpoints protected by server-side **same-origin session auth + CSRF**. Not a public bearer-token API.

### PostgreSQL data
`users` (is_admin,is_active), `vehicles`, `vehicle_images`, `vehicle_enquiries`, `test_drive_requests`, `site_settings`, `audit_events`; Laravel sessions, cache and job tables. UUID primary keys for vehicle/lead records, referential constraints, status indexes and publication guards. Vehicle archiving hides inventory without erasing enquiries.

### Safety
- No fake default inventory, contact information, reviews, payments or customers.
- Forms are validated server-side; public leads require consent and are rate limited.
- No sensitive information is emitted from public vehicle resources or logs.
- No client-app auth token is stored in browser localStorage.
- Public messages aren't mailed automatically; admin sees stored leads. Add notifications only after delivery and recipient controls are tested.
- Uploaded images are validated and stored on `public` disk; ensure file review and legal image ownership before launch.
- Site identity is editable but never filled with unverified contact details.

## Verification
GitHub backend CI targets PostgreSQL 16 and Laravel 13 with PHP 8.4; PHP lint, migrations and automated feature tests. A successful workflow is **repository-level certification**, not production runtime validation.

## Separate from static GitHub Pages
The React GitHub Pages demo still uses explicitly marked illustrative stock and **does not yet submit leads to the API**. Real cross-system integration, user journey certification, production hosting and release are Phase 5–6. Host the UI and backend behind the same origin (or approved session-safe reverse proxy) before connecting admin session endpoints.

## Known production prerequisites
1. Get Carfolk-approved vehicle data and photos, phone, WhatsApp, email, location, hours and privacy terms.
2. Generate and commit Composer lockfile; review dependency vulnerabilities.
3. Deploy PHP 8.3+, PostgreSQL privately, verified domain, TLS, web root `apps/api/public` and proper storage permissions.
4. Configure backups, audit monitoring, customer lead retention/deletion and the business' consent notice.
5. Perform actual browser-driven QA and operator permission/security penetration checks.
6. Configure frontend API base URL, remove static demo stock, perform staging end-to-end tests before claiming production readiness.

**This phase does not include M-PESA payments, multi-dealer marketplace, finance approvals or native mobile apps.**
