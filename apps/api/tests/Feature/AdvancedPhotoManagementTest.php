<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class AdvancedPhotoManagementTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true, 'is_active' => true]);
    }

    private function vehicle(string $reference = 'O74-1', bool $published = false): Vehicle
    {
        return Vehicle::create([
            'stock_reference' => $reference,
            'slug' => 'test-'.strtolower($reference),
            'make' => 'Mazda',
            'model' => 'CX-5',
            'year' => 2023,
            'price_kes' => 3500000,
            'description' => 'Verified vehicle with clean interior.',
            'features' => ['Reverse camera', 'Cruise control'],
            'status' => 'available',
            'is_published' => $published,
        ]);
    }

    private function addPhotos(Vehicle $vehicle, int $count = 3): array
    {
        Storage::fake('public');
        $images = [];
        for ($i = 1; $i <= $count; $i++) {
            $path = UploadedFile::fake()->image('vehicle-'.$i.'.jpg', 640, 480)
                ->store('vehicles/'.$vehicle->id, 'public');
            $images[] = $vehicle->images()->create([
                'disk' => 'public', 'path' => $path,
                'alt_text' => 'Angle '.$i, 'is_primary' => $i === 1,
                'sort_order' => $i,
            ]);
        }
        return $images;
    }

    public function test_preview_requires_admin_and_never_publishes_draft(): void
    {
        $vehicle = $this->vehicle();
        $this->addPhotos($vehicle);
        $preview = '/manage/vehicles/'.$vehicle->id.'/preview';
        $this->get($preview)->assertRedirect('/manage/login');
        $this->actingAs(User::factory()->create(['is_admin' => false, 'is_active' => true]))
            ->get($preview)->assertForbidden();
        $this->actingAs($this->admin());
        $this->get($preview)->assertOk()
            ->assertSee('Private listing preview')
            ->assertSee('Unpublished draft')
            ->assertSee('Mazda')
            ->assertSee('CX-5')
            ->assertSee('KES 3,500,000')
            ->assertSee('Verified vehicle with clean interior.')
            ->assertSee('Reverse camera')
            ->assertSee('Main photo')
            ->assertSee('Preview only');
        $response = $this->get($preview);
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
        $this->assertStringContainsString('noindex', $response->headers->get('X-Robots-Tag'));
        $this->assertFalse($vehicle->fresh()->is_published);
        $this->getJson('/api/v1/vehicles/'.$vehicle->slug)->assertNotFound();
    }

    public function test_primary_photo_can_be_changed_without_reordering_or_duplication(): void
    {
        $vehicle = $this->vehicle('O74-P', true);
        [$first,$second,$third] = $this->addPhotos($vehicle);
        $this->actingAs($this->admin());
        $this->post('/manage/vehicles/'.$vehicle->id.'/photos/'.$third->id.'/primary')
            ->assertRedirect();
        $this->assertFalse($first->fresh()->is_primary);
        $this->assertTrue($third->fresh()->is_primary);
        $this->assertSame(1, VehicleImage::where('vehicle_id',$vehicle->id)->where('is_primary',true)->count());
        $this->assertSame([1,2,3], $vehicle->images()->pluck('sort_order')->all());
        $this->getJson('/api/v1/vehicles/'.$vehicle->slug)->assertOk()
            ->assertJsonPath('data.images.2.is_primary', true);
    }

    public function test_gallery_reorder_is_persisted_to_public_api(): void
    {
        $vehicle = $this->vehicle('O74-O', true);
        [$first,$second,$third] = $this->addPhotos($vehicle);
        $this->actingAs($this->admin());
        $this->post('/manage/vehicles/'.$vehicle->id.'/photos/order',[
            'order' => [$third->id, $first->id, $second->id],
        ])->assertRedirect();
        $this->assertSame([$third->id,$first->id,$second->id], $vehicle->images()->pluck('id')->all());
        $this->assertTrue($first->fresh()->is_primary);
        $this->getJson('/api/v1/vehicles/'.$vehicle->slug)->assertJsonPath('data.images.0.id',$third->id);
        $this->post('/manage/vehicles/'.$vehicle->id.'/photos/'.$first->id.'/move',[
            'direction' => 'up',
        ])->assertRedirect();
        $this->assertSame([$first->id,$third->id,$second->id], $vehicle->images()->pluck('id')->all());
    }

    public function test_reorder_rejects_missing_duplicate_and_foreign_photo_ids(): void
    {
        $vehicle = $this->vehicle();
        [$first,$second] = $this->addPhotos($vehicle, 2);
        $foreign = $this->vehicle('O74-OTHER');
        $otherImage = $foreign->images()->create([
            'disk' => 'public', 'path' => 'other.jpg',
            'sort_order' => 1, 'is_primary' => true,
        ]);
        $this->actingAs($this->admin());
        $url='/manage/vehicles/'.$vehicle->id.'/photos/order';
        $this->post($url,['order'=>[$first->id]])->assertSessionHasErrors('order');
        $this->post($url,['order'=>[$first->id,$first->id]])->assertSessionHasErrors('order.1');
        $this->post($url,['order'=>[$first->id,$otherImage->id]])->assertSessionHasErrors('order');
        $this->assertSame([$first->id,$second->id],$vehicle->images()->pluck('id')->all());
        $this->post('/manage/vehicles/'.$vehicle->id.'/photos/'.$otherImage->id.'/primary')->assertNotFound();
    }

    public function test_replacing_photo_preserves_id_primary_order_and_caption(): void
    {
        $vehicle = $this->vehicle();
        [$first,$second] = $this->addPhotos($vehicle,2);
        $previous=$first->path;
        $this->actingAs($this->admin())->post('/manage/vehicles/'.$vehicle->id.'/photos/'.$first->id.'/replace',[
            'photo'=>UploadedFile::fake()->image('replacement.png', 800, 500),
        ])->assertRedirect();
        $new=$first->fresh();
        $this->assertNotSame($previous,$new->path);
        Storage::disk('public')->assertExists($new->path);
        Storage::disk('public')->assertMissing($previous);
        $this->assertTrue($new->is_primary);
        $this->assertSame(1,$new->sort_order);
        $this->assertSame('Angle 1',$new->alt_text);
        $this->assertSame(2,$vehicle->images()->count());
        $this->assertSame($second->id,$vehicle->images()->orderByDesc('sort_order')->first()->id);
    }

    public function test_alt_text_and_editor_controls_and_auth(): void
    {
        $vehicle=$this->vehicle();
        [$first,$second]=$this->addPhotos($vehicle,2);
        $url='/manage/vehicles/'.$vehicle->id.'/photos/'.$first->id.'/caption';
        $this->post($url,['alt_text'=>'Untouchable'])->assertRedirect('/manage/login');
        $this->actingAs($this->admin())->post($url,['alt_text'=>'  Passenger side exterior  '])->assertRedirect();
        $this->assertSame('Passenger side exterior',$first->fresh()->alt_text);
        $this->get('/manage/vehicles/'.$vehicle->id.'/edit')->assertOk()
            ->assertSee('Set as main photo')
            ->assertSee('Replace photo')
            ->assertSee('Save photo order',false);
    }

    public function test_only_valid_images_can_replace_and_other_vehicle_images_are_not_editable(): void
    {
        $vehicle=$this->vehicle();
        [$first]=$this->addPhotos($vehicle,1);
        $other=$this->vehicle('O74-X');
        $this->actingAs($this->admin())
            ->post('/manage/vehicles/'.$other->id.'/photos/'.$first->id.'/replace',[
                'photo'=>UploadedFile::fake()->image('different.jpg'),
            ])->assertNotFound();
        $this->post('/manage/vehicles/'.$vehicle->id.'/photos/'.$first->id.'/replace',[
            'photo'=>UploadedFile::fake()->createWithContent('bad.png','not-an-image'),
        ])->assertSessionHasErrors('photo');
    }
}
