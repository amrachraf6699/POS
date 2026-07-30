<?php

namespace Modules\Inventory\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;
use Modules\Catalog\App\Models\Product;
use Modules\Identity\App\Domain\Tenancy\BelongsToTenant;

/** @property int|null $inventory_movement_id */
final class StockAdjustmentItem extends Model
{
    use BelongsToTenant;

    protected $guarded = ['*'];

    protected $casts = ['quantity' => 'integer', 'inventory_movement_id' => 'integer'];

    protected static function booted(): void
    {
        self::updating(static function (self $item): void {
            if ($item->getOriginal('inventory_movement_id') === null
                && $item->isDirty('inventory_movement_id')
                && count($item->getDirty()) === 1) {
                return;
            }

            throw new LogicException('Stock adjustment items are immutable after posting.');
        });
        self::deleting(static function (): void {
            throw new LogicException('Stock adjustment items are immutable after posting.');
        });
    }

    public function adjustment(): BelongsTo
    {
        return $this->belongsTo(StockAdjustment::class, 'stock_adjustment_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function movement(): BelongsTo
    {
        return $this->belongsTo(InventoryMovement::class, 'inventory_movement_id');
    }

    public function attachMovement(InventoryMovement $movement): void
    {
        $this->forceFill(['inventory_movement_id' => $movement->getKey()]);
        $this->save();
    }
}
