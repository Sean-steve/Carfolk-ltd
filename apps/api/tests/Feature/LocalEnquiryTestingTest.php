<?php
namespace Tests\Feature;

use App\Models\Vehicle;
use App\Support\LeadAcceptance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocalEnquiryTestingTest extends TestCase {
 use RefreshDatabase;

 public function test_local_test_mode_is_disabled_by_default_even_on_loopback():void {
   config()->set('carfolk.local_enquiries_enabled',false);
   config()->set('carfolk.leads_enabled',false);
   $this->assertFalse(LeadAcceptance::enabled());
   $this->get('/local/privacy')->assertNotFound();
   $this->postJson('/api/v1/enquiries',[])->assertStatus(503);
 }

 public function test_explicitly_enabled_local_mode_saves_invented_enquiry_and_viewing():void {
   $original=$this->app['env'];
   try {
     $this->app['env']='local';
     config()->set('carfolk.leads_enabled',false);
     config()->set('carfolk.local_enquiries_enabled',true);
     config()->set('database.default','pgsql');
     config()->set('database.connections.pgsql.host','127.0.0.1');
     config()->set('database.connections.pgsql.database','carfolk');
     config()->set('database.connections.pgsql.url',null);
     $this->assertSame('local-test',LeadAcceptance::mode());
     $this->getJson('/api/v1/site')->assertOk()
       ->assertJsonPath('data.leads_enabled',true)
       ->assertJsonPath('data.test_mode',true)
       ->assertJsonPath('data.privacy_url','http://localhost/local/privacy');
     $this->get('/local/privacy')->assertOk()->assertSee('testing only',false);
     $this->postJson('/api/v1/enquiries',[
       'name'=>'Local Test','phone'=>'0712345678',
       'message'=>'Please tell me more about this car.',
       'consent'=>true
     ])->assertCreated();
     $this->assertDatabaseCount('vehicle_enquiries',1);
     $car=Vehicle::create([
        'stock_reference'=>'LOCAL-001','slug'=>'local-only-photo-car',
        'make'=>'Honda','model'=>'Fit','year'=>2020,'price_kes'=>1700000,
        'status'=>'available','is_published'=>true
     ]);
     $this->postJson('/api/v1/test-drives',[
       'vehicle_id'=>$car->id,'name'=>'Local Viewer',
       'phone'=>'0712345678','consent'=>true,
       'preferred_date'=>now('Africa/Nairobi')->addDays(3)->format('Y-m-d'),
       'preferred_time'=>'14:00'
     ])->assertCreated();
     $this->assertDatabaseCount('test_drive_requests',1);
   } finally {
     $this->app['env']=$original;
   }
 }

 public function test_local_switch_never_enables_production_or_remote_database():void {
   $original=$this->app['env'];
   try {
     config()->set('carfolk.local_enquiries_enabled',true);
     config()->set('carfolk.leads_enabled',false);
     $this->app['env']='production';
     $this->assertFalse(LeadAcceptance::enabled());
     $this->getJson('/api/v1/site')->assertJsonPath('data.leads_enabled',false);
     $this->postJson('/api/v1/enquiries',[])->assertStatus(503);
     $this->app['env']='local';
     config()->set('database.connections.pgsql.host','remote.example.invalid');
     $this->assertFalse(LeadAcceptance::localTestEnabled());
   } finally {
     $this->app['env']=$original;
   }
 }
}
