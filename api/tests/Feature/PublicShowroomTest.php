<?php
namespace Tests\Feature;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
class PublicShowroomTest extends TestCase {
 use RefreshDatabase;
 private function car(array $attributes=[]): Vehicle {
   return Vehicle::create(array_merge([
     'stock_reference'=>'CF-100','slug'=>'toyota-rav4-2021-cf-100','make'=>'Toyota','model'=>'RAV4','year'=>2021,
     'price_kes'=>3500000,'status'=>'available','is_published'=>true,
   ],$attributes));
 }
 public function test_only_published_nonarchived_inventory_is_public(): void {
   $visible=$this->car();
   $this->car(['stock_reference'=>'CF-101','slug'=>'private','is_published'=>false]);
   $this->car(['stock_reference'=>'CF-102','slug'=>'archived','archived_at'=>now()]);
   $this->getJson('/api/v1/vehicles')->assertOk()->assertJsonCount(1,'data')->assertJsonPath('data.0.id',$visible->id);
   $this->getJson('/api/v1/vehicles/'.$visible->slug)->assertOk()->assertJsonPath('data.make','Toyota');
   $this->getJson('/api/v1/vehicles/private')->assertNotFound();
   $this->getJson('/api/v1/vehicles/archived')->assertNotFound();
 }
 public function test_public_filters_and_sorting(): void {
   $this->car(['stock_reference'=>'CF-120','slug'=>'rav4','price_kes'=>3500000]);
   $this->car(['stock_reference'=>'CF-121','slug'=>'cx5','make'=>'Mazda','model'=>'CX-5','price_kes'=>2600000,'year'=>2019,'fuel_type'=>'Diesel']);
   $this->getJson('/api/v1/vehicles?make=Mazda&price_max=3000000')->assertOk()->assertJsonCount(1,'data')->assertJsonPath('data.0.model','CX-5');
   $this->getJson('/api/v1/vehicles?sort=price-asc')->assertOk()->assertJsonPath('data.0.model','CX-5');
   $this->getJson('/api/v1/vehicles?sort=invalid')->assertUnprocessable();
 }
 public function test_public_site_settings_use_safe_empty_defaults(): void {
   $this->getJson('/api/v1/site')->assertOk()->assertJsonPath('data.name','Carfolk LTD')->assertJsonPath('data.phone',null);
 }
}
