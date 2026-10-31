<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Models\SiteSetting;
use App\Support\VehicleData;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
class ShowroomController extends Controller {
 public function index(Request $request): JsonResponse {
   $params=$request->validate([
      'q'=>['nullable','string','max:100'],'make'=>['nullable','string','max:80'],
      'model'=>['nullable','string','max:100'],'body_type'=>['nullable','string','max:50'],
      'fuel_type'=>['nullable','string','max:40'],'transmission'=>['nullable','string','max:40'],
      'condition'=>['nullable','string','max:50'],'status'=>['nullable','in:available,reserved,sold'],
      'featured'=>['nullable','boolean'],
      'price_min'=>['nullable','integer','min:0'],'price_max'=>['nullable','integer','min:0'],
      'year_min'=>['nullable','integer','between:1950,2035'],'year_max'=>['nullable','integer','between:1950,2035'],
      'sort'=>['nullable','in:recent,price-asc,price-desc,year-desc,mileage-asc'],'page'=>['nullable','integer','min:1'],
   ]);
   $query=Vehicle::published()->with('images');
   foreach(['make','model','body_type','fuel_type','transmission','condition','status'] as $column) {
     if(!empty($params[$column]))$query->where($column,$params[$column]);
   }
   if(!empty($params['featured']))$query->where('is_featured',true);
   if(!empty($params['q'])) {
     $term='%'.str_replace(['%','_'],['\\%','\\_'],$params['q']).'%';
     $query->where(fn($q)=>$q->where('make','ilike',$term)->orWhere('model','ilike',$term)->orWhere('description','ilike',$term));
   }
   foreach(['price_min'=>['price_kes','>='],'price_max'=>['price_kes','<='],'year_min'=>['year','>='],'year_max'=>['year','<=']] as $input=>$rule) {
     if(isset($params[$input]))$query->where($rule[0],$rule[1],$params[$input]);
   }
   match($params['sort']??'recent') {
     'price-asc'=>$query->orderBy('price_kes'),
     'price-desc'=>$query->orderByDesc('price_kes'),
     'year-desc'=>$query->orderByDesc('year'),
     'mileage-asc'=>$query->orderBy('mileage_km'),
     default=>$query->orderByDesc('is_featured')->orderByDesc('created_at'),
   };
   $page=$query->orderBy('id')->paginate(12);
   return response()->json(['data'=>$page->getCollection()->map(fn($v)=>VehicleData::publicData($v)),'meta'=>[
     'current_page'=>$page->currentPage(),'last_page'=>$page->lastPage(),'per_page'=>$page->perPage(),'total'=>$page->total(),
   ]]);
 }
 public function show(string $slug): JsonResponse {
   $vehicle=Vehicle::published()->with('images')->where('slug',$slug)->firstOrFail();
   return response()->json(['data'=>VehicleData::publicData($vehicle)]);
 }
 public function options(): JsonResponse {
   $query=Vehicle::published();
   $pairs=(clone $query)->select('make','model')->distinct()->orderBy('make')->orderBy('model')->get();
   return response()->json(['data'=>[
     'makes'=>$pairs->pluck('make')->unique()->values(),
     'models_by_make'=>$pairs->groupBy('make')->map(fn($items)=>$items->pluck('model')->unique()->values()),
     'bodies'=>(clone $query)->whereNotNull('body_type')->distinct()->orderBy('body_type')->pluck('body_type'),
     'fuel_types'=>(clone $query)->whereNotNull('fuel_type')->distinct()->orderBy('fuel_type')->pluck('fuel_type'),
     'transmissions'=>(clone $query)->whereNotNull('transmission')->distinct()->orderBy('transmission')->pluck('transmission'),
     'conditions'=>(clone $query)->whereNotNull('condition')->distinct()->orderBy('condition')->pluck('condition'),
   ]]);
 }
 public function compare(Request $request): JsonResponse {
   $params=$request->validate(['ids'=>['required','array','min:1','max:3'],'ids.*'=>['required','uuid','distinct']]);
   $vehicles=Vehicle::published()->with('images')->whereIn('id',$params['ids'])->get()->keyBy('id');
   return response()->json(['data'=>collect($params['ids'])->map(fn($id)=>$vehicles->get($id))
     ->filter()->map(fn($v)=>VehicleData::publicData($v))->values()]);
 }
 public function site(): JsonResponse {
   return response()->json(['data'=>array_merge(SiteSetting::publicDetails(),[
     'leads_enabled'=>\App\Support\LeadAcceptance::enabled(),
     'privacy_url'=>filter_var(config('carfolk.privacy_url'),FILTER_VALIDATE_URL)?config('carfolk.privacy_url'):null,
   ])]);
 }
}
