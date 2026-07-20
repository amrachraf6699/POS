<?php

namespace Modules\Identity\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Business\App\Models\Branch;
use Modules\Identity\Database\Factories\TenantFactory;

/**
 * @property int $id
 * @property string $name
 * @property string $status
 */
class Tenant extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_SUSPENDED = 'suspended';

    protected $fillable = ['name', 'slug', 'status'];

    public function memberships()
    {
        return $this->hasMany(Membership::class);
    }

    public function branches()
    {
        return $this->hasMany(Branch::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'memberships')
            ->withPivot(['role', 'status'])
            ->withTimestamps();
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE && ! $this->trashed();
    }

    protected static function newFactory(): TenantFactory
    {
        return TenantFactory::new();
    }
}
