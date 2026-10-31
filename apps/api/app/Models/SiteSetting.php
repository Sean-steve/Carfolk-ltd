<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class SiteSetting extends Model {
    protected $fillable=['name','tagline','phone','whatsapp','email','address','opening_hours','about'];
    public static function publicDetails(): array {
        $s=static::first();
        return [
            'name'=>$s?->name??'Carfolk LTD',
            'tagline'=>$s?->tagline??'Quality & Affordability',
            'phone'=>$s?->phone,
            'whatsapp'=>$s?->whatsapp,
            'email'=>$s?->email,
            'address'=>$s?->address,
            'opening_hours'=>$s?->opening_hours,
            'about'=>$s?->about,
        ];
    }
}
