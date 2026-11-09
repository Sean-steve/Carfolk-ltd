<?php

namespace App\Support;

use App\Models\VehicleImage;
use Illuminate\Support\Facades\Storage;

/**
 * Generate small static WebP variants. The original asset and database path are
 * never modified. Unsupported or very large photos keep using their original.
 */
final class VehicleImageVariants
{
    private const SIZES = [
        'thumb' => [320, 72],
        'card' => [640, 74],
        'detail' => [1600, 78],
    ];

    // Limit GD memory use on shared hosting: enormous legacy photos are skipped.
    private const MAX_DECODE_PIXELS = 20_000_000;

    public static function pathFor(string $originalPath, string $variant): ?string
    {
        if (!isset(self::SIZES[$variant])) {
            return null;
        }

        $directory = dirname($originalPath);
        $stem = pathinfo($originalPath, PATHINFO_FILENAME);
        if ($directory === '.' || $stem === '' || !str_starts_with($directory, 'vehicles/')) {
            return null;
        }

        // Derivatives inherit the original random file stem, avoiding URL
        // cache collisions when an admin replaces an image with a new path.
        return $directory.'/variants/'.$stem.'-'.$variant.'.webp';
    }

    public static function urlFor(VehicleImage $image, string $variant): ?string
    {
        // A generated file existing on disk doesn't guarantee LiteSpeed will
        // serve it through /storage. Until verified, never advertise variants:
        // clients will use the original, known-working image URL instead.
        if (!config('carfolk.image_variants_enabled', false)) return null;
        if ($image->disk !== 'public') return null;
        $path = self::pathFor($image->path, $variant);
        if ($path === null || !Storage::disk('public')->exists($path)) return null;
        return Storage::disk('public')->url($path);
    }

    /** @return array{generated:int, skipped:int} */
    public static function generate(VehicleImage $image, bool $force = false, bool $dryRun = false): array
    {
        if ($image->disk !== 'public' || !function_exists('imagewebp')) {
            return ['generated' => 0, 'skipped' => count(self::SIZES)];
        }
        $disk = Storage::disk('public');
        if (!$disk->exists($image->path)) {
            return ['generated' => 0, 'skipped' => count(self::SIZES)];
        }
        $source = $disk->path($image->path);
        $info = @getimagesize($source);
        if (!is_array($info)) {
            return ['generated' => 0, 'skipped' => count(self::SIZES)];
        }
        $width = (int) ($info[0] ?? 0);
        $height = (int) ($info[1] ?? 0);
        $type = (int) ($info[2] ?? 0);
        if ($width < 1 || $height < 1 || $width * $height > self::MAX_DECODE_PIXELS
            || !in_array($type, [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP], true)) {
            return ['generated' => 0, 'skipped' => count(self::SIZES)];
        }

        $missing = [];
        foreach (self::SIZES as $variant => $size) {
            $targetPath = self::pathFor($image->path, $variant);
            if ($targetPath === null || (!$force && $disk->exists($targetPath))) continue;
            $missing[$variant] = [$targetPath, $size[0], $size[1]];
        }
        if (!$missing) return ['generated' => 0, 'skipped' => count(self::SIZES)];
        if ($dryRun) return ['generated' => count($missing), 'skipped' => count(self::SIZES) - count($missing)];

        $sourceImage = match ($type) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($source),
            IMAGETYPE_PNG => @imagecreatefrompng($source),
            IMAGETYPE_WEBP => @imagecreatefromwebp($source),
        };
        if (!$sourceImage instanceof \GdImage) {
            return ['generated' => 0, 'skipped' => count(self::SIZES)];
        }

        $generated = 0;
        try {
            // Existing phone photos may have EXIF orientation. Apply supported
            // rotations before encoding; do not rotate the original image.
            if ($type === IMAGETYPE_JPEG && function_exists('exif_read_data')) {
                $exif = @exif_read_data($source);
                $orientation = is_array($exif) ? (int) ($exif['Orientation'] ?? 1) : 1;
                if (in_array($orientation, [2, 3, 4, 6, 8], true)) {
                    if ($orientation === 2) imageflip($sourceImage, IMG_FLIP_HORIZONTAL);
                    if ($orientation === 4) imageflip($sourceImage, IMG_FLIP_VERTICAL);
                    if (in_array($orientation, [3, 6, 8], true)) {
                        $angle = match ($orientation) {3 => 180, 6 => -90, 8 => 90};
                        $rotated = imagerotate($sourceImage, $angle, 0);
                        if ($rotated instanceof \GdImage) {
                            imagedestroy($sourceImage);
                            $sourceImage = $rotated;
                        }
                    }
                } elseif (in_array($orientation, [5, 7], true)) {
                    // Transposed JPEGs need additional reflection rules; leave
                    // originals unchanged until an orientation-safe encoder exists.
                    return ['generated' => 0, 'skipped' => count(self::SIZES)];
                }
            }
            $width = imagesx($sourceImage);
            $height = imagesy($sourceImage);
            foreach ($missing as [$targetPath, $maxWidth, $quality]) {
                $ratio = min(1, $maxWidth / max($width, $height));
                $outWidth = max(1, (int) round($width * $ratio));
                $outHeight = max(1, (int) round($height * $ratio));
                $canvas = imagecreatetruecolor($outWidth, $outHeight);
                if (!$canvas instanceof \GdImage) continue;
                try {
                    imagealphablending($canvas, false);
                    imagesavealpha($canvas, true);
                    $transparent = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
                    imagefill($canvas, 0, 0, $transparent);
                    if (!imagecopyresampled($canvas, $sourceImage, 0, 0, 0, 0, $outWidth, $outHeight, $width, $height)) {
                        continue;
                    }
                    $disk->makeDirectory(dirname($targetPath));
                    // CLI backfills may inherit cPanel's private umask 077.
                    // Explicitly make only this new PUBLIC derivative folder
                    // traversable by LiteSpeed; never chmod the private API root.
                    @chmod($disk->path(dirname($targetPath)), 0755);
                    $destination = $disk->path($targetPath);
                    $temporary = $destination.'.'.bin2hex(random_bytes(4)).'.tmp';
                    try {
                        if (@imagewebp($canvas, $temporary, $quality) && is_file($temporary) && filesize($temporary) > 0) {
                            @chmod($temporary, 0644);
                            if (@rename($temporary, $destination)) {
                                $disk->setVisibility($targetPath, 'public');
                                $generated++;
                            }
                        }
                    } finally {
                        if (is_file($temporary)) @unlink($temporary);
                    }
                } finally {
                    imagedestroy($canvas);
                }
            }
        } finally {
            imagedestroy($sourceImage);
        }
        return ['generated' => $generated, 'skipped' => count(self::SIZES) - $generated];
    }

    public static function deleteForPath(string $disk, string $originalPath): void
    {
        if ($disk !== 'public') return;
        foreach (array_keys(self::SIZES) as $variant) {
            $path = self::pathFor($originalPath, $variant);
            if ($path !== null) Storage::disk('public')->delete($path);
        }
    }
}
