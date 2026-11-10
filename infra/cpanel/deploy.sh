#!/usr/bin/env bash
# Carfolk cPanel pull-deployer. Install a private copy in $HOME before using cron.
# No DB migrations, credential changes, or live upload deletion are automatic.
set -Eeuo pipefail
umask 077
HOME_DIR='/home2/carfolkl'
REPO="$HOME_DIR/carfolk-repo"
API="$HOME_DIR/carfolk-api"
PUBLIC="$HOME_DIR/public_html"
STATE="$HOME_DIR/.carfolk-deploy"
PHP_DIR='/opt/cpanel/ea-php84/root/usr/bin'
export PATH="$PHP_DIR:$PATH"
ORIGIN='https://carfolkltd.com'
mkdir -p "$STATE/backups"
exec 9>"$STATE/deploy.lock"
flock -n 9 || { echo 'Carfolk deployment already running'; exit 0; }
[[ -f "$STATE/enabled" ]] || { echo 'Carfolk auto-deploy disabled: create ~/.carfolk-deploy/enabled only after backup approval'; exit 0; }
for cmd in git tar cp find composer php curl; do command -v "$cmd" >/dev/null || { echo "Missing: $cmd" >&2; exit 1; }; done
[[ -f "$API/.env" && -f "$PUBLIC/index.php" && -f "$PUBLIC/.htaccess" && -f "$PUBLIC/index.html" ]] || { echo 'Existing application not completely provisioned' >&2; exit 1; }
[[ "$(php -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;')" == '8.4' ]] || { echo 'Expected PHP 8.4 CLI' >&2; exit 1; }
php -r 'exit(extension_loaded("pdo_pgsql") ? 0 : 1);' || { echo 'Missing PostgreSQL PHP driver' >&2; exit 1; }

git -C "$REPO" fetch --quiet origin \
  +refs/heads/main:refs/remotes/origin/main \
  +refs/heads/cpanel-release:refs/remotes/origin/cpanel-release
REF='refs/remotes/origin/cpanel-release'
RELEASE_ID="$(git -C "$REPO" rev-parse "$REF")"
[[ "$(cat "$STATE/deployed-release" 2>/dev/null || true)" != "$RELEASE_ID" ]] || { echo 'Already deployed'; exit 0; }
STAGE="$(mktemp -d "$STATE/stage.XXXXXXXX")"
BACKUP=''
MUTATED=0
cleanup() { rm -rf -- "$STAGE"; }

# Shared cPanel hosting may not provide rsync. Copy only managed API code,
# retaining the .env*, storage (including customer photos), and the runtime
# bootstrap/cache directory. Restore the exact managed-code snapshot on failure.
# Never use this against an arbitrary directory: destination must be $API.
sync_api_code() {
  local source="$1" destination="$2" entry filename child
  [[ "$destination" == "$API" && -d "$source" && -d "$destination" && ! -L "$destination" && -f "$destination/.env" ]] || {
    echo 'API sync refused: unexpected target or missing runtime .env' >&2
    return 1
  }
  # cPanel jailed shells can lack /dev/fd, even when Bash process substitution
  # works on CI. Enumerate names with dotglob/nullglob in an isolated subshell.
  # This includes hidden managed files and safely handles spaces in filenames.
  (
    shopt -s dotglob nullglob
    for entry in "$destination"/*; do
      filename="${entry##*/}"
      case "$filename" in
        .env*|storage) continue ;;
        bootstrap)
          if [[ -d "$entry" && ! -L "$entry" ]]; then
            for child in "$entry"/*; do
              [[ "${child##*/}" == cache ]] || rm -rf -- "$child"
            done
          else
            echo 'API sync refused: unexpected bootstrap directory type' >&2
            exit 1
          fi
          continue ;;
      esac
      rm -rf -- "$entry"
    done
  )
  # Keep the existing API directory metadata while extracting the staged
  # archive. cPanel stages are created with umask 077, so the archive's "."
  # entry has mode 700. Plain tar -xf applies that 700 mode to the live API
  # root and LiteSpeed can no longer follow public_html/storage -> API/storage,
  # causing all customer photos to 404. This applies to rollback as well.
  (cd "$source" && tar --exclude='./.env*' --exclude='./storage' --exclude='./bootstrap/cache' -cf - .) \
    | (cd "$destination" && tar --no-overwrite-dir -xf -)
  # Allow traversal, not listing, of this private code parent. This does not
  # change .env, photos or any nested private file or directory permissions.
  chmod 711 "$destination"
}

# The private deployer uses umask 077 for credentials and backups. Files copied
# into public_html may inherit 600/700 as a result; LiteSpeed cannot then serve
# either the homepage or static assets (often reporting HTTP 404). Normalize
# ONLY known public paths, including during rollback. Do not chmod Laravel API,
# storage, or arbitrary hidden files.
restore_public_permissions() {
  [[ "$PUBLIC" == '/home2/carfolkl/public_html' || ( -n "${TEST_PUBLIC_ROOT:-}" && "$PUBLIC" == "$TEST_PUBLIC_ROOT" ) ]] || {
    echo 'Refusing permissions update outside the expected public root' >&2
    return 1
  }
  [[ -d "$PUBLIC" && ! -L "$PUBLIC" ]] || return 1
  chmod 755 "$PUBLIC"
  for part in js css brand assets; do
    if [[ -d "$PUBLIC/$part" && ! -L "$PUBLIC/$part" ]]; then
      find "$PUBLIC/$part" -type d -exec chmod 755 {} +
      find "$PUBLIC/$part" -type f -exec chmod 644 {} +
    fi
  done
  # The SPA entry point is replaced via cp+mv with umask 077.
  # This targets public root files, excluding hidden operational files.
  find "$PUBLIC" -mindepth 1 -maxdepth 1 -type f ! -name '.*' -exec chmod 644 {} +
  [[ ! -f "$PUBLIC/.htaccess" ]] || chmod 644 "$PUBLIC/.htaccess"
}

