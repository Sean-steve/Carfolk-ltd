@extends('admin.layout')
@section('title','Dealership Overview')
@section('eyebrow','CARFOLK COMMAND CENTER')
@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin-dashboard.css') }}">
@endpush
@section('actions')<a href="{{ route('manage.vehicles.create') }}" class="btn btn-primary">+ Add a vehicle</a>@endsection
@section('content')
<div class="command-intro">
  <div><span class="admin-kicker">LIVE DEALERSHIP OPERATIONS</span><h2>Your showroom at a glance.</h2><p>Stock, customer interest and follow-ups from the Carfolk database.</p></div>
  <div class="command-intro-value"><small>PUBLISHED AVAILABLE STOCK · ASKING VALUE</small><strong>KES {{ number_format($askingValue) }}</strong><span>Asking prices, not sales proceeds</span></div>
</div>

<div class="metric-grid command-metrics">
  <a class="metric-card command-metric" href="{{ route('manage.vehicles.index') }}"><small>INVENTORY ENTRIES</small><strong>{{ number_format($total) }}</strong><span>Active, not archived ↗</span></a>
  <a class="metric-card command-metric" href="{{ route('manage.vehicles.index') }}"><small>PUBLISHED VEHICLES</small><strong>{{ number_format($published) }}</strong><span>Visible on the showroom ↗</span></a>
  <a class="metric-card command-metric" href="{{ route('manage.vehicles.index') }}"><small>AVAILABLE STOCK</small><strong>{{ number_format($available) }}</strong><span>Published and available ↗</span></a>
  <a class="metric-card command-metric" href="{{ route('manage.vehicles.index') }}"><small>RESERVED VEHICLES</small><strong>{{ number_format($reserved) }}</strong><span>Published reservations ↗</span></a>
  <a class="metric-card command-metric" href="{{ route('manage.vehicles.index') }}"><small>MARKED SOLD</small><strong>{{ number_format($sold) }}</strong><span>Stock status, not revenue ↗</span></a>
  <a class="metric-card command-metric" href="{{ route('manage.vehicles.index') }}"><small>UNPUBLISHED STOCK</small><strong>{{ number_format($drafts) }}</strong><span>Draft listings awaiting review ↗</span></a>
  <a class="metric-card command-metric command-metric--urgent" href="{{ route('manage.enquiries') }}"><small>NEW ENQUIRIES</small><strong>{{ number_format($enquiries) }}</strong><span>Awaiting first follow-up ↗</span></a>
  <a class="metric-card command-metric command-metric--urgent" href="{{ route('manage.viewings') }}"><small>VIEWING REQUESTS</small><strong>{{ number_format($viewings) }}</strong><span>Not yet confirmed ↗</span></a>
</div>

<div class="command-grid">
  <section class="panel command-panel" aria-labelledby="stock-chart-title">
    <div class="command-panel-heading"><div><span class="admin-kicker">INVENTORY INTELLIGENCE</span><h2 id="stock-chart-title">Stock composition</h2></div><a href="{{ route('manage.vehicles.index') }}">Manage inventory →</a></div>
    <p>All active stock, separated into public availability and private drafts.</p>
    @if($total > 0)
      <div class="command-stock-track" role="img" aria-label="{{ $available }} available, {{ $reserved }} reserved, {{ $sold }} marked sold and {{ $drafts }} unpublished">
        @foreach($stockBreakdown as $segment)
          @if($segment['count'] > 0)<span class="command-stock-fill command-stock-fill--{{ $segment['class'] }}" style="width:{{ round($segment['count'] / $total * 100, 3) }}%"></span>@endif
        @endforeach
      </div>
      <div class="command-stock-legend">
        @foreach($stockBreakdown as $segment)
          <div><span class="command-key command-key--{{ $segment['class'] }}"></span><span>{{ $segment['label'] }}</span><strong>{{ number_format($segment['count']) }}</strong></div>
        @endforeach
      </div>
    @else
      <div class="command-empty">No vehicles yet. Add approved stock to populate your showroom.</div>
    @endif
    <p class="command-note">Vehicles marked sold are not verified financial sales. Final sale prices and transaction dates are not recorded by this system.</p>
  </section>

  <section class="panel command-panel" aria-labelledby="interest-title">
    <div class="command-panel-heading"><div><span class="admin-kicker">CUSTOMER INTEREST</span><h2 id="interest-title">Enquiry trend</h2></div><span class="command-period">LAST 7 DAYS · EAT</span></div>
    <p><strong>{{ number_format($trendTotal) }}</strong> enquiries received over seven Nairobi calendar days.</p>
    <div class="command-chart" role="group" aria-label="Seven-day enquiry trend">
      @foreach($trend as $day)
        <div class="command-chart-day" aria-label="{{ $day['date'] }}: {{ $day['count'] }} enquiries">
          <strong>{{ $day['count'] }}</strong>
          <div class="command-chart-column"><span style="height:{{ $day['count'] ? max(7, round($day['count'] / $trendMax * 100)) : 0 }}%"></span></div>
          <time datetime="{{ $day['date'] }}" title="{{ $day['date'] }}">{{ $day['label'] }}</time>
        </div>
      @endforeach
    </div>
    <p class="command-note">Actual submissions only. A zero means none were recorded on that day.</p>
  </section>
