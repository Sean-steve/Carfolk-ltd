<?php
namespace Tests\Feature;

use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleEnquiry;
use App\Models\TestDriveRequest;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IntegrationContractsTest extends TestCase {
 use RefreshDatabase;
 protected function setUp(): void {
  parent::setUp();
  config()->set('carfolk.leads_enabled',true);
  config()->set('carfolk.privacy_url','https://carfolk.example/privacy');
 }
 private function inventory(array $attributes=[]): Vehicle {
  return Vehicle::create(array_merge([
   'stock_reference'=>'INT-001','slug'=>'real-stock-a','make'=>'Toyota','model'=>'RAV4',
   'year'=>2022,'price_kes'=>4800000,'body_type'=>'SUV','fuel_type'=>'Petrol',
   'transmission'=>'Automatic','condition'=>'Foreign-used','status'=>'available',
   'is_published'=>true,'is_featured'=>true,
  ],$attributes));
 }
 private function lead(array $additional=[]):array {
  return array_merge(['name'=>'Test Buyer','phone'=>'0712345678','message'=>'Please share information about this available vehicle.','consent'=>true],$additional);
 }
 public function test_frontend_filter_facets_featured_and_compare_contracts():void {
  $a=$this->inventory();
  $b=$this->inventory(['stock_reference'=>'INT-002','slug'=>'real-stock-b','make'=>'Honda','model'=>'Fit','body_type'=>'Hatchback','price_kes'=>1800000,'is_featured'=>false]);
  $this->inventory(['stock_reference'=>'INT-003','slug'=>'hidden','is_published'=>false]);
  $this->getJson('/api/v1/vehicles?featured=1')->assertOk()->assertJsonCount(1,'data')->assertJsonPath('data.0.id',$a->id);
  $this->getJson('/api/v1/vehicles?condition=Foreign-used&price_max=2000000')->assertOk()->assertJsonCount(1,'data')->assertJsonPath('data.0.id',$b->id);
  $this->getJson('/api/v1/vehicles/options')->assertOk()->assertJsonPath('data.makes.0','Honda')->assertJsonPath('data.models_by_make.Toyota.0','RAV4');
  $this->getJson('/api/v1/vehicles/compare?ids[0]='.$a->id.'&ids[1]='.$b->id)->assertOk()->assertJsonCount(2,'data')->assertJsonPath('data.0.id',$a->id);
  $this->getJson('/api/v1/vehicles/compare?ids[0]='.$a->id.'&ids[1]='.$a->id)->assertUnprocessable();
  $this->getJson('/api/v1/vehicles/compare?ids[0]=not-a-uuid')->assertUnprocessable();
 }
 public function test_lead_gate_fails_closed_when_approval_is_absent():void {
  $car=$this->inventory();
  config()->set('carfolk.leads_enabled',false);
  $this->postJson('/api/v1/enquiries',$this->lead(['vehicle_id'=>$car->id]))->assertStatus(503);
  $this->postJson('/api/v1/test-drives',$this->lead(['vehicle_id'=>$car->id]))->assertStatus(503);
  $this->getJson('/api/v1/site')->assertJsonPath('data.leads_enabled',false);
  $this->assertDatabaseCount('vehicle_enquiries',0);
  config()->set('carfolk.leads_enabled',true);
  config()->set('carfolk.privacy_url',null);
  $this->postJson('/api/v1/enquiries',$this->lead(['vehicle_id'=>$car->id]))->assertStatus(503);
  $this->assertDatabaseCount('vehicle_enquiries',0);
 }
 public function test_full_buyer_to_admin_enquiry_and_viewing_lifecycle():void {
  $admin=User::factory()->create(['is_admin'=>true,'is_active'=>true]);
  $car=$this->inventory();
  $this->getJson('/api/v1/site')->assertJsonPath('data.leads_enabled',true);
  $this->postJson('/api/v1/enquiries',$this->lead(['vehicle_id'=>$car->id]))->assertCreated()->assertJsonPath('data.status','new');
  $this->assertDatabaseCount('vehicle_enquiries',1);
  $date=CarbonImmutable::now('Africa/Nairobi')->addDays(3)->format('Y-m-d');
  $this->postJson('/api/v1/test-drives',$this->lead(['vehicle_id'=>$car->id,'preferred_date'=>$date,'preferred_time'=>'15:00']))->assertCreated()->assertJsonPath('data.status','requested');
  $this->assertDatabaseCount('test_drive_requests',1);
  $this->actingAs($admin)->getJson('/api/v1/admin/summary')->assertOk()->assertJsonPath('data.new_enquiries',1)->assertJsonPath('data.pending_viewings',1);
  $enquiry=VehicleEnquiry::firstOrFail();
  $viewing=TestDriveRequest::firstOrFail();
  $this->actingAs($admin)->patchJson('/api/v1/admin/enquiries/'.$enquiry->id,['status'=>'contacted'])->assertOk();
  $this->actingAs($admin)->patchJson('/api/v1/admin/test-drives/'.$viewing->id,['status'=>'confirmed'])->assertOk();
  $this->assertDatabaseHas('vehicle_enquiries',['id'=>$enquiry->id,'status'=>'contacted']);
  $this->assertDatabaseHas('test_drive_requests',['id'=>$viewing->id,'status'=>'confirmed']);
  $this->actingAs($admin)->patchJson('/api/v1/admin/vehicles/'.$car->id,[
   'stock_reference'=>$car->stock_reference,'make'=>$car->make,'model'=>$car->model,
   'year'=>$car->year,'price_kes'=>$car->price_kes,'status'=>'sold','is_published'=>true,
  ])->assertOk();
  $this->getJson('/api/v1/vehicles/'.$car->slug)->assertJsonPath('data.status','sold');
  $this->postJson('/api/v1/test-drives',$this->lead(['vehicle_id'=>$car->id,'preferred_date'=>$date,'preferred_time'=>'16:00']))->assertUnprocessable();
  $this->actingAs($admin)->deleteJson('/api/v1/admin/vehicles/'.$car->id)->assertOk();
  $this->getJson('/api/v1/vehicles/'.$car->slug)->assertNotFound();
  $this->assertDatabaseCount('vehicle_enquiries',1);
  $this->assertDatabaseCount('test_drive_requests',1);
 }
}
