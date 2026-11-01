<!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex,nofollow"><title>Admin Login | Carfolk LTD</title><link rel="stylesheet" href="{{ asset('css/admin.css') }}"></head>
<body class="login-body"><main class="login-card">
 <img src="{{ asset('brand/carfolk-logo.webp') }}" alt="Carfolk LTD — Quality & Affordability" class="login-logo">
 <span class="admin-kicker">AUTHORIZED DEALERSHIP ACCESS</span>
 <h1>Welcome back.</h1>
 @if($errors->any())<div class="flash flash-error" role="alert">Invalid account or credentials. Please try again.</div>@endif
 <form method="post" action="{{ route('manage.login.submit') }}">@csrf
  <label for="email">Email address</label><input id="email" name="email" type="email" required autocomplete="username" value="{{ old('email') }}">
  <label for="password">Password</label><input id="password" name="password" type="password" required autocomplete="current-password">
  <button class="btn btn-primary" type="submit">Sign in to dashboard →</button>
 </form>
</main></body></html>
