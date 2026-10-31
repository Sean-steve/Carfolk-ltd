<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class VehicleRequest extends FormRequest {
    public function authorize(): bool {return (bool)$this->user()?->is_admin && (bool)$this->user()?->is_active;}
    protected function prepareForValidation(): void {
        // The admin UI has optional feature inputs; do not persist blank features.
        $features=$this->input('features');
        if(is_array($features)) {
            $this->merge(['features'=>array_values(array_filter(array_map(
                fn($value)=>is_string($value)?trim($value):$value,$features
            ),fn($value)=>$value!==''&&$value!==null))]);
        }
    }
    public function rules(): array {
        $id=$this->route('vehicle')?->getKey();
        return [
          'stock_reference'=>['required','string','max:60',Rule::unique('vehicles','stock_reference')->ignore($id)],
          'make'=>['required','string','max:80'],
          'model'=>['required','string','max:100'],
          'trim'=>['nullable','string','max:100'],
          'year'=>['required','integer','between:1950,2035'],
          'price_kes'=>['required','integer','min:0','max:999999999999'],
          'mileage_km'=>['nullable','integer','min:0','max:999999999'],
          'engine_cc'=>['nullable','integer','min:0','max:30000'],
          'fuel_type'=>['nullable',Rule::in(['Petrol','Diesel','Hybrid','Electric','Other'])],
          'transmission'=>['nullable',Rule::in(['Automatic','Manual','CVT','Other'])],
          'drivetrain'=>['nullable',Rule::in(['2WD','AWD','4WD','Other'])],
          'body_type'=>['nullable',Rule::in(['SUV','Sedan','Crossover','Hatchback','Pickup','Van','Coupe','Other'])],
          'condition'=>['nullable',Rule::in(['New','Foreign-used','Locally-used'])],
          'colour'=>['nullable','string','max:50'],
          'location'=>['nullable','string','max:140'],
          'description'=>['nullable','string','max:10000'],
          'features'=>['nullable','array','max:50'],
          'features.*'=>['string','max:120'],
          'status'=>['required',Rule::in(['available','reserved','sold'])],
          'is_featured'=>['sometimes','boolean'],
          'is_published'=>['sometimes','boolean'],
        ];
    }
}
