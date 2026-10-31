@extends('admin.layout')
@section('title','Viewing Requests')
@section('eyebrow','APPOINTMENT REQUESTS')
@section('content')
<div class="panel"><div class="panel-head"><h2>Customer viewing requests</h2><p>A submitted request is not automatically a confirmed appointment. Contact the customer and confirm availability first.</p></div>
<div class="table-scroll"><table class="admin-table"><thead><tr><th>Customer</th><th>Vehicle</th><th>Preferred date / time (EAT)</th><th>Notes</th><th>Status</th></tr></thead><tbody>
@forelse($viewings as $v)<tr><td><strong>{{ $v->name }}</strong><small>{{ $v->phone }}@if($v->email)<br>{{ $v->email }}@endif</small></td><td>{{ $v->vehicle?->make }} {{ $v->vehicle?->model }}</td><td>{{ $v->requested_at->timezone('Africa/Nairobi')->format('d M Y · H:i') }}</td><td class="message-col">{{ $v->message??'—' }}</td><td><form method="post" action="{{ route('manage.viewings.update',$v) }}">@csrf @method('PATCH')<select name="status" aria-label="Viewing status for {{ $v->name }}">@foreach(['requested','confirmed','completed','cancelled'] as $status)<option value="{{ $status }}" @selected($v->status===$status)>{{ ucfirst($status) }}</option>@endforeach</select><button type="submit" class="btn btn-small">Save</button></form></td></tr>
@empty<tr><td class="empty" colspan="5">No viewing requests received yet.</td></tr>@endforelse
</tbody></table></div><div class="pagination">{{ $viewings->links() }}</div></div>
@endsection
