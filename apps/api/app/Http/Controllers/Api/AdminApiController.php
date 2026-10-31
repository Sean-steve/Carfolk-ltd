<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Http\Requests\SiteSettingRequest;
use App\Http\Requests\VehicleRequest;
use App\Models\AuditEvent;
use App\Models\SiteSetting;
use App\Models\TestDriveRequest;
use App\Models\Vehicle;
use App\Models\VehicleEnquiry;
use App\Models\VehicleImage;
use App\Support\VehicleData;
use App\Support\PhotoManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\File;
class AdminApiController extends Controller {
 public function summary(): JsonResponse {
    return response()->json(['data'=>[
      'total_vehicles'=>Vehicle::whereNull('archived_at')->count(),
      'published_vehicles'=>Vehicle::published()->count(),
      'available_vehicles'=>Vehicle::published()->where('status','available')->count(),
      'new_enquiries'=>VehicleEnquiry::where('status','new')->count(),
      'pending_viewings'=>TestDriveRequest::where('status','requested')->count(),
    ]]);
 }
 public function vehicles(): JsonResponse {
    $page=Vehicle::with('images')->whereNull('archived_at')->orderByDesc('created_at')->paginate(20);
    return response()->json(['data'=>$page->getCollection()->map(fn($v)=>array_merge(VehicleData::publicData($v),['is_published'=>$v->is_published,'stock_reference'=>$v->stock_reference])),'meta'=>['page'=>$page->currentPage(),'total'=>$page->total(),'last_page'=>$page->lastPage()]]);
 }
 public function store(VehicleRequest $request): JsonResponse {
    $v=DB::transaction(fn()=>VehicleData::create($request->safe()->merge(['is_featured'=>$request->boolean('is_featured'),'is_published'=>$request->boolean('is_published')])->all()));
    return response()->json(['data'=>VehicleData::publicData($v)],201);
 }
 public function update(VehicleRequest $request,Vehicle $vehicle): JsonResponse {
    abort_if($vehicle->archived_at,404);
    DB::transaction(fn()=>VehicleData::update($vehicle,$request->safe()->merge(['is_featured'=>$request->boolean('is_featured'),'is_published'=>$request->boolean('is_published')])->all()));
    return response()->json(['data'=>VehicleData::publicData($vehicle->fresh())]);
 }
 public function archive(Vehicle $vehicle): JsonResponse {
    abort_if($vehicle->archived_at,404);
    DB::transaction(fn()=>VehicleData::archive($vehicle));
    return response()->json(['message'=>'Vehicle archived; enquiry history retained.']);
 }
 public function upload(Request $request,Vehicle $vehicle): JsonResponse {
    abort_if($vehicle->archived_at,404);
    $data=$request->validate(['photo'=>['required',File::image()->max('8mb')],'alt_text'=>['nullable','string','max:200']]);
    $image=PhotoManager::add($vehicle,$data['photo'],$data['alt_text']??null);
    return response()->json(['data'=>['id'=>$image->id,'url'=>\Illuminate\Support\Facades\Storage::disk($image->disk)->url($image->path)]],201);
 }
 public function removePhoto(Vehicle $vehicle,VehicleImage $image): JsonResponse {
    PhotoManager::remove($vehicle,$image);
    return response()->json(['message'=>'Photo removed.']);
 }
 public function enquiries(): JsonResponse {
    return response()->json(VehicleEnquiry::with('vehicle:id,make,model,stock_reference')->latest()->paginate(20));
 }
 public function updateEnquiry(Request $request,VehicleEnquiry $enquiry): JsonResponse {
    $data=$request->validate(['status'=>['required',Rule::in(['new','contacted','closed'])]]);
    $enquiry->update($data);
    AuditEvent::record('enquiry.status_changed',$enquiry,['status'=>$data['status']]);
    return response()->json(['data'=>['id'=>$enquiry->id,'status'=>$enquiry->status]]);
 }
 public function viewings(): JsonResponse {
    return response()->json(TestDriveRequest::with('vehicle:id,make,model,stock_reference')->latest()->paginate(20));
 }
 public function updateViewing(Request $request,TestDriveRequest $viewing): JsonResponse {
    $data=$request->validate(['status'=>['required',Rule::in(['requested','confirmed','completed','cancelled'])]]);
    $viewing->update($data);
    AuditEvent::record('viewing.status_changed',$viewing,['status'=>$data['status']]);
    return response()->json(['data'=>['id'=>$viewing->id,'status'=>$viewing->status]]);
 }
 public function settings(): JsonResponse {return response()->json(['data'=>SiteSetting::publicDetails()]);}
 public function saveSettings(SiteSettingRequest $request): JsonResponse {
    $s=DB::transaction(function()use($request) {
       $s=SiteSetting::firstOrCreate(['id'=>1]);
       $s->update($request->validated());
       AuditEvent::record('site.settings_updated',$s);
       return $s;
    });
    return response()->json(['data'=>SiteSetting::publicDetails()]);
 }
}
