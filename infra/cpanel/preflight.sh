#!/usr/bin/env bash
set -euo pipefail
umask 077
die(){ printf 'FAIL: %s\n' "$*" >&2; exit 1; }
[[ "$(id -u)" != "0" ]] || die "Never run deployment as root"
[[ -n "${HOME:-}" && -d "$HOME/public_html" ]] || die "The expected cPanel document root ~/public_html is missing"
root="$HOME/carfolk"
shared="$root/shared"
envfile="$shared/.env"
[[ -f "$envfile" && ! -L "$envfile" ]] || die "Create the private server-only $envfile first"
[[ "$(stat -c %a "$envfile")" == "600" ]] || die "Set chmod 600 on the shared .env"
[[ -f "$shared/.deploy-approved" ]] || die "Create the private .deploy-approved file ONLY after owner and backup review"
grep -qx 'I_APPROVE_CARFOLK_DEPLOY' "$shared/.deploy-approved" || die "Invalid approval marker"
for x in 'APP_ENV=production' 'APP_DEBUG=false' 'SESSION_SECURE_COOKIE=true' 'DB_CONNECTION=pgsql' 'CARFOLK_LOCAL_ENQUIRIES_ENABLED=false' 'CARFOLK_LEADS_ENABLED=false' 'CARFOLK_SEO_ENABLED=false'; do
 grep -qx "$x" "$envfile" || die "Missing required safe setting: $x"
done
grep -q '^APP_KEY=base64:' "$envfile" || die "Set unique server APP_KEY"
grep -qx 'APP_URL=https://carfolkltd.com' "$envfile" || die "Use approved canonical HTTPS APP_URL"
grep -qx 'CARFOLK_PUBLIC_URL=https://carfolkltd.com' "$envfile" || die "Use matching public URL"
for k in DB_HOST DB_PORT DB_DATABASE DB_USERNAME DB_PASSWORD; do
 grep -Eq "^$k=.+$" "$envfile" || die "Missing $k in private .env"
done
phpbin="${CARFOLK_PHP_BIN:-}"
if [[ -z "$phpbin" && -f "$shared/php-bin" ]]; then phpbin="$(cat "$shared/php-bin")"; fi
if [[ -z "$phpbin" ]]; then
 if [[ -x /opt/cpanel/ea-php84/root/usr/bin/php ]]; then phpbin=/opt/cpanel/ea-php84/root/usr/bin/php
 elif [[ -x /opt/cpanel/ea-php83/root/usr/bin/php ]]; then phpbin=/opt/cpanel/ea-php83/root/usr/bin/php
 else phpbin="$(command -v php || true)"; fi
fi
[[ -n "$phpbin" && -x "$phpbin" ]] || die "PHP CLI not found"
"$phpbin" -r 'exit(version_compare(PHP_VERSION,"8.3","<")?1:0);' || die "PHP 8.3+ required for Laravel 13"
"$phpbin" -r 'foreach(["pdo_pgsql","mbstring","fileinfo","openssl"] as $e){if(!extension_loaded($e))exit(1);}' || die "Required PHP extensions missing: pdo_pgsql, mbstring, fileinfo or openssl"
for x in composer pg_dump pg_restore rsync tar curl; do command -v "$x" >/dev/null || die "Missing CLI utility: $x"; done
[[ -f "$shared/.pgpass" && "$(stat -c %a "$shared/.pgpass")" == "600" ]] || die "Set up a working 0600 PostgreSQL .pgpass for pg_dump backups"
[[ ! -e "$HOME/public_html/storage" || -L "$HOME/public_html/storage" ]] || die "Existing public_html/storage directory: resolve manually before deployment"
# First deployment may only replace an empty/default document root after explicit cPanel backup.
if [[ ! -e "$HOME/public_html/.carfolk-managed" ]]; then
 for path in index.html index.php assets css brand storage; do
  [[ ! -e "$HOME/public_html/$path" && ! -L "$HOME/public_html/$path" ]] || die "Existing $path in document root. Back it up and obtain owner approval; no overwrite."
 done
fi
printf 'PRE-FLIGHT OK: PHP %s, required extension and backup utilities available.\n' "$("$phpbin" -r 'echo PHP_VERSION;')"
printf '%s\n' "$phpbin"
