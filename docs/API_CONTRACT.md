# Planned API contract — NOT YET IMPLEMENTED

JSON API prefix: `/api/v1`, HTTPS.

## Public
- `GET /vehicles` — published inventory, paginated, filters for make/model/price/year/body/transmission/fuel/status.
- `GET /vehicles/{slug}` — published details and safe image URLs.
- `GET /site` — verified public dealership information.
- `POST /enquiries` — vehicle_id?, name, phone, email?, message, consent.
- `POST /test-drives` — vehicle_id, name, phone, requested_at, message?, consent.

## Authenticated single-dealership admin
- `GET /admin/summary`.
- `GET|POST /admin/vehicles`, `GET|PATCH|DELETE /admin/vehicles/{id}`.
- `POST /admin/vehicles/{id}/images`, `DELETE /admin/vehicles/{id}/images/{image_id}`.
- `GET|PATCH /admin/enquiries/{id}`, `GET|PATCH /admin/test-drives/{id}`.
- `GET|PATCH /admin/site`.

Use server-side validation, authorization, limits, spam controls and auditable status transitions. `price_kes` and `mileage_km` are nonnegative whole numbers. Timestamps are ISO-8601 UTC. Pagination and error format will be versioned in Phase 4.
