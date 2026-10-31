@extends('admin.layout')
@section('title','Vehicle Inventory')
@section('eyebrow','MANAGE YOUR STOCK')
@section('actions')<a class="btn btn-primary" href="{{ route('manage.vehicles.create') }}">+ Add vehicle</a>@endsection
@section('content')
 <div class="panel">
 <div class="panel-head"><h2>Your vehicles</h2><p>{{ $vehicles->total() }} records. Drafts and unpublished vehicles stay hidden from customers.</p></div>
 <div class="table-scroll"><table class="admin-table"><thead><tr><th>Vehicle</th><th>Stock ref</th><th>Price (KES)</th><th>Availability</th><th>Published</th><th>Photos</th><th>Actions</th></tr></thead><tbody>
 @forelse($vehicles as $v)
 <tr><td><strong>{{ $v->make }} {{ $v->model }}</strong><small>{{ $v->year }} · {{ $v->trim??'Standard' }}</small></td><td>{{ $v->stock_reference }}</td><td>{{ number_format($v->price_kes) }}</td><td><span class="pill pill-{{ $v->status }}">{{ ucfirst($v->status) }}</span></td><td><span class="pill {{ $v->is_published?'pill-available':'pill-draft' }}">{{ $v->is_published?'Yes':'Draft' }}</span></td><td>{{ $v->images_count }}</td><td class="table-actions"><a href="{{ route('manage.vehicles.edit',$v) }}">Edit</a><form method="post" action="{{ route('manage.vehicles.archive',$v) }}" onsubmit="return confirm('Archive this vehicle? It will be removed from public listings. Enquiry history remains.');">@csrf @method('DELETE')<button type="submit">Archive</button></form></td></tr>
 @empty <tr><td colspan="7" class="empty">No vehicles added. Create your first verified listing to begin.</td></tr>
 @endforelse</tbody></table></div>
 <div class="pagination">{{ $vehicles->links() }}</div>
 </div>
@endsection
