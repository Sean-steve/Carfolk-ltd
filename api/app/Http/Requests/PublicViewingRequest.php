<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class PublicViewingRequest extends FormRequest {
    public function authorize(): bool {return true;}
    public function rules(): array {
        return [
          'vehicle_id'=>['required','uuid',Rule::exists('vehicles','id')->where(fn($q)=>$q->where('is_published',true)->where('status','available')->whereNull('archived_at'))],
          'name'=>['required','string','min:2','max:80'],
          'phone'=>['required','regex:/^(?:0[17][0-9]{8}|(?:\\+?254)[17][0-9]{8})$/'],
          'email'=>['nullable','email:rfc','max:254'],
          'message'=>['nullable','string','max:1000'],
          'preferred_date'=>['required','date_format:Y-m-d','after_or_equal:today'],
          'preferred_time'=>['required','date_format:H:i'],
          'consent'=>['required','accepted'],
          'website'=>['prohibited'],
        ];
    }
    public function withValidator($validator): void {
       $validator->after(function($v) {
           $date=$this->input('preferred_date');
           $time=$this->input('preferred_time');
           if(!is_string($date)||!is_string($time))return;
           try {
               $when=\Carbon\CarbonImmutable::createFromFormat('!Y-m-d H:i', $date.' '.$time,'Africa/Nairobi');
               if(!$when||$when->format('Y-m-d H:i')!==$date.' '.$time||$when->lessThan(\Carbon\CarbonImmutable::now('Africa/Nairobi'))) {
                  $v->errors()->add('preferred_date','Viewing time must be in the future (Africa/Nairobi).');
               }
           } catch (\Throwable $e) { $v->errors()->add('preferred_date','Invalid proposed viewing date or time.'); }
       });
    }
}
