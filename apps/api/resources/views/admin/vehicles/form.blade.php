@extends('admin.layout')
@section('title',$mode==='create'?'Add Vehicle':'Edit Vehicle')
@section('eyebrow','INVENTORY MANAGEMENT')
@section('actions')<a class="btn btn-outline" href="{{ route('manage.vehicles.index') }}">← Back to inventory</a>@endsection
@section('content')
 <p class="admin-intro">{{ $mode==='create'?'Create an accurate stock record, review the details and decide when it is ready for buyers.':'Update the vehicle listing, manage photos and keep the published availability accurate.' }}</p>
 @if($mode==='edit')
 <div class="experience-summary-banner"><span class="pill {{ $vehicle->is_published?'pill-available':'pill-draft' }}">{{ $vehicle->is_published?'Published':'Draft' }}</span><strong>{{ $vehicle->make }} {{ $vehicle->model }} · {{ $vehicle->year }}</strong><small>{{ $vehicle->stock_reference }} · {{ ucfirst($vehicle->status) }}</small></div>
 @endif
 <form class="panel admin-edit" method="post" action="{{ $mode==='create'?route('manage.vehicles.store'):route('manage.vehicles.update',$vehicle) }}">
  @csrf @if($mode==='edit')@method('PATCH')@endif
  <div class="panel-head"><span class="admin-kicker">STEP 1 · VEHICLE RECORD</span><h2>Vehicle details</h2><p>Start with the unique stock reference and the exact details supplied by Carfolk.</p></div>
  <div class="fields-grid">
   <div class="experience-section-label"><h3>Identity and pricing</h3><p>Use the internal stock reference to prevent duplicates. Show the actual current selling price in Kenya shillings.</p></div>
   <label>Stock reference *<input name="stock_reference" maxlength="60" required value="{{ old('stock_reference',$vehicle->stock_reference) }}" placeholder="e.g. CF-0001"></label>
   <label>Make *<input name="make" maxlength="80" required value="{{ old('make',$vehicle->make) }}" placeholder="Toyota"></label>
   <label>Model *<input name="model" maxlength="100" required value="{{ old('model',$vehicle->model) }}" placeholder="RAV4"></label>
   <label>Trim / variant<input name="trim" maxlength="100" value="{{ old('trim',$vehicle->trim) }}"></label>
   <label>Model year *<input type="number" name="year" min="1950" max="2035" required value="{{ old('year',$vehicle->year) }}"></label>
   <label>Price in KES *<input type="number" name="price_kes" min="0" required value="{{ old('price_kes',$vehicle->price_kes) }}"></label>
   <div class="experience-section-label"><h3>Specifications</h3><p>Complete the details you can verify. Leave unknown optional fields empty instead of estimating.</p></div>
   <label>Mileage (km)<input type="number" min="0" name="mileage_km" value="{{ old('mileage_km',$vehicle->mileage_km) }}"></label>
   <label>Engine (cc)<input type="number" min="0" name="engine_cc" value="{{ old('engine_cc',$vehicle->engine_cc) }}"></label>
   <label>Fuel type<select name="fuel_type"><option value="">Choose fuel</option>@foreach(['Petrol','Diesel','Hybrid','Electric','Other'] as $v)<option value="{{ $v }}" @selected(old('fuel_type',$vehicle->fuel_type)===$v)>{{ $v }}</option>@endforeach</select></label>
   <label>Transmission<select name="transmission"><option value="">Choose transmission</option>@foreach(['Automatic','Manual','CVT','Other'] as $v)<option value="{{ $v }}" @selected(old('transmission',$vehicle->transmission)===$v)>{{ $v }}</option>@endforeach</select></label>
   <label>Drivetrain<select name="drivetrain"><option value="">Choose drivetrain</option>@foreach(['2WD','AWD','4WD','Other'] as $v)<option value="{{ $v }}" @selected(old('drivetrain',$vehicle->drivetrain)===$v)>{{ $v }}</option>@endforeach</select></label>
   <label>Body type<select name="body_type"><option value="">Choose body type</option>@foreach(['SUV','Sedan','Crossover','Hatchback','Pickup','Van','Coupe','Other'] as $v)<option value="{{ $v }}" @selected(old('body_type',$vehicle->body_type)===$v)>{{ $v }}</option>@endforeach</select></label>
   <label>Condition<select name="condition"><option value="">Choose condition</option>@foreach(['New','Foreign-used','Locally-used'] as $v)<option value="{{ $v }}" @selected(old('condition',$vehicle->condition)===$v)>{{ $v }}</option>@endforeach</select></label>
   <label>Exterior colour<input name="colour" maxlength="50" value="{{ old('colour',$vehicle->colour) }}"></label>
   <label>Location<input name="location" maxlength="140" value="{{ old('location',$vehicle->location) }}"></label>
   <div class="experience-section-label"><h3>Availability and description</h3><p>Available cars can receive viewing requests. Mark reserved or sold cars promptly to avoid disappointment.</p></div>
   <label>Availability *<select name="status" required>@foreach(['available','reserved','sold'] as $v)<option value="{{ $v }}" @selected(old('status',$vehicle->status??'available')===$v)>{{ ucfirst($v) }}</option>@endforeach</select></label>
   <label class="full">Description<textarea name="description" rows="5">{{ old('description',$vehicle->description) }}</textarea></label>
   <div class="full"><p class="small-label">Features (optional; up to four)</p><div class="fields-grid">@for($i=0;$i<4;$i++)<label>Feature {{ $i+1 }}<input name="features[]" maxlength="120" value="{{ old('features.'.$i,($vehicle->features??[])[$i]??'') }}" placeholder="e.g. Reverse camera"></label>@endfor</div></div>
   <div class="experience-section-label"><h3>Public visibility</h3><p>Save privately while preparing a listing. Publish only after reviewing stock facts and photo permissions.</p></div>
   <label class="check-field"><input type="checkbox" name="is_featured" value="1" @checked(old('is_featured',$vehicle->is_featured))> Featured vehicle</label>
   <label class="check-field"><input type="checkbox" name="is_published" value="1" @checked(old('is_published',$vehicle->is_published))> Publish publicly</label>
  </div>
  <p class="experience-footnote">* Required fields. Review descriptions, prices and availability before saving. If the listing is published, changes will be reflected in the public catalogue.</p>
  <div class="form-actions"><button type="submit" class="btn btn-primary">{{ $mode==='create'?'Create vehicle':'Save changes' }} →</button><a href="{{ route('manage.vehicles.index') }}" class="btn btn-outline">Cancel</a></div>
 </form>
 @if($mode==='edit')
 <section class="panel image-panel"><div class="panel-head"><span class="admin-kicker">STEP 2 · LISTING PHOTOGRAPHS</span><h2>Vehicle photos <small>({{ $vehicle->images->count() }}/20)</small></h2><p>Add clear, approved photos of this exact vehicle. The first uploaded image is used as the main photo.</p></div>
  <div class="vehicle-images">
   @forelse($vehicle->images as $image)
   <div class="vehicle-image"><img src="{{ \Illuminate\Support\Facades\Storage::disk($image->disk)->url($image->path) }}" alt="{{ $image->alt_text??'Vehicle photograph' }}"><div class="vehicle-image__meta">@if($image->is_primary)<span class="pill pill-available">Primary</span>@endif<form method="post" action="{{ route('manage.vehicles.photos.remove',[$vehicle,$image]) }}">@csrf @method('DELETE')<button type="submit">Remove</button></form></div></div>
   @empty <p class="empty">No photos yet. Add a front, rear and interior view when available and approved for use.</p>
   @endforelse
  </div>
  <form class="photo-upload" method="post" enctype="multipart/form-data" action="{{ route('manage.vehicles.photos.add',$vehicle) }}" data-photo-upload data-existing-photos="{{ $vehicle->images->count() }}" data-server-file-limit="{{ ini_get('upload_max_filesize') }}" data-server-post-limit="{{ ini_get('post_max_size') }}">@csrf
   <label>Choose photos (up to 5 JPEG, PNG or WebP; 8 MB each)<input type="file" name="photos[]" accept="image/jpeg,image/png,image/webp" multiple required></label>
   <label>Alternative text<input name="alt_text" maxlength="200" placeholder="e.g. Front view of vehicle"></label>
   <button type="submit" class="btn btn-primary">Upload selected photos</button>
   <p class="flash flash-error photo-upload-error" data-upload-error tabindex="-1" role="alert" hidden></p>
  </form>
  <p class="experience-footnote">Accepted: JPEG, PNG or WebP; up to 5 files per upload, 8 MB each, and 20 photos per vehicle. Only upload images Carfolk is allowed to publish.</p>
 </section>
 @endif
@endsection
