<?php
namespace App\Support;
use App\Models\Vehicle;
use App\Models\AuditEvent;
use Illuminate\Support\Str;
class VehicleData {
 public static function create(array $attributes): Vehicle {
    $attributes['slug']=Str::slug($attributes['make'].'-'.$attributes['model'].'-'.$attributes['year'].'-'.$attributes['stock_reference']);
    $attributes['features']=$attributes['features']??[];
    $vehicle=Vehicle::create($attributes);
    AuditEvent::record('vehicle.created',$vehicle);
    return $vehicle;
 }
 public static function update(Vehicle $vehicle,array $attributes): Vehicle {
    unset($attributes['slug']);
    $vehicle->update($attributes);
    AuditEvent::record('vehicle.updated',$vehicle);
    return $vehicle;
 }
 public static function archive(Vehicle $vehicle): void {
    $vehicle->update(['is_published'=>false,'archived_at'=>now()]);
    AuditEvent::record('vehicle.archived',$vehicle);
 }
 public static function publicData(Vehicle $vehicle): array {
    $vehicle->loadMissing('images');
    return [
      'id'=>$vehicle->id,'slug'=>$vehicle->slug,'make'=>$vehicle->make,'model'=>$vehicle->model,
      'trim'=>$vehicle->trim,'year'=>$vehicle->year,'price_kes'=>$vehicle->price_kes,
      'mileage_km'=>$vehicle->mileage_km,'engine_cc'=>$vehicle->engine_cc,
      'fuel_type'=>$vehicle->fuel_type,'transmission'=>$vehicle->transmission,
      'drivetrain'=>$vehicle->drivetrain,'body_type'=>$vehicle->body_type,
      'condition'=>$vehicle->condition,'colour'=>$vehicle->colour,'location'=>$vehicle->location,
      'description'=>$vehicle->description,'features'=>$vehicle->features??[],
      'status'=>$vehicle->status,'is_featured'=>$vehicle->is_featured,
      'published_at'=>$vehicle->created_at?->toIso8601String(),
      'images'=>$vehicle->images->map(fn($i)=>[
         'id'=>$i->id,'url'=>\Illuminate\Support\Facades\Storage::disk($i->disk)->url($i->path),
         'alt_text'=>$i->alt_text,'is_primary'=>$i->is_primary,'sort_order'=>$i->sort_order,
      ])->values()->all(),
    ];
 }
}
