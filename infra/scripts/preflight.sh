#!/usr/bin/env bash
set -euo pipefail
cd "$(dirname "$0")/../.."
fail=0
check() { if eval "$2"; then echo "PASS: $1"; else echo "BLOCKED: $1"; fail=1; fi; }
check 'Composer dependency lock exists' 'test -s apps/api/composer.lock'
check 'Node dependency lock exists' 'test -s apps/web/package-lock.json'
check 'Production environment template exists' 'test -s apps/api/.env.production.example'
check 'PostgreSQL migration source exists' 'test -s apps/api/database/migrations/2026_10_31_000002_create_dealership_tables.php'
check 'Nginx reverse proxy template exists' 'test -s infra/nginx/carfolk.conf.template'
check 'Rollback/backup instructions exist' 'test -s docs/PHASE_6_RELEASE.md'
check 'Server-side SEO entry exists' 'test -s apps/api/app/Http/Controllers/SeoController.php'
check 'No demo stock fixtures in active frontend' 'test ! -e apps/web/src/data/showroom.ts'
if [[ $fail -ne 0 ]]; then echo 'FAIL: production release preflight (do not deploy)'; exit 1; fi
echo "CODE PREFLIGHT PASSED. Live domain, backup restore, privacy approval and production traffic checks still require human sign-off."
