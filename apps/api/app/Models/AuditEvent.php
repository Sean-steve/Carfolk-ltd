<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
class AuditEvent extends Model {
    use HasUuids;
    public $timestamps=false;
    protected $fillable=['actor_id','action','resource_type','resource_id','metadata'];
    protected function casts(): array{return ['metadata'=>'array','created_at'=>'datetime'];}
    public static function record(string $action, Model $resource, array $metadata=[]): void {
        static::create([
          'actor_id'=>auth()->id(),
          'action'=>$action,
          'resource_type'=>$resource->getMorphClass(),
          'resource_id'=>(string)$resource->getKey(),
          'metadata'=>$metadata,
        ]);
    }
}
