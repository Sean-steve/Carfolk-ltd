<?php
namespace Tests\Feature;

use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MultiplePhotoUploadTest extends TestCase {
 use RefreshDatabase;

 private function car(): Vehicle {
   return Vehicle::create([
      'stock_reference'=>'MULTI-001','slug'=>'photo-upload-test','make'=>'Toyota',
      'model'=>'RAV4','year'=>2020,'price_kes'=>2500000,'status'=>'available',
      'is_published'=>true
   ]);
 }

 public function test_admin_can_upload_five_images_at_once_and_add_more():void {
   Storage::fake('public');
   $car=$this->car();
   $admin=User::factory()->create(['is_admin'=>true,'is_active'=>true]);
   $photos=[];
   foreach(range(1,5) as $i)$photos[]=UploadedFile::fake()->image('angle-'.$i.'.jpg',640,480);
   $this->actingAs($admin)->post('/manage/vehicles/'.$car->id.'/photos',[
      'photos'=>$photos,'alt_text'=>'Vehicle exterior'
   ])->assertRedirect()->assertSessionHas('success');
   $this->assertDatabaseCount('vehicle_images',5);
   $this->assertSame(1,$car->images()->where('is_primary',true)->count());
   $car->images()->get()->each(fn($i)=>Storage::disk('public')->assertExists($i->path));
   $this->getJson('/api/v1/vehicles/'.$car->slug)->assertOk()->assertJsonCount(5,'data.images');
   $this->actingAs($admin)->post('/manage/vehicles/'.$car->id.'/photos',[
     'photos'=>[UploadedFile::fake()->image('rear.png',500,400)]
   ])->assertRedirect();
   $this->assertDatabaseCount('vehicle_images',6);
   $this->assertSame(1,$car->images()->where('is_primary',true)->count());
 }

 public function test_images_must_fit_five_per_batch_and_twenty_total():void {
   Storage::fake('public');
   $car=$this->car();
   $admin=User::factory()->create(['is_admin'=>true,'is_active'=>true]);
   $photos=[];
   foreach(range(1,6) as $i)$photos[]=UploadedFile::fake()->image('car-'.$i.'.jpg');
   $this->actingAs($admin)->post('/manage/vehicles/'.$car->id.'/photos',['photos'=>$photos])
       ->assertSessionHasErrors('photos');
   $this->assertDatabaseCount('vehicle_images',0);

   foreach(range(1,4) as $batch) {
     $five=[];
     foreach(range(1,5) as $i)$five[]=UploadedFile::fake()->image('batch-'.$batch.'-'.$i.'.png');
     $this->actingAs($admin)->post('/manage/vehicles/'.$car->id.'/photos',['photos'=>$five])->assertRedirect();
   }
   $this->assertDatabaseCount('vehicle_images',20);
   $this->actingAs($admin)->post('/manage/vehicles/'.$car->id.'/photos',[
     'photos'=>[UploadedFile::fake()->image('extra.jpg')],
   ])->assertStatus(422);
   $this->assertDatabaseCount('vehicle_images',20);
 }

 public function test_api_accepts_legacy_single_image_and_new_photo_arrays():void {
   Storage::fake('public');
   $car=$this->car();
   $admin=User::factory()->create(['is_admin'=>true,'is_active'=>true]);
   $this->actingAs($admin)->withHeaders(['Accept'=>'application/json'])->post('/api/v1/admin/vehicles/'.$car->id.'/images',[
     'photo'=>UploadedFile::fake()->image('single.jpg'),
   ])->assertCreated()->assertJsonStructure(['data'=>['id','url']]);
   $this->actingAs($admin)->withHeaders(['Accept'=>'application/json'])->post('/api/v1/admin/vehicles/'.$car->id.'/images',[
     'photos'=>[UploadedFile::fake()->image('second.png'),UploadedFile::fake()->image('third.png')]
   ])->assertCreated()->assertJsonCount(2,'data');
   $this->assertDatabaseCount('vehicle_images',3);
 }
}
