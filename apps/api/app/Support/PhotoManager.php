<?php
namespace App\Support;
use App\Models\AuditEvent;
use App\Models\Vehicle;
use App\Models\VehicleImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class PhotoManager {
 public static function add(Vehicle $vehicle, UploadedFile $photo, ?string $alt=null): VehicleImage {
   return self::addMany($vehicle,[$photo],$alt)[0];
 }

 /**
  * Store up to five photos at once (20 total per car). A failed batch rolls
  * back the database and removes all files already stored in this batch.
  *
  * @param UploadedFile[] $photos
  * @return VehicleImage[]
  */
 public static function addMany(Vehicle $vehicle,array $photos,?string $alt=null): array {
   if(count($photos)<1 || count($photos)>5)abort(422,'Choose between 1 and 5 photos per upload.');
   $paths=[];
   try {
     return DB::transaction(function () use ($vehicle,$photos,$alt,&$paths) {
       $locked=Vehicle::whereKey($vehicle->id)->lockForUpdate()->firstOrFail();
       abort_if($locked->archived_at,404);
       $existing=$locked->images()->count();
       if($existing+count($photos)>20)abort(422,'Maximum 20 photos per vehicle. Remove an existing photo before adding more.');
       $nextSort=(int)$locked->images()->max('sort_order')+1;
       $hasPrimary=$locked->images()->where('is_primary',true)->exists();
       $created=[];
       foreach($photos as $index=>$photo) {
         if(!$photo instanceof UploadedFile)abort(422,'Invalid photo file.');
         $path=$photo->store('vehicles/'.$locked->id,'public');
         if(!$path)throw new \RuntimeException('Unable to store vehicle photo.');
         $paths[]=$path;
         $image=$locked->images()->create([
           'disk'=>'public','path'=>$path,'alt_text'=>$alt,
           'is_primary'=>!$hasPrimary && $index===0,'sort_order'=>$nextSort+$index,
         ]);
         AuditEvent::record('vehicle.photo_added',$locked,['image_id'=>$image->id]);
         $created[]=$image;
       }
       return $created;
     });
   } catch(\Throwable $e) {
     foreach($paths as $path)Storage::disk('public')->delete($path);
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
