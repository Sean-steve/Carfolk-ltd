#!/usr/bin/env bash
# Simulate LiteSpeed-facing public files produced under the private deployer's
# umask 077. Never touch a real webroot, database or vehicle-photo directory.
set -Eeuo pipefail
umask 077
tmp="$(mktemp -d)"
trap 'rm -rf -- "$tmp"' EXIT

PUBLIC="$tmp/public_html"
TEST_PUBLIC_ROOT="$PUBLIC"
mkdir -p "$PUBLIC/js" "$PUBLIC/assets/nested" "$PUBLIC/css" "$PUBLIC/brand"
printf 'app' > "$PUBLIC/index.html"
printf 'php' > "$PUBLIC/index.php"
printf 'routes' > "$PUBLIC/.htaccess"
printf 'PRIVATE DOTFILE' > "$PUBLIC/.private-settings"
printf 'js' > "$PUBLIC/js/admin-photo-gallery.js"
printf 'js' > "$PUBLIC/js/admin-errors.js"
printf 'css' > "$PUBLIC/css/admin.css"
printf 'logo' > "$PUBLIC/brand/logo.svg"
printf 'bundle' > "$PUBLIC/assets/nested/site.js"
mkdir -p "$tmp/private-photos"
printf 'photo' > "$tmp/private-photos/customer.jpg"
ln -s "$tmp/private-photos" "$PUBLIC/storage"
chmod 700 "$PUBLIC" "$PUBLIC/js" "$PUBLIC/assets" "$PUBLIC/assets/nested"
chmod 600 "$PUBLIC/index.html" "$PUBLIC/index.php" "$PUBLIC/.htaccess" \
  "$PUBLIC/.private-settings" "$PUBLIC/js/admin-photo-gallery.js"

# Extract the actual helper without executing the deployment.
sed -n '/^restore_public_permissions() {/,/^}/p' infra/cpanel/deploy.sh > "$tmp/helper.sh"
# shellcheck disable=SC1091
source "$tmp/helper.sh"
[[ "$(type -t restore_public_permissions)" == function ]]
restore_public_permissions

[[ "$(stat -c %a "$PUBLIC")" == 755 ]]
[[ "$(stat -c %a "$PUBLIC/js")" == 755 ]]
[[ "$(stat -c %a "$PUBLIC/assets/nested")" == 755 ]]
[[ "$(stat -c %a "$PUBLIC/index.html")" == 644 ]]
[[ "$(stat -c %a "$PUBLIC/index.php")" == 644 ]]
[[ "$(stat -c %a "$PUBLIC/.htaccess")" == 644 ]]
[[ "$(stat -c %a "$PUBLIC/js/admin-photo-gallery.js")" == 644 ]]
[[ "$(stat -c %a "$PUBLIC/assets/nested/site.js")" == 644 ]]
[[ "$(stat -c %a "$PUBLIC/.private-settings")" == 600 ]]
[[ "$(stat -c %a "$tmp/private-photos/customer.jpg")" == 600 ]]
[[ -L "$PUBLIC/storage" ]]

# Refuse to mutate directories not explicitly authorized as the public root.
PUBLIC="$tmp/arbitrary"
mkdir -p "$PUBLIC"
chmod 700 "$PUBLIC"
if restore_public_permissions 2>/dev/null; then
  echo "Refused safety test unexpectedly passed" >&2
  exit 1
fi
[[ "$(stat -c %a "$PUBLIC")" == 700 ]]
echo 'cPanel public file permission recovery checks passed'
