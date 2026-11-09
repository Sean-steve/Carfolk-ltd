# Carfolk photo regression — O7.5 emergency recovery

## Known facts
- O7.5 generated optional WebP derivatives; `VehicleData` exposed `thumbnail_url`, `card_url`, and `detail_url` if a file exists locally.
- The previous buyer photo component showed a placeholder immediately on a WebP request error, even when the original JPEG/PNG was still accessible.
- HostPinnacle previously returned 404 when public root or private Laravel parent permissions prevented LiteSpeed traversal. Disk existence alone does **not** establish HTTP accessibility.
- Original uploads must never be deleted to resolve this incident.

## Mitigation in this hotfix
- Introduce `CARFOLK_IMAGE_VARIANTS_ENABLED`, **default OFF** (fail-closed). When disabled, all three optional derivative URL fields are null; consumers display the original `url`.
- Buyer UI attempts the original if a preferred WebP fails, then shows the placeholder only if the original also fails.
- Admin gallery and listing preview include an original image fallback on the `img` error event.
- Existing generated WebP derivatives remain on disk; backfill/build command remains available, but cannot affect URL selection until verified and re-enabled.
- No database migration or original image mutation is required.

## Verify live health before enabling optimization
On the HostPinnacle cPanel Terminal:

```bash
curl -sS -o /dev/null -w 'Homepage HTTP %{http_code}\n' https://carfolkltd.com/
curl -sS -o /dev/null -w 'API HTTP %{http_code}\n' https://carfolkltd.com/api/v1/vehicles
curl -sS -o /dev/null -w 'Original HTTP %{http_code}, %{content_type}\n' 'https://carfolkltd.com/storage/vehicles/01a1b572-cc80-7205-aaab-c03d4aa6592a/w4A8wIGT0tU6GuQVEa88j5rGWyh2LWS1vljx5jlm.jpg'
```

To compare the first live image with its optimized variants, print the four URL fields from the public API using PHP without writing any credentials:

```bash
curl -fsS https://carfolkltd.com/api/v1/vehicles | /opt/cpanel/ea-php84/root/usr/bin/php -r '
$d=json_decode(stream_get_contents(STDIN),true);
foreach(($d["data"]??[]) as $car){
  if(empty($car["images"]))continue;
  $i=$car["images"][0];
  foreach(["url","thumbnail_url","card_url","detail_url"] as $key){
    echo "$key: ".($i[$key]??"null")."\n";
  }
  break;
}'
```

Check a printed URL with `curl -L -sS -o /dev/null -w '%{http_code} %{content_type}\n' 'URL'`.

**Do not change the server permissions blindly.** If original URLs fail, use `namei -l` to inspect parent directories, the `public_html/storage` symlink, and LiteSpeed 404/403 responses. If originals work and variants fail, keep optimization OFF while reviewing symlink/rewrite/variant directory permissions.

## Re-enable only after smoke testing

With the hotfix deployed, in the private `carfolk-api/.env` file:
```dotenv
CARFOLK_IMAGE_VARIANTS_ENABLED=true
```
Then run `/opt/cpanel/ea-php84/root/usr/bin/php artisan config:cache` inside the API directory. Confirm the API returns non-null optimized URL(s) and test a WebP file returns 200 with `Content-Type: image/webp` on the live host; check listing cards and thumbnails in browser. To roll back instantly without deleting images, set the flag `false` and refresh Laravel config cache.

## Release safety

Wait for the `cpanel-release` source commit to exactly match approved main. This hotfix only fixes application-level fallback. Existing CI/cPanel release checks do not prove that HostPinnacle serves a specific photo through the domain. Keep host-level image checks as a production acceptance gate.
