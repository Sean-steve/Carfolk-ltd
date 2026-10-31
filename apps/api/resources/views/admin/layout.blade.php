<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<meta name="theme-color" content="#101622">
<title>@yield('title','Dealership Administration') | Carfolk LTD</title>
<link rel="stylesheet" href="{{ asset('css/admin.css') }}">
</head>
<body>
<div class="admin-app">
  <aside class="admin-side">
    <a class="admin-logo" href="{{ route('manage.dashboard') }}"><img src="{{ asset('brand/carfolk-logo.webp') }}" alt="Carfolk LTD — Quality & Affordability"></a>
    <div class="admin-nav-label">DEALERSHIP WORKSPACE</div>
    <nav class="admin-nav" aria-label="Admin navigation">
      <a class="{{ request()->routeIs('manage.dashboard')?'active':'' }}" href="{{ route('manage.dashboard') }}">Overview</a>
      <a class="{{ request()->routeIs('manage.vehicles.*')?'active':'' }}" href="{{ route('manage.vehicles.index') }}">Vehicle Inventory</a>
      <a class="{{ request()->routeIs('manage.enquiries*')?'active':'' }}" href="{{ route('manage.enquiries') }}">Customer Enquiries</a>
      <a class="{{ request()->routeIs('manage.viewings*')?'active':'' }}" href="{{ route('manage.viewings') }}">Viewing Requests</a>
      <a class="{{ request()->routeIs('manage.settings*')?'active':'' }}" href="{{ route('manage.settings') }}">Dealership Settings</a>
    </nav>
    <div class="admin-side-footer"><p>Signed in as<br><strong>{{ auth()->user()->name }}</strong></p>
      <form action="{{ route('manage.logout') }}" method="post">@csrf <button type="submit">Sign out →</button></form>
    </div>
  </aside>
  <div class="admin-content">
    <header class="admin-top"><span>CARFOLK LTD / OPERATIONS</span><span class="admin-top-user">{{ auth()->user()->email }}</span></header>
    <main class="admin-main">
      <div class="admin-heading"><div><span class="admin-kicker">@yield('eyebrow','MANAGEMENT')</span><h1>@yield('title','Dashboard')</h1></div><div>@yield('actions')</div></div>
      @if(session('success'))<div class="flash flash-ok" role="status">{{ session('success') }}</div>@endif
      @if($errors->any())<div class="flash flash-error" role="alert"><strong>Please correct the following:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
      @yield('content')
    </main>
  </div>
</div>
</body>
</html>
