<?php
namespace Tests\Feature;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
class ImageAdministrationTest extends TestCase {
 use RefreshDatabase;
 public function test_admin_can_upload_and_remove_vehicle_photos(): void {
  Storage::fake('public');
  $admin=User::factory()->create(['is_admin'=>true,'is_active'=>true]);
  $vehicle=Vehicle::create(['stock_reference'=>'CF-400','slug'=>'photo-car','make'=>'Toyota','model'=>'Corolla','year'=>2018,'price_kes'=>1000000]);
  $this->actingAs($admin)->post('/manage/vehicles/'.$vehicle->id.'/photos',[
    'photo'=>UploadedFile::fake()->image('front.jpg',800,600),
    'alt_text'=>'Front view of example listing',
  ])->assertRedirect();
  $image=$vehicle->images()->first();
  $this->assertNotNull($image);
  Storage::disk('public')->assertExists($image->path);
  $this->actingAs($admin)->delete('/manage/vehicles/'.$vehicle->id.'/photos/'.$image->id)->assertRedirect();
  $this->assertDatabaseCount('vehicle_images',0);
  Storage::disk('public')->assertMissing($image->path);
 }
 public function test_unrelated_vehicle_photo_cannot_be_deleted(): void {
  Storage::fake('public');
  $admin=User::factory()->create(['is_admin'=>true,'is_active'=>true]);
  $a=Vehicle::create(['stock_reference'=>'CF-401','slug'=>'a','make'=>'A','model'=>'A','year'=>2020,'price_kes'=>5]);
  $b=Vehicle::create(['stock_reference'=>'CF-402','slug'=>'b','make'=>'B','model'=>'B','year'=>2020,'price_kes'=>5]);
  $this->actingAs($admin)->post('/manage/vehicles/'.$a->id.'/photos',['photo'=>UploadedFile::fake()->image('front.jpg')])->assertRedirect();
  $image=$a->images()->first();
  $this->actingAs($admin)->delete('/manage/vehicles/'.$b->id.'/photos/'.$image->id)->assertNotFound();
  $this->assertDatabaseCount('vehicle_images',1);
 }
}
