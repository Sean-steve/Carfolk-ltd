@extends('admin.layout')
@section('title','Vehicle Inventory')
@section('eyebrow','MANAGE YOUR STOCK')
@section('actions')<a class="btn btn-primary" href="{{ route('manage.vehicles.create') }}">+ Add vehicle</a>@endsection
@section('content')
 <p class="admin-intro">Keep every Carfolk vehicle accurate from first listing to sale. Search your stock, review publication status and open a vehicle to update photos, price or availability.</p>
 <div class="experience-stats" aria-label="Inventory summary">
  <div><span>ACTIVE STOCK</span><strong>{{ number_format($inventorySummary['total']) }}</strong><small>Not archived</small></div>
  <div><span>PUBLISHED</span><strong>{{ number_format($inventorySummary['published']) }}</strong><small>Visible to buyers</small></div>
  <div><span>AVAILABLE ONLINE</span><strong>{{ number_format($inventorySummary['available']) }}</strong><small>Ready for enquiries</small></div>
  <div><span>DRAFTS</span><strong>{{ number_format($inventorySummary['drafts']) }}</strong><small>Private until published</small></div>
 </div>
 <section class="panel experience-panel">
  <div class="experience-title-row"><div><span class="admin-kicker">YOUR SHOWROOM</span><h2>Vehicle records <small>({{ $vehicles->total() }} matching)</small></h2></div><a class="btn btn-outline btn-small" href="{{ route('manage.vehicles.create') }}">Add another vehicle</a></div>
  <form method="get" action="{{ route('manage.vehicles.index') }}" class="experience-filters" aria-label="Filter vehicle inventory">
   <label>Search stock<input name="q" type="search" maxlength="80" value="{{ $filters['q'] }}" placeholder="Stock ref, make or model"></label>
   <label>Availability<select name="status"><option value="">All statuses</option>@foreach(['available','reserved','sold'] as $status)<option value="{{ $status }}" @selected($filters['status']===$status)>{{ ucfirst($status) }}</option>@endforeach</select></label>
   <label>Visibility<select name="visibility"><option value="">Published & drafts</option><option value="published" @selected($filters['visibility']==='published')>Published only</option><option value="draft" @selected($filters['visibility']==='draft')>Drafts only</option></select></label>
   <button class="btn btn-primary" type="submit">Apply filters</button>
   <a class="btn btn-outline" href="{{ route('manage.vehicles.index') }}">Clear</a>
  </form>
  <div class="table-scroll"><table class="admin-table"><thead><tr><th>Vehicle</th><th>Stock ref</th><th>Price (KES)</th><th>Availability</th><th>Published</th><th>Photos</th><th>Actions</th></tr></thead><tbody>
   @forelse($vehicles as $v)
    <tr>
     <td><strong>{{ $v->make }} {{ $v->model }}</strong><small>{{ $v->year }} · {{ $v->trim??'Standard' }}</small></td>
     <td>{{ $v->stock_reference }}</td><td>{{ number_format($v->price_kes) }}</td>
     <td><span class="pill pill-{{ $v->status }}">{{ ucfirst($v->status) }}</span></td>
     <td><span class="pill {{ $v->is_published?'pill-available':'pill-draft' }}">{{ $v->is_published?'Yes':'Draft' }}</span></td>
     <td>@if($v->images_count===0)<span class="experience-warning">No photos</span>@else{{ $v->images_count }}@endif</td>
     <td class="table-actions"><a href="{{ route('manage.vehicles.edit',$v) }}">Edit</a><form method="post" action="{{ route('manage.vehicles.archive',$v) }}" onsubmit="return confirm('Archive this vehicle? It will be removed from public listings. Enquiry history remains.');">@csrf @method('DELETE')<button type="submit">Archive</button></form></td>
    </tr>
   @empty
    <tr><td colspan="7" class="empty"><div class="experience-empty"><strong>{{ $inventorySummary['total']===0?'No vehicles yet':'No vehicles match your filters' }}</strong><p>{{ $inventorySummary['total']===0?'Add your first verified vehicle to begin building the showroom.':'Try clearing a filter or search term.' }}</p><a class="btn btn-outline btn-small" href="{{ $inventorySummary['total']===0?route('manage.vehicles.create'):route('manage.vehicles.index') }}">{{ $inventorySummary['total']===0?'Add your first vehicle':'Reset search' }}</a></div></td></tr>
   @endforelse
  </tbody></table></div>
  <div class="pagination">{{ $vehicles->links() }}</div>
  <p class="experience-footnote">A draft is private. Published cars appear publicly, including reserved and sold listings with their availability status. Archived vehicles are removed from the showroom.</p>
 </section>
@endsection
