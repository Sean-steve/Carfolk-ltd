<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<meta name="theme-color" content="#101622">
<title>{{ $title }} | Carfolk LTD</title>
<style>
:root{font-family:system-ui,-apple-system,Segoe UI,sans-serif;color:#182338;background:#f5f7fa}
*{box-sizing:border-box}body{margin:0;min-height:100vh;display:grid;place-items:center;padding:24px}
main{width:min(100%,520px);background:#fff;border:1px solid #e2e7ef;border-radius:18px;padding:clamp(24px,5vw,48px);box-shadow:0 18px 60px #18233812}
.brand{font-weight:900;letter-spacing:.08em;color:#b61c2d}.status{margin-top:30px;color:#b61c2d;font-size:13px;font-weight:800}
h1{font-size:clamp(28px,5vw,38px);line-height:1.15;margin:10px 0 15px}
p{line-height:1.7;color:#526176}a{display:inline-block;padding:12px 20px;border-radius:8px;background:#dc1f32;color:white;text-decoration:none;font-weight:700;margin:15px 0}
small{display:block;color:#778395;font-size:12px;word-break:break-word}
</style>
</head>
<body>
<main>
<div class="brand">CARFOLK LTD</div>
<div class="status">ERROR {{ $status }}</div>
<h1>{{ $title }}</h1>
<p>{{ $message }}</p>
<a href="{{ $isAdmin ? route('manage.vehicles.index') : url('/') }}">{{ $isAdmin ? 'Return to vehicle inventory' : 'Return to Carfolk' }}</a>
@if($reference)<small>Support reference: {{ $reference }}</small>@endif
</main>
</body>
</html>
