@extends('admin.layout')
@section('title','Dealership Settings')
@section('eyebrow','PUBLIC BUSINESS PROFILE')
@section('content')
<div class="panel"><div class="panel-head"><h2>Verified contact and company details</h2><p>Only publish information that Carfolk LTD has confirmed. These details are exposed by the public website API.</p></div>
<form method="post" action="{{ route('manage.settings.update') }}" class="admin-edit">@csrf @method('PATCH')
 <div class="fields-grid">
  <label>Business name *<input name="name" required maxlength="130" value="{{ old('name',$site->name) }}"></label>
  <label>Tagline *<input name="tagline" required maxlength="150" value="{{ old('tagline',$site->tagline) }}"></label>
  <label>Phone (international Kenyan format)<input name="phone" maxlength="30" value="{{ old('phone',$site->phone) }}" placeholder="+2547XXXXXXXX"></label>
  <label>WhatsApp (international Kenyan format)<input name="whatsapp" maxlength="30" value="{{ old('whatsapp',$site->whatsapp) }}" placeholder="+2547XXXXXXXX"></label>
  <label>Official email<input name="email" type="email" maxlength="254" value="{{ old('email',$site->email) }}"></label>
  <label>Opening hours<input name="opening_hours" maxlength="250" value="{{ old('opening_hours',$site->opening_hours) }}" placeholder="e.g. Mon–Sat, 09:00–18:00"></label>
  <label class="full">Street address / location<input name="address" maxlength="300" value="{{ old('address',$site->address) }}"></label>
  <label class="full">About Carfolk<textarea name="about" rows="6">{{ old('about',$site->about) }}</textarea></label>
 </div><div class="form-actions"><button class="btn btn-primary" type="submit">Save verified information →</button></div>
</form></div>
@endsection
