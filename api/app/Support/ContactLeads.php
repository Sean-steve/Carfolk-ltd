<?php
namespace App\Support;
use App\Models\VehicleEnquiry;
use App\Models\TestDriveRequest;
use Carbon\CarbonImmutable;
class ContactLeads {
 public static function createEnquiry(array $data): VehicleEnquiry {
     // validated, whitelisted public input; consent timestamp is authoritative server time
     return VehicleEnquiry::create([
       'vehicle_id'=>$data['vehicle_id']??null,
       'name'=>$data['name'],'phone'=>$data['phone'],'email'=>$data['email']??null,
       'message'=>$data['message'],'consent_at'=>now(),'status'=>'new',
     ]);
 }
 public static function createViewing(array $data): TestDriveRequest {
     $requested=CarbonImmutable::createFromFormat('!Y-m-d H:i',$data['preferred_date'].' '.$data['preferred_time'],'Africa/Nairobi')->utc();
     return TestDriveRequest::create([
       'vehicle_id'=>$data['vehicle_id'],'name'=>$data['name'],'phone'=>$data['phone'],
       'email'=>$data['email']??null,'message'=>$data['message']??null,
       'requested_at'=>$requested,'consent_at'=>now(),'status'=>'requested',
     ]);
 }
}
