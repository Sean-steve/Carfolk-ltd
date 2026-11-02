#!/usr/bin/env bash
# Execute only as the cPanel account owner over verified SSH, from the guarded Actions job.
set -euo pipefail
umask 077
die(){ printf 'DEPLOY ABORTED: %s\n' "$*" >&2; exit 1; }
[[ $# -eq 1 && "$1" =~ ^[a-f0-9]{40}$ ]] || die "Expected a 40-character Git commit SHA"
sha="$1"
root="$HOME/carfolk"
docroot="$HOME/public_html"
shared="$root/shared"
package="$root/incoming/$sha.tar.gz"
release="$root/releases/$sha"
[[ -f "$package" ]] || die "Release package is missing"
[[ ! -e "$release" ]] || die "This release already exists; no overwrite permitted"
[[ -d "$docroot" ]] || die "Document root is missing"
mkdir -p "$root/releases" "$shared/backups"
# Validate account, PHP, private env, DB backup prerequisites and initial-install permissions.
# The preflight file is shipped in the package; unpack to a new private release directory first.
mkdir "$release"
tar -xzf "$package" -C "$release" || die "Corrupt release archive"
[[ -f "$release/infra/cpanel/preflight.sh" && -f "$release/infra/cpanel/public.htaccess" ]] || die "Not a cPanel release"
phpbin="$(bash "$release/infra/cpanel/preflight.sh" | tail -n 1)"
[[ -x "$phpbin" ]] || die "PHP runtime not executable"
export PATH="$(dirname "$phpbin"):$PATH"
[[ "$(command -v php)" == "$phpbin" ]] || die "Composer and php must use the same intended CLI PHP"
# Never keep the development storage directory or any application secrets in the release.
[[ ! -e "$release/apps/api/.env" ]] || die "Refusing release containing .env"
mkdir -p "$shared/storage/app/public" "$shared/storage/framework/cache/data" "$shared/storage/framework/sessions" "$shared/storage/framework/views" "$shared/storage/logs"
mv "$release/apps/api/storage" "$release/apps/api/storage-scaffold"
ln -s "$shared/storage" "$release/apps/api/storage"
ln -s "$shared/.env" "$release/apps/api/.env"
chmod 750 "$shared/storage" "$shared/backups"
# Install only production PHP dependencies on the shared host.
(cd "$release/apps/api" && composer install --no-dev --prefer-dist --no-interaction --no-progress --classmap-authoritative)
# Backup must succeed BEFORE migrations or docroot changes. Pgpass is never printed or committed.
getenv(){ sed -n "s/^$1=//p" "$shared/.env" | tail -n 1 | tr -d '\r' | sed 's/^"//;s/"$//'; }
export PGPASSFILE="$shared/.pgpass"
dbhost="$(getenv DB_HOST)"; dbport="$(getenv DB_PORT)"; dbname="$(getenv DB_DATABASE)"; dbuser="$(getenv DB_USERNAME)"
[[ -n "$dbhost" && -n "$dbname" && -n "$dbuser" && "$dbport" =~ ^[0-9]+$ ]] || die "Invalid PostgreSQL configuration"
backup="$shared/backups/before-$sha-$(date -u +%Y%m%dT%H%M%SZ).dump"
pg_dump --no-password --format=custom --host="$dbhost" --port="$dbport" --username="$dbuser" --file="$backup" "$dbname" || die "Database backup failed. Migration refused"
[[ -s "$backup" ]] && pg_restore --list "$backup" >/dev/null || die "PostgreSQL backup unreadable; no changes made"
sha256sum "$backup" > "$backup.sha256"
chmod 600 "$backup" "$backup.sha256"
(cd "$release/apps/api" && "$phpbin" artisan migrate --force --no-interaction && "$phpbin" artisan config:cache && "$phpbin" artisan route:cache && "$phpbin" artisan view:cache)
# Keep all customer records and photos under ~/carfolk/shared, never in public_html or a release tarball.
[[ ! -e "$docroot/storage" || ( -L "$docroot/storage" && "$(readlink "$docroot/storage")" == "$shared/storage/app/public" ) ]] || die "Unexpected public storage mapping"
if [[ ! -L "$docroot/storage" ]]; then ln -s "$shared/storage/app/public" "$docroot/storage"; fi
# Site ownership, public root contents and template guard have already been verified.
# Asset uploads are additive so old hashed bundle URLs remain available during the switch.
rsync -a --exclude='.htaccess' --exclude='index.php' --exclude='storage' --exclude='.*' "$release/apps/api/public/" "$docroot/"
rsync -a --exclude='index.html' --exclude='robots.txt' --exclude='.*' "$release/apps/web/dist/" "$docroot/"
# Keep previous code/HTML for a code-only rollback. DB migrations are NOT rolled back.
previous="$(readlink "$root/current" || true)"
if [[ -f "$docroot/index.html" ]]; then cp -a "$docroot/index.html" "$release/previous-index.html"; fi
cp "$release/infra/cpanel/public.htaccess" "$docroot/.htaccess.next"
cp "$release/infra/cpanel/front-controller.php" "$docroot/.index.php.next"
cp "$release/apps/web/dist/index.html" "$docroot/.index.html.next"
chmod 644 "$docroot/.htaccess.next" "$docroot/.index.php.next" "$docroot/.index.html.next"
ln -s "$release" "$root/.current-next"
mv -Tf "$root/.current-next" "$root/current"
mv -f "$docroot/.htaccess.next" "$docroot/.htaccess"
mv -f "$docroot/.index.php.next" "$docroot/index.php"
mv -f "$docroot/.index.html.next" "$docroot/index.html"
# Robots SEO defaults remain noindex. No enablement of production lead capture.
origin="https://carfolkltd.com"
if ! (curl --fail --silent --show-error --location --max-time 20 "$origin/up" >/dev/null &&
      curl --fail --silent --show-error --location --max-time 20 "$origin/api/v1/site" >/dev/null &&
      curl --fail --silent --show-error --location --max-time 20 "$origin/manage/login" >/dev/null); then
  if [[ -n "$previous" && -d "$previous" ]]; then
    ln -s "$previous" "$root/.current-rollback"
    mv -Tf "$root/.current-rollback" "$root/current"
    if [[ -f "$release/previous-index.html" ]]; then cp "$release/previous-index.html" "$docroot/index.html"; fi
  fi
  die "Live smoke check failed. Code restored when a previous release existed; inspect host, .htaccess and Laravel logs."
fi
printf 'I_AM_CARFOLK_MANAGED\n' > "$docroot/.carfolk-managed"
chmod 600 "$docroot/.carfolk-managed"
rm -f "$package"
printf 'Deployed Carfolk commit %s to %s; database backup stored privately.\n' "$sha" "$origin"
printf 'Production leads remain disabled; legal, stock and owner sign-off are separate.\n'
