<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Http\Requests\PublicEnquiryRequest;
use App\Http\Requests\PublicViewingRequest;
use App\Support\ContactLeads;
use Illuminate\Http\JsonResponse;
class LeadController extends Controller {
 public function enquiry(PublicEnquiryRequest $request): JsonResponse {
   $lead=ContactLeads::createEnquiry($request->validated());
   return response()->json(['data'=>['id'=>$lead->id,'status'=>$lead->status,'message'=>'Enquiry received. The dealership will review your request.']],201);
 }
 public function viewing(PublicViewingRequest $request): JsonResponse {
   $lead=ContactLeads::createViewing($request->validated());
   return response()->json(['data'=>['id'=>$lead->id,'status'=>$lead->status,'message'=>'Viewing requested. This is not a confirmed appointment.']],201);
 }
}
