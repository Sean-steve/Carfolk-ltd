<?php
namespace App\Support;
use App\Models\AuditEvent;
use App\Models\Vehicle;
use App\Models\VehicleImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
class PhotoManager {
 public static function add(Vehicle $vehicle, UploadedFile $photo,?string $alt=null): VehicleImage {
    if($vehicle->images()->count()>=20)abort(422,'Maximum 20 photos per vehicle.');
    $path=$photo->store('vehicles/'.$vehicle->id,'public');
    try {
       return DB::transaction(function()use($vehicle,$path,$alt) {
          $hasPrimary=$vehicle->images()->where('is_primary',true)->exists();
          $image=$vehicle->images()->create([
            'disk'=>'public','path'=>$path,'alt_text'=>$alt,
            'is_primary'=>!$hasPrimary,'sort_order'=>(int)$vehicle->images()->max('sort_order')+1,
          ]);
          AuditEvent::record('vehicle.photo_added',$vehicle,['image_id'=>$image->id]);
          return $image;
       });
    } catch(\Throwable $e) {
       Storage::disk('public')->delete($path);
       throw $e;
    }
 }
 public static function remove(Vehicle $vehicle,VehicleImage $image): void {
    abort_unless($image->vehicle_id===$vehicle->id,404);
    DB::transaction(function()use($vehicle,$image) {
       $wasPrimary=$image->is_primary;
       $image->delete();
       if($wasPrimary) $vehicle->images()->orderBy('sort_order')->first()?->update(['is_primary'=>true]);
       AuditEvent::record('vehicle.photo_removed',$vehicle,['image_id'=>$image->id]);
    });
    Storage::disk($image->disk)->delete($image->path);
 }
}
