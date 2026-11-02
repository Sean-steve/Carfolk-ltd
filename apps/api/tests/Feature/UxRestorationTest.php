<?php
namespace Tests\Feature;

use App\Models\TestDriveRequest;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleEnquiry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UxRestorationTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true, 'is_active' => true]);
    }

    private function car(string $ref, string $make, string $status = 'available', bool $published = true): Vehicle
    {
        return Vehicle::create([
            'stock_reference' => $ref, 'slug' => strtolower($ref),
            'make' => $make, 'model' => 'Vehicle', 'year' => 2022,
            'price_kes' => 2000000, 'status' => $status, 'is_published' => $published,
        ]);
    }

    public function test_inventory_filters_respect_published_status_and_search_without_losing_counts(): void
    {
        $this->car('CF-AAA', 'Toyota');
        $this->car('CF-BBB', 'Honda', 'reserved');
        $this->car('CF-CCC', 'Mazda', 'available', false);
        $this->actingAs($this->admin());

        $this->get('/manage/vehicles?q=Honda')->assertOk()
            ->assertSee('Honda')->assertDontSee('Toyota')
            ->assertViewHas('inventorySummary', fn ($summary) => $summary['total'] === 3
                && $summary['published'] === 2 && $summary['drafts'] === 1
                && $summary['available'] === 1);
        $this->get('/manage/vehicles?visibility=draft')->assertOk()
            ->assertSee('Mazda')->assertDontSee('Honda')->assertDontSee('Toyota');
        $this->get('/manage/vehicles?status=reserved&visibility=published')->assertOk()
            ->assertSee('Honda')->assertDontSee('Mazda');
        $this->get('/manage/vehicles?q=not-a-stock-reference')->assertOk()
            ->assertSee('No vehicles match your filters');
        $this->get('/manage/vehicles?status=unrecognized')->assertSessionHasErrors('status');
    }

    public function test_enquiry_status_filters_and_context_are_real(): void
    {
        VehicleEnquiry::create([
            'name' => 'Awaiting Buyer', 'phone' => '0712345678', 'message' => 'Could I view the vehicle?',
            'consent_at' => now(), 'status' => 'new',
        ]);
        VehicleEnquiry::create([
            'name' => 'Closed Buyer', 'phone' => '0712345678', 'message' => 'Resolved',
            'consent_at' => now(), 'status' => 'closed',
        ]);
        $this->actingAs($this->admin())->get('/manage/enquiries?status=new')->assertOk()
            ->assertSee('Awaiting Buyer')->assertDontSee('Closed Buyer')
            ->assertViewHas('leadCounts', fn ($counts) => $counts['new'] === 1
                && $counts['closed'] === 1 && $counts['total'] === 2)
            ->assertSee('Follow-up flow');
        $this->get('/manage/enquiries?status=closed')->assertSee('Closed Buyer')->assertDontSee('Awaiting Buyer');
        $this->get('/manage/enquiries?status=fake')->assertSessionHasErrors('status');
    }

    public function test_viewing_filters_show_nairobi_times_and_confirmation_guidance(): void
    {
        $vehicle = $this->car('CF-DDD', 'Mazda');
        TestDriveRequest::create([
            'vehicle_id' => $vehicle->id, 'name' => 'Pending Viewer', 'phone' => '0712345678',
            'consent_at' => now(), 'requested_at' => now()->addDay(), 'status' => 'requested',
        ]);
        TestDriveRequest::create([
            'vehicle_id' => $vehicle->id, 'name' => 'Confirmed Viewer', 'phone' => '0712345678',
            'consent_at' => now(), 'requested_at' => now()->addDays(2), 'status' => 'confirmed',
        ]);
        $this->actingAs($this->admin())->get('/manage/viewings?status=requested')->assertOk()
            ->assertSee('Pending Viewer')->assertDontSee('Confirmed Viewer')
            ->assertSee('Appointment flow')
            ->assertSee('East Africa Time')
            ->assertViewHas('viewingCounts', fn ($counts) => $counts['requested'] === 1 && $counts['confirmed'] === 1);
        $this->get('/manage/viewings?status=confirmed')->assertSee('Confirmed Viewer')->assertDontSee('Pending Viewer');
    }

    public function test_vehicle_editor_and_settings_restore_context_without_stale_developer_notices(): void
    {
        $car = $this->car('CF-EEE', 'Honda', 'available', false);
        $this->actingAs($this->admin())->get('/manage/vehicles/create')->assertOk()
            ->assertSee('Identity and pricing')->assertSee('Specifications')
            ->assertSee('Public visibility')->assertDontSee('Phase 5 will complete that integration.');
        $this->get('/manage/vehicles/'.$car->id.'/edit')->assertOk()
            ->assertSee('Vehicle photos')->assertSee('STEP 2')
            ->assertSee('Add clear, approved photos');
        $this->get('/manage/settings')->assertOk()
            ->assertSee('Key details configured')
            ->assertSee('Contact channels')
            ->assertSee('A filled field is not proof of verification.');
    }

    public function test_untrusted_user_cannot_access_enhanced_admin_pages(): void
    {
        $this->get('/manage/vehicles?visibility=draft')->assertRedirect('/manage/login');
        $this->get('/manage/enquiries?status=new')->assertRedirect('/manage/login');
        $this->get('/manage/viewings?status=requested')->assertRedirect('/manage/login');
        $this->actingAs(User::factory()->create(['is_admin'=>false]))->get('/manage/vehicles')->assertForbidden();
    }
}
