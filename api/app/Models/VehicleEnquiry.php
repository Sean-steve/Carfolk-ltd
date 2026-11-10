<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class VehicleEnquiry extends Model {
    use HasUuids;
    protected $fillable=['vehicle_id','name','phone','email','message','status','consent_at'];
    protected function casts(): array{return ['consent_at'=>'datetime'];}
    public function vehicle(): BelongsTo{return $this->belongsTo(Vehicle::class);}
}
