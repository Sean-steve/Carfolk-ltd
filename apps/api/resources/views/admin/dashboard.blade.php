@extends('admin.layout')
@section('title','Dealership Overview')
@section('eyebrow','CARFOLK COMMAND CENTER')
@section('actions')<a href="{{ route('manage.vehicles.create') }}" class="btn btn-primary">+ Add a vehicle</a>@endsection
@section('content')
  <p class="admin-intro">Your live dealership inventory and incoming customer requests in one place. Figures below are calculated from the PostgreSQL database.</p>
  <div class="metric-grid">
    <div class="metric-card"><small>INVENTORY ENTRIES</small><strong>{{ number_format($total) }}</strong><span>Active (not archived)</span></div>
    <div class="metric-card"><small>PUBLISHED VEHICLES</small><strong>{{ number_format($published) }}</strong><span>Visible in public API</span></div>
    <div class="metric-card"><small>AVAILABLE STOCK</small><strong>{{ number_format($available) }}</strong><span>Published + available</span></div>
    <div class="metric-card"><small>NEW ENQUIRIES</small><strong>{{ number_format($enquiries) }}</strong><span>Awaiting first response</span></div>
    <div class="metric-card"><small>VIEWING REQUESTS</small><strong>{{ number_format($viewings) }}</strong><span>Not yet confirmed</span></div>
  </div>
  <div class="dashboard-columns">
   <section class="panel"><span class="admin-kicker">WORKSPACE</span><h2>Inventory management</h2><p>Add verified vehicles, upload photos, choose featured stock, adjust prices, and mark a car as available, reserved, or sold.</p><a class="btn btn-outline" href="{{ route('manage.vehicles.index') }}">Manage inventory →</a></section>
   <section class="panel"><span class="admin-kicker">LEAD HANDLING</span><h2>Customer follow-ups</h2><p>Review incoming enquiries, keep track of viewing requests, and update statuses as your team responds.</p><a class="btn btn-outline" href="{{ route('manage.enquiries') }}">View customer enquiries →</a></section>
  </div>
  <div class="panel info-block"><strong>Environment note:</strong> This dashboard operates on the Laravel host. The public GitHub Pages website is still a preview and is not yet wired to this backend. Phase 5 will complete that integration.</div>
@endsection
