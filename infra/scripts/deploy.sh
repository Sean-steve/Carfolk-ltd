#!/usr/bin/env bash
set -euo pipefail
umask 027
# Manual deployment only. Do not run from GitHub Actions, root, or against an unknown host.
[[ $# -eq 1 && -d "$1/apps/api" && -d "$1/apps/web" ]] || {
 echo "Usage: infra/scripts/deploy.sh /absolute/path/to/checked-out-Carfolk-repository" >&2; exit 2;
}
[[ "$EUID" -ne 0 ]] || { echo 'Run as dedicated deploy user, never root' >&2; exit 1; }
: "${CARFOLK_RELEASE_APPROVED:?Set CARFOLK_RELEASE_APPROVED=I_HAVE_APPROVED_THIS_RELEASE after review}"
[[ "$CARFOLK_RELEASE_APPROVED" == "I_HAVE_APPROVED_THIS_RELEASE" ]] || exit 1
src="$(cd "$1" && pwd)"
root="${CARFOLK_ROOT:-/srv/carfolk}"
shared="$root/shared"
[[ -s "$shared/.env" ]] || { echo "Missing $shared/.env; do not deploy" >&2; exit 1; }
grep -qx 'APP_ENV=production' "$shared/.env" || { echo 'APP_ENV must be production' >&2; exit 1; }
grep -qx 'APP_DEBUG=false' "$shared/.env" || { echo 'APP_DEBUG must be false' >&2; exit 1; }
grep -qx 'SESSION_SECURE_COOKIE=true' "$shared/.env" || { echo 'Secure session cookies required' >&2; exit 1; }
grep -q '^APP_URL=https://' "$shared/.env" || { echo 'Canonical HTTPS origin missing' >&2; exit 1; }
if grep -Eq '^(APP_URL|CARFOLK_PUBLIC_URL)=.*example\\.com' "$shared/.env"; then
 echo 'Template example.com domain detected; real domain required' >&2; exit 1
fi

[[ -s "$src/apps/api/composer.lock" && -s "$src/apps/web/package-lock.json" ]] || {
 echo 'Missing dependency lockfile(s) — deploy aborted' >&2; exit 1;
}
stamp="$(date -u +%Y%m%dT%H%M%SZ)"
release="$root/releases/$stamp"
[[ ! -e "$release" ]] || { echo "Release directory exists" >&2; exit 1; }
mkdir -p "$release" "$shared/public-storage" "$root/releases"
[[ ! -e "$src/apps/api/.env" ]] || { echo 'Checkout contains an .env file; refusing to deploy copied secrets' >&2; exit 1; }
cp -a "$src/." "$release/"
ln -s "$shared/.env" "$release/apps/api/.env"
images="$release/apps/api/storage/app/public"
if [[ -d "$images" ]]; then
  find "$images" -mindepth 1 -maxdepth 1 ! -name .gitignore | grep -q . && { echo 'Unexpected release-local uploads' >&2; exit 1; }
  rm -f "$images/.gitignore"
  rmdir "$images"
fi
ln -s "$shared/public-storage" "$images"
(
 cd "$release/apps/api"
 composer install --no-dev --prefer-dist --no-interaction --no-progress --classmap-authoritative
 php artisan config:cache
 php artisan route:cache
 php artisan view:cache
 php artisan storage:link
)
(
 cd "$release/apps/web"
 npm ci --no-audit --no-fund
 npm run build
)
"$release/infra/scripts/backup-db.sh"
(cd "$release/apps/api" && php artisan migrate --force --no-interaction)
previous="$(readlink "$root/current" || true)"
ln -sfn "$release" "$root/.current-next"
mv -Tf "$root/.current-next" "$root/current"
echo "RELEASE SWITCHED: $release"
echo "Previous release: ${previous:-none}. Rollback code by restoring previous symlink, but DB migrations need explicit rollback planning."
echo "Next: reload PHP-FPM/NGINX by authorized operator; run smoke checks and monitor logs."
