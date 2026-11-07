<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleImage;
use App\Support\VehicleImageVariants;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class VehicleImageVariantsTest extends TestCase
{
    use RefreshDatabase;

    private function car(): Vehicle
    {
        return Vehicle::create([
            'stock_reference'=>'IMG-FAST-001','slug'=>'fast-gallery-example',
            'make'=>'Nissan','model'=>'Kicks','year'=>2022,
            'price_kes'=>2350000,'status'=>'available','is_published'=>true,
        ]);
    }

    private function photo(Vehicle $car): VehicleImage
    {
        $path=UploadedFile::fake()->image('large-photo.jpg',2400,1600)->store('vehicles/'.$car->id,'public');
        return $car->images()->create([
            'disk'=>'public','path'=>$path,'sort_order'=>1,
            'is_primary'=>true,'alt_text'=>'Front view',
        ]);
    }

    private function requireWebp(): void
    {
        if (!function_exists('imagewebp')) $this->markTestSkipped('GD WebP encoder not available.');
    }

    public function test_backfill_produces_small_static_images_without_modifying_original(): void
    {
        $this->requireWebp();
        Storage::fake('public');
        $car=$this->car();
        $image=$this->photo($car);
        $disk=Storage::disk('public');
        $before=hash_file('sha256',$disk->path($image->path));
        $originalSize=$disk->size($image->path);
        $this->assertNull(VehicleImageVariants::urlFor($image,'thumb'));

        $this->artisan('carfolk:images:build',['--dry-run'=>true])->assertSuccessful();
        $this->assertNull(VehicleImageVariants::urlFor($image,'thumb'));

        $this->artisan('carfolk:images:build')->assertSuccessful();
        foreach(['thumb'=>320,'card'=>640,'detail'=>1600] as $variant=>$maxWidth){
            $path=VehicleImageVariants::pathFor($image->path,$variant);
            $disk->assertExists($path);
            $dimensions=getimagesize($disk->path($path));
            $this->assertLessThanOrEqual($maxWidth,max($dimensions[0],$dimensions[1]));
            $this->assertSame(IMAGETYPE_WEBP,$dimensions[2]);
            $this->assertNotNull(VehicleImageVariants::urlFor($image,$variant));
        }
        $this->assertSame($originalSize,$disk->size($image->path));
        $this->assertSame($before,hash_file('sha256',$disk->path($image->path)));

        $response=$this->getJson('/api/v1/vehicles/'.$car->slug)->assertOk();
        $response->assertJsonPath('data.images.0.thumbnail_url',VehicleImageVariants::urlFor($image,'thumb'));
        $response->assertJsonPath('data.images.0.card_url',VehicleImageVariants::urlFor($image,'card'));
        $response->assertJsonPath('data.images.0.detail_url',VehicleImageVariants::urlFor($image,'detail'));
        $response->assertJsonPath('data.images.0.url',$disk->url($image->path));
    }

    public function test_unchanged_api_contract_before_backfill_and_after_photo_removal(): void
    {
        $this->requireWebp();
        Storage::fake('public');
        $car=$this->car();
        $image=$this->photo($car);
        $response=$this->getJson('/api/v1/vehicles/'.$car->slug)->assertOk();
        $response->assertJsonPath('data.images.0.thumbnail_url',null);
        $response->assertJsonPath('data.images.0.card_url',null);
        $response->assertJsonPath('data.images.0.detail_url',null);
        $response->assertJsonPath('data.images.0.url',Storage::disk('public')->url($image->path));

        VehicleImageVariants::generate($image);
        $thumb=VehicleImageVariants::pathFor($image->path,'thumb');
        Storage::disk('public')->assertExists($thumb);
        $this->actingAs(User::factory()->create(['is_admin'=>true,'is_active'=>true]))
            ->delete('/manage/vehicles/'.$car->id.'/photos/'.$image->id)->assertRedirect();
        Storage::disk('public')->assertMissing($thumb);
        Storage::disk('public')->assertMissing($image->path);
    }

    public function test_new_upload_generates_variants_and_replacement_keeps_record_and_new_urls(): void
    {
        $this->requireWebp();
        Storage::fake('public');
        $car=$this->car();
        $this->actingAs(User::factory()->create(['is_admin'=>true,'is_active'=>true]));
        $this->post('/manage/vehicles/'.$car->id.'/photos',[
            'photos'=>[UploadedFile::fake()->image('new.jpg',1200,800)],
        ])->assertRedirect();
        $image=$car->images()->firstOrFail();
        $originalPath=$image->path;
        $oldThumb=VehicleImageVariants::pathFor($originalPath,'thumb');
        Storage::disk('public')->assertExists($oldThumb);

        $this->post('/manage/vehicles/'.$car->id.'/photos/'.$image->id.'/replace',[
            'photo'=>UploadedFile::fake()->image('updated.png',1000,800),
        ])->assertRedirect();

        $updated=$image->fresh();
        $this->assertSame($image->id,$updated->id);
        $this->assertNotSame($originalPath,$updated->path);
        $this->assertTrue($updated->is_primary);
        Storage::disk('public')->assertMissing($oldThumb);
        Storage::disk('public')->assertExists(VehicleImageVariants::pathFor($updated->path,'thumb'));
    }

    public function test_backfill_honors_limit_and_rejects_invalid_limit(): void
    {
        $this->requireWebp();
        Storage::fake('public');
        $car=$this->car();
        $this->photo($car);
        $this->artisan('carfolk:images:build',['--limit'=>1])->assertSuccessful();
        $this->artisan('carfolk:images:build',['--limit'=>-3])->assertFailed();
    }
}
