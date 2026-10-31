#!/usr/bin/env bash
set -euo pipefail
[[ $# -eq 1 ]] || { echo 'Usage: production-smoke.sh https://approved-production-domain' >&2; exit 2; }
ORIGIN="${1%/}"
[[ "$ORIGIN" == https://* && "$ORIGIN" != *"example.com"* ]] || { echo 'HTTPS production domain required (not example.com)' >&2; exit 1; }
probe() {
  local endpoint="$1" expected="$2"
  status="$(curl --fail-with-body --silent --show-error --max-time 15 --output /tmp/carfolk-smoke-body --write-out '%{http_code}' "$ORIGIN$endpoint")"
  [[ "$status" == "$expected" ]] || { echo "Unexpected HTTP $status for $endpoint" >&2; exit 1; }
  echo "PASS: $endpoint ($status)"
}
probe /up 200
probe /api/v1/site 200
probe /api/v1/vehicles 200
probe /robots.txt 200
probe /sitemap.xml 200
probe /manage/login 200
if ! curl -fsSI --max-time 15 "$ORIGIN/manage/login" | grep -qi 'cache-control:.*no-store'; then
  echo 'FAIL: admin page must not be cached' >&2; exit 1
fi
echo 'HTTP smoke passes; browser admin/lead flows and signed go-live review still required.'
