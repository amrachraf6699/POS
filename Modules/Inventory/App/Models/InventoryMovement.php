<?php

namespace Modules\Inventory\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;
use Modules\Business\App\Models\Branch;
use Modules\Catalog\App\Models\Product;
use Modules\Identity\App\Domain\Tenancy\BelongsToTenant;
use Modules\Identity\App\Models\User;
use Modules\Inventory\App\Domain\Enums\InventoryMovementType;

/**
 * @property int $tenant_id
 * @property int $branch_id
 * @property int $product_id
 * @property InventoryMovementType $type
 * @property int $quantity
 * @property int $quantity_delta
 * @property int $balance_after
 * @property string $idempotency_key
 */
final class InventoryMovement extends Model
{
    use BelongsToTenant;

    protected $guarded = ['*'];

    protected $casts = [
        'type' => InventoryMovementType::class,
        'quantity' => 'integer',
        'quantity_delta' => 'integer',
        'balance_after' => 'integer',
        'actor_user_id' => 'integer',
    ];

    /** @param array<string, mixed> $attributes */
    public static function record(array $attributes): self
    {
        $movement = new self;
        $movement->forceFill($attributes);
        $movement->save();

        return $movement;
    }

    protected static function booted(): void
    {
        self::updating(static function (): void {
            throw new LogicException('Inventory movements are immutable.');
        });
        self::deleting(static function (): void {
            throw new LogicException('Inventory movements are immutable.');
        });
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }
}
