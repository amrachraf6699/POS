<?php

namespace Modules\Business\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Business\Database\Factories\BranchAssignmentFactory;
use Modules\Identity\App\Domain\Tenancy\BelongsToTenant;
use Modules\Identity\App\Models\User;

/** @property int $tenant_id @property int $branch_id @property int $user_id @property string $status */
class BranchAssignment extends Model
{
    use BelongsToTenant, HasFactory;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_INACTIVE = 'inactive';

    protected $table = 'branch_user';

    protected $fillable = ['tenant_id', 'branch_id', 'user_id', 'status'];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    protected static function newFactory(): BranchAssignmentFactory
    {
        return BranchAssignmentFactory::new();
    }
}
