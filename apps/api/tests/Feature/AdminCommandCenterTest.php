<?php
namespace Tests\Feature;

use App\Models\AuditEvent;
use App\Models\TestDriveRequest;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleEnquiry;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCommandCenterTest extends TestCase
{
    use RefreshDatabase;

    private function vehicle(string $ref, string $status, bool $published = true, array $extra = []): Vehicle
    {
        return Vehicle::create(array_merge([
            'stock_reference' => $ref, 'slug' => strtolower($ref),
            'make' => 'Toyota', 'model' => 'RAV4', 'year' => 2021,
            'price_kes' => 3000000, 'status' => $status, 'is_published' => $published,
        ], $extra));
    }

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true, 'is_active' => true]);
    }

    public function test_anonymous_and_non_admin_users_cannot_view_private_dashboard(): void
    {
        $this->get('/manage')->assertRedirect('/manage/login');
        $this->actingAs(User::factory()->create(['is_admin' => false]))->get('/manage')->assertForbidden();
    }

    public function test_empty_dashboard_has_honest_zeroes_and_useful_workflows(): void
    {
        $this->actingAs($this->admin())->get('/manage')->assertOk()
            ->assertSee('Your showroom at a glance')
            ->assertSee('No vehicles yet')
            ->assertSee('No enquiries have been received yet')
            ->assertSee('No upcoming viewing requests')
            ->assertSee('KES 0')
            ->assertSee('Operational alerts')
            ->assertDontSee('Environment note:')
            ->assertDontSee('Sales revenue');
    }

    public function test_real_stock_counts_are_mutually_exclusive_and_never_presented_as_revenue(): void
    {
        $this->vehicle('PUBLIC-A', 'available');
        $this->vehicle('PUBLIC-R', 'reserved');
        $this->vehicle('PUBLIC-S', 'sold');
        $this->vehicle('DRAFT-A', 'available', false);
        $this->vehicle('ARCHIVED-A', 'available', true, ['archived_at' => now()]);
        $this->actingAs($this->admin())->get('/manage')->assertOk()
            ->assertViewHas('total', 4)
            ->assertViewHas('published', 3)
            ->assertViewHas('available', 1)
            ->assertViewHas('reserved', 1)
            ->assertViewHas('sold', 1)
            ->assertViewHas('drafts', 1)
            ->assertViewHas('askingValue', 3000000)
            ->assertSee('KES 3,000,000')
            ->assertSee('Asking prices, not sales proceeds')
            ->assertSee('Sale', false);
    }

    public function test_recent_enquiries_upcoming_viewings_activity_and_operational_alerts_are_database_driven(): void
    {
        $admin = $this->admin();
        $car = $this->vehicle('PUBLIC-B', 'available');
        $old = VehicleEnquiry::create([
            'vehicle_id' => $car->id, 'name' => 'Test Buyer',
            'phone' => '0712345678', 'message' => 'Interested in the vehicle',
            'status' => 'new', 'consent_at' => now(),
        ]);
        $old->forceFill(['created_at' => now()->subDays(3)])->saveQuietly();
        $past = TestDriveRequest::create([
            'vehicle_id' => $car->id, 'name' => 'Test Viewer', 'phone' => '0712345678',
            'status' => 'requested', 'consent_at' => now(),
            'requested_at' => now()->subDay(),
        ]);
        $next = TestDriveRequest::create([
            'vehicle_id' => $car->id, 'name' => 'Upcoming Viewer', 'phone' => '0712345678',
            'status' => 'confirmed', 'consent_at' => now(),
            'requested_at' => now()->addDay(),
        ]);
        $this->actingAs($admin);
        AuditEvent::record('vehicle.updated', $car);
        $this->get('/manage')->assertOk()
            ->assertViewHas('enquiries', 1)
            ->assertViewHas('viewings', 1)
            ->assertSee('Test Buyer')
            ->assertSee('Upcoming Viewer')
            ->assertSee('Vehicle details updated')
            ->assertSee('enquiries unanswered for over 48 hours')
            ->assertSee('unconfirmed viewings past their requested time')
            ->assertSee('available public vehicles without photos')
            ->assertDontSee('Test Viewer');
        $this->assertNotNull($past->id);
        $this->assertNotNull($next->id);
    }

    public function test_enquiry_trend_is_seven_nairobi_calendar_days_without_fabricated_counts(): void
    {
        $admin = $this->admin();
        $when = CarbonImmutable::now('Africa/Nairobi')->startOfDay()->addHour()->utc();
        $lead = VehicleEnquiry::create([
            'name' => 'Clock Tester', 'phone' => '0712345678',
            'message' => 'Testing local day', 'status' => 'new', 'consent_at' => now(),
        ]);
        $lead->forceFill(['created_at' => $when])->saveQuietly();
        $this->actingAs($admin)->get('/manage')->assertOk()
            ->assertViewHas('trendTotal', 1)
            ->assertViewHas('trend', fn ($days) => count($days) === 7
                && end($days)['date'] === CarbonImmutable::now('Africa/Nairobi')->toDateString()
                && end($days)['count'] === 1);
    }
}
