<?php

namespace Modules\Catalog\App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Catalog\Database\Factories\CategoryFactory;
use Modules\Identity\App\Domain\Tenancy\BelongsToTenant;
use Modules\Identity\App\Models\Tenant;

final class Category extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = ['name', 'description'];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    protected static function newFactory(): CategoryFactory
    {
        return CategoryFactory::new();
    }
}
