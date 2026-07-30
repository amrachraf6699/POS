<?php

namespace Modules\Inventory\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Business\App\Models\Branch;
use Modules\Catalog\App\Models\Product;
use Modules\Identity\App\Domain\Tenancy\BelongsToTenant;

/**
 * @property int $tenant_id
 * @property int $branch_id
 * @property int $product_id
 * @property int $quantity_on_hand
 * @property-read Branch $branch
 * @property-read Product $product
 */
final class InventoryBalance extends Model
{
    use BelongsToTenant;

    protected $guarded = ['*'];

    protected $casts = ['quantity_on_hand' => 'integer'];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
