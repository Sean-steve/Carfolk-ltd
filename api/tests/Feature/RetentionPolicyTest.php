<?php
namespace Tests\Feature;
use App\Models\VehicleEnquiry;
use App\Models\TestDriveRequest;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;
class RetentionPolicyTest extends TestCase {
 use RefreshDatabase;
 public function test_manual_cleanup_requires_explicit_policy_and_execution():void {
   $car=Vehicle::create(['stock_reference'=>'RT-001','slug'=>'rt-car','make'=>'Toyota','model'=>'RAV4','year'=>2019,'price_kes'=>2000000]);
   $closed=VehicleEnquiry::create(['vehicle_id'=>$car->id,'name'=>'Fixture','phone'=>'0712345678','message'=>'Customer enquiry fixture test','status'=>'closed','consent_at'=>now()]);
   $active=VehicleEnquiry::create(['vehicle_id'=>$car->id,'name'=>'Active','phone'=>'0712345678','message'=>'Active enquiry fixture test','status'=>'new','consent_at'=>now()]);
   $closed->forceFill(['created_at'=>now()->subDays(95)])->saveQuietly();
   $active->forceFill(['created_at'=>now()->subDays(95)])->saveQuietly();
   $old=TestDriveRequest::create(['vehicle_id'=>$car->id,'name'=>'Fixture','phone'=>'0712345678','status'=>'completed','consent_at'=>now(),'requested_at'=>now()->subDays(95)]);
   $old->forceFill(['created_at'=>now()->subDays(95)])->saveQuietly();
   config()->set('carfolk.retention_days',90);
   config()->set('carfolk.retention_approved',false);
   $this->assertSame(1,Artisan::call('carfolk:privacy-prune',['--execute'=>true]));
   $this->assertDatabaseCount('vehicle_enquiries',2);
   config()->set('carfolk.retention_approved',true);
   $this->assertSame(0,Artisan::call('carfolk:privacy-prune'));
   $this->assertDatabaseCount('vehicle_enquiries',2);
   $this->assertSame(0,Artisan::call('carfolk:privacy-prune',['--execute'=>true]));
   $this->assertDatabaseCount('vehicle_enquiries',1);
   $this->assertDatabaseCount('test_drive_requests',0);
   $this->assertDatabaseHas('vehicle_enquiries',['id'=>$active->id,'status'=>'new']);
 }
}
