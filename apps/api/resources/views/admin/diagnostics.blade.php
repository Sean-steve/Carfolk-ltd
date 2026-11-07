@extends('admin.layout')
@section('title','System Health')
@section('eyebrow','DEALERSHIP OPERATIONS')
@section('content')
 <section class="panel admin-edit">
  <div class="panel-head"><h2>Photo upload readiness</h2><p>These are the PHP limits seen by the website, which may differ from PHP in cPanel Terminal.</p></div>
  <div class="experience-stats experience-stats--settings">
   <div><span>MAXIMUM FILE UPLOAD</span><strong>{{ $checks['upload_max_filesize'] }}</strong><small>{{ $checks['upload_ready'] ? 'Ready for 8 MB photos' : 'Below required 8 MB per photo' }}</small></div>
   <div><span>MAXIMUM POST REQUEST</span><strong>{{ $checks['post_max_size'] }}</strong><small>{{ $checks['batch_ready'] ? 'Allows a five-photo batch' : 'Must allow at least 41 MB including overhead' }}</small></div>
  </div>
  <div class="experience-guidance" role="status">
   <strong>Upload configuration: {{ $checks['upload_ready'] && $checks['batch_ready'] && $checks['image_storage_ready'] ? 'Ready for live verification' : 'Needs hosting configuration' }}</strong>
   <p>Web PHP limits alone do not confirm Apache, ModSecurity or proxy request-size restrictions. Complete the five-photo live test to certify upload availability.</p>
  </div>
  <h3>Protected storage checks</h3>
  <p>Photo storage: <strong>{{ $checks['image_storage_ready'] ? 'Writable' : 'Not writable' }}</strong></p>
  <p>Laravel error logs: <strong>{{ $checks['error_log_ready'] ? 'Writable' : 'Not writable' }}</strong></p>
  <p class="experience-footnote">This administrator-only screen does not expose server paths, credentials, file contents or client information.</p>
  <a class="btn btn-primary" href="{{ route('manage.vehicles.index') }}">Return to inventory</a>
 </section>
@endsection
