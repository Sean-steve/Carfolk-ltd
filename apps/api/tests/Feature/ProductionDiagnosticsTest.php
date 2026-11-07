<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\UploadCapacity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ProductionDiagnosticsTest extends TestCase
{
    use RefreshDatabase;

    public function test_size_parser_understands_php_ini_values(): void
    {
        $this->assertSame(8 * 1024 * 1024, UploadCapacity::bytes('8M'));
        $this->assertSame(50 * 1024 * 1024, UploadCapacity::bytes('50MB'));
        $this->assertSame(41 * 1024 * 1024, UploadCapacity::MIN_POST_BYTES);
        $this->assertSame(1536 * 1024, UploadCapacity::OPTIMIZED_TARGET_BYTES);
        $this->assertSame(2 * 1024 * 1024, UploadCapacity::OPTIMIZED_SINGLE_POST_BYTES);
        $this->assertSame(0, UploadCapacity::bytes('0'));
        $this->assertNull(UploadCapacity::bytes('not a size'));
    }

    public function test_diagnostics_require_active_dealership_admin(): void
    {
        $this->get('/manage/system-health')->assertRedirect('/manage/login');

        $normal = User::factory()->create(['is_admin' => false, 'is_active' => true]);
        $this->actingAs($normal)->get('/manage/system-health')->assertForbidden();

        $inactive = User::factory()->create(['is_admin' => true, 'is_active' => false]);
        $this->actingAs($inactive)->get('/manage/system-health')->assertForbidden();

        $admin = User::factory()->create(['is_admin' => true, 'is_active' => true]);
        $response = $this->actingAs($admin)->get('/manage/system-health');
        $this->assertStringContainsString('no-store', (string) $response->headers->get('Cache-Control'));
        $response->assertOk()
            ->assertSee('Photo upload readiness')
            ->assertSee('Optimized photo uploads')
            ->assertDontSee('DB_PASSWORD');
    }
}
