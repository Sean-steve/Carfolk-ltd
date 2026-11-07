<?php
namespace Tests\Feature;
use App\Models\Vehicle;
use App\Models\VehicleEnquiry;
use App\Models\TestDriveRequest;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
class LeadPersistenceTest extends TestCase {
 use RefreshDatabase;
 protected function setUp():void {parent::setUp();config()->set('carfolk.leads_enabled',true);config()->set('carfolk.privacy_url','https://carfolk.example/privacy');}
 private function vehicle(array $attrs=[]): Vehicle {
  return Vehicle::create(array_merge(['stock_reference'=>'CF-200','slug'=>'honda-crv','make'=>'Honda','model'=>'CR-V','year'=>2020,'price_kes'=>3400000,'is_published'=>true,'status'=>'available'],$attrs));
 }
 private function draft(array $extra=[]): array {return array_merge(['name'=>'Jane Doe','phone'=>'0712345678','message'=>'Please share more information about this vehicle.','consent'=>true],$extra);}
 public function test_contact_enquiry_is_persisted_only_after_valid_submission(): void {
  $car=$this->vehicle();
  $this->postJson('/api/v1/enquiries',$this->draft(['vehicle_id'=>$car->id]))->assertCreated()->assertJsonPath('data.status','new');
  $this->assertDatabaseCount('vehicle_enquiries',1);
  $this->assertNotNull(VehicleEnquiry::first()->consent_at);
 }
 public function test_bad_or_unpublished_vehicle_and_honeypot_are_rejected(): void {
  $hidden=$this->vehicle(['is_published'=>false]);
  $this->postJson('/api/v1/enquiries',$this->draft(['vehicle_id'=>$hidden->id]))->assertUnprocessable();
  $this->postJson('/api/v1/enquiries',$this->draft(['website'=>'spam']))->assertUnprocessable();
  $this->postJson('/api/v1/enquiries',$this->draft(['phone'=>'123']))->assertUnprocessable();
  $this->assertDatabaseCount('vehicle_enquiries',0);
 }
 public function test_viewing_request_is_saved_in_utc_but_not_auto_confirmed(): void {
  $car=$this->vehicle();
  $date=CarbonImmutable::now('Africa/Nairobi')->addDays(2)->format('Y-m-d');
  $payload=$this->draft(['vehicle_id'=>$car->id,'preferred_date'=>$date,'preferred_time'=>'14:30']);
  $this->postJson('/api/v1/test-drives',$payload)->assertCreated()->assertJsonPath('data.status','requested');
  $this->assertDatabaseCount('test_drive_requests',1);
  $v=TestDriveRequest::first();
  $this->assertSame('requested',$v->status);
  $this->assertSame($date.' 14:30',$v->requested_at->timezone('Africa/Nairobi')->format('Y-m-d H:i'));
 }
 public function test_a_sold_vehicle_cannot_be_booked_for_viewing(): void {
  $car=$this->vehicle(['status'=>'sold']);
  $date=CarbonImmutable::now('Africa/Nairobi')->addDays(2)->format('Y-m-d');
  $this->postJson('/api/v1/test-drives',$this->draft(['vehicle_id'=>$car->id,'preferred_date'=>$date,'preferred_time'=>'10:30']))->assertUnprocessable();
  $this->assertDatabaseCount('test_drive_requests',0);
 }
 public function test_guest_cannot_read_lead_lists(): void {
  $this->getJson('/api/v1/admin/enquiries')->assertUnauthorized();
  $this->get('/manage/enquiries')->assertRedirect('/manage/login');
 }
}
