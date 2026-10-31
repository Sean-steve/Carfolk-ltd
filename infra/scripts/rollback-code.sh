#!/usr/bin/env bash
set -euo pipefail
root="${CARFOLK_ROOT:-/srv/carfolk}"
[[ $# -eq 1 && -d "$1/apps/api" && -d "$1/apps/web" ]] || {
 echo 'Usage: rollback-code.sh /srv/carfolk/releases/PREVIOUS_RELEASE' >&2; exit 2;
}
[[ "$1" == "$root"/releases/* ]] || { echo "Refusing path outside releases" >&2; exit 1; }
[[ "${CARFOLK_ROLLBACK_APPROVED:-}" == 'I_HAVE_APPROVED_THIS_ROLLBACK' ]] || {
 echo 'Set CARFOLK_ROLLBACK_APPROVED=I_HAVE_APPROVED_THIS_ROLLBACK after schema compatibility review' >&2; exit 1;
}
ln -sfn "$1" "$root/.current-next"
mv -Tf "$root/.current-next" "$root/current"
echo "Code pointer switched. Reload PHP-FPM, smoke test and assess schema backward compatibility."
