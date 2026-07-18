<?php

namespace Modules\Identity\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Identity\Database\Factories\MembershipFactory;

/**
 * @property int $id
 * @property string $role
 * @property string $status
 */
class Membership extends Model
{
    use HasFactory;

    public const ROLE_OWNER = 'owner';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_INACTIVE = 'inactive';

    protected $fillable = ['tenant_id', 'user_id', 'role', 'status'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isOwner(): bool
    {
        return $this->role === self::ROLE_OWNER;
    }

    protected static function newFactory(): MembershipFactory
    {
        return MembershipFactory::new();
    }
}
