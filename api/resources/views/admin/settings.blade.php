@extends('admin.layout')
@section('title','Dealership Settings')
@section('eyebrow','PUBLIC BUSINESS PROFILE')
@section('content')
<p class="admin-intro">Keep the information customers see on the website accurate and consistent with Carfolk's approved business records.</p>
@php
  $details=['phone'=>$site->phone,'whatsapp'=>$site->whatsapp,'email'=>$site->email,'address'=>$site->address,'opening_hours'=>$site->opening_hours,'about'=>$site->about];
  $complete=collect($details)->filter(fn($value)=>filled($value))->count();
@endphp
<div class="experience-stats experience-stats--settings"><div><span>BUSINESS PROFILE</span><strong>{{ $complete }}/{{ count($details) }}</strong><small>Key details configured</small></div>
<div><span>CONTACT CHANNELS</span><strong>{{ collect(['phone','whatsapp','email'])->filter(fn($k)=>filled($details[$k]))->count() }}/3</strong><small>Phone, WhatsApp and email</small></div></div>
<div class="experience-guidance"><strong>Before publishing changes:</strong> Confirm the official phone, WhatsApp, email, location and opening hours with Carfolk. A filled field is not proof of verification.</div>
<div class="panel"><div class="panel-head"><h2>Business profile</h2></div>
<form method="post" action="{{ route('manage.settings.update') }}" class="admin-edit">@csrf @method('PATCH')
 <div class="experience-form-heading"><h3>Brand identity</h3><p>This name and tagline appear on the public website.</p></div>
 <div class="fields-grid">
  <label>Business name *<input name="name" required maxlength="130" value="{{ old('name',$site->name) }}"></label>
  <label>Tagline *<input name="tagline" required maxlength="150" value="{{ old('tagline',$site->tagline) }}"></label>
  <div class="experience-section-label"><h3>Contact channels</h3><p>Enter only Carfolk-approved contact details. Kenyan numbers must use +254 format.</p></div>
  <label>Phone (international Kenyan format)<input name="phone" maxlength="30" value="{{ old('phone',$site->phone) }}" placeholder="+2547XXXXXXXX"></label>
  <label>WhatsApp (international Kenyan format)<input name="whatsapp" maxlength="30" value="{{ old('whatsapp',$site->whatsapp) }}" placeholder="+2547XXXXXXXX"></label>
  <label>Official email<input name="email" type="email" maxlength="254" value="{{ old('email',$site->email) }}"></label>
  <div class="experience-section-label"><h3>Showroom information</h3><p>Let customers know when and where to arrange a visit.</p></div>
  <label>Opening hours<input name="opening_hours" maxlength="250" value="{{ old('opening_hours',$site->opening_hours) }}" placeholder="e.g. Mon–Sat, 09:00–18:00"></label>
  <label class="full">Street address / location<input name="address" maxlength="300" value="{{ old('address',$site->address) }}"></label>
  <div class="experience-section-label"><h3>Company story</h3><p>Use Carfolk-approved information; avoid unsupported guarantees or warranty claims.</p></div>
  <label class="full">About Carfolk<textarea name="about" rows="6">{{ old('about',$site->about) }}</textarea></label>
 </div><div class="form-actions"><button class="btn btn-primary" type="submit">Save verified information →</button></div>
</form></div>
<div class="experience-guidance"><strong>Website preview:</strong> Approved information is exposed in the public dealership profile after saving. If a field is empty, the website must not invent a phone number, map location or operating hours.</div>
@endsection
