<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Vehicle extends Model {
    use HasUuids;
    protected $fillable = ['stock_reference','slug','make','model','trim','year','price_kes','mileage_km','engine_cc','fuel_type','transmission','drivetrain','body_type','condition','colour','location','description','features','status','is_featured','is_published','archived_at'];
    protected function casts(): array {return ['features'=>'array','is_featured'=>'boolean','is_published'=>'boolean','archived_at'=>'datetime','price_kes'=>'integer','mileage_km'=>'integer','year'=>'integer'];}
    public function images(): HasMany {return $this->hasMany(VehicleImage::class)->orderBy('sort_order')->orderBy('created_at');}
    public function enquiries(): HasMany {return $this->hasMany(VehicleEnquiry::class);}
    public function viewings(): HasMany {return $this->hasMany(TestDriveRequest::class);}
    public function scopePublished($query) {return $query->where('is_published',true)->whereNull('archived_at');}
}
