<?php
namespace Database\Seeders;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\SiteSetting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CertificationSeeder extends Seeder {
 public function run(): void {
  if(!app()->environment('testing') || getenv('CI')!=='true') {
    throw new \RuntimeException('Certification fixtures can only run in isolated CI testing.');
  }
  $password=(string)getenv('CARFOLK_CERT_PASSWORD');
  if(strlen($password)<16)throw new \RuntimeException('Missing safe CI-only password.');
  User::updateOrCreate(['email'=>'cert-admin@example.invalid'],[
   'name'=>'Carfolk CI Operator','password'=>Hash::make($password),'is_admin'=>true,'is_active'=>true,
  ]);
  Vehicle::updateOrCreate(['stock_reference'=>'CI-CAR-001'],[
   'slug'=>'ci-toyota-rav4','make'=>'Toyota','model'=>'RAV4','year'=>2022,
   'price_kes'=>4250000,'status'=>'available','is_published'=>true,'is_featured'=>true,
   'mileage_km'=>52000,'fuel_type'=>'Petrol','body_type'=>'SUV','transmission'=>'Automatic',
   'condition'=>'Foreign-used','description'=>'Certified test-only listing for cross-system validation.',
   'features'=>['Reverse camera','Automatic transmission'],'location'=>'CI test location',
  ]);
  Vehicle::updateOrCreate(['stock_reference'=>'CI-CAR-002'],[
   'slug'=>'ci-honda-fit','make'=>'Honda','model'=>'Fit','year'=>2020,
   'price_kes'=>1850000,'status'=>'available','is_published'=>true,'is_featured'=>false,
   'mileage_km'=>64000,'fuel_type'=>'Hybrid','body_type'=>'Hatchback','transmission'=>'Automatic',
   'condition'=>'Locally-used','description'=>'Second certified test-only listing.',
   'features'=>['Hybrid powertrain'],
  ]);
  SiteSetting::updateOrCreate(['id'=>1],[
   'name'=>'Carfolk LTD','tagline'=>'Quality & Affordability',
   'phone'=>'+254712345678','whatsapp'=>'+254712345678',
   'email'=>'carfolk-ci@example.invalid','address'=>'CI test location',
   'opening_hours'=>'Testing hours only','about'=>'CI-only test business configuration.',
  ]);
 }
}
