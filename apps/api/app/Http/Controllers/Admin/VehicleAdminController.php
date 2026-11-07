<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Http\Requests\VehicleRequest;
use App\Models\Vehicle;
use App\Models\VehicleImage;
use App\Support\VehicleData;
use App\Support\PhotoManager;
use App\Support\VehiclePhotoEditor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\File;
class VehicleAdminController extends Controller {
 public function index(Request $request) {
    $filters=$request->validate([
        'q'=>['sometimes','nullable','string','max:80'],
        'status'=>['sometimes','nullable','in:available,reserved,sold'],
        'visibility'=>['sometimes','nullable','in:published,draft'],
    ]);
    $search=trim((string)($filters['q']??''));
    $base=Vehicle::query()->whereNull('archived_at');
    $vehicles=(clone $base)->withCount('images');
    if($search!==''){
        $vehicles->where(function($query)use($search){
            $term='%'.str_replace(['%','_'],['\\%','\\_'],$search).'%';
            $query->where('stock_reference','ilike',$term)
                ->orWhere('make','ilike',$term)
                ->orWhere('model','ilike',$term);
        });
    }
    if(!empty($filters['status']))$vehicles->where('status',$filters['status']);
    if(($filters['visibility']??'')==='published')$vehicles->where('is_published',true);
    if(($filters['visibility']??'')==='draft')$vehicles->where('is_published',false);
    return view('admin.vehicles.index',[
        'vehicles'=>$vehicles->latest()->paginate(15)->withQueryString(),
        'filters'=>['q'=>$search,'status'=>$filters['status']??'','visibility'=>$filters['visibility']??''],
        'inventorySummary'=>[
            'total'=>(clone $base)->count(),
            'published'=>(clone $base)->where('is_published',true)->count(),
            'drafts'=>(clone $base)->where('is_published',false)->count(),
            'available'=>(clone $base)->where('is_published',true)->where('status','available')->count(),
        ],
    ]);
 }
 public function create() {return view('admin.vehicles.form',['vehicle'=>new Vehicle(),'mode'=>'create']);}
 public function store(VehicleRequest $request) {
    $vehicle=DB::transaction(fn()=>VehicleData::create($this->payload($request)));
    // The next step is photo upload, not a return to the top of a long details form.
    return redirect()->to(route('manage.vehicles.edit',$vehicle).'#vehicle-photos')
        ->with('success','Vehicle created as '.($vehicle->is_published?'published':'draft').'. Next: add vehicle photos.');
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
    $multi=$request->hasFile('photos');
    $data=$request->validate($multi ? [
      'photos'=>['required','array','min:1','max:5'],
      'photos.*'=>['required',File::types(['jpg','jpeg','png','webp'])->max('8mb')],
      'alt_text'=>['nullable','string','max:200'],
      'client_batch_id'=>['sometimes','nullable','uuid'],
    ] : [
      'photo'=>['required',File::types(['jpg','jpeg','png','webp'])->max('8mb')],
      'alt_text'=>['nullable','string','max:200'],
      'client_batch_id'=>['sometimes','nullable','uuid'],
    ]);
    $photos=$multi?$data['photos']:[$data['photo']];
    $added=PhotoManager::addMany($vehicle,$photos,$data['alt_text']??null,$data['client_batch_id']??null);
    // Progressive enhancement: ordinary form posts still redirect; bulk JS
    // requests opt into JSON so the browser can show progress per batch.
    if($request->expectsJson()) {
       $total=$vehicle->images()->count();
       return response()->json([
         'ok'=>true,
         'uploaded'=>count($added),
         'total'=>$total,
         'remaining'=>max(0,20-$total),
         'client_batch_id'=>$data['client_batch_id']??null,
       ],201)->header('Cache-Control','no-store');
    }
    return back()->with('success',count($added).' vehicle '.(count($added)===1?'photo':'photos').' uploaded.');
 }
 public function removePhoto(Vehicle $vehicle,VehicleImage $image) {
    PhotoManager::remove($vehicle,$image);
    return back()->with('success','Photo removed.');
 }
 public function preview(Vehicle $vehicle) {
    abort_if($vehicle->archived_at,404);
    $vehicle->load('images');
    return response()->view('admin.vehicles.preview', ['vehicle'=>$vehicle])
        ->header('Cache-Control','private, no-store, max-age=0')
        ->header('X-Robots-Tag','noindex, nofollow, noarchive');
 }
 public function primaryPhoto(Vehicle $vehicle,VehicleImage $image) {
    VehiclePhotoEditor::primary($vehicle,$image);
    return redirect()->to(route('manage.vehicles.edit',$vehicle).'#vehicle-photos')->with('success','Primary vehicle photo updated.');
 }
 public function orderPhotos(Request $request,Vehicle $vehicle) {
    $data=$request->validate(['order'=>['required','array','min:1','max:20'],'order.*'=>['required','uuid','distinct']]);
    VehiclePhotoEditor::reorder($vehicle,$data['order']);
    return redirect()->to(route('manage.vehicles.edit',$vehicle).'#vehicle-photos')->with('success','Photo display order updated.');
 }
 public function movePhoto(Request $request,Vehicle $vehicle,VehicleImage $image) {
    $data=$request->validate(['direction'=>['required','in:up,down']]);
    VehiclePhotoEditor::move($vehicle,$image,$data['direction']);
    return redirect()->to(route('manage.vehicles.edit',$vehicle).'#vehicle-photos')->with('success','Photo moved.');
 }
 public function captionPhoto(Request $request,Vehicle $vehicle,VehicleImage $image) {
    $data=$request->validate(['alt_text'=>['nullable','string','max:200']]);
    VehiclePhotoEditor::updateAlt($vehicle,$image,$data['alt_text']??null);
    return redirect()->to(route('manage.vehicles.edit',$vehicle).'#vehicle-photos')->with('success','Photo description updated.');
 }
 public function replacePhoto(Request $request,Vehicle $vehicle,VehicleImage $image) {
    $data=$request->validate(['photo'=>['required',File::types(['jpg','jpeg','png','webp'])->max('8mb')]]);
    VehiclePhotoEditor::replace($vehicle,$image,$data['photo']);
    return redirect()->to(route('manage.vehicles.edit',$vehicle).'#vehicle-photos')->with('success','Vehicle photo replaced; its position and primary setting were preserved.');
 }
 private function payload(VehicleRequest $request): array {
    return array_merge($request->validated(),[
       'is_featured'=>$request->boolean('is_featured'),
       'is_published'=>$request->boolean('is_published'),
    ]);
 }
}
