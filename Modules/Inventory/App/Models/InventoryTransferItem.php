<?php

namespace Modules\Inventory\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;
use Modules\Catalog\App\Models\Product;
use Modules\Identity\App\Domain\Tenancy\BelongsToTenant;

/**
 * @property int $product_id
 * @property int $quantity
 * @property int|null $transfer_out_movement_id
 * @property int|null $transfer_in_movement_id
 */
final class InventoryTransferItem extends Model
{
    use BelongsToTenant;

    protected $guarded = ['*'];

    protected $casts = ['quantity' => 'integer', 'transfer_out_movement_id' => 'integer', 'transfer_in_movement_id' => 'integer'];

    protected static function booted(): void
    {
        self::updating(static function (self $item): void {
            $allowed = ['transfer_out_movement_id', 'transfer_in_movement_id', 'updated_at'];
            if (array_diff(array_keys($item->getDirty()), $allowed) !== []) {
                throw new LogicException('Transfer items are immutable except for movement attachments.');
            }
        });
        self::deleting(static function (): void {
            throw new LogicException('Transfer items cannot be deleted.');
        });
    }

    public function transfer(): BelongsTo
    {
        return $this->belongsTo(InventoryTransfer::class, 'inventory_transfer_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function transferOutMovement(): BelongsTo
    {
        return $this->belongsTo(InventoryMovement::class, 'transfer_out_movement_id');
    }

    public function transferInMovement(): BelongsTo
    {
        return $this->belongsTo(InventoryMovement::class, 'transfer_in_movement_id');
    }
}
