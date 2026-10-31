# Carfolk Model A API contract — Phase 4 implementation

All endpoints are rooted at `/api/v1` and return JSON unless noted.

## Public read
- `GET /vehicles`: only published non-archived records. `q`, `make`, `model`, `body_type`, `fuel_type`, `transmission`, `status`, `price_min/max`, `year_min/max`, `sort`, `page`. `data: Vehicle[]` and `meta` fields.
- `GET /vehicles/{slug}`: `data: Vehicle`, 404 if unpublished/archived.
- `GET /site`: approved business identity, verified public contacts, location and hours (nullable).

## Public writes
- `POST /enquiries` with `vehicle_id?`, `name`, `phone`, `email?`, `message`, `consent=true`, optional empty `website` honeypot. Success: **201** `{data:{id,status:'new',message}}`.
- `POST /test-drives` with `vehicle_id`, `name`, `phone`, `email?`, `message?`, `preferred_date:YYYY-MM-DD`, `preferred_time:HH:mm` (Africa/Nairobi), `consent=true`. Success: **201**, status **requested**, not confirmed.

## Admin (authenticated server session + CSRF, `/admin`)
- `GET /summary`
- `GET|POST /vehicles`, `PATCH|DELETE /vehicles/{vehicle}` (DELETE performs archive)
- `POST /vehicles/{vehicle}/images` (multipart photo, alt_text optional)
- `DELETE /vehicles/{vehicle}/images/{image}`
- `GET /enquiries`, `PATCH /enquiries/{enquiry}` with status new/contacted/closed
- `GET /test-drives`, `PATCH /test-drives/{viewing}` with status requested/confirmed/completed/cancelled
- `GET|PATCH /site`

**Security:** admin API routes are inside Laravel's `web` session and CSRF middleware, with `auth` + `admin` authorization; not a cross-site token API. See `routes/api.php`.

## Constraints
All write payloads server-validated. Monetary fields are integer KES. Date timestamps stored in UTC and presented Africa/Nairobi. Public output omits private stock reference, customer contact data and admin notes. Contact form integration on React GitHub Pages remains disabled until Phase 5 and suitable hosting.