</div>

<div class="command-grid">
  <section class="panel command-panel" aria-labelledby="enquiry-title">
    <div class="command-panel-heading"><div><span class="admin-kicker">LEAD HANDLING</span><h2 id="enquiry-title">Recent customer enquiries</h2></div><a href="{{ route('manage.enquiries') }}">View all →</a></div>
    <div class="command-list">
      @forelse($recentEnquiries as $lead)
        <div class="command-row">
          <span class="command-initial" aria-hidden="true">{{ mb_strtoupper(mb_substr($lead->name, 0, 1)) }}</span>
          <div class="command-row-body"><strong>{{ $lead->name }}</strong><small>{{ $lead->vehicle ? $lead->vehicle->make.' '.$lead->vehicle->model : 'General enquiry' }} · <time datetime="{{ $lead->created_at->toIso8601String() }}">{{ $lead->created_at->timezone('Africa/Nairobi')->format('d M, H:i') }} EAT</time></small></div>
          <span class="command-status command-status--{{ $lead->status === 'new' ? 'new' : 'muted' }}">{{ ucfirst($lead->status) }}</span>
        </div>
      @empty
        <div class="command-empty">No enquiries have been received yet.</div>
      @endforelse
    </div>
  </section>

  <section class="panel command-panel" aria-labelledby="viewing-title">
    <div class="command-panel-heading"><div><span class="admin-kicker">APPOINTMENT REQUESTS</span><h2 id="viewing-title">Upcoming viewings</h2></div><a href="{{ route('manage.viewings') }}">View all →</a></div>
    <div class="command-list">
      @forelse($upcomingViewings as $viewing)
        <div class="command-row">
          <span class="command-date"><strong>{{ $viewing->requested_at->timezone('Africa/Nairobi')->format('d') }}</strong><small>{{ $viewing->requested_at->timezone('Africa/Nairobi')->format('M') }}</small></span>
          <div class="command-row-body"><strong>{{ $viewing->vehicle?->make }} {{ $viewing->vehicle?->model }}</strong><small>{{ $viewing->name }} · {{ $viewing->requested_at->timezone('Africa/Nairobi')->format('D H:i') }} EAT</small></div>
          <span class="command-status command-status--{{ $viewing->status === 'confirmed' ? 'good' : 'new' }}">{{ ucfirst($viewing->status) }}</span>
        </div>
      @empty
        <div class="command-empty">No upcoming viewing requests. Past unconfirmed requests appear in alerts.</div>
      @endforelse
    </div>
    <p class="command-note">Requested appointments are not confirmed until an administrator approves them.</p>
  </section>
</div>

