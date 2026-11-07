@extends('admin.layout')
@section('title','Vehicle Listing Preview')
@section('eyebrow','BUYER VIEW · PRIVATE PREVIEW')
@section('actions')
 <a class="btn btn-outline" href="{{ route('manage.vehicles.edit',$vehicle) }}#vehicle-photos">← Back to vehicle editor</a>
@endsection
@section('content')
 <div class="listing-preview-warning" role="status">
  <div><strong>Private listing preview — not a published customer page</strong>
   <p>This shows the saved listing details and gallery in a buyer-style layout. Contact and viewing actions are disabled here. A draft remains invisible to the public showroom until you publish it.</p>
  </div>
  <span class="pill {{ $vehicle->is_published?'pill-available':'pill-draft' }}">{{ $vehicle->is_published?'Published listing':'Unpublished draft' }}</span>
 </div>
 <div class="listing-preview-topbar">
  <span class="admin-kicker">CARFOLK LTD · QUALITY & AFFORDABILITY</span>
  <a class="btn btn-primary" href="{{ route('manage.vehicles.edit',$vehicle) }}#vehicle-photos">Edit photos & details →</a>
 </div>
 <article class="buyer-preview" aria-label="Full saved vehicle listing preview">
  <header class="buyer-preview__header">
   <div>
    <p class="buyer-preview__eyebrow">CARFOLK SHOWROOM / VEHICLE DETAILS</p>
    <h2>{{ $vehicle->year }} {{ $vehicle->make }} {{ $vehicle->model }} @if($vehicle->trim)<span>{{ $vehicle->trim }}</span>@endif</h2>
    <p>{{ $vehicle->body_type ?: 'Vehicle' }} @if($vehicle->location) · {{ $vehicle->location }} @endif · Stock {{ $vehicle->stock_reference }}</p>
   </div>
   <div><span class="pill pill-{{ $vehicle->status }}">{{ ucfirst($vehicle->status) }}</span></div>
  </header>
  @php
   $photos=$vehicle->images;
   $cover=$photos->firstWhere('is_primary',true)??$photos->first();
   $specifications=[
    'Year'=>$vehicle->year,
    'Mileage'=>$vehicle->mileage_km!==null?number_format($vehicle->mileage_km).' km':null,
    'Fuel type'=>$vehicle->fuel_type,
    'Transmission'=>$vehicle->transmission,
    'Drivetrain'=>$vehicle->drivetrain,
    'Body type'=>$vehicle->body_type,
    'Condition'=>$vehicle->condition,
    'Exterior colour'=>$vehicle->colour,
    'Engine'=>$vehicle->engine_cc!==null?number_format($vehicle->engine_cc).' cc':null,
    'Location'=>$vehicle->location,
   ];
  @endphp
  <div class="buyer-preview__gallery">
   <div class="buyer-preview__cover">
    @if($cover)
     <img src="{{ \App\Support\VehicleImageVariants::urlFor($cover,'detail')??\Illuminate\Support\Facades\Storage::disk($cover->disk)->url($cover->path) }}" alt="{{ $cover->alt_text??($vehicle->make.' '.$vehicle->model.' main photograph') }}">
     <span class="buyer-preview__cover-label">Main photo · 1 of {{ $photos->count() }}</span>
    @else
     <div class="buyer-preview__empty">Photos have not been added yet. Upload approved photographs before publishing.</div>
    @endif
   </div>
   @if($photos->isNotEmpty())
    <div class="buyer-preview__thumbs" aria-label="Vehicle gallery, {{ $photos->count() }} photos">
     @foreach($photos as $photo)
      <figure @class(['buyer-preview__thumb','buyer-preview__thumb--main'=>$photo->id===$cover->id])>
       <img src="{{ \App\Support\VehicleImageVariants::urlFor($photo,'thumb')??\Illuminate\Support\Facades\Storage::disk($photo->disk)->url($photo->path) }}" alt="{{ $photo->alt_text??('Photo '.$loop->iteration.' of '.$vehicle->make.' '.$vehicle->model) }}" loading="lazy">
       <figcaption>{{ $loop->iteration }}@if($photo->is_primary) · Main @endif</figcaption>
      </figure>
     @endforeach
    </div>
   @endif
  </div>
  <div class="buyer-preview__layout">
   <div class="buyer-preview__details">
    <section><span class="buyer-preview__eyebrow">OVERVIEW</span><h3>About this vehicle</h3>
     <p class="buyer-preview__description">{{ $vehicle->description ?: 'Contact Carfolk for a complete description, verified inspection information and current availability.' }}</p>
    </section>
    <section><span class="buyer-preview__eyebrow">SPECIFICATIONS</span><h3>Vehicle details</h3>
     <div class="buyer-preview__specs">
      @foreach($specifications as $label=>$value)
       @if($value!==null && $value!=='')
        <div><small>{{ $label }}</small><strong>{{ $value }}</strong></div>
       @endif
      @endforeach
     </div>
    </section>
    @if(count($vehicle->features??[]))
     <section><span class="buyer-preview__eyebrow">FEATURES</span><h3>Listed highlights</h3>
      <div class="buyer-preview__features">@foreach($vehicle->features as $feature)<span>✓ {{ $feature }}</span>@endforeach</div>
     </section>
    @endif
    <section><span class="buyer-preview__eyebrow">BEFORE YOU VISIT</span><h3>Confirm the essentials with Carfolk</h3>
     <p>Ask about availability, documentation and inspection history before arranging a showroom visit.</p>
    </section>
   </div>
   <aside class="buyer-preview__aside" aria-label="Vehicle price and buyer actions">
    <small>LISTED PRICE</small><strong>KES {{ number_format($vehicle->price_kes) }}</strong>
    <p>{{ $vehicle->status==='available'?'Available · confirm with the dealership before travelling':($vehicle->status==='reserved'?'Reserved · ask about availability':'Vehicle has been marked sold.') }}</p>
    <button type="button" disabled>Request a viewing · Preview only</button>
    <button type="button" disabled>Enquire about this car · Preview only</button>
    <p class="buyer-preview__disclaimer">This is an internal preview. Enquiries, financing and comparison actions are intentionally disabled.</p>
   </aside>
  </div>
 </article>
 <div class="listing-preview-bottom"><a class="btn btn-outline" href="{{ route('manage.vehicles.edit',$vehicle) }}#vehicle-photos">Change photos</a><a class="btn btn-primary" href="{{ route('manage.vehicles.edit',$vehicle) }}#vehicle-details">Edit listing details</a></div>
@endsection
