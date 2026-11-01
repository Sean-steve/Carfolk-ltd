@extends('admin.layout')
@section('title','Customer Enquiries')
@section('eyebrow','CUSTOMER CONTACT')
@section('content')
<div class="panel"><div class="panel-head"><h2>Incoming messages</h2></div>
<div class="table-scroll"><table class="admin-table"><thead><tr><th>Customer</th><th>Vehicle</th><th>Message</th><th>Received</th><th>Status</th></tr></thead><tbody>
@forelse($leads as $lead)<tr><td><strong>{{ $lead->name }}</strong><small>{{ $lead->phone }}@if($lead->email)<br>{{ $lead->email }}@endif</small></td><td>{{ $lead->vehicle?->make }} {{ $lead->vehicle?->model }}</td><td class="message-col">{{ $lead->message }}</td><td>{{ $lead->created_at->timezone('Africa/Nairobi')->format('d M Y H:i') }}</td><td><form method="post" action="{{ route('manage.enquiries.update',$lead) }}">@csrf @method('PATCH')<select name="status" aria-label="Enquiry status for {{ $lead->name }}">@foreach(['new','contacted','closed'] as $status)<option value="{{ $status }}" @selected($lead->status===$status)>{{ ucfirst($status) }}</option>@endforeach</select><button type="submit" class="btn btn-small">Save</button></form></td></tr>
@empty<tr><td class="empty" colspan="5">No enquiries received yet.</td></tr>@endforelse
</tbody></table></div><div class="pagination">{{ $leads->links() }}</div></div>
@endsection
