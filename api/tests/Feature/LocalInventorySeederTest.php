<?php

namespace Tests\Feature;

use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocalInventorySeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_local_command_adds_five_admin_only_vehicles_without_replacing_staff_edits(): void
    {
        $this->artisan('carfolk:local-inventory')->assertExitCode(0);

        $this->assertDatabaseCount('vehicles', 5);
        $this->assertSame(0, Vehicle::published()->count());
        $this->assertDatabaseHas('vehicles', ['stock_reference' => 'CF-1001', 'make' => 'Toyota', 'is_published' => false]);
        $this->assertDatabaseHas('vehicles', ['stock_reference' => 'CF-1005', 'make' => 'Honda', 'is_published' => false]);

        $saved = Vehicle::where('stock_reference', 'CF-1001')->firstOrFail();
        $saved->update(['price_kes' => 1234567, 'status' => 'reserved', 'is_published' => true]);

        $this->artisan('carfolk:local-inventory')->assertExitCode(0);

        $this->assertDatabaseCount('vehicles', 5);
        $this->assertDatabaseHas('vehicles', [
            'id' => $saved->id, 'price_kes' => 1234567, 'status' => 'reserved', 'is_published' => true,
        ]);
    }

    public function test_publish_flag_makes_only_new_local_inventory_visible_to_public_api(): void
    {
        $this->artisan('carfolk:local-inventory', ['--publish' => true])->assertExitCode(0);
        $this->assertSame(5, Vehicle::published()->count());
        $this->getJson('/api/v1/vehicles')->assertOk()->assertJsonPath('meta.total', 5);
    }

    public function test_rejects_production_or_wrong_database_without_writing_any_rows(): void
    {
        $oldEnvironment = $this->app['env'];

        try {
            $this->app['env'] = 'production';
            $this->artisan('carfolk:local-inventory', ['--publish' => true])->assertExitCode(1);
        } finally {
            $this->app['env'] = $oldEnvironment;
        }

        $this->assertDatabaseCount('vehicles', 0);

        $originalHost = config('database.connections.pgsql.host');
        try {
            config()->set('database.connections.pgsql.host', 'external-postgres.invalid');
            $this->artisan('carfolk:local-inventory')->assertExitCode(1);
        } finally {
            config()->set('database.connections.pgsql.host', $originalHost);
        }

        $this->assertDatabaseCount('vehicles', 0);
    }
}
