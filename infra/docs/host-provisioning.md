# Carfolk LTD — host provisioning runbook

Status: template and instructions only; no live host, DNS or SSL was provisioned.

## Intended topology
Dedicated Linux server: Nginx, PHP 8.4-FPM, Composer 2, Node.js 22 release builds, PostgreSQL 16 on a private interface and valid TLS.
Allow public TCP 80/443 only, restrict SSH, and block PostgreSQL 5432/PHP-FPM from the internet.
Dedicated unprivileged deploy user owns releases, separate carfolk-backup service account can read backup credentials, PHP-FPM runs as www-data with write access to framework storage and shared images only.

Required directories:
/srv/carfolk/current -> releases/<timestamp>
/srv/carfolk/releases/<timestamp>/apps/api
/srv/carfolk/releases/<timestamp>/apps/web/dist
/srv/carfolk/shared/.env (mode 0600)
/srv/carfolk/shared/public-storage/ (persistent photos)
/srv/carfolk/shared/backups/ (restricted local backups)
/etc/carfolk/backup.env (restrict to backup service account)

## Before release
1. Obtain approved Carfolk domain and DNS/registrar control; issue TLS certificates.
2. Install PHP 8.4 extensions pdo_pgsql, gd, mbstring, fileinfo, xml, curl, exif and bcmath, Nginx, Composer 2, Node 22 and PostgreSQL 16 client.
3. Create private PostgreSQL database, least-privileged app role and encrypted offsite backup destination.
4. Copy apps/api/.env.production.example outside Git to /srv/carfolk/shared/.env; set a real APP_KEY and secrets; retain CARFOLK_LEADS_ENABLED=false and CARFOLK_SEO_ENABLED=false.
5. Configure Nginx using infra/nginx/carfolk.conf.template, replacing __DOMAIN__; verify PHP-FPM socket and cert paths, and run sudo nginx -t.
6. Write /etc/carfolk/backup.env as a root-managed service-owned secrets file: PGHOST, PGPORT, PGUSER, PGDATABASE, PGPASSWORD. Do not include secrets in command history.
7. Run and verify an initial local DB backup; run restore-drill.sh against a separate scratch database, verify offsite encrypted copies and alerting.
8. Verify dependency locks, run bash infra/scripts/preflight.sh, and request an authorized change window. Then explicitly invoke the deploy script.
9. Reload FPM and Nginx; run production-smoke.sh with the actual HTTPS origin; use approved test data to certify the browser buyer-to-admin journey.
10. Obtain approved privacy policy, inventory, contacts and operating hours; then enable leads. Turn on SEO and remove Nginx noindex ONLY after documented client sign-off.

## Backups and rollback
backup-db.sh creates a restricted PostgreSQL custom-format dump, integrity listing, SHA256 and 14-day local retention. It is not an offsite backup.
Restore drills must use carfolk_restore_drill and never overwrite production. Document actual RPO and RTO.
rollback-code.sh only swaps release symlink. It does NOT undo database migrations. Explicit schema compatibility assessment is required.
Enable timer units only after backup user/file permissions and restore drill have been verified. Monitor 5xx, SSL expiry, DB performance, disk, backup age and alert delivery.

## SEO limitation
The React app still uses hash navigation. Phase 6 adds crawlable server-rendered /listing/{slug} vehicle pages, XML sitemap and robots gating; complete application-wide SSR is not claimed.
GitHub Pages remains a nonproduction, noindex preview, with no live API credentials.

## Critical gates
Do not call the site launched from a green CI result alone. Actual domain/TLS, legal policy, real inventory, encrypted offsite backups, restoration proof, incident response, accessibility/performance, security checks and user acceptance must all be evidenced.
