<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditEvent;
use App\Models\SiteSetting;
use App\Models\TestDriveRequest;
use App\Models\Vehicle;
use App\Models\VehicleEnquiry;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $active = Vehicle::query()->whereNull('archived_at');
        $public = Vehicle::published();
        $now = CarbonImmutable::now('Africa/Nairobi');
        $today = $now->startOfDay();

        $total = (clone $active)->count();
        $published = (clone $public)->count();
        $available = (clone $public)->where('status', 'available')->count();
        $reserved = (clone $public)->where('status', 'reserved')->count();
        $sold = (clone $public)->where('status', 'sold')->count();
        $drafts = $total - $published;
        $newEnquiries = VehicleEnquiry::where('status', 'new')->count();
        $pendingViewings = TestDriveRequest::where('status', 'requested')->count();
        // Asking prices of unsold public stock are not revenues or recognized sales.
        $askingValue = (int) (clone $public)->where('status', 'available')->sum('price_kes');

        // Group against Nairobi-local dates to prevent UTC midnight from splitting local days.
        $start = $today->subDays(6);
        $counts = VehicleEnquiry::query()
            ->selectRaw("DATE(created_at AT TIME ZONE 'Africa/Nairobi') AS day, COUNT(*) AS total")
            ->where('created_at', '>=', $start->utc())
            ->groupByRaw("DATE(created_at AT TIME ZONE 'Africa/Nairobi')")
            ->pluck('total', 'day')->all();
        $trend = [];
        for ($i = 0; $i < 7; $i++) {
            $day = $start->addDays($i);
            $trend[] = [
                'date' => $day->toDateString(),
                'label' => $day->format('D'),
                'count' => (int) ($counts[$day->toDateString()] ?? 0),
            ];
        }
        $stockBreakdown = [
            ['label' => 'Available', 'count' => $available, 'class' => 'available'],
            ['label' => 'Reserved', 'count' => $reserved, 'class' => 'reserved'],
            ['label' => 'Marked sold', 'count' => $sold, 'class' => 'sold'],
            ['label' => 'Unpublished', 'count' => $drafts, 'class' => 'draft'],
        ];

        $missingPhotos = (clone $public)->where('status', 'available')->whereDoesntHave('images')->count();
        $staleEnquiries = VehicleEnquiry::where('status', 'new')
            ->where('created_at', '<', $now->utc()->subHours(48))->count();
        $overdueViewings = TestDriveRequest::where('status', 'requested')
            ->where('requested_at', '<', $now->utc())->count();
        $site = SiteSetting::first();
        $missingContacts = collect(['phone', 'whatsapp', 'email', 'address', 'opening_hours'])
            ->contains(fn ($field) => blank($site?->{$field}));

        $alerts = [];
        if ($staleEnquiries > 0) {
            $alerts[] = ['title' => "{$staleEnquiries} enquiries unanswered for over 48 hours", 'detail' => 'Prioritize customer follow-up.', 'route' => 'manage.enquiries', 'tone' => 'critical'];
        }
        if ($overdueViewings > 0) {
            $alerts[] = ['title' => "{$overdueViewings} unconfirmed viewings past their requested time", 'detail' => 'Contact customers to reschedule or update their requests.', 'route' => 'manage.viewings', 'tone' => 'critical'];
        }
        if ($missingPhotos > 0) {
            $alerts[] = ['title' => "{$missingPhotos} available public vehicles without photos", 'detail' => 'Upload approved images to improve listing quality.', 'route' => 'manage.vehicles.index', 'tone' => 'warning'];
        }
        if ($drafts > 0) {
            $alerts[] = ['title' => "{$drafts} unpublished vehicles", 'detail' => 'Verify stock details before publication.', 'route' => 'manage.vehicles.index', 'tone' => 'neutral'];
        }
        if ($missingContacts) {
            $alerts[] = ['title' => 'Dealership contact details are incomplete', 'detail' => 'Add only verified business information.', 'route' => 'manage.settings', 'tone' => 'warning'];
        }

        return view('admin.dashboard', [
            'total' => $total, 'published' => $published, 'available' => $available,
            'reserved' => $reserved, 'sold' => $sold, 'drafts' => $drafts,
            'enquiries' => $newEnquiries, 'viewings' => $pendingViewings,
            'askingValue' => $askingValue,
            'trend' => $trend,
            'trendMax' => max(1, ...array_column($trend, 'count')),
            'trendTotal' => array_sum(array_column($trend, 'count')),
            'stockBreakdown' => $stockBreakdown,
            'alerts' => $alerts,
            'recentEnquiries' => VehicleEnquiry::with('vehicle:id,make,model,stock_reference')->latest()->limit(5)->get(),
            'upcomingViewings' => TestDriveRequest::with('vehicle:id,make,model,stock_reference')
                ->whereIn('status', ['requested', 'confirmed'])
                ->where('requested_at', '>=', $now->utc())
                ->orderBy('requested_at')->limit(5)->get(),
            'recentVehicles' => (clone $active)->latest()->limit(5)->get(),
            'recentActivity' => AuditEvent::query()->whereIn('action', [
                'vehicle.created', 'vehicle.updated', 'vehicle.archived',
                'enquiry.status_changed', 'viewing.status_changed', 'site.settings_updated',
            ])->orderByDesc('created_at')->limit(6)->get(),
        ]);
    }
}
