#!/usr/bin/env bash
set -euo pipefail
umask 077
# Designed for a root-only scheduled service: secrets are NEVER embedded in this repository.
CONF="${CARFOLK_BACKUP_ENV:-/etc/carfolk/backup.env}"
[[ -r "$CONF" ]] || { echo "Missing restricted DB backup credentials file" >&2; exit 1; }
# shellcheck source=/dev/null
set -a
source "$CONF"
set +a
: "${PGHOST:?}" "${PGPORT:?}" "${PGUSER:?}" "${PGDATABASE:?}" "${PGPASSWORD:?}"
BACKUP_ROOT="${CARFOLK_BACKUP_DIR:-/srv/carfolk/shared/backups}"
mkdir -p "$BACKUP_ROOT"
exec 9>"$BACKUP_ROOT/.backup.lock"
flock -n 9 || { echo "Backup already active" >&2; exit 1; }
stamp="$(date -u +%Y%m%dT%H%M%SZ)"
tmp="$BACKUP_ROOT/.carfolk-${stamp}.$$.dump"
final="$BACKUP_ROOT/carfolk-${stamp}.dump"
trap 'rm -f "$tmp" "$tmp.sha256"' EXIT
pg_dump --format=custom --no-owner --no-acl --file="$tmp"
pg_restore --list "$tmp" > /dev/null
sha256sum "$tmp" | sed "s|$tmp|$final|" > "$tmp.sha256"
mv "$tmp" "$final"
mv "$tmp.sha256" "$final.sha256"
trap - EXIT
find "$BACKUP_ROOT" -maxdepth 1 -type f -name 'carfolk-*.dump' -mtime +13 -print -delete
find "$BACKUP_ROOT" -maxdepth 1 -type f -name 'carfolk-*.dump.sha256' -mtime +13 -print -delete
echo "Local PostgreSQL dump verified and stored: $final"
echo "IMPORTANT: an encrypted OFF-SITE copy and recurring restore drills are a separate mandatory launch gate."
