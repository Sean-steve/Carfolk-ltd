<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class VehicleImage extends Model {
    use HasUuids;
    protected $fillable=['vehicle_id','disk','path','alt_text','sort_order','is_primary'];
    protected function casts(): array{return ['is_primary'=>'boolean','sort_order'=>'integer'];}
    public function vehicle(): BelongsTo{return $this->belongsTo(Vehicle::class);}
}
