@extends('admin.layout')
@section('title','Customer Enquiries')
@section('eyebrow','CUSTOMER CONTACT')
@section('content')
<p class="admin-intro">Respond to interested buyers, track first contact and close each conversation once it is resolved. These are enquiries, not confirmed sales.</p>
<div class="experience-stats" aria-label="Customer enquiry summary">
 @foreach(['total'=>'ALL ENQUIRIES','new'=>'AWAITING RESPONSE','contacted'=>'CONTACTED','closed'=>'CLOSED'] as $key=>$label)
  <a href="{{ $key==='total'?route('manage.enquiries'):route('manage.enquiries',['status'=>$key]) }}" class="{{ $statusFilter===$key||($key==='total'&&$statusFilter==='')?'is-current':'' }}"><span>{{ $label }}</span><strong>{{ number_format($leadCounts[$key]) }}</strong><small>{{ $key==='new'?'Needs an initial reply':'Open matching records →' }}</small></a>
 @endforeach
</div>
<section class="panel experience-panel">
 <div class="experience-title-row"><div><span class="admin-kicker">FOLLOW-UP WORKSPACE</span><h2>Incoming messages <small>({{ $leads->total() }} matching)</small></h2></div>
  <form method="get" action="{{ route('manage.enquiries') }}" class="experience-inline-filter"><label>Show <select name="status" onchange="this.form.submit()"><option value="">All enquiries</option>@foreach(['new','contacted','closed'] as $state)<option value="{{ $state }}" @selected($statusFilter===$state)>{{ ucfirst($state) }}</option>@endforeach</select></label><button type="submit" class="btn btn-outline btn-small">Filter</button></form>
 </div>
 <div class="experience-guidance"><strong>Follow-up flow:</strong> New → Contacted → Closed. Always verify the vehicle's current availability before promising a viewing or price.</div>
 <div class="table-scroll"><table class="admin-table"><thead><tr><th>Customer</th><th>Vehicle</th><th>Message</th><th>Received (EAT)</th><th>Follow-up status</th></tr></thead><tbody>
 @forelse($leads as $lead)
 <tr>
  <td><strong>{{ $lead->name }}</strong><small>{{ $lead->phone }}@if($lead->email)<br>{{ $lead->email }}@endif</small></td>
  <td>@if($lead->vehicle)<strong>{{ $lead->vehicle->make }} {{ $lead->vehicle->model }}</strong><small>{{ $lead->vehicle->stock_reference }}</small>@else General enquiry @endif</td>
  <td class="message-col">{{ $lead->message }}</td>
  <td><time datetime="{{ $lead->created_at->toIso8601String() }}">{{ $lead->created_at->timezone('Africa/Nairobi')->format('d M Y · H:i') }}</time>@if($lead->status==='new'&&$lead->created_at->lt(now()->subHours(48)))<br><span class="experience-warning">Awaiting &gt;48h</span>@endif</td>
  <td><form method="post" action="{{ route('manage.enquiries.update',$lead) }}" class="experience-status-form">@csrf @method('PATCH')<select name="status" aria-label="Enquiry status for {{ $lead->name }}">@foreach(['new','contacted','closed'] as $status)<option value="{{ $status }}" @selected($lead->status===$status)>{{ ucfirst($status) }}</option>@endforeach</select><button type="submit" class="btn btn-small">Save</button></form></td>
 </tr>
 @empty<tr><td class="empty" colspan="5"><div class="experience-empty"><strong>{{ $leadCounts['total']===0?'No enquiries received yet':'No enquiries in this status' }}</strong><p>{{ $leadCounts['total']===0?'Customer messages will appear here once Carfolk enables approved enquiry collection.':'Choose another status to see matching conversations.' }}</p><a class="btn btn-outline btn-small" href="{{ route('manage.enquiries') }}">View all enquiries</a></div></td></tr>@endforelse
 </tbody></table></div><div class="pagination">{{ $leads->links() }}</div>
 <p class="experience-footnote">Customer details are for authorized dealership follow-up only. Online messages do not create a reservation or guarantee availability.</p>
</section>
@endsection
