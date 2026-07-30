<?php

namespace Modules\Inventory\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use LogicException;
use Modules\Business\App\Models\Branch;
use Modules\Identity\App\Domain\Tenancy\BelongsToTenant;
use Modules\Identity\App\Models\User;
use Modules\Inventory\App\Domain\Enums\InventoryTransferStatus;

/**
 * @property int $tenant_id
 * @property int $source_branch_id
 * @property int $destination_branch_id
 * @property InventoryTransferStatus $status
 * @property bool $requires_manager_approval
 * @property int $created_by_user_id
 */
final class InventoryTransfer extends Model
{
    use BelongsToTenant;

    protected $guarded = ['*'];

    protected $casts = [
        'status' => InventoryTransferStatus::class,
        'requires_manager_approval' => 'boolean',
        'posted_at' => 'immutable_datetime',
        'cancelled_at' => 'immutable_datetime',
    ];

    protected static function booted(): void
    {
        self::updating(static function (self $transfer): void {
            $allowed = ['status', 'posted_by_user_id', 'posted_at', 'cancelled_by_user_id', 'cancelled_at', 'cancellation_reason', 'updated_at'];
            if (array_diff(array_keys($transfer->getDirty()), $allowed) !== []) {
                throw new LogicException('Inventory transfers are immutable except for terminal status metadata.');
            }
        });
        self::deleting(static function (): void {
            throw new LogicException('Inventory transfers cannot be deleted.');
        });
    }

    public function sourceBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'source_branch_id');
    }

    public function destinationBranch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'destination_branch_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function poster(): BelongsTo
    {
        return $this->belongsTo(User::class, 'posted_by_user_id');
    }

    public function canceller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by_user_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(InventoryTransferItem::class);
    }
}
