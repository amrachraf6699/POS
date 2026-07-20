<?php

namespace Modules\Identity\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Modules\Business\App\Models\Branch;
use Modules\Business\App\Models\BranchAssignment;
use Modules\Identity\Database\Factories\UserFactory;

/**
 * @property int $id
 * @property string $email
 * @property string $status
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_INACTIVE = 'inactive';

    protected $fillable = ['name', 'email', 'password', 'status'];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function memberships()
    {
        return $this->hasMany(Membership::class);
    }

    public function branchAssignments()
    {
        return $this->hasMany(BranchAssignment::class);
    }

    public function branches()
    {
        return $this->belongsToMany(Branch::class, 'branch_user')
            ->using(BranchAssignment::class)
            ->withPivot(['id', 'tenant_id', 'status'])
            ->withTimestamps();
    }

    public function tenants()
    {
        return $this->belongsToMany(Tenant::class, 'memberships')
            ->withPivot(['role', 'status'])
            ->withTimestamps();
    }

    public function accessibleTenants()
    {
        if (! $this->isActive()) {
            return Tenant::query()->whereRaw('1 = 0');
        }

        return Tenant::query()
            ->where('tenants.status', Tenant::STATUS_ACTIVE)
            ->whereHas('memberships', function ($query): void {
                $query->where('user_id', $this->getKey())
                    ->where('status', Membership::STATUS_ACTIVE);
            });
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }
}
