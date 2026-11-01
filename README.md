# Carfolk LTD — Premium Dealership Website

**Business model:** Model A — own inventory only · **Brand:** Quality & Affordability.

## Project status

| Phase | Result |
|---|---|
| 0 — Foundation | React/TypeScript design system, Carfolk logo and CI |
| 1 — Landing | Branded landing page on GitHub Pages |
| 2 — Discovery | Browse, filter, vehicle gallery, compare and financing estimates (demo cars) |
| 3 — Conversion UX | About, Contact, Privacy preview, vehicle enquiry and viewing form designs (validation only) |
| **4 — Backend** | **Laravel 13, PostgreSQL migrations, admin dashboard, real inventory and lead endpoints, photo management and backend tests** |
| 5 — Integration | **Implemented:** Laravel-powered listings, customer lead capture (privacy-gated), status continuity, cross-system CI |
| 6 — Release | **Code implementation:** production infrastructure templates, SEO, security, backup/restore, release gate and dependency locks. Real launch blocked pending client and hosting sign-off. |

### Repositories and apps

```
apps/web/   React, TypeScript, Vite — GitHub Pages static frontend preview
apps/api/   Laravel 13, PHP 8.3+, PostgreSQL 16 — backend and admin application
docs/       Phase specifications, architecture, API/data contracts and certification notes
.github/    Frontend CI, backend CI and static preview deployment
```

The preview is at [Carfolk LTD on GitHub Pages](https://sean-steve.github.io/Carfolk-ltd/). It is a **static preview, not the live sales platform**. Because no backend is connected to Pages, the public showroom displays an unavailable/empty state rather than demonstration vehicles. The Laravel admin dashboard is available only when the backend runs on PHP-capable hosting.

### Start the frontend
```bash
cd apps/web
npm ci
npm run dev
npm run typecheck && npm run test && npm run build
```

### Start the backend (local Laravel + PostgreSQL)

Use **two terminals** — one for Laravel (port 8000), the other for the frontend (port 5173). Prerequisites: PHP 8.3+, Composer 2, Docker Engine + Compose.

**Terminal 1 — from repository root:**

```bash
cd apps/api
export CARFOLK_LOCAL_DB_PASSWORD='choose-your-own-local-db-password'
docker compose up -d postgres
cp -n .env.example .env
nano .env                          # Set DB_PASSWORD to the password above
composer install
php artisan key:generate
php artisan migrate
php artisan storage:link
php artisan carfolk:admin         # Creates your administrator account interactively
php artisan serve --host=127.0.0.1 --port=8000
```

**Terminal 2 — frontend (already running? Leave it running):**

```bash
cd apps/web
npm ci
npm run dev
```

Visit **http://127.0.0.1:8000/manage/login** for the admin dashboard, or **http://localhost:5173/#/cars** for the public catalogue. The Vite development proxy automatically connects `/api` and `/storage` to Laravel on port 8000. Add a vehicle in the admin and **publish** it to see it on the website. An empty catalogue at first is expected.

**Setup details, environment values and troubleshooting:** [Full backend setup guide](apps/api/README.md#local-development--ubuntulinux-works-with-the-react-frontend).


### Populate five cars for the local admin

After successful database migrations, run from `apps/api`:

```bash
php artisan carfolk:local-inventory
```

Log in at **http://127.0.0.1:8000/manage/login** and open **Vehicle Inventory**. Five editable draft vehicles are inserted once, with ordinary vehicle names and prices. Publish them from the admin if you want to see them on the local frontend. Use `php artisan carfolk:local-inventory --publish` on the *first run* to publish immediately (local database only). Existing staff edits are never overwritten; no photos are supplied. **Full instructions:** [Local inventory command](apps/api/README.md#add-five-vehicles-to-your-local-admin).

### Manage multiple photos and test enquiries locally

The admin now supports **1–5 photos per upload** (20 total per car), in **Vehicle Inventory → Edit → Vehicle photos**. For a local-only functioning enquiry form, set `CARFOLK_LOCAL_ENQUIRIES_ENABLED=true` in your `apps/api/.env` and restart Laravel; use invented contact details only. The local backend stores test enquiries and viewing requests in the admin inbox. This does not enable production customer-data collection.

[Complete photo upload and local enquiry instructions](apps/api/README.md#local-vehicle-galleries-and-enquiry-testing).

### Phase 5 cross-system integration

The React frontend connects to Laravel using same-origin `/api/v1` and `/storage` paths (proxied by Vite locally). On real hosting, an HTTPS reverse proxy must route these paths to Laravel. `CARFOLK_LEADS_ENABLED` is disabled by default, and a valid Carfolk-approved `CARFOLK_PRIVACY_URL` is required to collect enquiries. See [Phase 5 integration](docs/PHASE_5_INTEGRATION.md) for evidence, browser tests and rollout limits.

### Phase 6 launch controls

Production Nginx, deployment scripts, SSL/SEO toggles, backup/restore tools and launch acceptance requirements are under [infra](infra/) and [Phase 6 Release](docs/PHASE_6_RELEASE.md). The project is **not publicly launched**: no Carfolk domain, server, approved inventory or privacy sign-off has been supplied. GitHub Pages stays noindex/disconnected. Both PHP and npm dependencies are locked and audited in CI.

### Model A documentation
- [Delivery roadmap](docs/ROADMAP.md)
- [Backend Phase 4](docs/BACKEND_PHASE_4.md)
- [Architecture](docs/ARCHITECTURE.md)
- [API contract](docs/API_CONTRACT.md)
- [Scope and exclusions](docs/MODEL_A_SCOPE.md)
- [Design system](docs/DESIGN_SYSTEM.md)
- [Phase 1](docs/PHASE_1_LANDING.md), [Phase 2](docs/PHASE_2_DISCOVERY.md), [Phase 3](docs/PHASE_3_CONVERSION.md)

### Client data still required
Carfolk must verify actual vehicles/photos/prices, business phone/WhatsApp/email, location, opening hours, and an approved production privacy notice. No fictitious stock, customers or business performance metrics should enter production.

### Constraints
- GitHub Pages cannot run Laravel or PostgreSQL.
- React and Laravel are integrated in local/CI environments, but there is no public production server.
- The project excludes public marketplace sellers, checkout, M-PESA, automated financing approvals, NTSA ownership transfer and native mobile applications.
