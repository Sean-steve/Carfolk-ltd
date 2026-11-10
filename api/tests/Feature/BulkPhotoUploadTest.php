<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class BulkPhotoUploadTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['is_admin'=>true,'is_active'=>true]);
    }

    private function vehicle(): Vehicle
    {
        return Vehicle::create([
            'stock_reference'=>'BULK-001',
            'slug'=>'bulk-photo-car',
            'make'=>'Toyota',
            'model'=>'RAV4',
            'year'=>2021,
            'price_kes'=>2000000,
        ]);
    }

    private function photos(int $count): array
    {
        return array_map(fn(int $n)=>UploadedFile::fake()->image('photo-'.$n.'.png',640,480),range(1,$count));
    }

    public function test_seven_selected_photos_can_be_sent_as_five_and_two(): void
    {
        Storage::fake('public');
        $vehicle=$this->vehicle();
        $this->actingAs($this->admin());
        $first='d3ca48b1-7ef6-4be7-b610-4546cf36ce04';
        $second='4d8f0acc-b986-48c9-97df-0c24c7fe18d0';

        $this->postJson('/manage/vehicles/'.$vehicle->id.'/photos',[
            'client_batch_id'=>$first,
            'photos'=>$this->photos(5),
        ])->assertCreated()->assertJsonPath('uploaded',5)
            ->assertJsonPath('total',5)->assertJsonPath('remaining',15)
            ->assertJsonPath('client_batch_id',$first);
        $this->postJson('/manage/vehicles/'.$vehicle->id.'/photos',[
            'client_batch_id'=>$second,
            'photos'=>$this->photos(2),
        ])->assertCreated()->assertJsonPath('uploaded',2)
            ->assertJsonPath('total',7)->assertJsonPath('remaining',13);

        $this->assertDatabaseCount('vehicle_images',7);
        $this->assertDatabaseCount('vehicle_photo_upload_batches',2);
        $this->assertSame(1,$vehicle->images()->where('is_primary',true)->count());
        foreach ($vehicle->images as $image) Storage::disk('public')->assertExists($image->path);
    }

    public function test_replay_with_the_same_batch_id_never_duplicates_images(): void
    {
        Storage::fake('public');
        $vehicle=$this->vehicle();
        $this->actingAs($this->admin());
        $id='83ff2147-0346-42ad-9afe-d747830a2665';
        foreach (range(1,2) as $attempt) {
            $this->postJson('/manage/vehicles/'.$vehicle->id.'/photos',[
                'client_batch_id'=>$id,
                'photos'=>$this->photos(3),
            ])->assertCreated()->assertJsonPath('uploaded',3)
                ->assertJsonPath('total',3)
                ->assertJsonPath('client_batch_id',$id);
        }
        $this->assertDatabaseCount('vehicle_images',3);
        $this->assertDatabaseCount('vehicle_photo_upload_batches',1);
        $this->assertSame(3,$vehicle->images()->count());
        $this->assertSame(3,\App\Models\AuditEvent::where('action','vehicle.photo_added')->count());
    }

    public function test_total_limit_still_applies_across_sequential_batches(): void
    {
        Storage::fake('public');
        $vehicle=$this->vehicle();
        $this->actingAs($this->admin());
        foreach (range(1,4) as $n) {
            $this->postJson('/manage/vehicles/'.$vehicle->id.'/photos',[
                'client_batch_id'=>sprintf('21a41910-8a54-4ae0-8d7a-%012d',$n),
                'photos'=>$this->photos(5),
            ])->assertCreated();
        }
        $this->assertDatabaseCount('vehicle_images',20);
        $this->postJson('/manage/vehicles/'.$vehicle->id.'/photos',[
            'client_batch_id'=>'7365ff15-3581-45c2-b171-4d57133b496c',
            'photos'=>$this->photos(1),
        ])->assertStatus(422);
        $this->assertDatabaseCount('vehicle_images',20);
        $this->assertDatabaseCount('vehicle_photo_upload_batches',4);
    }

    public function test_six_in_one_request_still_rejected_and_no_batch_stored(): void
    {
        Storage::fake('public');
        $vehicle=$this->vehicle();
        $this->actingAs($this->admin())
            ->postJson('/manage/vehicles/'.$vehicle->id.'/photos',[
                'client_batch_id'=>'e58e44aa-e3c1-46b9-8c37-f949886048c7',
                'photos'=>$this->photos(6),
            ])->assertUnprocessable()->assertJsonValidationErrors('photos');
        $this->assertDatabaseCount('vehicle_images',0);
        $this->assertDatabaseCount('vehicle_photo_upload_batches',0);
    }

    public function test_guest_and_regular_user_cannot_upload_bulk_photos(): void
    {
        $vehicle=$this->vehicle();
        $url='/manage/vehicles/'.$vehicle->id.'/photos';
        $this->postJson($url,['client_batch_id'=>'e58e44aa-e3c1-46b9-8c37-f949886048c7'])
            ->assertUnauthorized();
        $user=User::factory()->create(['is_admin'=>false,'is_active'=>true]);
        $this->actingAs($user)->postJson($url,['client_batch_id'=>'e58e44aa-e3c1-46b9-8c37-f949886048c7'])
            ->assertForbidden();
    }

    public function test_photo_form_contains_bulk_upload_controls(): void
    {
        $vehicle=$this->vehicle();
        $this->actingAs($this->admin())->get('/manage/vehicles/'.$vehicle->id.'/edit')
            ->assertOk()
            ->assertSee('data-bulk-progress',false)
            ->assertSee('data-bulk-retry',false)
            ->assertSee('admin-bulk-photos.js');
    }
}
