<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Models\VehicleEnquiry;
use App\Models\TestDriveRequest;
class DashboardController extends Controller {
 public function index(){
    return view('admin.dashboard',[
      'total'=>Vehicle::whereNull('archived_at')->count(),
      'published'=>Vehicle::published()->count(),
      'available'=>Vehicle::published()->where('status','available')->count(),
      'enquiries'=>VehicleEnquiry::where('status','new')->count(),
      'viewings'=>TestDriveRequest::where('status','requested')->count(),
    ]);
 }
}
