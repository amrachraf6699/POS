<?php

namespace Modules\Business\App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Business\Database\Factories\BranchFactory;
use Modules\Identity\App\Domain\Tenancy\BelongsToTenant;
use Modules\Identity\App\Models\Membership;
use Modules\Identity\App\Models\User;

/**
 * @property int $tenant_id
 * @property string $name
 * @property string $code
 * @property string $status
 */
class Branch extends Model
{
    use BelongsToTenant, HasFactory;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_INACTIVE = 'inactive';

    protected $fillable = [
        'name', 'code', 'phone', 'email', 'address_line_1', 'address_line_2', 'city', 'state',
        'postal_code', 'country_code', 'timezone', 'status',
    ];

    public function assignments(): HasMany
    {
        return $this->hasMany(BranchAssignment::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'branch_user')
            ->using(BranchAssignment::class)
            ->withPivot(['id', 'tenant_id', 'status'])
            ->withTimestamps();
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function scopeAccessibleTo(Builder $query, User $user): Builder
    {
        if (! $user->isActive()) {
            $query->whereRaw('1 = 0');

            return $query;
        }

        $query->where('status', self::STATUS_ACTIVE)
            ->where(function (Builder $accessQuery) use ($user): void {
                $accessQuery->whereHas('tenant.memberships', function (Builder $membershipQuery) use ($user): void {
                    $membershipQuery->where('user_id', $user->getKey())
                        ->where('status', Membership::STATUS_ACTIVE)
                        ->where('role', Membership::ROLE_OWNER);
                })->orWhereHas('assignments', function (Builder $assignmentQuery) use ($user): void {
                    $assignmentQuery->where('branch_user.user_id', $user->getKey())
                        ->where('branch_user.status', BranchAssignment::STATUS_ACTIVE)
                        ->whereHas('user', function (Builder $userQuery): void {
                            $userQuery->where('status', User::STATUS_ACTIVE);
                        })
                        ->whereHas('user.memberships', function (Builder $membershipQuery): void {
                            $membershipQuery->where('status', Membership::STATUS_ACTIVE)
                                ->whereColumn('memberships.tenant_id', 'branch_user.tenant_id');
                        });
                });
            });

        return $query;
    }

    protected static function newFactory(): BranchFactory
    {
        return BranchFactory::new();
    }
}
