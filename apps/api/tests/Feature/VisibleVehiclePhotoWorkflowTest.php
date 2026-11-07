<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class VisibleVehiclePhotoWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true, 'is_active' => true]);
    }

    public function test_create_form_shows_the_photo_step_before_vehicle_is_saved(): void
    {
        $this->actingAs($this->admin())
            ->get('/manage/vehicles/create')
            ->assertOk()
            ->assertSee('Vehicle photos')
            ->assertSee('Save details to enable')
            ->assertSee('Your photo upload workspace is ready.')
            ->assertDontSee('name="photos[]"', false);
    }

    public function test_new_vehicle_save_advances_directly_to_the_photo_workspace(): void
    {
        $this->actingAs($this->admin());
        $response = $this->post('/manage/vehicles', [
            'stock_reference' => 'CF-O71-100',
            'make' => 'Toyota',
            'model' => 'Corolla',
            'year' => 2022,
            'price_kes' => 1800000,
            'status' => 'available',
        ]);

        $vehicle = Vehicle::where('stock_reference', 'CF-O71-100')->firstOrFail();

        $response->assertRedirect(route('manage.vehicles.edit', $vehicle).'#vehicle-photos')
            ->assertSessionHas('success', fn ($message) => str_contains($message, 'Next: add vehicle photos'));

        $page = $this->get(route('manage.vehicles.edit', $vehicle));
        $page->assertOk()
            ->assertSee('id="vehicle-photos"', false)
            ->assertSee('data-photo-upload', false)
            ->assertSee('Go to photo upload')
            ->assertSee('name="photos[]"', false);

        // The photo selector appears before the gallery, not after a long image list.
        $content = $page->getContent();
        $this->assertLessThan(
            strpos($content, 'class="vehicle-images vehicle-images--manage"'),
            strpos($content, 'data-photo-upload')
        );
    }

    public function test_inventory_has_direct_photo_management_link(): void
    {
        $vehicle = Vehicle::create([
            'stock_reference' => 'CF-O71-200',
            'slug' => 'o71-media-test',
            'make' => 'Honda',
            'model' => 'Fit',
            'year' => 2020,
            'price_kes' => 900000,
        ]);

        $this->actingAs($this->admin())
            ->get('/manage/vehicles')
            ->assertOk()
            ->assertSee('Add photos')
            ->assertSee(route('manage.vehicles.edit', $vehicle).'#vehicle-photos', false);
    }

    public function test_photo_workspace_remains_protected_by_admin_authentication(): void
    {
        $this->get('/manage/vehicles/create')->assertRedirect('/manage/login');

        $normalUser = User::factory()->create(['is_admin' => false, 'is_active' => true]);
        $this->actingAs($normalUser)->get('/manage/vehicles/create')->assertForbidden();
    }
}
