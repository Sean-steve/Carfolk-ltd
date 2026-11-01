@extends('admin.layout')
@section('title','Dealership Overview')
@section('eyebrow','CARFOLK COMMAND CENTER')
@section('actions')<a href="{{ route('manage.vehicles.create') }}" class="btn btn-primary">+ Add a vehicle</a>@endsection
@section('content')
  <div class="metric-grid">
    <div class="metric-card"><small>INVENTORY ENTRIES</small><strong>{{ number_format($total) }}</strong></div>
    <div class="metric-card"><small>PUBLISHED VEHICLES</small><strong>{{ number_format($published) }}</strong></div>
    <div class="metric-card"><small>AVAILABLE STOCK</small><strong>{{ number_format($available) }}</strong></div>
    <div class="metric-card"><small>NEW ENQUIRIES</small><strong>{{ number_format($enquiries) }}</strong></div>
    <div class="metric-card"><small>VIEWING REQUESTS</small><strong>{{ number_format($viewings) }}</strong></div>
  </div>
@endsection
