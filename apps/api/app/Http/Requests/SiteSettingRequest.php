<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class SiteSettingRequest extends FormRequest {
 public function authorize(): bool {return (bool)$this->user()?->is_admin && (bool)$this->user()?->is_active;}
 public function rules(): array {
  return [
    'name'=>['required','string','max:130'],
    'tagline'=>['required','string','max:150'],
    'phone'=>['nullable','regex:/^\\+?254[0-9]{9}$/'],
    'whatsapp'=>['nullable','regex:/^\\+?254[17][0-9]{8}$/'],
    'email'=>['nullable','email','max:254'],
    'address'=>['nullable','string','max:300'],
    'opening_hours'=>['nullable','string','max:250'],
    'about'=>['nullable','string','max:10000'],
  ];
 }
}
