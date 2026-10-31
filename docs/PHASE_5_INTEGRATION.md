# Phase 5 — Frontend–Backend Integration and Cross-System Certification

## Source of truth
The **Carfolk Laravel PostgreSQL database** is the only source for public inventory, images, availability and business contacts. The web frontend never substitutes invented or static demo listings.

### Public journeys
- Homepage: requests `GET /api/v1/vehicles`, displays published live stock or explicit empty/unavailable state.
- Catalog: `GET /api/v1/vehicles/options` for live make/model/body/etc. facets, server-side filtered `GET /api/v1/vehicles` with pagination.
- Details: `GET /api/v1/vehicles/{slug}` including ordered dealer-uploaded media and current availability.
- Compare: up to three published UUIDs, backed by `GET /api/v1/vehicles/compare?ids[]=...`; removed/unpublished inventory automatically leaves the shortlist.
- Financing: browser-only amortization calculator; when opened from a vehicle, pulls its **current published** price, never trusts a price from a URL.
- About/Contact: configured dealership site and verified contact channels, not invented defaults.
- Enquiry: POST `/api/v1/enquiries` with consent, optional real vehicle UUID, and validation. Only 201 creates an acknowledged lead.
- Viewing: POST `/api/v1/test-drives` with available published vehicle UUID, Nairobi-local preferred time, explicit consent. Returned **requested**, not a confirmed appointment.
- Admin: protected Laravel dashboard receives the persisted lead; operators update enquiry/viewing statuses and vehicle availability.

## Fail-closed publication
- GitHub Pages is a static preview and has **no configured API** by default: car listing sections show an honest disconnected state, not invented cars.
- Production deployments with the web + Laravel behind one origin use `/api/v1` via a reverse proxy. Local Vite proxies `/api` and `/storage` to Laravel at `127.0.0.1:8000`.
- Lead endpoints return **503** until *both* `CARFOLK_LEADS_ENABLED=true` and a valid, client-approved `CARFOLK_PRIVACY_URL` are configured. Frontend forms remain disabled until `/site` reports authorization.
- Customers only see success after HTTP 201. Validation errors appear in the form; failures must not simulate submission success.
- No personal enquiry data in localStorage, sessionStorage or browser analytics. Only vehicle comparison UUIDs are stored in sessionStorage.
- Public response excludes customer PII and private inventory stock references. Admin remains session- and CSRF-protected.
- Remote staging requires an explicitly configured approved HTTPS API with controlled CORS. The recommended production architecture is same-origin.

## Testing
1. Backend GitHub Actions: PostgreSQL 16 migrations and Laravel feature tests prove catalog, facets, comparison, gating and authenticated lead lifecycle.
2. Frontend tests: typed API mapping and HTTP error/no-success handling, query translation and rate-limit/error surfaces.
3. **Cross-system browser CI**: PostgreSQL service + Laravel server + Vite proxy + Chromium desktop/mobile. Ephemeral CI-only seed creates an administrator, two stock records and safe test contact information **only in CI testing**.
4. Browser drives published vehicle discovery, search, detail, vehicle-specific enquiry, viewing request, admin login, admin status updates, and sale transition. The database is isolated and removed with the CI runner.
5. GitHub Pages only validates static build/non-fabricated states; it does not host the live backend.

## Production gates still outstanding
- Obtain actual vehicle information, owned/licensed photographs, pricing and availability confirmation.
- Obtain verified Carfolk phone/WhatsApp/email, location/hours and a formally approved privacy notice including retention/deletion obligations.
- Deploy approved PHP runtime, PostgreSQL, TLS, file storage, backups, secrets, alerting and domain in Phase 6.
- Run real-domain human QA, accessibility, SEO, browser/device compatibility and security review.
- Resolve `composer.lock` for dependency determinism before release.
- Do **not** enable public lead collection on GitHub Pages without a configured, authorized and secure backend.

Model A exclusions remain: multi-dealer marketplace, payment processing, M-PESA deposits, lender decisions and vehicle title transfer.
