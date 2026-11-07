<?php

namespace Tests\Feature;

use App\Support\PhotoImageGuard;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

final class PhotoImageGuardTest extends TestCase
{
    public function test_dimension_limit_is_exact_and_does_not_decode_images(): void
    {
        $this->assertTrue(PhotoImageGuard::dimensionsAllowed(2560, 1920));
        $this->assertTrue(PhotoImageGuard::dimensionsAllowed(8000, 8000));
        $this->assertFalse(PhotoImageGuard::dimensionsAllowed(8001, 8000));
        $this->assertFalse(PhotoImageGuard::dimensionsAllowed(0, 1920));
    }

    public function test_normal_upload_is_accepted(): void
    {
        $photo = UploadedFile::fake()->image('car.jpg', 1024, 768);
        PhotoImageGuard::validate($photo, 'photos.0');
        $this->assertTrue(true);
    }

    public function test_unreadable_file_is_rejected(): void
    {
        $photo = UploadedFile::fake()->createWithContent('broken.png', 'not an image');
        $this->expectException(ValidationException::class);
        PhotoImageGuard::validate($photo, 'photos.0');
    }

    public function test_extreme_dimensions_are_rejected_from_headers_without_allocating_image_pixels(): void
    {
        $photo = UploadedFile::fake()->image('image.png', 2, 2);
        $path = $photo->getRealPath();
        $bytes = file_get_contents($path);
        $this->assertIsString($bytes);
        // PNG's IHDR width/height live at bytes 16..23; no 81-million-pixel
        // allocation is needed to exercise the guard.
        file_put_contents($path, substr_replace($bytes, pack('NN', 9000, 9000), 16, 8));
        $this->expectException(ValidationException::class);
        PhotoImageGuard::validate($photo, 'photos.0');
    }
}
