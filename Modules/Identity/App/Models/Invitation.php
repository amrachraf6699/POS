<?php

namespace Modules\Identity\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Identity\Database\Factories\InvitationFactory;

/**
 * @property int $id
 * @property int $tenant_id
 * @property int $invited_by
 * @property string $email
 * @property string $role
 * @property string $token_hash
 * @property string $status
 */
class Invitation extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';

    public const STATUS_ACCEPTED = 'accepted';

    public const STATUS_REVOKED = 'revoked';

    public const ROLE_MANAGER = Membership::ROLE_MANAGER;

    protected $fillable = [
        'tenant_id',
        'invited_by',
        'email',
        'role',
        'token_hash',
        'status',
        'expires_at',
        'accepted_at',
        'revoked_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'accepted_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function inviter()
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isExpired(): bool
    {
        return $this->getAttribute('expires_at') !== null && $this->getAttribute('expires_at')->isPast();
    }

    protected static function newFactory(): InvitationFactory
    {
        return InvitationFactory::new();
    }
}
