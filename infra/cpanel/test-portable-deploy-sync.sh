#!/usr/bin/env bash
# Unit-check the actual portable sync function without sourcing the deployer.
# All mutations happen under a throwaway temp folder, never under /home2.
set -Eeuo pipefail
tmp="$(mktemp -d)"
trap 'rm -rf -- "$tmp"' EXIT
API="$tmp/live-api"
stage="$tmp/stage-api"
snapshot="$tmp/snapshot-api"
mkdir -p "$API"/{app,storage/app/public,bootstrap/cache,public/js} "$stage"/{app,storage/app/public,bootstrap/cache,public/js} "$snapshot"
# Reproduce HostPinnacle: live API must stay traversable (711), but tar
# archives originate in private staging/backup directories with mode 700.
chmod 711 "$API"
chmod 700 "$stage" "$snapshot"
printf 'LIVE-ONLY-SECRET\n' > "$API/.env"
printf 'user-photo-content\n' > "$API/storage/app/public/photo.jpg"
printf 'keep-me\n' > "$API/bootstrap/cache/config.php"
printf 'old-version\n' > "$API/app/Old.php"
printf 'old-bootstrap\n' > "$API/bootstrap/old.php"
printf 'old-js\n' > "$API/public/js/old.js"
printf 'existing-hidden\n' > "$API/app/.old-secretless-config"
printf 'existing spaced filename\n' > "$API/app/old file.php"
printf 'keep-existing-env\n' > "$API/.env.backup"
printf 'keep-cached-dotfile\n' > "$API/bootstrap/cache/.cache-stamp"

printf 'MUST-NOT-OVERWRITE\n' > "$stage/.env"
printf 'MUST-NOT-OVERWRITE-PHOTO\n' > "$stage/storage/app/public/photo.jpg"
printf 'MUST-NOT-OVERWRITE-CACHE\n' > "$stage/bootstrap/cache/config.php"
printf 'new-version\n' > "$stage/app/New.php"
printf 'new-bootstrap\n' > "$stage/bootstrap/new.php"
printf 'new-js\n' > "$stage/public/js/new.js"
printf 'new-hidden\n' > "$stage/app/.new-config"
printf 'new spaced filename\n' > "$stage/app/new file.php"
printf 'MUST-NOT-OVERWRITE-ENV-BACKUP\n' > "$stage/.env.backup"
printf 'MUST-NOT-OVERWRITE-CACHE-DOTFILE\n' > "$stage/bootstrap/cache/.cache-stamp"

# cPanel jailed Bash may lack /dev/fd: forbid process-substitution dependency.
if grep -Fq '< <(' infra/cpanel/deploy.sh; then
  echo 'Non-portable process substitution found in deployer' >&2
  exit 1
fi
# Extract only the helper function, not the potentially destructive script body.
sed -n '/^sync_api_code() {/,/^}/p' infra/cpanel/deploy.sh > "$tmp/sync.sh"
# shellcheck source=/dev/null
source "$tmp/sync.sh"
[[ "$(type -t sync_api_code)" == function ]]

(cd "$API" && tar --exclude='./.env*' --exclude='./storage' --exclude='./bootstrap/cache' -cf - .) \
  | (cd "$snapshot" && tar -xf -)
sync_api_code "$stage" "$API"
[[ "$(stat -c '%a' "$API")" == 711 ]] || {
  echo 'Live API lost LiteSpeed traversal after the release sync' >&2
  exit 1
}

[[ "$(cat "$API/.env")" == LIVE-ONLY-SECRET ]]
[[ "$(cat "$API/storage/app/public/photo.jpg")" == user-photo-content ]]
[[ "$(cat "$API/bootstrap/cache/config.php")" == keep-me ]]
[[ "$(cat "$API/app/New.php")" == new-version ]]
[[ "$(cat "$API/bootstrap/new.php")" == new-bootstrap ]]
[[ "$(cat "$API/public/js/new.js")" == new-js ]]
[[ ! -e "$API/app/Old.php" && ! -e "$API/bootstrap/old.php" && ! -e "$API/public/js/old.js" ]]
[[ ! -e "$API/app/.old-secretless-config" && ! -e "$API/app/old file.php" ]]
[[ "$(cat "$API/app/.new-config")" == new-hidden ]]
[[ "$(cat "$API/app/new file.php")" == 'new spaced filename' ]]
[[ "$(cat "$API/.env.backup")" == keep-existing-env ]]
[[ "$(cat "$API/bootstrap/cache/.cache-stamp")" == keep-cached-dotfile ]]
[[ ! -f "$snapshot/.env" && ! -e "$snapshot/storage" && ! -e "$snapshot/bootstrap/cache" ]]

# Rollback replaces managed code, without touching any runtime data.
sync_api_code "$snapshot" "$API"
[[ "$(stat -c '%a' "$API")" == 711 ]] || {
  echo 'Live API lost LiteSpeed traversal after rollback' >&2
  exit 1
}
[[ "$(cat "$API/app/Old.php")" == old-version ]]
[[ "$(cat "$API/bootstrap/old.php")" == old-bootstrap ]]
[[ "$(cat "$API/public/js/old.js")" == old-js ]]
[[ ! -e "$API/app/New.php" && ! -e "$API/app/.new-config" && ! -e "$API/app/new file.php" ]]
[[ "$(cat "$API/app/.old-secretless-config")" == existing-hidden ]]
[[ "$(cat "$API/app/old file.php")" == 'existing spaced filename' ]]
[[ "$(cat "$API/.env.backup")" == keep-existing-env ]]
[[ "$(cat "$API/bootstrap/cache/.cache-stamp")" == keep-cached-dotfile ]]
[[ "$(cat "$API/.env")" == LIVE-ONLY-SECRET ]]
[[ "$(cat "$API/storage/app/public/photo.jpg")" == user-photo-content ]]
[[ "$(cat "$API/bootstrap/cache/config.php")" == keep-me ]]

# Fail closed on an unexpected destination.
if sync_api_code "$stage" "$tmp/elsewhere" 2>/dev/null; then
  echo 'Portable sync accepted an unexpected target' >&2
  exit 1
fi

echo 'Portable cPanel sync and rollback checks passed'
