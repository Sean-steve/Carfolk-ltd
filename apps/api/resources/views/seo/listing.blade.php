<!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>{{ $title }} for Sale | Carfolk LTD</title>
<meta name="description" content="{{ $description }}">
<meta name="robots" content="{{ $indexable ? 'index,follow' : 'noindex,nofollow' }}">
@if($canonical)<link rel="canonical" href="{{ $canonical }}">@endif
<meta property="og:type" content="product"><meta property="og:title" content="{{ $title }} | Carfolk LTD">
<meta property="og:description" content="{{ $description }}">
@if($canonical)<meta property="og:url" content="{{ $canonical }}">@endif
@if($image)<meta property="og:image" content="{{ $image }}">@endif
<script type="application/ld+json">@json($jsonLd)</script>
<link rel="stylesheet" href="{{ asset('css/listing.css') }}">
</head><body><header><div class="wrap top"><a class="brand" href="/"><img src="{{ asset('brand/carfolk-logo.webp') }}" alt="Carfolk LTD"></a><a href="/#/cars" class="nav">Browse all cars →</a></div></header>
<main class="wrap">
<nav aria-label="Breadcrumb"><a href="/">Home</a> / <a href="/#/cars">Cars for sale</a> / <span>{{ $title }}</span></nav>
<div class="hero"><div class="media">
@if($image)<img src="{{ $image }}" alt="{{ $vehicle->images->first()->alt_text ?: 'Vehicle photo: '.$title }}">@else<div class="placeholder">Vehicle photographs pending</div>@endif
</div><div class="details">
<p class="eyebrow">CARFOLK LTD · QUALITY &amp; AFFORDABILITY</p><h1>{{ $title }}</h1>
<p class="status">{{ ucfirst($vehicle->status) }} · {{ $vehicle->body_type ?: 'Vehicle' }}</p>
<strong class="price">KES {{ number_format($vehicle->price_kes) }}</strong>
<dl>
<div><dt>Year</dt><dd>{{ $vehicle->year }}</dd></div>
@if($vehicle->mileage_km !== null)<div><dt>Mileage</dt><dd>{{ number_format($vehicle->mileage_km) }} km</dd></div>@endif
@if($vehicle->fuel_type)<div><dt>Fuel</dt><dd>{{ $vehicle->fuel_type }}</dd></div>@endif
@if($vehicle->transmission)<div><dt>Transmission</dt><dd>{{ $vehicle->transmission }}</dd></div>@endif
@if($vehicle->condition)<div><dt>Condition</dt><dd>{{ $vehicle->condition }}</dd></div>@endif
@if($vehicle->location)<div><dt>Location</dt><dd>{{ $vehicle->location }}</dd></div>@endif
</dl>
<a class="primary" href="/#/cars/{{ rawurlencode($vehicle->slug) }}">View full details and enquire →</a>
<p class="fine">Verify final price, condition and availability directly with Carfolk before visiting. A listing does not constitute a sale agreement.</p>
</div></div>
<section class="description"><h2>Vehicle description</h2><p>{{ $vehicle->description ?: 'Contact Carfolk for more details about this vehicle.' }}</p>
@if(is_array($vehicle->features) && count($vehicle->features))<h2>Listed features</h2><ul>@foreach($vehicle->features as $feature)<li>{{ $feature }}</li>@endforeach</ul>@endif
</section></main><footer><div class="wrap"><span>© {{ date('Y') }} Carfolk LTD.</span> · <a href="/#/contact">Contact dealership</a></div></footer></body></html>