<div class="command-grid">
  <section class="panel command-panel" aria-labelledby="recent-stock-title">
    <div class="command-panel-heading"><div><span class="admin-kicker">LATEST STOCK</span><h2 id="recent-stock-title">Recently added vehicles</h2></div><a href="{{ route('manage.vehicles.index') }}">View all →</a></div>
    <div class="command-list">
      @forelse($recentVehicles as $vehicle)
        <a class="command-row command-row--link" href="{{ route('manage.vehicles.edit', $vehicle) }}">
          <span class="command-initial command-initial--stock" aria-hidden="true">CF</span>
          <span class="command-row-body"><strong>{{ $vehicle->make }} {{ $vehicle->model }} · {{ $vehicle->year }}</strong><small>{{ $vehicle->stock_reference }} · KES {{ number_format($vehicle->price_kes) }}</small></span>
          <span class="command-status command-status--{{ $vehicle->is_published ? 'good' : 'muted' }}">{{ $vehicle->is_published ? ucfirst($vehicle->status) : 'Draft' }}</span>
        </a>
      @empty
        <div class="command-empty">No vehicles added yet.</div>
      @endforelse
    </div>
  </section>

  <section class="panel command-panel" aria-labelledby="activity-title">
    <div class="command-panel-heading"><div><span class="admin-kicker">AUDIT TRAIL</span><h2 id="activity-title">Recent activity</h2></div><span class="command-period">LATEST EVENTS</span></div>
    <div class="command-list">
      @forelse($recentActivity as $event)
        <div class="command-row">
          <span class="command-activity-dot" aria-hidden="true"></span>
          <div class="command-row-body"><strong>{{ match($event->action) {
            'vehicle.created' => 'Vehicle added',
            'vehicle.updated' => 'Vehicle details updated',
            'vehicle.archived' => 'Vehicle archived',
            'enquiry.status_changed' => 'Enquiry status updated',
            'viewing.status_changed' => 'Viewing status updated',
            'site.settings_updated' => 'Dealership details updated',
            default => 'Management action'
          } }}</strong><small><time datetime="{{ $event->created_at->toIso8601String() }}">{{ $event->created_at->timezone('Africa/Nairobi')->format('d M Y, H:i') }} EAT</time></small></div>
        </div>
      @empty
        <div class="command-empty">No recorded management activity yet.</div>
      @endforelse
    </div>
  </section>
</div>

<div class="command-grid">
  <section class="panel command-panel" aria-labelledby="alert-title">
    <div class="command-panel-heading"><div><span class="admin-kicker">WHAT NEEDS ATTENTION</span><h2 id="alert-title">Operational alerts</h2></div><span class="command-period">{{ count($alerts) }} ISSUES</span></div>
    <div class="command-list">
      @forelse($alerts as $alert)
        <a class="command-alert command-alert--{{ $alert['tone'] }}" href="{{ route($alert['route']) }}">
          <span class="command-alert-dot" aria-hidden="true"></span>
          <span><strong>{{ $alert['title'] }}</strong><small>{{ $alert['detail'] }}</small></span>
          <span aria-hidden="true">→</span>
        </a>
      @empty
        <div class="command-empty command-empty--good">No outstanding record-quality or follow-up alerts.</div>
      @endforelse
    </div>
    <p class="command-note">Completeness checks do not independently verify vehicle ownership or contact authenticity.</p>
  </section>

  <section class="panel command-panel" aria-labelledby="quick-title">
    <div class="command-panel-heading"><div><span class="admin-kicker">YOUR WORKSPACE</span><h2 id="quick-title">Quick actions</h2></div></div>
    <div class="command-quick">
      <a href="{{ route('manage.vehicles.create') }}"><span>+</span><strong>Add a vehicle</strong><small>Register new stock</small></a>
      <a href="{{ route('manage.vehicles.index') }}"><span>↗</span><strong>Manage inventory</strong><small>Pricing, photos and availability</small></a>
      <a href="{{ route('manage.enquiries') }}"><span>↗</span><strong>Customer follow-ups</strong><small>Manage incoming enquiries</small></a>
      <a href="{{ route('manage.viewings') }}"><span>↗</span><strong>Viewing requests</strong><small>Confirm and reschedule visits</small></a>
    </div>
  </section>
</div>
@endsection
