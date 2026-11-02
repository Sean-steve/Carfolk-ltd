# Carfolk LTD — Phase O6: Centralized Error Handling, Recovery & Monitoring

## Scope and status

Implemented on a review branch, **not proof of production activation**.
The immediate incident was an HTTP 413 during admin vehicle-photo upload on HostPinnacle cPanel.

### O6 engineering changes

- A global request-correlation middleware creates a fresh server-side UUID for requests and includes `X-Request-ID` on responses. Laravel exception logs inherit the ID without logging customer form bodies.
- Laravel exception responses for HTTP 404/413/429/500/503 render safe branded HTML or consistent JSON with `message` and `error.code`/`error.request_id`. Existing 401/403, validation 422 and CSRF 419 behavior remain under Laravel control.
- Photo upload preflight rejects unsupported formats, batches exceeding five files, more than twenty per vehicle, files above 8 MB or smaller PHP upload limits, and multipart batches too large for `post_max_size`. The server **still validates every file**. Users keep their selection and can retry without submitting an invalid batch.
- The admin UI shows dismissible notices with transitions; Laravel form validation remains visible long enough to read. A React fallback handles unexpected rendering failures; the public API client recognizes even non-JSON server HTTP 413 responses.
- The existing `PhotoManager::addMany` database transaction and file-cleanup rollback logic remains unchanged.

### Production fix: HostPinnacle shared hosting

The Laravel code cannot increase Apache/PHP hosting limits or customize an HTTP 413 generated *before* PHP begins executing.

1. In **cPanel → MultiPHP INI Editor**, select **carfolkltd.com** and inspect the PHP version in **MultiPHP Manager**.
2. Suggested values (subject to the hosting plan's actual limits):
   - `upload_max_filesize = 10M`
   - `post_max_size = 50M`
   - `memory_limit = 256M`
   - `max_file_uploads = 20`
   - `max_execution_time = 120`
   - `max_input_time = 120`
3. Confirm the **web-served PHP runtime** sees those values; CLI PHP settings can differ. Allow for multipart overhead above five times 8 MB.
4. If a modest upload still returns 413, ask HostPinnacle to check Apache `LimitRequestBody`, ModSecurity/WAF audit events, proxy request-body caps and whether per-directory PHP INI overrides take effect. Do not disable ModSecurity wholesale.
5. Log in, add **one small JPEG**, then upload **five 6–8 MB images**, and confirm image URLs, photo count, public listing, and database references. Test on iPhone/mobile Safari. Do not use customer-owned photos without permission.
6. Verify a deliberately oversized browser selection is rejected **before network submission**, with a clear message and a usable file picker.
7. Keep `APP_DEBUG=false`; prefer `LOG_CHANNEL=daily`, `LOG_LEVEL=warning`, `LOG_DAILY_DAYS=14` for bounded Laravel logs. Logs must remain outside `public_html`. Review hosting server errors separately from Laravel logs.

On the current cPanel deployment topology, inspect the actual private Laravel installation, e.g.:

```bash
cd /home2/carfolkl/carfolk-api
php artisan about --only=environment
tail -n 80 storage/logs/laravel.log
```

The CLI command is a *diagnostic*, not proof of web-PHP upload settings. Never share raw logs if they contain secrets or customer details.

### Monitoring and recovery release gates

**Already implemented:** Laravel's exception logging, request IDs, 413 warning with request path and content length only, transactional photo rollback.

**Not yet activated/verified on production:** central alert delivery, a third-party error tracker, per-request server tracing, Apache/WAF log access, uptime/5xx/413 threshold alerts, and a live backup-and-restore drill. Do not claim active paging or complete observability until configured and verified with the operator.

Suggested alerts after monitoring integration: sustained 5xx errors, repeated 413s, admin login failure surges, unavailable `/up`, and unexpectedly failed photo batches. Protect customer privacy and limit retention.

### Certification checklist

- `php artisan test --filter=ErrorHandlingTest`
- `php artisan test` with PostgreSQL CI environment
- `npm test -- --run` and `npm run build` in `apps/web`
- Manual mobile and desktop test for upload success, client-side rejection, Laravel-generated 413, and host-generated 413
- API 404/413/429/500/503 JSON codes, no exception details; 401/403/419/422 unchanged
- Error reference ID in user response matches Laravel log context
- Retry and partial-upload rollbacks do not duplicate photographs
- Production backups and rollback plan verified before merging to `main` or enabling automatic deployment

The phase is **not complete** until CI is green and the live host settings and monitoring gates are verified.
