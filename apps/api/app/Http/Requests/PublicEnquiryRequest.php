<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class PublicEnquiryRequest extends FormRequest {
    public function authorize(): bool {return true;}
    public function rules(): array {
        return [
          'vehicle_id'=>['nullable','uuid',Rule::exists('vehicles','id')->where(fn($q)=>$q->where('is_published',true)->whereNull('archived_at'))],
          'name'=>['required','string','min:2','max:80'],
          'phone'=>['required','regex:/^(?:0[17][0-9]{8}|(?:\\+?254)[17][0-9]{8})$/'],
          'email'=>['nullable','email:rfc','max:254'],
          'message'=>['required','string','min:10','max:1000'],
          'consent'=>['required','accepted'],
          'website'=>['prohibited'],
        ];
    }
}
