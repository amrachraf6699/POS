<?php

namespace Modules\Catalog\App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Catalog\Database\Factories\ProductFactory;
use Modules\Identity\App\Domain\Tenancy\BelongsToTenant;

/**
 * @property int $id
 * @property int $tenant_id
 * @property int $category_id
 * @property int $tax_rate_id
 * @property string $name
 * @property string|null $sku
 * @property string|null $barcode
 * @property string|null $description
 * @property string $status
 * @property int $cost_price_minor
 * @property int $selling_price_minor
 * @property bool $track_inventory
 * @property int $low_stock_threshold
 * @property bool $allow_negative_stock
 * @property-read Category $category
 * @property-read TaxRate $taxRate
 */
final class Product extends Model
{
    use BelongsToTenant, HasFactory, SoftDeletes;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_INACTIVE = 'inactive';

    protected $fillable = ['category_id', 'tax_rate_id', 'name', 'sku', 'barcode', 'description', 'cost_price_minor', 'selling_price_minor', 'track_inventory', 'low_stock_threshold', 'allow_negative_stock', 'status'];

    protected $casts = ['cost_price_minor' => 'integer', 'selling_price_minor' => 'integer', 'track_inventory' => 'boolean', 'low_stock_threshold' => 'integer', 'allow_negative_stock' => 'boolean'];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function taxRate(): BelongsTo
    {
        return $this->belongsTo(TaxRate::class);
    }

    public function isSaleAvailable(): bool
    {
        return $this->status === self::STATUS_ACTIVE && ! $this->trashed();
    }

    public function scopeSaleAvailable(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    protected static function newFactory(): ProductFactory
    {
        return ProductFactory::new();
    }
}
