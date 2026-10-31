# Carfolk LTD — Laravel/PostgreSQL Backend

Laravel **13.x**, PHP **8.3+**, PostgreSQL **16**. This is a real server-side application, distinct from the static GitHub Pages showroom.

## Features
- Secure single-dealership admin login at `/manage/login` (session cookies, CSRF, 5-attempts/minute rate limit, no public signup).
- Database-backed vehicle CRUD, publication and status control, non-destructive archiving.
- Real vehicle photo upload/delete (up to 20 images each, max 8MB, image MIME validation).
- Customer enquiry and viewing-request storage with consent timestamps; viewed and managed only by authenticated admins.
- Site contact/business details managed via admin; no invented phone/address/email.
- Versioned public REST API and same-origin, session-protected admin API.
- PostgreSQL migrations, activity audit entries, and automated tests against PostgreSQL.

## Local setup
Requires PHP 8.3+ (extensions pdo_pgsql, mbstring, gd, fileinfo, xml etc.), Composer 2 and Docker.

```bash
cd apps/api
export CARFOLK_LOCAL_DB_PASSWORD='choose-a-unique-local-secret'
docker compose up -d postgres
cp .env.example .env
# Set DB_PASSWORD equal to the local secret in .env (never commit .env)
composer install
php artisan key:generate
php artisan migrate
php artisan storage:link
php artisan carfolk:admin
php artisan serve --host=127.0.0.1 --port=8000
```

Visit `http://localhost:8000/manage/login`. Admin creation is interactive and requires a password of at least 12 characters. The database starts empty; do not seed fake cars or customer leads.

## API
- `GET /api/v1/vehicles`, `GET /api/v1/vehicles/{slug}`, `GET /api/v1/site`.
- `POST /api/v1/enquiries`, `POST /api/v1/test-drives` (rate-limited).
- `/api/v1/admin/*` protected by same-origin session authentication and CSRF; see `routes/api.php`.

See `../../docs/PHASE_5_INTEGRATION.md` for the full live React integration and certification gate.

## Run tests
A PostgreSQL instance named `carfolk_test` is expected in CI. The GitHub Actions backend workflow provisions it automatically, performs migrations and executes feature tests:

```bash
php artisan test
```

## Security / production
- Never deploy Laravel to GitHub Pages. Use a PHP application server with document root **`apps/api/public`** and TLS termination.
- Set `APP_ENV=production`, `APP_DEBUG=false`, `SESSION_SECURE_COOKIE=true`, strong `APP_KEY` and database secrets.
- Enforce same-origin for session-based administration; don't move session cookies across unrelated domains.
- Run `php artisan migrate --force` only after DB backups and migration approval.
- Configure a private network PostgreSQL database, public uploads with `storage:link`, backups, PII retention and monitoring.
- No production infrastructure or domain has been provisioned. Phase 5 removes active demonstration listings and connects the React app to the API in local and configured hosting; GitHub Pages is disconnected by default.
- Commit a resolved `composer.lock` before production release to guarantee reproducible dependency versions.

## Phase 5 lead acceptance gate

**Disabled by default.** Real public enquiries are accepted only when `CARFOLK_LEADS_ENABLED=true` and `CARFOLK_PRIVACY_URL` points to a client-approved policy. Never use the CI policy URL for production. `GET /api/v1/site` exposes the public flag and privacy URL; visitor forms remain disabled when approval is missing. Local same-origin web development uses Vite proxy on port 5173 to Laravel port 8000. CI browser tests use an isolated PostgreSQL database and test-only fixtures which are guarded against production execution.
