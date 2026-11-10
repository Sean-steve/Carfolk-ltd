<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class TestDriveRequest extends Model {
    use HasUuids;
    protected $fillable=['vehicle_id','name','phone','email','message','requested_at','status','consent_at'];
    protected function casts(): array{return ['requested_at'=>'datetime','consent_at'=>'datetime'];}
    public function vehicle(): BelongsTo{return $this->belongsTo(Vehicle::class);}
}
