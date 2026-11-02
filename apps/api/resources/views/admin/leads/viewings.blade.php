@extends('admin.layout')
@section('title','Viewing Requests')
@section('eyebrow','APPOINTMENT REQUESTS')
@section('content')
<p class="admin-intro">Keep requested visits organized and confirm each appointment with the customer. Times are shown in East Africa Time (Nairobi).</p>
<div class="experience-stats" aria-label="Viewing request summary">
 @foreach(['total'=>'ALL REQUESTS','requested'=>'TO CONFIRM','confirmed'=>'CONFIRMED','completed'=>'COMPLETED','cancelled'=>'CANCELLED'] as $key=>$label)
  <a href="{{ $key==='total'?route('manage.viewings'):route('manage.viewings',['status'=>$key]) }}" class="{{ $statusFilter===$key||($key==='total'&&$statusFilter==='')?'is-current':'' }}"><span>{{ $label }}</span><strong>{{ number_format($viewingCounts[$key]) }}</strong><small>View {{ strtolower($label) }} →</small></a>
 @endforeach
</div>
<section class="panel experience-panel">
 <div class="experience-title-row"><div><span class="admin-kicker">APPOINTMENT DESK</span><h2>Customer viewing requests <small>({{ $viewings->total() }} matching)</small></h2></div>
  <form method="get" action="{{ route('manage.viewings') }}" class="experience-inline-filter"><label>Status <select name="status" onchange="this.form.submit()"><option value="">All requests</option>@foreach(['requested','confirmed','completed','cancelled'] as $state)<option value="{{ $state }}" @selected($statusFilter===$state)>{{ ucfirst($state) }}</option>@endforeach</select></label><button type="submit" class="btn btn-outline btn-small">Filter</button></form>
 </div>
 <div class="experience-guidance"><strong>Appointment flow:</strong> Requested → Confirmed → Completed. Contact the buyer first; if the car is unavailable, discuss an alternative and cancel the original request if needed.</div>
 <div class="table-scroll"><table class="admin-table"><thead><tr><th>Customer</th><th>Vehicle</th><th>Preferred date / time (EAT)</th><th>Notes</th><th>Appointment status</th></tr></thead><tbody>
 @forelse($viewings as $v)
 <tr>
  <td><strong>{{ $v->name }}</strong><small>{{ $v->phone }}@if($v->email)<br>{{ $v->email }}@endif</small></td>
  <td>@if($v->vehicle)<strong>{{ $v->vehicle->make }} {{ $v->vehicle->model }}</strong><small>{{ $v->vehicle->stock_reference }} · {{ ucfirst($v->vehicle->status) }}</small>@else Vehicle unavailable @endif</td>
  <td><time datetime="{{ $v->requested_at->toIso8601String() }}">{{ $v->requested_at->timezone('Africa/Nairobi')->format('d M Y · H:i') }}</time>@if($v->status==='requested'&&$v->requested_at->isPast())<br><span class="experience-warning">Past due · unconfirmed</span>@endif</td>
  <td class="message-col">{{ $v->message??'—' }}</td>
  <td><form method="post" action="{{ route('manage.viewings.update',$v) }}" class="experience-status-form">@csrf @method('PATCH')<select name="status" aria-label="Viewing status for {{ $v->name }}">@foreach(['requested','confirmed','completed','cancelled'] as $status)<option value="{{ $status }}" @selected($v->status===$status)>{{ ucfirst($status) }}</option>@endforeach</select><button type="submit" class="btn btn-small">Save</button></form></td>
 </tr>
 @empty<tr><td class="empty" colspan="5"><div class="experience-empty"><strong>{{ $viewingCounts['total']===0?'No viewing requests received yet':'No viewing requests in this status' }}</strong><p>{{ $viewingCounts['total']===0?'Viewing requests will appear here after customers submit them through the approved site.':'Choose another status to see existing requests.' }}</p><a class="btn btn-outline btn-small" href="{{ route('manage.viewings') }}">View all requests</a></div></td></tr>@endforelse
 </tbody></table></div><div class="pagination">{{ $viewings->links() }}</div>
 <p class="experience-footnote">Submitting a viewing request does not reserve a car or confirm an appointment. Only confirm a visit after speaking with the customer.</p>
</section>
@endsection
