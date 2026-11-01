@extends('admin.layout')
@section('title',$mode==='create'?'Add Vehicle':'Edit Vehicle')
@section('eyebrow','INVENTORY MANAGEMENT')
@section('actions')<a class="btn btn-outline" href="{{ route('manage.vehicles.index') }}">← Back to inventory</a>@endsection
@section('content')
 <form class="panel admin-edit" method="post" action="{{ $mode==='create'?route('manage.vehicles.store'):route('manage.vehicles.update',$vehicle) }}">
  @csrf @if($mode==='edit')@method('PATCH')@endif
  <div class="panel-head"><h2>Vehicle details</h2><p>Provide accurate information. Drafts remain private until you publish them.</p></div>
  <div class="fields-grid">
   <label>Stock reference *<input name="stock_reference" maxlength="60" required value="{{ old('stock_reference',$vehicle->stock_reference) }}" placeholder="e.g. CF-0001"></label>
   <label>Make *<input name="make" maxlength="80" required value="{{ old('make',$vehicle->make) }}" placeholder="Toyota"></label>
   <label>Model *<input name="model" maxlength="100" required value="{{ old('model',$vehicle->model) }}" placeholder="RAV4"></label>
   <label>Trim / variant<input name="trim" maxlength="100" value="{{ old('trim',$vehicle->trim) }}"></label>
   <label>Model year *<input type="number" name="year" min="1950" max="2035" required value="{{ old('year',$vehicle->year) }}"></label>
   <label>Price in KES *<input type="number" name="price_kes" min="0" required value="{{ old('price_kes',$vehicle->price_kes) }}"></label>
   <label>Mileage (km)<input type="number" min="0" name="mileage_km" value="{{ old('mileage_km',$vehicle->mileage_km) }}"></label>
   <label>Engine (cc)<input type="number" min="0" name="engine_cc" value="{{ old('engine_cc',$vehicle->engine_cc) }}"></label>
   <label>Fuel type<select name="fuel_type"><option value="">Choose fuel</option>@foreach(['Petrol','Diesel','Hybrid','Electric','Other'] as $v)<option value="{{ $v }}" @selected(old('fuel_type',$vehicle->fuel_type)===$v)>{{ $v }}</option>@endforeach</select></label>
   <label>Transmission<select name="transmission"><option value="">Choose transmission</option>@foreach(['Automatic','Manual','CVT','Other'] as $v)<option value="{{ $v }}" @selected(old('transmission',$vehicle->transmission)===$v)>{{ $v }}</option>@endforeach</select></label>
   <label>Drivetrain<select name="drivetrain"><option value="">Choose drivetrain</option>@foreach(['2WD','AWD','4WD','Other'] as $v)<option value="{{ $v }}" @selected(old('drivetrain',$vehicle->drivetrain)===$v)>{{ $v }}</option>@endforeach</select></label>
   <label>Body type<select name="body_type"><option value="">Choose body type</option>@foreach(['SUV','Sedan','Crossover','Hatchback','Pickup','Van','Coupe','Other'] as $v)<option value="{{ $v }}" @selected(old('body_type',$vehicle->body_type)===$v)>{{ $v }}</option>@endforeach</select></label>
   <label>Condition<select name="condition"><option value="">Choose condition</option>@foreach(['New','Foreign-used','Locally-used'] as $v)<option value="{{ $v }}" @selected(old('condition',$vehicle->condition)===$v)>{{ $v }}</option>@endforeach</select></label>
   <label>Exterior colour<input name="colour" maxlength="50" value="{{ old('colour',$vehicle->colour) }}"></label>
   <label>Location<input name="location" maxlength="140" value="{{ old('location',$vehicle->location) }}"></label>
   <label>Availability *<select name="status" required>@foreach(['available','reserved','sold'] as $v)<option value="{{ $v }}" @selected(old('status',$vehicle->status??'available')===$v)>{{ ucfirst($v) }}</option>@endforeach</select></label>
   <label class="full">Description<textarea name="description" rows="5">{{ old('description',$vehicle->description) }}</textarea></label>
   <div class="full"><p class="small-label">Features (optional; up to four)</p><div class="fields-grid">@for($i=0;$i<4;$i++)<label>Feature {{ $i+1 }}<input name="features[]" maxlength="120" value="{{ old('features.'.$i,($vehicle->features??[])[$i]??'') }}" placeholder="e.g. Reverse camera"></label>@endfor</div></div>
   <label class="check-field"><input type="checkbox" name="is_featured" value="1" @checked(old('is_featured',$vehicle->is_featured))> Featured vehicle</label>
   <label class="check-field"><input type="checkbox" name="is_published" value="1" @checked(old('is_published',$vehicle->is_published))> Publish publicly</label>
  </div>
  <div class="form-actions"><button type="submit" class="btn btn-primary">{{ $mode==='create'?'Create vehicle':'Save changes' }} →</button><a href="{{ route('manage.vehicles.index') }}" class="btn btn-outline">Cancel</a></div>
 </form>
 @if($mode==='edit')
 <section class="panel image-panel"><div class="panel-head"><h2>Vehicle photos</h2><p>Select up to 5 JPEG, PNG or WebP photos per upload (8 MB each). Maximum 20 total per vehicle. Upload again to add more.</p><p>{{ $vehicle->images->count() }} / 20 photos added.</p></div>
  <div class="vehicle-images">
   @forelse($vehicle->images as $image)
   <div class="vehicle-image"><img src="{{ \Illuminate\Support\Facades\Storage::disk($image->disk)->url($image->path) }}" alt="{{ $image->alt_text??'Vehicle photograph' }}"><div class="vehicle-image__meta">@if($image->is_primary)<span class="pill pill-available">Primary</span>@endif<form method="post" action="{{ route('manage.vehicles.photos.remove',[$vehicle,$image]) }}">@csrf @method('DELETE')<button type="submit">Remove</button></form></div></div>
   @empty <p class="empty">No photos uploaded yet.</p>
   @endforelse
  </div>
  <form class="photo-upload" method="post" enctype="multipart/form-data" action="{{ route('manage.vehicles.photos.add',$vehicle) }}">@csrf
   <label>Choose photos (1–5 per upload)<input type="file" name="photos[]" accept="image/jpeg,image/png,image/webp" multiple required></label>
   <label>Alternative text<input name="alt_text" maxlength="200" placeholder="e.g. Front view of vehicle"></label>
   <button type="submit" class="btn btn-primary">Upload selected photos</button><p class="small-label">If uploading several large files fails, check PHP upload_max_filesize and post_max_size. Total request must fit your server limits.</p>
  </form>
 </section>
 @endif
@endsection
