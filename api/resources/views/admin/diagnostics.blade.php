@extends('admin.layout')
@section('title','System Health')
@section('eyebrow','DEALERSHIP OPERATIONS')
@section('content')
 <section class="panel admin-edit">
  <div class="panel-head"><h2>Photo upload readiness</h2><p>These are the PHP limits seen by the website, which may differ from PHP in cPanel Terminal.</p></div>
  <div class="experience-stats experience-stats--settings">
   <div><span>MAXIMUM FILE UPLOAD</span><strong>{{ $checks['upload_max_filesize'] }}</strong><small>{{ $checks['upload_ready'] ? 'Allows a full 8 MB original' : 'Smaller optimized files may still upload' }}</small></div>
   <div><span>MAXIMUM POST REQUEST</span><strong>{{ $checks['post_max_size'] }}</strong><small>{{ $checks['batch_ready'] ? 'Allows five 8 MB files in one request' : 'Uploader splits batches to fit available capacity' }}</small></div>
  </div>
  <div class="experience-guidance" role="status">
   <strong>Optimized photo uploads: {{ $checks['optimized_ready'] && $checks['image_storage_ready'] ? 'PHP eligible — test a real upload' : 'PHP settings or storage need adjustment' }}</strong>
   <p>Carfolk resizes and compresses selected photographs in the browser to approximately 1.5 MB where practical, and splits uploads into smaller requests. The 8 MB maximum and 41 MB maximum-size batch requirements above only apply to uncompressed uploads.</p>
   <p>Web PHP limits alone cannot confirm Apache, ModSecurity or proxy restrictions. Perform live tests with one optimized photo and a complete selection before certifying this server.</p>
  </div>
  <h3>Protected storage checks</h3>
  <p>Photo storage: <strong>{{ $checks['image_storage_ready'] ? 'Writable' : 'Not writable' }}</strong></p>
  <p>Laravel error logs: <strong>{{ $checks['error_log_ready'] ? 'Writable' : 'Not writable' }}</strong></p>
  <p class="experience-footnote">This administrator-only screen does not expose server paths, credentials, file contents or client information.</p>
  <a class="btn btn-primary" href="{{ route('manage.vehicles.index') }}">Return to inventory</a>
 </section>
@endsection
