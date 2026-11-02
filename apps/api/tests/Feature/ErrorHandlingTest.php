<?php

namespace Tests\Feature;

use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ErrorHandlingTest extends TestCase
{
    public function test_html_upload_error_is_branded_and_has_a_support_reference(): void
    {
        Route::post('/o6-test-photo-upload', fn () => throw new PostTooLargeException());

        $response = $this->post('/o6-test-photo-upload');

        $response->assertStatus(413)
            ->assertSee('Upload too large')
            ->assertSee('Support reference:')
            ->assertHeader('Cache-Control', 'no-store');

        $this->assertNotEmpty($response->headers->get('X-Request-ID'));
    }

    public function test_api_upload_error_uses_safe_json_and_request_reference(): void
    {
        Route::post('/api/v1/o6-test-photo-upload', fn () => throw new PostTooLargeException());

        $response = $this->postJson('/api/v1/o6-test-photo-upload');

        $response->assertStatus(413)
            ->assertJsonPath('error.code', 'PAYLOAD_TOO_LARGE')
            ->assertJsonStructure(['message', 'error' => ['code', 'request_id']]);
        $this->assertSame(
            $response->headers->get('X-Request-ID'),
            $response->json('error.request_id'),
        );
    }

    public function test_missing_api_route_has_a_stable_error_code(): void
    {
        $this->getJson('/api/v1/o6-missing-page')
            ->assertNotFound()
            ->assertJsonPath('error.code', 'NOT_FOUND');
    }

    public function test_unexpected_production_error_never_exposes_internal_details(): void
    {
        config()->set('app.debug', false);
        Route::get('/api/v1/o6-test-crash', fn () => throw new \RuntimeException('secret-internal-stack-detail'));

        $this->getJson('/api/v1/o6-test-crash')
            ->assertStatus(500)
            ->assertJsonPath('error.code', 'SERVER_ERROR')
            ->assertDontSee('secret-internal-stack-detail');
    }
}
