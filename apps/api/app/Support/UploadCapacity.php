<?php

namespace App\Support;

/**
 * Host PHP limits that matter for the Carfolk vehicle-photo upload form.
 * In cPanel the CLI and web PHP limits can differ, so read them on a web request.
 */
final class UploadCapacity
{
    public const FILE_LIMIT_BYTES = 8 * 1024 * 1024;
    public const FILES_PER_BATCH = 5;
    // Allow at least 1 MiB beyond the 40 MiB photo payload for multipart overhead.
    public const MIN_POST_BYTES = (self::FILE_LIMIT_BYTES * self::FILES_PER_BATCH) + (1024 * 1024);

    public static function bytes(string|false|null $raw): ?int
    {
        if (!is_string($raw) || !preg_match('/^\s*(\d+(?:\.\d+)?)\s*([KMG]?)B?\s*$/i', $raw, $matches)) {
            return null;
        }
        $scale = match (strtoupper($matches[2])) {
            'K' => 1024,
            'M' => 1024 ** 2,
            'G' => 1024 ** 3,
            default => 1,
        };
        return (int) floor((float) $matches[1] * $scale);
    }

    public static function status(): array
    {
        $upload = (string) ini_get('upload_max_filesize');
        $post = (string) ini_get('post_max_size');
        $uploadBytes = self::bytes($upload);
        $postBytes = self::bytes($post);
        // post_max_size=0 means no fixed POST limit in PHP. A host WAF may still impose one.
        $photoReady = $uploadBytes !== null && $uploadBytes >= self::FILE_LIMIT_BYTES;
        $batchReady = $postBytes !== null && ($postBytes === 0 || $postBytes >= self::MIN_POST_BYTES);

        return [
            'upload_max_filesize' => $upload,
            'post_max_size' => $post,
            'upload_ready' => $photoReady,
            'batch_ready' => $batchReady,
            'image_storage_ready' => is_dir(storage_path('app/public')) && is_writable(storage_path('app/public')),
            'error_log_ready' => is_dir(storage_path('logs')) && is_writable(storage_path('logs')),
        ];
    }
}
