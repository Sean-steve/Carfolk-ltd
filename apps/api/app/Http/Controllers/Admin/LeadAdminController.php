<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\AuditEvent;
use App\Models\VehicleEnquiry;
use App\Models\TestDriveRequest;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
class LeadAdminController extends Controller {
 public function enquiries(Request $request) {
   $validated=$request->validate(['status'=>['sometimes','nullable','in:new,contacted,closed']]);
   $status=$validated['status']??'';
   $query=VehicleEnquiry::with('vehicle');
   if($status!=='')$query->where('status',$status);
   return view('admin.leads.enquiries',[
      'leads'=>$query->latest()->paginate(20)->withQueryString(),
      'statusFilter'=>$status,
      'leadCounts'=>[
          'total'=>VehicleEnquiry::count(),
          'new'=>VehicleEnquiry::where('status','new')->count(),
          'contacted'=>VehicleEnquiry::where('status','contacted')->count(),
          'closed'=>VehicleEnquiry::where('status','closed')->count(),
      ],
   ]);
 }
 public function updateEnquiry(Request $request,VehicleEnquiry $enquiry) {
   $data=$request->validate(['status'=>['required',Rule::in(['new','contacted','closed'])]]);
   $enquiry->update($data);
   AuditEvent::record('enquiry.status_changed',$enquiry,['status'=>$data['status']]);
   return back()->with('success','Enquiry status updated.');
 }
 public function viewings(Request $request) {
   $validated=$request->validate(['status'=>['sometimes','nullable','in:requested,confirmed,completed,cancelled']]);
   $status=$validated['status']??'';
   $query=TestDriveRequest::with('vehicle');
   if($status!=='')$query->where('status',$status);
   return view('admin.leads.viewings',[
      'viewings'=>$query->orderByDesc('created_at')->paginate(20)->withQueryString(),
      'statusFilter'=>$status,
      'viewingCounts'=>[
          'total'=>TestDriveRequest::count(),
          'requested'=>TestDriveRequest::where('status','requested')->count(),
          'confirmed'=>TestDriveRequest::where('status','confirmed')->count(),
          'completed'=>TestDriveRequest::where('status','completed')->count(),
          'cancelled'=>TestDriveRequest::where('status','cancelled')->count(),
      ],
   ]);
 }
 public function updateViewing(Request $request,TestDriveRequest $viewing) {
   $data=$request->validate(['status'=>['required',Rule::in(['requested','confirmed','completed','cancelled'])]]);
   $viewing->update($data);
   AuditEvent::record('viewing.status_changed',$viewing,['status'=>$data['status']]);
   return back()->with('success','Viewing status updated.');
 }
}
