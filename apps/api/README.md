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

## Local development — Ubuntu/Linux (works with the React frontend)

### Prerequisites

- **PHP 8.3+** and required extensions: `pdo_pgsql`, `mbstring`, `xml`, `gd`, `curl`, `bcmath`, `fileinfo`.
- **Composer 2**.
- **Docker Engine + Docker Compose plugin**, with access to the Docker daemon (or an existing local PostgreSQL 16 server).
- Frontend already running on **http://localhost:5173** (Vite proxies `/api` and `/storage` to Laravel port **8000**).

Check your tools:

```bash
php -v
composer --version
docker compose version
docker info
```

### 1. Start PostgreSQL (terminal A)

From the repository root:

```bash
cd apps/api

# Choose your own local password and remember it for the .env file.
export CARFOLK_LOCAL_DB_PASSWORD='replace-with-your-own-local-db-password'

# Starts a PostgreSQL 16 container, bound to 127.0.0.1:5432.
docker compose up -d postgres
docker compose ps
```

The Compose service automatically creates the **carfolk** database with username **carfolk**. The PostgreSQL data is stored in the persistent Docker volume `carfolk_pgdata`. **Do not use `docker compose down -v`** unless you intend to destroy the local database.

If you use an existing PostgreSQL instance, skip Docker and configure the matching `DB_*` values instead.

### 2. Configure Laravel (same terminal A)

```bash
# Still inside apps/api
cp -n .env.example .env
nano .env
```

Set the following values in **apps/api/.env** (the password MUST match `CARFOLK_LOCAL_DB_PASSWORD` above):

```dotenv
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=carfolk
DB_USERNAME=carfolk
DB_PASSWORD=your-chosen-local-db-password

SESSION_DRIVER=database
SESSION_SECURE_COOKIE=false
CARFOLK_LEADS_ENABLED=false
```

Never commit **.env**. The default lead-collection flag stays **false** until the dealership has an approved privacy notice and operational process.

### 3. Install and initialize the backend (terminal A)

```bash
composer install
php artisan key:generate
php artisan migrate
php artisan storage:link
php artisan carfolk:admin
```

`php artisan carfolk:admin` asks interactively for the admin's full name, email and password (**12+ characters**). There is **no default admin login** and no sample vehicle inventory.

### 4. Run Laravel (terminal A)

```bash
php artisan serve --host=127.0.0.1 --port=8000
```

Keep this terminal open. Open:

| Location | URL |
| --- | --- |
| Backend health check | http://127.0.0.1:8000/up |
| Laravel administration login | http://127.0.0.1:8000/manage/login |
| Public inventory JSON | http://127.0.0.1:8000/api/v1/vehicles |
| Public dealership settings JSON | http://127.0.0.1:8000/api/v1/site |

### 5. Run/refresh the React frontend (terminal B)

```bash
# From repository root
cd apps/web
npm ci
npm run dev
```

Open **http://localhost:5173/#/cars**. Vite's existing proxy forwards **/api** and **/storage** requests to **http://127.0.0.1:8000**, so **no CORS configuration or API URL override is needed** for local development. The admin dashboard itself runs on **port 8000**, not inside the React app.

The catalogue will initially be **empty**. Log in at `/manage/login`, add a real vehicle in **Vehicle Inventory → Add Vehicle**, upload photos on its edit screen, set status **Available**, and tick **Publish publicly**. Refresh the frontend to see the listing.

**Enquiry and viewing forms:** The public backend intentionally responds with **503** and the frontend disables personal-data submission until the approved privacy policy and `CARFOLK_LEADS_ENABLED` are configured. This is expected, not a database connection error.

### Troubleshooting

| Symptom | Check / fix |
| --- | --- |
| `docker: command not found` or Compose unavailable | Install Docker Engine and the Compose plugin; start the Docker service. |
| Docker permission denied | Configure the local user for Docker access (or use an appropriately authorized `sudo docker compose`). |
| `port 5432 already in use` | Stop the conflicting local PostgreSQL service, or change Compose's host port and `DB_PORT` to match. |
| `SQLSTATE[08006]` / `connection refused` | Confirm `docker compose ps`, `docker compose logs postgres --tail=40`, and correct `DB_HOST` / `DB_PORT`. |
| `SQLSTATE[28P01]` / password authentication failed | Make `DB_PASSWORD` in `.env` match the Docker volume's **original** `POSTGRES_PASSWORD`. Changing the shell variable later does **not** reset an initialized database password. |
| `could not find driver` | Install/enable the PHP **pgsql** / **pdo_pgsql** extensions and restart PHP. |
| `No application encryption key` | Run `php artisan key:generate`. |
| Laravel reads old `.env` values | Run `php artisan optimize:clear` and restart `artisan serve`. |
| Frontend says live API disconnected / unavailable | Confirm Laravel is running on port **8000**, Vite is running on **5173**, and `http://localhost:5173/api/v1/site` returns JSON. Restart Vite after changing config. |
| Vehicles not visible on website | Add and publish vehicles in the admin. Draft/archived cars do not show. |
| Photo URL returns 404 | Run `php artisan storage:link`. |
| `composer install` fails on missing PHP extension | Use Composer's error details to install the named extension, then retry. |

To stop only the database container (without deleting data), use `docker compose down` from `apps/api`. For details on the routes, see the API section below.

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
- The repository now includes composer.lock and package-lock.json for deterministic installs. Refresh them only via reviewed dependency changes.

## Phase 5 lead acceptance gate

**Disabled by default.** Real public enquiries are accepted only when `CARFOLK_LEADS_ENABLED=true` and `CARFOLK_PRIVACY_URL` points to a client-approved policy. Never use the CI policy URL for production. `GET /api/v1/site` exposes the public flag and privacy URL; visitor forms remain disabled when approval is missing. Local same-origin web development uses Vite proxy on port 5173 to Laravel port 8000. CI browser tests use an isolated PostgreSQL database and test-only fixtures which are guarded against production execution.

## Phase 6 release controls

The API now provides server-rendered indexable listing pages at `/listing/{slug}`, a gated dynamic `/sitemap.xml`, and `/robots.txt`; search indexing is disabled by default. Privacy data retention is dry-run only unless both the approved policy and explicit execution are provided (`php artisan carfolk:privacy-prune` then `--execute`). Uploads are limited to JPEG, PNG and WebP; sensitive admin responses use no-store headers. See [Phase 6 docs](../../docs/PHASE_6_RELEASE.md) and [operator runbook](../../infra/docs/host-provisioning.md). Real production infrastructure, domain, policy, offsite backup and legal sign-off remain pending.
