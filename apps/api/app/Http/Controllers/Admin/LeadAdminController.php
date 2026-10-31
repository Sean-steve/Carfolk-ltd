<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\AuditEvent;
use App\Models\VehicleEnquiry;
use App\Models\TestDriveRequest;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
class LeadAdminController extends Controller {
 public function enquiries() {
   return view('admin.leads.enquiries',['leads'=>VehicleEnquiry::with('vehicle')->latest()->paginate(20)]);
 }
 public function updateEnquiry(Request $request,VehicleEnquiry $enquiry) {
   $data=$request->validate(['status'=>['required',Rule::in(['new','contacted','closed'])]]);
   $enquiry->update($data);
   AuditEvent::record('enquiry.status_changed',$enquiry,['status'=>$data['status']]);
   return back()->with('success','Enquiry status updated.');
 }
 public function viewings() {
   return view('admin.leads.viewings',['viewings'=>TestDriveRequest::with('vehicle')->latest()->paginate(20)]);
 }
 public function updateViewing(Request $request,TestDriveRequest $viewing) {
   $data=$request->validate(['status'=>['required',Rule::in(['requested','confirmed','completed','cancelled'])]]);
   $viewing->update($data);
   AuditEvent::record('viewing.status_changed',$viewing,['status'=>$data['status']]);
   return back()->with('success','Viewing status updated.');
 }
}
