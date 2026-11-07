@extends('admin.layout')
@section('title',$mode==='create'?'Add Vehicle':'Edit Vehicle')
@section('eyebrow','INVENTORY MANAGEMENT')
@section('actions')
 @if($mode==='edit')<a class="btn btn-primary" href="{{ route('manage.vehicles.preview',$vehicle) }}">Preview vehicle listing ↗</a>@endif
 <a class="btn btn-outline" href="{{ route('manage.vehicles.index') }}">← Back to inventory</a>
@endsection
@section('content')
 <p class="admin-intro">{{ $mode==='create'?'Create the vehicle record first, then add photographs in the clearly marked Photos step.':'Update the vehicle details or jump straight to the Photos step to manage the listing gallery.' }}</p>
 <nav class="vehicle-stepper" aria-label="Vehicle setup">
  <a class="vehicle-step vehicle-step--active" href="#vehicle-details" aria-label="Step 1: Vehicle details">
   <span class="vehicle-step__number" aria-hidden="true">1</span><span><strong>Vehicle details</strong><small>{{ $mode==='create'?'Start here':'Saved · Edit anytime' }}</small></span>
  </a>
  @if($mode==='edit')
   <a class="vehicle-step vehicle-step--ready" href="#vehicle-photos" aria-label="Step 2: Vehicle photos, {{ $vehicle->images->count() }} of 20 uploaded">
    <span class="vehicle-step__number" aria-hidden="true">2</span><span><strong>Vehicle photos</strong><small>{{ $vehicle->images->count() }}/20 uploaded · Add or manage photos</small></span><span class="vehicle-step__arrow" aria-hidden="true">↓</span>
   </a>
  @else
   <div class="vehicle-step vehicle-step--locked" aria-label="Step 2: Vehicle photos, available after saving">
    <span class="vehicle-step__number" aria-hidden="true">2</span><span><strong>Vehicle photos</strong><small>Next · Available after saving</small></span>
   </div>
  @endif
 </nav>
 @if($mode==='edit')
 <div class="vehicle-photo-callout" role="note">
  <div><strong>Ready to add vehicle photos?</strong><p>You can upload photographs now without editing the vehicle details. The photo workspace is always visible in Step 2.</p></div>
  <a class="btn btn-primary" href="#vehicle-photos">Go to photo upload ↓</a>
 </div>
 @else
 <div class="vehicle-photo-callout" role="note">
  <div><strong>Photo uploading is the next step.</strong><p>Save these details to create the vehicle, and we'll take you straight to the photo-upload section. Your listing can stay a draft until it's ready.</p></div>
  <span class="vehicle-photo-callout__status">Step 2 ready after save</span>
 </div>
 @endif
 @if($mode==='edit')
 <div class="experience-summary-banner"><span class="pill {{ $vehicle->is_published?'pill-available':'pill-draft' }}">{{ $vehicle->is_published?'Published':'Draft' }}</span><strong>{{ $vehicle->make }} {{ $vehicle->model }} · {{ $vehicle->year }}</strong><small>{{ $vehicle->stock_reference }} · {{ ucfirst($vehicle->status) }}</small></div>
 @endif
 <form id="vehicle-details" class="panel admin-edit" method="post" action="{{ $mode==='create'?route('manage.vehicles.store'):route('manage.vehicles.update',$vehicle) }}">
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
 <section id="vehicle-photos" class="panel image-panel vehicle-photo-section" aria-labelledby="vehicle-photo-title" tabindex="-1">
  <div class="panel-head vehicle-photo-section__head">
   <div><span class="admin-kicker">STEP 2 · LISTING PHOTOGRAPHS</span>
    <h2 id="vehicle-photo-title">Vehicle photos @if($mode==='edit')<small>({{ $vehicle->images->count() }}/20)</small>@endif</h2>
    <p>Add clear, approved photos of this exact vehicle. Choose the main image, adjust their order and preview the complete listing before publishing.</p>
   </div>
   @if($mode==='edit')<span class="vehicle-photo-section__badge">Photo upload available</span>
   @else<span class="vehicle-photo-section__badge vehicle-photo-section__badge--pending">Save details to enable</span>@endif
  </div>
  @if($mode==='edit')
   <div class="vehicle-photo-instructions"><strong>Select photos — Carfolk will optimize them automatically</strong><p>Choose up to 20 images. Large photos are resized and compressed on your device before upload. Check the previews and size reductions, then upload in safe small batches.</p></div>
  <form class="photo-upload" method="post" enctype="multipart/form-data" action="{{ route('manage.vehicles.photos.add',$vehicle) }}" data-photo-upload data-existing-photos="{{ $vehicle->images->count() }}" data-server-file-limit="{{ ini_get('upload_max_filesize') }}" data-server-post-limit="{{ ini_get('post_max_size') }}">@csrf
   <label>Choose photos (up to {{ max(0,20-$vehicle->images->count()) }} remaining; JPEG, PNG or WebP; originals up to 60 MB)<input type="file" name="photos[]" accept="image/jpeg,image/png,image/webp" multiple required aria-describedby="photo-selection-hint"></label>
   <label>Alternative text<input name="alt_text" maxlength="200" placeholder="e.g. Front view of vehicle"></label>
   <button type="submit" class="btn btn-primary">Upload selected photos</button>
   <p id="photo-selection-hint" class="bulk-upload-selection" data-upload-selection role="status" aria-live="polite">Select up to {{ max(0,20-$vehicle->images->count()) }} photos at once; images are optimized before upload.</p>
   <p class="bulk-optimization-status" data-optimization-status role="status" aria-live="polite" hidden>Preparing photo previews…</p>
   <div class="bulk-optimized-previews" data-optimized-previews aria-label="Optimized photo previews" hidden></div>
   <div class="bulk-upload-status" data-bulk-status hidden>
    <label for="bulk-photo-progress">Photo upload progress</label>
    <progress id="bulk-photo-progress" max="20" value="0" data-bulk-progress></progress>
    <p role="status" aria-live="polite" data-bulk-progress-text>Preparing photo upload…</p>
    <button type="button" class="btn btn-outline" data-bulk-retry hidden>Retry remaining photos</button>
   </div>
   <p class="flash flash-error photo-upload-error" data-upload-error tabindex="-1" role="alert" hidden></p>
   <noscript><p class="experience-footnote">JavaScript is required for resizing and more than five photos per selection. Without JavaScript, each original photo must be within this server's file and request limits; upload no more than five at a time.</p></noscript>
  </form>

   <div class="vehicle-gallery-heading">
    <div><h3 class="vehicle-photo-gallery-title">Manage photo gallery <small>({{ $vehicle->images->count() }} of 20)</small></h3><p>Drag images to reorder on desktop, or use Move up / Move down buttons on any device. Select the main photo used for buyers.</p></div>
    <a class="btn btn-primary" href="{{ route('manage.vehicles.preview',$vehicle) }}">Preview complete listing ↗</a>
   </div>
  <div class="vehicle-images vehicle-images--manage" data-gallery-sort>
   @forelse($vehicle->images as $image)
    <article class="vehicle-image vehicle-image--manage" data-image-id="{{ $image->id }}" draggable="true" aria-label="Vehicle photo {{ $loop->iteration }}">
     <div class="vehicle-image__visual">
      <img src="{{ \Illuminate\Support\Facades\Storage::disk($image->disk)->url($image->path) }}" alt="{{ $image->alt_text??('Photo '.$loop->iteration.' of this vehicle') }}">
      @if($image->is_primary)<span class="pill pill-available vehicle-image__primary">Main photo</span>@endif
      <span class="vehicle-image__position">Photo {{ $loop->iteration }}</span>
     </div>
     <div class="vehicle-image__controls">
      @unless($image->is_primary)
       <form method="post" action="{{ route('manage.vehicles.photos.primary',[$vehicle,$image]) }}">@csrf
        <button type="submit" class="btn btn-outline btn-small">Set as main photo</button>
       </form>
      @endunless
      <form method="post" action="{{ route('manage.vehicles.photos.move',[$vehicle,$image]) }}" class="vehicle-image__move">@csrf
       <button class="btn btn-outline btn-small" type="submit" name="direction" value="up" @disabled($loop->first)>↑ Move up</button>
       <button class="btn btn-outline btn-small" type="submit" name="direction" value="down" @disabled($loop->last)>↓ Move down</button>
      </form>
      <form method="post" action="{{ route('manage.vehicles.photos.caption',[$vehicle,$image]) }}" class="vehicle-image__caption">@csrf
       <label>Photo description<input type="text" name="alt_text" maxlength="200" value="{{ $image->alt_text }}" placeholder="e.g. Driver-side exterior"></label>
       <button class="btn btn-outline btn-small" type="submit">Save description</button>
      </form>
      <form method="post" enctype="multipart/form-data" action="{{ route('manage.vehicles.photos.replace',[$vehicle,$image]) }}" class="vehicle-image__replace" data-photo-replace data-server-file-limit="{{ ini_get('upload_max_filesize') }}">@csrf
       <label>Replace this photo<input type="file" name="photo" accept="image/jpeg,image/png,image/webp" required></label>
       <small data-replace-status>Choose a replacement. Large photos will be optimized first.</small>
       <button class="btn btn-outline btn-small" type="submit">Replace photo</button>
      </form>
      <form method="post" action="{{ route('manage.vehicles.photos.remove',[$vehicle,$image]) }}" onsubmit="return confirm('Remove this vehicle photo?');">@csrf @method('DELETE')
       <button type="submit" class="btn btn-small vehicle-image__remove">Remove photo</button>
      </form>
     </div>
    </article>
   @empty <p class="empty">No photos yet. Add an exterior, rear and interior view when available and approved for use.</p>
   @endforelse
  </div>
  @if($vehicle->images->count()>1)
   <form method="post" action="{{ route('manage.vehicles.photos.order',$vehicle) }}" data-photo-order class="vehicle-photo-order-form">@csrf
    <div data-order-inputs>@foreach($vehicle->images as $image)<input type="hidden" name="order[]" value="{{ $image->id }}">@endforeach</div>
    <button type="submit" class="btn btn-primary">Save photo order</button>
    <p>Dragging changes the order here; click Save photo order to make it permanent. Move up / Move down saves immediately.</p>
   </form>
  @endif

   <p class="experience-footnote">Accepted: JPEG, PNG or WebP, up to 20 photos per vehicle. Carfolk optimizes selected images toward 2560px maximum edge and approximately 1.5 MB where practical, then sends batches of five or fewer within server request limits (8 MB maximum per uploaded image). Originals remain on your device. Only upload images Carfolk is allowed to publish.</p>
  @else
   <div class="vehicle-photo-locked">
    <strong>Your photo upload workspace is ready.</strong>
    <p>First save the vehicle details above. You will automatically return to this section with the photo chooser enabled.</p>
    <a class="btn btn-outline" href="#vehicle-details">Complete vehicle details ↑</a>
   </div>
  @endif
 </section>
@endsection
