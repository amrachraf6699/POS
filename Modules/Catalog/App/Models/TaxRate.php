<?php

namespace Modules\Catalog\App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Catalog\Database\Factories\TaxRateFactory;
use Modules\Identity\App\Domain\Tenancy\BelongsToTenant;
use Modules\Identity\App\Models\Tenant;

/**
 * @property string $status
 * @property int $rate_basis_points
 * @property CarbonImmutable $effective_from
 * @property CarbonImmutable|null $effective_to
 */
final class TaxRate extends Model
{
    use BelongsToTenant, HasFactory;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_INACTIVE = 'inactive';

    protected $fillable = ['name', 'rate_basis_points', 'effective_from', 'effective_to', 'status'];

    protected $casts = ['effective_from' => 'immutable_date', 'effective_to' => 'immutable_date'];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isEffectiveOn(CarbonImmutable $date): bool
    {
        return $this->isActive()
            && $this->effective_from->lessThanOrEqualTo($date)
            && ($this->effective_to === null || $this->effective_to->greaterThan($date));
    }

    protected static function newFactory(): TaxRateFactory
    {
        return TaxRateFactory::new();
    }
}
