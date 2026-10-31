#!/usr/bin/env bash
set -euo pipefail
umask 077
[[ $# -eq 1 && -f "$1" ]] || { echo 'Usage: restore-drill.sh /full/path/to/carfolk-YYYY.dump' >&2; exit 2; }
CONF="${CARFOLK_BACKUP_ENV:-/etc/carfolk/backup.env}"
[[ -r "$CONF" ]] || exit 1
# shellcheck source=/dev/null
set -a
source "$CONF"
set +a
: "${PGHOST:?}" "${PGPORT:?}" "${PGUSER:?}" "${PGPASSWORD:?}"
# Never allow a drill target of the live DB. Requires separate CREATE DATABASE privileges.
LIVE_DB="${PGDATABASE:?}"
DRILL_DB="${CARFOLK_DRILL_DATABASE:-carfolk_restore_drill}"
[[ "$DRILL_DB" != "$LIVE_DB" && "$DRILL_DB" == "carfolk_restore_drill" ]] || { echo 'Unsafe drill DB name' >&2; exit 1; }
backup="$1"
[[ -f "$backup.sha256" ]] && (cd "$(dirname "$backup")" && sha256sum -c "$(basename "$backup").sha256")
if psql --dbname=postgres --tuples-only --no-align --command="SELECT 1 FROM pg_database WHERE datname='$DRILL_DB'" | grep -q 1; then
  echo "Drill DB already exists; refusing to overwrite." >&2; exit 1
fi
createdb "$DRILL_DB"
trap 'echo "Inspect drill DB and manually drop after verification: $DRILL_DB" >&2' EXIT
pg_restore --no-owner --no-acl --exit-on-error --dbname="$DRILL_DB" "$backup"
psql --dbname="$DRILL_DB" -v ON_ERROR_STOP=1 -c "SELECT count(*) AS restored_vehicles FROM vehicles;"
echo "RESTORE DRILL COMPLETED. Validate sample records against backup manifest; drop drill DB manually."
