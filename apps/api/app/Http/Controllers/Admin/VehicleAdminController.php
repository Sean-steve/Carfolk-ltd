<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Http\Requests\VehicleRequest;
use App\Models\Vehicle;
use App\Models\VehicleImage;
use App\Support\VehicleData;
use App\Support\PhotoManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\File;
class VehicleAdminController extends Controller {
 public function index() {
    return view('admin.vehicles.index',['vehicles'=>Vehicle::whereNull('archived_at')->withCount('images')->latest()->paginate(15)]);
 }
 public function create() {return view('admin.vehicles.form',['vehicle'=>new Vehicle(),'mode'=>'create']);}
 public function store(VehicleRequest $request) {
    $vehicle=DB::transaction(fn()=>VehicleData::create($this->payload($request)));
    return redirect()->route('manage.vehicles.edit',$vehicle)->with('success','Vehicle created as '.($vehicle->is_published?'published':'draft').'.');
 }
 public function edit(Vehicle $vehicle) {
    abort_if($vehicle->archived_at,404);
    $vehicle->load('images');
    return view('admin.vehicles.form',['vehicle'=>$vehicle,'mode'=>'edit']);
 }
 public function update(VehicleRequest $request,Vehicle $vehicle) {
    abort_if($vehicle->archived_at,404);
    DB::transaction(fn()=>VehicleData::update($vehicle,$this->payload($request)));
    return back()->with('success','Vehicle details saved.');
 }
 public function archive(Vehicle $vehicle) {
    abort_if($vehicle->archived_at,404);
    DB::transaction(fn()=>VehicleData::archive($vehicle));
    return redirect()->route('manage.vehicles.index')->with('success','Vehicle archived. Enquiry history retained.');
 }
 public function upload(Request $request,Vehicle $vehicle) {
    abort_if($vehicle->archived_at,404);
    $data=$request->validate(['photo'=>['required',File::image()->max('8mb')],'alt_text'=>['nullable','string','max:200']]);
    PhotoManager::add($vehicle,$data['photo'],$data['alt_text']??null);
    return back()->with('success','Vehicle photo added.');
 }
 public function removePhoto(Vehicle $vehicle,VehicleImage $image) {
    PhotoManager::remove($vehicle,$image);
    return back()->with('success','Photo removed.');
 }
 private function payload(VehicleRequest $request): array {
    return array_merge($request->validated(),[
       'is_featured'=>$request->boolean('is_featured'),
       'is_published'=>$request->boolean('is_published'),
    ]);
 }
}
