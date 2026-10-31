# Carfolk LTD — Current architecture (post Phase 4)

## Frontend
Vite + React 18 + TypeScript. Deployed as a **static preview** on GitHub Pages. Its sample inventory and browser-only enquiry validation are intentionally labelled and currently disconnected from the backend.

## Backend
Laravel **13.x**, PHP **8.3+**, PostgreSQL **16** implementation located at `apps/api`.
- Web admin login and administration at `/manage` (session authentication, CSRF, role check, throttled login).
- Public read-only vehicle and site endpoints plus rate-limited lead/viewing writes under `/api/v1`.
- Admin JSON endpoints under `/api/v1/admin/*` protected by same-origin `web` session/CSRF plus `auth` and `admin` middleware.
- Physical media on a configurable filesystem disk; public vehicle images served through Laravel's public storage link.
- PostgreSQL schema includes users, vehicles, images, enquiries, viewings, site settings and audit trails.
- Backend ships no demo inventory and no default administrator password.

## Security boundary
Only authenticated active dealership administrators may modify inventory, view customer PII or update business settings. Public endpoints expose published, nonarchived vehicles and approved contact information only. Requests require explicit consent. Rate limits and honeypots help protect public lead capture, with further production abuse monitoring needed.

**Admin integration must be same-origin** or use an explicitly designed authentication gateway. GitHub Pages is not an authorized administration origin. Do not place admin access tokens in localStorage, source control or logs.

## Data storage and time
PostgreSQL is the transactional source of truth. Vehicle price is integer KES. Appointment date/time input is interpreted as Africa/Nairobi and stored in UTC. Contact PII must be managed under client-approved privacy/retention policies.

## Deployment separation
- Local: Vite + Laravel server + PostgreSQL Docker.
- Pages: static React preview only.
- Production (Phase 6): TLS reverse proxy, PHP runtime, PostgreSQL on private network, storage, backups, monitoring, secret management and approved public domain. Do not point web server document root at the repository; use `apps/api/public`.

## Roadmap
Phase 5 will connect React catalogue and all public forms to real Laravel endpoints, remove synthetic stock, and validate the cross-system customer/operator lifecycle. Phase 6 will complete SEO, hosting and release certification.

## Extensibility
Only one dealership is supported in Model A; marketplace multi-tenancy and sales checkout are explicitly out of scope.
