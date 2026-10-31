<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Http\Requests\SiteSettingRequest;
use App\Models\AuditEvent;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\DB;
class SiteAdminController extends Controller {
 public function edit() {
   $site=SiteSetting::first()??new SiteSetting(['name'=>'Carfolk LTD','tagline'=>'Quality & Affordability']);
   return view('admin.settings',['site'=>$site]);
 }
 public function save(SiteSettingRequest $request) {
   DB::transaction(function()use($request){
      $site=SiteSetting::firstOrCreate(['id'=>1]);
      $site->update($request->validated());
      AuditEvent::record('site.settings_updated',$site);
   });
   return back()->with('success','Public dealership details updated.');
 }
}
