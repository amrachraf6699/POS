<?php

namespace Modules\Inventory\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use LogicException;
use Modules\Business\App\Models\Branch;
use Modules\Identity\App\Domain\Tenancy\BelongsToTenant;
use Modules\Identity\App\Models\User;
use Modules\Inventory\App\Domain\Enums\StockAdjustmentType;

/**
 * @property StockAdjustmentType $type
 * @property int $branch_id
 * @property \Illuminate\Database\Eloquent\Collection<int, StockAdjustmentItem> $items
 */
final class StockAdjustment extends Model
{
    use BelongsToTenant;

    protected $guarded = ['*'];

    protected $casts = [
        'type' => StockAdjustmentType::class,
        'posted_at' => 'immutable_datetime',
        'actor_user_id' => 'integer',
    ];

    protected static function booted(): void
    {
        self::updating(static function (): void {
            throw new LogicException('Stock adjustments are immutable after posting.');
        });
        self::deleting(static function (): void {
            throw new LogicException('Stock adjustments are immutable after posting.');
        });
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(StockAdjustmentItem::class);
    }
}
