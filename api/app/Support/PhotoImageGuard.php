<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;

/**
 * Inspect image headers only. Never decode enormous images in shared-hosting PHP.
 * Browser-side O7.3 handles the actual compression before transport.
 */
final class PhotoImageGuard
{
    public const MAX_PIXELS = 64_000_000;

    public static function dimensionsAllowed(int $width, int $height): bool
    {
        return $width > 0 && $height > 0 && $width * $height <= self::MAX_PIXELS;
    }

    public static function validate(UploadedFile $photo, string $field): void
    {
        $path = $photo->getRealPath();
        $info = $path !== false ? @getimagesize($path) : false;
        if (!is_array($info) || !self::dimensionsAllowed((int) ($info[0] ?? 0), (int) ($info[1] ?? 0))) {
            throw ValidationException::withMessages([
                $field => 'This image is unreadable or exceeds the 64-megapixel safety limit. Please resize it before uploading.',
            ]);
        }
    }
}