rollback() {
  echo 'Deploy failed; reverting application files (database/uploads untouched)' >&2
  sync_api_code "$BACKUP/api" "$API" || true
  [[ -f "$BACKUP/index.html" ]] && cp -p "$BACKUP/index.html" "$PUBLIC/index.html" || true
  for part in css brand js; do
    if [[ -d "$BACKUP/$part" ]]; then
      mkdir -p "$PUBLIC/$part"
      cp -a "$BACKUP/$part/." "$PUBLIC/$part/" || true
    fi
  done
  restore_public_permissions || true
  (cd "$API" && php artisan config:clear && php artisan config:cache) || true
}
failed() {
  status=$?
  trap - ERR
  if [[ "$MUTATED" == 1 && -n "$BACKUP" ]]; then rollback; fi
  exit "$status"
}
trap cleanup EXIT
trap failed ERR

git -C "$REPO" archive "$REF" | tar -x -C "$STAGE"
SOURCE="$(cat "$STAGE/SOURCE_COMMIT")"
[[ "$SOURCE" =~ ^[0-9a-f]{40}$ ]] || { echo 'Invalid release provenance' >&2; exit 1; }
git -C "$REPO" merge-base --is-ancestor "$SOURCE" refs/remotes/origin/main || { echo 'Release not from main' >&2; exit 1; }
[[ -s "$STAGE/api/composer.lock" && -s "$STAGE/web/index.html" && -d "$STAGE/web/assets" ]] || { echo 'Incomplete release' >&2; exit 1; }
grep -q 'content="noindex,nofollow"' "$STAGE/web/index.html" || { echo 'Review SEO approval before deploying indexable content' >&2; exit 1; }

# Build PHP vendor in a private staging area, never on the live website.
cp -p "$API/.env" "$STAGE/api/.env"
mkdir -p "$STAGE/api/storage/framework/"{cache/data,sessions,views} "$STAGE/api/storage/logs" "$STAGE/api/bootstrap/cache"
(cd "$STAGE/api" && composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction --no-progress)
(cd "$STAGE/api" && php artisan migrate:status) > "$STAGE/migrations.txt"
if grep -q 'Pending' "$STAGE/migrations.txt"; then
  echo 'Schema migration pending: manual DB backup, review and migration required before this release' >&2
  exit 1
fi

STAMP="$(date -u +'%Y%m%dT%H%M%SZ')"
BACKUP="$STATE/backups/$STAMP-$RELEASE_ID"
mkdir -p "$BACKUP/api"
(cd "$API" && tar --exclude='./.env*' --exclude='./storage' --exclude='./bootstrap/cache' -cf - .) \
  | (cd "$BACKUP/api" && tar -xf -)
cp -p "$PUBLIC/index.html" "$BACKUP/index.html"
for part in css brand js; do
  [[ -d "$PUBLIC/$part" ]] && cp -a "$PUBLIC/$part" "$BACKUP/$part"
done

MUTATED=1
# Keep live .env, Laravel storage (including vehicle photos), and framework caches.
sync_api_code "$STAGE/api" "$API"
(cd "$API" && php artisan package:discover --ansi && php artisan config:clear && php artisan config:cache && php artisan view:cache)
for part in css brand js; do
  if [[ -d "$API/public/$part" ]]; then
    mkdir -p "$PUBLIC/$part"
    cp -a "$API/public/$part/." "$PUBLIC/$part/"
  fi
done
# Copy hashed assets first; replace the HTML entry point last.
mkdir -p "$PUBLIC/assets"
cp -a "$STAGE/web/assets/." "$PUBLIC/assets/"
(cd "$STAGE/web" && tar --exclude='./index.html' --exclude='./assets' \
  --exclude='./robots.txt' --exclude='./index.php' --exclude='./.htaccess' \
  --exclude='./.user.ini' --exclude='./php.ini' --exclude='./storage' \
  --exclude='./cgi-bin' --exclude='./.well-known' -cf - .) \
  | (cd "$PUBLIC" && tar -xf -)
cp "$STAGE/web/index.html" "$PUBLIC/.carfolk-index.new"
mv -f "$PUBLIC/.carfolk-index.new" "$PUBLIC/index.html"
restore_public_permissions
# Confirm every O7 admin bundle is served as actual JavaScript, not SPA HTML.
# O7.2 extracted upload handling from admin-errors.js; checking the old marker
# there would wrongly roll back an otherwise valid deployment.
for asset in admin-errors.js admin-image-optimizer.js admin-bulk-photos.js admin-photo-gallery.js; do
  curl --fail --silent --show-error --max-time 20 "$ORIGIN/js/$asset" > "$STAGE/$asset"
  [[ -s "$STAGE/$asset" ]] || { echo "Admin JavaScript missing: $asset" >&2; exit 1; }
done
grep -q 'data-photo-upload' "$STAGE/admin-bulk-photos.js"
grep -q 'CarfolkImageOptimizer' "$STAGE/admin-image-optimizer.js"
grep -q 'data-gallery-sort' "$STAGE/admin-photo-gallery.js"
for endpoint in /up /api/v1/site /api/v1/vehicles /manage/login; do
  curl --fail --silent --show-error --max-time 20 --output /dev/null "$ORIGIN$endpoint"
done
printf '%s\n' "$RELEASE_ID" > "$STATE/deployed-release"
printf '%s\n' "$SOURCE" > "$STATE/deployed-source"
echo "DEPLOYED: $SOURCE (release $RELEASE_ID)"
