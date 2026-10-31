<?php
namespace Tests\Feature;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
class AdminSecurityAndInventoryTest extends TestCase {
 use RefreshDatabase;
 private function admin(): User {
  return User::factory()->create(['is_admin'=>true,'is_active'=>true,'password'=>Hash::make('MyStrongTestPassword123!')]);
 }
 private function vehicleInput(array $extra=[]): array {
  return array_merge([
   'stock_reference'=>'CF-300','make'=>'Toyota','model'=>'RAV4','year'=>2021,
   'price_kes'=>3250000,'status'=>'available','is_published'=>1,'is_featured'=>1,
  ],$extra);
 }
 public function test_guest_and_normal_users_cannot_manage_inventory(): void {
  $this->get('/manage')->assertRedirect('/manage/login');
  $this->postJson('/api/v1/admin/vehicles',$this->vehicleInput())->assertUnauthorized();
  $this->actingAs(User::factory()->create(['is_admin'=>false]))->get('/manage')->assertForbidden();
  $this->postJson('/api/v1/admin/vehicles',$this->vehicleInput())->assertForbidden();
 }
 public function test_only_active_admin_can_log_in(): void {
  $admin=$this->admin();
  $this->post('/manage/login',['email'=>$admin->email,'password'=>'wrong'])->assertSessionHasErrors('email');
  $this->post('/manage/login',['email'=>$admin->email,'password'=>'MyStrongTestPassword123!'])->assertRedirect('/manage');
  $this->get('/manage')->assertOk();
  $this->post('/manage/logout')->assertRedirect('/manage/login');
  $this->get('/manage')->assertRedirect('/manage/login');
  $admin->update(['is_active'=>false]);
  $this->post('/manage/login',['email'=>$admin->email,'password'=>'MyStrongTestPassword123!'])->assertSessionHasErrors('email');
 }
 public function test_admin_creates_updates_and_archives_real_inventory(): void {
  $this->actingAs($this->admin());
  $this->postJson('/api/v1/admin/vehicles',$this->vehicleInput())->assertCreated();
  $this->assertDatabaseCount('vehicles',1);
  $vehicle=Vehicle::first();
  $this->assertTrue($vehicle->is_published);
  $this->getJson('/api/v1/vehicles/'.$vehicle->slug)->assertOk();
  $this->patchJson('/api/v1/admin/vehicles/'.$vehicle->id,$this->vehicleInput(['price_kes'=>3200000,'status'=>'reserved','is_published'=>false,'is_featured'=>false]))->assertOk();
  $vehicle->refresh();
  $this->assertSame(3200000,$vehicle->price_kes);
  $this->assertFalse($vehicle->is_published);
  $this->deleteJson('/api/v1/admin/vehicles/'.$vehicle->id)->assertOk();
  $this->assertNotNull($vehicle->fresh()->archived_at);
  $this->getJson('/api/v1/vehicles/'.$vehicle->slug)->assertNotFound();
  $this->assertDatabaseCount('vehicles',1);
  $this->assertDatabaseCount('audit_events',3);
 }
 public function test_admin_vehicle_form_and_settings_are_live(): void {
  $this->actingAs($this->admin());
  $this->get('/manage/vehicles')->assertOk()->assertSee('Vehicle Inventory');
  $this->get('/manage/vehicles/create')->assertOk();
  $this->get('/manage/settings')->assertOk();
  $this->patch('/manage/settings',['name'=>'Carfolk LTD','tagline'=>'Quality & Affordability','email'=>'info@carfolk.example'])->assertRedirect();
  $this->getJson('/api/v1/site')->assertJsonPath('data.email','info@carfolk.example');
 }
 public function test_html_vehicle_form_ignores_blank_optional_features(): void {
  $this->actingAs($this->admin());
  $payload=$this->vehicleInput(['features'=>['Reverse camera','','   ','Cruise control'],'is_published'=>1]);
  $this->post('/manage/vehicles',$payload)->assertRedirect();
  $this->assertSame(['Reverse camera','Cruise control'],Vehicle::first()->features);
  $this->get('/manage/vehicles/'.Vehicle::first()->id.'/edit')->assertOk();
 }
 public function test_inactive_administrator_is_denied_even_with_session(): void {
  $user=$this->admin();
  $user->update(['is_active'=>false]);
  $this->actingAs($user)->get('/manage')->assertForbidden();
  $this->getJson('/api/v1/admin/summary')->assertForbidden();
 }
}
